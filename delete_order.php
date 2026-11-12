<?php
session_start();
require 'koneksi.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];

    if ($orderId) {
        // Delete order from tblpemesanan
        $deleteQuery = "DELETE FROM tblpemesanan WHERE id_pemesanan = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param('i', $orderId);

        if ($stmt->execute()) {
            header('Location: admin_dashboard.php'); 
        } else {
            echo "Error deleting order.";
        }

        $stmt->close();
    }
}
?>
