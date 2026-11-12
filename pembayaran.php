<?php
session_start();
include 'koneksi.php';
require_once 'midtrans/Midtrans.php'; // Pastikan path Midtrans Anda benar

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-bhl-TL7DRl83z4pA4g4AlX3e';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Periksa jika ada keranjang belanja
if (empty($_SESSION['cart'])) {
    echo "<script>alert('Keranjang belanja kosong!'); window.location='keranjang.php';</script>";
    exit;
}

// Mengambil data dari keranjang dan menghitung total
$total_pembayaran = 0;
$items = [];
foreach ($_SESSION['cart'] as $item) {
    $produk_name = $item['name'];
    $query_check = $conn->prepare("SELECT menu_id, menu_price FROM tblmenu WHERE menu_name = ?");
    $query_check->bind_param("s", $produk_name);
    $query_check->execute();
    $query_check->bind_result($menu_id, $menu_price);
    $query_check->fetch();
    $query_check->close();

    if ($menu_id) {
        $items[] = [
            'id' => $menu_id,
            'price' => $menu_price,
            'quantity' => $item['quantity'],
            'name' => $produk_name
        ];
        $total_pembayaran += $menu_price * $item['quantity'];
    } else {
        echo "<script>alert('Produk tidak valid: $produk_name'); window.location='keranjang.php';</script>";
        exit;
    }
}

$order_id = '';
$snapToken = '';
$id_pemesanan = null;

// Jika form dikirim (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'clear_cart') {
            // Hapus keranjang
            unset($_SESSION['cart']);
            echo "Keranjang berhasil dihapus.";
            exit;
        }

        if ($action === 'update_status') {
            $order_id = $_POST['order_id'];
            $status = $_POST['status'];

            // Update status pembayaran di database
            $update_pembayaran = $conn->prepare("UPDATE tblpembayaran SET status_pembayaran = ? WHERE order_id = ?");
            $update_pembayaran->bind_param("ss", $status, $order_id);
            $update_pembayaran->execute();
            $update_pembayaran->close();

            // Hapus keranjang setelah status diperbarui
            unset($_SESSION['cart']);

            echo "Status pembayaran diperbarui ke $status.";
            exit;
        }
    }

    $nama_pemesan = mysqli_real_escape_string($conn, $_POST['nama_pemesan']);
    $alamat_pemesan = mysqli_real_escape_string($conn, $_POST['alamat_pemesan']);
    $telepon_pemesan = mysqli_real_escape_string($conn, $_POST['telepon_pemesan']);
    $email_pemesan = mysqli_real_escape_string($conn, $_POST['email_pemesan']);

    $order_id = 'ORDER-' . uniqid();

    $customer_details = [
        'first_name' => $nama_pemesan,
        'phone' => $telepon_pemesan,
        'email' => $email_pemesan,
        'address' => $alamat_pemesan,
    ];

    $params = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => $total_pembayaran,
        ],
        'customer_details' => $customer_details,
        'item_details' => $items,
    ];

    try {
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        // Simpan ke tblpemesanan
        $produk_id_str = implode(',', array_column($items, 'id'));
        $query_pemesanan = $conn->prepare("INSERT INTO tblpemesanan (order_id, nama_pemesan, alamat_pemesan, telepon_pemesan, email_pemesan, produk_id, jumlah_pemesanan, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $query_pemesanan->bind_param("ssssssii", $order_id, $nama_pemesan, $alamat_pemesan, $telepon_pemesan, $email_pemesan, $produk_id_str, $total_pembayaran, $_SESSION['id_user']);
        $query_pemesanan->execute();
        $id_pemesanan = $conn->insert_id;  // Dapatkan id_pemesanan yang baru dimasukkan
        $query_pemesanan->close();

        // Simpan ke tblpembayaran dengan id_pemesanan yang benar
        $status_pembayaran = 'pending';
        $metode_pembayaran = 'credit card';
        $query_pembayaran = $conn->prepare("INSERT INTO tblpembayaran (order_id, id_pemesanan, id_user, total_pembayaran, metode_pembayaran, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?)");
        $query_pembayaran->bind_param("siisss", $order_id, $id_pemesanan, $_SESSION['id_user'], $total_pembayaran, $metode_pembayaran, $status_pembayaran);
        $query_pembayaran->execute();
        $query_pembayaran->close();
    } catch (Exception $e) {
        echo "<script>alert('Terjadi kesalahan saat mendapatkan Snap Token: " . $e->getMessage() . "');</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment and Invoice</title>
    <link rel="stylesheet" href="css/pembayaran.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-qxyFzX4t8SlH6axJ"></script>
</head>
<body>
<div class="invoice-card">
            <h3>Invoice</h3>
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <p><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?> - Rp. <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></p>
            <?php endforeach; ?>
            <p><strong>Total Pembayaran: Rp. <?= number_format($total_pembayaran, 0, ',', '.') ?></strong></p>
            <hr>
            <div class="icon-text">
                <span class="material-icons-sharp">receipt_long</span>
                <p>Invoice ID: <?= htmlspecialchars($order_id ?: 'Belum Tersedia') ?></p>
            </div>
            <div class="icon-text">
                <span class="material-icons-sharp">
                    calendar_today
                </span>
                <p>Tanggal Pemesanan:</p>
            </div>
            <h3><?php echo date('d F, Y'); ?></h3>

            <div class="icon-text" style="padding-top: 65px;">
                <span class="material-icons-sharp">
                    forum
                </span>
                <p>Customer Support:</p>
            </div>
            <h3>Terima Kasih sudah Membeli!</h3>
        </div>
        <div class="payment-section">
            
    <form id="payment-form" method="POST">
        <label for="nama_pemesan">Name:</label>
        <input type="text" name="nama_pemesan" id="nama_pemesan" required>
        <label for="email_pemesan">Email:</label>
        <input type="email" name="email_pemesan" id="email_pemesan" required>
        <label for="alamat_pemesan">Address:</label>
        <input type="text" name="alamat_pemesan" id="alamat_pemesan" required>
        <label for="telepon_pemesan">Phone:</label>
        <input type="text" name="telepon_pemesan" id="telepon_pemesan" required>
        <button type="submit" id="pay-button">Bayar Sekarang</button>
    </form>
        </div>

       
<script type="text/javascript">document.addEventListener('DOMContentLoaded', function () {
    const snapToken = '<?= $snapToken ?>';

    if (snapToken) {
        snap.pay(snapToken, {
            onSuccess: function (result) {
                alert('Pembayaran Berhasil!');
                updatePaymentStatus('completed', result);
            },
            onPending: function (result) {
                alert('Menunggu Pembayaran.');
                updatePaymentStatus('pending', result);
            },
            onError: function (result) {
                alert('Pembayaran Gagal!');
                updatePaymentStatus('failed', result);
            },
            onClose: function () {
                alert('Anda menutup pop-up pembayaran.');
                updatePaymentStatus('pending', null);
            }
        });
    }
});

function updatePaymentStatus(status, result) {
    const orderId = '<?= $order_id ?>';
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            clearCart();
        }
    };
    xhr.send(`action=update_status&order_id=${orderId}&status=${status}`);
}

function clearCart() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            window.location.href = 'status.php';
        }
    };
    xhr.send('action=clear_cart');
}

</script>

</body>
</html>
