<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';

$host = 'localhost';
$db = 'car_rental_management';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log("Database Connection Failed: " . $e->getMessage()); 
    die("Database connection failed");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $full_name = trim(filter_var($_POST['first'], FILTER_SANITIZE_STRING));
    $email = trim(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL));
    $password = trim($_POST['password']);
    $repassword = trim($_POST['repassword']);
    $mobile = trim(filter_var($_POST['mobile'], FILTER_SANITIZE_STRING));
    $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
    $dob = $_POST['dob'];
    $occupation = trim(filter_var($_POST['occupation'], FILTER_SANITIZE_STRING));
    $residence = trim(filter_var($_POST['residence'], FILTER_SANITIZE_STRING));

    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Invalid email address.');
    }

    if ($password !== $repassword) {
        header("Location: passwordmismatch.html");
        exit();
    }

    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $emailExists = $stmt->fetchColumn();

    if ($emailExists) {
        header("Location: http://localhost:8000/customer/email_already_registered.html");
        exit();
    }

    
    if (strlen($password) < 8) {
        die('Password must be at least 8 characters.');
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  
    $sql = "INSERT INTO customers (full_name, email, password, mobile, gender, dob, occupation, residence) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$full_name, $email, $hashed_password, $mobile, $gender, $dob, $occupation, $residence])) {
        
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
                 $mail->addAddress($email, $full_name);

            $mail->isHTML(true);
            $mail->Subject = 'Thank You for Registering';
            $mail->Body    = '<h1>Thank you for being our member!</h1><p>Your registration was successful.</p>';
            $mail->AltBody = 'Thank you for being our member! Your registration was successful.';

            $mail->send();
            header("Location: registration_success.html");
            exit();
        } catch (Exception $e) {
            error_log("Mail error: " . $mail->ErrorInfo); 
            die("There was an error sending the email. Please try again.");
        }
    } else {
        die("Registration failed. Please try again.");
    }
}
?>
