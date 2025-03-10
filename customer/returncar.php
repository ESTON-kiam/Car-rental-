<?php
require_once 'include/db_connection.php';
$customer_id = $_SESSION['customer_id'];

function calculateOverdueCharges($end_date) {
    
    $end = new DateTime($end_date);
    
   
    $end->setTime(23, 59, 0);
    
    $today = new DateTime();
    
    if ($today > $end) {
        $diff = $today->diff($end);
        $total_hours = ($diff->days * 24) + $diff->h;
        $hourly_rate = 2000 / 24; 
        return round($total_hours * $hourly_rate); 
    }
    return 0;
}

function returnVehicle($conn, $booking_id, $customer_id, $additional_details = []) {
    error_log("Attempting to return vehicle - Booking ID: $booking_id, Customer ID: $customer_id");

   
    $conn->query("SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    $conn->begin_transaction();

    try {
        $check_booking_query = "SELECT b.*, v.availability_status, v.vehicle_id 
                                FROM bookings b
                                JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                                WHERE b.booking_id = ? 
                                AND b.customer_id = ? 
                                AND b.booking_status = 'active'
                                FOR UPDATE"; 
        $check_stmt = $conn->prepare($check_booking_query);
        $check_stmt->bind_param("ii", $booking_id, $customer_id);
        $check_stmt->execute();
        $booking_result = $check_stmt->get_result();

        if ($booking_result->num_rows == 0) {
            error_log("No active booking found for booking ID $booking_id and customer ID $customer_id");
            return ['success' => false, 'error' => 'no_active_booking']; 
        }

        $booking = $booking_result->fetch_assoc();
        $vehicle_id = $booking['vehicle_id'];
        
        error_log("Vehicle state before update: " . $booking['availability_status']);

        if ($booking['availability_status'] == 'available') {
            error_log("Vehicle $vehicle_id is already marked as available");
            return ['success' => false, 'error' => 'vehicle_already_available'];
        }

        $total_fare = $booking['total_fare'];
        
        $additional_charges = $additional_details['additional_charges'] ?? 0;
        $final_total = $total_fare + $additional_charges;

        error_log("Calculated charges - Base fare: $total_fare, Additional (including overdue): $additional_charges, Final: $final_total");

        
        $update_booking_query = "UPDATE bookings 
                                SET booking_status = 'completed', 
                                    total_fare = ?,
                                    additional_charges = ?,
                                    return_date = NOW()
                                WHERE booking_id = ?";
        $update_booking_stmt = $conn->prepare($update_booking_query);
        $update_booking_stmt->bind_param("ddi", $final_total, $additional_charges, $booking_id);
        $update_result = $update_booking_stmt->execute();

        if (!$update_result) {
            error_log("Failed to update booking status: " . $conn->error);
            throw new Exception("Booking update failed: " . $conn->error);
        }

        
        $update_vehicle_query = "UPDATE vehicles 
                                SET availability_status = 'available' 
                                WHERE vehicle_id = ?";
        $update_vehicle_stmt = $conn->prepare($update_vehicle_query);
        $update_vehicle_stmt->bind_param("i", $vehicle_id);
        $vehicle_update_result = $update_vehicle_stmt->execute();

        if (!$vehicle_update_result) {
            error_log("Failed to update vehicle availability: " . $conn->error);
            throw new Exception("Vehicle availability update failed: " . $conn->error);
        }

       
        $update_vehicle_query = "UPDATE vehicles 
        SET availability_status = 'available' 
        WHERE vehicle_id = ?";
$update_vehicle_stmt = $conn->prepare($update_vehicle_query);
$update_vehicle_stmt->bind_param("i", $vehicle_id);
$vehicle_update_result = $update_vehicle_stmt->execute();

if (!$vehicle_update_result) {
error_log("Failed to update vehicle availability: " . $conn->error);
throw new Exception("Vehicle availability update failed: " . $conn->error);
}


      
        $check_vehicle_query = "SELECT availability_status FROM vehicles WHERE vehicle_id = ?";
        $check_vehicle_stmt = $conn->prepare($check_vehicle_query);
        $check_vehicle_stmt->bind_param("i", $vehicle_id);
        $check_vehicle_stmt->execute();
        $vehicle_result = $check_vehicle_stmt->get_result();
        $vehicle_data = $vehicle_result->fetch_assoc();
        error_log("Vehicle state after update: " . $vehicle_data['availability_status']);

       
        $insert_service_query = "INSERT INTO services 
                                (vehicle_id, booking_id, return_condition, additional_charges, service_comments, rating) 
                                VALUES (?, ?, ?, ?, ?, ?)";
        $insert_service_stmt = $conn->prepare($insert_service_query);
        $service_comments = $additional_details['service_comments'] ?? NULL;
        $return_condition = $additional_details['return_condition'] ?? 'good';
        $rating = $additional_details['rating'] ?? NULL;
        
        error_log("Service record values - Vehicle ID: $vehicle_id, Booking ID: $booking_id, Condition: $return_condition, Charges: $additional_charges, Rating: " . ($rating ?? 'NULL'));
        
        $insert_service_stmt->bind_param("iisdsi", 
            $vehicle_id, 
            $booking_id, 
            $return_condition, 
            $additional_charges, 
            $service_comments,
            $rating
        );
        $service_insert_result = $insert_service_stmt->execute();

        if (!$service_insert_result) {
            error_log("Failed to insert service record: " . $conn->error . " - SQL: " . $insert_service_query);
            throw new Exception("Service record insertion failed: " . $conn->error);
        }

        $conn->commit();
        error_log("Vehicle return processed successfully for booking ID $booking_id");
        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Vehicle return failed: " . $e->getMessage());
        return ['success' => false, 'error' => 'transaction_failed', 'message' => $e->getMessage()];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_vehicle'])) {
    $booking_id = $_POST['booking_id'];
   
    $additional_charges = floatval($_POST['additional_charges'] ?? 0);
    $return_condition = $_POST['return_condition'] ?? 'good';
    $service_comments = $_POST['service_comments'] ?? '';
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : NULL;

    $additional_details = [
        'additional_charges' => $additional_charges,
        'return_condition' => $return_condition,
        'service_comments' => $service_comments,
        'rating' => $rating
    ];
    
    error_log("Processing vehicle return - POST data: " . json_encode($_POST));
    error_log("Structured additional details: " . json_encode($additional_details));
    
    $return_result = returnVehicle($conn, $booking_id, $customer_id, $additional_details);
    
    if ($return_result['success']) {
        $_SESSION['message'] = "Vehicle returned successfully.";
    } else {
        $error_type = $return_result['error'] ?? 'unknown';
        $error_message = $return_result['message'] ?? '';
        
        switch ($error_type) {
            case 'no_active_booking':
                $_SESSION['error'] = "No active booking found for this vehicle.";
                break;
            case 'vehicle_already_available':
                $_SESSION['error'] = "This vehicle is already marked as returned.";
                break;
            case 'transaction_failed':
                $_SESSION['error'] = "Database error occurred: " . $error_message;
                break;
            default:
                $_SESSION['error'] = "Unable to return vehicle. Please check the booking status or contact support.";
        }
        
        error_log("Return vehicle failed - Error type: $error_type, Message: $error_message");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Vehicle</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
   <link href="assets/css/returnvehicle.css" rel="stylesheet">
   <link href="assets/css/cancealreturn.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>Return Vehicle</h1>
            <nav class="nav-links">
                <a href="dashboard.php" class="dashboard-link">Dashboard</a>
            </nav>
        </div>
    </header>

    <div class="container">
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
                                    
                                    <div class="charges-container">
                                        <label>Additional Charges:</label>
                                        <div class="charges-info">
                                           
                                            <input type="number" 
                                                   name="additional_charges" 
                                                   id="additional_charges_<?php echo $booking['booking_id']; ?>"
                                                   step="1" 
                                                   min="0" 
                                                   data-end-date="<?php echo $booking['end_date']; ?>"
                                                   value="<?php echo calculateOverdueCharges($booking['end_date']); ?>">
                                            <span class="charges-explanation" 
                                                  id="charges_explanation_<?php echo $booking['booking_id']; ?>">
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <label>Return Condition:</label>
                                    <select name="return_condition" required>
                                        <option value="good">Good</option>
                                        <option value="fair">Fair</option>
                                        <option value="damaged">Damaged</option>
                                    </select>
                                    
                                    <label>Service Comments:</label>
                                    <textarea name="service_comments" placeholder="Enter any service or condition notes"></textarea>
                                    
                                    <div class="rating-container">
                                        <label>Rate your experience:</label>
                                        <div class="star-rating">
                                            <input type="radio" id="star5_<?php echo $booking['booking_id']; ?>" name="rating" value="5" required />
                                            <label for="star5_<?php echo $booking['booking_id']; ?>">5</label>
                                            <input type="radio" id="star4_<?php echo $booking['booking_id']; ?>" name="rating" value="4" />
                                            <label for="star4_<?php echo $booking['booking_id']; ?>">4</label>
                                            <input type="radio" id="star3_<?php echo $booking['booking_id']; ?>" name="rating" value="3" />
                                            <label for="star3_<?php echo $booking['booking_id']; ?>">3</label>
                                            <input type="radio" id="star2_<?php echo $booking['booking_id']; ?>" name="rating" value="2" />
                                            <label for="star2_<?php echo $booking['booking_id']; ?>">2</label>
                                            <input type="radio" id="star1_<?php echo $booking['booking_id']; ?>" name="rating" value="1" />
                                            <label for="star1_<?php echo $booking['booking_id']; ?>">1</label>
                                        </div>
                                    </div>
                                    
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function calculateCharges(endDate) {
            const end = new Date(endDate);
            
            end.setHours(23, 59, 0);
            
            const today = new Date();
            
            if (today > end) {
                const diffTime = Math.abs(today - end);
              
                const extraHours = Math.floor(diffTime / (1000 * 60 * 60));
                const hourlyRate = 2000 / 24; 
                const charges = Math.round(extraHours * hourlyRate);
                
               
                const days = Math.floor(extraHours / 24);
                const remainingHours = extraHours % 24;
                
                return {
                    totalHours: extraHours,
                    days: days,
                    hours: remainingHours,
                    charges: charges
                };
            }
            return {
                totalHours: 0,
                days: 0,
                hours: 0,
                charges: 0
            };
        }

        function updateAllCharges() {
            const chargeInputs = document.querySelectorAll('[id^="additional_charges_"]');
            
            chargeInputs.forEach(input => {
                const endDate = input.dataset.endDate;
                const bookingId = input.id.split('_').pop();
                const explanation = document.getElementById(`charges_explanation_${bookingId}`);
                
                const result = calculateCharges(endDate);
                
               
                if (!input.dataset.userModified) {
                    input.value = result.charges;
                }
                
                if (result.charges > 0) {
                    
                    let explanationText = '';
                    if (result.days > 0) {
                        explanationText += `${result.days} day${result.days > 1 ? 's' : ''}`;
                    }
                    if (result.hours > 0) {
                        if (result.days > 0) explanationText += ' and ';
                        explanationText += `${result.hours} hour${result.hours > 1 ? 's' : ''}`;
                    }
                    explanationText += ` overdue (${result.totalHours} total hours × KSH ${Math.round(2000 / 24)} per hour)`;
                    
                    explanation.textContent = `(${explanationText})`;
                    explanation.style.color = '#721c24';
                } else {
                    explanation.textContent = '(No overdue charges)';
                    explanation.style.color = '#155724';
                }
            });
        }

      
        document.querySelectorAll('[id^="additional_charges_"]').forEach(input => {
            input.addEventListener('change', function() {
                this.dataset.userModified = 'true';
            });
        });

        
        updateAllCharges();
        
       
        setInterval(updateAllCharges, 60000);
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>