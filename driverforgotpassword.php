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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $result = $conn->query("SELECT * FROM drivers WHERE email='$email'");

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(32)); 
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $conn->query("UPDATE drivers SET reset_token='$token', token_expiration='$expiry' WHERE email='$email'");

        $mail = new PHPMailer;
        $mail->isSMTP();                                           
        $mail->Host = 'smtp.gmail.com';                   
        $mail->SMTPAuth = true;                                
        $mail->Username = 'engestonbrandon@gmail.com';        
        $mail->Password = 'dsth izzm npjl qebi';               
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   
        $mail->Port = 587;                                     

        $mail->setFrom('no-reply@gmail.com', 'Online Car Rental');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';

        $resetLink = "http://localhost:8000/driverresetpassword.php?token=$token";
        $mail->Body = "
            <h2>Password Reset Request</h2>
            <p>Click the link below to reset your password:</p>
            <a href='$resetLink' style='display:inline-block; padding:10px 20px; color:white; background-color:#4CAF50; text-decoration:none;'>Reset Password</a>
            <p>This link will expire in 1 hour.</p>
        ";

        if ($mail->send()) {
            $message = "A password reset link has been sent to your email address.";
            $messageType = "success";
        } else {
            $message = "Error: " . $mail->ErrorInfo;
            $messageType = "error";
        }
    } else {
        $message = "No account found with this email address.";
        $messageType = "error";
    }
}

$conn->close(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f4f4f9; }
        .container { width: 400px; padding: 20px; background: #ffffff; border-radius: 8px; box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); }
    </style>
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>
    <form method="POST" action="">
        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        <button type="submit" class="btn btn-success btn-block">Send Reset Link</button>
    </form>
</div>


<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">Message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#messageModal').modal('show');
    });
</script>
</body>
</html>
