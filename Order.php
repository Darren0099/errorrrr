<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="stylesheet" href="css/Dashboard.css">

    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

</head>
<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon"><i class='bx bxs-lemon' ></i></span>
                        <span class="title">leCOmon</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon"><i class='bx bx-home-alt-2' ></i></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon"><i class='bx bx-user' ></i></span>
                        <span class="title">Customers</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon"><i class='bx bx-message-square-dots'></i></span>
                        <span class="title">Message</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon"><i class='bx bx-help-circle' ></i></span>
                        <span class="title">Help</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon"><i class='bx bx-cog' ></i></span>
                        <span class="title">Settings</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon"><i class='bx bx-lock-alt' ></i></span>
                        <span class="title">Password</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon"><i class='bx bx-log-in' ></i></span>
                        <span class="title">Lanjut Belanja</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- main -->

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <i class='bx bx-menu'></i>
                </div>
                <!-- search -->
                <div class="search">
                    <label>
                        <input type="text" placeholder="Search here">
                        <i class='bx bx-search'></i>
                    </label>
                </div>
                <!-- userImg -->
                <div class="user">
                    <img src="img/user.png" alt="user foto">
                </div>
            </div>

            <!-- cards -->

            <div class="cardBox">
                <div class="card">
                    <div>
                        <div class="numbers">2,612</div>
                        <div class="cardName">Daily Views</div>
                    </div>
                    <div class="iconBox">
                        <i class='bx bx-check-double' ></i>
                    </div>
                </div>
                <div class="card">
                    <div>
                        <div class="numbers">84</div>
                        <div class="cardName">Sales</div>
                    </div>
                    <div class="iconBox">
                        <i class='bx bx-cart'></i>
                    </div>
                </div>
                <div class="card">
                    <div>
                        <div class="numbers">312</div>
                        <div class="cardName">Comments</div>
                    </div>
                    <div class="iconBox">
                        <i class='bx bx-chat' ></i>
                    </div>
                </div>
                <div class="card">
                    <div>
                        <div class="numbers">$9,821</div>
                        <div class="cardName">Income</div>
                    </div>
                    <div class="iconBox">
                        <i class='bx bx-wallet' ></i>
                    </div>
                </div>
            </div>

           

            <div class="details">
                 <!-- order details list -->
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Recent Orders</h2>
                        <a href="#" class="btn">View All</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <td>Name</td>
                                <td>Price</td>
                                <td>Payment</td>
                                <td>Status</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Apple iPhone 14 Pro Max</td>
                                <td>$1099</td>
                                <td>Paid</td>
                                <td><span class="status delivered">Delivered</span></td>
                            </tr>
                            <tr>
                                <td>Google Pixel 7 Pro</td>
                                <td>$899</td>
                                <td>Due</td>
                                <td><span class="status pending">Pending</span></td>
                            </tr>
                            <tr>
                                <td>Apple iPhone 13</td>
                                <td>$699</td>
                                <td>Paid</td>
                                <td><span class="status return">Return</span></td>
                            </tr>
                            <tr>
                                <td>Samsung Galaxy S22 Ultra</td>
                                <td>$896</td>
                                <td>Due</td>
                                <td><span class="status inprogress">In Progress</span></td>
                            </tr>
                            <tr>
                                <td>Apple iPhone 13 Mini</td>
                                <td>$599</td>
                                <td>Paid</td>
                                <td><span class="status delivered">Delivered</span></td>
                            </tr>
                            <tr>
                                <td>Google Pixel 6A</td>
                                <td>$342</td>
                                <td>Paid</td>
                                <td><span class="status return">Return</span></td>
                            </tr>
                            <tr>
                                <td>Samsung Galaxy Z Flip 4</td>
                                <td>$999</td>
                                <td>Due</td>
                                <td><span class="status inprogress">In Progress</span></td>
                            </tr>
                            <tr>
                                <td>Samsung Galaxy Z Fold 4</td>
                                <td>$1800</td>
                                <td>Paid</td>
                                <td><span class="status pending">Pending</span></td>
                            </tr>
                            <tr>
                                <td>OnePlus 10T</td>
                                <td>$450</td>
                                <td>Paid</td>
                                <td><span class="status pending">Pending</span></td>
                            </tr>
                            <tr>
                                <td>Asus Zenfone 9</td>
                                <td>$629</td>
                                <td>Due</td>
                                <td><span class="status inprogress">In Progress</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- new customers -->
                <div class="recentCustomers">
                    <div class="cardHeader">
                        <h2>Recent Customers</h2>
                    </div>
                    <table>
                        <tr>
                            <td width="60px"><div class="imgBx"><img src="img/1.png" alt="img1"></div></td>
                            <td><h4>Nancy<br><span>Florida</span></h4></td>
                            <td><i class='bx bxl-instagram'></i></td>
                            <td><i class='bx bxl-whatsapp' ></i></td>
                        </tr>
                        <tr>
                            <td width="60px"><div class="imgBx"><img src="img/2.png" alt="img2"></div></td>
                            <td><h4>Robert<br><span>Montana</span></h4></td>
                            <td><i class='bx bxl-instagram'></i></td>
                            <td><i class='bx bxl-whatsapp' ></i></td>
                        </tr>
                        <tr>
                            <td width="60px"><div class="imgBx"><img src="img/3.png" alt="img3"></div></td>
                            <td><h4>Thomas<br><span>Iowa</span></h4></td>
                            <td><i class='bx bxl-instagram'></i></td>
                            <td><i class='bx bxl-whatsapp' ></i></td>
                        </tr>
                        <tr>
                            <td width="60px"><div class="imgBx"><img src="img/4.png" alt="img4"></div></td>
                            <td><h4>Patricia<br><span>Texas</span></h4></td>
                            <td><i class='bx bxl-instagram'></i></td>
                            <td><i class='bx bxl-whatsapp' ></i></td>
                        </tr>
                        <tr>
                            <td width="60px"><div class="imgBx"><img src="img/5.png" alt="img5"></div></td>
                            <td><h4>John<br><span>Oklahoma</span></h4></td>
                            <td><i class='bx bxl-instagram'></i></td>
                            <td><i class='bx bxl-whatsapp' ></i></td>
                        </tr>
                        <tr>
                            <td width="60px"><div class="imgBx"><img src="img/6.png" alt="img6"></div></td>
                            <td><h4>William<br><span>Virginia</span></h4></td>
                            <td><i class='bx bxl-instagram'></i></td>
                            <td><i class='bx bxl-whatsapp' ></i></td>
                        </tr>
                        <tr>
                            <td width="60px"><div class="imgBx"><img src="img/7.png" alt="img7"></div></td>
                            <td><h4>Susan<br><span>Florida</span></h4></td>
                            <td><i class='bx bxl-instagram'></i></td>
                            <td><i class='bx bxl-whatsapp' ></i></td>
                        </tr>
                        <tr>
                            <td width="60px"><div class="imgBx"><img src="img/8.png" alt="img8"></div></td>
                            <td><h4>Daniel<br><span>New York</span></h4></td>
                            <td><i class='bx bxl-instagram'></i></td>
                            <td><i class='bx bxl-whatsapp' ></i></td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
    </div>
    


    <script>
        // menu toggle //

        let toggle = document.querySelector('.toggle');
        let navigation = document.querySelector('.navigation');
        let main = document.querySelector('.main');

        toggle.onclick = function(){
            navigation.classList.toggle('active');
            main.classList.toggle('active');
        }


        // add hovered class in selected list item //

        let list = document.querySelectorAll('.navigation li');
        function activeLink(){
            list.forEach((item) =>
            item.classList.remove('hovered'));
            this.classList.add('hovered')
        }
            list.forEach((item) =>
            item.addEventListener('mouseover',activeLink));
    </script>
</body>
</html>