<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifikasi login, misalnya menggunakan hard-coded username dan password
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Misalnya, admin memiliki username 'admin' dan password 'admin123'
    if ($username == 'admin' && $password == 'admin123') {
        $_SESSION['user_id'] = 1;  // Admin ID
        header("Location: send_message.php");
        exit;
    } elseif ($username == 'pelanggan' && $password == 'pelanggan123') {
        $_SESSION['user_id'] = 2;  // Pelanggan ID
        header("Location: send_message.php");
        exit;
    } else {
        echo "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="login.php" method="POST">
        Username: <input type="text" name="username" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
