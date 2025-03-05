<?php
require 'include/db_connection.php';

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

   
    $update_query = "UPDATE bookings SET booking_status = ?, start_date = ?, end_date = ?, total_fare = ? WHERE booking_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssdi", $status, $start_date, $end_date, $total_fare, $booking_id);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?msg=updated");
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
    <title>Admin Edit Booking</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
   <link href="assets/css/carbookingsedit.css" rel="stylesheet">
</head>
<body>
<?php include('include/header.php') ?>
<?php include('include/sidebar.php') ?>
    <main class="main-content">
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
    </main>
    <script>
        setTimeout(() => {
            const alert = document.querySelector('.status-success, .status-failure');
            if (alert) alert.style.display = 'none';
        }, 5000);
    </script>
</body>
</html>