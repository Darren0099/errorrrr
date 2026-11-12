<?php
// Ambil data dari request POST
$data = json_decode(file_get_contents('php://input'), true);

$order_id = $data['order_id'];
$metode_pembayaran = $data['metode_pembayaran'];
$total_pembayaran = $data['total_pembayaran'];
$status_pembayaran = $data['status_pembayaran'];

// Simpan data pemesanan ke tabel tblpemesanan
include 'koneksi.php';

// Mengambil ID pengguna dari session
$id_user = $_SESSION['user_id'];  // Pastikan session 'user_id' sudah ada

// Simpan pemesanan
$query_pemesanan = "INSERT INTO tblpemesanan (nama_pemesan, alamat_pemesan, telepon_pemesan, produk_id, jumlah_pemesanan, tanggal_pemesanan, id_user) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
$stmt_pemesanan = $conn->prepare($query_pemesanan);
$stmt_pemesanan->bind_param('sssiii', $_POST['nama_pemesan'], $_POST['alamat_pemesan'], $_POST['telepon_pemesan'], $produk_id, $jumlah_pemesanan, $id_user);
$stmt_pemesanan->execute();
$id_pemesanan = $stmt_pemesanan->insert_id;  // Ambil ID pemesanan yang baru dimasukkan

// Simpan data pembayaran ke tabel tblpembayaran
$query_pembayaran = "INSERT INTO tblpembayaran (id_user, total_pembayaran, metode_pembayaran, status_pembayaran, id_pemesanan) VALUES (?, ?, ?, ?, ?)";
$stmt_pembayaran = $conn->prepare($query_pembayaran);
$stmt_pembayaran->bind_param('idssi', $id_user, $total_pembayaran, $metode_pembayaran, $status_pembayaran, $id_pemesanan);
$stmt_pembayaran->execute();

// Jika berhasil, beri response JSON
echo json_encode(['status' => 'success']);
?>
