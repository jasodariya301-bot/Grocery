<?php 
// Include auth check at the top of each admin page
require_once 'auth_check.php';

$current_page = basename($_SERVER['PHP_SELF']); 
$product_pages = ['products.php', 'add-product.php', 'edit-product.php'];
$categorie_pages = ['categories.php', 'add-category.php', 'edit-category.php'];
$order_pages = ['orders.php', 'order-details.php'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BharatMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-css/admin-styles.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-shopping-basket brand-icon"></i> BharatMart</h3>
            <p class="text-white-50">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
        </div>
        <ul class="sidebar-menu">
            <li class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
                <a href="index.php">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>

            <?php if ($_SESSION['admin_role'] == 'admin'): ?>
            <li class="<?= in_array($current_page,$categorie_pages) ? 'active' : '' ?>">
                <a href="categories.php">
                    <i class="fas fa-layer-group"></i> Categories
                </a>
            </li>

            <li class="<?= in_array($current_page, $product_pages) ? 'active' : '' ?>">
                <a href="products.php">
                    <i class="fas fa-box-open"></i> Products
                </a>
            </li>

            <li class="<?= in_array($current_page, $order_pages) ? 'active' : '' ?>">
                <a href="orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
            </li>

            <li class="<?= ($current_page == 'customers.php') ? 'active' : '' ?>">
                <a href="customers.php">
                    <i class="fas fa-users"></i> Customers
                </a>
            </li>

            <li class="<?= ($current_page == 'offers.php') ? 'active' : '' ?>">
                <a href="offers.php">
                    <i class="fas fa-tags"></i> Offers & Promotions
                </a>
            </li>

            <!-- Add this after the orders section -->
            <li class="<?= ($current_page == 'contact-messages.php') ? 'active' : '' ?>">
                <a href="contact-messages.php">
                    <i class="fas fa-envelope"></i> Contact Messages
                </a>
            </li>
            <!-- Admin Management (Only for super admins) -->
            <li class="<?= ($current_page == 'manage-admins.php') ? 'active' : '' ?>">
                <a href="manage-admins.php">
                    <i class="fas fa-user-shield"></i> Manage Admins
                </a>
            </li>

            <li class="<?= ($current_page == 'settings.php') ? 'active' : '' ?>">
                <a href="settings.php">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <?php endif; ?>
        </ul>
        
        <!-- In the sidebar-footer section, update the logout link -->
    <div class="sidebar-footer">
        
        <a href="../index.php" class="btn btn-outline-light btn-sm w-100 mb-2">
            <i class="fas fa-home"></i> Back to Website
        </a>
        <a href="logout.php" class="btn btn-danger btn-sm w-100" onclick="return confirm('Are you sure you want to logout?')">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    </div>