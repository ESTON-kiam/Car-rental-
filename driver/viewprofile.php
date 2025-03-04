<?php
require_once 'include/db_connection.php';

if (isset($_SESSION['driver_email'])) {
    $email = $_SESSION['driver_email'];

    $stmt = $conn->prepare("SELECT name, contact_no, email, residence, age, driving_license_no, license_image, profile_picture FROM drivers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($name, $contact_no, $email, $residence, $age, $driving_license_no, $license_image, $profile_picture);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "<p style='color: red;'>You are not logged in. Please log in to view your profile.</p>";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Profile</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/driverviewprofile.css">
</head>
<body>

<header>
    <h1>Driver Profile</h1>
</header>

<div class="profile">
    <img src="<?php echo htmlspecialchars($license_image); ?>" alt="License Image">
    <img class="profile-picture" src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
    <label>Name:</label>
    <p><?php echo htmlspecialchars($name); ?></p>
    <label>Contact Number:</label>
    <p><?php echo htmlspecialchars($contact_no); ?></p>
    <label>Email:</label>
    <p><?php echo htmlspecialchars($email); ?></p>
    <label>Residence:</label>
    <p><?php echo nl2br(htmlspecialchars($residence)); ?></p>
    <label>Age:</label>
    <p><?php echo htmlspecialchars($age); ?></p>
    <label>Driving License Number:</label>
    <p><?php echo htmlspecialchars($driving_license_no); ?></p>
</div>

<a href="http://localhost:8000/driver/edit_profile.php">Edit Profile</a>

</body>
</html>
