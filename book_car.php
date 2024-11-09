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
    header("Location: customer_login.php"); 
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


$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}


$sql = "SELECT vehicle_id, registration_no, model_name, description, availability_status, photo, price_per_day 
        FROM vehicles 
        WHERE availability_status = 'Available' 
        AND (model_name LIKE ? OR description LIKE ? OR price_per_day LIKE ?)";
$stmt = $conn->prepare($sql);
$like_query = "%" . $search_query . "%";
$stmt->bind_param("sss", $like_query, $like_query, $like_query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer side Car Collection</title>
    <link href="assets/img/p.png" rel="icon">
  <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --background-color: #f8f9fa;
            --text-color: #343a40;
            --card-background: #ffffff;
            --header-height: 60px;
            --border-radius: 8px;
            --box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            --price-color: #28a745; 
            --details-color: #17a2b8; 
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding-top: var(--header-height);
        }

        .header {
            width: 100%;
            height: var(--header-height);
            background-color: var(--primary-color);
            color: white;
            position: fixed;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: var(--box-shadow);
            z-index: 10;
        }

        .header h1 {
            font-size: 1.5rem;
            margin: 0;
        }

        .header a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: var(--border-radius);
            transition: background-color 0.3s;
        }

        .header a:hover {
            background-color: #0056b3;
        }

        .search-bar {
            display: flex;
            margin-top: 70px; 
            justify-content: center;
            align-items: center;
        }

        .search-bar input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            margin-right: 10px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }

        .vehicle-card {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin: 10px;
            width: calc(25% - 20px);
            overflow: hidden;
            transition: transform 0.3s;
        }

        .vehicle-card:hover {
            transform: translateY(-5px);
        }

        .vehicle-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .vehicle-info {
            padding: 15px;
            text-align: center;
        }

        .vehicle-info h3 {
            margin: 10px 0;
            font-size: 1.2rem;
        }

        .vehicle-info p {
            margin: 5px 0;
            color: var(--details-color);
        }

        .vehicle-price {
            color: var(--price-color);
            font-weight: bold;
            font-size: 1.5rem;
            margin: 10px 0;
        }

        .rent-button {
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s, transform 0.3s;
        }

        .rent-button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .vehicle-card {
                width: calc(50% - 20px); 
            }
        }

        @media (max-width: 480px) {
            .vehicle-card {
                width: 100%; 
            }
        }

        h1 {
            text-align: center;
            margin: 20px 0;
            font-size: 2rem;
            color: var(--primary-color);
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Car Collection</h1>
        <div>
            <a href="customer_dashboard.php">Dashboard</a>
            <a href="customerviewprofile.php">View Profile</a>
        </div>
    </div>

    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by model, description, or price..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="rent-button">Search</button>
        </form>
    </div>

    <h1>Available Vehicles</h1>

    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="vehicle-card">
                    <img src="<?php echo htmlspecialchars($row['photo']); ?>" alt="<?php echo htmlspecialchars($row['model_name']); ?>">
                    <div class="vehicle-info">
                        <h3><?php echo htmlspecialchars($row['model_name']); ?></h3>
                        <p><strong style="color: var(--details-color);">Registration No:</strong> <?php echo htmlspecialchars($row['registration_no']); ?></p>
                        <p><strong style="color: var(--details-color);">Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="vehicle-price">Price per day: KSH <?php echo number_format($row['price_per_day'], 2); ?></p>
                        <button class="rent-button" onclick="location.href='customerbooking.php?id=<?php echo $row['vehicle_id']; ?>'">Rent Now</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No vehicles available at the moment.</p>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
