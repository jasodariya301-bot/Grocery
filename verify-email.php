<?php
session_start();
include 'includes/db_config.php';

$page_title = "Email Verification - Fresh Groceries";
$message = '';
$message_type = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = mysqli_real_escape_string($con, $_GET['token']);
    
    // Check if token exists and user is inactive
    $check_query = "SELECT id, email, first_name, status FROM register WHERE token = '$token' AND status = 'Inactive'";
    $check_result = mysqli_query($con, $check_query);
    
    if ($check_result && mysqli_num_rows($check_result) == 1) {
        $user = mysqli_fetch_assoc($check_result);
        
        // Update user status to Active and clear token
        $update_query = "UPDATE register SET status = 'Active', token = NULL WHERE id = " . $user['id'];
        
        if (mysqli_query($con, $update_query)) {
            $message = "Your email has been successfully verified! You can now login to your account.";
            $message_type = "success";
        } else {
            $message = "Error activating your account. Please try again or contact support.";
            $message_type = "danger";
        }
    } else {
        // Check if token exists but user is already active
        $check_active = "SELECT status FROM register WHERE token = '$token'";
        $active_result = mysqli_query($con, $check_active);
        
        if ($active_result && mysqli_num_rows($active_result) == 1) {
            $user_active = mysqli_fetch_assoc($active_result);
            if ($user_active['status'] == 'Active') {
                $message = "Your account is already verified. You can login now.";
                $message_type = "info";
            } else {
                $message = "Invalid or expired verification link.";
                $message_type = "danger";
            }
        } else {
            $message = "Invalid or expired verification link.";
            $message_type = "danger";
        }
    }
} else {
    $message = "No verification token provided.";
    $message_type = "danger";
}

include 'includes/header.php';
?>

<!-- Verification Result Section -->
<section class="py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5 text-center">
                        <?php if ($message_type == 'success'): ?>
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                            </div>
                            <h3 class="mb-3">Email Verified Successfully!</h3>
                        <?php elseif ($message_type == 'danger'): ?>
                            <div class="mb-4">
                                <i class="fas fa-times-circle text-danger" style="font-size: 5rem;"></i>
                            </div>
                            <h3 class="mb-3">Verification Failed</h3>
                        <?php else: ?>
                            <div class="mb-4">
                                <i class="fas fa-info-circle text-info" style="font-size: 5rem;"></i>
                            </div>
                            <h3 class="mb-3">Account Status</h3>
                        <?php endif; ?>
                        
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <?php echo $message; ?>
                        </div>
                        
                        <div class="mt-4">
                            <a href="login.php" class="btn btn-success">
                                <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>