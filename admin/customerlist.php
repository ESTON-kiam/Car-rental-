<?php
require 'include/db_connection.php';

$query = "SELECT id, full_name, email,profile_picture, mobile, gender, dob, occupation, residence, created_at FROM customers";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List -Admin Panel</title>
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
        .delete-button, .edit-button { padding: 8px 12px; border-radius: 4px; cursor: pointer; }
        .delete-button { color: red; background-color: #f8d7da; border: 1px solid red; }
        .edit-button { color: green; background-color: #d4edda; border: 1px solid green; }
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



<h2>Customer List</h2>

<table>
    <thead>
        <tr>
        <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Gender</th>
            <th>Date of Birth</th>
            <th>Occupation</th>
            <th>Residence</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($customer = $result->fetch_assoc()): ?>
                <tr>
                
                    <td><?php echo htmlspecialchars($customer['id']); ?></td>
                    <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                    <td><?php echo htmlspecialchars($customer['mobile']); ?></td>
                    <td><?php echo htmlspecialchars($customer['gender']); ?></td>
                    <td><?php echo htmlspecialchars($customer['dob']); ?></td>
                    <td><?php echo htmlspecialchars($customer['occupation']); ?></td>
                    <td><?php echo htmlspecialchars($customer['residence']); ?></td>
                    <td>
                        <a href="editcustomerlist.php?id=<?php echo $customer['id']; ?>" class="edit-button">Edit</a>
                        <a href="delete_customer.php?id=<?php echo $customer['id']; ?>" class="delete-button">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9">No customers found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
    </main>
<?php 
$conn->close(); 
?>
</body>
</html>