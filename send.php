<?php
session_start();
include 'koneksi.php';

// Periksa login
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

$id_user = $_SESSION['id_user'];
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);
    $file_bukti = '';

    // Proses unggah file jika ada
    if (!empty($_FILES['file_bukti']['name'])) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES['file_bukti']['name']);
        $file_bukti = $target_dir . time() . "_" . $file_name;

        if (move_uploaded_file($_FILES['file_bukti']['tmp_name'], $file_bukti)) {
            // File berhasil diunggah
        } else {
            $error_message = 'Gagal mengunggah file.';
        }
    }

    // Masukkan pesan ke database
    if (!$error_message) {
        $query = $conn->prepare("INSERT INTO tblpesan (id_user, pesan, file_bukti, status, created_at) VALUES (?, ?, ?, 'terkirim', NOW())");
        $query->bind_param("iss", $id_user, $pesan, $file_bukti);

        if ($query->execute()) {
            $success_message = 'Pesan berhasil dikirim!';
        } else {
            $error_message = 'Gagal mengirim pesan: ' . $query->error;
        }
    }
}

// Ambil pesan dan balasan
$query = $conn->prepare("SELECT p.id_pesan, p.pesan, p.file_bukti, p.status, p.created_at, b.balasan, b.created_at AS balasan_created_at 
                        FROM tblpesan p 
                        LEFT JOIN tblbalasan b ON p.id_pesan = b.id_pesan 
                        WHERE p.id_user = ? ORDER BY p.created_at DESC");
$query->bind_param("i", $id_user);
$query->execute();
$result = $query->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Pesan</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Kirim Pesan ke Admin</h2>

        <?php if ($error_message): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form action="send.php" method="post" enctype="multipart/form-data">
            <div>
                <label for="pesan">Pesan:</label>
                <textarea name="pesan" id="pesan" rows="4" required></textarea>
            </div>
            <div>
                <label for="file_bukti">Unggah Bukti Pembayaran (opsional):</label>
                <input type="file" name="file_bukti" id="file_bukti" accept="image/*">
            </div>
            <button type="submit">Kirim</button>
        </form>

        <h2>Pesan Anda</h2>
        <?php if ($messages): ?>
            <ul>
                <?php foreach ($messages as $message): ?>
                    <li>
                        <p><strong>Pesan:</strong> <?php echo htmlspecialchars($message['pesan']); ?></p>
                        <?php if ($message['file_bukti']): ?>
                            <p><strong>Bukti:</strong> <a href="<?php echo $message['file_bukti']; ?>" target="_blank">Lihat File</a></p>
                        <?php endif; ?>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($message['status']); ?></p>
                        <?php if ($message['balasan']): ?>
                            <p><strong>Balasan Admin:</strong> <?php echo htmlspecialchars($message['balasan']); ?></p>
                            <p><small>Dikirim pada: <?php echo htmlspecialchars($message['balasan_created_at']); ?></small></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Belum ada pesan.</p>
        <?php endif; ?>
    </div>
</body>
</html>
