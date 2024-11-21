<?php

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
    header("Location: http://localhost:8000/driver/");
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

$query = "SELECT name, profile_picture FROM drivers WHERE driver_id='$driver_id'";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    $driver = $result->fetch_assoc();
    $name = $driver['name'];
    $profile_picture = !empty($driver['profile_picture']) ? $driver['profile_picture'] : 'default-profile.jpg';
} else {
    $name = "Driver";
    $profile_picture = 'default-profile.jpg';
}


$pendingCountQuery = "
SELECT COUNT(*) as pending_count FROM driver_assignments da 
JOIN bookings b ON da.booking_id = b.booking_id 
WHERE da.driver_id='$driver_id' AND b.booking_status='pending'";
$pendingCountResult = $conn->query($pendingCountQuery);
$pending_count = ($pendingCountResult && $pendingCountResult->num_rows > 0) ? 
                 $pendingCountResult->fetch_assoc()['pending_count'] : 0;


$pendingAssignmentsQuery = "
SELECT a.assignment_id, a.booking_id, a.vehicle_id, a.registration_no, a.model_name, 
       a.assigned_at, c.full_name AS customer_name, b.start_date, b.pick_up_time 
FROM driver_assignments a 
JOIN customers c ON a.customer_id = c.id 
JOIN bookings b ON a.booking_id = b.booking_id 
WHERE a.driver_id='$driver_id' AND b.booking_status='pending' 
ORDER BY a.assigned_at ASC";
$assignments = $conn->query($pendingAssignmentsQuery);


$bookingsQuery = "
SELECT booking_id, vehicle_id, registration_no, model_name, pick_up_location, start_date, pick_up_time 
FROM bookings WHERE driver_option='no' AND booking_status='pending'";
$bookingsResult = $conn->query($bookingsQuery);


