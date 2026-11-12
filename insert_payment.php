<?php
session_start();
include 'koneksi.php';

// Periksa apakah status pembayaran sukses
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    // Ambil data dari session
    $order_id = $_SESSION['order_id'];
    $total_pembayaran = $_SESSION['total_pembayaran'];
    $nama_pemesan = $_SESSION['nama_pemesan'];
    $alamat_pemesan = $_SESSION['alamat_pemesan'];
    $telepon_pemesan = $_SESSION['telepon_pemesan'];
    $email_pemesan = $_SESSION['email_pemesan'];

    // Simpan data pesanan ke database
    $query = $conn->prepare("INSERT INTO tblpemesanan (order_id, total_pembayaran, nama_pemesan, alamat_pemesan, telepon_pemesan, email_pemesan, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $status_pembayaran = 'success'; // Status pembayaran berhasil
    $query->bind_param("sdsssss", $order_id, $total_pembayaran, $nama_pemesan, $alamat_pemesan, $telepon_pemesan, $email_pemesan, $status_pembayaran);

    if ($query->execute()) {
        // Setelah berhasil insert, hapus semua data di keranjang
        unset($_SESSION['cart']);
        unset($_SESSION['order_id']);
        unset($_SESSION['total_pembayaran']);
        unset($_SESSION['nama_pemesan']);
        unset($_SESSION['alamat_pemesan']);
        unset($_SESSION['telepon_pemesan']);
        unset($_SESSION['email_pemesan']);

        // Redirect ke halaman status
        header("Location: status.php?status=success");
        exit();
    } else {
        echo "Error: " . $query->error;
    }
} else {
    // Jika status bukan 'success', redirect kembali ke halaman pembayaran
    header("Location: pembayaran.php");
    exit();
}
?>
