<?php
require_once 'include/db_connection.php';

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
    <link rel="stylesheet" href="assets/css/drivereditprofile.css">
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
