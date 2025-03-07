<?php
include 'include/db_connection.php'; 

$query = "SELECT c.full_name AS customer_name, cb.booking_id, cb.cancellation_reason 
          FROM cancelledbookings cb
          JOIN customers c ON cb.customer_id = c.id
          ORDER BY cb.cancellation_date DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelled Bookings-Admin Panel</title>
    <link rel="stylesheet" href="styles.css"> 
    <link rel="stylesheet" href="assets/css/cancelledbookings.css">
</head>
<body>
<nav class="navbar">
    
    <a href="dashboard.php" class="navbar-brand">
     <i class="fas fa-car"></i><b>
             Online Car Rental Admin Panel</b></a>       
 </nav>
    <?php include 'include/sidebar.php'; ?>
    <main class="main-content">
    <div class="container">
        <h2>Cancelled Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Booking ID</th>
                    <th>Cancellation Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['cancellation_reason']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>