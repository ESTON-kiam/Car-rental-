<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    header("Location: http://localhost:8000/customer/"); 
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
$customer_id = $_SESSION['customer_id'];

function returnVehicle($conn, $booking_id, $customer_id, $additional_details = []) {
   
    error_log("Attempting to return vehicle - Booking ID: $booking_id, Customer ID: $customer_id");

    $conn->begin_transaction();

    try {
       
        $check_booking_query = "SELECT b.*, v.availability_status 
                                FROM bookings b
                                JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                                WHERE b.booking_id = ? 
                                AND b.customer_id = ? 
                                AND b.booking_status = 'active'";
        $check_stmt = $conn->prepare($check_booking_query);
        $check_stmt->bind_param("ii", $booking_id, $customer_id);
        $check_stmt->execute();
        $booking_result = $check_stmt->get_result();

        if ($booking_result->num_rows == 0) {
            error_log("No active booking found for booking ID $booking_id and customer ID $customer_id");
            return false; 
        }

        $booking = $booking_result->fetch_assoc();
        $vehicle_id = $booking['vehicle_id'];

        
        if ($booking['availability_status'] == 'available') {
            error_log("Vehicle $vehicle_id is already marked as available");
            return false;
        }
    
    
        $total_fare = $booking['total_fare'];
        $additional_charges = $additional_details['additional_charges'] ?? 0;
        $return_condition = $additional_details['return_condition'] ?? 'good';
        $final_total = $total_fare + $additional_charges;

       
        $update_booking_query = "UPDATE bookings 
                                 SET booking_status = 'completed', 
                                     total_fare = ?,
                                     return_date = NOW()
                                 WHERE booking_id = ?";
        $update_booking_stmt = $conn->prepare($update_booking_query);
        $update_booking_stmt->bind_param("di", $final_total, $booking_id);
        $update_result = $update_booking_stmt->execute();

        if (!$update_result) {
            error_log("Failed to update booking status: " . $conn->error);
            throw new Exception("Booking update failed");
        }

       
        $update_vehicle_query = "UPDATE vehicles 
                                 SET availability_status = 'available' 
                                 WHERE vehicle_id = ?";
        $update_vehicle_stmt = $conn->prepare($update_vehicle_query);
        $update_vehicle_stmt->bind_param("i", $vehicle_id);
        $vehicle_update_result = $update_vehicle_stmt->execute();

        if (!$vehicle_update_result) {
            error_log("Failed to update vehicle availability: " . $conn->error);
            throw new Exception("Vehicle availability update failed");
        }

       
        $insert_service_query = "INSERT INTO services 
                                 (vehicle_id, booking_id, return_condition, additional_charges, service_comments) 
                                 VALUES (?, ?, ?, ?, ?)";
        $insert_service_stmt = $conn->prepare($insert_service_query);
        $service_comments = $additional_details['service_comments'] ?? NULL;
        $insert_service_stmt->bind_param("iisds", 
            $vehicle_id, 
            $booking_id, 
            $return_condition, 
            $additional_charges, 
            $service_comments
        );
        $service_insert_result = $insert_service_stmt->execute();

        if (!$service_insert_result) {
            error_log("Failed to insert service record: " . $conn->error);
            throw new Exception("Service record insertion failed");
        }

        
        $conn->commit();
        error_log("Vehicle return processed successfully for booking ID $booking_id");
        return true;
    } catch (Exception $e) {
       
        $conn->rollback();
        error_log("Vehicle return failed: " . $e->getMessage());
        return false;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_vehicle'])) {
    $booking_id = $_POST['booking_id'];
    $additional_charges = floatval($_POST['additional_charges'] ?? 0);
    $return_condition = $_POST['return_condition'] ?? 'good';
    $service_comments = $_POST['service_comments'] ?? '';

    $additional_details = [
        'additional_charges' => $additional_charges,
        'return_condition' => $return_condition,
        'service_comments' => $service_comments
    ];
    
    if (returnVehicle($conn, $booking_id, $customer_id, $additional_details)) {
        $_SESSION['message'] = "Vehicle returned successfully.";
    } else {
        $_SESSION['error'] = "Unable to return vehicle. Please check the booking status or contact support.";
    }
    header("Location: dashboard.php");
    exit();
}


$active_bookings_query = "SELECT b.*, v.model_name, v.registration_no, v.availability_status,
                                 (SELECT fullname FROM driver_assignments da 
                                  WHERE da.booking_id = b.booking_id) as driver_name
                          FROM bookings b
                          JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                          WHERE b.customer_id = ? AND b.booking_status = 'active'";
$active_stmt = $conn->prepare($active_bookings_query);
$active_stmt->bind_param("i", $customer_id);
$active_stmt->execute();
$active_bookings_result = $active_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Return Vehicle</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/cancealreturn.css">
</head>
<body>
    <div class="container">
        <h1>Return Vehicle</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="success-message">
                <?php 
                echo htmlspecialchars($_SESSION['message']); 
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if ($active_bookings_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Vehicle</th>
                        <th>Registration No</th>
                        <th>Driver</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $active_bookings_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['model_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['registration_no']); ?></td>
                            <td><?php echo htmlspecialchars($booking['driver_name'] ?? 'No Driver'); ?></td>
                            <td><?php echo htmlspecialchars($booking['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['end_date']); ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to return this vehicle?');">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    
                                    <label>Additional Charges:</label>
                                    <input type="number" name="additional_charges" step="0.01" min="0" placeholder="Enter additional charges">
                                    
                                    <label>Return Condition:</label>
                                    <select name="return_condition">
                                        <option value="good">Good</option>
                                        <option value="fair">Fair</option>
                                        <option value="damaged">Damaged</option>
                                    </select>
                                    
                                    <label>Service Comments:</label>
                                    <textarea name="service_comments" placeholder="Enter any service or condition notes"></textarea>
                                    
                                    <button type="submit" name="return_vehicle">Return Vehicle</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No active bookings to return.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php

$conn->close();
?>