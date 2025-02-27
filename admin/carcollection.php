<?php
require 'include/db_connection.php';


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


$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$query = "SELECT registration_no, model_name, description, availability_status, photo, price_per_day FROM vehicles";
$params = array();
$types = "";


$where_clauses = array();

if (!empty($search_query)) {
    $where_clauses[] = "(model_name LIKE ? OR registration_no LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($status_filter)) {
    $where_clauses[] = "availability_status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
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
        .search-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .search-container form {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            max-width: 800px;
            gap: 10px;
            justify-content: center;
        }
        .search-input {
            flex-grow: 1;
            padding: 10px;
            font-size: 16px;
            border: 2px solid #007BFF;
            border-radius: 5px;
            min-width: 250px;
        }
        .status-select {
            padding: 10px;
            font-size: 16px;
            border: 2px solid #007BFF;
            border-radius: 5px;
            cursor: pointer;
            background-color: white;
            min-width: 150px;
        }
        .search-button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: 2px solid #007BFF;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            min-width: 100px;
        }
        .search-button:hover {
            background-color: #0056b3;
        }
        .reset-button {
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            border: 2px solid #6c757d;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .reset-button:hover {
            background-color: #5a6268;
        }
        .no-results {
            text-align: center;
            color: #666;
            margin: 20px 0;
        }
        
    </style>
</head>
<body>
<header style="display: flex; justify-content: space-between; align-items: center;">
    <h1>Car Collection</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="add_vehicles.php">Add Vehicle</a>
    </nav>
</header>

<div class="search-container">
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by model name or registration number" 
               class="search-input" value="<?php echo htmlspecialchars($search_query); ?>">
        
        <select name="status" class="status-select">
            <option value="">All Availability</option>
            <option value="Available" <?php echo $status_filter === 'Available' ? 'selected' : ''; ?>>Available</option>
            <option value="Unavailable" <?php echo $status_filter === 'Unavailable' ? 'selected' : ''; ?>>Unavailable</option>
        </select>
        
        <button type="submit" class="search-button">
            <i class="fas fa-search"></i> Search
        </button>
        
        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="reset-button">
            <i class="fas fa-sync-alt"></i> Reset
        </a>
    </form>
</div>

<h2>
    <?php 
    $title = "Vehicles";
    
    if (!empty($search_query) && !empty($status_filter)) {
        $title = "Search Results for: \"" . htmlspecialchars($search_query) . "\" with status \"" . htmlspecialchars($status_filter) . "\"";
    } else if (!empty($search_query)) {
        $title = "Search Results for: \"" . htmlspecialchars($search_query) . "\"";
    } else if (!empty($status_filter)) {
        $title = htmlspecialchars($status_filter) . " Vehicles";
    }
    
    echo $title;
    ?>
</h2>

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
        <p class="no-results">
            <?php 
            if (!empty($search_query) || !empty($status_filter)) {
                echo "No vehicles found matching your search criteria.";
            } else {
                echo "No vehicles found.";
            }
            ?>
        </p>
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