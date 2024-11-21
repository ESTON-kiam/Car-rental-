<?php
session_name('driver_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => false, 
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();


header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


if (!isset($_SESSION['driver_id'])) {
    header("Location: http://localhost:8000/driver/");
    exit();
}


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$driver_id = $_SESSION['driver_id'];
$message = "";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

   
    $query = "SELECT password, email FROM drivers WHERE driver_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $driver = $result->fetch_assoc();

    
    if ($driver && password_verify($current_password, $driver['password'])) {
        if ($new_password === $confirm_password) {
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE drivers SET password = ? WHERE driver_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $hashed_password, $driver_id);
            if ($stmt->execute()) {
                $message = "<span style='color:blue;'>Password updated successfully!</span>";


                
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
                    $mail->addAddress($driver['email']); 

                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Change Notification';
                    $mail->Body    = 'Hello, your password has been successfully changed. If you did not make this change, please contact support.';
                    $mail->AltBody = 'Hello, your password has been successfully changed. If you did not make this change, please contact support.';

                    $mail->send();
                } catch (Exception $e) {
                    $message .= " Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $message = "Error updating password.";
            }
        } else {
            $message = "New passwords do not match.";
        }
    } else {
        $message = "Incorrect current password.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #4CAF50, #009688);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #333;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            text-align: center;
            border-radius: 8px;
            width: 100%; 
            position: absolute; 
            top: 0; 
        }
        header h2 {
            font-size: 24px;
            margin-bottom: 5px; 
        }
        header nav a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            padding: 0 8px;
            transition: color 0.3s ease;
        }
        header nav a:hover {
            color: #4CAF50;
        }
        .container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 40px 20px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            color: #333;
            margin-top: 80px; 
        }
        h3 {
            color: #333;
            font-size: 22px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        p {
            font-size: 14px;
            color: #d9534f;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-group input[type="password"]:focus {
            border-color: #4CAF50;
            outline: none;
        }
        button {
            background: linear-gradient(135deg, #4CAF50, #009688);
            color: #fff;
            padding: 10px 0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        button:hover {
            background: linear-gradient(135deg, #45a049, #00796b);
        }
        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <header>
        <h2>Driver Dashboard</h2>
        <nav>
            <a href="dashboard.php">Back to Dashboard</a>
        </nav>
    </header>

    <div class="container">
        <h3>Change Password</h3>
        <p><?php echo $message; ?></p>
        
        <form method="POST" action="changepassword.php">
            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Change Password</button>
        </form>
    </div>

</body>
</html>
