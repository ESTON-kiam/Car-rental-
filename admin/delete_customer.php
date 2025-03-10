<?php
session_name('admin_session');
session_set_cookie_params(3600); 
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    
    $servername = "localhost"; 
    $username = "root"; 
    $password = ""; 
    $dbname = "car_rental_management"; 

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $conn->close();

    
    header("Location: customerlist.php?message=Customer deleted successfully");
    exit();
} else {
    header("Location: customerlist.php?message=Invalid customer ID");
    exit();
}
?>