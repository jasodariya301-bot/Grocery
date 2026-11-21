<?php
session_start();
$page_title = "Forgot Password - Fresh Groceries Online";
$error_message = '';
$success_message = '';

// Include db_config
include 'includes/db_config.php';
include 'includes/mail_config.php';

// Process forgot password form
if (isset($_POST['send_otp'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    
    // Check if email exists
    $check_query = "SELECT id, first_name, email FROM register WHERE email = ?";
    $stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes')); // OTP valid for 15 minutes
        
        // Update user with OTP
        $update_query = "UPDATE register SET reset_otp = ?, otp_expiry = ?, otp_attempts = 0 WHERE id = ?";
        $stmt_update = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt_update, "ssi", $otp, $expiry, $user['id']);
        
        if (mysqli_stmt_execute($stmt_update)) {
            // Send OTP via email
            $subject = "Password Reset OTP - Fresh Groceries";
            $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 20px; background: #f9f9f9; border: 1px solid #ddd; }
                    .otp-code { font-size: 32px; font-weight: bold; color: #28a745; text-align: center; padding: 20px; background: white; border-radius: 10px; margin: 20px 0; letter-spacing: 5px; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                    .warning { color: #dc3545; font-size: 14px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Password Reset Request</h1>
                    </div>
                    <div class='content'>
                        <h2>Hello " . htmlspecialchars($user['first_name']) . ",</h2>
                        <p>We received a request to reset your password. Use the OTP code below to proceed:</p>
                        
                        <div class='otp-code'>
                            $otp
                        </div>
                        
                        <p>This OTP is valid for <strong>15 minutes</strong>.</p>
                        
                        <p class='warning'><strong>Security Note:</strong> If you didn't request this password reset, please ignore this email. Your password will remain unchanged.</p>
                        
                        <hr>
                        <p><strong>Never share this OTP with anyone.</strong> Fresh Groceries will never ask for your OTP or password.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " Fresh Groceries. All rights reserved.</p>
                        <p>This is an automated message, please do not reply.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail_result = sendResetOTPEmail($email, $user['first_name'], $otp, $subject, $message);
            
            if ($mail_result['success']) {
                // Store email in session for verification
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_otp_time'] = time();
                
                // Redirect to OTP verification page
                header("Location: verify-otp.php?email=" . urlencode($email));
                exit();
            } else {
                $error_message = "Failed to send OTP. Please try again later.";
            }
        } else {
            $error_message = "Database error. Please try again.";
        }
    } else {
        $error_message = "Email address not found in our records.";
    }
}

include 'includes/header.php';
?>

<!-- Forgot Password Form -->
<section class="py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-key fa-3x text-success mb-3"></i>
                            <h3>Forgot Password?</h3>
                            <p class="text-muted">Enter your email address and we'll send you an OTP to reset your password.</p>
                        </div>
                        
                        <form method="POST" action="" id="forgotPasswordForm">
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Enter your registered email" required>
                                </div>
                                <div class="error-message text-danger" id="error-email"></div>
                            </div>
                            
                            <button type="submit" name="send_otp" class="btn btn-success w-100 mb-3">
                                <i class="fas fa-paper-plane me-2"></i>Send OTP
                            </button>
                            
                            <div class="text-center">
                                <a href="login.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

<style>
.card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.alert {
    border-radius: 10px;
    margin-bottom: 20px;
}
.input-group-text {
    background-color: #f8f9fa;
}
</style>

<script>
$(document).ready(function() {
    $("#forgotPasswordForm").validate({
        rules: {
            email: {
                required: true,
                email: true,
                maxlength: 255
            }
        },
        messages: {
            email: {
                required: "Please enter your email address",
                email: "Please enter a valid email address",
                maxlength: "Email is too long"
            }
        },
        errorPlacement: function(error, element) {
            error.appendTo("#error-" + element.attr('id'));
        },
        highlight: function(element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function(element) {
            $(element).removeClass("is-invalid");
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>