<?php
session_start();

// Hapus keranjang di sesi
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
    echo "Keranjang berhasil dihapus.";
} else {
    echo "Tidak ada keranjang untuk dihapus.";
}
?>
