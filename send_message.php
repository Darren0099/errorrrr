<?php
session_start();

// Pastikan pengguna telah login, jika tidak redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

$sender_id = $_SESSION['user_id'];  // ID pengirim, sesuai dengan session
$receiver_id = 1; // Misalkan admin selalu memiliki ID 1

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];
    $message_type = 'text';

    // Mengirimkan pesan berupa teks
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text, message_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $message_type);
        $stmt->execute();
        $stmt->close();
    }

    // Mengirimkan pesan berupa file
    if (!empty($_FILES['file']['name'])) {
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_path = 'uploads/' . basename($file_name);
        move_uploaded_file($file_tmp, $file_path);

        $message_type = 'file';

        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text, message_type, file_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $sender_id, $receiver_id, $message, $message_type, $file_path);
        $stmt->execute();
        $stmt->close();
    }

    echo "Pesan berhasil dikirim!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat System</title>
</head>
<body>
    <h2>Kirim Pesan</h2>
    <form action="send_message.php" method="POST" enctype="multipart/form-data">
        <textarea name="message" placeholder="Tulis pesan..." rows="4" cols="50"></textarea><br><br>
        <label for="file">Kirim File:</label>
        <input type="file" name="file"><br><br>
        <button type="submit">Kirim Pesan</button>
    </form>
</body>
</html>
