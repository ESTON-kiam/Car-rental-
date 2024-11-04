<?php 
session_name('customer_session');
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php"); 
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

// Get vehicle ID from the URL
$vehicle_id = $_GET['id'] ?? null;

if ($vehicle_id) {
    $sql = "SELECT * FROM vehicles WHERE vehicle_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $vehicle = $result->fetch_assoc();
    } else {
        echo "Vehicle not found.";
        exit();
    }
} else {
    echo "No vehicle selected.";
    exit();
}

// Check if the customer already has an active or pending booking
$existing_booking_sql = "SELECT * FROM bookings WHERE customer_id = ? AND booking_status != 'completed'";
$existing_booking_stmt = $conn->prepare($existing_booking_sql);
$existing_booking_stmt->bind_param("i", $_SESSION['customer_id']);
$existing_booking_stmt->execute();
$existing_booking_result = $existing_booking_stmt->get_result();

if ($existing_booking_result->num_rows > 0) {
    echo "You cannot book another vehicle until your previous booking is completed.";
    exit();
}

// Fetch drivers from the database
$drivers = [];
$driver_sql = "SELECT driver_id, name FROM drivers WHERE availability_status = 'Available'";
$driver_result = $conn->query($driver_sql);

if ($driver_result->num_rows > 0) {
    while($row = $driver_result->fetch_assoc()) {
        $drivers[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $pick_up_location = $_POST['pick_up_location'];
    $pick_up_time = $_POST['pick_up_time'];
    $car_type = $_POST['car_type'];
    $charge_type = $_POST['charge_type'];
    $driver_option = $_POST['driver_option'];
    $advance_deposit = $_POST['advance_deposit'];
    $fare = $_POST['fare'];
    
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Update vehicle availability status
        $update_sql = "UPDATE vehicles SET availability_status = 'Unavailable' WHERE vehicle_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $vehicle_id);
        $update_stmt->execute();

        // Insert booking record
        $booking_sql = "INSERT INTO bookings (vehicle_id, customer_id, start_date, end_date, pick_up_location, 
                       pick_up_time, car_type, charge_type, driver_option, total_fare, advance_deposit, booking_status) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $booking_stmt = $conn->prepare($booking_sql);
        $booking_stmt->bind_param("iisssssssdd", 
            $vehicle_id, 
            $_SESSION['customer_id'],
            $start_date,
            $end_date,
            $pick_up_location,
            $pick_up_time,
            $car_type,
            $charge_type,
            $driver_option,
            $fare,
            $advance_deposit
        );
        $booking_stmt->execute();

        // If driver is selected, add driver assignment
        if ($driver_option === 'yes' && isset($_POST['driver_id'])) {
            $driver_assign_sql = "INSERT INTO driver_assignments (booking_id, driver_id) VALUES (LAST_INSERT_ID(), ?)";
            $driver_assign_stmt = $conn->prepare($driver_assign_sql);
            $driver_assign_stmt->bind_param("i", $_POST['driver_id']);
            $driver_assign_stmt->execute();

            // Update driver availability
            $update_driver_sql = "UPDATE drivers SET availability_status = 'Unavailable' WHERE driver_id = ?";
            $update_driver_stmt = $conn->prepare($update_driver_sql);
            $update_driver_stmt->bind_param("i", $_POST['driver_id']);
            $update_driver_stmt->execute();
        }

        // Commit transaction
        $conn->commit();
        
        // Redirect to customer dashboard
        header("Location: customerdashboard.php");
        exit();
    } catch (Exception $e) {
       
        $conn->rollback();
        echo "Error: " . $e->getMessage();
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Car - Premium Booking Experience</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
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

        .navbar-custom {
            background-color: #1e293b; /* Updated header background color */
            padding: 1.25rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .navbar-brand {
            font-weight: 800;
            color: white !important;
            font-size: 1.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-primary {
            color: white; /* Updated button color */
            border: 2px solid white; /* Updated button border */
        }

        .btn-outline-primary:hover {
            background: white;
            color: var(--primary-color);
        }

        .main-container {
            margin-top: 7rem;
            padding-bottom: 2rem;
        }

        .booking-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .booking-header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .booking-header h2 {
            color: var(--text-color);
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: none;
        }

        .vehicle-details {
            background: linear-gradient(145deg, #ffffff, var(--light-gray));
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .fare-display {
            background: linear-gradient(145deg, var(--accent-color), #ffffff);
            padding: 2rem;
            border-radius: var(--border-radius);
            margin: 2.5rem 0;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--accent-color);
        }

        .fare-label {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .fare-amount {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-color);
            text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.1);
        }

        .btn-confirm {
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            color: white;
            padding: 1.25rem 2rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15);
        }

        .footer {
            text-align: center;
            padding: 2rem 0;
            color: #6b7280;
            font-size: 0.875rem;
            background: white;
            margin-top: 3rem;
            border-top: 1px solid var(--light-gray);
        }

        @media (max-width: 768px) {
            .main-container {
                margin-top: 5rem;
                padding: 1rem;
            }
            
            .booking-card {
                padding: 1.5rem;
            }

            .booking-header h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom navbar-fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Car Rentals</a>
            <div class="ms-auto">
                <a href="customer_dashboard.php" class="btn btn-outline-primary me-2">Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="booking-header">
            <h2><?php echo htmlspecialchars($vehicle['model_name']); ?></h2>
            <div class="vehicle-details">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Registration:</strong> <?php echo htmlspecialchars($vehicle['registration_no']); ?></p>
                        <p><strong>Price per Day:</strong> KSH <?php echo number_format($vehicle['price_per_day'], 2); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($vehicle['description']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <form action="" method="POST" onsubmit="return validateAndSubmit()">
            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['vehicle_id']; ?>">
            <input type="hidden" name="fare" id="fare">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="start_date">Start Date</label>
                        <input type="date" class="form-control" name="start_date" id="start_date" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="end_date">End Date</label>
                        <input type="date" class="form-control" name="end_date" id="end_date" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="pick_up_location">Pick-Up Location</label>
                        <input type="text" class="form-control" name="pick_up_location" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="pick_up_time">Pick-Up Time</label>
                        <input type="time" class="form-control" name="pick_up_time" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="car_type">Car Type</label>
                        <select class="form-control" name="car_type" id="car_type" required onchange="calculateAndDisplayFare()">
                            <option value="With AC">With AC</option>
                            <option value="Without AC">Without AC</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="charge_type">Charge Type</label>
                        <select class="form-control" name="charge_type" required>
                            <option value="per_day">Per Day</option>
                            <option value="per_km">Per KM</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="driver_option">Driver Service</label>
                <select class="form-control" name="driver_option" id="driver_option" onchange="toggleDriverOptions()">
                    <option value="no">No Driver Needed</option>
                    <option value="yes">Include Driver</option>
                </select>
            </div>

            <div id="driver_details" style="display:none;">
                <div class="form-group">
                    <label class="form-label" for="driver_id">Select Driver</label>
                    <select class="form-control" name="driver_id">
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?php echo $driver['driver_id']; ?>"><?php echo htmlspecialchars($driver['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="fare-display">
                <div class="fare-label">Total Fare</div>
                <div class="fare-amount" id="fare_display">KSH 0.00</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="advance_deposit">Advance Deposit</label>
                <input type="number" class="form-control" name="advance_deposit" required>
            </div>

            <button type="submit" class="btn btn-confirm w-100">Confirm Booking</button>
        </form>
    </div>

    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> Car Rentals. All rights reserved.</p>
    </div>

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
                
                // Calculate fare based on charge type
                const chargeType = document.querySelector('select[name="charge_type"]').value;
                let totalFare;
                if (chargeType === 'per_day') {
                    totalFare = (pricePerDay * dayDiff) + driverCost + (acCost * dayDiff);
                } else {
                    const distance = 1; // Assuming 1 km for calculation
                    totalFare = (distance * 2000) + driverCost; // KSH 2000 per km
                }
                
                document.getElementById('fare').value = totalFare;
                document.getElementById('fare_display').textContent = `KSH ${totalFare.toLocaleString()}`;
            } else {
                document.getElementById('fare_display').textContent = 'KSH 0.00';
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
            return true;
        }

        // Add event listeners for real-time fare calculation
        document.getElementById('start_date').addEventListener('change', calculateAndDisplayFare);
        document.getElementById('end_date').addEventListener('change', calculateAndDisplayFare);
        document.getElementById('car_type').addEventListener('change', calculateAndDisplayFare);
        document.getElementById('driver_option').addEventListener('change', calculateAndDisplayFare);
    </script>
</body>
</html>
