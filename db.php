<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function () {
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
                updatePaymentStatus('pending', null); // Treat onClose as 'pending'.
            }
        });
    }
});

// Fungsi untuk mengupdate status pembayaran dan menghapus keranjang
function updatePaymentStatus(status, result) {
    const orderId = '<?= $order_id ?>';

    // Kirim status pembayaran ke server menggunakan AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '', true); // Mengirim ke halaman PHP yang sama
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log('Status pembayaran diperbarui ke:', status);

            // Hapus keranjang setelah status diperbarui
            clearCart();
        }
    };
    const data = action=update_status&order_id=${orderId}&status=${status};
    xhr.send(data);
}

// Fungsi untuk menghapus keranjang setelah pembayaran selesai
function clearCart() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '', true); // Mengirim ke halaman PHP yang sama
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log('Keranjang berhasil dihapus.');
            // Arahkan ke status.php setelah menghapus keranjang
            window.location.href = 'status.php';
        }
    };
    const data = 'action=clear_cart';
    xhr.send(data);
}
</script>
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'update_status') {
            $order_id = $_POST['order_id'];
            $status = $_POST['status'];

            $update_pembayaran = $conn->prepare("UPDATE tblpembayaran SET status_pembayaran = ? WHERE order_id = ?");
            $update_pembayaran->bind_param("ss", $status, $order_id);
            $update_pembayaran->execute();
            $update_pembayaran->close();

            echo "Status pembayaran diperbarui ke $status.";
        }

        if ($action === 'clear_cart') {
            unset($_SESSION['cart']);
            echo "Keranjang berhasil dihapus.";
        }

        exit;
    }
}

// Mengambil data dari keranjang dan menghitung total
$total_pembayaran = 0;
$items = [];
foreach ($_SESSION['cart'] as $item) {
    $produk_name = $item['name'];
    $query_check = $conn->prepare("SELECT menu_id, menu_price FROM tblmenu WHERE menu_name = ?");

    if (!$query_check) {
        die("Error prepare statement: " . $conn->error);
    }

    $query_check->bind_param("s", $produk_name);
    if (!$query_check->execute()) {
        die("Error execute statement: " . $query_check->error);
    }

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

// Jika form dikirim (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        if (!$query_pemesanan) {
            die("Error prepare tblpemesanan: " . $conn->error);
        }

        $query_pemesanan->bind_param("ssssssii", $order_id, $nama_pemesan, $alamat_pemesan, $telepon_pemesan, $email_pemesan, $produk_id_str, $total_pembayaran, $_SESSION['id_user']);
        if (!$query_pemesanan->execute()) {
            die("Error execute tblpemesanan: " . $query_pemesanan->error);
        }
        $query_pemesanan->close();

        // Simpan ke tblpembayaran dengan status ongoing
        $status_pembayaran = 'ongoing';
        $metode_pembayaran = 'credit card';
        $query_pembayaran = $conn->prepare("INSERT INTO tblpembayaran (order_id, id_user, total_pembayaran, metode_pembayaran, status_pembayaran) VALUES (?, ?, ?, ?, ?)");

        if (!$query_pembayaran) {
            die("Error prepare tblpembayaran: " . $conn->error);
        }

        $query_pembayaran->bind_param("sisss", $order_id, $_SESSION['id_user'], $total_pembayaran, $metode_pembayaran, $status_pembayaran);
        if (!$query_pembayaran->execute()) {
            die("Error execute tblpembayaran: " . $query_pembayaran->error);
        }
        $query_pembayaran->close();

    } catch (Exception $e) {
        echo "<script>alert('Terjadi kesalahan saat mendapatkan Snap Token: " . $e->getMessage() . "');</script>";
    }
}

// Menangani status pembayaran
if (isset($_GET['status']) && isset($_GET['order_id'])) {
    $status_pembayaran = $_GET['status'];
    $order_id = $_GET['order_id'];

    $update_pembayaran = $conn->prepare("UPDATE tblpembayaran SET status_pembayaran = ? WHERE order_id = ?");
    if (!$update_pembayaran) {
        die("Error prepare update pembayaran: " . $conn->error);
    }

    $update_pembayaran->bind_param("ss", $status_pembayaran, $order_id);
    if (!$update_pembayaran->execute()) {
        die("Error execute update pembayaran: " . $update_pembayaran->error);
    }
    $update_pembayaran->close();

    // Hapus keranjang
    unset($_SESSION['cart']);
    echo "<script>window.location='status.php';</script>";
}
?>