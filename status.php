<?php
session_start();
include 'koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Ambil tanggal filter dari parameter GET
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : null;

// Hitung jumlah order berdasarkan filter tanggal
$query_total_orders = "
    SELECT COUNT(*) AS total_orders
    FROM tblpemesanan
    WHERE id_user = ?
";
if ($filter_date) {
    $query_total_orders .= " AND DATE(tanggal_pemesanan) = ?";
}
$stmt_total_orders = mysqli_prepare($conn, $query_total_orders);
if ($filter_date) {
    mysqli_stmt_bind_param($stmt_total_orders, "is", $id_user, $filter_date);
} else {
    mysqli_stmt_bind_param($stmt_total_orders, "i", $id_user);
}
mysqli_stmt_execute($stmt_total_orders);
$result_total_orders = mysqli_stmt_get_result($stmt_total_orders);
$total_orders = $result_total_orders ? mysqli_fetch_assoc($result_total_orders)['total_orders'] : 0;

// Hitung jumlah pesanan sedang diantar
$query_delivery = "
    SELECT COUNT(*) AS total_delivery
    FROM tblpemesanan p
    LEFT JOIN tblstatuspemesanan s ON p.id_pemesanan = s.id_pemesanan
    WHERE p.id_user = ? AND s.status_pemesanan = 'diantar'
";
if ($filter_date) {
    $query_delivery .= " AND DATE(p.tanggal_pemesanan) = ?";
}
$stmt_delivery = mysqli_prepare($conn, $query_delivery);
if ($filter_date) {
    mysqli_stmt_bind_param($stmt_delivery, "is", $id_user, $filter_date);
} else {
    mysqli_stmt_bind_param($stmt_delivery, "i", $id_user);
}
mysqli_stmt_execute($stmt_delivery);
$result_delivery = mysqli_stmt_get_result($stmt_delivery);
$total_delivery = $result_delivery ? mysqli_fetch_assoc($result_delivery)['total_delivery'] : 0;

// Hitung jumlah pesanan yang telah selesai
$query_completed = "
    SELECT COUNT(*) AS total_completed
    FROM tblpemesanan p
    LEFT JOIN tblstatuspemesanan s ON p.id_pemesanan = s.id_pemesanan
    WHERE p.id_user = ? AND s.status_pemesanan = 'selesai'
";
if ($filter_date) {
    $query_completed .= " AND DATE(p.tanggal_pemesanan) = ?";
}
$stmt_completed = mysqli_prepare($conn, $query_completed);
if ($filter_date) {
    mysqli_stmt_bind_param($stmt_completed, "is", $id_user, $filter_date);
} else {
    mysqli_stmt_bind_param($stmt_completed, "i", $id_user);
}
mysqli_stmt_execute($stmt_completed);
$result_completed = mysqli_stmt_get_result($stmt_completed);
$total_completed = $result_completed ? mysqli_fetch_assoc($result_completed)['total_completed'] : 0;

// Query untuk mengambil data pesanan pengguna berdasarkan filter tanggal
$query_orders = "
    SELECT 
        m.menu_name, 
        p.jumlah_pemesanan, 
        p.alamat_pemesan, 
        p.tanggal_pemesanan,
        MAX(s.status_pemesanan) AS status_pemesanan,
        b.status_pembayaran
    FROM tblpemesanan p
    JOIN tblmenu m ON p.produk_id = m.menu_id
    LEFT JOIN tblstatuspemesanan s ON p.id_pemesanan = s.id_pemesanan
    LEFT JOIN tblpembayaran b ON p.id_pemesanan = b.id_pemesanan
    WHERE p.id_user = ?
";
if ($filter_date) {
    $query_orders .= " AND DATE(p.tanggal_pemesanan) = ?";
}
$query_orders .= " GROUP BY p.id_pemesanan, b.status_pembayaran ORDER BY p.tanggal_pemesanan DESC";

