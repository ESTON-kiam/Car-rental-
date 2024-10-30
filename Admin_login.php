<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="wrapper">
        <form action="" method="post">
            <h2>Admin Login</h2>
            <div class="input-field">
                <input type="text" id="email" name="email" required>
                <label for="">Enter Your Email</label>
            </div>
            <div class="input-field">
                <input type="password" id="password" name="password" required>
                <label>Enter your password</label>
            </div>
            <div class="forget">
                <label for="remember">
                    <input type="checkbox" id="remember">
                    <p>Remember me</p>
                </label>
                <a href="#">Forgot password?</a>
            </div>
            <button type="submit">Log In</button>
            <div class="register">
                <p>Return to Home? <a href="index.html">Return To Home Page</a></p>
            </div>
        </form>
    </div>

    <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection settings
    $servername = "localhost"; 
    $username = "root"; 
    $password = ""; 
    $dbname = "car_rental_management"; 

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Escape user inputs for security
    $email = $conn->real_escape_string(trim($_POST['email']));
    $passwordInput = trim($_POST['password']);

    // Prepare the query to prevent SQL injection
    $checkUserQuery = "SELECT password FROM admins WHERE email_address='$email'";
    $result = $conn->query($checkUserQuery);

    // Check if the query was successful
    if ($result === false) {
        echo "Error in query: " . $conn->error;
    } else if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        // Verify password
        if (password_verify($passwordInput, $hashedPassword)) {
            
            session_name('admin_session'); 
            session_start(); 

            
            $_SESSION['loggedin'] = true; 
            $_SESSION['email'] = $email;   

            // Redirect to admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "<p style='color: red;'>Incorrect password. Please try again.</p>";
        }
    } else {
        echo "<p style='color: red;'>Email not found. Please check your email.</p>";
    }

    // Close connection
    $conn->close();
}
?>

</body>
</html>
