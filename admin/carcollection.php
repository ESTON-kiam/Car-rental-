<?php
require 'include/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_discount'])) {
    $registration_no = $_POST['registration_no'];
    $discount_percentage = $_POST['discount_percentage'];
    
    // Validate discount percentage
    if ($discount_percentage >= 0 && $discount_percentage <= 100) {
        // Retrieve pricing information
        $stmt = $conn->prepare("SELECT 
            original_price_per_day, 
            original_ac_price_per_day, 
            original_non_ac_price_per_day, 
            original_km_price,
            discount_percentage
            FROM vehicles 
            WHERE registration_no = ?");
        $stmt->bind_param("s", $registration_no);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $vehicle = $result->fetch_assoc();
            $stmt->close();
            
            // Ensure original prices exist
            $original_price_per_day = $vehicle['original_price_per_day'] ?? 
                $vehicle['original_price_per_day'] = 
                $conn->query("SELECT price_per_day FROM vehicles WHERE registration_no = '$registration_no'")->fetch_assoc()['price_per_day'];
            
            // Calculate prices based on true original price
            if ($discount_percentage > 0) {
                $price_per_day = $original_price_per_day - 
                    ($original_price_per_day * $discount_percentage / 100);
                
                $ac_price_per_day = $vehicle['original_ac_price_per_day'] - 
                    ($vehicle['original_ac_price_per_day'] * $discount_percentage / 100);
                
                $non_ac_price_per_day = $vehicle['original_non_ac_price_per_day'] - 
                    ($vehicle['original_non_ac_price_per_day'] * $discount_percentage / 100);
                
                $km_price = $vehicle['original_km_price'] - 
                    ($vehicle['original_km_price'] * $discount_percentage / 100);
            } else {
                // Reset to original prices if discount is 0
                $price_per_day = $original_price_per_day;
                $ac_price_per_day = $vehicle['original_ac_price_per_day'];
                $non_ac_price_per_day = $vehicle['original_non_ac_price_per_day'];
                $km_price = $vehicle['original_km_price'];
            }
            
            // Update vehicle with new prices and discount
            $update_stmt = $conn->prepare("UPDATE vehicles SET 
                discount_percentage = ?,
                price_per_day = ?,
                ac_price_per_day = ?,
                non_ac_price_per_day = ?,
                km_price = ?
                WHERE registration_no = ?");
            
            $update_stmt->bind_param("ddddds", 
                $discount_percentage,
                $price_per_day,
                $ac_price_per_day,
                $non_ac_price_per_day,
                $km_price,
                $registration_no
            );
            
            if ($update_stmt->execute()) {
                echo "<p style='color: green;'>Discount updated successfully!</p>";
            } else {
                echo "<p style='color: red;'>Error updating discount: " . $conn->error . "</p>";
            }
            
            $update_stmt->close();
        } else {
            echo "<p style='color: red;'>Vehicle not found.</p>";
        }
    } else {
        echo "<p style='color: red;'>Invalid discount percentage. Must be between 0 and 100.</p>";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_availability'])) {
    $registration_no = $_POST['registration_no'];
    $current_status = $_POST['current_status'];
    
    $new_status = ($current_status === 'Available') ? 'Unavailable' : 'Available';
    
    
    if ($new_status === 'Unavailable' && isset($_POST['status_reason'])) {
        $status_reason = $_POST['status_reason'];
        
        $stmt = $conn->prepare("UPDATE vehicles SET availability_status = ?, status_reason = ? WHERE registration_no = ?");
        $stmt->bind_param("sss", $new_status, $status_reason, $registration_no);
    } else {
       
        $stmt = $conn->prepare("UPDATE vehicles SET availability_status = ?, status_reason = NULL WHERE registration_no = ?");
        $stmt->bind_param("ss", $new_status, $registration_no);
    }

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Status updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error updating status: " . $conn->error . "</p>";
    }

    $stmt->close();
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_all_prices'])) {
    $registration_no = $_POST['registration_no'];
    $base_price = $_POST['base_price'];
    
   
    $stmt = $conn->prepare("UPDATE vehicles SET 
        price_per_day = ?,
        ac_price_per_day = ?,
        non_ac_price_per_day = ?,
        km_price = ?,
        original_price_per_day = ?,
        original_ac_price_per_day = ?,
        original_non_ac_price_per_day = ?,
        original_km_price = ?,
        discount_percentage = 0
        WHERE registration_no = ?");
    
    $stmt->bind_param("dddddddds", 
        $base_price, 
        $base_price,
        $base_price,
        $base_price,
        $base_price,
        $base_price,
        $base_price,
        $base_price,
        $registration_no
    );
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>All prices updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error updating prices: " . $conn->error . "</p>";
    }
    
    $stmt->close();
}


$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$query = "SELECT registration_no, model_name, description, availability_status, status_reason, photo, 
                 price_per_day, ac_price_per_day, non_ac_price_per_day, km_price, 
                 original_price_per_day, original_ac_price_per_day, original_non_ac_price_per_day, original_km_price,
                 discount_percentage, created_at 
          FROM vehicles";
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
    <style>
      
        .status-reason {
            margin-top: 5px;
            font-style: italic;
            color: #d9534f;
        }
        .status-form, .discount-form {
            margin-top: 10px;
            display: none;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .status-form select, .discount-form input {
            width: 100%;
            padding: 5px;
            margin-bottom: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #d9534f;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            z-index: 10;
        }
        .original-price {
            text-decoration: line-through;
            color: #888;
            font-size: 0.9em;
            margin-right: 5px;
        }
        .discounted-price {
            color:   #d9534f;
            font-weight: bold;
        }
        .vehicle {
            position: relative;
        }
        .price-section {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
        }
        .button-section {
            margin-top: 8px;
        }
        .prices-container {
            margin-top: 15px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            align-items: center;
        }
        .price-label {
            font-weight: bold;
            width: 140px;
        }
        .tab-container {
            display: flex;
            margin-bottom: 10px;
        }
        .price-tab {
            padding: 5px 10px;
            background-color: #f1f1f1;
            cursor: pointer;
            border: 1px solid #ddd;
            border-radius: 4px 4px 0 0;
            margin-right: 2px;
        }
        .price-tab.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .price-content {
            display: none;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 0 4px 4px 4px;
        }
        .price-content.active {
            display: block;
        }
    </style>
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
            <?php
                
                $discount_percentage = $vehicle['discount_percentage'] ?? 0;
                $has_discount = $discount_percentage > 0;
                
                
                $original_price = $vehicle['price_per_day'] ?? 0;
                $discounted_price = $original_price - ($original_price * $discount_percentage / 100);
                
                
                $original_ac_price = $vehicle['ac_price_per_day'] ?? 0;
                $discounted_ac_price = $original_ac_price - ($original_ac_price * $discount_percentage / 100);
                
              
                $original_non_ac_price = $vehicle['non_ac_price_per_day'] ?? 0;
                $discounted_non_ac_price = $original_non_ac_price - ($original_non_ac_price * $discount_percentage / 100);
                
               
                $original_km_price = $vehicle['km_price'] ?? 0;
                $discounted_km_price = $original_km_price - ($original_km_price * $discount_percentage / 100);
            ?>
            <div class="vehicle">
                <?php if ($has_discount): ?>
                    <div class="discount-badge">
                        -<?php echo number_format($discount_percentage, 0); ?>% OFF
                    </div>
                <?php endif; ?>
                
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
                
                <div class="prices-container">
                    <div class="tab-container">
                        <div class="price-tab active" onclick="showPriceTab('standard-price-<?php echo htmlspecialchars($vehicle['registration_no']); ?>', this)">Standard</div>
                        <div class="price-tab" onclick="showPriceTab('ac-price-<?php echo htmlspecialchars($vehicle['registration_no']); ?>', this)">AC</div>
                        <div class="price-tab" onclick="showPriceTab('non-ac-price-<?php echo htmlspecialchars($vehicle['registration_no']); ?>', this)">Non-AC</div>
                        <div class="price-tab" onclick="showPriceTab('km-price-<?php echo htmlspecialchars($vehicle['registration_no']); ?>', this)">Per KM</div>
                    </div>
                    
                   
                    <div id="standard-price-<?php echo htmlspecialchars($vehicle['registration_no']); ?>" class="price-content active">
                        <div class="price-row">
                            <span class="price-label">Standard Price:</span>
                            <span class="price-section">
                                <?php if ($has_discount): ?>
                                    <span class="original-price">KSH <?php echo number_format($original_price, 2); ?></span>
                                    <span class="discounted-price">KSH <?php echo number_format($discounted_price, 2); ?></span>
                                <?php else: ?>
                                    <span class="value">KSH <?php echo number_format($original_price, 2); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    
                    
                    <div id="ac-price-<?php echo htmlspecialchars($vehicle['registration_no']); ?>" class="price-content">
                        <div class="price-row">
                            <span class="price-label">AC Price per Day:</span>
                            <span class="price-section">
                                <?php if ($has_discount && $original_ac_price > 0): ?>
                                    <span class="original-price">KSH <?php echo number_format($original_ac_price, 2); ?></span>
                                    <span class="discounted-price">KSH <?php echo number_format($discounted_ac_price, 2); ?></span>
                                <?php elseif ($original_ac_price > 0): ?>
                                    <span class="value">KSH <?php echo number_format($original_ac_price, 2); ?></span>
                                <?php else: ?>
                                    <span class="value">Not available</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    
                   
                    <div id="non-ac-price-<?php echo htmlspecialchars($vehicle['registration_no']); ?>" class="price-content">
                        <div class="price-row">
                            <span class="price-label">Non-AC Price per Day:</span>
                            <span class="price-section">
                                <?php if ($has_discount && $original_non_ac_price > 0): ?>
                                    <span class="original-price">KSH <?php echo number_format($original_non_ac_price, 2); ?></span>
                                    <span class="discounted-price">KSH <?php echo number_format($discounted_non_ac_price, 2); ?></span>
                                <?php elseif ($original_non_ac_price > 0): ?>
                                    <span class="value">KSH <?php echo number_format($original_non_ac_price, 2); ?></span>
                                <?php else: ?>
                                    <span class="value">Not available</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    
                   
                    <div id="km-price-<?php echo htmlspecialchars($vehicle['registration_no']); ?>" class="price-content">
                        <div class="price-row">
                            <span class="price-label">Price per KM:</span>
                            <span class="price-section">
                                <?php if ($has_discount && $original_km_price > 0): ?>
                                    <span class="original-price">KSH <?php echo number_format($original_km_price, 2); ?></span>
                                    <span class="discounted-price">KSH <?php echo number_format($discounted_km_price, 2); ?></span>
                                <?php elseif ($original_km_price > 0): ?>
                                    <span class="value">KSH <?php echo number_format($original_km_price, 2); ?></span>
                                <?php else: ?>
                                    <span class="value">Not available</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="status-section">
                    <span class="status-indicator <?php echo ($vehicle['availability_status'] === 'Available') ? 'status-available' : 'status-unavailable'; ?>">
                        <?php echo htmlspecialchars($vehicle['availability_status'] ?? 'N/A'); ?>
                    </span>
                    
                    <?php if ($vehicle['availability_status'] === 'Unavailable' && !empty($vehicle['status_reason'])): ?>
                        <div class="status-reason">
                            Reason: <?php echo htmlspecialchars($vehicle['status_reason']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="button-section">
                        <button type="button" class="status-toggle" onclick="toggleForm('status-form-<?php echo htmlspecialchars($vehicle['registration_no']); ?>')">
                            <i class="fas <?php echo ($vehicle['availability_status'] === 'Available') ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                        </button>
                        
                        <button type="button" class="edit-button" onclick="toggleForm('discount-form-<?php echo htmlspecialchars($vehicle['registration_no']); ?>')">
                            <i class="fas fa-tag"></i> Set Discount
                        </button>
                    </div>
                    
                  
                    <form id="status-form-<?php echo htmlspecialchars($vehicle['registration_no']); ?>" method="POST" action="" class="status-form">
                        <input type="hidden" name="registration_no" 
                               value="<?php echo htmlspecialchars($vehicle['registration_no']); ?>">
                        <input type="hidden" name="current_status" 
                               value="<?php echo htmlspecialchars($vehicle['availability_status']); ?>">
                               
                        <?php if ($vehicle['availability_status'] === 'Available'): ?>
                            <select name="status_reason" required>
                                <option value="">-- Select Reason --</option>
                                <option value="Booked">Booked</option>
                                <option value="Under Service">Under Service</option>
                            </select>
                        <?php endif; ?>
                        
                        <button type="submit" name="update_availability" class="edit-button">
                            <i class="fas fa-check"></i> Update Status
                        </button>
                    </form>
                    
                    
                    <form id="discount-form-<?php echo htmlspecialchars($vehicle['registration_no']); ?>" method="POST" action="" class="discount-form">
                        <input type="hidden" name="registration_no" 
                               value="<?php echo htmlspecialchars($vehicle['registration_no']); ?>">
                        
                        <label for="discount-<?php echo htmlspecialchars($vehicle['registration_no']); ?>">
                            Discount Percentage (0-100%):
                        </label>
                        <input type="number" id="discount-<?php echo htmlspecialchars($vehicle['registration_no']); ?>" 
                               name="discount_percentage" min="0" max="100" step="0.1"
                               value="<?php echo htmlspecialchars($vehicle['discount_percentage'] ?? 0); ?>">
                        
                        <p><small>Discount applies to all pricing options (Standard, AC, Non-AC, and Per KM)</small></p>
                        
                        <button type="submit" name="update_discount" class="edit-button">
                            <i class="fas fa-save"></i> Save Discount
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

function toggleForm(formId) {
    const form = document.getElementById(formId);
    if (form.style.display === 'block') {
        form.style.display = 'none';
    } else {
        form.style.display = 'block';
    }
}

function showPriceTab(tabId, clickedTab) {
    
    const allTabs = clickedTab.parentElement.getElementsByClassName('price-tab');
    for (let i = 0; i < allTabs.length; i++) {
        allTabs[i].classList.remove('active');
    }
    
   
    clickedTab.classList.add('active');
    
   
    const tabContainerParent = clickedTab.parentElement.parentElement;
    const allContent = tabContainerParent.getElementsByClassName('price-content');
    for (let i = 0; i < allContent.length; i++) {
        allContent[i].classList.remove('active');
    }
    

    document.getElementById(tabId).classList.add('active');
}
</script>

<?php $conn->close(); ?>
</body>
</html>