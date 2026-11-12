<?php
session_start();
require 'koneksi.php'; // Include database connection

// Retrieve selected date or default to current date
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$currentDate = date('Y-m-d');
$previousDate = date('Y-m-d', strtotime('-1 day', strtotime($selectedDate)));
$nextDate = date('Y-m-d', strtotime('+1 day', strtotime($selectedDate)));

// Perbarui status pemesanan yang sudah "diantar" lebih dari 5 menit
$updateDeliveredToCompletedQuery = "
    UPDATE tblstatuspemesanan
    SET status_pemesanan = 'selesai'
    WHERE status_pemesanan = 'diantar'
    AND TIMESTAMPDIFF(MINUTE, tanggal_pemesanan, NOW()) >= 5
";

$conn->query($updateDeliveredToCompletedQuery);

// Query untuk mengambil data pesanan berdasarkan tanggal yang dipilih
$ordersQuery = "
    SELECT 
        p.id_pemesanan,
        p.nama_pemesan,
        m.menu_name AS produk,
        p.jumlah_pemesanan / m.menu_price AS jumlah, -- Pembagian berdasarkan menu_price
        s.status_pemesanan,
        s.tanggal_pemesanan
    FROM tblpemesanan p
    JOIN tblmenu m ON p.produk_id = m.menu_id
    JOIN tblstatuspemesanan s ON p.id_pemesanan = s.id_pemesanan
    WHERE s.tanggal_pemesanan = ?  -- Filter berdasarkan tanggal_pemesanan yang sudah ada di tblstatuspemesanan
    ORDER BY p.id_pemesanan DESC
";

$stmt = $conn->prepare($ordersQuery);
$stmt->bind_param('s', $selectedDate);  // Menggunakan $selectedDate untuk mengambil data sesuai tanggal yang dipilih
$stmt->execute();
$ordersResult = $stmt->get_result();

// Query untuk menghitung total pesanan, ongoing, diantar, dan selesai berdasarkan tanggal yang dipilih
$totalOrdersQuery = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN s.status_pemesanan = 'ongoing' THEN 1 ELSE 0 END) as total_ongoing,
        SUM(CASE WHEN s.status_pemesanan = 'diantar' THEN 1 ELSE 0 END) as total_delivered,
        SUM(CASE WHEN s.status_pemesanan = 'selesai' THEN 1 ELSE 0 END) as total_completed
    FROM tblpemesanan p
    JOIN tblstatuspemesanan s ON p.id_pemesanan = s.id_pemesanan
    WHERE s.tanggal_pemesanan = ?  -- Filter berdasarkan tanggal_pemesanan yang sudah ada di tblstatuspemesanan
";

$stmt = $conn->prepare($totalOrdersQuery);
$stmt->bind_param('s', $selectedDate);  // Menggunakan $selectedDate untuk menghitung total pesanan
$stmt->execute();
$totalOrdersResult = $stmt->get_result();
$totalOrders = $totalOrdersResult->fetch_assoc();
$totalOrdersCount = $totalOrders['total_orders'];
$totalOngoing = $totalOrders['total_ongoing'];
$totalDelivered = $totalOrders['total_delivered'];
$totalCompleted = $totalOrders['total_completed'];

