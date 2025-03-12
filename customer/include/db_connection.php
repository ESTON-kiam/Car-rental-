<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('customer_session');
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => false, 
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: http://localhost:8000/customer/"); 
    exit();
}

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "car_rental_management";

$conn = new mysqli($servername, $username, $password, $dbname);

$timeout = 3600; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: http://localhost:8000/customer/?timeout=1");
    exit();
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>