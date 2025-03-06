<?php

require_once 'include/db_connection.php';

$customer_id = $_SESSION['customer_id'];

// Modified SQL to fetch the stored invoice_number
$sql = "SELECT b.*, c.full_name, c.email, c.mobile, c.residence
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        WHERE b.customer_id = ?
        ORDER BY b.created_at DESC LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$booking = $result->fetch_assoc();

// Check if the invoice number exists, if not, generate and save it
if (empty($booking['invoice_number'])) {
    // Generate invoice number
    $invoice_number = 'INV-' . 
                      date('Ymd', strtotime($booking['created_at'])) . 
                      '-' . 
                      str_pad($booking['booking_id'], 4, '0', STR_PAD_LEFT) . 
                      '-' . 
                      substr(md5($booking['booking_id'] . $booking['customer_id'] . $booking['created_at']), 0, 6);
    
    // Save to database
    $update_sql = "UPDATE bookings SET invoice_number = ? WHERE booking_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $invoice_number, $booking['booking_id']);
    $update_stmt->execute();
    
    // Update the booking array
    $booking['invoice_number'] = $invoice_number;
}

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

$vehicle_sql = "SELECT price_per_day, ac_price_per_day, non_ac_price_per_day, km_price 
               FROM vehicles WHERE vehicle_id = ?";
$vehicle_stmt = $conn->prepare($vehicle_sql);
$vehicle_stmt->bind_param("i", $booking['vehicle_id']);
$vehicle_stmt->execute();
$vehicle_result = $vehicle_stmt->get_result();
$vehicle_pricing = $vehicle_result->fetch_assoc();

$base_price = 0;
$driver_cost = 0;
$total_days = $duration;

if ($booking['driver_option'] === 'yes') {
    $driver_cost = 2000 * $total_days; 
}

if ($booking['charge_type'] === 'per_day') {
    if ($booking['car_type'] === 'With AC') {
        $base_price = $vehicle_pricing['ac_price_per_day'] * $total_days;
    } else {
        $base_price = $vehicle_pricing['non_ac_price_per_day'] * $total_days;
    }
} else {
    $kilometers = $booking['kilometers'] ?? 0;
    $base_price = $vehicle_pricing['km_price'] * $kilometers;
}