$stmt_orders = mysqli_prepare($conn, $query_orders);
if ($filter_date) {
    mysqli_stmt_bind_param($stmt_orders, "is", $id_user, $filter_date);
} else {
    mysqli_stmt_bind_param($stmt_orders, "i", $id_user);
}
mysqli_stmt_execute($stmt_orders);
$result_orders = mysqli_stmt_get_result($stmt_orders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/order.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li><a href="#"><span class="icon"><i class='bx bxs-lemon'></i></span><span class="title">MyOrder</span></a></li>
                <li><a href="#"><span class="icon"><i class='bx bx-home-alt-2'></i></span><span class="title">Status Order</span></a></li>
                <li><a href="keranjang.php"><span class="icon"><i class='bx bx-cart'></i></span><span class="title">Keranjang</span></a></li>
                <li><a href="send.php"><span class="icon"><i class='bx bx-cart'></i></span><span class="title">Chat</span></a></li>
                <li><a href="DapurUmmikyanaPalembang.php"><span class="icon"><i class='bx bx-log-in'></i></span><span class="title">Lanjut Belanja</span></a></li>
            </ul>
        </div>
        <div class="main">
            <div class="topbar">
                <div class="toggle"><i class='bx bx-menu'></i></div>
                <div class="search">
                    <label>
                        <input type="text" id="searchInput" placeholder="Search here" onkeyup="searchData()">
                        <i class='bx bx-search'></i>
                    </label>
                </div>
                <div class="user">
                    <img src="img/user.png" alt="user foto">
                </div>
            </div>
            <div class="cardBox">
                <div class="card">
                    <div>
                        <div class="numbers"><?= $total_orders ?></div>
                        <div class="cardName">Total Pesanan</div>
                    </div>
                    <div class="iconBox"><i class='bx bx-cart'></i></div>
                </div>
                <div class="card">
                    <div>
                        <div class="numbers"><?= $total_delivery ?></div>
                        <div class="cardName">Sedang Diantar</div>
                    </div>
                    <div class="iconBox"><i class='bx bx-truck'></i></div>
                </div>
                <div class="card">
                    <div>
                        <div class="numbers"><?= $total_completed ?></div>
                        <div class="cardName">Sudah Selesai</div>
                    </div>
                    <div class="iconBox"><i class='bx bx-check-double'></i></div>
                </div>
            </div>

            <div class="details">
                <div class="recentOrders" style="width: 1000px; min-height: 200px;">
                    <div class="cardHeader">
                        <h2>Recent Orders</h2>
                        <form method="GET" style="display: flex; align-items: center;">
                            <label for="filter_date" style="margin-right: 10px;">Filter Tanggal:</label>
                            <input type="date" name="filter_date" id="filter_date" value="<?= htmlspecialchars($filter_date) ?>">
                            <button type="submit" class="btn" style="margin-left: 10px;">Filter</button>
                        </form>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <td>Produk</td>
                                <td>Jumlah</td>
                                <td>Alamat</td>
                                <td>Tanggal</td>
                                <td>Status</td>
                                <td>Status Pembayaran</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result_orders) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_orders)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['menu_name']) ?></td>
                                        <td><?= htmlspecialchars($row['jumlah_pemesanan']) ?></td>
                                        <td><?= htmlspecialchars($row['alamat_pemesan']) ?></td>
                                        <td><?= htmlspecialchars($row['tanggal_pemesanan']) ?></td>
                                        <td>
                                            <?php 
                                                $status_pemesanan = htmlspecialchars($row['status_pemesanan']);
                                                echo '<span class="status" style="color: ' . ($status_pemesanan === 'selesai' ? 'green' : ($status_pemesanan === 'diantar' ? 'orange' : 'red')) . ';">' . $status_pemesanan . '</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $status_pembayaran = htmlspecialchars($row['status_pembayaran']);
                                                echo '<span class="status" style="color: ' . ($status_pembayaran === 'completed' ? 'green' : ($status_pembayaran === 'pending' ? 'orange' : 'red')) . ';">' . $status_pembayaran . '</span>';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let toggle = document.querySelector('.toggle');
        let navigation = document.querySelector('.navigation');
        let main = document.querySelector('.main');

        toggle.onclick = function () {
            navigation.classList.toggle('active');
            main.classList.toggle('active');
        }

        let list = document.querySelectorAll('.navigation li');
        function activeLink() {
            list.forEach((item) =>
                item.classList.remove('hovered'));
            this.classList.add('hovered');
        }
        list.forEach((item) =>
            item.addEventListener('mouseover', activeLink));

        function searchData() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase();
            let table = document.querySelector("table");
            let rows = table.querySelectorAll("tbody tr");

            rows.forEach(function(row) {
                let productNameCell = row.cells[0];
                let statusPemesananCell = row.cells[4];
                let statusPembayaranCell = row.cells[5];

                let productNameText = productNameCell.textContent || productNameCell.innerText;
                let statusPemesananText = statusPemesananCell.textContent || statusPemesananCell.innerText;
                let statusPembayaranText = statusPembayaranCell.textContent || statusPembayaranCell.innerText;

                if (productNameText.toLowerCase().indexOf(filter) > -1 || 
                    statusPemesananText.toLowerCase().indexOf(filter) > -1 || 
                    statusPembayaranText.toLowerCase().indexOf(filter) > -1) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>
</body>
</html>