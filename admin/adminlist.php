<?php
require 'include/db_connection.php';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    
    if ($id == $_SESSION['admin_id']) {
        $delete_error = "You cannot delete your own account!";
    } else {
        
        $check_query = "SELECT role FROM admins WHERE id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin_to_delete = $result->fetch_assoc();
        
        $current_role_query = "SELECT role FROM admins WHERE id = ?";
        $stmt = $conn->prepare($current_role_query);
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_admin = $result->fetch_assoc();
        
        if ($admin_to_delete['role'] == 'superadmin' && $current_admin['role'] != 'superadmin') {
            $delete_error = "You don't have permission to delete a superadmin!";
        } else {
           
            $delete_query = "DELETE FROM admins WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
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
        .action-buttons a {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Admin List</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="add_admin.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Admin
                </a>
            </div>
        </div>
        
        <?php if (isset($delete_success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $delete_success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($delete_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $delete_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
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
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <?php if (!empty($row['profile_picture']) && file_exists('uploads/profiles/' . $row['profile_picture'])): ?>
                                                <img src="<?php echo $row['profile_picture']; ?>" class="admin-img" alt="Profile">
                                            <?php else: ?>
                                                <img src="assets/default-avatar.png" class="admin-img" alt="Default Profile">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email_address']); ?></td>
                                        <td><?php echo ucfirst($row['gender']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($row['role'] == 'superadmin') ? 'danger' : 'primary'; ?>">
                                                <?php echo ucfirst($row['role']); ?>
                                            </span>
                                        </td>
                                        <td class="action-buttons">
                                            <a href="edit_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No admins found!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
   
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete admin <span id="adminName"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>
    
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id, name) {
            document.getElementById('adminName').textContent = name;
            document.getElementById('confirmDeleteBtn').href = 'adminlist.php?action=delete&id=' + id;
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>

<?php

$conn->close();
?>