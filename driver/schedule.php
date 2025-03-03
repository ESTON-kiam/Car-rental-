<?php
require_once 'include/db_connection.php';

$driver_id = $_SESSION['driver_id'];


date_default_timezone_set('Africa/Nairobi');
$current_datetime = date('Y-m-d H:i:s');


$query = "
    SELECT b.booking_id, b.start_date, b.end_date, b.pick_up_time, b.pick_up_location,
           b.car_type, b.model_name, b.registration_no,
           CONCAT(b.start_date, ' ', b.pick_up_time) as pickup_datetime
    FROM bookings b
    INNER JOIN driver_assignments da ON b.booking_id = da.booking_id
    WHERE da.driver_id = ?
      AND b.driver_option = 'yes'
      AND CONCAT(b.start_date, ' ', b.pick_up_time) > ?
      AND b.end_date > ? 
      AND b.booking_status IN ('pending','active', 'completed')
    ORDER BY b.start_date ASC, b.pick_up_time ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $driver_id, $current_datetime, $current_datetime); 
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Driver Schedule</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/schedule.css">
</head>
<body>
<div class="header">
    <a href="http://localhost:8000/driver/dashboard.php" class="dashboard-link" style="color: white; text-decoration: none;">← Back to Dashboard</a>
</div>
<h1>Upcoming Schedule</h1>
<div class="time-info">
    <p>Current Time: <span class="current-time"><?php echo date('F j, Y g:i A'); ?></span></p>
    <p>Timezone: <?php echo date_default_timezone_get(); ?></p>
</div>

<?php if ($result->num_rows > 0): ?>
<table>
    <thead>
        <tr>
            <th>Pickup Date</th>
            <th>End Date</th>
            <th>Pickup Time</th>
            <th>Location</th>
            <th>Vehicle Details</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): 
            $pickup_datetime = strtotime($row['pickup_datetime']);
            $current = strtotime($current_datetime);
            $time_difference = $pickup_datetime - $current;
            $status_class = '';
            $status_text = '';

            if ($time_difference < 3600) {
                
                $minutes = floor($time_difference / 60);
                $status_text = "Pickup in $minutes minutes";
                $status_class = 'status-soon';
            } else if ($time_difference < 86400) {
              
                $hours = floor($time_difference / 3600);
                $status_text = "Pickup in $hours hours";
                $status_class = 'status-upcoming';
            } else {
                
                $days = floor($time_difference / 86400);
                $status_text = "Pickup in $days days";
                $status_class = 'status-upcoming';
            } ?>
            <tr>
                <td><?php echo htmlspecialchars(date('F j, Y', strtotime($row['start_date']))); ?></td>
                <td><?php echo htmlspecialchars(date('F j, Y', strtotime($row['end_date']))); ?></td>
                <td><?php echo htmlspecialchars(date('g:i A', strtotime($row['pick_up_time']))); ?></td>
                <td><?php echo htmlspecialchars($row['pick_up_location']); ?></td>
                <td>
                    <div class="vehicle-info">
                        <strong><?php echo htmlspecialchars($row['model_name']); ?></strong><br> 
                        Reg: <?php echo htmlspecialchars($row['registration_no']); ?> 
                    </div>
                </td>
                <td><span class="status-indicator <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php else: ?>
<div class="no-schedules">
    <p>No upcoming schedules found.</p>
</div>

<?php endif;

$stmt->close();
$conn->close();
?>

<script>

setInterval(function() {
    const timeElement = document.querySelector('.current-time');
    const now = new Date();
    timeElement.textContent = now.toLocaleString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12:true
    });
    
    
    if (now.getMinutes() % 5 === 0) {
        location.reload();
    }
},60000);
</script>

</body>
</html>