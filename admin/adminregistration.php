<?php
require 'include/db_connection.php';

$email = $_SESSION['email'];
$query = "SELECT role FROM admins WHERE email_address='$email'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    if ($admin['role'] !== 'superadmin') {
        $_SESSION['message'] = "Only superadmins can register new admins";
        $_SESSION['message_type'] = "error";
        header("Location: dashboard.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration Form</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/adminregistration.css">
</head>
<body>
    <?php include('include/header.php') ?>
    <?php include('include/sidebar.php')?>
    <main class="main-content">
        <div class="container">
            <h2>Admin Registration</h2>
            <form action="registartion.php" method="POST">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" placeholder="Enter your name" required>

                <label for="contact_no">Contact Number:</label>
                <input type="text" id="contact_no" name="contact_no" placeholder="Enter your contact number" required>

                <label for="email_address">Email Address:</label>
                <input type="email" id="email_address" name="email_address" placeholder="Enter your email" required>

                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <label for="role">Role:</label> 
                <select id="role" name="role" required>
                <option value="">Select Role</option>
                <option value="superadmin">SuperAdmin</option>
                <option value="admin">Admin</option>
                </select>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
                <button type="submit">Register</button>
            </form>
        </div>
    </main>
</body>
</html>