<?php
require 'include/db_connection.php';

if (!isset($_SESSION['customer_id'])) {
    
    $_SESSION['customer_id'] = 1; 
    $_SESSION['customer_name'] = 'Test Customer'; 
}


$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];


$msgQuery = "SELECT sender, message, created_at FROM support_messages WHERE customer_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($msgQuery);
if ($stmt) {
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $messages = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Support</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
      h2 {
    position: relative;
    z-index: 10;
    margin-top: 25px; 
}


        .message-bubble {
            max-width: 70%;
            padding: 10px;
            border-radius: 20px;
            margin-bottom: 10px;
            position: relative;
        }
        .message-bubble.admin {
            background-color: #e0f7fa; 
        }
        .message-bubble.customer {
            background-color: #f1f1f1; 
            margin-left: auto;
        }
        .chat-container {
            max-height: 400px;
            overflow-y: auto;
            padding:10px;
        }
        .bg-gray-100{
            padding:auto;
        }
    </style>
</head>


<body class="bg-gray-100">
<header>  
<?php include 'include/header.php'; ?>
    </header>
<?php include 'include/sidebar.php'; ?>
    <div class="container mx-auto py-10">
        <div class="max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold mb-6">Customer Support</h2>
            
          
            <div class="chat-container border p-4 rounded-lg bg-white mb-4" id="chat-box">
                <?php if (isset($messages) && $messages->num_rows > 0): ?>
                    <?php while ($msg = $messages->fetch_assoc()): ?>
                        <div class="message-bubble <?= ($msg['sender'] == 'admin') ? 'admin' : 'customer' ?>">
                            <strong><?= ($msg['sender'] == 'admin') ? 'Support Agent' : 'You' ?>:</strong>
                            <p><?= htmlspecialchars($msg['message']) ?></p>
                            <span class="text-xs text-gray-500"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500">No messages yet. Start a conversation with our support team!</p>
                <?php endif; ?>
            </div>
            
            
            <form action="support_send.php" method="POST" class="flex mt-4" id="message-form">
                <input type="hidden" name="customer_id" value="<?= htmlspecialchars($customer_id) ?>">
                <input type="hidden" name="sender" value="customer">
                <input type="text" name="message" required placeholder="Type your message here..." class="flex-1 border p-2 rounded-l-lg">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r-lg">Send</button>
            </form>
        </div>
    </div>

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
            fetch(window.location.href)
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
        
        
        setInterval(refreshChat, 5000);
    </script>
</body>
</html>