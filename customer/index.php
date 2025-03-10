<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('customer_session');
session_set_cookie_params([
    'lifetime' => 3600,
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
                    // NEW: Update last_login timestamp
                    $update_last_login_sql = "UPDATE customers SET last_login = NOW() WHERE id = ?";
                    $stmt_last_login = $conn->prepare($update_last_login_sql);
                    $stmt_last_login->bind_param("i", $user['id']);
                    $stmt_last_login->execute();

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
                            
                            error_log("Remember token error: " . $e->getMessage());
                        }
                    }

                    header("Location: dashboard.php");
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
    
</head>
<body>
    <div class="wrapper">
    
        <form action="" method="POST" novalidate>
        <div class="login-logo">
            <i class="fas fa-lock"></i>
        </div>
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
                <a href="forgotpassword.php">Forgot password?</a>
            </div>
            <button type="submit">Log In</button>
            <div class="register">
                <p>Don't have an account? <a href="http://localhost:8000/customer/registration.html">Register</a></p>
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