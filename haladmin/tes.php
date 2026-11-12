<?php
session_start();
require 'koneksi.php'; // Include database connection

// Retrieve total number of orders
$totalOrdersQuery = "SELECT COUNT(*) as total_orders FROM tblpemesanan";
$totalOrdersResult = $conn->query($totalOrdersQuery);
$totalOrders = $totalOrdersResult && $totalOrdersResult->num_rows > 0
    ? $totalOrdersResult->fetch_assoc()['total_orders']
    : 0;

// Retrieve total number of users
$totalUsersQuery = "SELECT COUNT(*) as total_users FROM tbluser";
$totalUsersResult = $conn->query($totalUsersQuery);
$totalUsers = $totalUsersResult && $totalUsersResult->num_rows > 0
    ? $totalUsersResult->fetch_assoc()['total_users']
    : 0;

// Define date range (7 days)
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-6 days'));
$endDate = date('Y-m-d', strtotime($startDate . ' +6 days'));

// Retrieve total income from payments
$totalIncomeQuery = "SELECT SUM(total_pembayaran) as total_income FROM tblpembayaran";
$totalIncomeResult = $conn->query($totalIncomeQuery);
$totalIncome = $totalIncomeResult && $totalIncomeResult->num_rows > 0
    ? $totalIncomeResult->fetch_assoc()['total_income']
    : 0;

// Format total income
$formattedTotalIncome = $totalIncome > 0
    ? number_format($totalIncome, 0, ',', '.')
    : '0,000';

// Retrieve recent payments
$paymentsQuery = "
    SELECT 
        b.id_pembayaran,
        b.metode_pembayaran,
        b.total_pembayaran,
        b.status_pembayaran,
        u.nama_user
    FROM tblpembayaran b
    JOIN tbluser u ON b.id_user = u.id_user
    ORDER BY b.id_pembayaran DESC
";
$paymentsResult = $conn->query($paymentsQuery);

// Retrieve daily order and income data for chart
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

// Handle update or delete payment requests
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

    header('Location: hal_pembayaran.php');
    exit();
} elseif (isset($_POST['delete_payment'])) {
    $paymentId = intval($_POST['payment_id']);

    $deletePaymentQuery = "DELETE FROM tblpembayaran WHERE id_pembayaran = ?";
    $stmt = $conn->prepare($deletePaymentQuery);
    if ($stmt) {
        $stmt->bind_param('i', $paymentId);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: hal_pembayaran.php');
    exit();
}

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
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=acute" />
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
                <a href="hal_stats.php">
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
            <h1>Statistika</h1>
            <div class="analyse">
                <div class="status">
                    <h3>Total Orders</h3>
                    <h1><?= $totalOrders ?></h1>
                </div>
                <div class="status">
                    <h3>Total Income</h3>
                    <h1>Rp<?= $formattedTotalIncome ?></h1>
                </div>
            </div>

            <div class="chart-container">
        <canvas id="orderIncomeChart"></canvas>
            </div>

            <div class="navigation-buttons">
                <a href="?start_date=<?= date('Y-m-d', strtotime($startDate . ' -7 days')) ?>">Previous 7 Days</a>
                <a href="?start_date=<?= date('Y-m-d', strtotime($startDate . ' +7 days')) ?>">Next 7 Days</a>
            </div>

            
    </main>
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

    <script src="../js/admin.js"></script>
    
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
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                barThickness: 30, // Make bars a bit thicker
                borderRadius: 8,  // Rounded corners for the bars
                hoverBackgroundColor: 'rgba(54, 162, 235, 0.8)', // Hover effect
                hoverBorderColor: 'rgba(54, 162, 235, 1)',
            },
            {
                label: 'Total Income (Rp)',
                data: <?= json_encode($incomeValues) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                barThickness: 30, 
                borderRadius: 8, 
                hoverBackgroundColor: 'rgba(75, 192, 192, 0.8)',
                hoverBorderColor: 'rgba(75, 192, 192, 1)',
            }
        ]
    },
    options: {
        responsive: true,
        animation: {
            duration: 1500, // Duration of the animation
            easing: 'easeInOutBack', // Animation easing effect
            onComplete: function () {
                var chartInstance = this.chart,
                    ctx = chartInstance.ctx;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                this.data.datasets.forEach(function (dataset, i) {
                    var meta = chartInstance.getDatasetMeta(i);
                    meta.data.forEach(function (bar, index) {
                        var value = dataset.data[index];
                        ctx.fillStyle = 'white';
                        ctx.fillText(value, bar.x, bar.y - 5); // Add labels on top of bars
                    });
                });
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 14, // Adjust the font size for better readability
                    },
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 14,
                    },
                    callback: function(value) {
                        return value.toLocaleString(); // Format numbers for better readability
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return tooltipItem.raw.toLocaleString(); // Add comma separation in tooltips
                    }
                }
            }
        }
    }
});

   </script>

    <style>
        .chart-container {
    width: 80%;
    margin: auto;
    padding: 40px 0; /* Add padding to create space around the chart */
    background-color: #f7f7f7; /* Light background */
    border-radius: 12px; /* Rounded corners for the container */
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); /* Subtle shadow for 3D effect */
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
    background-color: #0056b3; /* Darker color on hover */
    transform: scale(1.1); /* Slightly enlarge the button on hover */
}

body {
    background-color: #fff; /* Light background for the page */
    font-family: 'Arial', sans-serif; /* Choose a modern font */
}

    </style>
</body>
</html>
