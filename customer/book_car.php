<?php
require_once 'include/db_connection.php';

$search_query = $_GET['search'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$sql = "SELECT 
            vehicle_id, 
            registration_no, 
            model_name, 
            description, 
            availability_status, 
            photo, 
            price_per_day,
            ac_price_per_day,
            non_ac_price_per_day,
            km_price,
            original_price_per_day,
            original_ac_price_per_day,
            original_non_ac_price_per_day,
            original_km_price,
            discount_percentage 
        FROM vehicles 
        WHERE availability_status = 'Available'";

$conditions = [];
$param_types = '';
$param_values = [];

if (!empty($search_query)) {
    $conditions[] = "(model_name LIKE ? OR description LIKE ?)";
    $param_types .= 'ss';
    $param_values[] = "%$search_query%";
    $param_values[] = "%$search_query%";
}

if (!empty($min_price)) {
    $conditions[] = "(
        (original_price_per_day * (1 - discount_percentage/100) >= ?) OR 
        (price_per_day >= ?)
    )";
    $param_types .= 'dd';
    $param_values[] = $min_price;
    $param_values[] = $min_price;
}

if (!empty($max_price)) {
    $conditions[] = "(
        (original_price_per_day * (1 - discount_percentage/100) <= ?) OR 
        (price_per_day <= ?)
    )";
    $param_types .= 'dd';
    $param_values[] = $max_price;
    $param_values[] = $max_price;
}

if (!empty($conditions)) {
    $sql .= " AND " . implode(' AND ', $conditions);
}

$stmt = $conn->prepare($sql);

