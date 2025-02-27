<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 0;
            --sidebar-color: #1e293b;
            --light-text: rgba(255, 255, 255, 0.75);
            --header-height: 60px;
            --transition-speed: 0.3s;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            overflow-x: hidden;
        }

        .page-container {
            display: flex;
            min-height: 100vh;
            padding: 0;
        }

        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background-color: var(--sidebar-color);
            color: white;
            overflow-y: auto;
            transition: transform var(--transition-speed) ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar-content {
            padding: 1rem 0;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        button:hover {
            background-color: #0056b3; 
        }

        .sidebar a, 
        .sidebar button {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            color: var(--light-text);
            text-decoration: none;
            width: 100%;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 0.95rem;
            text-align: left;
            transition: all 0.2s ease;
            position: relative;
        }

        .sidebar a:hover,
        .sidebar button:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            padding-left: 1.75rem;
        }

        .sidebar a.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 4px solid #fff;
        }

        .sidebar i {
            width: 20px;
            text-align: center;
        }

        .dropdown {
            background-color: rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-height: 0;
            text-decoration: none;
            transition: max-height 0.3s ease-out;
        }

        .dropdown.show {
            max-height: 500px;
            transition: max-height 0.3s ease-in;
        }

        .dropdown a {
            padding-left: 3.25rem;
        }

        .dropdown a:hover {
            padding-left: 3.5rem;
        }

        .fa-chevron-right {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .dropdown-toggle[aria-expanded="true"] .fa-chevron-right {
            transform: rotate(90deg);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 20px;
            margin-top: var(--header-height);
            transition: margin-left var(--transition-speed) ease;
            background-color: #f5f5f5;
        }

        .main-content.collapsed {
            margin-left: 0;
        }

        .toggle-btn {
            position: fixed;
            top: calc(var(--header-height) + 10px);
            left: var(--sidebar-width);
            transform: translateX(-50%);
            background-color: #000042;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1001;
            transition: left var(--transition-speed) ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .toggle-btn:hover {
            background-color: #444;
        }

        .toggle-btn.collapsed {
            left: 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-btn {
                left: 0;
            }

            .sidebar.expanded {
                transform: translateX(0);
            }

            .toggle-btn.expanded {
                left: var(--sidebar-width);
            }
        }
    </style>
</head>
<body>
   
        <nav class="sidebar" role="navigation" aria-label="Main navigation">
            <div class="sidebar-content">
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li><a href="add_vehicles.php"><i class="fas fa-car-side"></i><span>Add Vehicle</span></a></li>
                    <li>
                    <li><a href="carcollection.php"><i class="fas fa-car"></i><span>Car Collection</span></a></li>
                        <button class="dropdown-toggle" aria-expanded="false" aria-controls="staff-menu">
                            <i class="fas fa-users"></i><span>Add Staff</span><i class="fas fa-chevron-right"></i>
                        </button>
                        <ul id="staff-menu" class="dropdown">
                            <li><a href="driverreg.php"><i class="fas fa-id-card"></i> Driver</a></li>
                            <li><a href="add_employee.html"><i class="fas fa-user-tie"></i> Employee</a></li>
                            <li><a href="adminregistration.php"><i class="fas fa-id-card"></i>Add Admin</a></li>
                        </ul>
                    </li>
                    <li>
                        <button class="dropdown-toggle" aria-expanded="false" aria-controls="list-menu">
                            <i class="fas fa-list"></i><span>List</span><i class="fas fa-chevron-right"></i>
                        </button>
                        <ul id="list-menu" class="dropdown">
                            <li><a href="customerlist.php"><i class="fas fa-users"></i> Customers List</a></li>
                            <li><a href="add_employee.html"><i class="fas fa-user-tie"></i> Employee List</a></li>
                            <li><a href="driverslist.php"><i class="fas fa-id-card"></i>Driver List</a></li>
                            <li><a href="adminlist.php"><i class="fas fa-id-card"></i>Admins</a></li>
                        </ul>
                    </li>
                    <li><a href="carbookings.php"><i class="fas fa-book"></i><span>Car Bookings</span></a></li>
                    <li>
                        <button class="dropdown-toggle" aria-expanded="false" aria-controls="payment-menu">
                            <i class="fas fa-money-bill-wave"></i><span>Payment History</span><i class="fas fa-chevron-right"></i>
                        </button>
                        <ul id="payment-menu" class="dropdown">
                            <li><a href="all_payments.html"><i class="fas fa-list"></i> All Payments</a></li>
                            <li><a href="pending_payments.html"><i class="fas fa-clock"></i> Pending Payments</a></li>
                            <li><a href="cancelled_payments.html"><i class="fas fa-times-circle"></i> Cancelled Payments</a></li>
                            <li><a href="successful_payments.html"><i class="fas fa-check-circle"></i> Successful Payments</a></li>
                        </ul>
                    </li>
                    
                    <li><a href="invoices.php"><i class="fas fa-file-invoice"></i><span>Invoices</span></a></li>
                    <li><a href="support_view.php"><i class="fas fa-headset"></i><span>Support Reply</span></a></li>
                </ul>
            </div>
        </nav>

        <button class="toggle-btn" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const toggleBtn = document.querySelector('.toggle-btn');
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

            // Toggle sidebar
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('collapsed');
                toggleBtn.classList.toggle('collapsed');

                // For mobile
                sidebar.classList.toggle('expanded');
                toggleBtn.classList.toggle('expanded');
            });

            // Handle dropdowns
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    const dropdown = toggle.nextElementSibling;
                    const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                    
                    // Close all other dropdowns
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== toggle) {
                            otherToggle.setAttribute('aria-expanded', 'false');
                            otherToggle.nextElementSibling.classList.remove('show');
                        }
                    });

                    // Toggle current dropdown
                    toggle.setAttribute('aria-expanded', !isExpanded);
                    dropdown.classList.toggle('show');
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.sidebar-content')) {
                    dropdownToggles.forEach(toggle => {
                        toggle.setAttribute('aria-expanded', 'false');
                        toggle.nextElementSibling.classList.remove('show');
                    });
                }
            });
        });
    </script>
</body>
</html>