<?php

require 'include/db_connection.php';


$stmt = $conn->prepare("SELECT role FROM admins WHERE id = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$current_admin = $result->fetch_assoc();


if (!$current_admin || $current_admin['role'] !== 'superadmin') {
    header("Location: dashboard.php?error=access_denied");
    exit();
}

$delete_error = $delete_success = "";


if (isset($_GET['action'], $_GET['id']) && $_GET['action'] == 'delete') {
    $id = intval($_GET['id']);

    if ($id == $_SESSION['admin_id']) {
        $delete_error = "You cannot delete your own account!";
    } else {
       
        $stmt = $conn->prepare("SELECT role FROM admins WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin_to_delete = $result->fetch_assoc();
        
        if ($admin_to_delete && $admin_to_delete['role'] == 'superadmin') {
            $delete_error = "You cannot delete another superadmin!";
        } else {
           
            $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $delete_success = "Admin deleted successfully!";
            } else {
                $delete_error = "Error deleting admin: " . $conn->error;
            }
        }
    }
}


$query = "SELECT id, name, contact_no, email_address, gender, role, profile_picture FROM admins ORDER BY id";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Admin List</h2>
        <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
        <a href="adminregistration.php" class="btn btn-primary">Add New Admin</a>

        <?php if ($delete_success): ?>
            <div class="alert alert-success"> <?php echo $delete_success; ?> </div>
        <?php endif; ?>
        <?php if ($delete_error): ?>
            <div class="alert alert-danger"> <?php echo $delete_error; ?> </div>
        <?php endif; ?>

        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td>
                            <img src="<?php echo !empty($row['profile_picture']) && file_exists('uploads/profiles/' . $row['profile_picture']) ? 'uploads/profiles/' . $row['profile_picture'] : 'assets/default-avatar.png'; ?>" class="admin-img">
                        </td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['email_address']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($row['gender'])); ?></td>
                        <td>
                            <span class="badge bg-<?php echo ($row['role'] == 'superadmin') ? 'danger' : 'primary'; ?>">
                                <?php echo ucfirst(htmlspecialchars($row['role'])); ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <?php if ($row['id'] != $_SESSION['admin_id'] && $row['role'] != 'superadmin'): ?>
                                <a href="adminlist.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>