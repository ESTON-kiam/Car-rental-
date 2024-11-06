<?php
// Enable error reporting for debugging
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
    header("Location: Admin_login.php");
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-10">
        <h2 class="text-2xl font-bold mb-6">Customer Support Messages</h2>
        
        <?php while ($customer = $customers->fetch_assoc()): ?>
            <div class="bg-white shadow-md rounded-lg mb-6 p-6">
                <h3 class="font-semibold text-lg mb-2">Chat with <?= htmlspecialchars($customer['full_name']) ?></h3>
                
                <?php
                // Fetch messages for this specific customer
                $msgQuery = "SELECT sender, message, created_at FROM support_messages WHERE customer_id = ? ORDER BY created_at ASC";
                $stmt = $conn->prepare($msgQuery);
                $stmt->bind_param("i", $customer['id']);
                $stmt->execute();
                $messages = $stmt->get_result();

                if ($messages->num_rows > 0): ?>
                    <div class="border p-4 rounded-lg overflow-y-auto max-h-60 bg-gray-50 mb-4" style="max-height: 400px;">
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <div class="mb-3 <?= $msg['sender'] == 'admin' ? 'text-right' : 'text-left' ?>">
                                <span class="font-semibold <?= $msg['sender'] == 'admin' ? 'text-blue-500' : 'text-gray-600' ?>">
                                    <?= $msg['sender'] == 'admin' ? 'Admin' : htmlspecialchars($customer['full_name']) ?>:
                                </span>
                                <div class="inline-block px-4 py-2 rounded-lg <?= $msg['sender'] == 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= htmlspecialchars($msg['message']) ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No messages yet with <?= htmlspecialchars($customer['full_name']) ?>.</p>
                <?php endif; ?>
                
                <?php $stmt->close(); ?>

                <!-- Admin response form -->
                <form action="admin_support_send.php" method="POST" class="flex mt-4">
                    <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
                    <input type="text" name="message" required placeholder="Reply to <?= htmlspecialchars($customer['full_name']) ?>" class="flex-1 border p-2 rounded-l-lg">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r-lg">Send</button>
                </form>
            </div>
        <?php endwhile; ?>

        <?php
        $customers->close();
        $conn->close();
        ?>
    </div>
</body>
</html>
