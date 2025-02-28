<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --sidebar-color: #1e293b;
            --sidebar-hover: rgba(255, 255, 255, 0.1);
            --sidebar-active: #3b82f6;
            --light-text: rgba(255, 255, 255, 0.75);
            --header-height: 60px;
            --transition-speed: 0.3s;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            transition: all var(--transition-speed) ease;
            z-index: 1000;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
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

        .section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            padding: 1.5rem 1.5rem 0.5rem;
            margin: 0;
        }

        .sidebar.collapsed .section-title {
            display: none;
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
            border-left: 3px solid transparent;
        }

        .sidebar a:hover,
        .sidebar button:hover {
            color: white;
            background-color: var(--sidebar-hover);
        }

        .sidebar a.active {
            color: white;
            background-color: rgba(59, 130, 246, 0.15);
            border-left: 3px solid var(--sidebar-active);
        }

        .sidebar i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .item-text {
            transition: opacity var(--transition-speed) ease;
            white-space: nowrap;
        }

        .sidebar.collapsed .item-text {
            opacity: 0;
            width: 0;
            height: 0;
            overflow: hidden;
        }

        .dropdown {
            background-color: rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease-out;
        }

        .dropdown.show {
            max-height: 500px;
            transition: max-height 0.3s ease-in;
        }

        .dropdown a {
            padding-left: 3.25rem;
        }

        .sidebar.collapsed .dropdown {
            display: none;
        }

        .fa-chevron-right {
            margin-left: auto;
            transition: transform 0.3s ease;
            opacity: 0.7;
            font-size: 0.8rem;
        }

        .dropdown-toggle[aria-expanded="true"] .fa-chevron-right {
            transform: rotate(90deg);
        }

        .sidebar.collapsed .fa-chevron-right {
            display: none;
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
            margin-left: var(--sidebar-collapsed-width);
        }

        .toggle-btn {
            position: fixed;
            top: calc(var(--header-height) + 10px);
            left: var(--sidebar-width);
            transform: translateX(-50%);
            background-color: #3b82f6;
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1001;
            transition: left var(--transition-speed) ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .toggle-btn:hover {
            background-color: #2563eb;
        }

        .toggle-btn.collapsed {
            left: var(--sidebar-collapsed-width);
        }

        /* Tooltip for collapsed sidebar */
        .sidebar.collapsed a,
        .sidebar.collapsed button {
            position: relative;
        }

        .sidebar.collapsed a::after,
        .sidebar.collapsed button::after {
            content: attr(data-title);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background-color: #2d3748;
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.85rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            z-index: 1002;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar.collapsed a:hover::after,
        .sidebar.collapsed button:hover::after {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
            }

            .sidebar.collapsed {
                transform: translateX(-100%);
            }

            .sidebar.expanded {
                transform: translateX(0);
                width: var(--sidebar-width);
            }

            .sidebar.expanded .item-text {
                opacity: 1;
                width: auto;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.collapsed {
                margin-left: 0;
            }

            .toggle-btn {
                left: 20px;
                transform: translateX(0);
            }

            .toggle-btn.collapsed {
                left: 20px;
            }

            .toggle-btn.expanded {
                left: calc(var(--sidebar-width) - 20px);
            }

            .sidebar.expanded .dropdown {
                display: block;
            }
        }
    </style>
</head>
<body>
    <nav class="sidebar" role="navigation" aria-label="Main navigation">
        <div class="sidebar-content">
            <h2 class="section-title">Dashboard</h2>
            <ul>
                <li><a href="dashboard.php" class="active" data-title="Dashboard"><i class="fas fa-tachometer-alt"></i><span class="item-text">Dashboard</span></a></li>
            </ul>
            
            <h2 class="section-title">Vehicle Management</h2>
            <ul>
                <li><a href="add_vehicles.php" data-title="Add Vehicle"><i class="fas fa-car-side"></i><span class="item-text">Add Vehicle</span></a></li>
                <li><a href="carcollection.php" data-title="Car Collection"><i class="fas fa-car"></i><span class="item-text">Car Collection</span></a></li>
            </ul>
            
            <h2 class="section-title">Staff Management</h2>
            <ul>
                <li>
                    <button class="dropdown-toggle" aria-expanded="false" aria-controls="staff-menu" data-title="Staff Management">
                        <i class="fas fa-users"></i><span class="item-text">Staff Management</span><i class="fas fa-chevron-right"></i>
                    </button>
                    <ul id="staff-menu" class="dropdown">
                        <li><a href="driverreg.php" data-title="Add Driver"><i class="fas fa-id-card"></i><span class="item-text">Add Driver</span></a></li>
                        <li><a href="add_employee.html" data-title="Add Employee"><i class="fas fa-user-tie"></i><span class="item-text">Add Employee</span></a></li>
                        <li><a href="adminregistration.php" data-title="Add Admin"><i class="fas fa-user-shield"></i><span class="item-text">Add Admin</span></a></li>
                    </ul>
                </li>
                <li>
                    <button class="dropdown-toggle" aria-expanded="false" aria-controls="list-menu" data-title="User Lists">
                        <i class="fas fa-list"></i><span class="item-text">User Lists</span><i class="fas fa-chevron-right"></i>
                    </button>
                    <ul id="list-menu" class="dropdown">
                        <li><a href="customerlist.php" data-title="Customers"><i class="fas fa-users"></i><span class="item-text">Customers</span></a></li>
                        <li><a href="employeelist.html" data-title="Employees"><i class="fas fa-user-tie"></i><span class="item-text">Employees</span></a></li>
                        <li><a href="driverslist.php" data-title="Drivers"><i class="fas fa-id-card"></i><span class="item-text">Drivers</span></a></li>
                        <li><a href="adminlist.php" data-title="Admins"><i class="fas fa-user-shield"></i><span class="item-text">Admins</span></a></li>
                    </ul>
                </li>
            </ul>
            
            <h2 class="section-title">Bookings</h2>
            <ul>
                <li><a href="carbookings.php" data-title="Car Bookings"><i class="fas fa-book"></i><span class="item-text">Car Bookings</span></a></li>
                <li><a href="cancelledbookings.php" data-title="Cancelled Bookings"><i class="fas fa-ban"></i><span class="item-text">Cancelled Bookings</span></a></li>
            </ul>
            
            <h2 class="section-title">Finance</h2>
            <ul>
                <li>
                    <button class="dropdown-toggle" aria-expanded="false" aria-controls="payment-menu" data-title="Payment History">
                        <i class="fas fa-money-bill-wave"></i><span class="item-text">Payment History</span><i class="fas fa-chevron-right"></i>
                    </button>
                    <ul id="payment-menu" class="dropdown">
                        <li><a href="all_payments.html" data-title="All Payments"><i class="fas fa-list"></i><span class="item-text">All Payments</span></a></li>
                        <li><a href="pending_payments.html" data-title="Pending Payments"><i class="fas fa-clock"></i><span class="item-text">Pending</span></a></li>
                        <li><a href="cancelled_payments.html" data-title="Cancelled Payments"><i class="fas fa-times-circle"></i><span class="item-text">Cancelled</span></a></li>
                        <li><a href="successful_payments.html" data-title="Successful Payments"><i class="fas fa-check-circle"></i><span class="item-text">Successful</span></a></li>
                    </ul>
                </li>
                <li><a href="invoices.php" data-title="Invoices"><i class="fas fa-file-invoice"></i><span class="item-text">Invoices</span></a></li>
            </ul>
            
            <h2 class="section-title">Support</h2>
            <ul>
                <li><a href="support_view.php" data-title="Support Tickets"><i class="fas fa-headset"></i><span class="item-text">Support Tickets</span></a></li>
            </ul>
        </div>
        <h2 class="section-title">Management</h2>
            <ul>
                <li><a href="customerinformation.php" data-title="Support Tickets"><i class="fas fa-headset"></i><span class="item-text">Customers</span></a></li>
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

            
            const sidebarState = localStorage.getItem('sidebarCollapsed');
            if (sidebarState === 'true') {
                sidebar.classList.add('collapsed');
                mainContent?.classList.add('collapsed');
                toggleBtn.classList.add('collapsed');
            }

            
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent?.classList.toggle('collapsed');
                toggleBtn.classList.toggle('collapsed');

                
                sidebar.classList.toggle('expanded');
                toggleBtn.classList.toggle('expanded');
                
               
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });

           
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    const dropdown = toggle.nextElementSibling;
                    const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                    
                    
                    toggle.setAttribute('aria-expanded', !isExpanded);
                    dropdown.classList.toggle('show');
                    
                   
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== toggle && otherToggle.getAttribute('aria-expanded') === 'true') {
                            otherToggle.setAttribute('aria-expanded', 'false');
                            otherToggle.nextElementSibling.classList.remove('show');
                        }
                    });
                });
            });

            
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.dropdown-toggle')) {
                    dropdownToggles.forEach(toggle => {
                        if (!toggle.contains(e.target)) {
                            toggle.setAttribute('aria-expanded', 'false');
                            toggle.nextElementSibling.classList.remove('show');
                        }
                    });
                }
            });

            
            sidebar.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
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