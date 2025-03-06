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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .main-content {
    position: relative;
    z-index: 1;
}
.navbar { background: #1e293b; 
    padding: 1rem 2rem;
     box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      display: flex; 
      justify-content: space-between; 
      align-items: center;
       position: fixed;
        width: 100%;
         top: 0;
          z-index: 1000; } .navbar-brand { color: white;
             font-size: 1.5rem;
              font-weight: 700; 
              text-decoration: none; 
              text-transform: uppercase; 
              letter-spacing: 0.5px; }
.container {
    position: relative;
    z-index: 2;
    
}

        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
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