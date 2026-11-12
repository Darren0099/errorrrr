<?php
session_start();
require 'koneksi.php'; // Include database connection

// Handle search query
$searchTerm = isset($_POST['search_term']) ? $_POST['search_term'] : '';

// Retrieve users who ordered at least 3 times
$topUsersQuery = "
    SELECT u.nama_user, COUNT(*) as total_orders
    FROM tblpemesanan p
    JOIN tbluser u ON p.id_user = u.id_user
    GROUP BY u.id_user
    HAVING total_orders >= 3
";
$topUsersResult = $conn->query($topUsersQuery);

// Retrieve all users for display or based on search term
if ($searchTerm) {
    // Search for users by name
    $usersQuery = "SELECT * FROM tbluser WHERE nama_user LIKE ?";
    $stmt = $conn->prepare($usersQuery);
    $searchLike = '%' . $searchTerm . '%';
    $stmt->bind_param('s', $searchLike);
    $stmt->execute();
    $usersResult = $stmt->get_result();
} else {
    // If no search term, retrieve all users
    $usersQuery = "SELECT * FROM tbluser";
    $usersResult = $conn->query($usersQuery);
}

$totalUsersQuery = "SELECT COUNT(*) as total_users FROM tbluser";
$totalUsersResult = $conn->query($totalUsersQuery);
$totalUsers = $totalUsersResult ? $totalUsersResult->fetch_assoc()['total_users'] : 0;

// Handle delete user request
if (isset($_POST['delete_user'])) {
    $userId = intval($_POST['user_id']);

    $deleteUserQuery = "DELETE FROM tbluser WHERE id_user = ?";
    $stmt = $conn->prepare($deleteUserQuery);
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: hal_shipment.php');
    exit();
}

// Retrieve the admin's name from the database
$adminId = $_SESSION['id_user'] ?? null; // Assuming id_user is stored in the session
$adminName = '';

if ($adminId) {
    $adminQuery = "SELECT nama_user FROM tbluser WHERE id_user = ?";
    $stmt = $conn->prepare($adminQuery);
    if ($stmt) {
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $stmt->bind_result($adminName);
        $stmt->fetch();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=acute" />
    <link rel="stylesheet" href="../css/adm.css">
    <title>Dashboard User</title>
</head>
<body>
<div class="container">
<aside>
                    <div class="toggle">
                        <div class="logo">
                            <img src="../asset/logo1.gif">
                            <h2>Admini<span class="danger">strator</span></h2>
                        </div>
                        <div class="close" id="close-btn">
                            <span class="material-icons-sharp">
                                close
                            </span>
                        </div>
                    </div>

                    <div class="sidebar">
                        <a href="../hal_admin.php">
                            <span class="material-icons-sharp">
                                dashboard
                            </span>
                            <h3>Dashboard</h3>
                        </a>
                        <a href="hal_user.php">
                            <span class="material-icons-sharp">
                                person_outline
                            </span>
                            <h3>Users</h3>
                        </a>
                        <a href="hal_pembayaran.php">
                            <span class="material-icons-sharp">
                                receipt_long
                            </span>
                            <h3>Pembayaran</h3>
                        </a>
                        <a href="hal_shipment.php">
                            <span class="material-symbols-outlined" class="active">
                                acute
                            </span>
                            <h3>Shipment</h3>
                        </a>
                        <a href="hal_stats.php">
                            <span class="material-icons-sharp">
                                insights
                            </span>
                            <h3>Analytics</h3>
                        </a>
                        <a href="../logout.php">
                            <span class="material-icons-sharp">
                                logout
                            </span>
                            <h3>Logout</h3>
                        </a>
                    </div>
        </aside>

    <main>
        <h1>Pengguna</h1>

        <div class="analyse">
            <div class="status">
                <h3>User Top Order</h3>
                <ul>
                    <?php if ($topUsersResult && $topUsersResult->num_rows > 0): ?>
                        <?php while ($user = $topUsersResult->fetch_assoc()): ?>
                            <h2><?= htmlspecialchars($user['nama_user']) ?> (<?= $user['total_orders'] ?> pesanan)</h2>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li>Tidak ada user dengan pesanan ≥3 kali.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="visits">
                    <div class="status">
                        <div class="info">
                            <h3>Total Users</h3>
                            <h1><?= $totalUsers ?></h1>
                        </div>
                    </div>
            </div>
            <div class="visits">
                <form method="POST" action="hal_user.php">
                    <input type="text" name="search_term" placeholder="Cari Nama Pengguna" value="<?= htmlspecialchars($searchTerm) ?>">
                    <button type="submit" style="background-color: #45a049; ">Cari</button>
                </form>
            </div>
            
          
        </div>

        <div class="recent-orders">
            <h2>Data Pengguna</h2>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Level</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($usersResult && $usersResult->num_rows > 0): ?>
                    <?php while ($user = $usersResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['id_user'] ?></td>
                            <td><?= htmlspecialchars($user['nama_user']) ?></td>
                            <td><?= htmlspecialchars($user['email_user']) ?></td>
                            <td><?= htmlspecialchars($user['level']) ?></td>
                            <td>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="user_id" value="<?= $user['id_user'] ?>">
                                    <button type="submit" name="delete_user" onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Tidak ada data pengguna.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div class="right-section">
            <div class="nav">
                <button id="menu-btn">
                    <span class="material-icons-sharp">
                        menu
                    </span>
                </button>
                <div class="dark-mode">
                    <span class="material-icons-sharp active">
                        light_mode
                    </span>
                    <span class="material-icons-sharp">
                        dark_mode
                    </span>
                </div>

                <div class="profile">
                    <div class="admin-header">
                         <p>Hi, <b><span class="admin-name"><?= htmlspecialchars($adminName) ?></span></b> </p>
                         <p>Admin</p>
                    </div>
                   
                </div>

            </div>
            <!-- End of Nav -->

            <div class="user-profile">
                <div class="logo">
                    <img src="../asset/user.gif">
                    <h2>Administrator</h2>
                    <p>Disini kamu bisa melihat dan mengedit para pengguna yang sudah mendaftar</p>
                </div>
            </div>

    </div>
</div>

<style>
        
        form button {
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        
        form button[name="search_term"] {
            background-color: #4caf50; 
        }
        
        form button[name="search_term"]:hover {
            background-color: #45a049; 
            transform: scale(1.05); 
        }
        
        form button[name="delete_user"] {
            background-color: #f44336; 
        }
        
        form button[name="delete_user"]:hover {
            background-color: #d32f2f; 
            transform: scale(1.05); 
        }
        
        form {
            display: inline-block;
            margin: 0;
        }
            </style>
             <script src="../js/admin.js"></script>
</body>
</html>
