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


$vehicle_id = $_GET['id'] ?? null;

$full_name = ''; 
$registration_no = ''; 
$model_name = ''; 


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
$customer_sql = "SELECT full_name FROM customers WHERE id = ?";
$customer_stmt = $conn->prepare($customer_sql);
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();

if ($customer_result->num_rows > 0) {
    $customer = $customer_result->fetch_assoc();
    $full_name = $customer['full_name'];
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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    echo "POST Data:<br>";
    print_r($_POST);
    echo "<br>";

    
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $pick_up_location = $_POST['pick_up_location'];
    $pick_up_time = $_POST['pick_up_time'];
    $car_type = $_POST['car_type'];
    $charge_type = $_POST['charge_type'];
    $driver_option = $_POST['driver_option'];
    
   
    $fare = floatval($_POST['fare_hidden']);
    $advance_deposit = floatval($_POST['deposit_hidden']);

    if ($fare <= 0 || $advance_deposit <= 0) {
        echo "Invalid fare or deposit amount";
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
            model_name
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)";
        
        $booking_stmt = $conn->prepare($booking_sql);
        if (!$booking_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $booking_status = 'pending';
        if (!$booking_stmt->bind_param("iisssssssddss", 
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
            $model_name
        )) {
            throw new Exception("Binding parameters failed: " . $booking_stmt->error);
        }

        if (!$booking_stmt->execute()) {
            throw new Exception("Execute failed: " . $booking_stmt->error);
        }
        
        $booking_id = $conn->insert_id;
        
       
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
                throw new Exception("Prepare failed: " . $conn->error);
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
                throw new Exception("Binding parameters failed: " . $driver_assign_stmt->error);
            }

            if (!$driver_assign_stmt->execute()) {
                throw new Exception("Execute failed: " . $driver_assign_stmt->error);
            }

            
            $update_driver_sql = "UPDATE drivers SET availability_status = 'Unavailable' WHERE driver_id = ?";
            $update_driver_stmt = $conn->prepare($update_driver_sql);
            $update_driver_stmt->bind_param("i", $driver_id);
            $update_driver_stmt->execute();
        }

        $conn->commit();
        echo "Transaction committed successfully!<br>";
        header("Location: bookingconfrimation.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error occurred:<br>";
        echo "Error message: " . $e->getMessage() . "<br>";
        echo "Stack trace:<br>";
        echo nl2br($e->getTraceAsString());
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #dbeafe;
            --text-color: #1f2937;
            --light-gray: #f3f4f6;
            --border-radius: 12px;
            --gradient-start: #4f46e5;
            --gradient-end: #2563eb;
        }

        body {
            background: linear-gradient(135deg, #f6f7ff 0%, #ffffff 100%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(to right, #1e293b, #334155);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .booking-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        .btn-dashboard {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-dashboard:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .fare-card {
            background: linear-gradient(145deg, #ffffff, var(--accent-color));
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1);
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper::after {
            content: '▼';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6b7280;
            font-size: 0.8rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <nav class="navbar fixed w-full top-0 z-50 px-6 py-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-white tracking-wider">Car Rentals</a>
            <a href="dashboard.php" class="btn-dashboard">Dashboard</a>
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Registration Number</p>
                        <p class="font-semibold"><?php echo htmlspecialchars($registration_no); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Model Name</p>
                        <p class="font-semibold"><?php echo htmlspecialchars($model_name); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Price Per Day</p>
                        <p class="font-semibold">KSH <?php echo number_format($vehicle['price_per_day']); ?></p>
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
                            <select class="form-control" name="charge_type" required>
                                <option value="per_day">Per Day</option>
                                <option value="per_km">Per KM</option>
                            </select>
                        </div>
                        <input type="hidden" name="fare_hidden" id="fare_hidden" value="0">
                        <input type="hidden" name="deposit_hidden" id="deposit_hidden" value="0">
                        <div id="driver_details" class="form-group" style="display:none;">
                            <label class="form-label">Select Driver</label>
                            <select class="form-control" name="driver_id">
                                <?php foreach ($drivers as $driver): ?>
                                    <option value="<?php echo $driver['driver_id']; ?>">
                                        <?php echo htmlspecialchars($driver['name']); ?>
                                    </option>
                                <?php endforeach; ?>
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
        const pricePerDay = <?php echo $vehicle['price_per_day']; ?>;
        const driverOption = document.getElementById('driver_option').value;
        const carType = document.getElementById('car_type').value;
        
        if (startDate && endDate && startDate < endDate) {
            const timeDiff = endDate - startDate;
            const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            const driverCost = driverOption === 'yes' ? 2000 : 0; 
            const acCost = carType === 'With AC' ? 500 : 0;
            
            const chargeType = document.querySelector('select[name="charge_type"]').value;
            let totalFare;
            if (chargeType === 'per_day') {
                totalFare = (pricePerDay * dayDiff) + driverCost + (acCost * dayDiff);
            } else {
                const distance = 1; 
                totalFare = (distance * 2000) + driverCost;
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

    function validateAndSubmit() {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        
        if (endDate <= startDate) {
            alert("End date must be after start date.");
            return false;
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
    document.getElementById('driver_option').addEventListener('change', function() {
        toggleDriverOptions();
        calculateAndDisplayFare();
    });
    document.querySelector('select[name="charge_type"]').addEventListener('change', calculateAndDisplayFare);

    window.addEventListener('load', calculateAndDisplayFare);
</script>
</body>
</html>
