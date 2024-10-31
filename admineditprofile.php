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
$query = "SELECT * FROM admins WHERE email_address='$email'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    echo "Error fetching admin data.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact_no = $_POST['contact_no'];
    $gender = $_POST['gender'];
    $profile_picture = $_FILES['profile_picture']['name'];

    if (!empty($profile_picture)) {
        $target_dir = "admin/"; 
        $target_file = $target_dir . basename($profile_picture);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            // File upload success
        } else {
            $error_message = "Error uploading file.";
        }
    } else {
        $target_file = $admin['profile_picture']; // Retain old profile picture if none uploaded
    }

    $sql = "UPDATE admins SET name=?, contact_no=?, gender=?, profile_picture=? WHERE email_address=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $contact_no, $gender, $target_file, $email);
    
    if ($stmt->execute()) {
        // Redirect to the view profile page after successful update
        header("Location: adminviewprofile.php");
        exit();
    } else {
        $error_message = "Error updating profile: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        header {
            background-color: #007BFF; 
            color: white;
            padding: 15px;
            text-align: center;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
        }
        div {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        select,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007BFF; /* Blue color for button */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
        img {
            margin-top: 10px;
        }
        .success-message {
            text-align: center;
            color: #007BFF; /* Blue color for success message */
        }
        .error-message {
            text-align: center;
            color: red;
        }
    </style>
        <link href="assets/img/p.png" rel="icon">
        <link href="assets/img/p.png" rel="apple-touch-icon">
</head>
<body>

<header>
    <h1>Edit Profile</h1>
    <nav>
        <a href="admin_dashboard.php" style="color: white; margin-right: 20px;">Dashboard</a>
        <a href="adminviewprofile.php" style="color: white;">View Profile</a>
    </nav>
</header>

<form action="" method="post" enctype="multipart/form-data">
    <h2>Edit Profile</h2>
    <?php if (isset($success_message)): ?>
        <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
    <?php elseif (isset($error_message)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    <div>
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
    </div>
    <div>
        <label>Contact No:</label>
        <input type="text" name="contact_no" value="<?php echo htmlspecialchars($admin['contact_no']); ?>" required>
    </div>
    <div>
        <label>Gender:</label>
        <select name="gender" required>
            <option value="male" <?php echo ($admin['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
            <option value="female" <?php echo ($admin['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
            <option value="other" <?php echo ($admin['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
        </select>
    </div>
    <div>
        <label>Profile Picture:</label>
        <input type="file" name="profile_picture">
        <img src="<?php echo htmlspecialchars($admin['profile_picture']); ?>" alt="Profile Picture" width="100">
    </div>
    <button type="submit">Update Profile</button>
</form>

</body>
</html>
