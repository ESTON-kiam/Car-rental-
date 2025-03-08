<?php
session_name('admin_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => false, 
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: http://localhost:8000/admin/");
    exit();
}

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "car_rental_management"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_SESSION['email'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    
    if ($new_password !== $confirm_password) {
        $error_message = "New password and confirmation do not match.";
    } else {
        
        $query = "SELECT password FROM admins WHERE email_address='$email'";
        $result = $conn->query($query);
        $admin = $result->fetch_assoc();

        
        if (password_verify($current_password, $admin['password'])) {
           
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            
            $sql = "UPDATE admins SET password=? WHERE email_address=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $hashed_password, $email);

            if ($stmt->execute()) {
                
                $mail = new PHPMailer(true);
                try {
                    
                    $mail->isSMTP();                                           
            $mail->Host       = 'smtp.gmail.com';                   
            $mail->SMTPAuth   = true;                                
            $mail->Username   = 'engestonbrandon@gmail.com';            
            $mail->Password   = 'dsth izzm npjl qebi';                    
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     
            $mail->Port       = 587;                                  

            $mail->setFrom('no-reply@gmail.com', 'Online Car Rental');
                    $mail->addAddress($email); 

                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Changed Successfully';
                    $mail->Body    = 'Your password has been changed successfully. If you did not initiate this change, please contact support.';

                    $mail->send();
                    $success_message = "Password changed successfully! An email notification has been sent.";
                } catch (Exception $e) {
                    $error_message = "Password changed, but could not send email. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error_message = "Error changing password: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $error_message = "Current password is incorrect.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password-Admin Panel<</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link href="assets/css/changepass.css" rel="stylesheet">
</head>
<body>

<header>
   <?php include('include/header.php') ?>
</header>
<?php include('include/sidebar.php') ?>
<main class="main-content">
<form action="" method="post">
    <h2>Change Your Password</h2>
    <?php if (isset($success_message)): ?>
        <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
    <?php elseif (isset($error_message)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    <div>
        <label>Current Password:</label>
        <input type="password" name="current_password" required>
    </div>
    <div>
        <label>New Password:</label>
        <input type="password" name="new_password" required>
    </div>
    <div>
        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password" required>
    </div>
    <button type="submit">Change Password</button>
</form>
</main>
</body>
</html>