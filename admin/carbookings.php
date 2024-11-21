<?php
session_name('admin_session');
session_set_cookie_params(1800); 
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: http://localhost:8000/admin/");
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
        header("Location: carbookings.php?msg=deleted");
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
    <style> :root { --primary-color: #2563eb; --secondary-color: #1e40af; --accent-color: #dbeafe; --text-color: #1f2937; --light-gray: #f3f4f6; --danger-color: #dc2626; --success-color: #059669; --border-radius: 12px; } body { font-family: 'Inter', system-ui, -apple-system, sans-serif; margin: 0; padding: 0; background: #f8fafc; color: var(--text-color); } .navbar { background: #1e293b; padding: 1rem 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; position: fixed; width: 100%; top: 0; z-index: 1000; } .navbar-brand { color: white; font-size: 1.5rem; font-weight: 700; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px; } .dashboard-btn { background: transparent; color: white; border: 2px solid white; padding: 0.5rem 1rem; border-radius: var(--border-radius); text-decoration: none; transition: all 0.3s ease; } .dashboard-btn:hover { background: white; color: var(--primary-color); } .container { max-width: 1200px; margin: 6rem auto 2rem; padding: 0 1rem; } .bookings-table { width: 100%; background: white; border-radius: var(--border-radius); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-collapse: collapse; margin-top: 2rem; } .bookings-table th, .bookings-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--light-gray); } .bookings-table th { background: #f8fafc; font-weight: 600; color: var(--text-color); } .bookings-table tr:hover { background: #f8fafc; } .status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; } .status-active { background: #dcfce7; color: var(--success-color); } .status-completed { background: #dbeafe; color: var(--primary-color); } .status-cancelled { background: #fee2e2; color: var(--danger-color); } .action-buttons { display: flex; gap: 0.5rem; } .btn { padding: 0.5rem 1rem; border-radius: var(--border-radius); border: none; cursor: pointer; font-size: 0.875rem; transition: all 0.3s ease; } .btn-edit { background: var(--primary-color); color: white; } .btn-edit:hover { background: var(--secondary-color); } .btn-delete { background: var(--danger-color); color: white; } .btn-delete:hover { background: #b91c1c; } .alert { padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1rem; } .alert-success { background: #dcfce7; color: var(--success-color); border: 1px solid #86efac; } .alert-danger { background: #fee2e2; color: var(--danger-color); border: 1px solid #fecaca; } @media (max-width: 768px) { .container { padding: 0 0.5rem; } .bookings-table { display: block; overflow-x: auto; } .action-buttons { flex-direction: column; } } </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">Car Rentals</a>
        <a href="dashboard.php" class="dashboard-btn">Dashboard</a>
    </nav>

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
