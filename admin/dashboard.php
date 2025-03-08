<?php
require 'include/db_connection.php';

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
$sql = "SELECT COUNT(*) AS available_vehicles 
        FROM vehicles 
        WHERE availability_status='available'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$available_vehicles = $row['available_vehicles'];

$sql = "SELECT COUNT(*) AS completed_bookings
        FROM bookings
        WHERE booking_status = 'completed'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$completed_bookings = $row['completed_bookings'];
$last_month_completed = 10; 
$percentage_change = ($completed_bookings - $last_month_completed) / $last_month_completed * 100;
$change_class = $percentage_change >= 0 ? 'positive' : 'negative';
$last_month_available = 23; 
$percentage_change = ($available_vehicles - $last_month_available) / $last_month_available * 100;
$change_class = $percentage_change >= 0 ? 'positive' : 'negative';
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin dashboard for Online Car Rental system">
    <title>Admin Dashboard - Admin Panel<</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="preload" href="assets/css/admindash.css" as="style">
    <link rel="preload" href="assets/js/admindash.js" as="script">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admindash.css">
</head>
<body>
    <header>
        <?php include('include/header.php') ?>
    </header>

    <?php include('include/sidebar.php') ?>

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

                <article class="card">
    <div class="card-content">
        <h3>Available Vehicles</h3>
        <p class="card-value"><?php echo number_format($available_vehicles, 0); ?></p>
        <p class="card-change <?php echo $change_class; ?>">
            <?php echo number_format($percentage_change, 2); ?>% from last month
        </p>
    </div>
    <div class="card-icon">
        <i class="fas fa-KSH-sign">NUM</i>
    </div>
</article>
<article class="card">
    <div class="card-content">
        <h3>Completed Bookings</h3>
        <p class="card-value"><?php echo number_format($completed_bookings, 0); ?></p>
        <p class="card-change <?php echo $change_class; ?>">
            <?php echo number_format($percentage_change, 2); ?>% from last month
        </p>
    </div>
    <div class="card-icon">
        <i class="fas fa-KSH-sign">NUM</i>
    </div>
</article>
              
            </div>
        </main>
    </div>

    <script src="assets/js/admindash.js" defer></script>
</body>
</html>