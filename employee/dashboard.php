<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Car Rental System - Employee Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--primary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        
        .logo {
            font-size: 1.4rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            margin-right: 10px;
        }
        
        .panel-type {
            background-color: var(--secondary-color);
            padding: 4px 8px;
            border-radius: 4px;
            margin-left: 10px;
            font-size: 0.9rem;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
            position: relative;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid white;
            object-fit: cover;
        }
        
        .user-name {
            margin-right: 5px;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 55px;
            right: 0;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 4px;
            width: 200px;
            display: none;
            z-index: 1001;
        }
        
        .dropdown-menu.active {
            display: block;
        }
        
        .dropdown-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: var(--dark-color);
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .dropdown-item:hover {
            background-color: #f5f7fa;
        }
        
        .dropdown-item i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 0;
        }
        
      
        .sidebar {
            position: fixed;
            left: 0;
            top: 73px;
            bottom: 0;
            width: 250px;
            background-color: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            overflow-y: auto;
            z-index: 999;
            transition: all 0.3s;
        }
        
        .sidebar-collapsed {
            width: 70px;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        
        .nav-pills .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--dark-color);
            text-decoration: none;
            transition: all 0.3s;
            border-radius: 0;
            border-left: 4px solid transparent;
        }
        
        .nav-pills .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        .nav-pills .nav-link:hover {
            background-color: #f8f9fa;
            border-left: 4px solid var(--primary-color);
        }
        
        .nav-pills .nav-link.active {
            background-color: #e9f5ff;
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }
        
        .role-badge {
            background-color: var(--primary-color);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            margin-top: 73px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        
        .main-content-expanded {
            margin-left: 70px;
        }
        
        .dashboard-title {
            margin-bottom: 20px;
            color: var(--dark-color);
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-weight: bold;
            color: var(--dark-color);
        }
        
        .card-body {
            padding: 20px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            padding: 20px;
            border-radius: 8px;
            color: white;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .stat-card.primary {
            background-color: var(--primary-color);
        }
        
        .stat-card.success {
            background-color: var(--success-color);
        }
        
        .stat-card.warning {
            background-color: var(--warning-color);
        }
        
        .stat-card.danger {
            background-color: var(--danger-color);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            display: none;
        }
        
        /* Task List */
        .task-list {
            list-style: none;
        }
        
        .task-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .task-item:last-child {
            border-bottom: none;
        }
        
        .task-checkbox {
            margin-right: 10px;
        }
        
        .task-title {
            flex-grow: 1;
        }
        
        .task-status {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-in-progress {
            background-color: #e0f2fe;
            color: #075985;
        }
        
        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }
        
     
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar-expanded {
                width: 250px;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .main-content-shrunk {
                margin-left: 250px;
            }
            
            .toggle-sidebar {
                display: block;
            }
            
            .nav-pills .nav-link span {
                display: none;
            }
            
            .sidebar-expanded .nav-pills .nav-link span {
                display: inline;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 0.8rem 1rem;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                width: 0;
                box-shadow: none;
            }
            
            .sidebar-expanded {
                width: 250px;
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .user-name {
                display: none;
            }
        }
        
        /* Car Cleaner Dashboard */
        .cleaner-stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .vehicle-list {
            list-style: none;
        }
        
        .vehicle-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .vehicle-item:last-child {
            border-bottom: none;
        }
        
        .vehicle-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        
        .vehicle-details {
            flex-grow: 1;
        }
        
        .vehicle-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .vehicle-info {
            display: flex;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .vehicle-info div {
            margin-right: 15px;
            display: flex;
            align-items: center;
        }
        
        .vehicle-info i {
            margin-right: 5px;
        }
        
        .cleaning-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
        }
        
        /* Technician Dashboard */
        .maintenance-item {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            overflow: hidden;
        }
        
        .maintenance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        
        .maintenance-title {
            font-weight: bold;
        }
        
        .maintenance-body {
            padding: 15px;
        }
        
        .maintenance-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .maintenance-detail {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .maintenance-progress {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 4px;
        }
        
       
        .booking-list {
            list-style: none;
        }
        
        .booking-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .booking-item:last-child {
            border-bottom: none;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .booking-id {
            font-weight: bold;
        }
        
        .booking-date {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .booking-customer {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .booking-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
            margin-left: 10px;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-outline-danger {
            border-color: var(--danger-color);
            color: var(--danger-color);
        }
        
        .btn-outline-danger:hover {
            background-color: var(--danger-color);
            color: white;
        }
        
        /* Toggle dashboard views */
        #car-cleaner-dashboard, #technician-dashboard, #office-assistant-dashboard {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <i class="fas fa-car"></i> 
            Online Car Rental System
            <span class="panel-type" id="panel-type-display">Driver Panel</span>
        </div>
        <button class="toggle-sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <div class="user-profile" id="user-profile">
            <img src="/api/placeholder/200/200" alt="User" class="user-avatar">
            <span class="user-name" id="employee-name">John Doe</span>
            <i class="fas fa-chevron-down"></i>
            
            <div class="dropdown-menu" id="profile-dropdown">
                <a href="#" class="dropdown-item">
                    <i class="fas fa-user"></i> View Profile
                </a>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-key"></i> Change Password
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div id="employee-role" class="role-badge">Car Cleaner</div>
            <div id="employee-id">EMP-001</div>
        </div>
        <ul class="nav-pills">
            <li>
                <a href="#" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-link">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-link">
                    <i class="fas fa-tasks"></i>
                    <span>Tasks</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-link">
                    <i class="fas fa-car"></i>
                    <span>Vehicles</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content" id="main-content">
        <!-- Car Cleaner Dashboard -->
        <div id="car-cleaner-dashboard">
            <h2 class="dashboard-title">Car Cleaner Dashboard</h2>
            
            <!-- Stats Overview -->
            <div class="cleaner-stats-container">
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <div class="stat-value">8</div>
                    <div class="stat-label">Assigned Vehicles Today</div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">5</div>
                    <div class="stat-label">Completed Cleanings</div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-value">3</div>
                    <div class="stat-label">Pending Cleanings</div>
                </div>
            </div>
            
            <!-- Today's Schedule -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-day"></i> Today's Cleaning Schedule
                </div>
                <div class="card-body">
                    <ul class="vehicle-list">
                        <li class="vehicle-item">
                            <img src="/api/placeholder/160/120" alt="Vehicle" class="vehicle-image">
                            <div class="vehicle-details">
                                <div class="vehicle-title">Toyota Camry - KBX 123A</div>
                                <div class="vehicle-info">
                                    <div><i class="fas fa-clock"></i> 09:00 AM</div>
                                    <div><i class="fas fa-car-alt"></i> Sedan</div>
                                    <div><i class="fas fa-map-marker-alt"></i> Bay #3</div>
                                </div>
                            </div>
                            <span class="cleaning-status status-completed">Completed</span>
                        </li>
                        
                        <li class="vehicle-item">
                            <img src="/api/placeholder/160/120" alt="Vehicle" class="vehicle-image">
                            <div class="vehicle-details">
                                <div class="vehicle-title">Honda CR-V - KCY 456B</div>
                                <div class="vehicle-info">
                                    <div><i class="fas fa-clock"></i> 10:30 AM</div>
                                    <div><i class="fas fa-car-alt"></i> SUV</div>
                                    <div><i class="fas fa-map-marker-alt"></i> Bay #2</div>
                                </div>
                            </div>
                            <span class="cleaning-status status-completed">Completed</span>
                        </li>
                        
                        <li class="vehicle-item">
                            <img src="/api/placeholder/160/120" alt="Vehicle" class="vehicle-image">
                            <div class="vehicle-details">
                                <div class="vehicle-title">Ford F-150 - KDZ 789C</div>
                                <div class="vehicle-info">
                                    <div><i class="fas fa-clock"></i> 01:00 PM</div>
                                    <div><i class="fas fa-car-alt"></i> Truck</div>
                                    <div><i class="fas fa-map-marker-alt"></i> Bay #1</div>
                                </div>
                            </div>
                            <span class="cleaning-status status-in-progress">In Progress</span>
                        </li>
                        
                        <li class="vehicle-item">
                            <img src="/api/placeholder/160/120" alt="Vehicle" class="vehicle-image">
                            <div class="vehicle-details">
                                <div class="vehicle-title">Nissan Rogue - KEA 101D</div>
                                <div class="vehicle-info">
                                    <div><i class="fas fa-clock"></i> 02:30 PM</div>
                                    <div><i class="fas fa-car-alt"></i> SUV</div>
                                    <div><i class="fas fa-map-marker-alt"></i> Bay #4</div>
                                </div>
                            </div>
                            <span class="cleaning-status status-pending">Pending</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Cleaning Checklist -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clipboard-list"></i> Cleaning Checklist
                </div>
                <div class="card-body">
                    <ul class="task-list">
                        <li class="task-item">
                            <input type="checkbox" class="task-checkbox" checked>
                            <span class="task-title">Exterior wash and dry</span>
                        </li>
                        <li class="task-item">
                            <input type="checkbox" class="task-checkbox" checked>
                            <span class="task-title">Windows and mirrors cleaning</span>
                        </li>
                        <li class="task-item">
                            <input type="checkbox" class="task-checkbox">
                            <span class="task-title">Interior vacuuming</span>
                        </li>
                        <li class="task-item">
                            <input type="checkbox" class="task-checkbox">
                            <span class="task-title">Dashboard and console wiping</span>
                        </li>
                        <li class="task-item">
                            <input type="checkbox" class="task-checkbox">
                            <span class="task-title">Tire and rim cleaning</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Technician Dashboard -->
        <div id="technician-dashboard">
            <h2 class="dashboard-title">Maintenance Technician Dashboard</h2>
            
            <!-- Stats Overview -->
            <div class="stats-container">
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-value">5</div>
                    <div class="stat-label">Assigned Maintenance Tasks</div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">3</div>
                    <div class="stat-label">Completed Tasks</div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-value">2</div>
                    <div class="stat-label">Pending Tasks</div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">1</div>
                    <div class="stat-label">Critical Issues</div>
                </div>
            </div>
            
       
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-wrench"></i> Active Maintenance Tasks
                </div>
                <div class="card-body">
                    <div class="maintenance-item">
                        <div class="maintenance-header">
                            <div class="maintenance-title">Toyota Camry - KBX 123A</div>
                            <span class="task-status status-in-progress">In Progress</span>
                        </div>
                        <div class="maintenance-body">
                            <div class="maintenance-details">
                                <div class="maintenance-detail">
                                    <span class="detail-label">Maintenance Type</span>
                                    <span class="detail-value">Regular Service</span>
                                </div>
                                <div class="maintenance-detail">
                                    <span class="detail-label">Due Date</span>
                                    <span class="detail-value">Mar 13, 2025</span>
                                </div>
                                <div class="maintenance-detail">
                                    <span class="detail-label">Assigned By</span>
                                    <span class="detail-value">Manager Johnson</span>
                                </div>
                                <div class="maintenance-detail">
                                    <span class="detail-label">Priority</span>
                                    <span class="detail-value">Medium</span>
                                </div>
                            </div>
                            <div>
                                <div class="detail-label">Progress</div>
                                <div class="detail-value">60%</div>
                                <div class="maintenance-progress">
                                    <div class="progress-bar" style="width: 60%; background-color: var(--primary-color);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="maintenance-item">
                        <div class="maintenance-header">
                            <div class="maintenance-title">Honda CR-V - KCY 456B</div>
                            <span class="task-status status-pending">Pending</span>
                        </div>
                        <div class="maintenance-body">
                            <div class="maintenance-details">
                                <div class="maintenance-detail">
                                    <span class="detail-label">Maintenance Type</span>
                                    <span class="detail-value">Brake Inspection</span>
                                </div>
                                <div class="maintenance-detail">
                                    <span class="detail-label">Due Date</span>
                                    <span class="detail-value">Mar 14, 2025</span>
                                </div>
                                <div class="maintenance-detail">
                                    <span class="detail-label">Assigned By</span>
                                    <span class="detail-value">Manager Johnson</span>
                                </div>
                                <div class="maintenance-detail">
                                    <span class="detail-label">Priority</span>
                                    <span class="detail-value">High</span>
                                </div>
                            </div>
                            <div>
                                <div class="detail-label">Progress</div>
                                <div class="detail-value">0%</div>
                                <div class="maintenance-progress">
                                    <div class="progress-bar" style="width: 0%; background-color: var(--primary-color);">