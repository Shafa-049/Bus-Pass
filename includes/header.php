<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEUSL Bus Pass Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0F766E;
            --primary-light: #2DD4BF;
            --primary-dark: #115E59;
            --secondary-color: #FACC15;
            --accent-color: #DC2626;
            --bg-color: #F8FAFC;
            --text-color: #1E293B;
            --card-bg: #FFFFFF;
            --pass-bg: #CCFBF1;
            --card-border: rgba(0,0,0,0.05);
            --text-muted: #64748B;
        }
        body {
            font-family: 'Poppins', sans-serif;
            padding-top: 55px; /* Slightly increased for a small gap */
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
        }
        .navbar {
            background-color: #008080 !important;
            background: #008080 !important;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 0.5rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }
        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
            color: white !important;
            display: flex;
            align-items: center;
        }
        .navbar-brand i {
            margin-right: 10px;
            font-size: 1.5rem;
        }
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: white !important;
            background-color: rgba(255,255,255,0.15);
        }
        .btn-outline-light {
            border-color: white;
            color: white;
            font-weight: 500;
        }
        .btn-light {
            background-color: white;
            color: var(--primary-color);
            font-weight: 600;
            border: none;
        }
        .btn-light:hover {
            background-color: #f8f9fa;
            color: var(--primary-dark);
        }
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background-color: var(--primary-dark);
                padding: 1rem;
                border-radius: 0.5rem;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: #008080 !important; background: #008080 !important;">
        <div class="container">
            <a class="navbar-brand" href="/bus_pass_seusl/index.php">
                <i class="fas fa-bus"></i> SEUSL Bus Pass
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php
                // Get the current URL and hash
                $current_url = $_SERVER['REQUEST_URI'];
                $current_hash = isset($_GET['#']) ? '#' . $_GET['#'] : (strpos($current_url, '#') !== false ? '#' . explode('#', $current_url)[1] : '');
                $is_index = basename($_SERVER['PHP_SELF']) == 'index.php';
                ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($is_index && empty($current_hash)) ? 'active' : ''; ?>" 
                           href="/bus_pass_seusl/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($is_index && ($current_hash == '#about' || $current_hash == '#')) ? 'active' : ''; ?>" 
                           href="/bus_pass_seusl/index.php#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($is_index && $current_hash == '#routes') ? 'active' : ''; ?>" 
                           href="/bus_pass_seusl/index.php#routes">Routes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($is_index && $current_hash == '#contact') ? 'active' : ''; ?>" 
                           href="/bus_pass_seusl/index.php#contact">Contact</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/bus_pass_seusl/student/dashboard.php" class="btn btn-outline-light me-2">Dashboard</a>
                        <a href="/bus_pass_seusl/auth/logout.php" class="btn btn-light">Logout</a>
                    <?php else: ?>
                        <a href="/bus_pass_seusl/auth/login.php" class="btn btn-outline-light me-2">Login</a>
                        <a href="/bus_pass_seusl/auth/register.php" class="btn btn-light">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <script>
    // Update active state on page load and hash change
    document.addEventListener('DOMContentLoaded', updateActiveNav);
    window.addEventListener('hashchange', updateActiveNav);
    
    function updateActiveNav() {
        // Remove active class from all nav links
        document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Get current hash
        const hash = window.location.hash || '#';
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        
        // Find and activate the correct nav link
        navLinks.forEach(link => {
            if (hash === '#' && link.getAttribute('href') === '/bus_pass_seusl/index.php') {
                link.classList.add('active');
            } else if (hash !== '#' && link.getAttribute('href').endsWith(hash)) {
                link.classList.add('active');
            }
        });
    }
    </script>
    <div class="container" style="padding-top: 20px;">
