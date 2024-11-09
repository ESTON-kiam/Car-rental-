<?php
session_name('admin_session');
session_set_cookie_params(1800); 
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


if (isset($_GET['id'])) {
    $id = $_GET['id'];

  
    $stmt = $conn->prepare("DELETE FROM drivers WHERE driver_id = ?");
    $stmt->bind_param("i", $id);

    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>Driver deleted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error deleting driver: " . $stmt->error . "</p>";
    }

    $stmt->close();
} else {
    echo "<p style='color: red;'>No driver ID specified.</p>";
}


header("Refresh: 2; url=driverslist.php");
$conn->close();
?>
