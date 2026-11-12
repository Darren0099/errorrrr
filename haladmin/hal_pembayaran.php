<?php
session_start();
require 'koneksi.php'; // Include database connection

// Get the selected date from the URL or default to today
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Calculate the previous and next dates
$prevDate = date('Y-m-d', strtotime($selectedDate . ' -1 day'));
$nextDate = date('Y-m-d', strtotime($selectedDate . ' +1 day'));

// Perbarui status pembayaran yang berstatus "pending" lebih dari 5 menit menjadi "failed"
$updatePendingToFailedQuery = "
    UPDATE tblpembayaran
    SET status_pembayaran = 'failed', status_update_at = NOW()
    WHERE status_pembayaran = 'pending'
    AND TIMESTAMPDIFF(MINUTE, tanggal_pembayaran, NOW()) >= 5
";
$conn->query($updatePendingToFailedQuery);

// Retrieve total orders for the selected date
$totalOrdersQuery = "SELECT COUNT(*) as total_orders FROM tblpemesanan WHERE DATE(tanggal_pemesanan) = ?";
$stmt = $conn->prepare($totalOrdersQuery);
$stmt->bind_param('s', $selectedDate);
$stmt->execute();
$totalOrdersResult = $stmt->get_result();
$totalOrders = $totalOrdersResult->fetch_assoc()['total_orders'] ?? 0;

// Retrieve total income for the selected date
$totalIncomeQuery = "SELECT SUM(total_pembayaran) as total_income FROM tblpembayaran WHERE DATE(tanggal_pembayaran) = ?";
$stmt = $conn->prepare($totalIncomeQuery);
$stmt->bind_param('s', $selectedDate);
$stmt->execute();
$totalIncomeResult = $stmt->get_result();
$totalIncome = $totalIncomeResult->fetch_assoc()['total_income'] ?? 0;

// Retrieve payment statuses for the selected date
$statusQuery = "
    SELECT 
        COUNT(CASE WHEN status_pembayaran = 'completed' THEN 1 END) as completed,
        COUNT(CASE WHEN status_pembayaran = 'pending' THEN 1 END) as pending,
        COUNT(CASE WHEN status_pembayaran = 'failed' THEN 1 END) as failed
    FROM tblpembayaran
    WHERE DATE(tanggal_pembayaran) = ?";
$stmt = $conn->prepare($statusQuery);
$stmt->bind_param('s', $selectedDate);
$stmt->execute();
$statusResult = $stmt->get_result();
$statusCounts = $statusResult->fetch_assoc();
$completedPayments = $statusCounts['completed'] ?? 0;
$pendingPayments = $statusCounts['pending'] ?? 0;
$failedPayments = $statusCounts['failed'] ?? 0;

// Retrieve recent payments for the selected date
$paymentsQuery = "
    SELECT DISTINCT
        b.id_pembayaran,
        b.metode_pembayaran,
        b.total_pembayaran,
        b.status_pembayaran,
        u.nama_user
    FROM tblpembayaran b
    JOIN tbluser u ON b.id_user = u.id_user
    WHERE DATE(b.tanggal_pembayaran) = ?
    ORDER BY b.id_pembayaran DESC";
$stmt = $conn->prepare($paymentsQuery);
$stmt->bind_param('s', $selectedDate);
$stmt->execute();
$paymentsResult = $stmt->get_result();

// Handle payment status update
if (isset($_POST['update_payment'])) {
    $paymentId = $_POST['payment_id'] ?? null;
    $newStatus = $_POST['new_status'] ?? null;

    if ($paymentId && $newStatus) {
        // Update the status in the database
        $updateQuery = "UPDATE tblpembayaran SET status_pembayaran = ?, status_update_at = NOW() WHERE id_pembayaran = ?";
        $stmt = $conn->prepare($updateQuery);

        if ($stmt) {
            $stmt->bind_param('si', $newStatus, $paymentId);
            if ($stmt->execute()) {
                echo "<script>alert('Payment status updated successfully!'); window.location.href = 'hal_pembayaran.php?date=$selectedDate';</script>";
            } else {
                echo "<script>alert('Failed to update payment status.');</script>";
            }
            $stmt->close();
        }
    } else {
        echo "<script>alert('Invalid payment ID or status.');</script>";
    }
}

// Retrieve admin name
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

