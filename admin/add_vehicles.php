<?php
require 'include/db_connection.php';
if (!function_exists('basename') || !function_exists('move_uploaded_file')) {
    die('Required functions are not available.');
}

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registration_no = $_POST['registration_no'];
    $model_name = $_POST['model_name'];
    $description = $_POST['description'];
    $availability_status = $_POST['availability_status'];
    
   
    $original_price_per_day = $_POST['original_price_per_day'];
    $original_ac_price_per_day = $_POST['original_ac_price_per_day'];
    $original_non_ac_price_per_day = $_POST['original_non_ac_price_per_day'];
    $original_km_price = $_POST['original_km_price'];

    $photo = $_FILES['photo']['name'];

    $target_dir = "Cars/"; 
    $target_file = $target_dir . basename($photo);
    move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);

    $sql = "INSERT INTO vehicles (
        registration_no, 
        model_name, 
        description, 
        availability_status, 
        photo, 
        original_price_per_day,
        original_ac_price_per_day,
        original_non_ac_price_per_day,
        original_km_price
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssdddd", 
        $registration_no, 
        $model_name, 
        $description, 
        $availability_status, 
        $target_file, 
        $original_price_per_day,
        $original_ac_price_per_day,
        $original_non_ac_price_per_day,
        $original_km_price
    );
    
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
    <title>Add Vehicle-Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="assets/css/addvehicle.css" rel="stylesheet">
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
</head>
<body>
    <header>
        <h1>Online Car Rental</h1>
        <?php include('include/header.php') ?>
    </header>
    <?php include('include/sidebar.php') ?>
    <main class="main-content">
    <?php if (!empty($success_message)): ?>
        <div class="message success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="message error">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <h2>Add Vehicle</h2>
        
        <div class="form-column">
            <div class="tooltip">
                <label for="registration_no">Registration No (7 Characters):</label>
                <span class="tooltiptext">(e.g., KBC 213R)</span>
            </div>
            <input type="text" id="registration_no" name="registration_no" maxlength="8" 
                   placeholder="e.g. KBC 213R" required>

            <label for="model_name">Model Name:</label>
            <input type="text" id="model_name" name="model_name" 
                   placeholder="Enter Model Name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" 
                      placeholder="Describe the vehicle" required></textarea>

            <label for="original_price_per_day">Stand Price per Day (KSH):</label>
            <input type="number" id="original_price_per_day" name="original_price_per_day" 
                   step="0.01" min="0" placeholder="Enter base price per day" required>

            <label for="original_ac_price_per_day">AC Price per Day (KSH):</label>
            <input type="number" id="original_ac_price_per_day" name="original_ac_price_per_day" 
                   step="0.01" min="0" placeholder="Enter AC price per day">
        </div>

        <div class="form-column">
            <label for="original_non_ac_price_per_day">Non-AC Price per Day (KSH):</label>
            <input type="number" id="original_non_ac_price_per_day" name="original_non_ac_price_per_day" 
                   step="0.01" min="0" placeholder="Enter non-AC price per day">

            <label for="original_km_price">Price per KM (KSH):</label>
            <input type="number" id="original_km_price" name="original_km_price" 
                   step="0.01" min="0" placeholder="Enter price per kilometer">

            <label for="availability_status">Availability Status:</label>
            <select id="availability_status" name="availability_status" required>
                <option value="Available">Available</option>
                <option value="Unavailable">Unavailable</option>
            </select>

            <label for="photo" class="custom-file-upload">
                <i class="fas fa-upload"></i> Upload Photo
            </label>
            <input type="file" id="photo" name="photo" accept="image/*" required>
        </div>

        <button type="submit">Add Vehicle</button>
    </form>
    </main>
</body>
</html>