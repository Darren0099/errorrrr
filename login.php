<?php
session_start();

if (isset($_GET['pesan']) && $_GET['pesan'] == "gagal") {
    echo "<div class='alert'>Username dan Password tidak sesuai!</div>";
}

if (isset($_SESSION['register_success'])) {
    echo "<div style='color: green;'>" . $_SESSION['register_success'] . "</div>";
    unset($_SESSION['register_success']);
}

if (isset($_SESSION['register_error'])) {
    echo "<div style='color: red;'>" . $_SESSION['register_error'] . "</div>";
    unset($_SESSION['register_error']);
}

if (isset($_SESSION['logout_message'])) {
    echo '<div class="logout-message" style="background-color: red; color: white; padding: 10px; margin: 10px 0;">' . $_SESSION['logout_message'] . '</div>';
    unset($_SESSION['logout_message']);
}

if (isset($_SESSION['message'])) {
    echo '<div class="notif-message">'.$_SESSION['message'].'</div>';
    unset($_SESSION['message']);
}


if (isset($_SESSION['login_message'])) {
    echo "<div style='background-color: #4CAF50; color: white; padding: 15px; margin: 20px 0; border-radius: 8px; font-family: Arial, sans-serif; font-size: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); display: flex; align-items: center; gap: 10px;'>
            <i style=\"font-size: 20px;\" class=\"fa fa-check-circle\"></i> " . $_SESSION['login_message'] . "</div>";
    unset($_SESSION['login_message']);
}

if (isset($_SESSION['error_message'])) {
    echo "<div style='background-color: #f44336; color: white; padding: 15px; margin: 20px 0; border-radius: 8px; font-family: Arial, sans-serif; font-size: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
            <i style=\"font-size: 20px;\" class=\"fa fa-times-circle\"></i> " . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
    <title>Login Website</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background: url(/img/bgac.png);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 100vh;
        }

        .alert {
            background-color: #f8d7da;
            color: #842029;
            padding: 15px;
            border: 1px solid #f5c2c7;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            width: 90%;
            max-width: 768px;
        }

                .notif-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-family: Arial, sans-serif;
        }

        .container {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 480px;
        }

        .container p {
            font-size: 14px;
            line-height: 20px;
            letter-spacing: 0.3px;
            margin: 20px 0;
        }

        .container span {
            font-size: 12px;
        }

        .container a {
            color: #333;
            font-size: 13px;
            text-decoration: none;
            margin: 15px 0 10px;
        }

        .logout-message {
    background-color: red;
    color: white;
    padding: 15px;
    font-size: 16px;
    text-align: center;
    border-radius: 5px;
    margin: 20px auto;
    max-width: 500px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}

        .container button {
            background-color: #2d5ca8;
            color: #fff;
            font-size: 12px;
            padding: 10px 45px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 10px;
            cursor: pointer;
        }

        .container button.hidden {
            background-color: transparent;
            border-color: #fff;
        }

        .container form {
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            height: 100%;
        }

        .container input {
            background-color: #eee;
            border: none;
            margin: 8px 0;
            padding: 10px 15px;
            font-size: 13px;
            border-radius: 8px;
            width: 100%;
            outline: none;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .container.active .sign-in {
            transform: translateX(100%);
        }

        .sign-up {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .container.active .sign-up {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: move 0.6s;
        }

        @keyframes move {
            0%,
            49.99% {
                opacity: 0;
                z-index: 1;
            }

            50%,
            100% {
                opacity: 1;
                z-index: 5;
            }
        }

        .social-icons {
            margin: 20px 0;
        }

        .social-icons a {
            border: 1px solid #ccc;
            border-radius: 20%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 3px;
            width: 40px;
            height: 40px;
        }

        .toggle-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: all 0.6s ease-in-out;
            border-radius: 150px 0 0 100px;
            z-index: 1000;
        }

        .container.active .toggle-container {
            transform: translateX(-100%);
            border-radius: 0 150px 100px 0;
        }

        .toggle {
            background-color: #2d5ca8;
            background: linear-gradient(to right, #5c7cc0, #2d5ca8);
            color: #fff;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .container.active .toggle {
            transform: translateX(50%);
        }

        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 30px;
            text-align: center;
            top: 0;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .toggle-left {
            transform: translateX(-200%);
        }

        .container.active .toggle-left {
            transform: translateX(0);
        }

        .toggle-right {
            right: 0;
            transform: translateX(0);
        }

        .container.active .toggle-right {
            transform: translateX(200%);
        }
    </style>
</head>

<body>
    <div class="container" id="container">
        <div class="form-container sign-up">
            <form action="register.php" method="POST">
                <h1>Create Account</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
                <span>or use your email for registration</span>
                <input type="text" placeholder="Name" id="nama_user" name="nama_user" required>
                <input type="email" placeholder="Email" id="email_user" name="email_user" required>
                <input type="password" placeholder="Password" id="password_user" name="password_user" required>
                <button type="submit">Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in">
            <form action="proses_login.php" method="POST">
                <h1>Sign In</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
                <span>or use your email password</span>
                <input type="text" placeholder="username" name="username">
                <input type="password" placeholder="Password" name="password">
                <a href="#">Forget Your Password?</a>
                <button><a>Sign In</a></button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Enter your personal details to use all of site features</p>
                    <button class="hidden" value="LOGIN" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hi Teman</h1>
                    <p>Register with your personal details to use all of site features</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script src="login.js"></script>
<script>
    setTimeout(() => {
        const notif = document.getElementById('notifMessage');
        if (notif) {
            notif.style.transition = "opacity 0.5s ease";
            notif.style.opacity = "0"; 
            setTimeout(() => notif.remove(), 500);
        }
    }, 5000); 
    setTimeout(() => {
        const logoutMessage = document.querySelector('.logout-message');
        if (logoutMessage) {
            logoutMessage.style.transition = "opacity 0.5s ease";
            logoutMessage.style.opacity = "0"; 
            setTimeout(() => logoutMessage.remove(), 500); 
        }
    }, 2000); 
</script>


</body>

</html>
