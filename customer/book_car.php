<?php
session_name('customer_session');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '',
    'secure' => false, 
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location:http://localhost:8000/customer/"); 
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


$search_query = $_GET['search'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';


$sql = "SELECT vehicle_id, registration_no, model_name, description, availability_status, photo, price_per_day 
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
    $conditions[] = "price_per_day >= ?";
    $param_types .= 'd';
    $param_values[] = $min_price;
}

if (!empty($max_price)) {
    $conditions[] = "price_per_day <= ?";
    $param_types .= 'd';
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
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="vehicle-card">
                    <img src="/admin/<?php echo htmlspecialchars($row['photo']); ?>" alt="<?php echo htmlspecialchars($row['model_name']); ?>">
                    <div class="vehicle-info">
                        <h3><?php echo htmlspecialchars($row['model_name']); ?></h3>
                        <p><strong>Registration No:</strong> <?php echo htmlspecialchars($row['registration_no']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="vehicle-price">Price per day: KSH <?php echo number_format($row['price_per_day'], 2); ?></p>
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
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>