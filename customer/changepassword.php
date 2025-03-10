<?php

require 'include/db_connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';



$customer_id = $_SESSION['customer_id'];


$sql = "SELECT email FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($user_email);
$stmt->fetch();
$stmt->close();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    
    $sql = "SELECT password FROM customers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($current_password, $hashed_password)) {
        if ($new_password === $confirm_password) {
            $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $sql = "UPDATE customers SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_hashed_password, $customer_id);
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
                    $mail->addAddress($user_email); 

                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Change Confirmation';
                    $mail->Body = 'Your password has been changed successfully. If you did not make this change, please contact support immediately.';

                    $mail->send();
                    $success_message = "Password changed successfully! A confirmation email has been sent.";
                } catch (Exception $e) {
                    $error_message = "Password changed, but could not send email. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error_message = "Error updating password. Please try again.";
            }
            $stmt->close();
        } else {
            $error_message = "New passwords do not match.";
        }
    } else {
        $error_message = "Current password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #0056b3;
            --background-color: #f8f9fa;
            --text-color: #343a40;
            --card-background: #ffffff;
            --header-height: 60px;
            --border-radius: 8px;
            --box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding-top: var(--header-height);
        }

        .header {
            width: 100%;
            height: var(--header-height);
            background-color: var(--primary-color);
            color: white;
            position: fixed;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: var(--box-shadow);
            z-index: 10;
        }

        .header h1 {
            font-size: 1.5rem;
            margin: 0;
        }

        .header a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            padding: 10px 15px;
            border-radius: var(--border-radius);
            transition: background-color 0.3s;
        }

        .header a:hover {
            background-color: var(--secondary-color);
        }

        .password-container {
            max-width: 600px;
            margin: 80px auto 20px;
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            transition: transform 0.3s;
        }

        .password-container:hover {
            transform: translateY(-5px);
        }

        .password-details {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            transition: border 0.3s;
        }

        input[type="password"]:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        button {
            padding: 12px 20px;
            margin-top: 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s, transform 0.3s;
        }

        button:hover {
            background-color: var(--secondary-color);
            transform: scale(1.05);
        }

        .error-message, .success-message {
            margin: 10px 0;
            color: red;
        }

        .success-message {
            color: green;
        }

        @media (max-width: 600px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header h1 {
                margin-bottom: 10px;
            }

            .password-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Change Password</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="viewprofile.php">View Profile</a>
        </div>
    </div>

    <div class="password-container">
        <form method="POST">
            <div class="password-details">
                <div>
                    <strong>Current Password:</strong>
                    <input type="password" name="current_password" required>
                </div>
                <div>
                    <strong>New Password:</strong>
                    <input type="password" name="new_password" required>
                </div>
                <div>
                    <strong>Confirm New Password:</strong>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <button type="submit">Change Password</button>
        </form>
    </div>

</body>
</html>

<?php
$conn->close();
?>
