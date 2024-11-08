<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_name('driver_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; script-src 'self' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com;");

if (!isset($_SESSION['driver_id']) || !is_numeric($_SESSION['driver_id'])) {
    header("Location: Driver_login.php");
    exit();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'car_rental_management');

function fetchBookings($conn, $query, $driver_id) {
    try {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $driver_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        return $result;
    } catch (Exception $e) {
        error_log("Error fetching bookings: " . $e->getMessage());
        return false;
    }
}

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    $driver_id = (int)$_SESSION['driver_id'];
    
    $completedBookingsQuery = "
        SELECT 
            ct.task_id,
            ct.assignment_id,
            ct.booking_id,
            ct.vehicle_id,
            ct.registration_no,
            ct.model_name,
            ct.completed_at,
            ct.created_at
        FROM completed_tasks ct
        WHERE ct.driver_id = ?
        ORDER BY ct.created_at DESC";
    
    $completedBookings = fetchBookings($conn, $completedBookingsQuery, $driver_id);
    
    if ($completedBookings === false) {
        throw new Exception("Error fetching completed bookings");
    }
    
} catch (Exception $e) {
    error_log("Error in driver dashboard: " . $e->getMessage());
    $error_message = "An error occurred. Please try again later.";
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Driver Completed Tasks Dashboard</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f6f8;
        }

        .header {
            background-color: #1a237e;
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-link {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            background-color: rgba(255,255,255,0.1);
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .dashboard-link:hover {
            background-color: rgba(255,255,255,0.2);
        }

        .main-content {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .content-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            padding: 1.5rem;
        }

        .section-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eef2f7;
        }

        .booking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .booking-table th,
        .booking-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eef2f7;
        }

        .booking-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .booking-table tr:hover {
            background-color: #f8f9fa;
        }

        .no-bookings {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-top: 1rem;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            text-align: center;
        }

      
        .delete-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            background-color: #ff4444;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .delete-button:hover {
            background-color: #cc0000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transform: translateY(-1px);
        }

        .delete-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .delete-button i {
            margin-right: 8px;
            font-size: 14px;
        }

       
        .booking-table td:last-child {
            text-align: center;
        }

       
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .delete-button:hover i {
            animation: pulse 1s infinite;
        }

       
        .delete-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 68, 68, 0.5);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1><i class="fas fa-car"></i> Driver Completed Tasks Dashboard</h1>
            <a href="driverdashboard.php" class="dashboard-link">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </header>

    <main class="main-content">
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <section id="completed-bookings" class="content-section">
            <div class="section-header">
                <h2>Completed Tasks</h2>
            </div>

            <?php if (isset($completedBookings) && $completedBookings->num_rows > 0): ?>
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Task ID</th>
                            <th>Assignment ID</th>
                            <th>Booking ID</th>
                            <th>Vehicle Model</th>
                            <th>Registration No</th>
                            <th>Completed At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $completedBookings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string)$booking['task_id']); ?></td>
                                <td><?php echo htmlspecialchars((string)$booking['assignment_id']); ?></td>
                                <td><?php echo htmlspecialchars((string)$booking['booking_id']); ?></td>
                                <td><?php echo htmlspecialchars($booking['model_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['registration_no']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($booking['completed_at']))); ?></td>
                                <td>
                                    <a href="delete_tasks.php?task_id=<?php echo urlencode((string)$booking['task_id']); ?>" 
                                       onclick="return confirm('Are you sure you want to delete this task?');"
                                       class="delete-button">
                                        <i class="fas fa-trash"></i>Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-bookings">No completed tasks found.</div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>