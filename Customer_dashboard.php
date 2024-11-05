<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('customer_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => false, 
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();


if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php"); 
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


$customer_id = $_SESSION['customer_id'];
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows > 0) {
    $customer_details = $result->fetch_assoc();
} else {
    die("Customer not found.");
}


$full_name = $customer_details['full_name'];
$email = $customer_details['email'];
$mobile = $customer_details['mobile'];
$gender = $customer_details['gender'];
$dob = $customer_details['dob'];
$occupation = $customer_details['occupation'];
$residence = $customer_details['residence'];
$profile_picture = $customer_details['profile_picture'] ?? 'path/to/default-profile-picture.jpg';

date_default_timezone_set('Africa/Nairobi');

function getGreeting() {
    $currentHour = (int)date('H');
    
    
    error_log("Current hour in Nairobi: " . $currentHour);
    
    if ($currentHour >= 5 && $currentHour < 12) {
        return "Good Morning";
    } elseif ($currentHour >= 12 && $currentHour < 16) {
        return "Good Afternoon";
    } elseif ($currentHour >= 16 && $currentHour < 19) {
        return "Good Evening";
    } else {
        return "Good Night";
    }
}


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$greeting = getGreeting();
$currentDateTime = date('Y-m-d H:i:s');
$currentDay = date('l');


$debug = [
    'Timezone' => date_default_timezone_get(),
    'Current Time' => $currentDateTime,
    'Current Hour' => date('H'),
    'Day' => $currentDay,
    'Greeting' => $greeting
];



$active_bookings = 2; 
$total_bookings = 15; 
$loyalty_points = 450; 
$upcoming_booking = [
    'car' => 'Toyota Camry',
    'date' => '2024-11-05',
    'status' => 'Confirmed'
];


$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Online Car Rental</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/customerdash.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <i class="fas fa-car"></i>
            <span>Car Rental</span>
        </div>
        <nav>
            <div class="nav-item">
                <a href="customer_dashboard.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="book_car.php" class="nav-link">
                    <i class="fas fa-car-side"></i>
                    <span>Book a Car</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="booking_history.php" class="nav-link">
                    <i class="fas fa-history"></i>
                    <span>Booking History</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="payment_history.php" class="nav-link">
                    <i class="fas fa-wallet"></i>
                    <span>Payment History</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="stk_payment.php" class="nav-link">
                    <i class="fas fa-mobile-alt"></i>
                    <span>STK Payment</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="support.php" class="nav-link">
                    <i class="fas fa-life-ring"></i>
                    <span>Support</span>
                </a>
            </div>
        </nav>
    </aside>

    <header class="header">
        <div class="header-content">
            <div class="search-bar">
                <input type="text" placeholder="Search...">
            </div>
            <div class="profile">
                <button class="profile-button" onclick="toggleDropdown()">
                    <div class="profile-picture">
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile">
                    </div>
                    <span class="profile-name"><strong><?php echo htmlspecialchars($full_name); ?></strong></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="profile-dropdown">
                    <a href="customerviewprofile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>View Profile</span>
                    </a>
                    <a href="customereditprofile.php" class="dropdown-item">
                        <i class="fas fa-edit"></i>
                        <span>Edit Profile</span>
                    </a>
                    <a href="customerchangepassword.php" class="dropdown-item">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="customerlogout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="welcome-message">
            <h1><?php echo $greeting . ', ' . htmlspecialchars($full_name) . '!'; ?></h1> <!-- Greeting message -->
            <p>Here's an overview of your rental activity</p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">Active Bookings</h3>
                    <i class="fas fa-car text-primary"></i>
                </div>
                <div class="card-value"><?php echo $active_bookings; ?></div>
                <div class="card-subtitle">Out of <?php echo $total_bookings; ?> total bookings</div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">Loyalty Points</h3>
                    <i class="fas fa-star text-warning"></i>
                </div>
                <div class="card-value"><?php echo $loyalty_points; ?></div>
                <div class="card-subtitle">Points available for redemption</div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">Upcoming Booking</h3>
                    <i class="fas fa-calendar text-success"></i>
                </div>
                <div class="card-value"><?php echo $upcoming_booking['car']; ?></div>
                <div class="card-subtitle">
                    <?php echo date('F j, Y', strtotime($upcoming_booking['date'])); ?>
                    <span class="status-badge status-confirmed"><?php echo $upcoming_booking['status']; ?></span>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="quick-actions">
                <form method="POST" action="book_car.php">
                    <input type="hidden" name="vehicle_id" value="<?php echo $row['vehicle_id']; ?>">
                    <button type="submit" class="action-button">
                        <i class="fas fa-car-side"></i>
                        <span>Book Now</span>
                    </button>
                </form>

                <button class="action-button">
                    <i class="fas fa-history"></i>
                    <span>View History</span>
                </button>
                <button class="action-button">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Quick Pay</span>
                </button>
                <button class="action-button">
                    <i class="fas fa-life-ring"></i>
                    <span>Get Help</span>
                </button>
            </div>
        </div>
    </main>

    <script src="assets/js/customerdash.js" defer></script>
</body>
</html>