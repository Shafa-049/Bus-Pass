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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        :root {
            --primary-color: #008080;  /* Teal */
            --primary-light: #00a3a3;
            --primary-dark: #006666;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --card-bg: #ffffff;
            --card-border: rgba(0, 0, 0, 0.05);
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background: linear-gradient(180deg, #008080 0%, #006666 100%);
            color: white;
            transition: all 0.3s;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            margin: 0.25rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 24px;
            margin-right: 10px;
            text-align: center;
        }
        
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .user-dropdown .dropdown-menu {
            margin-top: 10px;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
            border-radius: 0.5rem;
        }
        
        .user-dropdown .dropdown-item {
            padding: 0.5rem 1.5rem;
            transition: all 0.2s;
        }
        .user-dropdown .dropdown-item:hover {
            background-color: #f8f9fa;
            padding-left: 1.75rem;
        }
        
        .btn-primary {
            background-color: #008080;
            border-color: #008080;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: #006666;
            border-color: #006666;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            color: var(--primary-dark);
            border-top-left-radius: 0.5rem !important;
            border-top-right-radius: 0.5rem !important;
        }
        
        .main-content {
            padding: 2rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                position: fixed;
                width: 100%;
                z-index: 1000;
                bottom: 0;
                height: 60px;
            }
            
            .sidebar .nav {
                flex-direction: row;
                overflow-x: auto;
                white-space: nowrap;
                padding: 0.5rem;
            }
            
            .sidebar .nav-link {
                display: inline-flex;
                align-items: center;
                margin: 0 0.25rem;
                padding: 0.5rem 1rem;
            }
            
            .sidebar .nav-link i {
                margin-right: 0;
                font-size: 1.25rem;
            }
            
            .sidebar .nav-link span {
                display: none;
            }
            
            .main-content {
                padding: 1rem;
                margin-bottom: 60px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(90deg, #008080 0%, #006666 100%);">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <i class="fas fa-bus me-2"></i>
                <span>SEUSL Bus Pass</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="me-2 d-none d-sm-block text-end">
                                <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Student'); ?></div>
                                <small>Student</small>
                            </div>
                            <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: linear-gradient(45deg, #4facfe 0%, #00f2fe 100%);">
                                <i class="fas fa-user-graduate text-primary"></i>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky vh-100">
                    <div class="d-flex flex-column h-100">
                        <ul class="nav flex-column pt-3">
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'apply.php' ? 'active' : ''; ?>" href="apply.php">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span>Apply for Pass</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_passes.php' ? 'active' : ''; ?>" href="my_passes.php">
                                    <i class="fas fa-id-card"></i>
                                    <span>My Passes</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'active' : ''; ?>" href="schedule.php">
                                    <i class="fas fa-bus"></i>
                                    <span>Bus Schedule</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>" href="payments.php">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Payments</span>
                                </a>
                            </li>
                            <li class="nav-item mt-auto">
                                <a class="nav-link text-white-50" href="../auth/logout.php">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
