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
        $name = htmlspecialchars($_POST['name']);
        $email = htmlspecialchars($_POST['email']);
        $subject = htmlspecialchars($_POST['subject']);
        $message = htmlspecialchars($_POST['message']);

        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        
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

            $mail->addAddress($email, $name);                          

                
                $mail->isHTML(true);                                      
                $mail->Subject = "Thank you for contacting us";
                $mail->Body    = "Hello $name,<br><br>Thank you for reaching out to us. We have received your message:<br><strong>Subject:</strong> $subject<br><strong>Message:</strong> $message<br><br>We will get back to you shortly.<br><br>Best regards,<br>Online Car Rental";

                $mail->send();                                           
                echo "Message sent successfully. Thank you!";
            } catch (Exception $e) {
                echo "Failed to send the email. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "There was an error in submitting your message.";
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>