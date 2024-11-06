<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Stronger session security
session_name('customer_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => true, // Changed to true for HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Check for session timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: customer_login.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Database configuration - Consider moving to separate config file
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'car_rental_management'
];

// Database connection with error handling
try {
    $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname']);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Sorry, there was a problem connecting to the database. Please try again later.");
}

// Function to fetch messages
function fetchMessages($conn, $customer_id) {
    $query = "SELECT sender, message, created_at FROM support_messages 
              WHERE customer_id = ? 
              ORDER BY created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    return $stmt->get_result();
}

// If it's an AJAX request to fetch new messages
if (isset($_GET['fetch_messages'])) {
    $result = fetchMessages($conn, $customer_id);
    while ($row = $result->fetch_assoc()) {
        if ($row['sender'] == 'customer') {
            echo '<div class="text-right mb-4">
                    <span class="inline-block bg-blue-500 text-white p-2 rounded-lg">
                        '.htmlspecialchars($row['message']).'
                    </span>
                    <div class="text-xs text-gray-500 mt-1">
                        '.date('H:i', strtotime($row['created_at'])).'
                    </div>
                  </div>';
        } else {
            echo '<div class="text-left mb-4">
                    <span class="font-semibold text-gray-600">Admin</span>
                    <span class="inline-block bg-gray-200 p-2 rounded-lg">
                        '.htmlspecialchars($row['message']).'
                    </span>
                    <div class="text-xs text-gray-500 mt-1">
                        '.date('H:i', strtotime($row['created_at'])).'
                    </div>
                  </div>';
        }
    }
    exit;
}

$result = fetchMessages($conn, $customer_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white shadow-lg rounded-lg">
        <!-- Header -->
        <div class="p-4 border-b">
            <h2 class="text-xl font-semibold">Support Chat</h2>
            <p class="text-sm text-gray-500">Customer ID: <?= htmlspecialchars($customer_id) ?></p>
        </div>
        
        <!-- Chat messages -->
        <div id="chatBox" class="h-96 overflow-y-scroll p-4 bg-gray-50">
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php if ($row['sender'] == 'customer'): ?>
                    <div class="text-right mb-4">
                        <span class="inline-block bg-blue-500 text-white p-2 rounded-lg">
                            <?= htmlspecialchars($row['message']) ?>
                        </span>
                        <div class="text-xs text-gray-500 mt-1">
                            <?= date('H:i', strtotime($row['created_at'])) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-left mb-4">
                        <span class="font-semibold text-gray-600">Admin</span>
                        <span class="inline-block bg-gray-200 p-2 rounded-lg">
                            <?= htmlspecialchars($row['message']) ?>
                        </span>
                        <div class="text-xs text-gray-500 mt-1">
                            <?= date('H:i', strtotime($row['created_at'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>

        <!-- Message input form -->
        <div class="p-4 border-t">
            <form id="chatForm" class="flex space-x-2">
                <input type="text" 
                       name="message" 
                       required 
                       placeholder="Type your message..." 
                       class="flex-1 border p-2 rounded-lg focus:outline-none focus:border-blue-500"
                       maxlength="500">
                <button type="submit" 
                        class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                    Send
                </button>
            </form>
            <div id="errorMessage" class="text-red-500 text-sm mt-2 hidden"></div>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById('chatBox');
        const chatForm = document.getElementById('chatForm');
        const errorMessage = document.getElementById('errorMessage');
        let lastMessageTime = Date.now();

        // Auto-scroll to bottom
        function scrollToBottom() {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
        scrollToBottom();

        // Fetch new messages periodically
        async function fetchNewMessages() {
            try {
                const response = await fetch('?fetch_messages=1');
                if (response.ok) {
                    const messages = await response.text();
                    chatBox.innerHTML = messages;
                    scrollToBottom();
                }
            } catch (error) {
                console.error('Error fetching messages:', error);
            }
        }

        // Update messages every 5 seconds
        setInterval(fetchNewMessages, 5000);

        // Handle form submission
        chatForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            // Rate limiting
            if (Date.now() - lastMessageTime < 1000) {
                errorMessage.textContent = 'Please wait a moment before sending another message.';
                errorMessage.classList.remove('hidden');
                return;
            }

            const messageInput = this.querySelector('input[name="message"]');
            const message = messageInput.value.trim();
            
            if (message === '') return;

            try {
                const formData = new FormData();
                formData.append('message', message);
                formData.append('customer_id', '<?= $customer_id ?>');

                const response = await fetch('support_send.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    messageInput.value = '';
                    errorMessage.classList.add('hidden');
                    lastMessageTime = Date.now();
                    await fetchNewMessages();
                } else {
                    throw new Error('Failed to send message');
                }
            } catch (error) {
                console.error('Error:', error);
                errorMessage.textContent = 'Failed to send message. Please try again.';
                errorMessage.classList.remove('hidden');
            }
        });

        // Clear error message when typing
        chatForm.querySelector('input').addEventListener('input', function() {
            errorMessage.classList.add('hidden');
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>