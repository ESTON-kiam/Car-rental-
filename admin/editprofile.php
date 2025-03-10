<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('admin_session');
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => true, 
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

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    
    $email = filter_var($_SESSION['email'], FILTER_SANITIZE_EMAIL);

    $query = "SELECT * FROM admins WHERE email_address = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
    } else {
        throw new Exception("Admin data not found");
    }
    $stmt->close();

    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $contact_no = filter_input(INPUT_POST, 'contact_no', FILTER_SANITIZE_STRING);
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);

        
        $profile_picture = $admin['profile_picture']; 
        
        if (!empty($_FILES['profile_picture']['name'])) {
            $target_dir = "adminprof/"; 
            
           
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_file_size = 5 * 1024 * 1024; 
            $file_type = mime_content_type($_FILES['profile_picture']['tmp_name']);
            $file_size = $_FILES['profile_picture']['size'];

            if (in_array($file_type, $allowed_types) && $file_size <= $max_file_size) {
                $unique_filename = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
                $target_file = $target_dir . $unique_filename;

                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                    $profile_picture = $target_file;
                } else {
                    $error_message = "Error uploading file.";
                }
            } else {
                $error_message = "Invalid file type or size.";
            }
        }

        $sql = "UPDATE admins SET name = ?, contact_no = ?, gender = ?, profile_picture = ? WHERE email_address = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $contact_no, $gender, $profile_picture, $email);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Profile updated successfully";
            header("Location: viewprofile.php");
            exit();
        } else {
            $error_message = "Error updating profile: " . $stmt->error;
        }
        
        $stmt->close();
    }
} catch (Exception $e) {
    
    error_log($e->getMessage());
    $_SESSION['error_message'] = "An error occurred. Please try again.";
    header("Location: error.php");
    exit();
} finally {
    
    if (isset($conn)) {
        $conn->close();
    }
}

$name = htmlspecialchars($admin['name'] ?? '');
$contact_no = htmlspecialchars($admin['contact_no'] ?? '');
$gender = htmlspecialchars($admin['gender'] ?? '');
$profile_picture = htmlspecialchars($admin['profile_picture'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Edit Profile-Admin Panel</title>
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
            background-color: #007BFF; 
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        img {
            margin-top: 10px;
            max-width: 200px;
            max-height: 200px;
        }
        .success-message {
            text-align: center;
            color: #007BFF; 
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
    <?php include('include/header.php')?>
</header>
<?php include('include/sidebar.php') ?>
<main class="main-content">
<form action="" method="post" enctype="multipart/form-data">
    <h2>Edit Profile</h2>
    
    <?php 
    
    if (isset($_SESSION['success_message'])) {
        echo '<p class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</p>';
        unset($_SESSION['success_message']);
    }
    
    if (isset($error_message)) {
        echo '<p class="error-message">' . htmlspecialchars($error_message) . '</p>';
    }
    ?>
    
    <div>
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo $name; ?>" required>
    </div>
    <div>
        <label>Contact No:</label>
        <input type="text" name="contact_no" value="<?php echo $contact_no; ?>" required>
    </div>
    <div>
        <label>Gender:</label>
        <select name="gender" required>
            <option value="male" <?php echo ($gender == 'male') ? 'selected' : ''; ?>>Male</option>
            <option value="female" <?php echo ($gender == 'female') ? 'selected' : ''; ?>>Female</option>
            <option value="other" <?php echo ($gender == 'other') ? 'selected' : ''; ?>>Other</option>
        </select>
    </div>
    <div>
        <label>Profile Picture:</label>
        <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/gif">
        <?php if (!empty($profile_picture)): ?>
            <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
        <?php endif; ?>
    </div>
    <button type="submit">Update Profile</button>
</form></main>
</body>
</html>