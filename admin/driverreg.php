<?php
require 'include/db_connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = htmlspecialchars($_POST['name']);
    $contact = htmlspecialchars($_POST['contact']);
    $email = htmlspecialchars($_POST['email']);
    $residence = htmlspecialchars($_POST['residence']);
    $age = htmlspecialchars($_POST['age']);
    $password = htmlspecialchars($_POST['password']); 

    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); 

    $license_no = "LIC-" . uniqid();

    $target_dir = "Drivers/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $license_image = $target_dir . basename($_FILES["license_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($license_image, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES["license_image"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "<p style='color: red;'>File is not an image.</p>";
        $uploadOk = 0;
    }

    if ($uploadOk == 1 && move_uploaded_file($_FILES["license_image"]["tmp_name"], $license_image)) {
        
        
        $stmt = $conn->prepare("INSERT INTO drivers (name, contact_no, email, residence, age, driving_license_no, license_image, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssisss", $name, $contact, $email, $residence, $age, $license_no, $license_image, $hashedPassword); // Bind the hashed password

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Driver registered successfully!</p>";

            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'engestonbrandon@gmail.com';            
            $mail->Password   = 'dsth izzm npjl qebi';                    
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     
            $mail->Port       = 587;                                  


            $mail->setFrom('no-reply@gmail.com', 'Online Car Rental');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Driver Registration Confirmation - Online Car Rental';

            // Create a more visually appealing HTML email
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333333;
                        max-width: 600px;
                        margin: 0 auto;
                    }
                    .email-container {
                        border: 1px solid #dddddd;
                        border-radius: 5px;
                        padding: 20px;
                        background-color: #f9f9f9;
                    }
                    .header {
                        background-color: #4a7c59;
                        color: white;
                        padding: 15px;
                        text-align: center;
                        border-radius: 5px 5px 0 0;
                        margin: -20px -20px 20px -20px;
                    }
                    .content {
                        padding: 0 15px;
                    }
                    .credentials {
                        background-color: #eef7f2;
                        border-left: 4px solid #4a7c59;
                        padding: 10px 15px;
                        margin: 15px 0;
                    }
                    .footer {
                        margin-top: 20px;
                        font-size: 12px;
                        text-align: center;
                        color: #777777;
                        border-top: 1px solid #dddddd;
                        padding-top: 15px;
                    }
                    .button {
                        display: inline-block;
                        background-color: #4a7c59;
                        color: white;
                        padding: 10px 20px;
                        text-decoration: none;
                        border-radius: 5px;
                        margin: 15px 0;
                    }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='header'>
                        <h2>Welcome to Online Car Rental</h2>
                    </div>
                    <div class='content'>
                        <p>Hello <strong>$name</strong>,</p>
                        
                        <p>Thank you for registering as a driver with our service! Your registration has been successfully completed.</p>
                        
                        <div class='credentials'>
                            <p><strong>Your Login Details:</strong></p>
                            <p>Email: <strong>$email</strong><br>
                            Password: <strong>$password</strong></p>
                        </div>
                        
                        <p>For security reasons, we recommend changing your password after your first login.</p>
                        
                        <p>Your driver license number is: <strong>$license_no</strong></p>
                        
                        <p>Please ensure that all your information is accurate. If you need to make any changes, you can update your profile after logging in.</p>
                        
                        <center><a href='https://yourcarrentalwebsite.com/login' class='button'>Login to Your Account</a></center>
                        
                        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                        
                        <p>Best regards,<br>
                        Online Car Rental Team</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message. Please do not reply to this email.</p>
                        <p>&copy; " . date('Y') . " Online Car Rental. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ";

            // Plain text version for email clients that don't support HTML
            $mail->AltBody = "Hello $name,

            Thank you for registering as a driver with our service! Your registration has been successfully completed.

            YOUR LOGIN DETAILS:
            Email: $email
            Password: $password

            For security reasons, we recommend changing your password after your first login.

            Your driver license number is: $license_no

            Please ensure that all your information is accurate. If you need to make any changes, you can update your profile after logging in.

            To login to your account, please visit: http://localhost:8000/driver/

            If you have any questions or need assistance, please don't hesitate to contact our support team.

            Best regards,
            Online Car Rental Team

            © " . date('Y') . " Online Car Rental. All rights reserved.";

            if ($mail->send()) {
                echo "<p style='color: green;'>Confirmation email sent to $email!</p>";
            } else {
                echo "<p style='color: red;'>Failed to send confirmation email: {$mail->ErrorInfo}</p>";
            }
        } else {
            echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>Error uploading file.</p>";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Registration-Admin Panel</title>
   <link href="assets/css/driverreg.css" rel="stylesheet">
</head>
<body>  <?php include('include/header.php')?>
<?php include('include/sidebar.php') ?>
     <main class="main-content">
    <div class="container">
        <h1>Driver Registration</h1>
        <form action="driverreg.php" method="POST" enctype="multipart/form-data">
            <div>
                <label for="name">Name (as per license)</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div>
                <label for="contact">Contact Number</label>
                <input type="tel" id="contact" name="contact" pattern="[0-9]{10}" required>
            </div>

            <div>
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div>
                <label for="age">Age</label>
                <input type="number" id="age" name="age" min="18" max="65" required>
            </div>

            <div class="full-width">
                <label for="residence">Permanent Address</label>
                <textarea id="residence" name="residence" required></textarea>
            </div>
            <div class="full-width">
                <label for="license_image">Driving License Image</label>
                <input type="file" id="license_image" name="license_image" accept="image/*" required>
            </div>

            <div class="full-width">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <input type="submit" value="Register Driver">
        </form>
    </div></main>
</body>
</html>