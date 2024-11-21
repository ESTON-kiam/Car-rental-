<head><link rel="preload" href="assets/css/admindash.css" as="style">
    <link rel="preload" href="assets/js/admindash.js" as="script">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admindash.css"></head>
<nav class="sidebar" role="navigation" aria-label="Main navigation">
    <div class="sidebar-content">
        <ul>
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li><a href="add_vehicles.php"><i class="fas fa-car-side"></i><span>Add Vehicle</span></a></li>
            <li>
                <button class="dropdown-toggle" aria-expanded="false" aria-controls="staff-menu">
                    <i class="fas fa-users"></i><span>Add Staff</span><i class="fas fa-chevron-right"></i>
                </button>
                <ul id="staff-menu" class="dropdown" hidden>
                    <li><a href="driverreg.php"><i class="fas fa-id-card"></i> Driver</a></li>
                    <li><a href="add_employee.html"><i class="fas fa-user-tie"></i> Employee</a></li>
                    <li><a href="reg.html"><i class="fas fa-id-card"></i>Add Admin</a></li>
                </ul>
            </li>
            <li>
                <button class="dropdown-toggle" aria-expanded="false" aria-controls="staff-menu">
                    <i class="fas fa-users"></i><span>List</span><i class="fas fa-chevron-right"></i>
                </button>
                <ul id="staff-menu" class="dropdown" hidden>
                    <li><a href="customerlist.php"><i class="fas fa-id-card"></i> Customers List</a></li>
                    <li><a href="add_employee.html"><i class="fas fa-user-tie"></i> Employee List</a></li>
                    <li><a href="driverslist.php"><i class="fas fa-id-card"></i>Driver List</a></li>
                </ul>
            </li>
            <li><a href="carbookings.php"><i class="fas fa-book"></i><span>Car Bookings</span></a></li>
            <li>
                <button class="dropdown-toggle" aria-expanded="false" aria-controls="payment-menu">
                    <i class="fas fa-money-bill-wave"></i><span>Payment History</span><i class="fas fa-chevron-right"></i>
                </button>
                <ul id="payment-menu" class="dropdown" hidden>
                    <li><a href="all_payments.html"><i class="fas fa-list"></i> All Payments</a></li>
                    <li><a href="pending_payments.html"><i class="fas fa-clock"></i> Pending Payments</a></li>
                    <li><a href="cancelled_payments.html"><i class="fas fa-times-circle"></i> Cancelled Payments</a></li>
                    <li><a href="successful_payments.html"><i class="fas fa-check-circle"></i> Successful Payments</a></li>
                </ul>
            </li>
            <li><a href="carcollection.php"><i class="fas fa-users"></i><span>Car collection</span></a></li>
            <li><a href="invoices.php"><i class="fas fa-users"></i><span>Invoices</span></a></li>
            <li><a href="support_view.php"><i class="fas fa-users"></i><span>Support Reply</span></a></li>
        </ul>
    </div>
</nav>