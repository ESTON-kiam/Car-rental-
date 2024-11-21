<?php
session_name('driver_session');
session_start();

if (!isset($_SESSION['driver_id']) || !is_numeric($_GET['task_id'])) {
    header("Location: http://localhost:8000/driver/");
    exit();
}

$task_id = (int)$_GET['task_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental_management";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $sql = "DELETE FROM completed_tasks WHERE task_id = ? AND driver_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $task_id, $_SESSION['driver_id']);
    
    if ($stmt->execute()) {
        header("Location: completeassignmen.php?message=Task+Deleted+Successfully");
        exit();
    } else {
        throw new Exception("Error deleting task");
    }
    
} catch (Exception $e) {
    error_log("Error deleting task: " . $e->getMessage());
    header("Location: completeassignmen.php?error=Unable+to+Delete+Task");
    exit();
} finally {
    $stmt->close();
    $conn->close();
}
?>
