<?php
session_name('admin_session'); 
session_start();
$_SESSION = array();
session_destroy();
header("Location: Admin_login.php");
exit();


?>