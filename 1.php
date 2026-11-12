<?php
session_start();
include 'koneksi.php';

// Ambil data notifikasi terbaru
$query = "
    SELECT p.id_pembayaran, p.id_user, p.total_pembayaran, p.metode_pembayaran, p.tanggal_pembayaran, u.nama_user 
    FROM tblpembayaran p 
    JOIN tbluser u ON p.id_user = u.id_user 
    WHERE p.is_notified = 0
    ORDER BY p.tanggal_pembayaran DESC
";
$result = $conn->query($query);

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['id_pembayaran'])) {
        $id_pembayaran = $data['id_pembayaran'];
        $updateQuery = "UPDATE tblpembayaran SET is_notified = 1 WHERE id_pembayaran = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $id_pembayaran);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["status" => "success"]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Notifikasi Pembayaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .notification-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            flex-direction: column;
            gap: 10px;
            z-index: 1000;
        }

        .notification-popup {
            background: #1c1c1c;
            color: #fff;
            border-radius: 30px;
            padding: 15px 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 60px;
            height: 60px;
            transform-origin: center;
            animation: expand-close 0.8s ease forwards;
        }

        .notification-popup.expanding {
            animation: expand 0.8s ease forwards;
        }

        .notification-popup.collapsing {
            animation: expand-close 0.8s ease forwards;
        }

        .notification-popup .icon {
            background: #007bff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .notification-popup .icon img {
            width: 20px;
        }

        .notification-popup .text {
            opacity: 0;
            font-size: 14px;
            line-height: 1.5;
            margin-left: 10px;
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .notification-popup.expanding .text {
            opacity: 1;
            transition: opacity 0.3s ease 0.5s;
        }

        .notification-icon {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: #fff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .notification-icon img {
            width: 24px;
        }

        @keyframes expand {
            0% {
                width: 60px;
                height: 60px;
            }
            60% {
                width: 400px;
                height: 60px;
            }
            100% {
                width: 800px;
                height: 60px;
            }
        }

        @keyframes expand-close {
            0% {
                width: 800px;
                height: 60px;
            }
            60% {
                width: 400px;
                height: 60px;
            }
            100% {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <h1 class="text-center">Admin Dashboard</h1>

    <!-- Ikon Notifikasi -->
    <div class="notification-icon" onclick="toggleNotifications()">
        <img src="https://cdn-icons-png.flaticon.com/512/1827/1827504.png" alt="Notification Icon">
    </div>

    <!-- Kontainer Notifikasi -->
    <div class="notification-container" id="notification-container">
        <?php foreach ($notifications as $notif): ?>
        <div class="notification-popup collapsing" id="notif-<?php echo $notif['id_pembayaran']; ?>">
            <div class="icon" onclick="markAsRead(<?php echo $notif['id_pembayaran']; ?>)">
                <img src="https://cdn-icons-png.flaticon.com/512/709/709496.png" alt="Check Icon">
            </div>
            <div class="text">
                Pelanggan <strong><?php echo $notif['nama_user']; ?></strong> 
                (ID: <?php echo $notif['id_user']; ?>) 
                telah membayar Rp<?php echo number_format($notif['total_pembayaran'], 0, ',', '.'); ?> 
                menggunakan <strong><?php echo $notif['metode_pembayaran']; ?></strong> 
                pada <strong><?php echo $notif['tanggal_pembayaran']; ?></strong>.
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        let isOpen = false;

        function markAsRead(idPembayaran) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_pembayaran: idPembayaran })
            }).then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    const notificationElement = document.getElementById(`notif-${idPembayaran}`);
                    if (notificationElement) {
                        notificationElement.remove();
                    }
                }
            });
        }

        function toggleNotifications() {
            const container = document.getElementById('notification-container');
            isOpen = !isOpen;

            if (isOpen) {
                container.style.display = 'flex';
                const popups = container.querySelectorAll('.notification-popup');
                popups.forEach(popup => {
                    popup.classList.remove('collapsing');
                    popup.classList.add('expanding');
                });
            } else {
                const popups = container.querySelectorAll('.notification-popup');
                popups.forEach(popup => {
                    popup.classList.remove('expanding');
                    popup.classList.add('collapsing');
                });
                setTimeout(() => {
                    container.style.display = 'none';
                }, 800);
            }
        }
    </script>
</body>
</html>