if (isset($_POST['complete_assignment'])) {
    $assignmentId = isset($_POST['assignment_id']) ? $_POST['assignment_id'] : null; 
    $bookingId = isset($_POST['booking_id']) ? $_POST['booking_id'] : null;

    if ($bookingId) {
        
        $updateBookingStatusQuery = "
        UPDATE bookings SET booking_status='active' 
        WHERE booking_id=?";
        
        $stmtUpdateBookingStatus = $conn->prepare($updateBookingStatusQuery);
        $stmtUpdateBookingStatus->bind_param("i", $bookingId);
        $stmtUpdateBookingStatus->execute();
        $stmtUpdateBookingStatus->close();
    } else if ($assignmentId) {
        
        $updateBookingStatusQuery = "
        UPDATE bookings SET booking_status='active' 
        WHERE booking_id=(SELECT booking_id FROM driver_assignments WHERE assignment_id=?)";
        
        $updateDriverStatusQuery = "
        UPDATE drivers SET availability_status='available' 
        WHERE driver_id='$driver_id'";
        
        
        $insertCompletedTaskQuery = "
        INSERT INTO completed_tasks (assignment_id, driver_id, booking_id, vehicle_id, registration_no, model_name, completed_at)
        SELECT da.assignment_id, da.driver_id, b.booking_id, v.vehicle_id, v.registration_no, v.model_name, NOW()
        FROM driver_assignments da
        JOIN bookings b ON da.booking_id = b.booking_id
        JOIN vehicles v ON da.vehicle_id = v.vehicle_id
        WHERE da.assignment_id = ?";
        
        $stmtUpdateBookingStatus = $conn->prepare($updateBookingStatusQuery);
        $stmtUpdateDriverStatus = $conn->prepare($updateDriverStatusQuery);
        $stmtInsertCompletedTask = $conn->prepare($insertCompletedTaskQuery);
        
        $stmtUpdateBookingStatus->bind_param("i", $assignmentId);
        $stmtInsertCompletedTask->bind_param("i", $assignmentId); 
        
        if ($stmtUpdateBookingStatus->execute() && $stmtUpdateDriverStatus->execute() && $stmtInsertCompletedTask->execute()) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
        
        $stmtUpdateBookingStatus->close();
        $stmtUpdateDriverStatus->close();
        $stmtInsertCompletedTask->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - Car Rental System</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/driverdash.css">
    <script src="assets/js/driverdash.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <style>
        .action-btn {
            background-color: #4CAF50; 
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }
        .action-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<header class="header" role="banner">
    <div class="header-content">
        <h1><i class="fas fa-car"></i> Drivers' Dashboard</h1>
        <div class="profile" role="navigation" aria-label="User menu">
            <button class="profile-button" aria-expanded="false" aria-controls="profile-menu">
                <div class="profile-picture">
                    <img id="profile-img" src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Driver profile picture" loading="lazy" width="40" height="40">
                </div>
                <span class="profile-name"><?php echo htmlspecialchars($name); ?></span>
            </button>
            <div id="profile-menu" class="profile-dropdown" hidden>
                <nav>
                    <ul>
                        <li><a href="viewprofile.php"><i class="fas fa-user"></i> View Profile</a></li>
                        <li><a href="edit_profile.php"><i class="fas fa-edit"></i> Edit Profile</a></li>
                        <li><a href="changepassword.php"><i class="fas fa-key"></i> Change Password</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</header>

<div class="dashboard">
    <aside class="sidebar">
        <nav>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link active"><i class="fas fa-tasks"></i> Pending Assignments <span class="badge"><?php echo $pending_count; ?></span></a>
                </li>
                <li class="nav-item">
                    <a href="schedule.php" class="nav-link"><i class="fas fa-calendar"></i> Schedule</a>
                </li>
                <li class="nav-item">
                    <a href="completeassignmen.php" class="nav-link"><i class="fas fa-check-circle"></i> Completed Assignments</a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <section id="assignments" class="content-section">
            <div class="section-header">
                <h1>Pending Assignments</h1>
                <div class="actions">
                    <button class='refresh-btn' onclick='refreshAssignments()'><i class='fas fa-sync'></i> Refresh</button>
                </div>
            </div>

            <div class='assignment-cards' id='assignmentList'>
                <?php while ($assignment = $assignments->fetch_assoc()) { ?>
                    <div class='assignment-card'>
                        <div class='assignment-header'>
                            <h3>Assignment <?php echo htmlspecialchars($assignment['assignment_id']); ?></h3>
                            <span class='assignment-date'><?php echo date('M d, Y', strtotime($assignment['assigned_at'])); ?></span>
                        </div>

                        <div class='assignment-details'>
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($assignment['customer_name']); ?></p>
                            <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($assignment['model_name']); ?></p>
                            <p><strong>Registration No:</strong> <?php echo htmlspecialchars($assignment['registration_no']); ?></p>
                            <p><strong>Start Date:</strong> <?php echo htmlspecialchars($assignment['start_date']); ?></p>
                            <p><strong>Pick-Up Time:</strong> <?php echo htmlspecialchars($assignment['pick_up_time']); ?></p>
                        </div>

                        <form method='post'>
                            <input type='hidden' name='assignment_id' value='<?php echo htmlspecialchars($assignment['assignment_id']); ?>'>
                            <button type='submit' name='complete_assignment' onclick='return confirm("Are you sure you want to mark this assignment as completed?");' class='action-btn'>Complete Assignment</button>
                        </form>
                    </div><?php } 

                    if ($assignments->num_rows === 0) {
                        echo '<div class=\'no-assignments\'>No pending assignments at the moment.</div>';
                    } ?>
            </div>
        </section>

        <section id='bookings' class='content-section'>
            <div class='section-header'>
                <h1>Available Bookings</h1>
            </div>

            <div class='booking-cards' id='bookingList'>
                <?php while ($booking = $bookingsResult->fetch_assoc()) { ?>
                    <div class='booking-card'>
                        <div class='booking-header'>
                            <h3>Booking <?php echo htmlspecialchars($booking['booking_id']); ?></h3>
                        </div>

                        <div class='booking-details'>
                            <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['model_name']); ?></p>
                            <p><strong>Registration No:</strong> <?php echo htmlspecialchars($booking['registration_no']); ?></p>
                            <p><strong>Pick-Up Location:</strong> <?php echo htmlspecialchars($booking['pick_up_location']); ?></p>
                            <p><strong>Start Date:</strong> <?php echo htmlspecialchars($booking['start_date']); ?></p>
                            <p><strong>Pick-Up Time:</strong> <?php echo htmlspecialchars($booking['pick_up_time']); ?></p>
                        </div>

                        <form method='post'>
                            <input type='hidden' name='booking_id' value='<?php echo htmlspecialchars($booking['booking_id']); ?>'>
                            <button type='submit' name='complete_assignment' onclick='return confirm("Are you sure you want to mark this booking as completed?");' class='action-btn'>Complete Booking</button>
                        </form>
                    </div><?php } 

                    if ($bookingsResult->num_rows === 0) {
                        echo '<div class=\'no-bookings\'>No available bookings at the moment.</div>';
                    } ?>
            </div>
        </section>
    </main>
</div>

</body>
</html>
