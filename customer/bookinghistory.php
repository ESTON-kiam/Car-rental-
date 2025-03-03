<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    header("Location: http://localhost:8000/customer/"); 
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

$customer_id = $_SESSION['customer_id'];

$sql_history = "SELECT b.booking_id, b.booking_date, b.vehicle_id, v.model_name AS car, b.booking_status
                FROM bookings b 
                INNER JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                WHERE b.customer_id = ? ORDER BY b.booking_date DESC";


$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param('i', $customer_id);
$stmt_history->execute();
$result_history = $stmt_history->get_result();


$per_page = 10;
$total_books = $result_history->num_rows;
$total_pages = ceil($total_books / $per_page);
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;


$sql_paginated_history = $sql_history . " LIMIT ?, ?";
$stmt_paginated_history = $conn->prepare($sql_paginated_history);
$stmt_paginated_history->bind_param('iii', $customer_id, $offset, $per_page);
$stmt_paginated_history->execute();
$result_paginated_history = $stmt_paginated_history->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            color: #333;
        }
        .container {
            max-width: 1100px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            text-align: center;
            font-size: 1.5em;
            padding: 1rem;
            border-radius: 5px 5px 0 0;
        }
        .status-badge {
            padding: 0.3em 0.6em;
            border-radius: 20px;
            font-size: 0.9em;
        }
        .status-pending {
            background-color: #f0ad4e;
            color: white;
        }
        .status-active {
            background-color: #5bc0de;
            color: white;
        }
        .status-completed {
            background-color: #28a745;
            color: white;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8f9fa;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .pagination {
            justify-content: center;
        }
        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
        .card-body {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0 0 5px 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card {
            margin-top: 30px;
            border-radius: 8px;
            overflow: hidden;
        }
        .table td, .table th {
            padding: 15px;
        }
        .no-bookings {
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>
    <main class="main-content">
<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h3>Booking History</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Vehicle</th>
                        <th>Booking Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_paginated_history->num_rows > 0): ?>
                        <?php while ($row = $result_paginated_history->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['booking_id']; ?></td>
                                <td><?php echo $row['car']; ?></td>
                                <td><?php echo date('F j, Y', strtotime($row['booking_date'])); ?></td>
                                <td>
                                    <span class="status-badge 
                                        <?php echo ($row['booking_status'] == 'pending') ? 'status-pending' : ''; ?>
                                        <?php echo ($row['booking_status'] == 'active') ? 'status-active' : ''; ?>
                                        <?php echo ($row['booking_status'] == 'completed') ? 'status-completed' : ''; ?>">
                                        <?php echo ucfirst($row['booking_status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr class="no-bookings">
                            <td colspan="4">No bookings found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

           
            <div class="d-flex justify-content-center">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="dashboard.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
    </main>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>


