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


$pendingCountQuery = "SELECT COUNT(*) as pending_count FROM deliveries WHERE driver_id='$driver_id' AND status='pending'";
$pendingCountResult = $conn->query($pendingCountQuery);
$pending_count = ($pendingCountResult && $pendingCountResult->num_rows > 0) ? $pendingCountResult->fetch_assoc()['pending_count'] : 0;


$pendingDeliveriesQuery = "
    SELECT d.*, c.full_name as customer_name, v.model_name as vehicle_model 
    FROM deliveries d
    JOIN customers c ON d.customer_id = c.id
    JOIN vehicles v ON d.vehicle_id = v.vehicle_id
    WHERE d.driver_id='$driver_id' AND d.status='pending'
    ORDER BY d.delivery_date ASC
";
$deliveries = $conn->query($pendingDeliveriesQuery);

// Fetch completed deliveries
$completedDeliveriesQuery = "
    SELECT d.*, c.full_name as customer_name, v.model_name as vehicle_model 
    FROM deliveries d
    JOIN customers c ON d.customer_id = c.id
    JOIN vehicles v ON d.vehicle_id = v.vehicle_id
    WHERE d.driver_id='$driver_id' AND d.status='completed'
    ORDER BY d.completion_date DESC
    LIMIT 10
";
$completed = $conn->query($completedDeliveriesQuery);

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - Car Rental System</title>
    <link rel="stylesheet" href="assets/css/driverdash.css">
    <script src="assets/js/driverdash.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    
    <script>
        window.onload = function() {
          
            if (!<?php echo isset($_SESSION['driver_id']) ? 'true' : 'false'; ?>) {
                window.location.href = "Driver_login.php";
            }
        };
    </script>
</head>

<body>
<header class="header" role="banner">
    <div class="header-content">
        <h1>
            <i class="fas fa-car"></i>
            Drivers' Dashboard
        </h1>
        <div class="profile" role="navigation" aria-label="User menu">
            <button class="profile-button" aria-expanded="false" aria-controls="profile-menu">
            <div class="profile-picture">
    <img id="profile-img" src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Driver profile picture" loading="lazy" width="40" height="40">
</div>
                <span class="profile-name"><?php echo htmlspecialchars($driver['name']); ?></span>
            </button>
            <div id="profile-menu" class="profile-dropdown" hidden>
                <nav>
                    <ul>
                        <li><a href="driverviewprofile.php"><i class="fas fa-user"></i> View Profile</a></li>
                        <li><a href="driver_edit_profile.php"><i class="fas fa-edit"></i> Edit Profile</a></li>
                        <li><a href="driverchangepassword.php"><i class="fas fa-key"></i> Change Password</a></li>
                        <li><a href="driverlogout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</header>

<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
        <nav>
        

            <ul class="nav-menu">
            <li class="nav-item">
                    <a href="driverdashboard.php" class="nav-link" data-section="schedule">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link active" data-section="deliveries">
                        <i class="fas fa-truck"></i>
                        Pending Deliveries
                        <span class="badge"><?php echo $pending_count; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="schedule">
                        <i class="fas fa-calendar"></i>
                        Schedule
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="completed">
                        <i class="fas fa-check-circle"></i>
                        Completed Deliveries
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="driverlogout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <section id="deliveries" class="content-section">
            <div class="section-header">
                <h1>Pending Deliveries</h1>
                <div class="actions">
                    <button class="refresh-btn" onclick="refreshDeliveries()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="delivery-cards" id="deliveryList">
                <?php
                while ($delivery = $deliveries->fetch_assoc()) {
                    ?>
                    <div class="delivery-card">
                        <div class="delivery-header">
                            <h3>Delivery #<?php echo htmlspecialchars($delivery['id']); ?></h3>
                            <span class="delivery-date">
                                <?php echo date('M d, Y', strtotime($delivery['delivery_date'])); ?>
                            </span>
                        </div>
                        <div class="delivery-details">
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($delivery['customer_name']); ?></p>
                            <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($delivery['vehicle_model']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($delivery['delivery_address']); ?></p>
                        </div>
                        <div class="delivery-actions">
                            <button onclick="startDelivery(<?php echo $delivery['id']; ?>)" class="action-btn">
                                Start Delivery
                            </button>
                            <button onclick="viewRoute(<?php echo $delivery['id']; ?>)" class="action-btn secondary">
                                View Route
                            </button>
                        </div>
                    </div>
                    <?php
                }
                if ($deliveries->num_rows === 0) {
                    echo '<div class="no-deliveries">No pending deliveries at the moment.</div>';
                }
                ?>
            </div>
        </section>

        <section id="schedule" class="content-section" style="display: none;">
            <h1>Delivery Schedule</h1>
            <div id="calendar"></div>
        </section>

        <section id="completed" class="content-section" style="display: none;">
            <h1>Completed Deliveries</h1>
            <div class="completed-list" id="completedDeliveries">
                <?php
                $stmt = $conn->prepare("
                    SELECT d.*, c.name as customer_name, v.model as vehicle_model 
                    FROM deliveries d
                    JOIN customers c ON d.customer_id = c.id
                    JOIN vehicles v ON d.vehicle_id = v.id
                    WHERE d.driver_id = ? AND d.status = 'completed'
                    ORDER BY d.completion_date DESC
                    LIMIT 10
                ");
                $stmt->bind_param("i", $driver_id);
                $stmt->execute();
                $completed = $stmt->get_result();

                while ($delivery = $completed->fetch_assoc()) {
                    ?>
                    <div class="completed-card">
                        <div class="completed-header">
                            <h3>Delivery #<?php echo htmlspecialchars($delivery['id']); ?></h3>
                            <span class="completed-date">
                                <?php echo date('M d, Y', strtotime($delivery['completion_date'])); ?>
                            </span>
                        </div>
                        <div class="completed-details">
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($delivery['customer_name']); ?></p>
                            <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($delivery['vehicle_model']); ?></p>
                            <p><strong>Status:</strong> Completed</p>
                        </div>
                    </div>
                    <?php
                }
                if ($completed->num_rows === 0) {
                    echo '<div class="no-completed">No completed deliveries at the moment.</div>';
                }
                ?>
            </div>
        </section>

        <section id="profile" class="content-section" style="display: none;">
            <h1>Your Profile</h1>
            <div class="profile-info">
                <img src="<?php echo htmlspecialchars($driver['profile_photo'] ?? '/api/placeholder/40/40'); ?>" alt="Profile Picture">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($driver['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($driver['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($driver['phone']); ?></p>
            </div>
        </section>
    </main>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: [
            
        ],
        dateClick: function(info) {
            alert('Date: ' + info.dateStr);
        }
    });
    calendar.render();
});


function refreshDeliveries() {
    
}


function startDelivery(deliveryId) {
   
}


function viewRoute(deliveryId) {
    
}
</script>

</body>
</html>
