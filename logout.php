<?php
session_start();
session_destroy(); // Hapus semua sesi
session_start(); // Mulai sesi baru
$_SESSION['logout_message'] = "Anda berhasil logout.";
header("Location: login.php"); // Redirect ke login.php
exit();
?>