// Menangani permintaan update atau delete pesanan
if (isset($_POST['update_order'])) {
    $orderId = intval($_POST['order_id']);
    $newOrderStatus = htmlspecialchars($_POST['status_pemesanan']);

    $updateOrderQuery = "UPDATE tblstatuspemesanan SET status_pemesanan = ? WHERE id_pemesanan = ?";
    $stmt = $conn->prepare($updateOrderQuery);
    if ($stmt) {
        $stmt->bind_param('si', $newOrderStatus, $orderId);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: hal_shipment.php');
    exit();
} elseif (isset($_POST['delete_order'])) {
    $orderId = intval($_POST['order_id']);

    $deleteOrderQuery = "DELETE FROM tblpemesanan WHERE id_pemesanan = ?";
    $stmt = $conn->prepare($deleteOrderQuery);
    if ($stmt) {
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: hal_shipment.php');
    exit();
}

// Mengambil nama admin dari database
$adminId = $_SESSION['id_user'] ?? null; 
$adminName = '';

if ($adminId) {
    $adminQuery = "SELECT nama_user FROM tbluser WHERE id_user = ?";
    $stmt = $conn->prepare($adminQuery);
    if ($stmt) {
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $stmt->bind_result($adminName);
        $stmt->fetch();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/adm.css">
    <title>Dashboard Pengantaran</title>
</head>
<body>
<div class="container">
    <aside>
        <div class="toggle">
            <div class="logo">
                <img src="../asset/logo1.gif">
                <h2>Admini<span class="danger">strator</span></h2>
            </div>
            <div class="close" id="close-btn">
                <span class="material-icons-sharp">close</span>
            </div>
        </div>
        <div class="sidebar">
            <a href="../hal_admin.php">
                <span class="material-icons-sharp">dashboard</span>
                <h3>Dashboard</h3>
            </a>
            <a href="hal_user.php">
                <span class="material-icons-sharp">person_outline</span>
                <h3>Users</h3>
            </a>
            <a href="hal_pembayaran.php">
                <span class="material-icons-sharp">receipt_long</span>
                <h3>Pembayaran</h3>
            </a>
            <a href="hal_shipment.php">
                <span class="material-symbols-outlined">acute</span>
                <h3>Shipment</h3>
            </a>
            <a href="hal_stats.php">
                <span class="material-icons-sharp">insights</span>
                <h3>Analytics</h3>
            </a>
            <a href="../logout.php">
                <span class="material-icons-sharp">logout</span>
                <h3>Logout</h3>
            </a>
        </div>
    </aside>

    <main>
        <h1>Shipment</h1>
        
        <div class="filter-navigation">
            <form method="GET" style="text-align: center;">
                <button type="submit" name="date" value="<?= $previousDate ?>">Previous Day</button>
                <input type="date" name="date" value="<?= $selectedDate ?>" onchange="this.form.submit()">
                <button type="submit" name="date" value="<?= $nextDate ?>">Next Day</button>
            </form>
        </div>

        <div class="date-display">
            <h3>Tanggal: <?= date('l, d F Y', strtotime($selectedDate)) ?></h3>
        </div>

        <div class="analyse">
            <div class="status">
                <h3>Total Pemesanan</h3>
                <h1><?= $totalOrdersCount ?></h1>
            </div>
            <div class="status">
                <h3>Pemesanan Ongoing</h3>
                <h1><?= $totalOngoing ?></h1>
            </div>
            <div class="status">
                <h3>Pemesanan Diantar</h3>
                <h1><?= $totalDelivered ?></h1>
            </div>
            <div class="status">
                <h3>Pemesanan Selesai</h3>
                <h1><?= $totalCompleted ?></h1>
            </div>
        </div>

        <div class="recent-orders">
            <h2>Data Pemesanan</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pemesan</th>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
                    <?php while ($order = $ordersResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= $order['id_pemesanan'] ?></td>
                            <td><?= htmlspecialchars($order['nama_pemesan']) ?></td>
                            <td><?= htmlspecialchars($order['produk']) ?></td>
                            <td><?= number_format($order['jumlah'], 2) ?></td>
                            <td>
                                <?php 
                                    $status_pemesanan = htmlspecialchars($order['status_pemesanan']);
                                    if ($status_pemesanan == 'ongoing') {
                                        echo '<span style="color: red;">' . $status_pemesanan . '</span>';
                                    } elseif ($status_pemesanan == 'diantar') {
                                        echo '<span style="color: orange;">' . $status_pemesanan . '</span>';
                                    } elseif ($status_pemesanan == 'selesai') {
                                        echo '<span style="color: green;">' . $status_pemesanan . '</span>';
                                    } else {
                                        echo '<span>' . $status_pemesanan . '</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <form action="hal_shipment.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['id_pemesanan'] ?>">
                                    <select name="status_pemesanan">
                                        <option value="ongoing" <?= $status_pemesanan == 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                        <option value="diantar" <?= $status_pemesanan == 'diantar' ? 'selected' : '' ?>>Diantar</option>
                                        <option value="selesai" <?= $status_pemesanan == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                    </select>
                                    <button type="submit" name="update_order">Update</button>
                                </form>
                                <form action="hal_shipment.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['id_pemesanan'] ?>">
                                    <button type="submit" name="delete_order" style="color: red;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">Tidak ada data pemesanan untuk tanggal ini.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <div class="right-section">
            <div class="nav">
                <button id="menu-btn">
                    <span class="material-icons-sharp">
                        menu
                    </span>
                </button>
                <div class="dark-mode">
                    <span class="material-icons-sharp active">
                        light_mode
                    </span>
                    <span class="material-icons-sharp">
                        dark_mode
                    </span>
                </div>

                <div class="profile">
                    <div class="admin-header">
                         <p>Hi, <b><span class="admin-name"><?= htmlspecialchars($adminName) ?></span></b> </p>
                         <p>Admin</p>
                    </div>
                   
                </div>

            </div>
            <!-- End of Nav -->

            <div class="user-profile">
                <div class="logo">
                    <img src="../asset/Delivery Cars Icons.gif">
                    <h2>Administrator</h2>
                    <p>Disini kamu bisa melihat dan mengatur status pengantaran pesanan para pengguna</p>
                </div>
            </div>

    </div>
</div>


<style>
    .date-display {
        margin-bottom: 20px;
        font-size: 18px;
        font-weight: bold;
    }

    .filter-navigation form {
        text-align: center;
        margin: 20px 0;
    }

    .filter-navigation button {
        padding: 10px 15px;
        margin: 0 10px;
        color: white;
        background-color: #4caf50;
        border-radius: 5px;
        text-decoration: none;
    }

    .filter-navigation input[type="date"] {
        padding: 10px;
        font-size: 16px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .filter-navigation button:hover {
        background-color: #45a049;
    }

    
    form button {
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        
        form button[name="update_order"] {
            background-color: #4caf50; 
        }
        
        form button[name="update_order"]:hover {
            background-color: #45a049; 
            transform: scale(1.05); 
        }
        
        form button[name="delete_order"] {
            background-color: #f44336; 
        }
        
        form button[name="delete_order"]:hover {
            background-color: #d32f2f; 
            transform: scale(1.05); 
        }
        
        form {
            display: inline-block;
            margin: 0;
        }
</style>
</body>
</html>