<?php
session_name('driver_session'); 
session_start();
$_SESSION = array();
session_destroy();
header("Location: Driver_login.php");
exit();


?>