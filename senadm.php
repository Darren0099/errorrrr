<?php
session_start();
include('koneksi.php'); // Menghubungkan dengan database

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php"); // Arahkan ke halaman login jika belum login
    exit();
}

// Ambil ID pesan dari URL
if (isset($_GET['id_pesan'])) {
    $id_pesan = $_GET['id_pesan'];

    // Ambil data pesan yang belum dibalas
    $query = "SELECT * FROM tblpesan WHERE id_pesan = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_pesan);
    $stmt->execute();
    $result = $stmt->get_result();
    $pesan = $result->fetch_assoc();

    // Jika pesan ditemukan
    if ($pesan) {
        // Proses balasan pesan
        if (isset($_POST['balasan'])) {
            $balasan = $_POST['balasan'];

            // Insert balasan ke tblbalasan
            $query = "INSERT INTO tblbalasan (id_pesan, balasan) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $id_pesan, $balasan);
            $stmt->execute();

            // Update status pesan menjadi 'dibalas'
            $query = "UPDATE tblpesan SET status = 'dibalas' WHERE id_pesan = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id_pesan);
            $stmt->execute();

            echo "<script>alert('Balasan berhasil dikirim.'); window.location.href = 'admin_dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Pesan tidak ditemukan.'); window.location.href = 'admin_dashboard.php';</script>";
    }
} else {
    echo "<script>alert('ID pesan tidak ditemukan.'); window.location.href = 'admin_dashboard.php';</script>";
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balas Pesan</title>
</head>
<body>
    <h2>Balas Pesan</h2>

    <p><strong>Pesan dari Pengguna:</strong></p>
    <p><?php echo htmlspecialchars($pesan['pesan']); ?></p>

    <form action="" method="POST">
        <label for="balasan">Balasan Anda:</label><br>
        <textarea name="balasan" id="balasan" rows="4" cols="50" required></textarea><br><br>
        <button type="submit">Kirim Balasan</button>
    </form>

    <br><a href="admin_dashboard.php">Kembali ke Dashboard</a>
</body>
</html>
