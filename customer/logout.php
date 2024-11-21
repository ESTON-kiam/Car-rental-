<?php
session_name('customer_session');
session_start();


if (isset($_SESSION['customer_id'])) {
    
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_email']);
    unset($_SESSION['customer_name']);
    
    
    session_regenerate_id(true);
}


header("Location: http://localhost:8000/customer/");
exit();
?>
