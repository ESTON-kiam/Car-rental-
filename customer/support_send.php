<?php
session_start();
$customer_id = $_SESSION['customer_id'];
$message = $_POST['message'];

$conn = new mysqli("localhost", "root", "", "car_rental_management");


$query = "INSERT INTO support_messages (customer_id, sender, message) VALUES (?, 'customer', ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $customer_id, $message);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: support.php");
exit();
?>
