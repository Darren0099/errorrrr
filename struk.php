<?php
session_start();
require 'koneksi.php'; // Hubungkan ke database

// Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// Ambil ID pembayaran dari URL
$id_pembayaran = isset($_GET['id_pembayaran']) ? intval($_GET['id_pembayaran']) : 0;

if ($id_pembayaran <= 0) {
    echo "ID pembayaran tidak valid.";
    exit();
}

// Ambil data pembayaran
$query = "SELECT p.total_pembayaran, p.metode_pembayaran, u.nama_user, o.produk_id 
          FROM tblpembayaran p
          JOIN tbluser u ON p.id_user = u.id_user
          JOIN tblpemesanan o ON p.id_user = o.id_user
          WHERE p.id_pembayaran = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $id_pembayaran);
    $stmt->execute();
    $stmt->bind_result($total_pembayaran, $metode_pembayaran, $nama_user, $produk_id);
    if (!$stmt->fetch()) {
        echo "Pembayaran tidak ditemukan.";
        exit();
    }
    $stmt->close();
} else {
    echo "Terjadi kesalahan saat mengambil data pembayaran.";
    exit();
}

// Tampilkan struk
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #f9f9f9;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .container h1 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        .details {
            margin-bottom: 15px;
            font-size: 18px;
            text-align: left;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Struk Pembayaran</h1>
        <div class="details">
            <p><strong>Nama:</strong> <?= htmlspecialchars($nama_user) ?></p>
            <p><strong>Produk:</strong> <?= htmlspecialchars($produk_id) ?></p>
            <p><strong>Metode Pembayaran:</strong> <?= htmlspecialchars($metode_pembayaran) ?></p>
            <p><strong>Total Pembayaran:</strong> Rp <?= number_format($total_pembayaran, 0, ',', '.') ?></p>
        </div>
        <p>Terima kasih atas pembayaran Anda!</p>
        <a href="status.php" class="button">Kembali ke Status</a>
    </div>
</body>

</html>