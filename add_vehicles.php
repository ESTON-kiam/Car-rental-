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

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registration_no = $_POST['registration_no'];
    $model_name = $_POST['model_name'];
    $description = $_POST['description'];
    $availability_status = $_POST['availability_status'];
    $photo = $_FILES['photo']['name'];

    // Upload photo
    $target_dir = "Cars/"; 
    $target_file = $target_dir . basename($photo);
    move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);

    $sql = "INSERT INTO vehicles (registration_no, model_name, description, availability_status, photo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $registration_no, $model_name, $description, $availability_status, $target_file);
    
    if ($stmt->execute()) {
        $success_message = "Vehicle added successfully!";
    } else {
        $error_message = "Error adding vehicle: " . $stmt->error;
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
    <title>Add Vehicle</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
        }

        header {
            width: 100%;
            background-color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #74ebd5;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #69d4bf;
        }

        form {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            color: #333;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 10px;
            border: 2px solid #ccc;
            transition: border-color 0.3s ease;
            outline: none;
            font-size: 16px;
            background-color: #f9f9f9;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #74ebd5;
        }

        button {
            width: 100%;
            padding: 15px;
            background-color: #74ebd5;
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #69d4bf;
        }

        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 180px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px 0;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -90px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        @media screen and (max-width: 600px) {
            form {
                padding: 30px;
            }
        }
    </style>
        <link href="assets/img/p.png" rel="icon">
        <link href="assets/img/p.png" rel="apple-touch-icon">
</head>
<body>

    <header>
        <h1>Online Car Rental</h1>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="carcollection.php">Car Collection</a>
        </nav>
    </header>

    <!-- Display success or error message -->
    <?php if (!empty($success_message)): ?>
        <div style="color: green; text-align: center; margin: 20px;">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div style="color: red; text-align: center; margin: 20px;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <h2>Add Vehicle</h2>

        <!-- Registration Number -->
        <div class="tooltip">
            <label for="registration_no">Registration No (7 Characters):</label>
            <span class="tooltiptext">(e.g., KBC 213R)</span>
        </div>
        <input type="text" id="registration_no" name="registration_no" maxlength="8" placeholder="e.g. KBC 213R" required>

        <!-- Model Name -->
        <label for="model_name">Model Name:</label>
        <input type="text" id="model_name" name="model_name" placeholder="Enter Model Name" required>

        <!-- Description -->
        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="4" placeholder="Describe the vehicle" required></textarea>

        <!-- Availability Status -->
        <label for="availability_status">Availability Status:</label>
        <select id="availability_status" name="availability_status" required>
            <option value="Available">Available</option>
            <option value="Unavailable">Unavailable</option>
        </select>

        <!-- Upload Photo -->
        <label for="photo" class="custom-file-upload">
            <i class="fas fa-upload"></i> Upload Photo
        </label>
        <input type="file" id="photo" name="photo" accept="image/*" required>

        <!-- Submit Button -->
        <button type="submit">Add Vehicle</button>
    </form>

</body>
</html>
