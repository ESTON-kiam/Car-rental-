<?php


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: http://localhost:8000/admin/");
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
                            <li><a href="viewprofile.php"><i class="fas fa-user"></i> View Profile</a></li>
                            <li><a href="editprofile.php"><i class="fas fa-edit"></i> Edit Profile</a></li>
                            <li><a href="changepassword.php"><i class="fas fa-key"></i> Change Password</a></li>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <script src="assets/js/admindash.js" defer></script>
    </header>