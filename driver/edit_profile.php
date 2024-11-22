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

    $stmt = $conn->prepare("SELECT name, contact_no, residence, age, driving_license_no, profile_picture,license_image FROM drivers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($name, $contact_no, $residence, $age, $driving_license_no, $profile_picture, $driving_license_image);
    $stmt->fetch();
    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_name = $_POST['name'];
        $new_contact_no = $_POST['contact_no'];
        $new_residence = $_POST['residence'];
        $new_age = $_POST['age'];
        $new_driving_license_no = $_POST['driving_license_no'];

        
        if ($_FILES['profile_picture']['name']) {
            $target_dir = "Driverprof/";
            $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = $target_file; 
            } else {
                echo "<p style='color: red;'>Sorry, there was an error uploading your profile picture.</p>";
            }
        } else {
            $profile_picture = $profile_picture; 
        }

        if ($_FILES['driving_license_image']['name']) {
            $license_dir = "Drivers/";
            $license_file = $license_dir . basename($_FILES["driving_license_image"]["name"]);
            if (move_uploaded_file($_FILES["driving_license_image"]["tmp_name"], $license_file)) {
                $driving_license_image = $license_file; 
            } else {
                echo "<p style='color: red;'>Sorry, there was an error uploading your driving license image.</p>";
            }
        } else {
            $driving_license_image = $driving_license_image;
        }

        $stmt = $conn->prepare("UPDATE drivers SET name = ?, contact_no = ?, residence = ?, age = ?, driving_license_no = ?, profile_picture = ?, license_image = ? WHERE email = ?");
        $stmt->bind_param("ssssssss", $new_name, $new_contact_no, $new_residence, $new_age, $new_driving_license_no, $profile_picture, $driving_license_image, $email);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Profile updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error updating profile: " . $stmt->error . "</p>";
        }
        
        $stmt->close();
    }
} else {
    echo "<p style='color: red;'>You are not logged in. Please log in to edit your profile.</p>";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Driver Profile</title>
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

        .profile-edit {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .profile-edit label {
            font-weight: bold;
            color: #003366;
            display: block;
            margin-top: 10px;
        }

        .profile-edit input[type="text"],
        .profile-edit input[type="file"],
        .profile-edit input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .profile-edit button {
            background-color: #0077b6;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }

        .profile-edit button:hover {
            background-color: #005f8c;
        }

        .profile-edit img {
            max-width: 150px;
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .dashboard-header {
            background-color: #005f8c;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .dashboard-header a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .dashboard-header a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<header>
    <h1>Edit Driver Profile</h1>
</header>

<div class="dashboard-header">
    <a href="dashboard.php">Go to Dashboard</a>
    <a href="viewprofile.php">View Profile</a>
</div>

<div class="profile-edit">
    <form action="" method="post" enctype="multipart/form-data">
        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label for="contact_no">Contact Number:</label>
        <input type="text" name="contact_no" id="contact_no" value="<?php echo htmlspecialchars($contact_no); ?>" required>

        <label for="residence">Residence:</label>
        <input type="text" name="residence" id="residence" value="<?php echo htmlspecialchars($residence); ?>" required>

        <label for="age">Age:</label>
        <input type="number" name="age" id="age" value="<?php echo htmlspecialchars($age); ?>" required>

        <label for="driving_license_no">Driving License Number:</label>
        <input type="text" name="driving_license_no" id="driving_license_no" value="<?php echo htmlspecialchars($driving_license_no); ?>" required>

        <label for="driving_license_image">Upload Driving License Image:</label>
        <input type="file" name="driving_license_image" id="driving_license_image">

        <label for="profile_picture">Upload Profile Picture:</label>
        <input type="file" name="profile_picture" id="profile_picture">

        <button type="submit">Update Profile</button>
    </form>
</div>

</body>
</html>
