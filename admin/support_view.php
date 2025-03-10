<?php

require 'include/db_connection.php';
$customerQuery = "SELECT DISTINCT c.id, c.full_name FROM support_messages sm JOIN customers c ON sm.customer_id = c.id";
$customers = $conn->query($customerQuery);

if (isset($_POST['simulate_customer_message']) && isset($_POST['customer_id']) && isset($_POST['customer_message'])) {
    $customer_id = $_POST['customer_id'];
    $message = $_POST['customer_message']; 
  
    $query = "INSERT INTO support_messages (customer_id, sender, message) VALUES (?, 'customer', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $customer_id, $message);
    $stmt->execute();
    $stmt->close();
    
    $botResponse = generateChatbotResponse($message);

    $query = "INSERT INTO support_messages (customer_id, sender, message) VALUES (?, 'admin', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $customer_id, $botResponse);
    $stmt->execute();
    $stmt->close();
    
 
    header("Location: support_view.php?customer_id=" . $customer_id);
    exit();
}


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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Support View-Admin Panel</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        
        .message-bubble {
            max-width: 70%;
            padding: 10px;
            border-radius: 20px;
            margin-bottom: 10px;
            position: relative;
        }
        .message-bubble.admin {
            background-color: #e0f7fa; 
            margin-left: auto; 
        }
        .message-bubble.customer {
            background-color: #f1f1f1; 
        }
        .chat-container {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .chat-box-container {
            margin-left: 20px; 
            transition: all 0.3s ease; 
        }
    </style>
</head>
<body class="bg-gray-100"> <header>
        <?php include('include/header.php') ?>
    </header>

    <?php include('include/sidebar.php') ?>

        <main class="main-content">
    <div class="container mx-auto py-10 flex">
        <div class="w-1/3">
            <h2 class="text-2xl font-bold mb-6">Customer Support Messages</h2>

            
            <div class="mb-6">
                <?php while ($customer = $customers->fetch_assoc()): ?>
                    <div class="bg-white shadow-md rounded-lg mb-4 p-4 cursor-pointer hover:bg-gray-100">
                        <a href="?customer_id=<?= htmlspecialchars($customer['id']) ?>" class="text-blue-600 hover:underline">
                            <?= htmlspecialchars($customer['full_name']) ?>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <div class="chat-box-container w-2/3">
            <?php if (isset($_GET['customer_id'])): ?>
                <?php
                
                $selected_customer_id = intval($_GET['customer_id']);
                $msgQuery = "SELECT sender, message, created_at FROM support_messages WHERE customer_id = ? ORDER BY created_at ASC";
                $stmt = $conn->prepare($msgQuery);
                if ($stmt) {
                    $stmt->bind_param("i", $selected_customer_id);
                    $stmt->execute();
                    $messages = $stmt->get_result();
                }

               
                $customerDetailsQuery = "SELECT full_name FROM customers WHERE id = ?";
                $stmtDetails = $conn->prepare($customerDetailsQuery);
                if ($stmtDetails) {
                    $stmtDetails->bind_param("i", $selected_customer_id);
                    $stmtDetails->execute();
                    $resultDetails = $stmtDetails->get_result();

                    
                    if ($resultDetails && $resultDetails->num_rows > 0) {
                        $customerDetails = $resultDetails->fetch_assoc();
                        ?>
                        <h3 class="font-semibold text-lg mb-4">Chat with <?= htmlspecialchars($customerDetails['full_name']) ?></h3>
                        <?php
                    } else {
                        echo "<p class='text-red-500'>Customer not found.</p>";
                    }
                }
                ?>

                
                <div class="chat-container border p-4 rounded-lg bg-gray-50 mb-4" id="chat-box">
                    <?php if (isset($messages) && $messages->num_rows > 0): ?>
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <div class="message-bubble <?= ($msg['sender'] == 'admin') ? 'admin' : 'customer' ?>">
                                <strong><?= ($msg['sender'] == 'admin') ? 'Admin' : htmlspecialchars($customerDetails['full_name']) ?>:</strong>
                                <p><?= htmlspecialchars($msg['message']) ?></p>
                                <span class="text-xs text-gray-500"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500">No messages yet with <?= isset($customerDetails) ? htmlspecialchars($customerDetails['full_name']) : '' ?>.</p>
                    <?php endif; ?>
                </div>

               
                <form action="support_send.php" method="POST" class="flex mt-4" id="message-form">
                    <input type="hidden" name="customer_id" value="<?= htmlspecialchars($selected_customer_id) ?>">
                    <input type="text" name="message" required placeholder="Reply to <?= isset($customerDetails) ? htmlspecialchars($customerDetails['full_name']) : '' ?>" class="flex-1 border p-2 rounded-l-lg">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r-lg">Send</button>
                </form>
            <?php endif; ?>

            <?php
            
            if (isset($customers)) {
                $customers->close();
            }
            if (isset($conn)) {
                $conn->close();
            }
            ?>
        </div>
    </div></main>

    <script>
        
        const chatBox = document.getElementById('chat-box');
        
        
        function scrollToBottom() {
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        
        window.onload = scrollToBottom;

        if (document.getElementById('message-form')) {
            document.getElementById('message-form').onsubmit = function() {
                setTimeout(scrollToBottom, 100); 
            };
        }
        
        function refreshChat() {
            const currentUrl = window.location.href;
            if (currentUrl.includes('customer_id=')) {
               
                fetch(currentUrl)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newChatBox = doc.getElementById('chat-box');
                        if (newChatBox && chatBox) {
                            chatBox.innerHTML = newChatBox.innerHTML;
                            scrollToBottom();
                        }
                    });
            }
        }
        
        
        setInterval(refreshChat, 10000);
    </script>
</body>
</html>