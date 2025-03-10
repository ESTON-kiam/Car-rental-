<?php
require 'include/db_connection.php';

$customer_id = $_SESSION['customer_id'];
$sql = "SELECT full_name, email, mobile, profile_picture, gender FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($full_name, $email, $phone, $profile_picture, $gender);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #003c8f;
            --background-color: #f4f6f9;
            --text-color: #2e3b4e;
            --card-background: #ffffff;
            --header-height: 80px;
            --box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding-top: var(--header-height);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
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
            padding: 0 30px;
            box-shadow: var(--box-shadow);
            z-index: 10;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
        }

        .header a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            margin-left: 20px;
            padding: 8px 16px;
            border: 2px solid white;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .header a:hover {
            background-color: white;
            color: var(--primary-color);
        }

        .profile-container {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: var(--box-shadow);
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

        .profile-picture img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .profile-picture img:hover {
            transform: scale(1.05);
        }

        .profile-details {
            font-size: 1.2rem;
            margin-top: 20px;
        }

        .profile-details div {
            margin: 15px 0;
            color: var(--text-color);
        }

        .profile-details div strong {
            font-weight: 500;
            color: var(--secondary-color);
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
            background-color: var(--secondary-color);
            border-radius: 6px;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .button-container a:hover {
            background-color: var(--primary-color);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
    </style>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
</head>
<body>

    <div class="header">
        <h1>My Profile</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="editprofile.php">Edit Profile</a>
        </div>
    </div>

    <div class="profile-container">
        <div class="profile-picture">
            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
        </div>
        <div class="profile-details">
            <div><strong>Name:</strong> <?php echo htmlspecialchars($full_name); ?></div>
            <div><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></div>
            <div><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></div>
            <div><strong>Gender:</strong> <?php echo htmlspecialchars($gender); ?></div> 
        </div>
        <div class="button-container">
            <a href="editprofile.php">Edit Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

</body>
</html>

<?php
$conn->close();
?>
