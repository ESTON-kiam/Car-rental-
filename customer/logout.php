<?php
session_name('customer_session');
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "car_rental_management";

try {
   
    $conn = new mysqli($servername, $username, $password, $dbname);

    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if (isset($_SESSION['customer_id'])) {
        $customer_id = $_SESSION['customer_id'];

        
        $update_logout_sql = "UPDATE customers SET last_logout = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_logout_sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();

       
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_email']);
        unset($_SESSION['customer_name']);

       
        session_regenerate_id(true);

        
        session_destroy();

      
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, "/", "", false, true);
        }

       
        $delete_token_sql = "DELETE FROM remember_tokens WHERE user_id = ?";
        $stmt_token = $conn->prepare($delete_token_sql);
        $stmt_token->bind_param("i", $customer_id);
        $stmt_token->execute();
    }

    header("Location: http://localhost:8000/customer/");
    exit();

} catch (Exception $e) {
   
    error_log("Logout error: " . $e->getMessage());
    
   
    header("Location: http://localhost:8000/customer/");
    exit();
} finally {
    
    if (isset($conn)) {
        $conn->close();
    }
}
?>