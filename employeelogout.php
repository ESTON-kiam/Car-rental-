<?php
session_start();
session_destroy(); 
header("Location: Employee_login.html"); 
exit();
?>