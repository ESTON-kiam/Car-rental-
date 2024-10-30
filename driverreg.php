<?php
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

    // Hash the password before storing it
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
        
        // Update the query to include the password
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
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #eaf2f8;
        }

        h1 {
            text-align: center;
            color: #003366;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #003366;
        }

        form {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #003366;
            font-weight: bold;
        }

        input[type="text"],
        input[type="tel"],
        input[type="number"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        input[type="file"] {
            margin-bottom: 20px;
        }

        input[type="submit"] {
            background-color: #003366;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #004080;
        }

        
        p {
            text-align: center;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        p[style*="green"] {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
        }

        p[style*="red"] {
            background-color: #f2dede;
            border: 1px solid #ebccd1;
        }

        
        @media screen and (max-width: 600px) {
            body {
                padding: 10px;
                margin: 10px;
            }

            form {
                padding: 15px;
            }

            input[type="text"],
            input[type="tel"],
            input[type="number"],
            input[type="email"],
            textarea {
                font-size: 14px;
            }
        }
    </style>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
</head>
<body>
<header style="background-color: #0077b6; color: white; padding: 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Driver Registration</h1>
        <nav>
            <a href="admin_dashboard.php" style="color: white; text-decoration: none; margin-right: 1rem;">Dashboard</a>
            <a href="driverslist.php" style="color: white; text-decoration: none;">Drivers</a>
        </nav>
    </div>
</header>

    <form action="driverreg.php" method="POST" enctype="multipart/form-data">
        <label for="name">Name (as per license):</label>
        <input type="text" id="name" name="name" required>

        <label for="contact">Contact Number:</label>
        <input type="tel" id="contact" name="contact" pattern="[0-9]{10}" required>

        <label for="email">Email Address:</label> 
        <input type="email" id="email" name="email" required>

        <label for="residence">Permanent Address:</label>
        <textarea id="residence" name="residence" required></textarea>

        <label for="age">Age:</label>
        <input type="number" id="age" name="age" min="18" max="65" required>

        <label for="license_image">Driving License Image:</label>
        <input type="file" id="license_image" name="license_image" accept="image/*" required>
        
        <label for="password" style="display: block; margin-bottom: 8px; color: #003366; font-weight: bold;">Password:</label>
        <input type="password" id="password" name="password" required style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 16px;">

        <input type="submit" value="Register Driver">
    </form>
</body>
</html>
