<?php
session_name('customer_session');
session_start();
session_destroy(); 
header("Location: customer_login.php"); 
exit();
?>