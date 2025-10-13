<?php
// Include configuration and database connection
require_once 'config/database.php';

// Include header
$page_title = 'Home - SEUSL Bus Pass Management System';
include 'includes/header.php';
?>

<!-- Hero Section with Video Carousel -->
<style>
    .hero {
        min-height: calc(100vh - 65px) !important; /* Increased to create gap */
        height: calc(100vh - 65px) !important;    /* Increased to create gap */
        margin-top: 10px !important;             /* Added top margin for gap */
        padding-top: 0 !important;
        overflow: hidden;
        box-sizing: border-box;
        position: relative;
    }
    #heroCarousel {
        height: 100% !important;
        max-height: 100%;
    }
    .carousel-inner, 
    .carousel-item,
    .carousel-item .row,
    .carousel-item .col-lg-6 {
        height: 100% !important;
        max-height: 100%;
    }
    .carousel-item .p-5 {
        height: 100% !important;
        max-height: 100%;
        overflow-y: auto;
        padding: 1.5rem !important;
    }
    @media (max-width: 992px) {
        .hero {
            min-height: calc(100vh - 65px) !important; /* Increased to match desktop */
            height: auto !important;
            padding-top: 0 !important;
            margin-top: 0 !important;
        }
        .carousel-item .p-5 {
            max-height: 50vh;
            overflow-y: auto;
        }
    }
