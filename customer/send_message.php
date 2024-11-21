<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['message']) && isset($_POST['customerId'])) {
    $message = $_POST['message'];
    $customerId = intval($_POST['customerId']);
    $stmt = $conn->prepare("INSERT INTO support_messages (customer_id, message, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $customerId, $message);
    $stmt->execute();
    $stmt->close();
}
$conn->close();
?>
