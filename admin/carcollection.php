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
   <link href="assets/css/carcollection.css" rel="stylesheet">
</head>
<body>
<header style="display: flex; justify-content: space-between; align-items: center;">
    <h1><i class="fas fa-car"></i>Car Collection</h1>
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