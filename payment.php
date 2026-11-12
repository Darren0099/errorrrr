<?php

namespace Midtrans;

require_once 'midtrans/Midtrans.php'; // Sesuaikan dengan path Midtrans Anda

// Set Your server key
Config::$serverKey = 'SB-Mid-server-bhl-TL7DRl83z4pA4g4AlX3e';
Config::$clientKey = 'SB-Mid-client-qxyFzX4t8SlH6axJ';

// Cek apakah ada data di keranjang
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $total_pembayaran = 0;
    $items = [];
    
    // Debugging: Menampilkan sesi keranjang
    echo "<pre>";
    print_r($_SESSION['cart']); // Cek apakah data keranjang ada
    echo "</pre>";
    
    // Mengambil data keranjang dan menghitung total
    foreach ($_SESSION['cart'] as $item) {
        $produk_name = $item['name'];
        // Asumsikan $conn adalah koneksi database
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

    // Tentukan transaksi
    $transaction_details = array(
        'order_id' => rand(),
        'gross_amount' => $total_pembayaran, // Total pembayaran yang dihitung
    );

    // Mengatur metode pembayaran
    $enable_payments = array('credit_card','cimb_clicks','mandiri_clickpay','echannel');

    // Menyusun data transaksi
    $transaction = array(
        'enabled_payments' => $enable_payments,
        'transaction_details' => $transaction_details,
        'item_details' => $items,
    );

    // Mendapatkan Snap Token
    $snap_token = '';
    try {
        $snap_token = Snap::getSnapToken($transaction);
    }
    catch (\Exception $e) {
        echo $e->getMessage();
    }

    echo "snapToken = ".$snap_token;

} else {
    echo "<script>alert('Keranjang Anda kosong.'); window.location='keranjang.php';</script>";
    exit;
}

?>
<!DOCTYPE html>
<html>
    <body>
        <h3>Detail Pembelian</h3>
        <ul>
            <?php foreach ($items as $item): ?>
                <li><?php echo $item['name']; ?> - x<?php echo $item['quantity']; ?> - Rp. <?php echo number_format($item['price'] * $item['quantity']); ?></li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Total Pembayaran: </strong>Rp. <?php echo number_format($total_pembayaran); ?></p>

        <button id="pay-button">Bayar!</button>
        <pre><div id="result-json">JSON result will appear here after payment:</div></pre>

        <!-- Script Midtrans -->
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?php echo Config::$clientKey; ?>"></script>
        <script type="text/javascript">
            document.getElementById('pay-button').onclick = function() {
                snap.pay('<?php echo $snap_token; ?>', {
                    onSuccess: function(result) {
                        document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                    },
                    onPending: function(result) {
                        document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                    },
                    onError: function(result) {
                        document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                    }
                });
            };
        </script>
    </body>
</html>
