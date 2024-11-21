<?php
session_name('admin_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => false, 
    'httponly' => true,
    'samesite' => 'Strict'
]);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_vehicle'])) {
    $registration_no = $_POST['registration_no'];
    
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE registration_no = ?");
    $stmt->bind_param("s", $registration_no);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Vehicle deleted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error deleting vehicle: " . $conn->error . "</p>";
    }

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_availability'])) {
    $registration_no = $_POST['registration_no'];
    $current_status = $_POST['current_status'];
    
    $new_status = ($current_status === 'Available') ? 'Unavailable' : 'Available';

    $stmt = $conn->prepare("UPDATE vehicles SET availability_status = ? WHERE registration_no = ?");
    $stmt->bind_param("ss", $new_status, $registration_no);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Status updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error updating status: " . $conn->error . "</p>";
    }

    $stmt->close();
}

$query = "SELECT registration_no, model_name, description, availability_status, photo, price_per_day FROM vehicles";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Collection</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .vehicle-list {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .vehicle {
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
        }
        .vehicle:hover {
            transform: scale(1.02);
        }
        .vehicle img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .price {
            font-size: 1.1em;
            color: #007BFF;
            font-weight: bold;
            margin: 10px 0;
            padding: 5px;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-align: center;
        }
        .label {
            color: #666;
            font-weight: normal;
            display: inline;
            margin-right: 5px;
        }
        .value {
            color: #333;
            font-weight: bold;
            display: inline;
        }
        .price .label {
            color: #666;
        }
        .price .value {
            color: #007BFF;
        }
        header {
            background-color: #007BFF;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
        nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #FFD700;
        }
        .status-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }
        .status-indicator {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-available {
            background-color: #d4edda;
            color: #155724;
        }
        .status-unavailable {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2em;
            color: #007BFF;
            padding: 5px;
        }
        .status-toggle:hover {
            color: #0056b3;
        }
        .button-container {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .edit-button {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
            width: 50%;
        }
        .edit-button:hover {
            background-color: #218838;
        }
        .delete-button {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
            width: 100%;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
        
        @media (max-width: 1200px) {
            .vehicle-list {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 900px) {
            .vehicle-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 600px) {
            .vehicle-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Car Collection</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="add_vehicles.php">Add Vehicle</a>
    </nav>
</header>

<h2>Available Vehicles</h2>
<div class="vehicle-list">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($vehicle = $result->fetch_assoc()): ?>
            <div class="vehicle">
                <img src="<?php echo htmlspecialchars($vehicle['photo'] ?? ''); ?>" 
                     alt="<?php echo htmlspecialchars($vehicle['model_name'] ?? 'Vehicle Image'); ?>">
                
                <h3><?php echo htmlspecialchars($vehicle['model_name'] ?? 'Unknown Model'); ?></h3>
                <p>
                    <span class="label">Registration No:</span>
                    <span class="value"><?php echo htmlspecialchars($vehicle['registration_no'] ?? 'N/A'); ?></span>
                </p>
                <p>
                    <span class="label">Description:</span>
                    <span class="value"><?php echo htmlspecialchars($vehicle['description'] ?? 'No description available'); ?></span>
                </p>
                <p class="price">
                    <span class="label">Price per Day:</span>
                    <span class="value">KSH <?php echo number_format($vehicle['price_per_day'] ?? 0, 2); ?></span>
                </p>
                
                <div class="status-section">
                    <span class="status-indicator <?php echo ($vehicle['availability_status'] === 'Available') ? 'status-available' : 'status-unavailable'; ?>">
                        <?php echo htmlspecialchars($vehicle['availability_status'] ?? 'N/A'); ?>
                    </span>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="registration_no" 
                               value="<?php echo htmlspecialchars($vehicle['registration_no']); ?>">
                        <input type="hidden" name="current_status" 
                               value="<?php echo htmlspecialchars($vehicle['availability_status']); ?>">
                        <button type="submit" name="update_availability" class="status-toggle">
                            <i class="fas <?php echo ($vehicle['availability_status'] === 'Available') ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                        </button>
                    </form>
                </div>

                <div class="button-container">
                    <a href="edit_vehicle.php?registration_no=<?php echo htmlspecialchars($vehicle['registration_no']); ?>" style="width: 50%;">
                        <button type="button" class="edit-button">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </a>
                    <form method="POST" action="" style="width: 50%;" onsubmit="return confirmDelete()">
                        <input type="hidden" name="registration_no" 
                               value="<?php echo htmlspecialchars($vehicle['registration_no']); ?>">
                        <button type="submit" name="delete_vehicle" class="delete-button">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No vehicles found.</p>
    <?php endif; ?>
</div>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this vehicle? This action cannot be undone.');
}
</script>

<?php $conn->close(); ?>
</body>
</html>