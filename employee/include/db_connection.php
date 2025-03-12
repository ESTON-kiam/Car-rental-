<?php
session_name('employee_session');
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['employee_id'])) {
    header("Location: http://localhost:8000/employee/");
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


$timeout = 3600; 
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: http://localhost:8000/employee/?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();
?>