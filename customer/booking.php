<?php 
require 'include/db_connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';

require 'vendor/autoload.php';

$vehicle_id = $_GET['id'] ?? null;
$full_name = ''; 
$registration_no = ''; 
$model_name = ''; 
$customer_email = ''; 

if ($vehicle_id) {
    $sql = "SELECT * FROM vehicles WHERE vehicle_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $vehicle = $result->fetch_assoc();
        $registration_no = $vehicle['registration_no'];
        $model_name = $vehicle['model_name'];
    } else {
        echo "Vehicle not found.";
        exit();
    }
} else {
    echo "No vehicle selected.";
    exit();
}

$customer_id = $_SESSION['customer_id']; 
$customer_sql = "SELECT full_name, email FROM customers WHERE id = ?";
$customer_stmt = $conn->prepare($customer_sql);
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();

if ($customer_result->num_rows > 0) {
    $customer = $customer_result->fetch_assoc();
    $full_name = $customer['full_name'];
    $customer_email = $customer['email']; 
}

$existing_booking_sql = "SELECT * FROM bookings WHERE customer_id = ? AND booking_status != 'completed'";
$existing_booking_stmt = $conn->prepare($existing_booking_sql);
$existing_booking_stmt->bind_param("i", $customer_id);
$existing_booking_stmt->execute();
$existing_booking_result = $existing_booking_stmt->get_result();

if ($existing_booking_result->num_rows > 0) {
    header("Location: booking_restriction_page.php");
    exit();
}

$drivers = [];
$driver_sql = "SELECT driver_id, name FROM drivers WHERE availability_status = 'Available'";
$driver_result = $conn->query($driver_sql);

if ($driver_result->num_rows > 0) {
    while($row = $driver_result->fetch_assoc()) {
        $drivers[] = $row;
    }
}

