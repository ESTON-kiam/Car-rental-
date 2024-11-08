<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('customer_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

$error_message = '';
$error_type = '';

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "car_rental_management";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . htmlspecialchars($conn->connect_error));
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $error_type = 'warning';
            $error_message = "Please enter a valid email address.";
        } else {
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $password = $_POST['password'];

            $sql = "SELECT * FROM customers WHERE email = ?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Database error: " . htmlspecialchars($conn->error));
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    $_SESSION['customer_id'] = $user['id'];
                    $_SESSION['customer_email'] = $user['email'];
                    $_SESSION['customer_name'] = $user['full_name']; 

                    if (isset($_POST['remember'])) {
                        try {
                            $token = bin2hex(random_bytes(16));
                            $expiry = date('Y-m-d H:i:s', time() + (86400 * 30)); 
                            $token_hash = password_hash($token, PASSWORD_DEFAULT);

                            $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token_hash, expiry) VALUES (?, ?, ?)");
                            if (!$stmt) {
                                throw new Exception("Error preparing remember token statement");
                            }
                            $stmt->bind_param("iss", $user['id'], $token_hash, $expiry);
                            $stmt->execute();

                            setcookie('remember_token', $token, time() + (86400 * 30), "/", "", false, true); 
                        } catch (Exception $e) {
                            // Log the error but don't show to user
                            error_log("Remember token error: " . $e->getMessage());
                        }
                    }

                    header("Location: customer_dashboard.php");
                    exit();
                } else {
                    $error_type = 'warning';
                    $error_message = "Invalid email or password.";
                }
            } else {
                $error_type = 'warning';
                $error_message = "Invalid email or password.";
            }
        }
    }
} catch (Exception $e) {
    $error_type = 'error';
    $error_message = "A system error occurred. Please try again later.";
    error_log("Login error: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
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

        /* Improve form field validation styling */
        .input-field input:invalid {
            border-color: #dc3545;
        }

        .input-field input:valid {
            border-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <form action="" method="POST" novalidate>
            <h2>Customer Login</h2>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-<?php echo $error_type; ?> fade-out">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <div class="input-field">
                <input type="email" id="email" name="email" required 
                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                <label for="email">Enter Your Email</label>
            </div>
            <div class="input-field">
                <input type="password" id="password" name="password" required 
                       minlength="8">
                <label for="password">Enter your password</label>
            </div>
            <div class="forget">
                <label for="remember">
                    <input type="checkbox" id="remember" name="remember">
                    <p>Remember me</p>
                </label>
                <a href="forgot_password.php">Forgot password?</a>
            </div>
            <button type="submit">Log In</button>
            <div class="register">
                <p>Don't have an account? <a href="customer_registration.html">Register</a></p>
            </div>
        </form>
    </div>

    <script>
        // Remove alert messages after animation completes
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 5000);
            });

            // Client-side email validation
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('input', function() {
                if (emailInput.validity.typeMismatch) {
                    emailInput.setCustomValidity('Please enter a valid email address');
                } else {
                    emailInput.setCustomValidity('');
                }
            });
        });
    </script>
</body>
</html>