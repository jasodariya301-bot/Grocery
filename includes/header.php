<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'BharatMart - Fresh Groceries Delivered'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Custom CSS for navbar -->
    <style>
    .navbar-custom {
        background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
        padding: 15px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .navbar-brand {
        font-size: 24px;
        font-weight: 700;
        color: white !important;
    }
    .brand-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .brand-icon {
        font-size: 28px;
        color: #ffc107;
    }
    .brand-text {
        background: linear-gradient(45deg, #fff, #ffc107);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .nav-link {
        color: white !important;
        font-weight: 500;
        padding: 8px 16px !important;
        margin: 0 5px;
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    .nav-link:hover {
        background-color: rgba(255,255,255,0.2);
        transform: translateY(-2px);
    }
    .nav-link i {
        margin-right: 5px;
    }
    .cart-link {
        position: relative;
    }
    .cart-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ffc107;
        color: #1b5e20;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
        font-weight: bold;
    }
    .user-dropdown .dropdown-toggle::after {
        color: white;
    }
    .dropdown-menu {
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        border-radius: 10px;
        margin-top: 10px;
    }
    .dropdown-item {
        padding: 10px 20px;
        font-weight: 500;
    }
    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #2e7d32;
    }
    .dropdown-item i {
        width: 20px;
        color: #2e7d32;
    }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <div class="brand-content">
                    <i class="fas fa-shopping-basket brand-icon"></i>
                    <span class="brand-text">BharatMart</span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-shopping-bag me-1"></i>Products
                        </a>
                    </li>
                   
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                    
                    <?php if ($logged_in): ?>
                        <!-- User Dropdown for Logged In Users -->
                       <li class="nav-item">
                                <a class="nav-link cart-link" href="cart.php">
                                    <i class="fas fa-shopping-cart me-1"></i>Cart 
                                    <span class="badge bg-warning text-dark cart-count">
                                        <?php 
                                        // Check if connection exists, if not include db_config
                                        if (!isset($con)) {
                                            include_once 'includes/db_config.php';
                                        }
                                        
                                        // Only include cart_functions if not already included
                                        if (!function_exists('getCartCount')) {
                                            include_once 'includes/cart_functions.php';
                                        }
                                        
                                        // Get cart count safely
                                        if (isset($_SESSION['user_id']) && function_exists('getCartCount')) {
                                            echo getCartCount($_SESSION['user_id']);
                                        } else {
                                            echo '0';
                                        }
                                        ?>
                                    </span>
                                </a>
                            </li>
                                                <li class="nav-item dropdown user-dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="fas fa-user me-2"></i>My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="My-orders.php">
                                        <i class="fas fa-shopping-bag me-2"></i>My Orders
                                    </a>
                                </li>
                               
                               
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Login/Register for Guests -->
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Display logout success message if set -->
    <?php if (isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>You have been successfully logged out.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
  <!-- <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="login.php"><i class="fas fa-sign-in-alt me-2"></i>Login</a></li>
                            <li><a class="dropdown-item" href="account.php"><i class="fas fa-user me-2"></i>My Account</a></li>
                            
                        </ul> -->