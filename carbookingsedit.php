<?php
session_name('admin_session');
session_set_cookie_params(1800); 
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: admin_login.php");
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

$booking = null;

if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    if (!$booking) {
        echo "Booking not found.";
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_booking'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $total_fare = $_POST['total_fare'];

    // Use the correct variable for booking status
    $update_query = "UPDATE bookings SET booking_status = ?, start_date = ?, end_date = ?, total_fare = ? WHERE booking_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssdi", $status, $start_date, $end_date, $total_fare, $booking_id);
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?msg=updated");
        exit();
    } else {
        $error_message = "Failed to update booking.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --danger-color: #dc2626;
            --success-color: #059669;
            --border-radius: 12px;
            --padding: 1rem;
        }

        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            color: #333;
        }

        /* Form Styling */
        .container {
            max-width: 600px;
            margin: 3rem auto;
            background: #fff;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: var(--primary-color);
        }

        label {
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: block;
            color: #444;
        }

        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: var(--padding);
            margin-bottom: 1.2rem;
            border-radius: var(--border-radius);
            border: 1px solid #ddd;
            font-size: 1rem;
            outline: none;
            box-sizing: border-box;
        }

        .btn-primary {
            width: 100%;
            padding: var(--padding);
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        .status-success,
        .status-failure {
            padding: var(--padding);
            text-align: center;
            margin-top: 1rem;
            border-radius: var(--border-radius);
        }

        .status-success {
            background-color: #e6ffed;
            color: var(--success-color);
        }

        .status-failure {
            background-color: #fcebea;
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Booking</h2>
      

        <form method="POST">
            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">

            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="pending" <?php if ($booking['booking_status'] == 'pending') echo 'selected'; ?>>Pending</option>
                <option value="active" <?php if ($booking['booking_status'] == 'active') echo 'selected'; ?>>Active</option>
                <option value="completed" <?php if ($booking['booking_status'] == 'completed') echo 'selected'; ?>>Completed</option>
            </select>

            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($booking['start_date']); ?>" required>

            <label for="end_date">End Date</label>
            <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($booking['end_date']); ?>" required>

            <label for="total_fare">Total Fare (KSH)</label>
            <input type="text" name="total_fare" id="total_fare" value="<?php echo htmlspecialchars($booking['total_fare']); ?>" required pattern="\d+(\.\d{1,2})?" title="Enter a valid fare amount.">

            <button type="submit" name="update_booking" class="btn-primary"><i class="fas fa-save"></i> Update Booking</button>
        </form>
    </div>

    <script>
        setTimeout(() => {
            const alert = document.querySelector('.status-success, .status-failure');
            if (alert) alert.style.display = 'none';
        }, 5000);
    </script>
</body>
</html>
