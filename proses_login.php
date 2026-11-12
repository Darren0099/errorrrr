<?php
session_start();
include 'koneksi.php';

$username = $_POST['username'];
$password = sha1($_POST['password']);

$login = mysqli_query($conn, "SELECT * FROM tbluser WHERE nama_user='$username' AND password_user='$password'");
$cek = mysqli_num_rows($login);

if ($cek > 0) {
    $data = mysqli_fetch_assoc($login);

    if ($data['level'] == "admin") {
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['username'] = $username;
        $_SESSION['level'] = "admin";
        $_SESSION['login_message'] = "Anda telah login sebagai Admin, $username";
        header("location:hal_admin.php");
        exit();
    } else if ($data['level'] == "pelanggan") {
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['username'] = $username;
        $_SESSION['level'] = "pelanggan";
        $_SESSION['login_message'] = "Anda telah login sebagai Pelanggan, $username";
        header("location:DapurUmmikyanaPalembang.php");
        exit();
    } else {
        header("location:login.php?pesan=gagal");
        exit();
    }
} else {
    $_SESSION['error_message'] = "Username atau Password salah!";
    header("location:login.php");
    exit();
}
?>
