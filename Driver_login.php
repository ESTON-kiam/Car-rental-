<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('driver_session');
session_start();

$error_message = '';
$error_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "car_rental_management";

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        $error_type = 'error';
        $error_message = "Connection failed: " . htmlspecialchars($conn->connect_error);
    } else {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];

        $checkUserQuery = "SELECT driver_id, password FROM drivers WHERE email = '$email'";
        $result = $conn->query($checkUserQuery);

        if ($result === false) {
            $error_type = 'error';
            $error_message = "An error occurred while processing your request. Please try again later.";
        } else if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashedPassword = $row['password'];

            if (password_verify($password, $hashedPassword)) {
                $_SESSION['driver_id'] = $row['driver_id'];
                $_SESSION['driver_email'] = $email;
                header("Location: driverdashboard.php");
                exit();
            } else {
                $error_type = 'warning';
                $error_message = "Incorrect password. Please try again.";
            }
        } else {
            $error_type = 'warning';
            $error_message = "No account found with that email. Please check your email.";
        }

        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Login</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
        .alert {
            padding: 15px;
            margin: 15px 0;
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
        }

        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .fade-out {
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <form action="" method="POST">
            <h2>Driver Login</h2>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-<?php echo $error_type; ?> fade-out">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <div class="input-field">
                <input type="email" id="email" name="email" required>
                <label for="email">Enter Your Email</label>
            </div>
            <div class="input-field">
                <input type="password" id="password" name="password" required>
                <label for="password">Enter your password</label>
            </div>
            <div class="forget">
                <label for="remember">
                    <input type="checkbox" id="remember">
                    <p>Remember me</p>
                </label>
                <a href="driverforgotpassword.php">Forgot password?</a>
            </div>
            <button type="submit">Log In</button>
            <div class="register">
                <p>Return to Home? <a href="index.html">Return To Home Page</a></p>
            </div>
        </form>
    </div>

    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 5000);
            });
        });
    </script>
</body>
</html>