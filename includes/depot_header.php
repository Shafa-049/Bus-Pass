<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" 
            crossorigin="anonymous"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
    // Initialize when DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize all popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Initialize all collapse components
        var collapseElementList = [].slice.call(document.querySelectorAll('.collapse'));
        collapseElementList.map(function (collapseEl) {
            return new bootstrap.Collapse(collapseEl, {
                toggle: false
            });
        });
        
        // Initialize all modals with better focus management
        var modalElementList = [].slice.call(document.querySelectorAll('.modal'));
        modalElementList.forEach(function (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            let focusedElementBeforeModal = null;
            
            // Handle show event
            modalEl.addEventListener('show.bs.modal', function() {
                // Store the current focus
                focusedElementBeforeModal = document.activeElement;
                
                // Update ARIA attributes
                this.removeAttribute('aria-hidden');
                this.setAttribute('aria-modal', 'true');
                this.removeAttribute('inert');
                
                // Focus the first focusable element in the modal
                const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
                const firstFocusableElement = this.querySelectorAll(focusableElements)[0];
                
                // Focus the first element after a small delay to ensure the modal is visible
                setTimeout(() => {
                    if (firstFocusableElement) firstFocusableElement.focus();
                }, 100);
            });
            
            // Handle hide event
            modalEl.addEventListener('hidden.bs.modal', function() {
                // Restore focus to the element that had it before the modal opened
                if (focusedElementBeforeModal) {
                    focusedElementBeforeModal.focus();
                }
                
                // Update ARIA attributes
                this.setAttribute('aria-hidden', 'true');
                this.removeAttribute('aria-modal');
                this.setAttribute('inert', '');
            });
            
            // Trap focus inside the modal when open
            modalEl.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') return; // Let Bootstrap handle escape
                
                if (e.key === 'Tab') {
                    const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
                    const focusableContent = this.querySelectorAll(focusableElements);
                    
                    if (focusableContent.length === 0) return;
                    
                    const firstFocusableElement = focusableContent[0];
                    const lastFocusableElement = focusableContent[focusableContent.length - 1];
                    
                    if (e.shiftKey) {
                        if (document.activeElement === firstFocusableElement) {
                            lastFocusableElement.focus();
                            e.preventDefault();
                        }
                    } else {
                        if (document.activeElement === lastFocusableElement) {
                            firstFocusableElement.focus();
                            e.preventDefault();
                        }
                    }
                }
            });
            
            return modal;
        });
        
        // Debug: Log Bootstrap initialization
        console.log('Bootstrap components initialized');
    });
    </script>
    
    <style>
        /* Modal and Form Fixes */
        /* Fix for inert attribute support */
        [inert] {
            pointer-events: none;
            cursor: default;
        }
        
        [inert], [inert] * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Better modal behavior */
        .modal {
            display: none;
            background: rgba(0,0,0,0.5);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s linear;
        }
        
        .modal.show {
            display: block;
            pointer-events: auto;
            opacity: 1;
        }
        
        .modal-dialog {
            pointer-events: auto;
            margin: 2rem auto;
            position: relative;
            z-index: 1060;
        }
            max-width: 500px;
            z-index: 1051;
        }
        
        .modal-content {
            background-color: #fff;
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .form-control {
            display: block !important;
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff !important;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }
        
        .input-group {
            display: flex;
            width: 100%;
        }
        
        .input-group .form-control {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
            margin-bottom: 0;
        }
        
        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-outline-secondary:hover {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .form-text {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #6c757d;
        }
        
        :root {
            --primary-color: #0F766E;
            --primary-light: #2DD4BF;
            --primary-dark: #115E59;
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            padding: 0;
            min-height: 100vh;
            overflow: auto;
            position: relative;
        
        /* Main Content */
        /* Fixed header */
        .navbar-depot {
            height: var(--header-height);
            z-index: 1030;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: calc(var(--header-height) + 1rem);
            min-height: 100vh;
            background-color: #f8f9fa;
            position: relative;
            z-index: 1;
        }
        
        /* Content wrapper for scrolling */
        .content-wrapper {
            padding: 0 1.5rem 2rem 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Ensure content doesn't overlap with fixed navbar */
        /* Body and main layout */
        body {
            padding-top: 0; /* No padding needed for navbar */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        /* Navbar styles */
        .navbar-depot {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            z-index: 1000;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0.5rem 1.5rem;
        }
        
        /* Main content area */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            padding-top: calc(var(--header-height) + 1rem);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            background-color: #f8f9fa;
            position: relative;
            z-index: 1;
        }
        
        /* Page header */
        .page-header {
            background: #fff;
            padding: 0.75rem 1.25rem;
            margin: -1.5rem -1.5rem 1rem -1.5rem;
            position: relative;
            z-index: 1;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            width: calc(100% + 3rem);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 0.75rem 1.25rem;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 56px; /* Below navbar */
            left: 0;
            width: var(--sidebar-width);
            bottom: 0;
            background: linear-gradient(180deg, #008080 0%, #006666 100%);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1001; /* Higher than main content but lower than navbar */
            overflow-y: auto;
            transition: all 0.3s ease;
            color: #fff;
            padding: 0.5rem 0;
        }
        
        .navbar-depot {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            z-index: 2000; /* Increased z-index */
        }
        
        .sidebar-link {
            padding: 0.65rem 1.5rem;
            color: #ffffff;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
            margin: 0.15rem 0.5rem;
            border-radius: 0.25rem;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar-link.text-danger {
            color: #f87171 !important;
        }
        
        .sidebar-link.text-danger:hover {
            background-color: rgba(248, 113, 113, 0.1);
        }
        
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            padding-left: 25px;
            border-left-color: #fff;
        }
        
        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.25);
            border-left-color: #fff;
            color: #fff !important;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-link i {
            width: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
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
        
        .user-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            padding: 0.5rem 0;
            min-width: 12rem;
        }
        
        .user-dropdown .dropdown-item {
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
            color: #4a5568;
            display: flex;
            align-items: center;
        }
        
        .user-dropdown .dropdown-item i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
            color: #a0aec0;
        }
        
        .user-dropdown .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }
        
        .user-dropdown .dropdown-divider {
            margin: 0.25rem 0;
            border-color: #edf2f7;
        }
        
        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        /* Responsive */
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
    <nav class="navbar navbar-expand-lg navbar-light navbar-depot fixed-top">
        <div class="container-fluid">
            <button class="btn btn-link text-dark d-lg-none me-2" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand navbar-brand-depot" href="dashboard.php">
                <i class="fas fa-bus me-2"></i>SEUSL Bus Pass Depot
            </a>
            
            <div class="d-flex align-items-center ms-auto">
                <!-- User Dropdown -->
                <div class="dropdown user-dropdown">
                    <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" 
                       id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="me-2 d-none d-sm-block">
                            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Depot User'); ?></div>
                            <small class="text-muted">Depot Manager</small>
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
                <a href="applications.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'applications.php' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i> Pass Applications
                </a>
                <a href="schedule.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bus"></i> Bus Schedule
                </a>
                <a href="students.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Students
                </a>
                <a href="reports.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </div>
            
            <div class="px-3 mb-4">
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
    <div class="main-content" style="padding-top: calc(var(--header-height) + 1.5rem);">
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
