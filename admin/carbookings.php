<?php
require_once 'include/db_connection.php';

if (isset($_POST['delete_booking'])) {
    $booking_id = $_POST['booking_id'];

    
    $delete_assignments_query = "DELETE FROM driver_assignments WHERE booking_id = ?";
    $stmt = $conn->prepare($delete_assignments_query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();

    
    $delete_booking_query = "DELETE FROM bookings WHERE booking_id = ?";
    $stmt = $conn->prepare($delete_booking_query);
    $stmt->bind_param("i", $booking_id);
    if ($stmt->execute()) {
        if (!headers_sent()) {
            header("Location: carbookings.php?msg=deleted");
        } else {
            echo "<script>window.location.href='carbookings.php?msg=deleted';</script>";
        }
        exit();
    }
}


$query = "SELECT b.*, v.model_name, c.full_name as customer_name 
          FROM bookings b 
          JOIN vehicles v ON b.vehicle_id = v.vehicle_id 
          JOIN customers c ON b.customer_id = c.id 
          ORDER BY b.booking_date DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Bookings Management</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/carbookings.css" rel="stylesheet">
</head>
<body>
     <nav class="navbar">
    
       <a href="dashboard.php" class="navbar-brand">
        <i class="fas fa-car"></i><b>
                Online Car Rental Admin Panel</b></a>
        <a href="dashboard.php" class="dashboard-btn">Dashboard</a>
        
    </nav>
    
    <?php include('include/sidebar.php') ?><main class="main-content">
    <div class="container">
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-success">
                    Booking has been successfully deleted.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <h1>Car Bookings</h1>
        
        <table class="bookings-table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Total Fare</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['booking_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['model_name']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['start_date'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['end_date'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo isset($row['booking_status']) ? strtolower($row['booking_status']) : 'unknown'; ?>">
                                <?php echo isset($row['booking_status']) ? $row['booking_status'] : 'Unknown'; ?>
                            </span>
                        </td>
                        <td>KSH <?php echo number_format($row['total_fare'], 2); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="carbookingsedit.php?id=<?php echo $row['booking_id']; ?>" 
                                   class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="" method="POST" style="display: inline;"
                                      onsubmit="return confirm('Are you sure you want to delete this booking?')">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                    <button type="submit" name="delete_booking" class="btn btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                
                <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">
                            No bookings found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
    <script>
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
