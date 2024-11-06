<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['customerId'])) {
    $customerId = $_POST['customerId'];
    $messageQuery = "SELECT message, created_at FROM support_messages WHERE customer_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($messageQuery);
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = "";
    while ($row = $result->fetch_assoc()) {
        $messages .= "<div class='mb-2'>";
        $messages .= "<span class='font-bold'>Customer:</span>";
        $messages .= "<span class='ml-2'>" . htmlspecialchars($row['message']) . "</span>";
        $messages .= "<div class='text-xs text-gray-500'>" . date('Y-m-d H:i:s', strtotime($row['created_at'])) . "</div>";
        $messages .= "</div>";
    }

    echo $messages;
}
?>
