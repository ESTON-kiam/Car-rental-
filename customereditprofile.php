<?php
session_name('customer_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => false, 
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();


if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php"); 
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

if (!isset($_SESSION['customer_id'])) {
    header("Location: customerlogout.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];


$sql = "SELECT full_name, email, mobile, profile_picture, gender FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($full_name, $email, $phone, $profile_picture, $gender);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender']; // Get gender from form

    $profile_picture_update = false; // Flag to track if profile picture is updated
    $error_message = ''; // Initialize error message

    // Check if a new profile picture is being uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "Customerprofile/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);

        if ($check !== false && move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture_update = true; // Set flag to true
        } else {
            $error_message = "There was an error uploading your file.";
        }
    }

    // Prepare the SQL statement for updating customer details
    if ($profile_picture_update) {
        // Update with new profile picture
        $sql = "UPDATE customers SET full_name = ?, email = ?, mobile = ?, gender = ?, profile_picture = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $full_name, $email, $phone, $gender, $target_file, $customer_id);
    } else {
        // Update without changing profile picture
        $sql = "UPDATE customers SET full_name = ?, email = ?, mobile = ?, gender = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $gender, $customer_id);
    }
    
    $stmt->execute();
    $stmt->close();

    // Optionally add a success message or redirect after the update
    header("Location: customerviewprofile.php"); // Redirect to view profile
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Edit Profile</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS styles remain unchanged */
        :root {
            --primary-color: #007bff;
            --secondary-color: #0056b3;
            --background-color: #f8f9fa;
            --text-color: #343a40;
            --card-background: #ffffff;
            --header-height: 60px;
            --border-radius: 8px;
            --box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding-top: var(--header-height);
        }

        .header {
            width: 100%;
            height: var(--header-height);
            background-color: var(--primary-color);
            color: white;
            position: fixed;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: var(--box-shadow);
            z-index: 10;
        }

        .header h1 {
            font-size: 1.5rem;
            margin: 0;
        }

        .header a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            padding: 10px 15px;
            border-radius: var(--border-radius);
            transition: background-color 0.3s;
        }

        .header a:hover {
            background-color: var(--secondary-color);
        }

        .profile-container {
            max-width: 600px;
            margin: 80px auto 20px;
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            transition: transform 0.3s;
        }

        .profile-container:hover {
            transform: translateY(-5px);
        }

        .profile-details {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .profile-details div {
            margin: 15px 0;
        }

        input[type="text"],
        input[type="email"],
        input[type="file"],
        select {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            transition: border 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        select:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        button {
            padding: 12px 20px;
            margin-top: 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s, transform 0.3s;
        }

        button:hover {
            background-color: var(--secondary-color);
            transform: scale(1.05);
        }

        .error-message {
            color: red;
            margin: 10px 0;
        }

        @media (max-width: 600px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header h1 {
                margin-bottom: 10px;
            }

            .profile-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Edit Profile</h1>
        <div>
            <a href="customer_dashboard.php">Dashboard</a>
            <a href="customerviewprofile.php">View Profile</a>
        </div>
    </div>

    <div class="profile-container">
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-details">
                <div>
                    <strong>Name:</strong>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                </div>
                <div>
                    <strong>Email:</strong>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div>
                    <strong>Phone:</strong>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                </div>
                <div>
                    <strong>Gender:</strong>
                    <select name="gender" required>
                        <option value="Male" <?php echo ($gender === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($gender === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($gender === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div>
                    <strong>Profile Picture:</strong>
                    <div>
                        <?php if (!empty($profile_picture)): ?>
                            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="max-width: 150px; margin-bottom: 10px;">
                        <?php endif; ?>
                    </div>
                    <input type="file" name="profile_picture" accept="image/*">
                    <p style="color: #6c757d;">Leave this empty if you don't want to change your profile picture.</p>
                </div>
            </div>
            <?php if (isset($error_message) && $error_message != ''): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
