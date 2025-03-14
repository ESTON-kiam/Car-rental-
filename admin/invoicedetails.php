<?php
require 'include/db_connection.php';
$invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : 0;

if ($invoice_id == 0) {
    die("Invalid Invoice ID.");
}

$sql = "SELECT b.booking_id, b.created_at, b.total_fare, b.vehicle_id, b.driver_option, 
            b.invoice_number, b.start_date, b.end_date,
            c.full_name, c.email, c.mobile, c.residence,
            v.model_name, v.registration_no,
            IF(b.driver_option = 'yes', d.name, NULL) AS driver_name,
            IF(b.driver_option = 'yes', d.contact_no, NULL) AS driver_phone
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN vehicles v ON b.vehicle_id = v.vehicle_id
        LEFT JOIN drivers d ON b.driver_option = 'yes' 
        WHERE b.booking_id = ? 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Invoice not found.");
}

$invoice = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Details-Admin Panel</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script>
        window.addEventListener("beforeunload", function (event) {
            var message = "Are you sure you want to leave this page? You might lose your changes.";
            event.returnValue = message; 
            return message; 
        });
    </script>
</head>
<body class="bg-gray-50">
    <nav class="bg-gradient-to-r from-blue-800 to-blue-900 fixed w-full top-0 z-50 px-6 py-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-bold text-white tracking-wider">Car Rentals</a>
            <div class="space-x-4">
                <a href="dashboard.php" class="text-white hover:text-blue-200 transition">Dashboard</a>
                <a href="logout.php" class="text-white hover:text-blue-200 transition">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-24 p-6">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Invoice Details</h1>

            <div class="mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Invoice Information</h2>
                <p><strong>Invoice No:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
                <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($invoice['full_name']); ?></p>
                <p><strong>Customer Email:</strong> <?php echo htmlspecialchars($invoice['email']); ?></p>
                <p><strong>Customer Mobile:</strong> <?php echo htmlspecialchars($invoice['mobile']); ?></p>
                <p><strong>Customer Residence:</strong> <?php echo htmlspecialchars($invoice['residence']); ?></p>
                <p><strong>Booking Date:</strong> <?php echo date('F d, Y', strtotime($invoice['created_at'])); ?></p>
                <p><strong>Rental Period:</strong> <?php echo date('F d, Y', strtotime($invoice['start_date'])); ?> to <?php echo date('F d, Y', strtotime($invoice['end_date'])); ?></p>
                <p><strong>Total Fare:</strong> KSH <?php echo number_format($invoice['total_fare'], 2); ?></p>
            </div>

            <div class="mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Vehicle Information</h2>
                <p><strong>Model Name:</strong> <?php echo htmlspecialchars($invoice['model_name']); ?></p>
                <p><strong>Registration No:</strong> <?php echo htmlspecialchars($invoice['registration_no']); ?></p>
            </div>

            <div class="mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Driver Information</h2>
                <?php if ($invoice['driver_option'] == 'yes' && $invoice['driver_name'] && $invoice['driver_phone']) { ?>
                    <p><strong>Driver Name:</strong> <?php echo htmlspecialchars($invoice['driver_name']); ?></p>
                    <p><strong>Driver Phone:</strong> <?php echo htmlspecialchars($invoice['driver_phone']); ?></p>
                <?php } else { ?>
                    <p><strong>Driver Option:</strong> No driver requested</p>
                <?php } ?>
            </div>

            <div class="mt-8 flex space-x-4">
                <a href="invoices.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Back to All Invoices</a>
                <button onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Print Invoice</button>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>