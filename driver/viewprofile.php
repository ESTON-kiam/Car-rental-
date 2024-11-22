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

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental_management";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


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
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #eaf2f8;
        }

        header {
            background-color: #0077b6;
            color: white;
            padding: 1rem;
            text-align: center;
        }

        h1 {
            margin: 0;
        }

        .profile {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .profile label {
            font-weight: bold;
            color: #003366;
            display: block;
            margin-top: 10px;
        }

        .profile img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .profile p {
            margin: 5px 0;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 2px solid #0077b6;
            object-fit: cover;
            margin-bottom: 20px;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #0077b6;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        a:hover {
            background-color: #005f8c;
        }
    </style>
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
