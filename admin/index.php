<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('admin_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

$error_message = '';
$error_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $servername = "localhost"; 
        $username = "root"; 
        $password = ""; 
        $dbname = "car_rental_management";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . htmlspecialchars($conn->connect_error));
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $error_type = 'warning';
            $error_message = "Please enter a valid email address.";
        } else {
            $email = $conn->real_escape_string(trim($_POST['email']));
            $password = trim($_POST['password']);

            
            $stmt = $conn->prepare("SELECT id, password FROM admins WHERE email_address = ?");
            if (!$stmt) {
                throw new Exception("Database error: " . htmlspecialchars($conn->error));
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    
                    $_SESSION['loggedin'] = true;
                    $_SESSION['email'] = $email;
                    $_SESSION['admin_id'] = $row['id']; 
                    $_SESSION['last_activity'] = time();

                   
                    if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                        $token = bin2hex(random_bytes(32));
                        $token_hash = password_hash($token, PASSWORD_DEFAULT);
                        
                        $stmt = $conn->prepare("UPDATE admins SET remember_token = ? WHERE email_address = ?");
                        $stmt->bind_param("ss", $token_hash, $email);
                        $stmt->execute();
                        
                        setcookie('admin_remember', $token, [
                            'expires' => time() + (30 * 24 * 60 * 60),
                            'path' => '/',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict'
                        ]);
                    }

                    
                    header("Location: http://localhost:8000/admin/dashboard.php");
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
    } catch (Exception $e) {
        $error_type = 'error';
        $error_message = "A system error occurred. Please try again later.";
        error_log("Admin Login Error: " . $e->getMessage());
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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
            animation: fadeIn 0.3s ease-in;
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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }

        .input-field {
            position: relative;
        }

        .input-field input:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        .input-field input:invalid {
            border-color: #dc3545;
        }

        .input-field input:valid {
            border-color: #28a745;
        }

        button[type="submit"] {
            transition: background-color 0.3s ease;
        }
        .register a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <form action="" method="POST" novalidate>
            <h2>Admin Login</h2>
            
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
                <p>Return to Home? <a href="http://localhost:8000/">Return To Home Page</a></p>
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

           
            const form = document.querySelector('form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            emailInput.addEventListener('input', function() {
                const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
                this.setCustomValidity(isValid ? '' : 'Please enter a valid email address');
            });

            passwordInput.addEventListener('input', function() {
                this.setCustomValidity(
                    this.value.length < 8 ? 'Password must be at least 8 characters long' : ''
                );
            });

           
            form.addEventListener('submit', function(e) {
                const submitButton = this.querySelector('button[type="submit"]');
                if (submitButton.disabled) {
                    e.preventDefault();
                    return;
                }
                submitButton.disabled = true;
                setTimeout(() => {
                    submitButton.disabled = false;
                }, 2000);
            });
        });
    </script>
</body>
</html>