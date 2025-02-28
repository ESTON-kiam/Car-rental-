<?php
require 'include/db_connection.php';

$email = $_SESSION['email'];
$query = "SELECT name, contact_no, gender,role, profile_picture FROM admins WHERE email_address='$email'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $name = $admin['name'];
    $contact_no = $admin['contact_no'];
    $gender = $admin['gender'];
    $role =$admin['role'];
    $profile_picture = $admin['profile_picture'] ?: 'default-profile.png'; 
} else {
    $name = "Admin"; 
    $contact_no = "N/A";
    $gender = "N/A";
    $profile_picture = 'default-profile.png'; 
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .profile-body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
            color: #2e3b4e;
            margin: 0;
            padding-top: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 80px);
            overflow: hidden;
        }

        .profile-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            padding: 40px 30px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.7s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-picturer img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #4a90e2;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .profile-picturer img:hover {
            transform: scale(1.05);
        }

        .profile-details {
            font-size: 1.2rem;
            margin-top: 20px;
        }

        .profile-details div {
            margin: 15px 0;
            color: #2e3b4e;
        }

        .profile-details div strong {
            font-weight: 500;
            color: #003c8f;
        }

        .button-container {
            margin-top: 30px;
        }

        .button-container a {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            font-size: 1rem;
            color: white;
            background-color: #003c8f;
            border-radius: 6px;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .button-container a:hover {
            background-color: #4a90e2;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
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
    <div class="profile-body">
        <div class="profile-container">
            <div class="profile-picturer">
                <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
            </div>
            <div class="profile-details">
                <div><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></div>
                <div><strong>Contact No:</strong> <?php echo htmlspecialchars($contact_no); ?></div>
                <div><strong>Role:</strong> <?php echo htmlspecialchars($role); ?></div>
                <div><strong>Gender:</strong> <?php echo htmlspecialchars($gender); ?></div>
            </div>
            <div class="button-container">
                <a href="editprofile.php">Edit Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div></main>
</body>
</html>