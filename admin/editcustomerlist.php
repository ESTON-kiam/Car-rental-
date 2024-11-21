<?php
session_name('admin_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => false, 
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

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


$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = "SELECT * FROM customers WHERE id = $customerId";
$result = $conn->query($query);
$customer = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $fullName = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $mobile = $conn->real_escape_string($_POST['mobile']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $occupation = $conn->real_escape_string($_POST['occupation']);
    $residence = $conn->real_escape_string($_POST['residence']);
    
    $updateQuery = "UPDATE customers SET full_name='$fullName', email='$email', mobile='$mobile', gender='$gender', dob='$dob', occupation='$occupation', residence='$residence' WHERE id=$customerId";
    
    if ($conn->query($updateQuery) === TRUE) {
        echo "<p style='color: green;'>Customer details updated successfully.</p>";
        header("Location: customerlist.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer - Admin Dashboard</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            margin: 0;
        }
        h2 {
            color: #333;
            text-align: center;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; 
        }
        button {
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3; 
        }
        p {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h2>Edit Customer Details</h2>

<form action="" method="post">
    <label for="full_name">Full Name:</label>
    <input type="text" name="full_name" value="<?php echo htmlspecialchars($customer['full_name']); ?>" required>
    
    <label for="email">Email:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
    
    <label for="mobile">Mobile:</label>
    <input type="text" name="mobile" value="<?php echo htmlspecialchars($customer['mobile']); ?>" required>
    
    <label for="gender">Gender:</label>
    <select name="gender" required>
        <option value="Male" <?php if ($customer['gender'] == 'Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if ($customer['gender'] == 'Female') echo 'selected'; ?>>Female</option>
        <option value="Other" <?php if ($customer['gender'] == 'Other') echo 'selected'; ?>>Other</option>
    </select>
    
    <label for="dob">Date of Birth:</label>
    <input type="date" name="dob" value="<?php echo htmlspecialchars($customer['dob']); ?>" required>
    
    <label for="occupation">Occupation:</label>
    <input type="text" name="occupation" value="<?php echo htmlspecialchars($customer['occupation']); ?>" required>
    
    <label for="residence">Residence:</label>
    <input type="text" name="residence" value="<?php echo htmlspecialchars($customer['residence']); ?>" required>
    
    <button type="submit">Update Customer</button>
</form>

</body>
</html>

<?php $conn->close(); ?>
