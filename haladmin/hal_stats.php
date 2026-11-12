<?php
session_start();
require 'koneksi.php'; // Include database connection

// Handle date range for the chart
$currentDate = isset($_GET['current_date']) ? $_GET['current_date'] : date('Y-m-d');
$startDate = date('Y-m-d', strtotime($currentDate . ' -6 days'));
$endDate = $currentDate;

// Retrieve weekly order and income data for chart
$orderIncomeQuery = "
    SELECT 
        DATE(tanggal_pemesanan) as order_date,
        COUNT(*) as total_orders,
        SUM(total_pembayaran) as total_income
    FROM tblpemesanan
    LEFT JOIN tblpembayaran ON tblpemesanan.id_pemesanan = tblpembayaran.id_pemesanan
    WHERE DATE(tanggal_pemesanan) BETWEEN ? AND ?
    GROUP BY DATE(tanggal_pemesanan)
    ORDER BY DATE(tanggal_pemesanan)
";
$stmt = $conn->prepare($orderIncomeQuery);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

$orderData = [];
$incomeData = [];
$dateLabels = [];
while ($row = $result->fetch_assoc()) {
    $orderData[$row['order_date']] = $row['total_orders'];
    $incomeData[$row['order_date']] = $row['total_income'] ?: 0;
    $dateLabels[] = $row['order_date'];
}
$stmt->close();

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

// Fill missing dates with zero values
$period = new DatePeriod(
    new DateTime($startDate),
    new DateInterval('P1D'),
    new DateTime($endDate . ' +1 day')
);
foreach ($period as $date) {
    $formattedDate = $date->format('Y-m-d');
    if (!isset($orderData[$formattedDate])) {
        $orderData[$formattedDate] = 0;
        $incomeData[$formattedDate] = 0;
    }
}
ksort($orderData);
ksort($incomeData);
$dateLabels = array_keys($orderData);
$orderValues = array_values($orderData);
$incomeValues = array_values($incomeData);

// Handle total income navigation by month
$currentMonth = isset($_GET['current_month']) ? $_GET['current_month'] : date('Y-m');
$monthStartDate = date('Y-m-01', strtotime($currentMonth));
$monthEndDate = date('Y-m-t', strtotime($currentMonth));

$totalIncomeQuery = "
    SELECT SUM(total_pembayaran) as total_income
    FROM tblpembayaran
    WHERE DATE(tanggal_pembayaran) BETWEEN ? AND ?
";
$stmt = $conn->prepare($totalIncomeQuery);
$stmt->bind_param('ss', $monthStartDate, $monthEndDate);
$stmt->execute();
$stmt->bind_result($totalIncome);
$stmt->fetch();
$stmt->close();

$totalIncome = $totalIncome ?: 0;
$formattedTotalIncome = number_format($totalIncome, 0, ',', '.');

$prevMonth = date('Y-m', strtotime($currentMonth . ' -1 month'));
$nextMonth = date('Y-m', strtotime($currentMonth . ' +1 month'));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/adm.css">
    <title>Dashboard</title>
    <style>
        .chart-container {
            width: 80%;
            margin: auto;
            padding: 40px 0;
            background-color: #f7f7f7;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .navigation-buttons a {
            margin: 0 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .navigation-buttons a:hover {
            background-color: #0056b3;
            transform: scale(1.1);
        }
    </style>
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
        <a href="hal_pembayaran.php">
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
        <a href="hal_stats.php" class="active">
            <span class="material-icons-sharp">
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
    <h1>Statistika</h1>
    <div class="analyse">
        <div class="status">
            <h3>Total Income (Monthly)</h3>
            <h1>Rp<?= $formattedTotalIncome ?></h1>
        </div>
    </div>
    <div class="navigation-buttons">
        <a href="?current_month=<?= $prevMonth ?>">Previous Month</a>
        <a href="?current_month=<?= $nextMonth ?>">Next Month</a>
    </div>

    <div class="chart-container">
        <canvas id="orderIncomeChart"></canvas>
        <div class="navigation-buttons">
            <a href="?current_date=<?= date('Y-m-d', strtotime($currentDate . ' -1 day')) ?>">Previous Day</a>
            <a href="?current_date=<?= date('Y-m-d', strtotime($currentDate . ' +1 day')) ?>">Next Day</a>
        </div>
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
            <img src="../asset/stat.gif">
            <h2>Administrator</h2>
            <p>Disini kamu bisa melihat dan mengubah status pembayaran pengguna</p>
        </div>
    </div>
</div>
</div>

<script>
    const ctx = document.getElementById('orderIncomeChart').getContext('2d');
    const orderIncomeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($dateLabels) ?>,
            datasets: [
                {
                    label: 'Total Orders',
                    data: <?= json_encode($orderValues) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                },
                {
                    label: 'Total Income (Rp)',
                    data: <?= json_encode($incomeValues) ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true,
                },
                y: {
                    beginAtZero: true,
                }
            }
        }
    });
</script>

<script src="../js/admin.js"></script>
</body>
</html>
