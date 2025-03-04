<?php
require 'include/db_connection.php';

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
    $ac_price_per_day = $_POST['ac_price_per_day'];
    $non_ac_price_per_day = $_POST['non_ac_price_per_day'];
    $km_price = $_POST['km_price'];
    
    $status_reason = ($availability_status === 'Unavailable' && isset($_POST['status_reason'])) 
                     ? $_POST['status_reason'] 
                     : null;
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo = 'Cars/' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    } else {
        $photo = $vehicle['photo']; 
    }
    
    $stmt = $conn->prepare("UPDATE vehicles SET model_name = ?, description = ?, availability_status = ?, 
                            photo = ?, price_per_day = ?, status_reason = ?, ac_price_per_day = ?, 
                            non_ac_price_per_day = ?, km_price = ? WHERE registration_no = ?");
    $stmt->bind_param("ssssdsddds", $model_name, $description, $availability_status, $photo, 
                      $price_per_day, $status_reason, $ac_price_per_day, $non_ac_price_per_day, 
                      $km_price, $registration_no);

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
    <link href="assets/css/edit_vehicle.css" rel="stylesheet">
    <style>
        #status_reason_container {
            display: none; 
        }
        
        .form-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .form-column {
            flex: 1;
            min-width: 300px;
        }
        
        .full-width {
            width: 100%;
        }
        
        .price-field {
            position: relative;
        }
        
        .price-field input {
            width: 100%;
        }
        
        .current-photo {
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .form-container {
                flex-direction: column;
            }
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
            <div class="form-container">
                
                <div class="form-column">
                    <label for="model_name">Model Name:</label>
                    <input type="text" id="model_name" name="model_name" value="<?php echo htmlspecialchars($vehicle['model_name']); ?>" required>

                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($vehicle['description']); ?></textarea>

                    <label for="availability_status">Availability Status:</label>
                    <select id="availability_status" name="availability_status" required>
                        <option value="Available" <?php echo $vehicle['availability_status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                        <option value="Unavailable" <?php echo $vehicle['availability_status'] === 'Unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                    </select>

                    <div id="status_reason_container" <?php echo $vehicle['availability_status'] === 'Unavailable' ? 'style="display:block;"' : ''; ?>>
                        <label for="status_reason">Unavailability Reason:</label>
                        <select id="status_reason" name="status_reason">
                            <option value="">-- Select Reason --</option>
                            <option value="Booked" <?php echo $vehicle['status_reason'] === 'Booked' ? 'selected' : ''; ?>>Booked</option>
                            <option value="Under Service" <?php echo $vehicle['status_reason'] === 'Under Service' ? 'selected' : ''; ?>>Under Service</option>
                        </select>
                    </div>
                </div>
                
               
                <div class="form-column">
                    <label for="price_per_day">Base Price per Day (Standard):</label>
                    <div class="price-field">
                        <input type="number" id="price_per_day" name="price_per_day" value="<?php echo htmlspecialchars($vehicle['price_per_day']); ?>" required min="0" step="0.01">
                    </div>
                    
                    <label for="ac_price_per_day">AC Price per Day (AP):</label>
                    <div class="price-field">
                        <input type="number" id="ac_price_per_day" name="ac_price_per_day" value="<?php echo htmlspecialchars($vehicle['ac_price_per_day'] ?? 0); ?>" min="0" step="0.01">
                    </div>
                    
                    <label for="non_ac_price_per_day">Non-AC Price per Day (NAP):</label>
                    <div class="price-field">
                        <input type="number" id="non_ac_price_per_day" name="non_ac_price_per_day" value="<?php echo htmlspecialchars($vehicle['non_ac_price_per_day'] ?? 0); ?>" min="0" step="0.01">
                    </div>
                    
                    <label for="km_price">Price per Kilometer (PPK):</label>
                    <div class="price-field">
                        <input type="number" id="km_price" name="km_price" value="<?php echo htmlspecialchars($vehicle['km_price'] ?? 0); ?>" min="0" step="0.01">
                    </div>
                </div>
                
              
                <div class="full-width">
                    <label for="photo">Current Photo:</label>
                    <?php if (!empty($vehicle['photo'])): ?>
                        <div class="current-photo">
                            <img src="<?php echo htmlspecialchars($vehicle['photo']); ?>" alt="Current vehicle photo" style="max-width: 300px;">
                        </div>
                    <?php else: ?>
                        <p>No image available</p>
                    <?php endif; ?>

                    <label for="photo">Upload New Photo:</label>
                    <input type="file" id="photo" name="photo">

                    <button type="submit">Update Vehicle</button>
                </div>
            </div>
        </form>
    <?php else: ?>
        <p class="error-message">Vehicle not found.</p>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const availabilitySelect = document.getElementById('availability_status');
        const statusReasonContainer = document.getElementById('status_reason_container');
        
        function toggleStatusReason() {
            if (availabilitySelect.value === 'Unavailable') {
                statusReasonContainer.style.display = 'block';
            } else {
                statusReasonContainer.style.display = 'none';
            }
        }
        
        toggleStatusReason();
        
        availabilitySelect.addEventListener('change', toggleStatusReason);
    });
</script>

</body>
</html>