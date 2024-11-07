<?php
// Existing session setup code
session_name('driver_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['driver_id'])) {
    header("Location: Driver_login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental_management";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$driver_id = $_SESSION['driver_id'];

// Query for completed assignments
$completedAssignmentsQuery = "
SELECT a.assignment_id, a.booking_id, a.vehicle_id, a.registration_no, a.model_name, 
       a.assigned_at, c.full_name AS customer_name 
FROM driver_assignments a 
JOIN customers c ON a.customer_id = c.id 
JOIN bookings b ON a.booking_id = b.booking_id 
WHERE a.driver_id='$driver_id' AND b.booking_status='completed' 
ORDER BY a.assigned_at DESC";
$completedAssignments = $conn->query($completedAssignmentsQuery);

// Query for pending assignments
$pendingAssignmentsQuery = "
SELECT a.assignment_id, a.booking_id, a.vehicle_id, a.registration_no, a.model_name, 
       a.assigned_at, c.full_name AS customer_name 
FROM driver_assignments a 
JOIN customers c ON a.customer_id = c.id 
JOIN bookings b ON a.booking_id = b.booking_id 
WHERE a.driver_id='$driver_id' AND b.booking_status='active' 
ORDER BY a.assigned_at DESC";
$pendingAssignments = $conn->query($pendingAssignmentsQuery);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - Driver Dashboard</title>
    <link rel="stylesheet" href="assets/css/driverdash.css">
    <script src="assets/js/driverdash.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <style>
        .assignment-card {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }
        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .assignment-details {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<header class="header" role="banner">
    <div class="header-content">
        <h1><i class="fas fa-car"></i> Assignments</h1>
    </div>
</header>

<main class="main-content">
    <section id="completed-assignments" class="content-section">
        <div class="section-header">
            <h1>Your Completed Assignments</h1>
        </div>

        <div class='assignment-cards' id='completedList'>
            <?php if ($completedAssignments->num_rows > 0) {
                while ($assignment = $completedAssignments->fetch_assoc()) { ?>
                    <div class='assignment-card'>
                        <div class='assignment-header'>
                            <h3>Assignment <?php echo htmlspecialchars($assignment['assignment_id']); ?></h3>
                            <span class='assignment-date'><?php echo date('M d, Y', strtotime($assignment['assigned_at'])); ?></span>
                        </div>

                        <div class='assignment-details'>
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($assignment['customer_name']); ?></p>
                            <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($assignment['model_name']); ?></p>
                            <p><strong>Registration No:</strong> <?php echo htmlspecialchars($assignment['registration_no']); ?></p>
                        </div>
                    </div>
                <?php }
            } else {
                echo '<div class=\'no-assignments\'>No completed assignments at the moment.</div>';
            } ?>
        </div>
    </section>

    <section id="pending-assignments" class="content-section">
        <div class="section-header">
            <h1>Your Pending Assignments</h1>
        </div>

        <div class='assignment-cards' id='pendingList'>
            <?php if ($pendingAssignments->num_rows > 0) {
                while ($assignment = $pendingAssignments->fetch_assoc()) { ?>
                    <div class='assignment-card'>
                        <div class='assignment-header'>
                            <h3>Assignment <?php echo htmlspecialchars($assignment['assignment_id']); ?></h3>
                            <span class='assignment-date'><?php echo date('M d, Y', strtotime($assignment['assigned_at'])); ?></span>
                        </div>

                        <div class='assignment-details'>
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($assignment['customer_name']); ?></p>
                            <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($assignment['model_name']); ?></p>
                            <p><strong>Registration No:</strong> <?php echo htmlspecialchars($assignment['registration_no']); ?></p>
                        </div>
                    </div>
                <?php }
            } else {
                echo '<div class=\'no-assignments\'>No pending assignments at the moment.</div>';
            } ?>
        </div>
    </section>
</main>

</body>
</html>