function sendBookingConfirmationEmail($customer_email, $full_name, $vehicle_details, $booking_details) {
    
    
    if (empty($customer_email)) {
        error_log("Cannot send email: Customer email is empty");
        return false;
    }
    
    $mail = new PHPMailer(true);
    
    try {
       
        $mail->SMTPDebug = 2;    
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };
        
        $mail->isSMTP();                                           
        $mail->Host       = 'smtp.gmail.com';                   
        $mail->SMTPAuth   = true;                                
        $mail->Username   = 'engestonbrandon@gmail.com';            
        $mail->Password   = 'dsth izzm npjl qebi';                    
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     
        $mail->Port       = 587; 
        
        $mail->setFrom('noreply@carrentals.com', 'Car Rentals');
        $mail->addAddress($customer_email, $full_name);
        
        $mail->isHTML(true);
        $mail->Subject = "Car Rental Booking Confirmation";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                h1 { color: #2563eb; }
                .booking-details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { margin-top: 30px; font-size: 12px; color: #6c757d; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>Booking Confirmation</h1>
                <p>Dear {$full_name},</p>
                <p>Thank you for booking with Car Rentals. Your booking has been confirmed.</p>
                
                <div class='booking-details'>
                    <h3>Vehicle Details:</h3>
                    <p>Registration Number: {$vehicle_details['registration_no']}</p>
                    <p>Model: {$vehicle_details['model_name']}</p>
                    
                    <h3>Booking Details:</h3>
                    <p>Start Date: {$booking_details['start_date']}</p>
                    <p>End Date: {$booking_details['end_date']}</p>
                    <p>Pick-up Location: {$booking_details['pick_up_location']}</p>
                    <p>Pick-up Time: {$booking_details['pick_up_time']}</p>
                    <p>Car Type: {$booking_details['car_type']}</p>
                    <p>Driver: " . ($booking_details['driver_option'] === 'yes' ? 'Yes' : 'No') . "</p>
                    <p>Total Fare: KSH " . number_format($booking_details['fare']) . "</p>
                    <p>Advance Deposit: KSH " . number_format($booking_details['deposit']) . "</p>
                </div>
                
                <p>If you have any questions, please contact our customer support.</p>
                
                <div class='footer'>
                    <p>&copy; " . date("Y") . " Car Rentals. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
        
        $mail->Body = $message;
        
        
        $mail->AltBody = "Booking Confirmation\n\nDear {$full_name},\n\nThank you for booking with Car Rentals. Your booking has been confirmed.\n\nVehicle Details:\nRegistration Number: {$vehicle_details['registration_no']}\nModel: {$vehicle_details['model_name']}\n\nBooking Details:\nStart Date: {$booking_details['start_date']}\nEnd Date: {$booking_details['end_date']}\nPick-up Location: {$booking_details['pick_up_location']}\nPick-up Time: {$booking_details['pick_up_time']}\nCar Type: {$booking_details['car_type']}\nDriver: " . ($booking_details['driver_option'] === 'yes' ? 'Yes' : 'No') . "\nTotal Fare: KSH " . number_format($booking_details['fare']) . "\nAdvance Deposit: KSH " . number_format($booking_details['deposit']) . "\n\nIf you have any questions, please contact our customer support.";
        
        $result = $mail->send();
        error_log("Email sending result: " . ($result ? "Success" : "Failed"));
        return $result;
    } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        
        error_log("Error details: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        return false;
    }
}

function sendReturnReminder($customer_email, $full_name, $vehicle_details, $booking_details) {
   
    
    if (empty($customer_email)) {
        error_log("Cannot send reminder: Customer email is empty");
        return false;
    }
    
    $mail = new PHPMailer(true);
    
    try {
       
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };
        
        $mail->isSMTP();                                           
        $mail->Host       = 'smtp.gmail.com';                   
        $mail->SMTPAuth   = true;                                
        $mail->Username   = 'engestonbrandon@gmail.com';            
        $mail->Password   = 'dsth izzm npjl qebi';                    
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     
        $mail->Port       = 587; 
        
        $mail->setFrom('noreply@carrentals.com', 'Car Rentals');
        $mail->addAddress($customer_email, $full_name);
        
        $mail->isHTML(true);
        $mail->Subject = "Car Rental Return Reminder";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                h1 { color: #2563eb; }
                .reminder-details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .important { color: #dc2626; font-weight: bold; }
                .footer { margin-top: 30px; font-size: 12px; color: #6c757d; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>Vehicle Return Reminder</h1>
                <p>Dear {$full_name},</p>
                <p>This is a friendly reminder that your car rental is scheduled to end soon.</p>
                
                <div class='reminder-details'>
                    <h3>Rental Details:</h3>
                    <p>Registration Number: {$vehicle_details['registration_no']}</p>
                    <p>Model: {$vehicle_details['model_name']}</p>
                    <p>End Date: {$booking_details['end_date']}</p>
                    <p class='important'>Please ensure to return the vehicle on time to avoid additional charges.</p>
                </div>
                
                <p>If you need to extend your rental period, please contact our customer support as soon as possible.</p>
                
                <div class='footer'>
                    <p>&copy; " . date("Y") . " Car Rentals. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
        
        $mail->Body = $message;
        $mail->AltBody = "Vehicle Return Reminder\n\nDear {$full_name},\n\nThis is a friendly reminder that your car rental is scheduled to end soon.\n\nRental Details:\nRegistration Number: {$vehicle_details['registration_no']}\nModel: {$vehicle_details['model_name']}\nEnd Date: {$booking_details['end_date']}\n\nPlease ensure to return the vehicle on time to avoid additional charges.\n\nIf you need to extend your rental period, please contact our customer support as soon as possible.";
        
        $result = $mail->send();
        error_log("Return reminder email result: " . ($result ? "Success" : "Failed"));
        return $result;
    } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        error_log("Error details: " . $e->getMessage());
        return false;
    }
}

function scheduleReturnReminders($booking_id, $customer_id, $customer_email, $full_name, $end_date) {
    global $conn;
    
    if (empty($customer_email)) {
        error_log("Cannot schedule reminder: Customer email is empty");
        return false;
    }
    
    
    $end_date_obj = new DateTime($end_date);
    $end_date_obj->setTime(12, 0, 0); 
    $reminder_time = $end_date_obj->format('Y-m-d H:i:s');
    
    $sql = "INSERT INTO email_reminders (booking_id, customer_id, customer_email, customer_name, end_date, reminder_time, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed for reminder scheduling: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("iissss", $booking_id, $customer_id, $customer_email, $full_name, $end_date, $reminder_time);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Failed to schedule reminder: " . $stmt->error);
        return false;
    }
    
    error_log("Reminder scheduled for: " . $reminder_time . " (12 hours before midnight of end date: " . $end_date . ")");
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    error_log("Starting booking process with POST data: " . json_encode($_POST));

    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $pick_up_location = $_POST['pick_up_location'];
    $pick_up_time = $_POST['pick_up_time'];
    $car_type = $_POST['car_type'];
    $charge_type = $_POST['charge_type'];
    $driver_option = $_POST['driver_option'];
    $kilometers = isset($_POST['kilometers']) ? intval($_POST['kilometers']) : 0;
    
    $fare = floatval($_POST['fare_hidden']);
    $advance_deposit = floatval($_POST['deposit_hidden']);

   
    if ($fare <= 0 || $advance_deposit <= 0) {
        echo "Invalid fare or deposit amount";
        exit();
    }
    
    if (empty($customer_email)) {
        error_log("Customer email is missing or empty. Cannot proceed.");
        echo "Customer email is required for booking.";
        exit();
    }
    
    $conn->begin_transaction();

    try {
        
        $update_sql = "UPDATE vehicles SET availability_status = 'Unavailable' WHERE vehicle_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $vehicle_id);
        $update_stmt->execute();

       
        $booking_sql = "INSERT INTO bookings (
            vehicle_id, customer_id, start_date, end_date, 
            pick_up_location, pick_up_time, car_type, 
            charge_type, driver_option, total_fare, 
            advance_deposit, booking_status, registration_no, 
            model_name,kilometers
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?,?)";
        
        $booking_stmt = $conn->prepare($booking_sql);
        if (!$booking_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $booking_status = 'pending';
        if (!$booking_stmt->bind_param("iisssssssddssi", 
            $vehicle_id, 
            $customer_id,
            $start_date,
            $end_date,
            $pick_up_location,
            $pick_up_time,
            $car_type,
            $charge_type,
            $driver_option,
            $fare,
            $advance_deposit,
            $registration_no,
            $model_name,
            $kilometers
        )) {
            throw new Exception("Binding parameters failed: " . $booking_stmt->error);
        }

        if (!$booking_stmt->execute()) {
            throw new Exception("Execute failed: " . $booking_stmt->error);
        }
        
        $booking_id = $conn->insert_id;
        error_log("New booking created with ID: $booking_id");
        
      
        if ($driver_option === 'yes' && isset($_POST['driver_id'])) {
            $driver_id = $_POST['driver_id'];

            $driver_assign_sql = "INSERT INTO driver_assignments (
                booking_id, 
                vehicle_id, 
                registration_no, 
                model_name, 
                driver_id,
                customer_id,
                fullname,
                assigned_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $driver_assign_stmt = $conn->prepare($driver_assign_sql);
            if (!$driver_assign_stmt) {
                throw new Exception("Prepare failed for driver assignment: " . $conn->error);
            }

            if (!$driver_assign_stmt->bind_param("iississ", 
                $booking_id,
                $vehicle_id,
                $registration_no,
                $model_name,
                $driver_id,
                $customer_id,
                $full_name
            )) {
                throw new Exception("Binding parameters failed for driver assignment: " . $driver_assign_stmt->error);
            }

            if (!$driver_assign_stmt->execute()) {
                throw new Exception("Execute failed for driver assignment: " . $driver_assign_stmt->error);
            }

           
            $update_driver_sql = "UPDATE drivers SET availability_status = 'Unavailable' WHERE driver_id = ?";
            $update_driver_stmt = $conn->prepare($update_driver_sql);
            $update_driver_stmt->bind_param("i", $driver_id);
            $update_driver_stmt->execute();
        }

        
        $booking_details = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'pick_up_location' => $pick_up_location,
            'pick_up_time' => $pick_up_time,
            'car_type' => $car_type,
            'driver_option' => $driver_option,
            'fare' => $fare,
            'deposit' => $advance_deposit
        ];
        
        $vehicle_details = [
            'registration_no' => $registration_no,
            'model_name' => $model_name
        ];
        
        
        error_log("Attempting to send confirmation email to: $customer_email");
        error_log("Email details - Name: $full_name, Vehicle: $model_name, Booking ID: $booking_id");
        
      
        $email_result = false;
        try {
            $email_result = sendBookingConfirmationEmail($customer_email, $full_name, $vehicle_details, $booking_details);
            if ($email_result) {
                error_log("Booking confirmation email sent successfully");
            } else {
                error_log("Booking confirmation email failed to send, but continuing with booking");
            }
        } catch (Exception $e) {
            error_log("Exception when sending email: " . $e->getMessage());
            
        }
        
        
        try {
            $reminder_result = scheduleReturnReminders($booking_id, $customer_id, $customer_email, $full_name, $end_date);
            if ($reminder_result) {
                error_log("Return reminder scheduled successfully for 12 hours before end date");
            } else {
                error_log("Failed to schedule return reminder, but continuing with booking");
            }
        } catch (Exception $e) {
            error_log("Exception when scheduling reminder: " . $e->getMessage());
            
        }

      
        $conn->commit();
        error_log("Transaction committed successfully!");
        
        
        header("Location: bookingconfrimation.php");
        exit();
    } catch (Exception $e) {
        
        $conn->rollback();
        error_log("Error occurred: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        
        echo "<div style='color:red; padding:20px; margin:20px; border:1px solid red;'>";
        echo "<h2>Error Occurred</h2>";
        echo "<p>Error message: " . $e->getMessage() . "</p>";
        echo "<p>Please try again or contact support if the problem persists.</p>";
        echo "</div>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Car Booking Experience</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/booking.css">
</head>
<body class="bg-gray-50">
    
    <nav class="navbar fixed w-full top-0 z-50 px-6 py-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-white tracking-wider"><i class="fas fa-car"></i>Car Rentals</a>
           </i> <a href="dashboard.php" class="btn-dashboard">Dashboard</a>
        </div>
    </nav>
    <div class="container mx-auto mt-24 px-4">
        <div class="booking-container">
            <div class="text-center mb-10">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">Book Your Premium Experience</h1>
                <p class="text-gray-600">Complete your booking details below</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4">Selected Vehicle Details</h2>
                
                <div class="vehicle-details-grid mb-6">
                    <div class="detail-item">
                        <div class="label">Registration Number</div>
                        <div class="value"><?php echo htmlspecialchars($registration_no); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="label">Model Name</div>
                        <div class="value"><?php echo htmlspecialchars($model_name); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="label">Price Per Day (Standard)</div>
                        <div class="value">KSH <?php echo number_format($vehicle['price_per_day']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="label">Price Per Day (With AC)</div>
                        <div class="value">KSH <?php echo number_format($vehicle['ac_price_per_day']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="label">Price Per Day (Without AC)</div>
                        <div class="value">KSH <?php echo number_format($vehicle['non_ac_price_per_day']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="label">Price Per KM</div>
                        <div class="value">KSH <?php echo number_format($vehicle['km_price']); ?></div>
                    </div>
                </div>
            </div>

            <form action="" method="POST" onsubmit="return validateAndSubmit()">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="space-y-6">
                        <div class="form-group">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="start_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Pick-Up Location</label>
                            <input type="text" class="form-control" name="pick_up_location" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Car Type</label>
                            <select class="form-control" name="car_type" id="car_type" required>
                                <option value="With AC">With AC</option>
                                <option value="Without AC">Without AC</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Driver Service</label>
                            <select class="form-control" name="driver_option" id="driver_option">
                                <option value="no">No Driver Needed</option>
                                <option value="yes">Include Driver</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="end_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Pick-Up Time</label>
                            <input type="time" class="form-control" name="pick_up_time" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Charge Type</label>
                            <select class="form-control" name="charge_type" id="charge_type" required>
                                <option value="per_day">Per Day</option>
                                <option value="per_km">Per KM</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="km_input_container" style="display:none;">
                            <label class="form-label">Number of Kilometers</label>
                            <input type="number" class="form-control" name="kilometers" id="kilometers" min="1">
                        </div>
                        
                        <input type="hidden" name="fare_hidden" id="fare_hidden" value="0">
                        <input type="hidden" name="deposit_hidden" id="deposit_hidden" value="0">
                        
                        <div id="driver_details" class="form-group" style="display:none;">
                            <label class="form-label">Select Driver</label>
                            <select class="form-control" name="driver_id">
                                <?php if (empty($drivers)): ?>
                                    <option value="">No drivers available</option>
                                <?php else: ?>
                                    <?php foreach ($drivers as $driver): ?>
                                        <option value="<?php echo $driver['driver_id']; ?>">
                                            <?php echo htmlspecialchars($driver['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <div class="fare-card">
                        <h3 class="text-xl font-semibold mb-2">Total Fare</h3>
                        <p class="text-3xl font-bold text-primary" id="fare_display">KSH 0.00</p>
                    </div>

                    <div class="fare-card bg-blue-50">
                        <h3 class="text-xl font-semibold mb-2">Required Deposit (70%)</h3>
                        <p class="text-3xl font-bold text-primary" id="deposit_display">KSH 0.00</p>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-800 text-white py-4 px-6 rounded-xl font-semibold text-lg mt-8 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    Confirm Booking
                </button>
            </form>
        </div>
    </div>

    <footer class="bg-white py-6 mt-12">
        <div class="container mx-auto text-center text-gray-600">
            <p>&copy; <?php echo date("Y"); ?> Online Car Rentals. All rights reserved. Designed by Eston Kiama</p>
        </div>
    </footer>

    <script>
    function calculateAndDisplayFare() {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        const carType = document.getElementById('car_type').value;
        const driverOption = document.getElementById('driver_option').value;
        const chargeType = document.getElementById('charge_type').value;
        const kilometersInput = document.getElementById('kilometers');
        
       
        let basePrice = 0;
        if (chargeType === 'per_day') {
            if (carType === 'With AC') {
                basePrice = <?php echo floatval($vehicle['ac_price_per_day']); ?>;
            } else {
                basePrice = <?php echo floatval($vehicle['non_ac_price_per_day']); ?>;
            }
        } else {
            basePrice = <?php echo floatval($vehicle['km_price']); ?>;
        }
        
        
        const detailItems = document.querySelectorAll('.detail-item');
        detailItems.forEach(item => item.classList.remove('bg-blue-50'));
        
        if (chargeType === 'per_day') {
            if (carType === 'With AC') {
                detailItems[3].classList.add('bg-blue-50'); 
            } else {
                detailItems[4].classList.add('bg-blue-50'); 
            }
        } else {
            detailItems[5].classList.add('bg-blue-50'); 
        }
        
        if (startDate && endDate && startDate < endDate) {
            const timeDiff = endDate - startDate;
            const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            const driverCost = driverOption === 'yes' ? 2000 * dayDiff : 0; 
            
            let totalFare = 0;
            
            if (chargeType === 'per_day') {
                
                totalFare = (basePrice * dayDiff) + driverCost;
            } else {
               
                const kilometers = parseInt(kilometersInput.value) || 0;
                if (kilometers > 0) {
                    totalFare = (basePrice * kilometers) + driverCost;
                } else {
                    totalFare = driverCost;
                }
            }
            
            const deposit = totalFare * 0.7;
            
            document.getElementById('fare_display').textContent = `KSH ${totalFare.toLocaleString()}`;
            document.getElementById('deposit_display').textContent = `KSH ${deposit.toLocaleString()}`;
            
            document.getElementById('fare_hidden').value = totalFare;
            document.getElementById('deposit_hidden').value = deposit;

            window.calculatedFare = totalFare;
            window.calculatedDeposit = deposit;
        }
    }

    function toggleDriverOptions() {
        const driverDetails = document.getElementById('driver_details');
        driverDetails.style.display = document.getElementById('driver_option').value === 'yes' ? 'block' : 'none';
        calculateAndDisplayFare();
    }
    
    function toggleKilometerInput() {
        const kmContainer = document.getElementById('km_input_container');
        const chargeType = document.getElementById('charge_type').value;
        
        kmContainer.style.display = chargeType === 'per_km' ? 'block' : 'none';
        
        if (chargeType === 'per_km') {
            document.getElementById('kilometers').setAttribute('required', 'required');
        } else {
            document.getElementById('kilometers').removeAttribute('required');
        }
        
        calculateAndDisplayFare();
    }

    function validateAndSubmit() {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        const chargeType = document.getElementById('charge_type').value;
        
        if (endDate <= startDate) {
            alert("End date must be after start date.");
            return false;
        }
        
        if (chargeType === 'per_km') {
            const kilometers = parseInt(document.getElementById('kilometers').value) || 0;
            if (kilometers <= 0) {
                alert("Please enter a valid number of kilometers.");
                return false;
            }
        }
        
        calculateAndDisplayFare();
        
        if (!window.calculatedFare || !window.calculatedDeposit) {
            alert("Please ensure all booking details are filled correctly.");
            return false;
        }
        
        document.getElementById('fare_hidden').value = window.calculatedFare;
        document.getElementById('deposit_hidden').value = window.calculatedDeposit;
        
        return true;
    }

   
    document.getElementById('start_date').addEventListener('change', calculateAndDisplayFare);
    document.getElementById('end_date').addEventListener('change', calculateAndDisplayFare);
    document.getElementById('car_type').addEventListener('change', calculateAndDisplayFare);
    document.getElementById('driver_option').addEventListener('change', toggleDriverOptions);
    document.getElementById('charge_type').addEventListener('change', toggleKilometerInput);
    document.getElementById('kilometers').addEventListener('input', calculateAndDisplayFare);
    
   
    window.addEventListener('load', function() {
        toggleDriverOptions();
        toggleKilometerInput();
        calculateAndDisplayFare();
        
        
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').min = today;
        document.getElementById('end_date').min = today;
    });
</script>
</body>
</html>