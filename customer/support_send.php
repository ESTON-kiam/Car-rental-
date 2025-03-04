<?php
require 'include/db_connection.php';

function generateChatbotResponse($message, $customer_id, $conn) {
    $message = strtolower($message);
    
   
    $customer_name = "";
    $customer_query = "SELECT full_name FROM customers WHERE id = ?";
    $stmt = $conn->prepare($customer_query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $customer_name = $row['full_name'];
    }
    $stmt->close();
    
   
    $name_parts = explode(' ', $customer_name);
    $first_name = !empty($name_parts[0]) ? $name_parts[0] : $customer_name;
    
   
    if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false || strpos($message, 'hey') !== false) {
        return "Hello $first_name! How can I assist you today with your car rental needs?";
    } 
    else if (strpos($message, 'how are you') !== false || strpos($message,'how are you doing') !== false) {
        return "I'm just a chatbot, but I'm here to help you, $first_name! How can I assist you today?";
    }
    
   
    else if (strpos($message, 'available') !== false && (strpos($message, 'car') !== false || strpos($message, 'vehicle') !== false || strpos($message, 'cars') !== false || strpos($message, 'vehicles') !== false || strpos($message, 'vehicles available') !== false || strpos($message, 'available vehicle') !== false)) {
        $query = "SELECT model_name, price_per_day FROM vehicles WHERE availability_status = 'Available' ORDER BY price_per_day ASC";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            $response = "Here are our currently available vehicles, $first_name:\n\n";
            while ($row = $result->fetch_assoc()) {
                $response .= "• " . $row['model_name'] . " - KSh" . number_format($row['price_per_day'], 2) . " per day\n";
            }
            $response .= "\nWould you like more details about any specific model?";
            return $response;
        } else {
            return "I'm sorry $first_name, there are no vehicles available at the moment. Please check back later or contact our office directly.";
        }
    }
    
    
    else if ((strpos($message, 'price') !== false || strpos($message, 'cost') !== false || strpos($message, 'rate') !== false) && 
             (strpos($message, 'below') !== false || strpos($message, 'under') !== false || strpos($message, 'less than') !== false || 
              strpos($message, 'cheaper than') !== false || strpos($message, 'maximum') !== false || 
              preg_match('/\d+/', $message, $matches))) {
        
       
        $max_price = 0;
        if (preg_match('/\b(\d+)[k\s]*(?:sh|ksh|shilling|shillings)?\b/i', $message, $matches)) {
            $max_price = (int)$matches[1];
            
            if (strpos($matches[0], 'k') !== false) {
                $max_price *= 1000;
            }
        } else {
           
            $max_price = 10000;
        }
        
        $query = "SELECT model_name, price_per_day FROM vehicles WHERE availability_status = 'Available' AND price_per_day <= ? ORDER BY price_per_day ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("d", $max_price);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response = "Here are vehicles available under KSh" . number_format($max_price, 2) . ", $first_name:\n\n";
            while ($row = $result->fetch_assoc()) {
                $response .= "• " . $row['model_name'] . " - KSh" . number_format($row['price_per_day'], 2) . " per day\n";
            }
            $response .= "\nWould you like to book any of these vehicles?";
            return $response;
        } else {
            return "I'm sorry $first_name, we don't have any vehicles available under KSh" . number_format($max_price, 2) . " at the moment. Our lowest priced available vehicle starts from KSh" . getLowestPrice($conn) . " per day.";
        }
        $stmt->close();
    }
    
    
    else if (preg_match('/\b(?:about|details|info|information|specifications|specs)\s+(?:the\s+)?([a-zA-Z0-9\s]+)(?:\s+car|\s+vehicle)?\b/i', $message, $matches)) {
        $model = trim($matches[1]);
        $query = "SELECT * FROM vehicles WHERE model_name LIKE ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $search_term = "%$model%";
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response = "Here are the details for the {$row['model_name']}, $first_name:\n\n";
            $response .= "• Registration: {$row['registration_no']}\n";
            $response .= "• Status: {$row['availability_status']}\n";
            $response .= "• Price per day: KSh" . number_format($row['price_per_day'], 2) . "\n";
            if (!empty($row['ac_price_per_day'])) {
                $response .= "• AC price: KSh" . number_format($row['ac_price_per_day'], 2) . " per day\n";
            }
            if (!empty($row['non_ac_price_per_day'])) {
                $response .= "• Non-AC price: KSh" . number_format($row['non_ac_price_per_day'], 2) . " per day\n";
            }
            if (!empty($row['km_price'])) {
                $response .= "• Price per KM: KSh" . number_format($row['km_price'], 2) . "\n";
            }
            $response .= "• Description: {$row['description']}\n\n";
            $response .= "Would you like to book this vehicle?";
            return $response;
        } else {
            return "I'm sorry $first_name, I couldn't find any information about a '$model' in our fleet. Please check the model name or ask about our available vehicles.";
        }
        $stmt->close();
    }
    
   
    else if (preg_match('/\b(?:do you have|got any|have any|looking for|need a|want a|rent a)\s+([a-zA-Z0-9\s]+)(?:\s+car|\s+vehicle|\s+van|\s+suv|\s+truck)?\b/i', $message, $matches)) {
        $type = trim($matches[1]);
        $query = "SELECT model_name, price_per_day, availability_status FROM vehicles WHERE model_name LIKE ? OR description LIKE ?";
        $stmt = $conn->prepare($query);
        $search_term = "%$type%";
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response = "Here are the vehicles matching '$type', $first_name:\n\n";
            while ($row = $result->fetch_assoc()) {
                $response .= "• " . $row['model_name'] . " - KSh" . number_format($row['price_per_day'], 2) . " per day (" . $row['availability_status'] . ")\n";
            }
            $response .= "\nWould you like more details about any of these vehicles?";
            return $response;
        } else {
            return "I'm sorry $first_name, we don't have any vehicles matching '$type' in our fleet currently. Please ask about our available vehicles to see what we offer.";
        }
        $stmt->close();
    }
    
   
    else if (strpos($message, 'price') !== false || strpos($message, 'cost') !== false || strpos($message, 'rate') !== false) {
        return "$first_name, our rental rates vary based on the vehicle model and rental duration. You can ask about 'available vehicles' to see our current pricing, or ask about a specific car model for details.";
    }
    else if (strpos($message, 'book') !== false || strpos($message, 'reservation') !== false || strpos($message, 'reserve') !== false) {
        return "To make a reservation, $first_name, please provide your preferred dates, location, and vehicle type, and I can help you with the process.";
    }
    else if (strpos($message, 'cancel') !== false || strpos($message, 'cancel booking') !== false || strpos($message, 'cancellation') !== false) {
        return "$first_name, cancellations can be made up to 24 hours before your scheduled pickup without penalty. Would you like me to help you with a cancellation?";
    }
    else if (strpos($message, 'payment') !== false || strpos($message, 'pay') !== false) {
        return "$first_name, we accept all major credit cards, Mpesa, PayPal, and bank transfers for payments. Mainly, we encourage payment through Mpesa as it is easy to follow up. Is there a specific payment question I can help with?";
    }
    else if (strpos($message,'price') !== false || strpos($message,'pricing') !== false) {
        return "Hello, $first_name! we have different pricing mechanisms as you will be booking";
    }
    else if (strpos($message, 'terms') !== false || strpos($message, 'conditions') !== false || strpos($message, 'terms and conditions') !== false) {
        return "$first_name, our car rental terms and conditions include requirements such as a valid a security deposit or what we call advanced Deposit, fuel policy, mileage limits, and insurance coverage. Please visit our Terms and Conditions page for full details. Let me know if you need any specific clarification!";
    }
    else if (strpos($message, 'thank') !== false) {
        return "You're welcome, $first_name! Is there anything else I can help you with?";
    }
    else if (strpos($message, 'insurance') !== false) {
        return "$first_name, all our rental cars come with basic insurance coverage. Additional insurance options are available for extra protection. Would you like more details?";
    }
    else if (strpos($message, 'fuel') !== false || strpos($message, 'gas') !== false) {
        return "$first_name, our fuel policy is 'full-to-full,' meaning you receive the car with a full tank and should return it the same way to avoid extra charges.";
    }
    else if (strpos($message, 'age') !== false || strpos($message, 'age requirement') !== false || strpos($message, 'age limit') !== false) {
        return "$first_name, the minimum age to rent a car is 18 years. However, drivers under 25 may be subject to a young driver surcharge.";
    }
    else if (strpos($message, 'driver') !== false || strpos($message, 'chauffeur') !== false) {
        return "Yes $first_name, we offer chauffeur-driven services at an additional cost. Let me know if you need a driver included in your rental.";
    }
    else if (strpos($message, 'location') !== false || strpos($message, 'branch') !== false) {
        return "$first_name, we have multiple rental locations. Please let me know your preferred pick-up and drop-off location.";
    }
    else if (strpos($message, 'late') !== false || strpos($message, 'late return') !== false || strpos($message, 'late fee') !== false) {
        return "$first_name, late returns may incur additional charges. Please return the vehicle on time or contact us in advance to extend your rental period.";
    }
    else if (strpos($message, 'car availability') !== false || strpos($message, 'available cars') !== false) {
        return "$first_name, our car availability depends on the rental dates and location. Please ask about 'available cars' and I can check for you.";
    }
    else if (strpos($message, 'discount') !== false || strpos($message, 'promo') !== false || strpos($message, 'offer') !== false) {
        return "$first_name, we occasionally offer discounts and promotions. Please check our website or contact us for the latest deals.";
    }
    else if (strpos($message, 'security deposit') !== false || strpos($message, 'deposit') !== false) {
        return "A refundable security deposit is required for all rentals, $first_name. The amount depends on the car category and will be returned upon vehicle return.";
    }
    else if (strpos($message, 'extend') !== false || strpos($message, 'extension') !== false) {
        return "$first_name, you can extend your rental period based on availability. Please contact us in advance to arrange an extension.";
    }
    else if (strpos($message, 'document') !== false || strpos($message, 'documents') !== false || strpos($message, 'id') !== false || strpos($message, 'license') !== false) {
        return "$first_name, you'll need a valid driving license, ID card/passport, and a credit card or cash for the security deposit to rent a vehicle. For corporate rentals, we may require additional documentation.";
    }
    else if (strpos($message, 'pickup') !== false || strpos($message, 'pick up') !== false || strpos($message, 'collection') !== false) {
        return "$first_name, we offer pickup services from selected locations including airports, hotels, and our branch offices. Is there a specific location you'd like to arrange for pickup?";
    }
    else if (strpos($message, 'drop') !== false || strpos($message, 'return') !== false || strpos($message, 'dropping') !== false) {
        return "$first_name, vehicles can be returned to any of our branches (additional fees may apply for different pickup/return locations). Please let me know your preferred return location.";
    }
    else if (strpos($message, 'long term') !== false || strpos($message, 'monthly') !== false) {
        return "$first_name, we offer special rates for long-term rentals (weekly, monthly). Long-term rentals often come with significant discounts compared to daily rates. Would you like me to provide more details?";
    }
    else if (strpos($message, 'damage') !== false || strpos($message, 'accident') !== false || strpos($message, 'breakdown') !== false) {
        return "$first_name, in case of damage or breakdown, please contact our emergency hotline immediately. Basic insurance covers some damages, but you may be liable for the excess amount. We provide 24/7 roadside assistance for all rentals.";
    }
    else if (strpos($message, 'additional driver') !== false || strpos($message, 'second driver') !== false) {
        return "$first_name, additional drivers can be added to your rental agreement for a small fee. Each additional driver must meet our age requirements and provide a valid driving license.";
    }
    else if (strpos($message, 'mileage') !== false || strpos($message, 'kilometer') !== false || strpos($message, 'km') !== false) {
        return "$first_name, our standard rentals include unlimited mileage. However, some special offers and long-term rentals may have mileage limitations. Please check your specific rental agreement for details.";
    }
    else if (strpos($message, 'contact') !== false || strpos($message, 'phone') !== false || strpos($message, 'number') !== false || strpos($message, 'email') !== false) {
        return "$first_name, you can reach our customer service team at +25457196660 or via email at support@carrentals.com. Our office hours are Monday to Friday, 8 AM to 6 PM, and Saturday 9 AM to 3 PM.";
    }
    else {
        return "Thank you for your message, $first_name. I'll do my best to assist you. Could you please provide more details about your inquiry so I can better help you? You can ask about available vehicles, pricing, booking process, or specific models.";
    }
}


function getLowestPrice($conn) {
    $query = "SELECT MIN(price_per_day) as min_price FROM vehicles WHERE availability_status = 'Available'";
    $result = $conn->query($query);
    if ($row = $result->fetch_assoc()) {
        return number_format($row['min_price'], 2);
    }
    return "N/A";
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
    $botResponse = generateChatbotResponse($message, $customer_id, $conn);
    
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
    
    header("Location: support.php");
}
exit();
?>