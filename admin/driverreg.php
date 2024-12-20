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
            $mail->Subject = 'Driver Registration Confirmation';
            $mail->Body    = "Hello $name,<br><br>Your registration as a driver has been successfully completed. Here are your login details:<br><br>Email: $email<br>Password: $password<br><br>Please remember to update your driving license information and upload an image.<br><br>Thank you!";
            $mail->AltBody = "Hello $name,\n\nYour registration as a driver has been successfully completed. Here are your login details:\n\nEmail: $email\nPassword: $password\n\nPlease remember to update your driving license information and upload an image.\n\nThank you!";

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 40px auto;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2.2rem;
            font-weight: 600;
        }

        form {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="tel"],
        input[type="number"],
        input[type="email"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e1e8ef;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #e1e8ef;
            border-radius: 8px;
            box-sizing: border-box;
            cursor: pointer;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        .full-width {
            grid-column: span 2;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            grid-column: span 2;
            transition: background-color 0.3s, transform 0.2s;
            margin-top: 12px;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
            transform: translateY(-1px);
        }

        input[type="submit"]:active {
            transform: translateY(1px);
        }

        @media screen and (max-width: 768px) {
            form {
                grid-template-columns: 1fr;
                padding: 24px;
            }

            .container {
                padding: 0 20px;
            }
        }
    </style>
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