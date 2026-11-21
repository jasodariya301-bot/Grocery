<?php
session_start();
$page_title = "Verify OTP - Fresh Groceries Online";
$error_message = '';
$success_message = '';

// Check if email exists in session
if (!isset($_GET['email']) && !isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}

$email = isset($_GET['email']) ? urldecode($_GET['email']) : $_SESSION['reset_email'];
$_SESSION['reset_email'] = $email;

// Include db_config
include 'includes/db_config.php';

// Process OTP verification
if (isset($_POST['verify_otp'])) {
    $otp = mysqli_real_escape_string($con, $_POST['otp']);
    
    // Check OTP validity
    $check_query = "SELECT id, reset_otp, otp_expiry, otp_attempts FROM register WHERE email = ?";
    $stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        $current_time = date('Y-m-d H:i:s');
        
        // Check OTP attempts
        if ($user['otp_attempts'] >= 5) {
            $error_message = "Too many failed attempts. Please request a new OTP.";
            
            // Reset OTP after too many attempts
            $reset_query = "UPDATE register SET reset_otp = NULL, otp_expiry = NULL, otp_attempts = 0 WHERE id = ?";
            $stmt_reset = mysqli_prepare($con, $reset_query);
            mysqli_stmt_bind_param($stmt_reset, "i", $user['id']);
            mysqli_stmt_execute($stmt_reset);
            
        } elseif ($user['reset_otp'] == $otp && strtotime($user['otp_expiry']) > strtotime($current_time)) {
            // OTP is valid
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_verified'] = true;
            
            // Clear OTP after successful verification
            $clear_otp = "UPDATE register SET reset_otp = NULL, otp_expiry = NULL, otp_attempts = 0 WHERE id = ?";
            $stmt_clear = mysqli_prepare($con, $clear_otp);
            mysqli_stmt_bind_param($stmt_clear, "i", $user['id']);
            mysqli_stmt_execute($stmt_clear);
            
            // Redirect to reset password page
            header("Location: reset-password.php");
            exit();
            
        } elseif (strtotime($user['otp_expiry']) <= strtotime($current_time)) {
            $error_message = "OTP has expired. Please request a new OTP.";
            
            // Clear expired OTP
            $clear_otp = "UPDATE register SET reset_otp = NULL, otp_expiry = NULL WHERE id = ?";
            $stmt_clear = mysqli_prepare($con, $clear_otp);
            mysqli_stmt_bind_param($stmt_clear, "i", $user['id']);
            mysqli_stmt_execute($stmt_clear);
            
        } else {
            // Increment failed attempts
            $new_attempts = $user['otp_attempts'] + 1;
            $update_attempts = "UPDATE register SET otp_attempts = ? WHERE id = ?";
            $stmt_attempts = mysqli_prepare($con, $update_attempts);
            mysqli_stmt_bind_param($stmt_attempts, "ii", $new_attempts, $user['id']);
            mysqli_stmt_execute($stmt_attempts);
            
            $remaining = 5 - $new_attempts;
            $error_message = "Invalid OTP. You have $remaining attempt(s) remaining.";
        }
    } else {
        $error_message = "User not found.";
    }
}

// Resend OTP
if (isset($_POST['resend_otp'])) {
    include 'includes/mail_config.php';
    
    $check_query = "SELECT id, first_name FROM register WHERE email = ?";
    $stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        // Generate new OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Update OTP
        $update_query = "UPDATE register SET reset_otp = ?, otp_expiry = ?, otp_attempts = 0 WHERE id = ?";
        $stmt_update = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt_update, "ssi", $otp, $expiry, $user['id']);
        
        if (mysqli_stmt_execute($stmt_update)) {
            // Send new OTP
            $subject = "New Password Reset OTP - Fresh Groceries";
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
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>New OTP for Password Reset</h1>
                    </div>
                    <div class='content'>
                        <h2>Hello " . htmlspecialchars($user['first_name']) . ",</h2>
                        <p>Here is your new OTP for password reset:</p>
                        <div class='otp-code'>$otp</div>
                        <p>This OTP is valid for <strong>15 minutes</strong>.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail_result = sendResetOTPEmail($email, $user['first_name'], $otp, $subject, $message);
            
            if ($mail_result['success']) {
                $success_message = "New OTP has been sent to your email.";
            } else {
                $error_message = "Failed to send OTP. Please try again.";
            }
        }
    }
}

include 'includes/header.php';
?>

<!-- OTP Verification Form -->
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
                            <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                            <h3>Verify OTP</h3>
                            <p class="text-muted">Please enter the 6-digit OTP sent to<br><strong><?php echo htmlspecialchars($email); ?></strong></p>
                        </div>
                        
                        <form method="POST" action="" id="otpForm">
                            <div class="mb-4">
                                <label class="form-label">Enter OTP <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="text" class="form-control text-center" id="otp" name="otp" 
                                           placeholder="000000" maxlength="6" required
                                           style="font-size: 24px; letter-spacing: 5px;">
                                </div>
                                <div class="error-message text-danger" id="error-otp"></div>
                            </div>
                            
                            <button type="submit" name="verify_otp" class="btn btn-success w-100 mb-3">
                                <i class="fas fa-check-circle me-2"></i>Verify OTP
                            </button>
                            
                            <div class="text-center">
                                <button type="submit" name="resend_otp" class="btn btn-link text-decoration-none">
                                    <i class="fas fa-redo-alt me-1"></i>Resend OTP
                                </button>
                                <span class="mx-2">|</span>
                                <a href="forgot-password.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-1"></i>Back
                                </a>
                            </div>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>OTP valid for 15 minutes
                            </small>
                        </div>
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
#otp {
    font-family: monospace;
    font-weight: bold;
}
</style>

<script>
$(document).ready(function() {
    // Auto-focus and format OTP input
    $('#otp').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, '').slice(0,6));
    });
    
    $("#otpForm").validate({
        rules: {
            otp: {
                required: true,
                minlength: 6,
                maxlength: 6,
                digits: true
            }
        },
        messages: {
            otp: {
                required: "Please enter the OTP",
                minlength: "OTP must be 6 digits",
                maxlength: "OTP must be 6 digits",
                digits: "Please enter only numbers"
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