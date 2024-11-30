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

function cancelBooking($conn, $booking_id, $customer_id, $cancellation_reason = null) {
   
    $check_status_query = "SELECT booking_id, vehicle_id, start_date, end_date, booking_status 
                           FROM bookings 
                           WHERE booking_id = ? AND customer_id = ?";
    $check_stmt = $conn->prepare($check_status_query);
    $check_stmt->bind_param("ii", $booking_id, $customer_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        error_log("No booking found with ID $booking_id for customer $customer_id");
        return false; 
    }

    $booking = $check_result->fetch_assoc();
    if ($booking['booking_status'] !== 'pending') {
        error_log("Booking $booking_id is not in pending status");
        return false; 
    }

    $conn->begin_transaction();

    try {
        $check_driver_assignment_query = "SELECT * FROM driver_assignments WHERE booking_id = ?";
        $check_driver_stmt = $conn->prepare($check_driver_assignment_query);
        $check_driver_stmt->bind_param("i", $booking_id);
        $check_driver_stmt->execute();
        $driver_assignment_result = $check_driver_stmt->get_result();
        
        
        if ($driver_assignment_result->num_rows == 0) {
            error_log("No driver assignment found for booking ID: $booking_id");
        }

      
        $update_driver_query = "UPDATE drivers 
                                SET availability_status = 'available' 
                                WHERE driver_id IN (
                                    SELECT driver_id FROM driver_assignments 
                                    WHERE booking_id = ?
                                )";
        $update_driver_stmt = $conn->prepare($update_driver_query);
        $update_driver_stmt->bind_param("i", $booking_id);
        if (!$update_driver_stmt->execute()) {
            error_log("Failed to update driver availability: " . $update_driver_stmt->error);
            throw new Exception("Driver update failed");
        }

        
        $delete_assignment_query = "DELETE FROM driver_assignments 
                                    WHERE booking_id = ?";
        $delete_assignment_stmt = $conn->prepare($delete_assignment_query);
        $delete_assignment_stmt->bind_param("i", $booking_id);
        if (!$delete_assignment_stmt->execute()) {
            error_log("Failed to delete driver assignment: " . $delete_assignment_stmt->error);
            throw new Exception("Driver assignment deletion failed");
        }

       
        error_log("Driver assignments deleted. Affected rows: " . $delete_assignment_stmt->affected_rows);

       
        $update_vehicle_query = "UPDATE vehicles 
                                 SET availability_status = 'available' 
                                 WHERE vehicle_id = ?";
        $update_vehicle_stmt = $conn->prepare($update_vehicle_query);
        $update_vehicle_stmt->bind_param("i", $booking['vehicle_id']);
        if (!$update_vehicle_stmt->execute()) {
            error_log("Failed to update vehicle availability: " . $update_vehicle_stmt->error);
            throw new Exception("Vehicle update failed");
        }

        
        $insert_cancelled_query = "INSERT INTO cancelledbookings 
                                   (booking_id, customer_id, vehicle_id, start_date, end_date, cancellation_reason) 
                                   VALUES (?, ?, ?, ?, ?, ?)";
        $insert_cancelled_stmt = $conn->prepare($insert_cancelled_query);
        $insert_cancelled_stmt->bind_param(
            "iissss", 
            $booking_id, 
            $customer_id, 
            $booking['vehicle_id'], 
            $booking['start_date'], 
            $booking['end_date'], 
            $cancellation_reason
        );
        if (!$insert_cancelled_stmt->execute()) {
            error_log("Failed to insert into cancelled bookings: " . $insert_cancelled_stmt->error);
            throw new Exception("Cancelled booking insertion failed");
        }

       
        $delete_booking_query = "DELETE FROM bookings 
                                 WHERE booking_id = ? AND customer_id = ?";
        $delete_booking_stmt = $conn->prepare($delete_booking_query);
        $delete_booking_stmt->bind_param("ii", $booking_id, $customer_id);
        if (!$delete_booking_stmt->execute()) {
            error_log("Failed to delete original booking: " . $delete_booking_stmt->error);
            throw new Exception("Booking deletion failed");
        }

        
        $conn->commit();

        return true;
    } catch (Exception $e) {
        
        $conn->rollback();
        error_log("Booking cancellation failed: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    $cancellation_reason = $_POST['cancellation_reason'] ?? null;
    
    if (cancelBooking($conn, $booking_id, $customer_id, $cancellation_reason)) {
        $_SESSION['message'] = "Booking successfully cancelled. Vehicle is now available.";
    } else {
        $_SESSION['error'] = "Unable to cancel booking. It may no longer be in pending status.";
    }
    header("Location: bookinghistory.php");
    exit();
}

$pending_bookings_query = "SELECT b.*, v.model_name, v.registration_no 
                           FROM bookings b
                           JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                           WHERE b.customer_id = ? AND b.booking_status = 'pending'";
$pending_stmt = $conn->prepare($pending_bookings_query);
$pending_stmt->bind_param("i", $customer_id);
$pending_stmt->execute();
$pending_bookings_result = $pending_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Bookings</title>
    <link rel="stylesheet" href="assets/css/cancealreturn.css">
</head>
<body>
    <div class="container">
        <h1>Cancel Pending Bookings</h1>
        
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

        <?php if ($pending_bookings_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Vehicle</th>
                        <th>Registration No</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $pending_bookings_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['model_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['registration_no']); ?></td>
                            <td><?php echo htmlspecialchars($booking['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['end_date']); ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <label for="cancellation_reason">Reason (optional):</label>
                                    <input type="text" name="cancellation_reason" id="cancellation_reason" placeholder="Reason for cancellation">
                                    <button type="submit" name="cancel_booking">Cancel Booking</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending bookings available to cancel.</p>
        <?php endif; ?>
    </div>
</body>
</html>