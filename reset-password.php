<?php
session_start();
$page_title = "Reset Password - Fresh Groceries Online";
$error_message = '';
$success_message = '';

// Check if user is verified
if (!isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true) {
    header("Location: forgot-password.php");
    exit();
}

// Include db_config
include 'includes/db_config.php';

// Process password reset
if (isset($_POST['reset_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        $user_id = $_SESSION['reset_user_id'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password
        $update_query = "UPDATE register SET password = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Clear session variables
            unset($_SESSION['reset_verified']);
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_email']);
            
            $success_message = "Password reset successfully! You can now login with your new password.";
            
            // Redirect after 3 seconds
            header("refresh:3;url=login.php");
        } else {
            $error_message = "Failed to reset password. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<!-- Reset Password Form -->
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
                            <i class="fas fa-lock fa-3x text-success mb-3"></i>
                            <h3>Create New Password</h3>
                            <p class="text-muted">Please enter your new password below.</p>
                        </div>
                        
                        <form method="POST" action="" id="resetPasswordForm">
                            <div class="mb-3">
                                <label class="form-label">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter new password" required>
                                </div>
                                <div class="error-message text-danger" id="error-password"></div>
                                <small class="text-muted">Password must be at least 6 characters</small>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Confirm new password" required>
                                </div>
                                <div class="error-message text-danger" id="error-confirm_password"></div>
                            </div>
                            
                            <button type="submit" name="reset_password" class="btn btn-success w-100 mb-3">
                                <i class="fas fa-save me-2"></i>Reset Password
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
    $("#resetPasswordForm").validate({
        rules: {
            password: {
                required: true,
                minlength: 6
            },
            confirm_password: {
                required: true,
                equalTo: "#password"
            }
        },
        messages: {
            password: {
                required: "Please enter a password",
                minlength: "Password must be at least 6 characters"
            },
            confirm_password: {
                required: "Please confirm your password",
                equalTo: "Passwords do not match"
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