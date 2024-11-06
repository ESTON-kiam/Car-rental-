<?php
session_name('admin_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => false, 
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: admin_login.php");
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

$email = $_SESSION['email'];
$query = "SELECT name, profile_picture FROM admins WHERE email_address='$email'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $name = $admin['name'];
    $profile_picture = $admin['profile_picture'];
} else {
    $name = "Admin"; 
    $profile_picture = 'default-profile.png'; 
}

$totalCustomersQuery = "SELECT COUNT(*) as total FROM customers";
$totalCustomersResult = $conn->query($totalCustomersQuery);

if ($totalCustomersResult) {
    $totalCustomers = $totalCustomersResult->fetch_assoc()['total'];
} else {
    $totalCustomers = 0; 
}


$vehicleQuery = "SELECT COUNT(*) as total_vehicles FROM vehicles";
$vehicleResult = $conn->query($vehicleQuery);
$totalVehicles = 0;

if ($vehicleResult) {
    $vehicleData = $vehicleResult->fetch_assoc();
    $totalVehicles = $vehicleData['total_vehicles'];
}
$bookingQuery = "SELECT COUNT(*) as total_bookings FROM bookings";
$bookingResult = $conn->query($bookingQuery);
$totalBookings = 0;

if ($bookingResult) {
    $bookingData = $bookingResult->fetch_assoc();
    $totalBookings = $bookingData['total_bookings'];
}

$conn->close();





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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin dashboard for Online Car Rental system">
    <title>Admin Dashboard - Online Car Rental</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="preload" href="assets/css/admindash.css" as="style">
    <link rel="preload" href="assets/js/admindash.js" as="script">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admindash.css">
</head>
<body>
    <header class="header" role="banner">
        <div class="header-content">
            <button id="menu-toggle" class="menu-toggle" aria-label="Toggle navigation menu">
                <i class="fas fa-bars"></i>
            </button>
            <h1>
                <i class="fas fa-car"></i>
                Online Car Rental
            </h1>
            <div class="profile" role="navigation" aria-label="User menu">
                <button class="profile-button" aria-expanded="false" aria-controls="profile-menu">
                    <div class="profile-picture">
                        <img id="profile-img" src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Admin profile picture" loading="lazy" width="40" height="40">
                    </div>
                    <span class="profile-name"><?php echo htmlspecialchars($name); ?></span>
                </button>
                <div id="profile-menu" class="profile-dropdown" hidden>
                    <nav>
                        <ul>
                            <li><a href="adminviewprofile.php"><i class="fas fa-user"></i> View Profile</a></li>
                            <li><a href="admineditprofile.php"><i class="fas fa-edit"></i> Edit Profile</a></li>
                            <li><a href="adminchangepassword.php"><i class="fas fa-key"></i> Change Password</a></li>
                            <li><a href="adminlogout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <nav class="sidebar" role="navigation" aria-label="Main navigation">
            <div class="sidebar-content">
                <ul>
                    <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li><a href="add_vehicles.php"><i class="fas fa-car-side"></i><span>Add Vehicle</span></a></li>
                    <li>
                        <button class="dropdown-toggle" aria-expanded="false" aria-controls="staff-menu">
                            <i class="fas fa-users"></i><span>Add Staff</span><i class="fas fa-chevron-right"></i>
                        </button>
                        <ul id="staff-menu" class="dropdown" hidden>
                            <li><a href="driverreg.php"><i class="fas fa-id-card"></i> Driver</a></li>
                            <li><a href="add_employee.html"><i class="fas fa-user-tie"></i> Employee</a></li>
                            <li><a href="adminreg.html"><i class="fas fa-id-card"></i>Add Admin</a></li>
                        </ul>
                    </li>
                    <li>
                        <button class="dropdown-toggle" aria-expanded="false" aria-controls="staff-menu">
                            <i class="fas fa-users"></i><span>List</span><i class="fas fa-chevron-right"></i>
                        </button>
                        <ul id="staff-menu" class="dropdown" hidden>
                            <li><a href="customerlist.php"><i class="fas fa-id-card"></i> Customers List</a></li>
                            <li><a href="add_employee.html"><i class="fas fa-user-tie"></i> Employee List</a></li>
                            <li><a href="driverslist.php"><i class="fas fa-id-card"></i>Driver List</a></li>
                        </ul>
                    </li>
                    <li><a href="carbookings.php"><i class="fas fa-book"></i><span>Car Bookings</span></a></li>
                    <li>
                        <button class="dropdown-toggle" aria-expanded="false" aria-controls="payment-menu">
                            <i class="fas fa-money-bill-wave"></i><span>Payment History</span><i class="fas fa-chevron-right"></i>
                        </button>
                        <ul id="payment-menu" class="dropdown" hidden>
                            <li><a href="all_payments.html"><i class="fas fa-list"></i> All Payments</a></li>
                            <li><a href="pending_payments.html"><i class="fas fa-clock"></i> Pending Payments</a></li>
                            <li><a href="cancelled_payments.html"><i class="fas fa-times-circle"></i> Cancelled Payments</a></li>
                            <li><a href="successful_payments.html"><i class="fas fa-check-circle"></i> Successful Payments</a></li>
                        </ul>
                    </li>
                    <li><a href="carcollection.php"><i class="fas fa-users"></i><span>Car collection</span></a></li>
                </ul>
            </div>
        </nav>

        <main class="main-content">
            <div class="dashboard-greeting">
                <h2>
                    <strong class="greeting-name"><?php echo $greeting; ?>, Welcome Back to the Admin Dashboard, <?php echo htmlspecialchars($name); ?></strong>
                </h2>
                <p>This is your dashboard where you can manage your car rental business.</p>
            </div>

            <div class="dashboard-cards">
            <article class="card">
                    <div class="card-content">
                        <h3>Total Bookings</h3>
                        <p class="card-value"><?php echo number_format($totalBookings); ?></p>
                        <p class="card-change positive">+12% from last month</p>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-car"></i>
                    </div>
                </article>
             
                <article class="card">
                    <div class="card-content">
                        <h3>Total Vehicles</h3>
                        <p class="card-value"><?php echo $totalVehicles; ?></p>
                        <p class="card-change positive">+17% from last month</p>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-car-side"></i>
                    </div>
                </article>

                
                <article class="card">
    <div class="card-content">
        <h3>Total Customers</h3>
        <p class="card-value"><?php echo $totalCustomers; ?></p>
        <p class="card-change positive">+13% from last month</p>
    </div>
    <div class="card-icon">
        <i class="fas fa-user"></i> 
    </div>
</article>

                <article class="card">
                    <div class="card-content">
                        <h3>Total Revenue</h3>
                        <p class="card-value">KSH50,000</p>
                        <p class="card-change positive">+15% from last month</p>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-KSH-sign">KSH</i>
                    </div>
                </article>
                </a>
            </div>
        </main>
    </div>

    <script src="assets/js/admindash.js" defer></script>
</body>
</html>
