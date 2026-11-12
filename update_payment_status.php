<?php
session_start();
include 'koneksi.php';

if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Perbarui status pembayaran di tblpembayaran
    $query_update = $conn->prepare("UPDATE tblpembayaran SET status_pembayaran = ? WHERE order_id = ?");
    if ($query_update) {
        $query_update->bind_param("ss", $status, $order_id);
        if ($query_update->execute()) {
            echo "Status pembayaran diperbarui menjadi $status.";
        } else {
            echo "Gagal memperbarui status pembayaran.";
        }
        $query_update->close();
    } else {
        echo "Kesalahan query: " . $conn->error;
    }
} else {
    echo "Parameter tidak valid.";
}
?>
