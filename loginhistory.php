<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Access Denied! Please log in.");
}

$user_id = $_SESSION['user_id'];


$sql = "SELECT ip_address, browser, device, login_time FROM login_history WHERE user_id = ? ORDER BY login_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<h2>Login History</h2>

<table>
    <thead>
    <tr>
        <th>IP Address</th>
        <th>Browser</th>
        <th>Device</th>
        <th>Date</th>
    </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
        <td><?php echo htmlspecialchars($row['browser']); ?></td>
        <td><?php echo htmlspecialchars($row['device']); ?></td>
        <td><?php echo htmlspecialchars($row['login_time']); ?></td>
    </tr>
    <?php } ?>
    </tbody>
</table>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>