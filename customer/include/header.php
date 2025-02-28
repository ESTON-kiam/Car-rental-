
<?php


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

$sql_active_or_pending = "SELECT COUNT(*) AS active_or_pending_bookings FROM bookings WHERE customer_id = ? AND booking_status IN ('active', 'pending')";
$sql_total = "SELECT COUNT(*) AS total_bookings FROM bookings WHERE customer_id = ?";

$stmt_active_or_pending = $conn->prepare($sql_active_or_pending);
$stmt_active_or_pending->bind_param('i', $customer_id);
$stmt_active_or_pending->execute();
$result_active_or_pending = $stmt_active_or_pending->get_result();
$active_or_pending_booking_data = $result_active_or_pending->fetch_assoc();
$active_bookings = $active_or_pending_booking_data['active_or_pending_bookings'];

$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param('i', $customer_id);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_booking_data = $result_total->fetch_assoc();
$total_bookings = $total_booking_data['total_bookings'];

$loyalty_points = $total_bookings * 10;
$sql_upcoming = "SELECT model_name AS car, booking_date AS date, booking_status AS status
                 FROM bookings 
                 WHERE customer_id = ? 
                 AND (booking_status = 'active' OR booking_status = 'pending') 
                 ORDER BY booking_date ASC LIMIT 1"; 

$stmt_upcoming = $conn->prepare($sql_upcoming);
$stmt_upcoming->bind_param('i', $customer_id);
$stmt_upcoming->execute();
$result_upcoming = $stmt_upcoming->get_result();

if ($result_upcoming->num_rows > 0) {
    $upcoming_booking = $result_upcoming->fetch_assoc();
} else {
    $upcoming_booking = null; 
}
$conn->close();
?><head>
<link rel="stylesheet" href="assets/css/customerdash.css"></head>
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
                    <span class="profile-name">
    <strong><span style="display: inline-block; width: 10px; height: 10px; background-color: green; border-radius: 50%; margin-left: 5px; vertical-align: middle;"></span><?php echo htmlspecialchars($full_name); ?></strong>
    <?php if (isset($_SESSION['customer_id'])): ?>
        
    <?php endif; ?>
</span>

                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="profile-dropdown">
                    <a href="viewprofile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>View Profile</span>
                    </a>
                    <a href="editprofile.php" class="dropdown-item">
                        <i class="fas fa-edit"></i>
                        <span>Edit Profile</span>
                    </a>
                    <a href="changepassword.php" class="dropdown-item">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>
    <script src="assets/js/customerdash.js" defer></script>