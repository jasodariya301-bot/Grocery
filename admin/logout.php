<?php
session_start();
include '../includes/db_config.php';

// Log the logout action if admin is logged in (with error handling)
if (isset($_SESSION['admin_id'])) {
    $admin_id = (int)$_SESSION['admin_id'];
    $ip_address = mysqli_real_escape_string($con, $_SERVER['REMOTE_ADDR']);
    
    // Check if admin_logs table exists before inserting
    $table_check = mysqli_query($con, "SHOW TABLES LIKE 'admin_logs'");
    if ($table_check && mysqli_num_rows($table_check) > 0) {
        $insert_log = "INSERT INTO admin_logs (admin_id, action, ip_address) 
                       VALUES ($admin_id, 'Logout', '$ip_address')";
        @mysqli_query($con, $insert_log); // Use @ to suppress errors
    }
}

// Store message before destroying session
$logout_message = "You have been successfully logged out.";

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally destroy the session
session_destroy();

// Start a new session to set the success message
session_start();
$_SESSION['success'] = $logout_message;

// Redirect to login page
header("Location: admin_login.php");
exit();
?>