<?php
require 'include/db_connection.php';


if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $employee_id = $_GET['delete'];
    
  
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
        try {
            $delete_sql = "DELETE FROM employees WHERE employee_id = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param("s", $employee_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $success_message = "Employee deleted successfully.";
            } else {
                $error_message = "Failed to delete employee.";
            }
            $stmt->close();
            
           
            header("Location: employeelist.php?deleted=success");
            exit();
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}


$search = isset($_GET['search']) ? $_GET['search'] : '';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($current_page - 1) * $records_per_page;

$sql_count = "SELECT COUNT(*) as total FROM employees";
$sql = "SELECT * FROM employees";


if (!empty($search)) {
    $search_term = "%$search%";
    $sql_count .= " WHERE employee_id LIKE ? OR name LIKE ? OR email_address LIKE ? OR designation LIKE ?";
    $sql .= " WHERE employee_id LIKE ? OR name LIKE ? OR email_address LIKE ? OR designation LIKE ?";
}


$sql .= " ORDER BY date_hired DESC LIMIT ?, ?";


if (!empty($search)) {
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    $stmt_count->execute();
} else {
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->execute();
}

$result_count = $stmt_count->get_result();
$row_count = $result_count->fetch_assoc();
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $records_per_page);
$stmt_count->close();


$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $stmt->bind_param("ssssii", $search_term, $search_term, $search_term, $search_term, $offset, $records_per_page);

} else {
    $stmt->bind_param("ii", $offset, $records_per_page);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees - Car Rental Management</title>
    <link href="assets/img/p.png" rel="icon">
    <link href="assets/img/p.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="assets/css/employeelist.css" rel="stylesheet">
</head>
<body>
<nav class="navbar">
    
    <a href="dashboard.php" class="navbar-brand">
     <i class="fas fa-car"></i><b>
             Online Car Rental Admin Panel</b></a>       
 </nav>
   
    <?php include 'include/sidebar.php'; ?>
    <main class="main-content">
        <div class="container-fluid mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Manage Employees</h4>
                            <a href="add_employee.php" class="btn btn-light"><i class="fas fa-plus"></i> Add New Employee</a>
                        </div>
                        <div class="card-body">
                            <?php if (isset($success_message) && !empty($success_message)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo $success_message; ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($error_message) && !empty($error_message)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo $error_message; ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'success'): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Employee deleted successfully.
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                            
                            <div class="row mb-3">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <div class="search-box">
                                        <form action="" method="GET">
                                            <div class="input-group">
                                                <input type="text" class="form-control" placeholder="Search by ID, name, email or designation" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <span class="text-muted">Total Employees: <?php echo $total_records; ?></span>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Contact</th>
                                            <th>Designation</th>
                                            <th>Date Hired</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result->num_rows > 0): ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email_address']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($row['date_hired'])); ?></td>
                                                    <td class="action-btns">
                                                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?php echo $row['employee_id']; ?>" title="Delete">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                        
                                                       
                                                        <div class="modal fade" id="deleteModal<?php echo $row['employee_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $row['employee_id']; ?>" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header bg-danger text-white">
                                                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $row['employee_id']; ?>">Confirm Delete</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Are you sure you want to delete employee <strong><?php echo htmlspecialchars($row['name']); ?></strong> (<?php echo htmlspecialchars($row['employee_id']); ?>)?
                                                                        <p class="text-danger mt-2 mb-0"><small>This action cannot be undone.</small></p>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <form action="employeelist.php?delete=<?php echo $row['employee_id']; ?>" method="post">
                                                                            <input type="hidden" name="confirm_delete" value="yes">
                                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No employees found<?php echo !empty($search) ? ' matching "' . htmlspecialchars($search) . '"' : ''; ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                           
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        
        $(document).ready(function(){
            setTimeout(function(){
                $(".alert").alert('close');
            }, 5000);
        });
    </script>
</body>
</html>