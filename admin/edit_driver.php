<?php
require 'include/db_connection.php';

$driver_id = $name = $contact_no = $residence = $age = $driving_license_no = $email = $profile_picture = $license_image = "";
$availability_status = "";
$success_msg = $error_msg = "";

if(isset($_POST["driver_id"]) && !empty($_POST["driver_id"])){
    // Getting the driver ID from form submission
    $driver_id = $_POST["driver_id"];
    
    // Only processing the availability_status field
    $availability_status = isset($_POST["availability_status"]) ? $_POST["availability_status"] : "Available";
    
    // No validation needed as it's a select dropdown with predefined values
    
    // Update only the availability_status in the database
    $sql = "UPDATE drivers SET availability_status = ? WHERE driver_id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "si", $param_availability_status, $param_driver_id);
        
        $param_availability_status = $availability_status;
        $param_driver_id = $driver_id;
        
        if(mysqli_stmt_execute($stmt)){
            $success_msg = "Driver availability status updated successfully.";
        } else{
            $error_msg = "Oops! Something went wrong. Please try again later. Error: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
    
} else {
    // If no form submitted, fetch driver details from database based on URL parameter
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        
        $driver_id = trim($_GET["id"]);
        
        $sql = "SELECT * FROM drivers WHERE driver_id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $param_driver_id);
            
            $param_driver_id = $driver_id;
            
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
                
                if(mysqli_num_rows($result) == 1){
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Get all driver details but we'll only allow editing availability_status
                    $driver_id = $row["driver_id"];
                    $name = $row["name"];
                    $contact_no = $row["contact_no"];
                    $residence = $row["residence"];
                    $age = $row["age"];
                    $driving_license_no = $row["driving_license_no"];
                    $license_image = $row["license_image"];
                    $email = $row["email"];
                    $profile_picture = $row["profile_picture"];
                    $availability_status = $row["availability_status"];
                } else{
                    header("location: error.php");
                    exit();
                }
                
            } else{
                $error_msg = "Oops! Something went wrong. Please try again later.";
            }
            
            mysqli_stmt_close($stmt);
        }
    } else{
        header("location: error.php");
        exit();
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <title>Edit Driver Availability</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
        .wrapper{
            width: 800px;
            margin: 0 auto;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
        }
        .driver-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5">Edit Driver Availability</h2>
                    <p>You can only edit the driver's availability status on this page.</p>
                    
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                    <?php endif; ?>
                    
                    <!-- Display driver information (read-only) -->
                    <div class="driver-info">
                        <div class="row">
                            <div class="col-md-8">
                                <h4><?php echo htmlspecialchars($name); ?></h4>
                                <p><strong>Contact:</strong> <?php echo htmlspecialchars($contact_no); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                                <p><strong>Age:</strong> <?php echo htmlspecialchars($age); ?></p>
                                <p><strong>License No:</strong> <?php echo htmlspecialchars($driving_license_no); ?></p>
                                <p><strong>Residence:</strong> <?php echo htmlspecialchars($residence); ?></p>
                                <p><strong>Availability Status:</strong> <?php echo htmlspecialchars($availability_status); ?></p>
                            </div>
                            <div class="col-md-4">
                                <?php if(!empty($profile_picture)): ?>
                                    <img src="<?php echo $profile_picture; ?>" class="preview-image" alt="Profile Picture">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                   
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group mb-3">
                            <label><strong>Availability Status</strong></label>
                            <select name="availability_status" class="form-control">
                                <option value="Available" <?php echo ($availability_status == "Available") ? "selected" : ""; ?>>Available</option>
                                <option value="Unavailable" <?php echo ($availability_status == "Unavailable") ? "selected" : ""; ?>>Unavailable</option>
                            </select>
                        </div>
                        
                        <input type="hidden" name="driver_id" value="<?php echo $driver_id; ?>"/>
                        <div class="d-flex justify-content-between mt-4">
                            <input type="submit" class="btn btn-primary" value="Update Status">
                            <a href="driverslist.php" class="btn btn-secondary">Back to Drivers</a>
                        </div>
                    </form>
                </div>
            </div>        
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>