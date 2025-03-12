<?php
session_name('employee_session'); 
session_start();
$_SESSION = array();
session_destroy();
header("Location: http://localhost:8000/customer/");
exit();
?>