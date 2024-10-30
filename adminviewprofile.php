<?php
session_name('admin_session');
session_set_cookie_params(1800); 
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: Admin_login.php");
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
$query = "SELECT name, contact_no, gender, profile_picture FROM admins WHERE email_address='$email'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $name = $admin['name'];
    $contact_no = $admin['contact_no'];
    $gender = $admin['gender'];
    $profile_picture = $admin['profile_picture'] ?: 'default-profile.png'; 
} else {
    $name = "Admin"; 
    $contact_no = "N/A";
    $gender = "N/A";
    $profile_picture = 'default-profile.png'; 
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
        }
        header h1 {
            margin: 0;
            display: inline;
        }
        header nav {
            display: inline;
            float: right;
        }
        header nav a {
            color: #fff;
            text-decoration: none;
            margin-left: 15px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
        h2 {
            margin-top: 0;
        }
    </style>
        <link href="assets/img/p.png" rel="icon">
        <link href="assets/img/p.png" rel="apple-touch-icon">
</head>
<body>

<header>
    <h1>Admin Profile</h1>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
    </nav>
</header>

<div class="container">
    <h2>Profile Information</h2>
    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-picture">
    <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
    <p><strong>Contact No:</strong> <?php echo htmlspecialchars($contact_no); ?></p>
    <p><strong>Gender:</strong> <?php echo htmlspecialchars($gender); ?></p>
</div>

</body>
</html>
