<?php
session_start();
include 'koneksi.php';

// Periksa apakah user telah login
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

if (empty($_SESSION['cart'])) {
    echo "<script>alert('Keranjang belanja kosong!'); window.location='keranjang.php';</script>";
    exit;
}

$id_user = $_SESSION['id_user']; 
$produk_id = [];
foreach ($_SESSION['cart'] as $item) {
    $produk_name = $item['name'];
    $query_check = $conn->prepare("SELECT menu_id FROM tblmenu WHERE menu_name = ?");
    $query_check->bind_param("s", $produk_name);
    $query_check->execute();
    $query_check->bind_result($menu_id);
    $query_check->fetch();
    $query_check->close();

    if ($menu_id) {
        $produk_id[] = $menu_id;
    } else {
        echo "<script>alert('Produk tidak valid: $produk_name'); window.location='keranjang.php';</script>";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pemesan = mysqli_real_escape_string($conn, $_POST['nama_pemesan']);
    $alamat_pemesan = mysqli_real_escape_string($conn, $_POST['alamat_pemesan']);
    $telepon_pemesan = mysqli_real_escape_string($conn, $_POST['telepon_pemesan']);
    $metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);

    $total_pembayaran = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_pembayaran += $item['price'] * $item['quantity'];
    }
    $produk_id_str = implode(', ', $produk_id);
    $query_pemesanan = $conn->prepare("INSERT INTO tblpemesanan (nama_pemesan, alamat_pemesan, telepon_pemesan, produk_id, jumlah_pemesanan, id_user) 
    VALUES (?, ?, ?, ?, ?, ?)");
    $query_pemesanan->bind_param("sssisi", $nama_pemesan, $alamat_pemesan, $telepon_pemesan, $produk_id_str, $total_pembayaran, $id_user);

    if ($query_pemesanan->execute()) {
        $id_pemesanan = $conn->insert_id; 
        $query_pembayaran = $conn->prepare("INSERT INTO tblpembayaran (id_user, total_pembayaran, metode_pembayaran, id_pemesanan) 
                                            VALUES (?, ?, ?, ?)");
        $query_pembayaran->bind_param("idss", $id_user, $total_pembayaran, $metode_pembayaran, $id_pemesanan);

        if ($query_pembayaran->execute()) {
            unset($_SESSION['cart']);
            echo "<script>alert('Pembayaran berhasil!'); window.location='status.php';</script>";
            exit;
        } else {
            error_log("Error pembayaran: " . $query_pembayaran->error);
            echo "<script>alert('Gagal memproses pembayaran!'); window.location='pembayaran.php';</script>";
            exit;
        }
    } else {
        error_log("Error pemesanan: " . $query_pemesanan->error);
        echo "<script>alert('Gagal menyimpan data pemesanan!'); window.location='pembayaran.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Formulir Pembayaran</h1>
        <form action="pembayaran.php" method="post">
            <label for="nama_pemesan">Nama Lengkap:</label>
            <input type="text" name="nama_pemesan" id="nama_pemesan" required>

            <label for="alamat_pemesan">Alamat Pengiriman:</label>
            <textarea name="alamat_pemesan" id="alamat_pemesan" required></textarea>

            <label for="telepon_pemesan">Nomor HP:</label>
            <input type="text" name="telepon_pemesan" id="telepon_pemesan" required>

            <label for="metode_pembayaran">Metode Pembayaran:</label>
            <select name="metode_pembayaran" id="metode_pembayaran" required>
                <option value="Transfer Bank">Transfer Bank</option>
                <option value="COD">COD</option>
                <option value="E-Wallet">E-Wallet</option>
            </select>

            <h3>Ringkasan Pesanan</h3>
            <ul>
                <?php
                $total_pembayaran = 0; 
                foreach ($_SESSION['cart'] as $item):
                    $total_pembayaran += $item['price'] * $item['quantity'];
                ?>
                    <li><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?> - Rp. <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Total Pembayaran: Rp. <?= number_format($total_pembayaran, 0, ',', '.') ?></strong></p>

            <button type="submit">Bayar</button>
        </form>
    </div>
</body>
</html>
