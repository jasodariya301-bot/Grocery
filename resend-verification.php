<?php
session_start();
include 'includes/db_config.php';
include 'includes/mail_config.php';

$message = '';
$message_type = '';

if (isset($_GET['email']) && !empty($_GET['email'])) {
    $email = mysqli_real_escape_string($con, $_GET['email']);
    
    // Check if user exists and is inactive
    $check_query = "SELECT id, first_name, email, token, status FROM register WHERE email = '$email' AND status = 'Inactive'";
    $check_result = mysqli_query($con, $check_query);
    
    if (mysqli_num_rows($check_result) == 1) {
        $user = mysqli_fetch_assoc($check_result);
        
        // Generate new token
        $new_token = md5(uniqid() . $email . time());
        
        // Update token in database
        $update_query = "UPDATE register SET token = '$new_token' WHERE id = " . $user['id'];
        
        if (mysqli_query($con, $update_query)) {
            // Send verification email
            if (sendVerificationEmail($email, $user['first_name'], $new_token)) {
                $message = "Verification email has been resent successfully. Please check your inbox.";
                $message_type = "success";
            } else {
                $message = "Failed to send verification email. Please try again later.";
                $message_type = "danger";
            }
        } else {
            $message = "Error processing your request. Please try again.";
            $message_type = "danger";
        }
    } else {
        $message = "Email not found or account already verified.";
        $message_type = "danger";
    }
}

include 'includes/header.php';
?>

<!-- Resend Verification Result -->
<section class="py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <i class="fas fa-envelope <?php echo $message_type == 'success' ? 'text-success' : 'text-danger'; ?>" 
                               style="font-size: 5rem;"></i>
                        </div>
                        
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <?php echo $message; ?>
                        </div>
                        
                        <div class="mt-4">
                            <a href="login.php" class="btn btn-success">
                                <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>