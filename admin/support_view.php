<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
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


$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "car_rental_management"; 


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$customerQuery = "SELECT DISTINCT c.id, c.full_name FROM support_messages sm JOIN customers c ON sm.customer_id = c.id";
$customers = $conn->query($customerQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Support View</title>
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
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        
        window.onload = scrollToBottom;

        document.getElementById('message-form').onsubmit = function() {
            setTimeout(scrollToBottom, 100); 
        };
    </script>
</body>
</html>