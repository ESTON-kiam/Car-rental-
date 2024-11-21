<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';
require 'vendor/autoload.php';

$host = 'localhost';
$db_name = 'car_rental_management';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$messageType = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT * FROM drivers WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $driver = $result->fetch_assoc();
        $token_expiration = $driver['token_expiration'];

        if (strtotime($token_expiration) > time()) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $newPassword = $_POST['new_password'];
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                $updateStmt = $conn->prepare("UPDATE drivers SET password = ?, reset_token = NULL, token_expiration = NULL WHERE reset_token = ?");
                $updateStmt->bind_param("ss", $hashedPassword, $token);
                $updateStmt->execute();

                $message = "Your password has been reset successfully. You can now log in with your new password.";
                $messageType = "success";

                $updateStmt->close();
            }
        } else {
            $message = "Invalid or expired token. Please request a new password reset link.";
            $messageType = "error";
        }
    } else {
        $message = "No user found for this token.";
        $messageType = "error";
    }

    $stmt->close();
} else {
    $message = "No token provided.";
    $messageType = "error";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/driverdash.css">
    <script src="assets/js/driverdash.js"></script>
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f4f4f9; }
        .container { width: 400px; padding: 20px; background: #ffffff; border-radius: 8px; box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); }
        h2 { color: #333; text-align: center; }
        input[type="password"], button { width: 100%; padding: 12px; margin: 8px 0; border: none; border-radius: 4px; box-sizing: border-box; }
        input[type="password"] { background-color: #f1f1f1; color: #333; }
        button { background-color: #4CAF50; color: white; font-weight: bold; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>
<div class="container">
    <h2>Reset Your Password</h2>
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="password" name="new_password" placeholder="Enter your new password" required>
        <button type="submit">Reset Password</button>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
