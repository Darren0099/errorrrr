<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_user = isset($_POST['nama_user']) ? $_POST['nama_user'] : null;
    $email_user = isset($_POST['email_user']) ? $_POST['email_user'] : null;
    $password_user = isset($_POST['password_user']) ? sha1($_POST['password_user']) : null;

    // Validasi input
    if (!$nama_user || !$email_user || !$password_user) {
        $_SESSION['message'] = "Semua data wajib diisi!";
        header("Location: login.php");
        exit();
    }

    $cek_email = mysqli_query($conn, "SELECT * FROM tbluser WHERE email_user = '$email_user'");
    if (mysqli_num_rows($cek_email) > 0) {
        $_SESSION['message'] = "Email sudah digunakan. Silakan gunakan email lain.";
        header("Location: login.php");
        exit();
    }

    $query = "INSERT INTO tbluser (nama_user, email_user, password_user, level) VALUES ('$nama_user', '$email_user', '$password_user', 'pelanggan')";
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Registrasi berhasil!";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['message'] = "Gagal menyimpan data: " . mysqli_error($conn);
        header("Location: login.php");
        exit();
    }
}
?>