</style>
<section class="hero d-flex align-items-center">
    <div class="container-fluid p-0 h-100">
        <div id="heroCarousel" class="carousel slide h-100 rounded-top-4 overflow-hidden" data-bs-ride="carousel" data-bs-interval="3000" data-bs-pause="false" style="box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); height: 100%;">
            <div class="carousel-inner h-100 rounded-top-4">
                <!-- Slide 1 -->
                <div class="carousel-item active h-100">
                    <div class="row g-0 h-100">
                        <!-- Text Column -->
                        <div class="col-lg-6 d-flex align-items-center position-relative p-4" style="background: linear-gradient(135deg, #f0fdfa 0%, #e0f8f5 100%);">
                            <div class="p-5 w-100 position-relative" style="z-index: 2;">
                                <div class="d-flex align-items-center mb-5">
                                    <div class="p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); box-shadow: 0 5px 15px rgba(13, 148, 136, 0.2);">
                                        <i class="fas fa-bus text-white" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <span class="text-uppercase small fw-semibold letter-spacing-1 d-block" style="color: var(--primary-color); font-size: 0.7rem; margin-bottom: 2px;">Premium Experience</span>
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex">
                                                <i class="fas fa-star text-warning" style="font-size: 0.7rem; margin-right: 2px;"></i>
                                                <i class="fas fa-star text-warning" style="font-size: 0.7rem; margin-right: 2px;"></i>
                                                <i class="fas fa-star text-warning" style="font-size: 0.7rem; margin-right: 2px;"></i>
                                                <i class="fas fa-star text-warning" style="font-size: 0.7rem; margin-right: 2px;"></i>
                                                <i class="fas fa-star text-warning" style="font-size: 0.7rem;"></i>
                                            </div>
                                            <span class="ms-2 small text-muted">5.0 Rating</span>
                                        </div>
                                    </div>
                                </div>
                                <h2 class="display-4 fw-bold mb-4"><span class="text-gradient">Seamless Bus Pass</span> <br><span style="color: var(--primary-dark);">Management</span></h2>
                                <p class="lead mb-4">Experience the convenience of managing your bus pass online. Apply, renew, and track your pass with our easy-to-use platform.</p>
                                <div class="d-flex flex-wrap gap-3 mt-5 pt-2">
                                    <a href="/bus_pass_seusl/auth/register.php" class="btn btn-primary btn-lg px-4 shadow-sm position-relative overflow-hidden" style="background: var(--primary-color); border: none; min-width: 160px; transition: all 0.3s ease;">
                                        <span class="position-relative z-1">
                                            <i class="fas fa-user-plus me-2"></i>Get Started
                                        </span>
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-white opacity-10" style="transform: translateX(-100%) skewX(-15deg); transition: all 0.5s ease;"></span>
                                    </a>
                                    <a href="#routes" class="btn btn-outline-primary btn-lg px-4 position-relative overflow-hidden" style="border: 2px solid var(--primary-color); color: var(--primary-color); background: transparent; min-width: 160px; transition: all 0.3s ease;">
                                        <span class="position-relative z-1">
                                            <i class="fas fa-route me-2"></i>View Routes
                                        </span>
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-primary opacity-0" style="transition: all 0.3s ease; z-index: 0;"></span>
                                    </a>
                                </div>
                                <div class="position-absolute top-0 end-0 me-4 mt-4">
                                    <span class="badge p-2" style="background: rgba(255, 255, 255, 0.9); color: var(--primary-color); border: 1px solid var(--border-color); box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                        <i class="fas fa-crown me-1" style="color: #ffc107;"></i> Premium Feature
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- Video Column -->
                        <div class="col-lg-6 h-100">
                            <div class="video-container h-100" style="min-height: 400px;">
                                <video class="h-100 w-100 object-fit-cover" autoplay muted loop playsinline>
                                    <source src="/bus_pass_seusl/assets/videos/buss pas_.mp4" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="carousel-item h-100">
                    <div class="row g-0 h-100">
                        <!-- Text Column -->
                        <div class="col-lg-6 d-flex align-items-center" style="background: linear-gradient(135deg, #f0fdfa 0%, #e0f8f5 100%);">
                            <div class="p-5">
                                <div class="d-flex align-items-center mb-5">
                                    <div class="p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); box-shadow: 0 5px 15px rgba(13, 148, 136, 0.2);">
                                        <i class="fas fa-mobile-alt text-white" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <span class="text-uppercase small fw-semibold letter-spacing-1 d-block" style="color: var(--primary-color); font-size: 0.7rem; margin-bottom: 2px;">Digital Experience</span>
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex">
                                                <i class="fas fa-check-circle me-2" style="color: var(--primary-color);"></i>
                                                <span class="small">100% Digital</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h2 class="display-5 fw-bold mb-4">Easy Boarding Process</h2>
                                <p class="lead">Quick and hassle-free boarding with your digital bus pass. No more paper passes to worry about.</p>
                                <ul class="list-unstyled mt-4">
                                    <li class="mb-3 d-flex align-items-center">
                                        <div class="me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px; background: var(--bg-light); border: 1px solid var(--border-color);">
                                            <i class="fas fa-check" style="font-size: 0.7rem; color: var(--primary-color);"></i>
                                        </div>
                                        <span>Instant pass activation</span>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <div class="me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px; background: var(--bg-light); border: 1px solid var(--border-color);">
                                            <i class="fas fa-check" style="font-size: 0.7rem; color: var(--primary-color);"></i>
                                        </div>
                                        <span>Digital pass on your phone</span>
                                    </li>
                                    <li class="mb-4 d-flex align-items-center">
                                        <div class="me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px; background: var(--bg-light); border: 1px solid var(--border-color);">
                                            <i class="fas fa-check" style="font-size: 0.7rem; color: var(--primary-color);"></i>
                                        </div>
                                        <span>Contactless verification</span>
                                    </li>
                                </ul>
                                <a href="#how-it-works" class="btn btn-primary btn-lg px-4" style="background: var(--primary-color); border: none; color: white; transition: all 0.3s ease;">
                                    <i class="fas fa-info-circle me-2"></i>Learn More
                                </a>
                            </div>
                        </div>
                        <!-- Video Column -->
                        <div class="col-lg-6 h-100">
                            <div class="video-container h-100">
                                <video class="h-100 w-100" autoplay muted loop playsinline>
                                    <source src="/bus_pass_seusl/assets/videos/getin.mp4" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="carousel-item h-100">
                    <div class="row g-0 h-100">
                        <!-- Text Column -->
                        <div class="col-lg-6 d-flex align-items-center" style="background: linear-gradient(135deg, #f0fdfa 0%, #e0f8f5 100%);">
                            <div class="p-5">
                                <div class="d-flex align-items-center mb-5">
                                    <div class="p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); box-shadow: 0 5px 15px rgba(13, 148, 136, 0.2);">
                                        <i class="fas fa-users text-white" style="font-size: 1.25rem;"></i>
                                    </div>
                                    <div>
                                        <span class="text-uppercase small fw-semibold letter-spacing-1 d-block" style="color: var(--primary-color); font-size: 0.7rem; margin-bottom: 2px;">Our Community</span>
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex">
                                                <i class="fas fa-user-check me-2" style="color: var(--primary-color);"></i>
                                                <span class="small">5,000+ Active Users</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h2 class="display-5 fw-bold mb-4">Join Our Community</h2>
                                <p class="lead">Thousands of students trust our bus pass system for their daily commute. Be part of our growing community.</p>
                                <div class="d-flex align-items-center mt-5">
                                    <div class="avatar-group me-4">
                                        <div class="avatar-wrapper" style="position: relative; width: 50px; height: 50px;">
                                            <img src="https://randomuser.me/api/portraits/women/32.jpg" class="avatar" alt="Student 1" style="width: 50px; height: 50px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                            <div class="position-absolute bottom-0 end-0 rounded-circle" style="width: 12px; height: 12px; border: 2px solid white; background: var(--primary-color);"></div>
                                        </div>
                                        <div class="avatar-wrapper" style="position: relative; width: 50px; height: 50px; margin-left: -10px;">
                                            <img src="https://randomuser.me/api/portraits/men/44.jpg" class="avatar" alt="Student 2" style="width: 50px; height: 50px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                            <div class="position-absolute bottom-0 end-0 rounded-circle" style="width: 12px; height: 12px; border: 2px solid white; background: var(--primary-color);"></div>
                                        </div>
                                        <div class="avatar-wrapper" style="position: relative; width: 50px; height: 50px; margin-left: -10px;">
                                            <img src="https://randomuser.me/api/portraits/women/68.jpg" class="avatar" alt="Student 3" style="width: 50px; height: 50px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                            <div class="position-absolute bottom-0 end-0 rounded-circle" style="width: 12px; height: 12px; border: 2px solid white; background: var(--primary-color);"></div>
                                        </div>
                                        <div class="avatar-wrapper d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; margin-left: -10px; background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)); border-radius: 50%; color: white; font-weight: 600; font-size: 0.8rem; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                            5K+
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-star me-1" style="color: var(--primary-color);"></i>
                                            <span class="fw-semibold me-2">4.9</span>
                                            <span class="text-muted small">(2,500+ reviews)</span>
                                        </div>
                                        <div class="small text-muted mt-1">Trusted by students</div>
                                    </div>
                                </div>
                                <a href="/bus_pass_seusl/auth/register.php" class="btn btn-primary btn-lg px-4" style="background: var(--primary-color); border: none; transition: all 0.3s ease;">
                                    <i class="fas fa-user-plus me-2"></i>Join Now
                                </a>
                            </div>
                        </div>
                        <!-- Video Column -->
                        <div class="col-lg-6 h-100">
                            <div class="video-container h-100">
                                <video class="h-100 w-100" autoplay muted loop playsinline>
                                    <source src="/bus_pass_seusl/assets/videos/girl&boy.mp4" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Carousel Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-dark bg-opacity-50 p-3" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-dark bg-opacity-50 p-3" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            
            <!-- Carousel Indicators -->
            <div class="carousel-indicators position-absolute bottom-0 mb-4">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
        </div>
    </div>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Smooth scrolling and section alignment */
        html {
            scroll-behavior: smooth;
            scroll-snap-type: y mandatory;
            scroll-padding-top: 80px; /* Height of fixed header */
        }
        
        /* Ensure sections take full viewport height and snap into place */
        #about, #routes, #contact {
            scroll-snap-align: start;
            scroll-margin-top: 80px;
        }
        
        /* Enhanced Route Card Hover Effect */
        .route-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .route-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(13, 148, 136, 0.05) 0%, rgba(13, 148, 136, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }
        
        .route-card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2) !important;
            border-color: var(--primary-color) !important;
        }
        
        .route-card:hover::before {
            opacity: 1;
        }
        
        .route-card .card-body {
            transition: all 0.4s ease;
            position: relative;
        }
        
        .route-card:hover .card-body {
            background-color: #f8f9fa;
        }
        
        .route-card .fa-route,
        .route-card .fa-clock {
            transition: all 0.4s ease;
        }
        
        .route-card:hover .fa-route,
        .route-card:hover .fa-clock {
            transform: scale(1.1);
            color: var(--primary-dark) !important;
        }
        
        .route-card .card-title {
            position: relative;
            display: inline-block;
        }
        
        .route-card .card-title::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary-color);
            transition: width 0.4s ease;
        }
        
        .route-card:hover .card-title::after {
            width: 100%;
        }
        
        /* Global Background */
        body {
            background-color: #f0fdfa !important;
        }
        
        /* Override all section backgrounds */
        section, 
        .bg-light, 
        .bg-white,
        .hero,
        .navbar {
            background-color: #f0fdfa !important;
        }
        
        /* Cards should stay white for contrast */
        .card {
            background-color: #ffffff !important;
        }
        
        /* Navigation bar should be white */
        .navbar {
            background-color: #ffffff !important;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        /* Footer styling */
        footer {
            background-color: var(--primary-dark) !important;
            color: white;
        }
        /* Smooth scrolling for anchor links */
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 100px; /* Increased padding to account for fixed header */
        }
        
        /* Adjust scroll position for sections */
        #about:target::before,
        #routes:target::before {
            content: '';
            display: block;
            height: 80px; /* Height of fixed header */
            margin-top: -80px; /* Negative margin to pull up */
            visibility: hidden;
            pointer-events: none;
        }
        
        /* Smooth scroll behavior for all sections */
        [id] {
            scroll-margin-top: 80px;
        }
        
        /* Custom Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .transition-all {
            transition: all 0.3s ease;
        }
        
        .transition-all:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(13, 148, 136, 0.1) !important;
        }
        
        /* Reset body and html to remove default margins */
        :root {
            --primary-font: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            --primary-color: #0d9488;      /* Teal-600 */
            --primary-light: #5eead4;     /* Teal-300 */
            --primary-dark: #0f766e;      /* Teal-700 */
            --text-dark: #1e293b;         /* Slate-800 */
            --text-muted: #64748b;        /* Slate-500 */
            --accent-color: #0f766e;      /* Teal-700 */
            --gradient-start: #0d9488;    /* Teal-600 */
            --gradient-end: #0f766e;      /* Teal-700 */
            --bg-light: #f8fafc;          /* Slate-50 */
            --border-color: #e2e8f0;      /* Slate-200 */
        }
        
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            font-family: var(--primary-font);
            color: var(--text-dark);
        }
        
        .video-container {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 400px;
            overflow: hidden;
        }
        
        @media (max-width: 992px) {
            .hero {
                min-height: 60vh !important;
                margin-top: 60px !important;
            }
            .carousel-item .col-lg-6:first-child {
                min-height: 50%;
            }
            .video-container {
                min-height: 300px !important;
            }
        }
        
        @media (max-width: 768px) {
            .hero {
                min-height: 50vh !important;
                margin-top: 60px !important;
            }
            .carousel-item .col-lg-6:first-child {
                min-height: 40%;
                padding: 1.5rem !important;
            }
            .video-container {
                min-height: 250px !important;
            }
        }
        
        /* Hero Section Styles */
        .carousel-inner {
            border-radius: 0;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
        }
        
        .carousel-item {
            transition: transform 0.6s ease-in-out;
        }
        
        .carousel {
            overflow: hidden;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }
        
        .hero {
            color: #333;
            overflow: hidden;
            position: relative;
            height: 80vh;
            min-height: 600px;
            margin: 0;
            padding: 15px;
            margin-bottom: 2rem;
        }
        
        /* Video Container */
        .video-container {
            position: relative;
            overflow: hidden;
            background: #000;
            height: 100%;
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }
        
        .video-container video {
            object-fit: cover;
            object-position: center;
        }
        
        /* Text Content */
        .carousel-item .col-lg-6:last-child {
            padding: 0;
        }
        
        /* Ensure text content has proper spacing */
        .carousel-item .col-lg-6:first-child {
            padding: 2rem !important;
            height: 100%;
            overflow: hidden;
            background: linear-gradient(145deg, var(--bg-light) 0%, #f1f5f9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
            position: relative;
            box-shadow: 8px 0 25px rgba(100, 116, 139, 0.08);
            font-weight: 400;
            border-right: 1px solid var(--border-color);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
        }
        
        .carousel-item .col-lg-6:first-child::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            border-top-left-radius: 20px;
        }
        
        .carousel-item .p-5 {
            height: 100%;
            overflow-y: hidden;
            overflow-x: hidden;
            padding: 1.5rem !important;
            position: relative;
            display: flex;
            flex-direction: column;
            margin: 0;
        }
        
        .carousel-item h2 {
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.3;
            margin-bottom: 1.5rem;
            font-size: 2.5rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .carousel-item h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            border-radius: 3px;
        }
        
        .carousel-item p.lead {
            color: var(--text-muted);
            font-weight: 400;
            line-height: 1.8;
            font-size: 1.15rem;
            margin: 2rem 0;
            max-width: 90%;
        }
        
        /* Make sure the inner content doesn't cause overflow */
        /* Removed duplicate .p-5 rule */
        
        .carousel-item h2 {
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.3;
            margin-bottom: 1rem;
            font-size: 2rem;
        }
        
        .carousel-item p.lead {
            color: var(--text-muted);
            font-weight: 400;
            line-height: 1.6;
            font-size: 1rem;
            margin: 0.5rem 0 1.5rem;
            flex-grow: 1;
        }
        
        .carousel-item ul.list-unstyled {
            margin: 0.5rem 0 1rem !important;
            padding: 0 !important;
        }
        
        .text-gradient {
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        
        .letter-spacing-1 {
            letter-spacing: 1px;
        }
        
        .bg-gradient-light {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }
        
        /* Carousel Controls */
        .carousel-control-prev,
        .carousel-control-next {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.8;
            transition: all 0.3s ease;
            background-color: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .carousel-control-prev {
            left: 20px;
        }
        
        .carousel-control-next {
            right: 20px;
        }
        
        /* Carousel Indicators */
        .carousel-indicators {
            bottom: 10px;
            margin: 0;
            padding: 0 15px;
            justify-content: center;
        }
        
        .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 8px;
            background-color: rgba(0, 0, 0, 0.5);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .carousel-indicators button.active {
            background-color: var(--primary-color);
            transform: scale(1.2);
            border-color: white;
        }
        
        /* Avatar Group */
        .avatar-group {
            display: flex;
            align-items: center;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid white;
            margin-left: -10px;
            background: #f8f9fa;
            object-fit: cover;
        }
        
        .avatar:first-child {
            margin-left: 0;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 991.98px) {
            .hero {
                height: auto !important;
            }
            
            .carousel-item .row > div {
                height: 400px !important;
            }
            
            .carousel-item .col-lg-6:last-child {
                padding: 3rem 1.5rem;
            }
        }
        
        @media (max-width: 767.98px) {
            .carousel-item .row > div {
                height: 350px !important;
            }
            
            .display-5 {
                font-size: 2rem;
            }
            
            .btn-lg {
                padding: 0.5rem 1.25rem;
                font-size: 1rem;
            }
        }
    </style>
</section>

<!-- Main Content -->
<main>
    <!-- About Section -->
    <section id="about" class="py-5" style="padding: 100px 0; min-height: 100vh; display: flex; align-items: center; position: relative; z-index: 1; scroll-margin-top: 80px; scroll-snap-align: start;">
        <div class="container" style="position: relative;">
            <div class="text-center mb-3" style="padding-top: 20px;">
                <h2 class="fw-bold" style="color: var(--primary-dark); font-size: 1.75rem; position: relative; padding-bottom: 10px; margin-bottom: 1.5rem;">
                    <span style="position: relative; z-index: 1; background: #e0f2f1; padding: 0 20px; border-radius: 15px;">About Our Service</span>
                    <span style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 60px; height: 2px; background: var(--primary-color); border-radius: 2px;"></span>
                </h2>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 rounded-3 overflow-hidden shadow-sm bg-white" style="transition: all 0.3s ease; border-top: 4px solid var(--primary-color) !important;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-bus" style="color: var(--primary-color); font-size: 1.5rem;"></i>
                                </div>
                                <h4 class="mb-0 fw-bold" style="color: var(--primary-dark);">Convenient Travel</h4>
                            </div>
                            <p class="text-muted mb-4" style="line-height: 1.7;">Hassle-free access to all SEUSL bus routes with your digital pass. No more waiting in lines or carrying physical passes.</p>
                            <a href="#routes" class="btn btn-sm px-4 py-2" style="background: var(--primary-color); color: white; border-radius: 30px; font-weight: 500; transition: all 0.3s ease; border: none;">
                                View Routes <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 rounded-3 overflow-hidden shadow-sm bg-white" style="transition: all 0.3s ease; border-top: 4px solid var(--primary-color) !important;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-mobile-alt" style="color: var(--primary-color); font-size: 1.5rem;"></i>
                                </div>
                                <h4 class="mb-0 fw-bold" style="color: var(--primary-dark);">Digital Management</h4>
                            </div>
                            <p class="text-muted mb-4" style="line-height: 1.7;">Manage your bus pass online anytime. Apply, renew, or check status with just a few clicks from your device.</p>
                            <a href="/bus_pass_seusl/auth/register.php" class="btn btn-sm px-4 py-2" style="background: var(--primary-color); color: white; border-radius: 30px; font-weight: 500; transition: all 0.3s ease; border: none;">
                                Get Started <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mx-auto">
                    <div class="card h-100 border-0 rounded-3 overflow-hidden shadow-sm bg-white" style="transition: all 0.3s ease; border-top: 4px solid var(--primary-color) !important;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-shield-alt" style="color: var(--primary-color); font-size: 1.5rem;"></i>
                                </div>
                                <h4 class="mb-0 fw-bold" style="color: var(--primary-dark);">Secure & Reliable</h4>
                            </div>
                            <p class="text-muted mb-4" style="line-height: 1.7;">Your data security is our priority. We use advanced encryption to protect your personal information.</p>
                            <a href="#contact" class="btn btn-sm px-4 py-2" style="background: var(--primary-color); color: white; border-radius: 30px; font-weight: 500; transition: all 0.3s ease; border: none;">
                                Learn More <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Routes Section -->
    <section id="routes" class="py-5" style="background-color: #f8f9fa; min-height: 100vh; display: flex; align-items: center; position: relative; scroll-snap-align: start;">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="fw-bold" style="color: var(--primary-dark); font-size: 1.75rem; position: relative; padding-bottom: 10px; margin-bottom: 1.5rem;">
                    <span style="position: relative; z-index: 1; background: #e0f2f1; padding: 0 20px; border-radius: 15px;">Available Routes & Fares</span>
                    <span style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 60px; height: 2px; background: var(--primary-color); border-radius: 2px;"></span>
                </h2>
            </div>
            <div class="row g-4">
                <?php
                // Sample route data - In a real application, this would come from the database
                $routes = [
                    ['from' => 'Akkaraipattu', 'to' => 'Oluvil', 'distance' => '10 km', 'time' => '12 min', 'fare' => 'LKR 50'],
                    ['from' => 'Addalaichenai', 'to' => 'Oluvil', 'distance' => '7 km', 'time' => '10 min', 'fare' => 'LKR 40'],
                    ['from' => 'Palamunai', 'to' => 'Oluvil', 'distance' => '7 km', 'time' => '10 min', 'fare' => 'LKR 40'],
                    ['from' => 'Nintavur', 'to' => 'Oluvil', 'distance' => '9 km', 'time' => '11 min', 'fare' => 'LKR 45'],
                    ['from' => 'Sammanthurai', 'to' => 'Oluvil', 'distance' => '16.5 km', 'time' => '21 min', 'fare' => 'LKR 60'],
                    ['from' => 'Karaitivu', 'to' => 'Oluvil', 'distance' => '12.7 km', 'time' => '15 min', 'fare' => 'LKR 55'],
                    ['from' => 'Sainthamaruthu', 'to' => 'Oluvil', 'distance' => '12 km', 'time' => '14 min', 'fare' => 'LKR 50'],
                    ['from' => 'Kalmunai', 'to' => 'Oluvil', 'distance' => '17 km', 'time' => '21 min', 'fare' => 'LKR 65'],
                    ['from' => 'Maruthamunai', 'to' => 'Oluvil', 'distance' => '18.6 km', 'time' => '24 min', 'fare' => 'LKR 70']
                ];

                foreach ($routes as $route) {
                    echo '<div class="col-12 col-sm-6 col-lg-4 mb-4">';
                    echo '  <div class="card h-100 border-0 rounded-3 overflow-hidden shadow-sm route-card" ';
                    echo '       style="transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); ';
                    echo '              border-top: 4px solid var(--primary-color) !important;';
                    echo '              cursor: pointer;';
                    echo '              background: white;';
                    echo '              cursor: pointer;';
                    echo '              transform: translateY(0);"';
                    echo '       onclick="window.location.href=\'/bus_pass_seusl/auth/login.php\'">';
                    
                    // Card header with icon
                    echo '      <div class="card-body p-4">';
                    echo '          <div class="d-flex align-items-center mb-4">';
                    echo '              <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3" ';
                    echo '                   style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">';
                    echo '                  <i class="fas fa-route" style="color: var(--primary-color); font-size: 1.5rem;"></i>';
                    echo '              </div>';
                    echo '              <h5 class="card-title mb-0" style="color: var(--primary-dark);">' . htmlspecialchars($route['from']) . ' to ' . htmlspecialchars($route['to']) . '</h5>';
                    echo '          </div>'; // End card header
                    
                    // Route details
                    echo '          <div class="mb-4">';
                    
                    // Distance and time with themed icons
                    echo '              <div class="d-flex justify-content-between mb-3">';
                    echo '                  <div class="d-flex align-items-center">';
                    echo '                      <div class="me-2" style="width: 32px; height: 32px; background: rgba(13, 148, 136, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">';
                    echo '                          <i class="fas fa-route" style="color: var(--primary-color); font-size: 0.9rem;"></i>';
                    echo '                      </div>';
                    echo '                      <div>';
                    echo '                          <div class="small text-muted">Distance</div>';
                    echo '                          <div class="fw-medium" style="color: var(--primary-dark);">' . htmlspecialchars($route['distance']) . '</div>';
                    echo '                      </div>';
                    echo '                  </div>';
                    echo '                  <div class="d-flex align-items-center">';
                    echo '                      <div class="me-2" style="width: 32px; height: 32px; background: rgba(13, 148, 136, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">';
                    echo '                          <i class="far fa-clock" style="color: var(--primary-color); font-size: 0.9rem;"></i>';
                    echo '                      </div>';
                    echo '                      <div>';
                    echo '                          <div class="small text-muted">Duration</div>';
                    echo '                          <div class="fw-medium" style="color: var(--primary-dark);">' . htmlspecialchars($route['time']) . '</div>';
                    echo '                      </div>';
                    echo '                  </div>';
                    echo '              </div>';
                    
                    echo '          </div>'; // End card details
                    echo '      </div>'; // End card body
                    echo '  </div>'; // End card
                    echo '</div>'; // End col
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5" style="background-color: #f8f9fa; min-height: 100vh; display: flex; align-items: center; position: relative; z-index: 1; scroll-snap-align: start;">
        <div class="container" style="position: relative; top: -50px;">
            <div class="text-center mb-3">
                <h2 class="fw-bold" style="color: var(--primary-dark); font-size: 1.5rem; position: relative; padding-bottom: 8px; margin-bottom: 1rem;">
                    <span style="position: relative; z-index: 1; background: #e0f2f1; padding: 0 15px; border-radius: 10px;">Contact Us</span>
                    <span style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 40px; height: 2px; background: var(--primary-color); border-radius: 2px;"></span>
                </h2>
            </div>
            
            <!-- Contact Info Cards -->
            <div class="row g-2 mb-3">
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm p-2" style="border-radius: 8px; transition: all 0.3s ease; font-size: 0.9rem;">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="fas fa-map-marker-alt" style="color: var(--primary-color); font-size: 1.1rem;"></i>
                            </div>
                            <div>
                                <h6 class="mb-2" style="color: var(--primary-dark); font-size: 0.95rem;">Our Location</h6>
                                <p class="mb-0 text-muted small">SEUSL, Oluvil, Sri Lanka</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm p-2" style="border-radius: 8px; transition: all 0.3s ease; font-size: 0.9rem;">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="far fa-clock" style="color: var(--primary-color); font-size: 1.1rem;"></i>
                            </div>
                            <div>
                                <h6 class="mb-2" style="color: var(--primary-dark); font-size: 0.95rem;">Working Hours</h6>
                                <p class="mb-0 text-muted small">Mon-Fri: 8:30 AM - 4:30 PM<br>Sat: 8:30 AM - 12:30 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm p-2" style="border-radius: 8px; transition: all 0.3s ease; font-size: 0.9rem;">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="fas fa-phone-alt" style="color: var(--primary-color); font-size: 1.1rem;"></i>
                            </div>
                            <div>
                                <h6 class="mb-2" style="color: var(--primary-dark); font-size: 0.95rem;">Phone</h6>
                                <p class="mb-0 text-muted small">+94 67 226 0000<br>+94 67 226 0001</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm p-2" style="border-radius: 8px; transition: all 0.3s ease; font-size: 0.9rem;">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="far fa-envelope" style="color: var(--primary-color); font-size: 1.1rem;"></i>
                            </div>
                            <div>
                                <h6 class="mb-2" style="color: var(--primary-dark); font-size: 0.95rem;">Email</h6>
                                <p class="mb-0 text-muted small">transport@seusl.lk</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form and Map -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-body p-4">
                            <h5 class="mb-2" style="color: var(--primary-dark); font-size: 1rem; font-weight: 600;">Send us a Message</h5>
                            <form id="contactForm">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-group mb-1">
                                            <label for="name" class="form-label small text-muted mb-0">Your Name</label>
                                            <input type="text" class="form-control form-control-sm py-1" id="name" placeholder="John Doe" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-1">
                                            <label for="email" class="form-label small text-muted mb-0">Email</label>
                                            <input type="email" class="form-control form-control-sm py-1" id="email" placeholder="your@email.com" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-1">
                                            <label for="subject" class="form-label small text-muted mb-0">Subject</label>
                                            <input type="text" class="form-control form-control-sm py-1" id="subject" placeholder="How can we help you?">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-2">
                                            <label for="message" class="form-label small text-muted mb-0">Message</label>
                                            <textarea class="form-control form-control-sm" id="message" rows="2" style="min-height: 50px;" placeholder="Your message..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-sm px-3 py-1" style="background: var(--primary-color); border: none; border-radius: 4px; font-weight: 500;">
                                            <i class="fas fa-paper-plane me-1"></i>Send
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-body p-0 h-100">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3959.527621403473!2d81.84783831477433!3d7.02948299490512!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3afca5a6ff3b6e17%3A0x5d2c6f5b5d1f3f1f!2sSouth%20Eastern%20University%20of%20Sri%20Lanka!5e0!3m2!1sen!2slk!4v1620000000000!5m2!1sen!2slk" 
                                width="100%" 
                                height="100%" 
                                style="min-height: 250px; border: 0;" 
                                allowfullscreen="" 
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
// Include footer
include 'includes/footer.php';
?>
