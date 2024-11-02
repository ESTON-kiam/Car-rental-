<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('customer_session');
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "car_rental_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM customers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_email'] = $user['email'];
            $_SESSION['customer_name'] = $user['name'];
            
            if (isset($_POST['remember'])) {
                setcookie('customer_login', $user['email'], time() + (86400 * 30), "/");
            }
            
            header("Location: customer_dashboard.php");
            exit();
        } else {
            header("Location: login.php?error=Invalid email or password");
            exit();
        }
    } else {
        header("Location: login.php?error=Invalid email or password");
        exit();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="wrapper">
        <form action="" method="POST">
            <h2>Customer Login</h2>
            <div class="input-field">
                <input type="text" id="email" name="email" required>
                <label for="email">Enter Your Email</label>
            </div>
            <div class="input-field">
                <input type="password" id="password" name="password" required>
                <label for="password">Enter your password</label>
            </div>
            <div class="forget">
                <label for="remember">
                    <input type="checkbox" id="remember" name="remember">
                    <p>Remember me</p>
                </label>
                <a href="#">Forgot password?</a>
            </div>
            <button type="submit">Log In</button>
            <div class="register">
                <p>Don't have an account? <a href="customer_registration.html">Register</a></p>
            </div>
        </form>
    </div>
</body>
</html>