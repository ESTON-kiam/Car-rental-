<?php

require_once 'include/db_connection.php';

function getBookingCount($conn, $customer_id) {
    $sql = "SELECT COUNT(*) as total FROM bookings WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}


function getCancelledBookingCount($conn, $customer_id) {
    $sql = "SELECT COUNT(*) as total FROM cancelledbookings WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}


$results_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $results_per_page;


$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "WHERE full_name LIKE ? OR email LIKE ? OR mobile LIKE ?";
    $search_param = "%$search%";
}


if (!empty($status_filter)) {
    $search_condition = empty($search_condition) ? "WHERE status = ?" : "$search_condition AND status = ?";
}


$count_sql = "SELECT COUNT(*) as total FROM customers $search_condition";
$count_stmt = $conn->prepare($count_sql);

if (!empty($search) && !empty($status_filter)) {
    $count_stmt->bind_param("ssss", $search_param, $search_param, $search_param, $status_filter);
} elseif (!empty($search)) {
    $count_stmt->bind_param("sss", $search_param, $search_param, $search_param);
} elseif (!empty($status_filter)) {
    $count_stmt->bind_param("s", $status_filter);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_customers = $count_row['total'];
$total_pages = ceil($total_customers / $results_per_page);

$sql = "SELECT * FROM customers $search_condition ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if (!empty($search) && !empty($status_filter)) {
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $status_filter, $results_per_page, $offset);
} elseif (!empty($search)) {
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $results_per_page, $offset);
} elseif (!empty($status_filter)) {
    $stmt->bind_param("sii", $status_filter, $results_per_page, $offset);
} else {
    $stmt->bind_param("ii", $results_per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customers - Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>

.main-content {
  padding-top: 70px; 
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-body {
  padding-top: 20px;
}

.row.g-3.mb-4 {
  margin-top: 15px;
  padding-top: 5px;
}
header, .navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1030;
}


body {
  padding-top: 56px; 
}


.sidebar {
  top: 56px; 
  height: calc(100vh - 56px);
}

@media (max-width: 768px) {
  .row.g-3.mb-4 {
    flex-direction: column;
  }
  
  .row.g-3.mb-4 > div {
    margin-bottom: 10px;
    width: 100%;
  }
  
  .col-md-4, .col-md-3 {
    width: 100%;
    max-width: 100%;
    flex: 0 0 100%;
  }
}
        </style>
</head>
<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/sidebar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
           
            
            <main class="main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Customer Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="export_customers.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-download"></i> Export
                        </a>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Customers List</h5>
                    </div>
                    <div class="card-body">
                      
                        <form method="GET" action="" class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search by name, email or mobile" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="suspended" <?php echo $status_filter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <?php if (!empty($search) || !empty($status_filter)): ?>
                                <a href="viewcustomers.php" class="btn btn-outline-secondary">Reset Filters</a>
                                <?php endif; ?>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#ID</th>
                                        <th>Profile</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Status</th>
                                        <th>Active Bookings</th>
                                        <th>Completed Bookings</th>
                                        <th>Cancelled Bookings</th>
                                        <th>Total Bookings</th>
                                        <th>Registered Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $total_bookings = getBookingCount($conn, $row['id']);
                                            $cancelled_bookings = getCancelledBookingCount($conn, $row['id']);
                                            $active_sql = "SELECT COUNT(*) as total FROM bookings WHERE customer_id = ? AND booking_status = 'active'";
                                            $active_stmt = $conn->prepare($active_sql);
                                            $active_stmt->bind_param("i", $row['id']);
                                            $active_stmt->execute();
                                            $active_result = $active_stmt->get_result();
                                            $active_row = $active_result->fetch_assoc();
                                            $active_bookings = $active_row['total'];
                                            
                                            $completed_sql = "SELECT COUNT(*) as total FROM bookings WHERE customer_id = ? AND booking_status = 'completed'";
                                            $completed_stmt = $conn->prepare($completed_sql);
                                            $completed_stmt->bind_param("i", $row['id']);
                                            $completed_stmt->execute();
                                            $completed_result = $completed_stmt->get_result();
                                            $completed_row = $completed_result->fetch_assoc();
                                            $completed_bookings = $completed_row['total'];
                                    ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td>
                                                <img src="uploads/profiles/<?php echo htmlspecialchars($row['profile_picture']); ?>" 
                                                     alt="Profile" class="rounded-circle" width="40" height="40">
                                            </td>
                                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $row['status'] === 'active' ? 'success' : 
                                                        ($row['status'] === 'inactive' ? 'secondary' : 
                                                            ($row['status'] === 'suspended' ? 'danger' : 'warning')); 
                                                ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary"><?php echo $active_bookings; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success"><?php echo $completed_bookings; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger"><?php echo $cancelled_bookings; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-dark"><?php echo $total_bookings; ?></span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="customer_details.php?id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="customer_bookings.php?id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="View Bookings">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="updateStatus(<?php echo $row['id']; ?>)" title="Update Status">
                                                        <i class="fas fa-user-cog"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                        <tr>
                                            <td colspan="12" class="text-center">No customers found</td>
                                        </tr>
                                    <?php 
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                        Previous
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                        Next
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <div class="text-muted">Total: <?php echo $total_customers; ?> customers</div>
                    </div>
                </div>
            
        </div>
    </div>
    
    <section class="main-content">
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Update Customer Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="statusForm" action="update_customer_status.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="customer_id" name="customer_id">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason (Optional)</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
                                </section>
  
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(id) {
            document.getElementById('customer_id').value = id;
            var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
            statusModal.show();
        }
    </script>
    </main>
</body>
</html>

<?php
$conn->close();
?>