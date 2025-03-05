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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<p style='color: red;'>No driver ID specified.</p>";
    header("Refresh: 2; url=driverslist.php");
    exit();
}

$id = intval($_GET['id']); 


$conn->begin_transaction();

try {
   
    $stmt1 = $conn->prepare("DELETE FROM completed_tasks WHERE driver_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

  
    $stmt2 = $conn->prepare("DELETE FROM drivers WHERE driver_id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    
    if ($stmt2->affected_rows > 0) {
        $conn->commit(); 
        echo "<p style='color: green;'>Driver deleted successfully!</p>";
    } else {
        throw new Exception("No driver found with the given ID.");
    }
    
    $stmt2->close();
} catch (Exception $e) {
    $conn->rollback(); 
    echo "<p style='color: red;'>Error deleting driver: " . $e->getMessage() . "</p>";
}

header("Refresh: 2; url=driverslist.php");
$conn->close();
?>