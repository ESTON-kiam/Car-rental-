<?php
require 'include/db_connection.php';


$admin_id = 0;
$admin_data = null;
$error_message = '';
$success_message = '';


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $admin_id = intval($_GET['id']);
    
   
    $stmt = $conn->prepare("SELECT role FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $current_admin = $result->fetch_assoc();
        if ($current_admin['role'] !== 'superadmin' && $_SESSION['admin_id'] !== $admin_id) {
            $error_message = "You don't have permission to edit other admin accounts.";
            $admin_id = 0; 
        }
    } else {
        $error_message = "Your session is invalid. Please log in again.";
        header("Location: http://localhost:8000/admin/");
        exit();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && $admin_id > 0) {
    
    $stmt = $conn->prepare("SELECT role FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_admin = $result->fetch_assoc();
    
    if ($current_admin['role'] === 'superadmin') {
        $new_role = $_POST['role'];
        
        if ($new_role === 'superadmin' || $new_role === 'admin') {
            $stmt = $conn->prepare("UPDATE admins SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $admin_id);
            
            if ($stmt->execute()) {
                $success_message = "Admin role updated successfully!";
            } else {
                $error_message = "Error updating admin role: " . $conn->error;
            }
        } else {
            $error_message = "Invalid role specified.";
        }
    } else {
        $error_message = "Only superadmins can change admin roles.";
    }
}


if ($admin_id > 0) {
    $stmt = $conn->prepare("SELECT id, name, contact_no, email_address, gender, profile_picture, role FROM admins WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin_data = $result->fetch_assoc();
    } else {
        $error_message = "Admin not found.";
        $admin_id = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .admin-profile {
            padding: 20px;
            border-radius: 5px;
            background-color: #f8f9fa;
            margin-bottom: 20px;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #6c757d;
        }
        .form-control:disabled {
            background-color: #e9ecef;
            opacity: 1;
        }
        .alert {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Edit Admin</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($admin_data): ?>
                            <div class="admin-profile text-center">
                                <img src="<?php echo !empty($admin_data['profile_picture']) ? 'uploads/profiles/' . htmlspecialchars($admin_data['profile_picture']) : 'assets/img/default-profile.png'; ?>" 
                                     alt="Profile Picture" class="profile-img mb-3">
                                <h4><?php echo htmlspecialchars($admin_data['name']); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars($admin_data['email_address']); ?></p>
                            </div>
                            
                            <form method="POST" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($admin_data['name']); ?>" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contact" class="form-label">Contact Number</label>
                                        <input type="text" class="form-control" id="contact" value="<?php echo htmlspecialchars($admin_data['contact_no']); ?>" disabled>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($admin_data['email_address']); ?>" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gender" class="form-label">Gender</label>
                                        <input type="text" class="form-control" id="gender" value="<?php echo ucfirst(htmlspecialchars($admin_data['gender'])); ?>" disabled>
                                    </div>
                                </div>
                                
                                <?php 
                               
                                $stmt = $conn->prepare("SELECT role FROM admins WHERE id = ?");
                                $stmt->bind_param("i", $_SESSION['admin_id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $current_admin = $result->fetch_assoc();
                                
                                if ($current_admin['role'] === 'superadmin'): 
                                ?>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="role" class="form-label">Role</label>
                                        <select class="form-select" id="role" name="role">
                                            <option value="admin" <?php echo $admin_data['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="superadmin" <?php echo $admin_data['role'] === 'superadmin' ? 'selected' : ''; ?>>Super Admin</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary px-4">Update Role</button>
                                    <a href="adminlist.php" class="btn btn-secondary">Cancel</a>
                                </div>
                                <?php else: ?>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="role_display" class="form-label">Role</label>
                                        <input type="text" class="form-control" id="role_display" value="<?php echo ucfirst(htmlspecialchars($admin_data['role'])); ?>" disabled>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="manage_admins.php" class="btn btn-secondary">Back</a>
                                </div>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <div class="text-center">
                                <p>Admin not found or you don't have permission to view this page.</p>
                                <a href="adminlist.php" class="btn btn-primary">Back to Admin Management</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>