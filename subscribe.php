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

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = htmlspecialchars($_POST['email']);

       
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM newsletter_subscribers WHERE email = :email");
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();

        if ($checkStmt->fetchColumn() > 0) {
            
            echo "You have already subscribed with this email.";
        } else {
           
            $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (:email)");
            $stmt->bindParam(':email', $email);

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


                    $mail->setFrom('engestonbrandon@gmail.com', 'Online Car Rental');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = "Thank you for subscribing!";
                    $mail->Body    = "Hello,<br><br>Thank you for subscribing to our newsletter! We are excited to keep you updated.<br><br>Best regards,<br>Online Car Rental";

                    $mail->send(); 
                    echo "Subscription successful. A thank-you email has been sent.";
                } catch (Exception $e) {
                    echo "Failed to send the email. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                echo "There was an error in subscribing. Please try again.";
            }
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>