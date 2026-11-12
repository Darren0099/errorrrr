<?php
session_start();

// Mulai session jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Cek apakah ada input pencarian
$search_query = isset($_GET['search']) ? strtolower($_GET['search']) : '';

// Jika pencarian kosong, tampilkan semua item
if ($search_query) {
    // Filter item yang ada di keranjang berdasarkan pencarian
    $filtered_cart = array_filter($_SESSION['cart'], function($item) use ($search_query) {
        return strpos(strtolower($item['name']), $search_query) !== false;
    });
} else {
    // Jika pencarian kosong, tampilkan semua item
    $filtered_cart = $_SESSION['cart'];
}



// Tambahkan item ke keranjang
if (isset($_POST['add_to_cart'])) {
    $menu_id = $_POST['menu_id'];
    $menu_name = $_POST['menu_name'];
    $menu_price = $_POST['menu_price'];

    // Periksa apakah menu sudah ada di keranjang
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $menu_id) {
            $item['quantity'] += 1; // Tambahkan jumlah jika sudah ada
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $menu_id,
            'name' => $menu_name,
            'price' => $menu_price,
            'quantity' => 1,
            'image' => 'path/to/image.jpg', // Tambahkan gambar produk
        ];
    }

    header('Location: keranjang.php');
    exit;
}

// Update jumlah item langsung
if (isset($_POST['update_quantity'])) {
    $menu_id = $_POST['menu_id'];
    $action = $_POST['update_quantity']; // Ambil nilai dari tombol

    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $menu_id) {
            if ($action === 'increase') {
                $item['quantity'] += 1;
            } elseif ($action === 'decrease' && $item['quantity'] > 1) {
                $item['quantity'] -= 1;
            }
            break;
        }
    }

    header('Location: keranjang.php');
    exit;
}


// Hapus item dari keranjang
if (isset($_GET['remove'])) {
    $menu_id = $_GET['remove'];
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) use ($menu_id) {
        return $item['id'] != $menu_id;
    });

    header('Location: keranjang.php');
    exit;
}

