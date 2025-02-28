<?php
require 'include/db_connection.php';


function generateChatbotResponse($message) {
   
    $message = strtolower($message);
    
   
    if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false || strpos($message, 'hey') !== false ) {
        return "Hello! How can I assist you today with your car rental needs?";
    } 
    else if (strpos($message, 'how are you') !== false || strpos($message,'how are you doing') !==false) {
        return "I'm just a chatbot, but I'm here to help! How can I assist you today?";
    }
    else if (strpos($message, 'price') !== false || strpos($message, 'cost') !== false || strpos($message, 'rate') !== false) {
        return "Our rental rates vary based on the vehicle model and rental duration. You can check our pricing page for details or let me know which car model you're interested in.";
    }
    else if (strpos($message, 'book') !== false || strpos($message, 'reservation') !== false || strpos($message, 'reserve') !== false) {
        return "To make a reservation, please visit our booking page or provide your preferred dates, location, and vehicle type, and I can help you with the process.";
    }
    else if (strpos($message, 'cancel') !== false) {
        return "Cancellations can be made up to 24 hours before your scheduled pickup without penalty. Would you like me to help you with a cancellation?";
    }
    else if (strpos($message, 'payment') !== false || strpos($message, 'pay') !== false) {
        return "We accept all major credit cards, PayPal, and bank transfers for payments. Is there a specific payment question I can help with?";
    }
    else if (strpos($message, 'thank') !== false) {
        return "You're welcome! Is there anything else I can help you with?";
    }
    else {
        return "Thank you for your message. I'll do my best to assist you. Could you please provide more details about your inquiry so I can better help you?";
    }
}


$customer_id = $_POST['customer_id'];
$message = $_POST['message'];


$sender = 'admin';


if (isset($_POST['sender']) && $_POST['sender'] == 'customer') {
    $sender = 'customer';
}


$conn = new mysqli("localhost", "root", "", "car_rental_management");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$query = "INSERT INTO support_messages (customer_id, sender, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $customer_id, $sender, $message);
$stmt->execute();
$stmt->close();


if ($sender == 'customer') {
    
    $botResponse = generateChatbotResponse($message);
    
    $query = "INSERT INTO support_messages (customer_id, sender, message) VALUES (?, 'admin', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $customer_id, $botResponse);
    $stmt->execute();
    $stmt->close();
}

$conn->close();


if ($sender == 'admin') {
    header("Location: support_view.php?customer_id=" . $customer_id);
} else {
   
    header("Location:support.php");
}
exit();
?>