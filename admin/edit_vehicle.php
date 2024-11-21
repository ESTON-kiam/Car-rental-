<?php
session_name('admin_session');
session_set_cookie_params(1800); 
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

$registration_no = $_GET['registration_no'] ?? '';
$vehicle = null;

if ($registration_no) {
    
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE registration_no = ?");
    $stmt->bind_param("s", $registration_no);
    $stmt->execute();
    $result = $stmt->get_result();
    $vehicle = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model_name = $_POST['model_name'];
    $description = $_POST['description'];
    $availability_status = $_POST['availability_status'];
    $price_per_day = $_POST['price_per_day'];

    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo = 'Cars/' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    } else {
        $photo = $vehicle['photo']; 
    }

    
    $stmt = $conn->prepare("UPDATE vehicles SET model_name = ?, description = ?, availability_status = ?, photo = ?, price_per_day = ? WHERE registration_no = ?");
    $stmt->bind_param("ssssds", $model_name, $description, $availability_status, $photo, $price_per_day, $registration_no);

    if ($stmt->execute()) {
        header("Location: carcollection.php");
        exit(); 
    
    } else {
        echo "<p style='color: red;'>Error updating vehicle: " . $conn->error . "</p>";
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
    <title>Edit Vehicle</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .header {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            text-align: center;
        }
        .header a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }
        .header a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #333;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s;
        }
        input:focus, textarea:focus, select:focus {
            border-color: #007BFF;
            outline: none;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .price-field {
            position: relative;
        }
        .price-field:before {
            content: "KSH";
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            z-index: 1;
        }
        .price-field input {
            padding-left: 45px;
        }
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <a href="dashboard.php">Dashboard</a>
    <a href="carcollection.php">Car Collection</a>
    <a href="add_vehicles.php">Add Vehicle</a>
</div>

<h2>Edit Vehicle Details</h2>
<div class="container">
    <?php if ($vehicle): ?>
        <form method="POST" enctype="multipart/form-data">
            <label for="model_name">Model Name:</label>
            <input type="text" id="model_name" name="model_name" value="<?php echo htmlspecialchars($vehicle['model_name']); ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($vehicle['description']); ?></textarea>

            <label for="availability_status">Availability Status:</label>
            <select id="availability_status" name="availability_status" required>
                <option value="Available" <?php echo $vehicle['availability_status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                <option value="Unavailable" <?php echo $vehicle['availability_status'] === 'Unavailable' ? 'selected' : ''; ?>>Unavailable</option>
            </select>

            <label for="price_per_day">Price per Day:</label>
            <div class="price-field">
                <input type="number" id="price_per_day" name="price_per_day" value="<?php echo htmlspecialchars($vehicle['price_per_day']); ?>" required min="0" step="0.01">
            </div>

            <label for="photo">Upload New Photo:</label>
            <input type="file" id="photo" name="photo">

            <button type="submit">Update Vehicle</button>
        </form>
    <?php else: ?>
        <p class="error-message">Vehicle not found.</p>
    <?php endif; ?>
</div>

</body>
</html>