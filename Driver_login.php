<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('driver_session');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost"; 
    $username = "root"; 
    $password = ""; 
    $dbname = "car_rental_management"; 

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $checkUserQuery = "SELECT driver_id, password FROM drivers WHERE email = '$email'";
    $result = $conn->query($checkUserQuery);

    if ($result === false) {
        echo "Error in query: " . $conn->error;
    } else if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['driver_id'] = $row['driver_id']; 
            $_SESSION['driver_email'] = $email;
            header("Location: driverdashboard.php");
            exit();
        } else {
            echo "<p style='color: red;'>Incorrect password. Please try again.</p>";
        }
    } else {
        echo "<p style='color: red;'>No account found with that email. Please check your email.</p>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Login</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="wrapper">
        <form action="" method="POST">
            <h2>Driver Login</h2>
            <div class="input-field">
                <input type="email" id="email" name="email" required>
                <label for="email">Enter Your Email</label>
            </div>
            <div class="input-field">
                <input type="password" id="password" name="password" required>
                <label for="password">Enter your password</label>
            </div>
            <div class="forget">
                <label for="remember">
                    <input type="checkbox" id="remember">
                    <p>Remember me</p>
                </label>
                <a href="driverforgotpassword.php">Forgot password?</a>
            </div>
            <button type="submit">Log In</button>
            <div class="register">
            <p>Return to Home? <a href="index.html">Return To Home Page</a></p>
            </div>
        </form>
    </div>
</body>
</html>
