<?php
session_start();
require 'koneksi.php'; // Include database connection

// Retrieve the selected month or default to the current month
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$currentMonth = date('Y-m');
$previousMonth = date('Y-m', strtotime('-1 month', strtotime($selectedMonth)));
$nextMonth = date('Y-m', strtotime('+1 month', strtotime($selectedMonth)));

// Retrieve total number of orders
$totalOrdersQuery = "SELECT COUNT(*) as total_orders FROM tblpemesanan WHERE DATE_FORMAT(tanggal_pemesanan, '%Y-%m') = '$selectedMonth'";
$totalOrdersResult = $conn->query($totalOrdersQuery);
$totalOrders = $totalOrdersResult ? $totalOrdersResult->fetch_assoc()['total_orders'] : 0;

// Retrieve total number of users
$totalUsersQuery = "SELECT COUNT(*) as total_users FROM tbluser";
$totalUsersResult = $conn->query($totalUsersQuery);
$totalUsers = $totalUsersResult ? $totalUsersResult->fetch_assoc()['total_users'] : 0;

// Retrieve total income from payments for the selected month
$totalIncomeQuery = "SELECT SUM(total_pembayaran) as total_income FROM tblpembayaran WHERE DATE_FORMAT(tanggal_pembayaran, '%Y-%m') = '$selectedMonth'";
$totalIncomeResult = $conn->query($totalIncomeQuery);
$totalIncome = $totalIncomeResult ? $totalIncomeResult->fetch_assoc()['total_income'] : 0;

// Retrieve most recent user who placed an order
$recentUserQuery = "
    SELECT u.nama_user, MAX(p.tanggal_pemesanan) as last_order_time
    FROM tblpemesanan p
    JOIN tbluser u ON p.id_user = u.id_user
    GROUP BY u.id_user
    ORDER BY last_order_time DESC
    LIMIT 1
";
$recentUserResult = $conn->query($recentUserQuery);
$recentUser = $recentUserResult ? $recentUserResult->fetch_assoc() : null;

// Ambil bulan yang dipilih dari form, default adalah bulan saat ini
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Memecah bulan dan tahun yang dipilih
$yearMonth = explode('-', $selectedMonth);
$year = $yearMonth[0];
$month = $yearMonth[1];
$recentOrdersQuery = "
    SELECT 
        p.id_pemesanan, 
        u.nama_user, 
        p.tanggal_pemesanan, 
        m.menu_name AS pesanan, 
        (p.jumlah_pemesanan / m.menu_price) AS quantity,
        p.alamat_pemesan AS alamat, 
        sp.status_pemesanan AS status,
        IF(tp.status_pembayaran IS NULL, 'Unknown', tp.status_pembayaran) AS status_pembayaran
    FROM tblpemesanan p
    JOIN tbluser u ON p.id_user = u.id_user
    JOIN tblmenu m ON p.produk_id = m.menu_id
    LEFT JOIN tblstatuspemesanan sp ON p.id_pemesanan = sp.id_pemesanan
    LEFT JOIN tblpembayaran tp ON p.id_pemesanan = tp.id_pemesanan
    WHERE YEAR(p.tanggal_pemesanan) = ? AND MONTH(p.tanggal_pemesanan) = ?
    GROUP BY p.id_pemesanan
    ORDER BY p.tanggal_pemesanan DESC
";


$stmt = $conn->prepare($recentOrdersQuery);
$stmt->bind_param('ii', $year, $month);
$stmt->execute();
$recentOrdersResult = $stmt->get_result();



