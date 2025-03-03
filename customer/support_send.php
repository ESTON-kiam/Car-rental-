<?php
require 'include/db_connection.php';


function generateChatbotResponse($message) {
   
    $message = strtolower($message);
    
   
    if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false || strpos($message, 'hey') !== false) {
        return "Hello! How can I assist you today with your car rental needs?";
    } 
    else if (strpos($message, 'how are you') !== false || strpos($message,'how are you doing') !== false) {
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
        return "We accept all major credit cards, Mpesa, PayPal, and bank transfers for payments. Mainly, we encourage payment through Mpesa as it is easy to follow up. Is there a specific payment question I can help with?";
    }
    else if (strpos($message, 'terms') !== false || strpos($message, 'conditions') !== false || strpos($message, 'terms and conditions') !== false) {
        return "Our car rental terms and conditions include requirements such as a valid driving license, a security deposit, fuel policy, mileage limits, and insurance coverage. Please visit our Terms and Conditions page for full details. Let me know if you need any specific clarification!";
    }
    else if (strpos($message, 'thank') !== false) {
        return "You're welcome! Is there anything else I can help you with?";
    }
    else if (strpos($message, 'insurance') !== false) {
        return "All our rental cars come with basic insurance coverage. Additional insurance options are available for extra protection. Would you like more details?";
    }
    else if (strpos($message, 'fuel') !== false || strpos($message, 'gas') !== false) {
        return "Our fuel policy is 'full-to-full,' meaning you receive the car with a full tank and should return it the same way to avoid extra charges.";
    }
    else if (strpos($message, 'age requirement') !== false || strpos($message, 'age limit') !== false) {
        return "The minimum age to rent a car is 18 years. However, drivers under 25 may be subject to a young driver surcharge.";
    }
    else if (strpos($message, 'driver') !== false || strpos($message, 'chauffeur') !== false) {
        return "Yes, we offer chauffeur-driven services at an additional cost. Let me know if you need a driver included in your rental.";
    }
    else if (strpos($message, 'location') !== false || strpos($message, 'branch') !== false) {
        return "We have multiple rental locations. Please let me know your preferred pick-up and drop-off location.";
    }
    else if (strpos($message, 'late return') !== false || strpos($message, 'late fee') !== false) {
        return "Late returns may incur additional charges. Please return the vehicle on time or contact us in advance to extend your rental period.";
    }
    else if (strpos($message, 'car availability') !== false || strpos($message, 'available cars') !== false) {
        return "Our car availability depends on the rental dates and location. Please visit our website or provide details, and I can check for you.";
    }
    else if (strpos($message, 'discount') !== false || strpos($message, 'promo') !== false || strpos($message, 'offer') !== false) {
        return "We occasionally offer discounts and promotions. Please check our website or contact us for the latest deals.";
    }
    else if (strpos($message, 'security deposit') !== false || strpos($message, 'deposit') !== false) {
        return "A refundable security deposit is required for all rentals. The amount depends on the car category and will be returned upon vehicle return.";
    }
    else if (strpos($message, 'extend rental') !== false || strpos($message, 'rental extension') !== false) {
        return "You can extend your rental period based on availability. Please contact us in advance to arrange an extension.";
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