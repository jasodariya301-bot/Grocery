<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Store the attempted URL for redirect after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Set error message
    $_SESSION['error'] = "Please login to access the admin panel";
    
    // Redirect to login page
    header("Location: admin_login.php");
    exit();
}

// Check session timeout (30 minutes of inactivity)
$timeout = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    // Session expired
    session_unset();
    session_destroy();
    
    $_SESSION['error'] = "Session expired. Please login again.";
    header("Location: admin_login.php");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Optional: Check for specific role permissions
function checkRole($allowed_roles = ['admin', 'super_admin']) {
    if (!isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], $allowed_roles)) {
        header("Location: unauthorized.php");
        exit();
    }
}

// Function to check if user is super admin
function isSuperAdmin() {
    return (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin');
}

// Function to check if user is regular admin
function isAdmin() {
    return (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin');
}

// Function to get current admin info
function getCurrentAdmin() {
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'name' => $_SESSION['admin_name'] ?? null,
        'email' => $_SESSION['admin_email'] ?? null,
        'role' => $_SESSION['admin_role'] ?? null
    ];
}
?>