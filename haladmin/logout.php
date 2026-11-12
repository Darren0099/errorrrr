<?php
session_start();
session_destroy();
session_start();
$_SESSION['logout_message'] = "Anda berhasil logout.";
echo "Redirecting to login.php..."; 
header("Location: index.php");
exit();
?>
