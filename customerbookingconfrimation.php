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

$customer_id = $_SESSION['customer_id'];


$sql = "SELECT b.*, c.full_name, c.email, c.mobile, c.residence,
        CONCAT(
            'INV-', 
            DATE_FORMAT(b.created_at, '%Y%m%d'), 
            '-', 
            WEEK(b.created_at), 
            '-', 
            UNIX_TIMESTAMP(b.created_at),
            '-', 
            LPAD(FLOOR(RAND() * 1000000), 6, '0')
        ) AS invoice_number
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        WHERE b.customer_id = ?
        ORDER BY b.created_at DESC LIMIT 1";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: customer_dashboard.php");
    exit();
}

$booking = $result->fetch_assoc();


$driver_details = null;
if ($booking['driver_option'] === 'yes') {
    
    $driver_sql = "SELECT d.driver_id, d.name, d.contact_no, d.driving_license_no 
                   FROM driver_assignments da
                   JOIN drivers d ON da.driver_id = d.driver_id
                   WHERE da.booking_id = ?";
    $driver_stmt = $conn->prepare($driver_sql);
    $driver_stmt->bind_param("i", $booking['booking_id']);
    $driver_stmt->execute();
    $driver_result = $driver_stmt->get_result();
    
    
    if ($driver_result->num_rows > 0) {
        $driver_details = $driver_result->fetch_assoc();
    }
}



$start_date = new DateTime($booking['start_date']);
$end_date = new DateTime($booking['end_date']);
$duration = $start_date->diff($end_date)->days;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .print-only {
                display: block;
            }
        }
    </style>

 
    <script>
        window.addEventListener('beforeunload', function (e) {
            var confirmationMessage = "Are you sure you want to leave this page? Any unsaved changes will be lost.";
            e.returnValue = confirmationMessage; 
            return confirmationMessage; 
        });
    </script>

</head>
<body class="bg-gray-50">
    
    <nav class="bg-gradient-to-r from-blue-800 to-blue-900 fixed w-full top-0 z-50 px-6 py-4 no-print">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-white tracking-wider">Car Rentals</a>
            <div class="space-x-4">
                <a href="customer_dashboard.php" class="text-white hover:text-blue-200 transition">Dashboard</a>
                <button onclick="window.print()" class="bg-white text-blue-800 px-4 py-2 rounded-lg hover:bg-blue-50 transition">
                    Print Invoice
                </button>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-24 p-6">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
            
            <div class="border-b pb-4 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Booking Confirmation</h1>
                        <p class="text-gray-600 mt-1">Invoice : <?php echo htmlspecialchars($booking['invoice_number']); ?></p>
                        <p class="text-gray-600">Date: <?php echo date('F d, Y', strtotime($booking['created_at'])); ?></p>
                    </div>
                    <div class="text-right">
                        <h2 class="text-xl font-bold text-gray-800">Online Car Rentals</h2>
                        <p class="text-gray-600">Kinadruma Road</p>
                        <p class="text-gray-600">Nairobi, Kenya</p>
                        <p class="text-gray-600">Tel: +254 757 196660</p>
                    </div>
                </div>
            </div>

           
            <div class="grid grid-cols-2 gap-8 mb-8">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Customer Details</h3>
                    <p class="text-gray-700"><strong>Name:</strong> <?php echo htmlspecialchars($booking['full_name']); ?></p>
                    <p class="text-gray-700"><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
                    <p class="text-gray-700"><strong>Phone:</strong> <?php echo htmlspecialchars($booking['mobile']); ?></p>
                    <p class="text-gray-700"><strong>Address:</strong> <?php echo htmlspecialchars($booking['residence']); ?></p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-2">Booking Details</h3>
                    <p class="text-gray-700"><strong>Start Date:</strong> <?php echo date('F d, Y', strtotime($booking['start_date'])); ?></p>
                    <p class="text-gray-700"><strong>End Date:</strong> <?php echo date('F d, Y', strtotime($booking['end_date'])); ?></p>
                    <p class="text-gray-700"><strong>Duration:</strong> <?php echo $duration; ?> days</p>
                    <p class="text-gray-700"><strong>Pick-up Location:</strong> <?php echo htmlspecialchars($booking['pick_up_location']); ?></p>
                    <p class="text-gray-700"><strong>Pick-up Time:</strong> <?php echo date('h:i A', strtotime($booking['pick_up_time'])); ?></p>
                </div>
            </div>

           
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-2">Vehicle Details</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700"><strong>Registration No:</strong> <?php echo htmlspecialchars($booking['registration_no']); ?></p>
                    <p class="text-gray-700"><strong>Model:</strong> <?php echo htmlspecialchars($booking['model_name']); ?></p>
                    <p class="text-gray-700"><strong>Type:</strong> <?php echo htmlspecialchars($booking['car_type']); ?></p>
                    <p class="text-gray-700"><strong>Charge Type:</strong> <?php echo htmlspecialchars($booking['charge_type']); ?></p>
                </div>
            </div>

            <?php if ($driver_details): ?>
          
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-2">Assigned Driver</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700"><strong>Name:</strong> <?php echo htmlspecialchars($driver_details['name']); ?></p>
                    <p class="text-gray-700"><strong>Phone:</strong> <?php echo htmlspecialchars($driver_details['contact_no']); ?></p>
                    <p class="text-gray-700"><strong>License No:</strong> <?php echo htmlspecialchars($driver_details['driving_license_no']); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="border-t pt-6">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-semibold">Total Fare:</span>
                    <span class="text-2xl font-bold text-blue-600">KSH <?php echo number_format($booking['total_fare'], 2); ?></span>
                </div>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-semibold">Advance Deposit (70%):</span>
                    <span class="text-xl text-green-600">KSH <?php echo number_format($booking['advance_deposit'], 2); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold">Balance Due:</span>
                    <span class="text-xl text-red-600">KSH <?php echo number_format($booking['total_fare'] - $booking['advance_deposit'], 2); ?></span>
                </div>
            </div>

            
            <div class="mt-6 text-center">
                <a href="make_payment.php?booking_id=<?php echo $booking['booking_id']; ?>" 
                   class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg text-lg hover:bg-blue-700 transition duration-200">
                    Make Payment
                </a>
            </div>

            
            <div class="mt-8 text-sm text-gray-600">
                <h4 class="font-semibold mb-2">Terms and Conditions:</h4>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Balance payment must be made before vehicle collection</li>
                    <li>Please bring valid identification and driver's license</li>
                    <li>Cancellation charges may apply as per our policy</li>
                    <li>Vehicle must be returned in the same condition as received</li>
                    <li>Additional charges may apply for late returns</li>
                </ul>
            </div>

            
            <div class="mt-8 text-center text-gray-600">
                <p>Thank you for choosing our service!</p>
                <p>For any queries, please contact our support team.</p>
            </div>
        </div>
    </div>
</body>
</html>
