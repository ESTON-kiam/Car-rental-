<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';




if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    
    $servername = "localhost"; 
    $username = "root"; 
    $password = ""; 
    $dbname = "car_rental_management"; 

    
    $conn = new mysqli($servername, $username, $password, $dbname);

    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    
    $name = $conn->real_escape_string($_POST['name']);
    $contact_no = $conn->real_escape_string($_POST['contact_no']);
    $email_address = $conn->real_escape_string($_POST['email_address']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    
    $checkEmailQuery = "SELECT * FROM admins WHERE email_address='$email_address'";
    $result = $conn->query($checkEmailQuery);

    if ($result->num_rows > 0) {
        echo "<p style='color: red;'>Email already exists. Please choose a different email.</p>";
    } else {
        $insertQuery = "INSERT INTO admins (name, contact_no, email_address, gender, password) VALUES ('$name', '$contact_no', '$email_address', '$gender', '$password')";
        
        if ($conn->query($insertQuery) === TRUE) {
            echo "<p style='color: green;'>Registration successful! A confirmation email has been sent.</p>";

            
            $mail = new PHPMailer();

            
            $mail->isSMTP();                                           
            $mail->Host       = 'smtp.gmail.com';                   
            $mail->SMTPAuth   = true;                                
            $mail->Username   = 'engestonbrandon@gmail.com';            
            $mail->Password   = 'dsth izzm npjl qebi';                    
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     
            $mail->Port       = 587;                                   


           
            $mail->setFrom('no-reply@your-domain.com', 'Online Car Rental Admin');
            $mail->addAddress($email_address, $name); 
            $mail->Subject = 'Registration Confirmation';
            $mail->Body = "Dear $name,\n\nYou have successfully registered as an admin.\n\nYour login details are as follows:\nEmail: $email_address\nPassword: {$_POST['password']}\n\nBest regards,\nAdmin Team";
            $mail->isHTML(false); 

            
            if (!$mail->send()) {
                echo "<p style='color: red;'>Email could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
            }
        } else {
            echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
        }
    }

   
    $conn->close();
}
?>
