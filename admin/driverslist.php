<?php
require 'include/db_connection.php';

$query = "SELECT driver_id, name, email, contact_no, residence, driving_license_no, license_image FROM drivers";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver List - Admin Panel</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        header { background-color: #007BFF; color: white; padding: 10px 20px; }
        header h1 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007BFF; color: white; }
        tr:hover { background-color: #f1f1f1; }
        h2 { color: #333; text-align: center; }
        .delete-button { color: red; text-decoration: none; padding: 8px 12px; border: 1px solid red; border-radius: 4px; background-color: #f8d7da; cursor: pointer; }
        .driver-image { width: 50px; height: auto; } 
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
    </style>
</head>
<body>


<nav class="navbar">
    
    <a href="dashboard.php" class="navbar-brand">
     <i class="fas fa-car"></i><b>
             Online Car Rental Admin Panel</b></a>       
 </nav>
    <?php include'include/sidebar.php' ?>
    <main class="main-content">

<h2>Driver List</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Contact No</th>
            <th>Residence</th>
            <th>Driving License No</th>
            <th>License Image</th> 
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($driver = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($driver['driver_id']); ?></td>
                    <td><?php echo htmlspecialchars($driver['name']); ?></td>
                    <td><?php echo htmlspecialchars($driver['email']); ?></td>
                    <td><?php echo htmlspecialchars($driver['contact_no']); ?></td>
                    <td><?php echo htmlspecialchars($driver['residence']); ?></td>
                    <td><?php echo htmlspecialchars($driver['driving_license_no']); ?></td>
                    <td>
                        <?php if (!empty($driver['license_image'])): ?>
                            <img src="/driver/<?php echo htmlspecialchars($driver['license_image']); ?>" alt="License Image" class="driver-image">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td><a href="edit_driver.php?id=<?php echo $driver['driver_id']; ?>" class="delete-button">Edit</a></td>
                    <td><a href="delete_driver.php?id=<?php echo $driver['driver_id']; ?>" class="delete-button">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">No drivers found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
    </main>
<?php $conn->close(); ?>
</body>
</html>