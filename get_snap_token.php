<?php
require_once 'midtrans/Midtrans.php'; // Pastikan path benar

\Midtrans\Config::$serverKey = 'SB-Mid-server-bhl-TL7DRl83z4pA4g4AlX3e'; // Gunakan Server Key Anda
\Midtrans\Config::$isProduction = true; // Gunakan `true` untuk lingkungan produksi
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Ambil data POST dari fetch request
$data = json_decode(file_get_contents('php://input'), true);

$id_user = $data['id_user'];
$total_pembayaran = $data['total_pembayaran'];

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Dapatkan detail pengguna dari database (gunakan koneksi Anda)
include 'koneksi.php';
$query = $conn->prepare("SELECT id, nama_user, email FROM tbluser WHERE id = ?");
$query->bind_param('i', $id_user);
$query->execute();
$query->bind_result($id, $nama_user, $email);
$query->fetch();
$query->close();

// Periksa apakah data pengguna ditemukan
if (empty($nama_user)) {
    echo json_encode(['error' => 'Data pengguna tidak ditemukan.']);
    exit;
}

// Buat parameter transaksi
$params = array(
    'transaction_details' => array(
        'order_id' => uniqid('ORDER-'), // Buat order_id unik
        'gross_amount' => $total_pembayaran, // Total pembayaran
    ),
    'customer_details' => array(
        'first_name' => $nama_user,
        'last_name' => '', // Tidak ada nama belakang
        'email' => $email, // Email pengguna
        'phone' => '', // Kosongkan jika tidak diperlukan
    ),
);

try {
    // Dapatkan Snap Token dari Midtrans
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    echo json_encode(['snapToken' => $snapToken]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