$total_fare = $base_price + $driver_cost;
$deposit = $total_fare * 0.7;
$balance = $total_fare - $deposit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation | Invoice #<?php echo htmlspecialchars($booking['invoice_number']); ?></title>
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
            body {
                font-size: 12pt;
            }
            .container {
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0;
            }
        }
        
        .invoice-header {
            border-bottom: 2px solid #3B82F6;
        }
        
        .section-heading {
            color: #1E40AF;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 0.5rem;
        }
        
        .price-row {
            transition: background-color 0.2s;
        }
        
        .price-row:hover {
            background-color: #F3F4F6;
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
                <a href="dashboard.php" class="text-white hover:text-blue-200 transition">Dashboard</a>
                <button onclick="window.print()" class="bg-white text-blue-800 px-4 py-2 rounded-lg hover:bg-blue-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Invoice
                </button>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-24 p-6">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
            
            <div class="invoice-header pb-6 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Booking Confirmation</h1>
                        <p class="text-gray-600 mt-1">Invoice: <span class="font-medium"><?php echo htmlspecialchars($booking['invoice_number']); ?></span></p>
                        <p class="text-gray-600">Date: <?php echo date('F d, Y', strtotime($booking['created_at'])); ?></p>
                        <p class="text-gray-600">Status: <span class="font-medium text-blue-600"><?php echo ucfirst(htmlspecialchars($booking['booking_status'])); ?></span></p>
                    </div>
                    <div class="text-right">
                        <h2 class="text-xl font-bold text-gray-800">Online Car Rentals</h2>
                        <p class="text-gray-600">Kinadruma Road</p>
                        <p class="text-gray-600">Nairobi, Kenya</p>
                        <p class="text-gray-600">Tel: +254 757 196660</p>
                        <p class="text-gray-600">info@carrentals.co.ke</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <h3 class="section-heading text-lg font-semibold mb-3">Customer Details</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700"><strong>Name:</strong> <?php echo htmlspecialchars($booking['full_name']); ?></p>
                        <p class="text-gray-700"><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
                        <p class="text-gray-700"><strong>Phone:</strong> <?php echo htmlspecialchars($booking['mobile']); ?></p>
                        <p class="text-gray-700"><strong>Address:</strong> <?php echo htmlspecialchars($booking['residence']); ?></p>
                    </div>
                </div>
                <div>
                    <h3 class="section-heading text-lg font-semibold mb-3">Booking Details</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700"><strong>Start Date:</strong> <?php echo date('F d, Y', strtotime($booking['start_date'])); ?></p>
                        <p class="text-gray-700"><strong>End Date:</strong> <?php echo date('F d, Y', strtotime($booking['end_date'])); ?></p>
                        <p class="text-gray-700"><strong>Duration:</strong> <?php echo $duration; ?> days</p>
                        <p class="text-gray-700"><strong>Pick-up Location:</strong> <?php echo htmlspecialchars($booking['pick_up_location']); ?></p>
                        <p class="text-gray-700"><strong>Pick-up Time:</strong> <?php echo date('h:i A', strtotime($booking['pick_up_time'])); ?></p>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h3 class="section-heading text-lg font-semibold mb-3">Vehicle Details</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-700"><strong>Registration No:</strong> <?php echo htmlspecialchars($booking['registration_no']); ?></p>
                            <p class="text-gray-700"><strong>Model:</strong> <?php echo htmlspecialchars($booking['model_name']); ?></p>
                            <p class="text-gray-700"><strong>Type:</strong> <?php echo htmlspecialchars($booking['car_type']); ?></p>
                            <p class="text-gray-700"><strong>Charge Type:</strong> <?php echo htmlspecialchars($booking['charge_type']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-700"><strong>Standard Price/Day:</strong> KSH <?php echo number_format($vehicle_pricing['price_per_day'], 2); ?></p>
                            <p class="text-gray-700"><strong>With AC/Day:</strong> KSH <?php echo number_format($vehicle_pricing['ac_price_per_day'], 2); ?></p>
                            <p class="text-gray-700"><strong>Without AC/Day:</strong> KSH <?php echo number_format($vehicle_pricing['non_ac_price_per_day'], 2); ?></p>
                            <p class="text-gray-700"><strong>Price Per KM:</strong> KSH <?php echo number_format($vehicle_pricing['km_price'], 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($driver_details): ?>
            <div class="mb-8">
                <h3 class="section-heading text-lg font-semibold mb-3">Assigned Driver</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700"><strong>Name:</strong> <?php echo htmlspecialchars($driver_details['name']); ?></p>
                    <p class="text-gray-700"><strong>Phone:</strong> <?php echo htmlspecialchars($driver_details['contact_no']); ?></p>
                    <p class="text-gray-700"><strong>License No:</strong> <?php echo htmlspecialchars($driver_details['driving_license_no']); ?></p>
                    <p class="text-gray-700"><strong>Daily Rate:</strong> KSH 2,000.00</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="mb-8">
                <h3 class="section-heading text-lg font-semibold mb-3">Price Breakdown</h3>
                <table class="min-w-full">
                    <tbody>
                        <?php if ($booking['charge_type'] === 'per_day'): ?>
                        <tr class="price-row">
                            <td class="py-2">Base Rate (<?php echo htmlspecialchars($booking['car_type']); ?>)</td>
                            <td class="py-2">KSH <?php echo number_format($booking['car_type'] === 'With AC' ? $vehicle_pricing['ac_price_per_day'] : $vehicle_pricing['non_ac_price_per_day'], 2); ?> × <?php echo $duration; ?> days</td>
                            <td class="py-2 text-right font-medium">KSH <?php echo number_format($base_price, 2); ?></td>
                        </tr>
                        <?php else: ?>
                        <tr class="price-row">
                            <td class="py-2">Distance Rate</td>
                            <td class="py-2">KSH <?php echo number_format($vehicle_pricing['km_price'], 2); ?> × <?php echo htmlspecialchars($booking['kilometers'] ?? 0); ?> km</td>
                            <td class="py-2 text-right font-medium">KSH <?php echo number_format($base_price, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($booking['driver_option'] === 'yes'): ?>
                        <tr class="price-row">
                            <td class="py-2">Driver Service</td>
                            <td class="py-2">KSH 2,000.00 × <?php echo $duration; ?> days</td>
                            <td class="py-2 text-right font-medium">KSH <?php echo number_format($driver_cost, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr class="price-row bg-gray-50">
                            <td class="py-2 font-semibold">Subtotal</td>
                            <td class="py-2"></td>
                            <td class="py-2 text-right font-semibold">KSH <?php echo number_format($total_fare, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

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

            <div class="mt-6 text-center no-print">
                <a href="make_payment.php?booking_id=<?php echo $booking['booking_id']; ?>" 
                   class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg text-lg hover:bg-blue-700 transition duration-200">
                    Make Payment
                </a>
            </div>

            <div class="mt-8 text-sm text-gray-600 border-t border-gray-200 pt-4">
                <h4 class="font-semibold mb-2">Terms and Conditions:</h4>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Balance payment must be made before vehicle collection</li>
                    <li>Please bring valid identification and driver's license</li>
                    <li>Cancellation charges may apply as per our policy</li>
                    <li>Vehicle must be returned in the same condition as received</li>
                    <li>Additional charges may apply for late returns</li>
                    <li>Fuel is not included in the rental price</li>
                    <li>Insurance coverage details are provided separately</li>
                    <li>Additional charges should be paid in cash during return day</li>
                </ul>
            </div>

            <div class="mt-8 text-center text-gray-600">
                <p class="font-medium">Thank you for choosing our service!</p>
                <p>For any queries, please contact our support team at +254 757 196660 or support@carrentals.co.ke</p>
                <p class="mt-2 text-xs">This is a computer-generated invoice and requires no signature</p>
            </div>
        </div>
    </div>
</body>
</html>