// Format total income
$formattedTotalIncome = number_format($totalIncome, 0, ',', '.');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/adm.css">
    <title>Dashboard Payments</title>
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
                    <span class="material-icons-sharp">
                        close
                    </span>
                </div>
            </div>

            <div class="sidebar">
                <a href="../hal_admin.php">
                    <span class="material-icons-sharp">
                        dashboard
                    </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="hal_user.php">
                    <span class="material-icons-sharp">
                        person_outline
                    </span>
                    <h3>Users</h3>
                </a>
                <a href="haladmin/hal_pembayaran.php" class="active">
                    <span class="material-icons-sharp">
                        receipt_long
                    </span>
                    <h3>Pembayaran</h3>
                </a>
                <a href="hal_shipment.php">
                     <span class="material-symbols-outlined">
                        acute
                    </span>
                    <h3>Shipment</h3>
                </a>
                <a href="hal_stats.php">
                    <span class="material-icons-sharp">
                        insights
                    </span>
                    <h3>Analytics</h3>
                </a>
                <a href="chat.php">
                            <span class="material-icons-sharp" class="active">
                                insights
                            </span>
                            <h3>Analytics</h3>
                        </a>
                <a href="../logout.php">
                    <span class="material-icons-sharp">
                        logout
                    </span>
                    <h3>Logout</h3>
                </a>
            </div>
        </aside>
    <main>
        <h1>Payment Dashboard</h1>
        <div class="filter-navigation">
            <form method="GET" style="text-align: center;">
                <button type="submit" name="date" value="<?= $prevDate ?>">Previous Day</button>
                <input type="date" name="date" value="<?= $selectedDate ?>" onchange="this.form.submit()">
                <button type="submit" name="date" value="<?= $nextDate ?>">Next Day</button>
            </form>
        </div>
        <div class="analyse">
            <div class="status">
                <h3>Total Orders</h3>
                <h1><?= $totalOrders ?></h1>
            </div>
            <div class="status">
                <h3>Total Income</h3>
                <h1>Rp<?= $formattedTotalIncome ?></h1>
            </div>
            <div class="status">
                <h3>Completed Payments</h3>
                <h1><?= $completedPayments ?></h1>
            </div>
            <div class="status">
                <h3>Pending Payments</h3>
                <h1><?= $pendingPayments ?></h1>
            </div>
            <div class="status">
                <h3>Failed Payments</h3>
                <h1><?= $failedPayments ?></h1>
            </div>
        </div>

        <div class="recent-orders">
            <h2>Recent Payments</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Payment Method</th>
                        <th>Total Payment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
    <?php while ($payment = $paymentsResult->fetch_assoc()): ?>
        <tr>
            <td><?= $payment['id_pembayaran'] ?></td>
            <td><?= htmlspecialchars($payment['nama_user']) ?></td>
            <td><?= htmlspecialchars($payment['metode_pembayaran']) ?></td>
            <td>Rp<?= number_format($payment['total_pembayaran'], 0, ',', '.') ?></td>
            <td>
                            <?php 
                                $status_pembayaran = htmlspecialchars($payment['status_pembayaran']);
                                if ($status_pembayaran == 'pending') {
                                    echo '<span style="color: orange;">' . $status_pembayaran . '</span>';
                                } elseif ($status_pembayaran == 'completed') {
                                    echo '<span style="color: green;">' . $status_pembayaran . '</span>';
                                } elseif ($status_pembayaran == 'failed') {
                                    echo '<span style="color: red;">' . $status_pembayaran . '</span>';
                                } else {
                                    echo '<span>' . $status_pembayaran . '</span>';
                                }
                            ?>
                        </td>
                        <td>
                        <form method="POST" style="display:inline-block;">
    <input type="hidden" name="payment_id" value="<?= $payment['id_pembayaran'] ?>">
    <select name="new_status" required>
        <option value="pending" <?= $payment['status_pembayaran'] === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="completed" <?= $payment['status_pembayaran'] === 'completed' ? 'selected' : '' ?>>Completed</option>
        <option value="failed" <?= $payment['status_pembayaran'] === 'failed' ? 'selected' : '' ?>>Failed</option>
    </select>
    <button type="submit" name="update_payment">Update</button>
</form>

                        </td>
        </tr>
    <?php endwhile; ?>
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
                    <img src="../asset/Payment method success.gif">
                    <h2>Administrator</h2>
                    <p>Disini kamu bisa melihat dan mengubah status pembayaran pengguna</p>
                </div>
            </div>

        </div>
</div>
<style>
        
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
        
        form button[name="update_payment"] {
            background-color: #4caf50; 
        }
        
        form button[name="update_payment"]:hover {
            background-color: #45a049; 
            transform: scale(1.05); 
        }
        
        form button[name="delete_payment"] {
            background-color: #f44336; 
        }
        
        form button[name="delete_payment"]:hover {
            background-color: #d32f2f; 
            transform: scale(1.05); 
        }
        
        form {
            display: inline-block;
            margin: 0;
        }

        .filter-navigation button {
    background-color: #4A90E2; /* Default color */
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.filter-navigation button:hover {
    background-color: #357ABD; /* Darker blue */
}
.filter-navigation button[name="date"][value="<?= $nextDate ?>"] {
    background-color: #7ED321; /* Green */
}
.filter-navigation button[name="date"][value="<?= $prevDate ?>"] {
    background-color: #4A90E2; /* Blue */
}

            </style>
         <script src="../js/admin.js"></script>
</body>
</html>