// Handle update status request
if (isset($_POST['update_order'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = htmlspecialchars($_POST['status_pemesanan']);

    $updateStatusQuery = "UPDATE tblstatuspemesanan SET status_pemesanan = ? WHERE id_pemesanan = ?";
    $stmt = $conn->prepare($updateStatusQuery);
    if ($stmt) {
        $stmt->bind_param('si', $newStatus, $orderId);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: hal_admin.php');
    exit();
}

// Handle update payment status request
if (isset($_POST['update_payment'])) {
    $paymentId = intval($_POST['payment_id']);
    $newPaymentStatus = htmlspecialchars($_POST['status_pembayaran']);

    $updatePaymentQuery = "UPDATE tblpembayaran SET status_pembayaran = ? WHERE id_pembayaran = ?";
    $stmt = $conn->prepare($updatePaymentQuery);
    if ($stmt) {
        $stmt->bind_param('si', $newPaymentStatus, $paymentId);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: hal_admin.php');
    exit();
}

// Ambil data notifikasi yang belum diberitahukan
$query = "
    SELECT p.id_pembayaran, p.id_user, p.total_pembayaran, p.metode_pembayaran, p.tanggal_pembayaran, u.nama_user 
    FROM tblpembayaran p 
    JOIN tbluser u ON p.id_user = u.id_user 
    WHERE p.is_notified = 0
    ORDER BY p.tanggal_pembayaran DESC
";
$result = $conn->query($query);

// Konversi data ke array
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Ambil notifikasi terbaru
$latestNotification = count($notifications) > 0 ? $notifications[0] : null;

// Tandai notifikasi sebagai sudah diberitahukan (jika ada permintaan AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['id_pembayaran'])) {
        $id_pembayaran = $data['id_pembayaran'];
        $updateQuery = "UPDATE tblpembayaran SET is_notified = 1 WHERE id_pembayaran = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $id_pembayaran);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["status" => "success"]);
        exit;
    }
}

// Retrieve the admin's name from the database
$adminId = $_SESSION['id_user'] ?? null; // Assuming id_user is stored in the session
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

// Format total income with trailing zeros
$formattedTotalIncome = number_format($totalIncome, 0, ',', '.');
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="css/adm.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=acute" />
    <link rel="stylesheet" href="">
    <title>Administrator</title>
</head>

<body>
    <div class="container">
        
        <!-- Sidebar Section -->
        <aside class="slide-up">
            <div class="toggle slide-up">
                <div class="logo">
                    <img src="asset/logo.gif">
                    <h2>Admini<span class="danger">strator</span></h2>
                </div>
                <div class="close" id="close-btn">
                    <span class="material-icons-sharp">
                        close
                    </span>
                </div>
            </div>

            <div class="sidebar slide-up">
                <a href="adminpage/alamat.php" class="active">
                    <span class="material-icons-sharp">
                        dashboard
                    </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="haladmin/hal_user.php">
                    <span class="material-icons-sharp">
                        person_outline
                    </span>
                    <h3>Users</h3>
                </a>
                <a href="haladmin/hal_pembayaran.php">
                    <span class="material-icons-sharp">
                        receipt_long
                    </span>
                    <h3>Pembayaran</h3>
                </a>
                <a href="haladmin/hal_shipment.php">
                     <span class="material-symbols-outlined">
                        acute
                    </span>
                    <h3>Shipment</h3>
                </a>
                <a href="haladmin/hal_stats.php">
                    <span class="material-icons-sharp">
                        insights
                    </span>
                    <h3>Analytics</h3>
                </a>
                

                <a href="logout.php">
                    <span class="material-icons-sharp">
                        logout
                    </span>
                    <h3>Logout</h3>
                </a>
            </div>
        </aside>
        <!-- End of Sidebar Section -->

        <!-- Main Content -->
        <main>
            
            <h1>Dashboard</h1>
            <div class="analyse slide-up">
    <div class="sales">
        <div class="status">
            <div class="info">
                <h3>Total Orders</h3>
                <h1><?= $totalOrders ?></h1>
            </div>
        </div>
    </div>
    <div class="visits">
        <div class="status">
            <div class="info">
                <h3>Total Users</h3>
                <h1><?= $totalUsers ?></h1>
            </div>
        </div>
    </div>
    <div class="visits">
        <div class="status">
            <div class="info">
                <h3>Total Pemasukkan</h3>
                <h1>Rp<?= $formattedTotalIncome ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="recent-orders">
    <h2>Recent Orders</h2>
    <form method="GET">
        <label for="month">Select Month:</label>
        <input type="month" name="month" value="<?= $selectedMonth ?>" onchange="this.form.submit()">
    </form><table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Order Date</th>
            <th>Items</th>
            <th>Quantity</th>
            <th>Address</th>
            <th>Status</th>
            <th>Payment</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($recentOrdersResult && $recentOrdersResult->num_rows > 0): ?>
        <?php while ($order = $recentOrdersResult->fetch_assoc()): ?>
            <?php
                // Warna berdasarkan status pemesanan
                $statusColor = '';
                switch (strtolower($order['status'])) {
                    case 'ongoing':
                        $statusColor = 'color: red;';
                        break;
                    case 'diantar':
                        $statusColor = 'color: orange;';
                        break;
                    case 'selesai':
                        $statusColor = 'color: green;';
                        break;
                }

                // Warna berdasarkan status pembayaran
                $paymentColor = '';
                switch (strtolower($order['status_pembayaran'])) {
                    case 'pending':
                        $paymentColor = 'color: orange;';
                        break;
                    case 'completed':
                        $paymentColor = 'color: green;';
                        break;
                    case 'failed':
                        $paymentColor = 'color: red;';
                        break;
                    default:
                        $paymentColor = 'color: gray;'; // Untuk nilai NULL atau tidak diketahui
                        break;
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($order['id_pemesanan']) ?></td>
                <td><?= htmlspecialchars($order['nama_user']) ?></td>
                <td><?= htmlspecialchars($order['tanggal_pemesanan']) ?></td>
                <td><?= htmlspecialchars($order['pesanan']) ?></td>
                <td><?= number_format($order['quantity'], 2) ?></td>
                <td><?= htmlspecialchars($order['alamat']) ?></td>
                <td style="<?= $statusColor ?>"><?= htmlspecialchars($order['status']) ?></td>
                <td style="<?= $paymentColor ?>"><?= htmlspecialchars($order['status_pembayaran']) ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="8">No recent orders found for this month.</td>
        </tr>
    <?php endif; ?>
</tbody>

</table>

</div>

<div class="notification-container" id="notification-container">
    <?php foreach ($notifications as $notif): ?>
        <div class="notification-popup collapsing" id="notif-<?php echo $notif['id_pembayaran']; ?>">
            <div class="icon" onclick="markAsRead(<?php echo $notif['id_pembayaran']; ?>)">
                <img src="https://cdn-icons-png.flaticon.com/512/709/709496.png" alt="Check Icon">
            </div>
            <div class="text">
                Pelanggan <strong><?php echo $notif['nama_user']; ?></strong> 
                (ID: <?php echo $notif['id_user']; ?>) 
                telah membayar Rp<?php echo number_format($notif['total_pembayaran'], 0, ',', '.'); ?> 
                menggunakan <strong><?php echo $notif['metode_pembayaran']; ?></strong> 
                pada <strong><?php echo $notif['tanggal_pembayaran']; ?></strong>.
            </div>
        </div>
    <?php endforeach; ?>
</div>

        </main>
        <!-- End of Main Content -->
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
                <div class="notification-icon" onclick="toggleNotifications()">
                    <img src="https://cdn-icons-png.flaticon.com/512/1827/1827504.png" alt="Notification Icon">
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
                    <img src="asset/admin.gif">
                    <h2>Administrator</h2>
                    <p>Disini kamu bisa melihat para pengguna baru dan mengorganisir pemeliharaan website</p>
                </div>
            </div>

        </div>
    </div>
    <script src="js/admin.js"></script>
    <script>
        setTimeout(function() {
            var message = document.querySelector('.login-message');
            if (message) {
                message.style.display = 'none'; 
            }
        }, 8000); 
    </script>
    
    <script>
        let isOpen = false;

        function markAsRead(idPembayaran) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_pembayaran: idPembayaran })
            }).then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    const notificationElement = document.getElementById(`notif-${idPembayaran}`);
                    if (notificationElement) {
                        notificationElement.remove();
                    }
                }
            });
        }

        function toggleNotifications() {
            const container = document.getElementById('notification-container');
            isOpen = !isOpen;

            if (isOpen) {
                container.style.display = 'flex';
                const popups = container.querySelectorAll('.notification-popup');
                popups.forEach(popup => {
                    popup.classList.remove('collapsing');
                    popup.classList.add('expanding');
                });
            } else {
                const popups = container.querySelectorAll('.notification-popup');
                popups.forEach(popup => {
                    popup.classList.remove('expanding');
                    popup.classList.add('collapsing');
                });
                setTimeout(() => {
                    container.style.display = 'none';
                }, 800);
            }
        }
    </script>
</body>

</html>
