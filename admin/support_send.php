<?php
$customer_id = $_POST['customer_id'];
$message = $_POST['message'];

$conn = new mysqli("localhost", "root", "", "car_rental_management");


$query = "INSERT INTO support_messages (customer_id, sender, message) VALUES (?, 'admin', ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $customer_id, $message);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: support_view.php");
exit();
?>
