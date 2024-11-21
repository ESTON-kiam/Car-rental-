<?php
session_name('driver_session'); 
session_start();
$_SESSION = array();
session_destroy();
header("Location: http://localhost:8000/driver/");
exit();


?>