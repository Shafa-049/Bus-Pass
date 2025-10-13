<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'SEUSL Bus Pass Admin'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0F766E;
            --primary-light: #2DD4BF;
            --primary-dark: #115E59;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            padding-top: 56px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            background-color: #f8f9fa;
            min-height: calc(100vh - 56px);
            margin-top: 0;
            padding-top: 1rem;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 56px; /* Start below the header */
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - 56px);
            background: linear-gradient(180deg, #008080 0%, #006666 100%);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1020;
            transition: all 0.3s ease;
            overflow-y: auto;
            color: #fff;
            padding: 0.5rem 0;
        }
        
        .navbar-admin {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            z-index: 1030;
        }
        
        .sidebar-link {
            padding: 0.65rem 1.5rem;
            color: #ffffff; /* Changed from rgba to solid white */
            font-weight: 500; /* Added font weight for better readability */
            text-decoration: none;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
            margin: 0.15rem 0.5rem;
            border-radius: 0.25rem;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2); /* Added subtle text shadow */
        }
        
        .sidebar-link.text-danger {
            color: #f87171 !important;
        }
        
        .sidebar-link.text-danger:hover {
            background-color: rgba(248, 113, 113, 0.1);
        }
        
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.2); /* Slightly darker on hover */
            padding-left: 25px;
            border-left-color: #fff;
        }
        
        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.25); /* More visible active state */
            border-left-color: #fff;
            color: #fff !important;
            font-weight: 600; /* Bolder for active item */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        }
        
        .sidebar-link i {
            width: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.8); /* Improve icon visibility */
            margin-right: 12px;
            font-size: 1.1rem;
            opacity: 0.8;
        }
        .sidebar .dropdown-item {
            color: rgba(255, 255, 255, 0.9);
            padding: 8px 15px;
        }
        
        .sidebar .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        
        .sidebar .dropdown-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 0.5rem 0;
        }
        
        /* Responsive */
        /* Ensure content is full width on mobile */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
            .sidebar {
                background-color: #0F766E;
                color: #fff;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
            
            .sidebar .menu-header {
                color: rgba(255, 255, 255, 0.5);
                font-size: 0.7rem;
                text-transform: uppercase;
                font-weight: 600;
                padding: 1.5rem 1.5rem 0.5rem;
                letter-spacing: 0.5px;
                margin: 0;
            }
            
            .sidebar .menu-header:first-child {
                padding-top: 1rem;
            }
            
            .sidebar .menu-header + .sidebar-link {
                margin-top: 0.5rem;
            }
            
            .sidebar-overlay.show {
                display: block;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-admin fixed-top">
        <div class="container-fluid">
            <button class="btn btn-link text-dark d-lg-none me-2" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand navbar-brand-admin" href="dashboard.php">
                <i class="fas fa-bus me-2"></i>SEUSL Bus Pass Admin
            </a>
            
            <div class="d-flex align-items-center ms-auto">
                <!-- Notifications Dropdown -->
                <div class="dropdown me-3">
                    <a class="btn btn-link text-dark p-0" href="#" role="button" id="notificationsDropdown" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><a class="dropdown-item" href="#">New registration request</a></li>
                        <li><a class="dropdown-item" href="#">System update available</a></li>
                        <li><a class="dropdown-item" href="#">New message received</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">View all notifications</a></li>
                    </ul>
                </div>
                
                <!-- User Dropdown -->
                <div class="dropdown user-dropdown">
                    <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" 
                       id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="me-2 d-none d-sm-block">
                            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
                            <small class="text-muted">Administrator</small>
                        </div>
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px;">
                            <i class="fas fa-user text-white"></i>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="py-2">
            <div class="px-2">
                <h6 class="text-uppercase text-white fw-bold small mb-3 px-3">Main Menu</h6>
                <a href="dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="users.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="depots.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'depots.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bus"></i> Depots
                </a>
                <a href="routes.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'routes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-route"></i> Routes
                </a>
                <a href="passes.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'passes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i> Passes
                </a>
            </div>
            
            <div class="px-3 mb-4">
                <h6 class="text-uppercase text-white fw-bold small mb-3 mt-4 px-3" style="color: #fff;">Management</h6>
                <a href="reports.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <a href="settings.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Settings
                
                <!-- Logout Button -->
                <div class="mt-auto pt-3 border-top border-white-10">
                    <a href="../auth/logout.php" class="sidebar-link text-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content" style="padding-top: 1rem;">
        <!-- JavaScript for sidebar toggle -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const sidebarToggle = document.getElementById('sidebarToggle');
                const sidebarOverlay = document.getElementById('sidebarOverlay');
                
                // Toggle sidebar on button click
                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        sidebar.classList.toggle('show');
                        sidebarOverlay.classList.toggle('show');
                    });
                }
                
                // Close sidebar when clicking overlay
                if (sidebarOverlay) {
                    sidebarOverlay.addEventListener('click', function() {
                        sidebar.classList.remove('show');
                        this.classList.remove('show');
                    });
                }
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 992) {
                        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                            sidebar.classList.remove('show');
                            sidebarOverlay.classList.remove('show');
                        }
                    }
                });
            });
            
            // Update active state on page load
            document.addEventListener('DOMContentLoaded', function() {
                const currentPage = window.location.pathname.split('/').pop();
                const navLinks = document.querySelectorAll('.sidebar-link');
                
                navLinks.forEach(link => {
                    const linkHref = link.getAttribute('href');
                    if (linkHref && linkHref.includes(currentPage)) {
                        link.classList.add('active');
                    }
                });
            });
        </script>
