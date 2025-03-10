<?php
require 'include/db_connection.php';


if (isset($_GET['delete_invoice_id'])) {
    $delete_id = intval($_GET['delete_invoice_id']);
    
    
    $conn->begin_transaction();
    
    try {
        
        $delete_sql = "DELETE FROM bookings WHERE booking_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $delete_id);
        $result = $stmt->execute();
        
        if ($result) {
            $conn->commit();
            $_SESSION['success_message'] = "Invoice deleted successfully.";
        } else {
            throw new Exception("Failed to delete invoice.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    
    
    header("Location: invoices.php");
    exit();
}


$sql = "SELECT b.booking_id, b.created_at, b.total_fare, b.invoice_number, 
            c.full_name, c.email, c.mobile, c.residence
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        ORDER BY b.created_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices -Admin Panel</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    
    <header class="bg-gradient-to-r from-blue-800 to-blue-900 fixed w-full top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                   
                    <button id="sidebarToggle" class="text-white mr-4 lg:hidden">
                        <i class="fas fa-bars"></i>
                    </button>
                    <a href="dashboard.php" class="text-2xl font-bold text-white tracking-wider" ><i class="fas fa-car"></i><b>
                    Online Car Rental Admin Panel</b></a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="logout.php" class="text-white hover:text-blue-200 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    
   <?php include('include/sidebar.php') ?>

  
    <main  class="main-content">
        <div class="container mx-auto p-6">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-500 text-white p-4 mb-4 rounded">
                    <?php echo $_SESSION['success_message']; ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-500 text-white p-4 mb-4 rounded">
                    <?php echo $_SESSION['error_message']; ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">All Invoices</h1>

                <?php if ($result->num_rows > 0): ?>
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Invoice No</th>
                            <th class="px-4 py-2 text-left">Customer</th>
                            <th class="px-4 py-2 text-left">Booking Date</th>
                            <th class="px-4 py-2 text-left">Total Fare</th>
                            <th class="px-4 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($invoice = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($invoice['full_name']); ?></td>
                            <td class="px-4 py-2"><?php echo date('F d, Y', strtotime($invoice['created_at'])); ?></td>
                            <td class="px-4 py-2">KSH <?php echo number_format($invoice['total_fare'], 2); ?></td>
                            <td class="px-4 py-2">
                                <a href="invoicedetails.php?invoice_id=<?php echo $invoice['booking_id']; ?>" class="text-blue-600 hover:underline">View</a> |
                                <!--<a href="?delete_invoice_id=<?php echo $invoice['booking_id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this invoice?');">Delete</a>-->
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="text-gray-600">No invoices found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        });
    </script>
</body>
</html>