if (!empty($param_values)) {
    $stmt->bind_param($param_types, ...$param_values);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Car Collection</title>
    <link href="assets/img/p.png" rel="icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/book.css">
    <style>
        :root {
            --primary-color: #007bff;
            --discount-color: #d9534f;
            --text-muted: #888;
        }
        
        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--discount-color);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            z-index: 10;
        }
        
        .original-price {
            text-decoration: line-through;
            color: var(--text-muted);
            font-size: 0.9em;
            margin-right: 10px;
        }
        
        .discounted-price {
            color: var(--discount-color);
            font-weight: bold;
        }
        
        .vehicle-card {
            position: relative;
            transition: transform 0.3s ease;
        }
        
        .vehicle-card:hover {
            transform: scale(1.03);
        }
        
        .price-section {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .tab-container {
            display: flex;
            margin: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .price-tab {
            padding: 8px 15px;
            background-color: #f4f4f4;
            cursor: pointer;
            border-radius: 4px 4px 0 0;
            margin-right: 5px;
            transition: all 0.3s ease;
        }
        
        .price-tab:hover {
            background-color: #e0e0e0;
        }
        
        .price-tab.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .price-content {
            display: none;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 0 4px 4px 4px;
            margin-bottom: 15px;
        }
        
        .price-content.active {
            display: block;
        }
        
        .rent-button {
            width: 100%;
            padding: 10px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        
        .rent-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-car"></i>Online Car Collection</h1>
        <div class="header-links">
            <a href="dashboard.php">Dashboard</a>
        </div>
    </div>

    <div class="search-container">
        <form method="GET" action="" class="search-bar">
            <input type="text" name="search" placeholder="Search by model or description" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
        
        <form method="GET" action="" class="price-range">
            <input type="number" name="min_price" placeholder="Min Price" value="<?php echo htmlspecialchars($min_price); ?>">
            <input type="number" name="max_price" placeholder="Max Price" value="<?php echo htmlspecialchars($max_price); ?>">
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>

    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): 
                $discount_percentage = $row['discount_percentage'] ?? 0;
                $has_discount = $discount_percentage > 0;
                
                // Original Prices
                $original_price = $row['original_price_per_day'] ?? 0;
                $original_ac_price = $row['original_ac_price_per_day'] ?? 0;
                $original_non_ac_price = $row['original_non_ac_price_per_day'] ?? 0;
                $original_km_price = $row['original_km_price'] ?? 0;

                // Discounted Prices
                $discounted_price = $original_price * (1 - $discount_percentage / 100);
                $discounted_ac_price = $original_ac_price * (1 - $discount_percentage / 100);
                $discounted_non_ac_price = $original_non_ac_price * (1 - $discount_percentage / 100);
                $discounted_km_price = $original_km_price * (1 - $discount_percentage / 100);

                // Filtering logic
                $final_price = $has_discount ? $discounted_price : $original_price;
                if ((!empty($min_price) && $final_price < $min_price) || 
                    (!empty($max_price) && $final_price > $max_price)) {
                    continue; 
                }
            ?>
                <div class="vehicle-card">
                    <?php if ($has_discount): ?>
                        <div class="discount-badge">
                            -<?php echo number_format($discount_percentage, 0); ?>% OFF
                        </div>
                    <?php endif; ?>
                    
                    <img src="/admin/<?php echo htmlspecialchars($row['photo']); ?>" alt="<?php echo htmlspecialchars($row['model_name']); ?>">
                    <div class="vehicle-info">
                        <h3><?php echo htmlspecialchars($row['model_name']); ?></h3>
                        <p><strong>Registration No:</strong> <?php echo htmlspecialchars($row['registration_no']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                        
                        <div class="tab-container">
                            <div class="price-tab active" onclick="showPriceTab('standard-price-<?php echo $row['vehicle_id']; ?>', this)">Standard</div>
                            <?php if ($original_ac_price > 0): ?>
                                <div class="price-tab" onclick="showPriceTab('ac-price-<?php echo $row['vehicle_id']; ?>', this)">AC</div>
                            <?php endif; ?>
                            <?php if ($original_non_ac_price > 0): ?>
                                <div class="price-tab" onclick="showPriceTab('non-ac-price-<?php echo $row['vehicle_id']; ?>', this)">Non-AC</div>
                            <?php endif; ?>
                            <?php if ($original_km_price > 0): ?>
                                <div class="price-tab" onclick="showPriceTab('km-price-<?php echo $row['vehicle_id']; ?>', this)">Per KM</div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="standard-price-<?php echo $row['vehicle_id']; ?>" class="price-content active">
                            <div class="price-section">
                                <?php if ($has_discount): ?>
                                    <span class="original-price">KSH <?php echo number_format($original_price, 2); ?></span>
                                    <span class="discounted-price">KSH <?php echo number_format($discounted_price, 2); ?></span>
                                <?php else: ?>
                                    <span class="vehicle-price">Price per day: KSH <?php echo number_format($original_price, 2); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($original_ac_price > 0): ?>
                            <div id="ac-price-<?php echo $row['vehicle_id']; ?>" class="price-content">
                                <div class="price-section">
                                    <?php if ($has_discount): ?>
                                        <span class="original-price">KSH <?php echo number_format($original_ac_price, 2); ?></span>
                                        <span class="discounted-price">KSH <?php echo number_format($discounted_ac_price, 2); ?></span>
                                    <?php else: ?>
                                        <span class="vehicle-price">AC Price per day: KSH <?php echo number_format($original_ac_price, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($original_non_ac_price > 0): ?>
                            <div id="non-ac-price-<?php echo $row['vehicle_id']; ?>" class="price-content">
                                <div class="price-section">
                                    <?php if ($has_discount): ?>
                                        <span class="original-price">KSH <?php echo number_format($original_non_ac_price, 2); ?></span>
                                        <span class="discounted-price">KSH <?php echo number_format($discounted_non_ac_price, 2); ?></span>
                                    <?php else: ?>
                                        <span class="vehicle-price">Non-AC Price per day: KSH <?php echo number_format($original_non_ac_price, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($original_km_price > 0): ?>
                            <div id="km-price-<?php echo $row['vehicle_id']; ?>" class="price-content">
                                <div class="price-section">
                                    <?php if ($has_discount): ?>
                                        <span class="original-price">KSH <?php echo number_format($original_km_price, 2); ?></span>
                                        <span class="discounted-price">KSH <?php echo number_format($discounted_km_price, 2); ?></span>
                                    <?php else: ?>
                                        <span class="vehicle-price">Price per KM: KSH <?php echo number_format($original_km_price, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <button class="rent-button" onclick="location.href='booking.php?id=<?php echo $row['vehicle_id']; ?>'">
                            <i class="fas fa-car"></i> Rent Now
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No vehicles available matching your search criteria.</p>
        <?php endif; ?>
    </div>
    
    <script>
    function showPriceTab(tabId, clickedTab) {
        const allTabs = clickedTab.parentElement.getElementsByClassName('price-tab');
        for (let i = 0; i < allTabs.length; i++) {
            allTabs[i].classList.remove('active');
        }
        
        clickedTab.classList.add('active');
        
        const vehicleInfo = clickedTab.parentElement.parentElement;
        const allContent = vehicleInfo.getElementsByClassName('price-content');
        for (let i = 0; i < allContent.length; i++) {
            allContent[i].classList.remove('active');
        }
        
        document.getElementById(tabId).classList.add('active');
    }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>