// Hapus semua item dari keranjang
if (isset($_GET['clear_cart'])) {
    $_SESSION['cart'] = [];
    header('Location: keranjang.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="stylesheet" href="css/order.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body>
    <div class="container">
    <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon"><i class='bx bxs-lemon'></i></span>
                        <span class="title">MyOrder</span>
                    </a>
                </li>
                <li>
                    <a href="status.php">
                        <span class="icon"><i class='bx bx-home-alt-2'></i></span>
                        <span class="title">Status Order</span>
                    </a>
                </li>
                <li>
                    <a href="keranjang.php" class="active">
                          <div class="icon"><i class='bx bx-cart'></i></div>
                        <span class="title">Keranjang</span>
                    </a>
                </li>
                <li>
                    <a href="DapurUmmikyanaPalembang.php">
                        <span class="icon"><i class='bx bx-log-in'></i></span>
                        <span class="title">Lanjut Belanja</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="main">
        <div class="topbar">
                <div class="toggle">
                    <i class='bx bx-menu'></i>
                </div>
                <div class="search">
    <form action="keranjang.php" method="get">
        <label>
            <input type="text" name="search" placeholder="Search here" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <i class='bx bx-search'></i>
        </label>
    </form>
                </div>


                <div class="user">
                    <img src="img/user.png" alt="user foto">
                </div>
            </div>

            <div class="details">
                <div class="recentOrders2">
                    <div class="cardHeader">
                        <h2>Recent Orders</h2>
                        <a href="#" class="btn">View All</a>
                    </div>
                    <?php if (empty($filtered_cart)): ?>
    <p>Keranjang Anda kosong atau tidak ada item yang sesuai dengan pencarian.</p>
<?php else: ?>
    <?php foreach ($filtered_cart as $item): ?>
        <div class="cart-item-details">
            <div>
                <h4><?= htmlspecialchars($item['name']) ?></h4>
                <div class="cart-item-price">
                    <p>Harga: Rp. <?= number_format($item['price'], 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
        <div class="item-controls">
            <form action="keranjang.php" method="post" class="cart-item-quantity">
                <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                <button type="submit" name="update_quantity" value="decrease">-</button>
                <span><?= $item['quantity'] ?></span>
                <button type="submit" name="update_quantity" value="increase">+</button>
                <a href="keranjang.php?remove=<?= $item['id'] ?>" class="cart-item-remove">x</a>
            </form>
        </div>
    <?php endforeach; ?>
                    <?php endif; ?>


                </div>

                <div class="recentCustomers">
                    <div class="cardHeader">
                        <h2>Recent Customers</h2>
                    </div>
                   
                <h2>Ringkasan Pesanan</h2>
                <ul class="details">
                    <?php 
                    $total = 0;
                    foreach ($_SESSION['cart'] as $item):
                        $total += $item['price'] * $item['quantity'];
                    ?>
                        <li><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="grand-">
                <p>Subtotal: Rp. <?= number_format($total, 0, ',', '.') ?></p>
                </div>
                <a href="pembayaran.php" class="checkout-button">Lanjut ke Pembayaran</a>
                
                </div>

            </div>
        </div>
    </div>
   
    <script>
        // menu toggle
        let toggle = document.querySelector('.toggle');
        let navigation = document.querySelector('.navigation');
        let main = document.querySelector('.main');

        toggle.onclick = function () {
            navigation.classList.toggle('active');
            main.classList.toggle('active');
        }

        // add hovered class in selected list item
        let list = document.querySelectorAll('.navigation li');
        function activeLink() {
            list.forEach((item) =>
                item.classList.remove('hovered'));
            this.classList.add('hovered');
        }
        list.forEach((item) =>
            item.addEventListener('mouseover', activeLink));
    </script>
    <style>
        
        .cart-layout {
            display: flex;
            gap: 20px;
        }
        .cart-left {
            flex: 2;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .item-details {
            display: flex;
            align-items: center;
            flex: 1;
        }
        .item-details h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        .item-details p {
            margin: 5px 0 0;
            color: #777;
            font-size: 14px;
        }
        .item-controls {
            text-align: right;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }
        .quantity-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-form button {
            width: 30px;
            height: 30px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }
        .quantity-form button:hover {
            background-color: #0056b3;
        }
        .quantity-form span {
            font-size: 16px;
            font-weight: bold;
        }
        .remove-button {
            color: red;
            text-decoration: none;
            font-size: 18px;
        }
        .cart-right {
            flex: 1;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .cart-right h2 {
            margin: 0 0 15px;
            font-size: 20px;
        }
        .summary-list {
            list-style: none;
            padding: 0;
            margin: 0 0 15px;
        }
        .summary-list li {
            margin-bottom: 10px;
            color: #333;
        }
        .checkout-button {
            display: block;
            text-align: center;
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .checkout-button:hover {
            background-color: #218838;
        }

        .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px;
        }
        .cart-item img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
        }

        .cart-item-details {
            flex: 1;
            margin-left: 15px;
        }

        .cart-item-details h4 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .cart-item-details p {
            margin: 5px 0 0;
            color: #777;
            font-size: 14px;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-item-quantity button {
            width: 30px;
            height: 30px;
            background-color: #f3f3f3;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            color: #333;
        }

        .cart-item-quantity button:hover {
            background-color: #e0e0e0;
        }

        .cart-item-quantity span {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .cart-item-price {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .cart-item-remove {
            font-size: 18px;
            color: #ff4d4f;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
        }

        .cart-item-remove:hover {
            color: #d9363e;
        }

        
    .summary {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 20px;
      width: 300px;
      box-sizing: border-box;
    }

    .summary h2 {
      font-size: 24px;
      font-weight: bold;
      margin: 0 0 20px;
      text-transform: lowercase;
    }

    .summary .coupon-code {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
    }

    .summary input[type="text"] {
      width: 60%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .summary a {
      color: #6f2dff;
      text-decoration: none;
      font-weight: bold;
    }

    .summary a:hover {
      text-decoration: underline;
    }

    .summary .details {
      margin: 10px 0;
    }

    .summary .details span {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 14px;
    }

    .summary .details span.total {
      font-weight: bold;
    }

    .summary .grand-total {
      font-size: 18px;
      font-weight: bold;
      margin: 20px 0;
      display: flex;
      justify-content: space-between;
    }

    .summary button {
      width: 100%;
      background: #6f2dff;
      color: #fff;
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
    }

    .summary button:hover {
      background: #5c23d1;
    }

    .details .recentOrders2 {
    position: relative;
    display: grid;
    width: 800px;
    background: var(--white);
    padding: 20px;
    box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
    border-radius: 20px;}

    </style>
</body>
</html>
