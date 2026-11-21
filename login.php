<?php
session_start(); // Start session at the beginning

// Check for password reset success
if (isset($_GET['reset']) && $_GET['reset'] == 'success') {
    $success_message = "Your password has been successfully reset. Please login with your new password.";
}

// Redirect if already logged in - THIS MUST COME BEFORE ANY OUTPUT
if (isset($_SESSION['user_id'])) {
    // Check user role and redirect accordingly
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$page_title = "Login - Fresh Groceries Online";

// Initialize variables
$errors = [];
$success_message = '';

// Process login form submission - THIS MUST HAPPEN BEFORE ANY OUTPUT
if (isset($_POST['login_submit'])) {
    
    // Include db_config here for database connection
    include 'includes/db_config.php';
    
    // Get form data
    $email = mysqli_real_escape_string($con, $_POST['loginEmail']);
    $password = $_POST['loginPassword'];
    $remember_me = isset($_POST['rememberMe']) ? true : false;
    
    // Server-side validation
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors['password'] = "Password is required";
    }
    
    // If no validation errors, check credentials
    if (empty($errors)) {
        // Use prepared statement to prevent SQL injection
        $login_query = "SELECT id, first_name, last_name, email, password, role, status, profile_picture 
                        FROM register 
                        WHERE email = ?";
        
        $stmt = mysqli_prepare($con, $login_query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $login_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($login_result) == 1) {
            $user = mysqli_fetch_assoc($login_result);
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                
                // Check if account is active
                if ($user['status'] == 'Active') {
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_profile'] = $user['profile_picture'];
                    $_SESSION['logged_in'] = true;
                    
                    // Remember me functionality
                    if ($remember_me) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                        
                        // Store token in database
                        $update_token = "UPDATE register SET token = ? WHERE id = ?";
                        $stmt_token = mysqli_prepare($con, $update_token);
                        mysqli_stmt_bind_param($stmt_token, "si", $token, $user['id']);
                        mysqli_stmt_execute($stmt_token);
                        
                        // Set cookie
                        setcookie('remember_token', $token, $expiry, '/', '', false, true);
                        setcookie('user_email', $email, $expiry, '/', '', false, true);
                    }
                    
                    // Redirect based on role
                    if ($user['role'] == 'admin') {
                        header("Location: admin/index.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                    
                } else {
                    $errors['login'] = "Your account is not verified. Please check your email for the verification link. 
                                       <br><a href='resend-verification.php?email=" . urlencode($email) . "' 
                                       class='alert-link'>Resend verification email</a>";
                }
            } else {
                $errors['login'] = "Invalid email or password";
            }
        } else {
            $errors['login'] = "Invalid email or password";
        }
    }
}

// NOW include header.php AFTER all processing is done
include 'includes/header.php';
?>


<!-- Login Section -->
<section class="py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
             
                <!-- Display error messages -->
                <?php if (!empty($errors['login'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $errors['login']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Display success message for password reset -->
            <?php if (isset($success_message) && !empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Welcome Back!</h3>
                        
                        <form id="loginForm" method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="loginEmail" name="loginEmail" 
                                       placeholder="Enter your email" 
                                       value="<?php echo isset($_POST['loginEmail']) ? htmlspecialchars($_POST['loginEmail']) : ''; ?>" 
                                        >
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                                <div class="error-message text-danger" id="error-login-email"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                       id="loginPassword" name="loginPassword" 
                                       placeholder="Enter your password">
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                                <?php endif; ?>
                                <div class="error-message text-danger" id="error-login-password"></div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                                <label class="form-check-label" for="rememberMe">Remember me</label>
                            </div>
                            <button type="submit" name="login_submit" class="btn btn-success w-100 mb-3" id="loginBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                            
                            <div class="text-center mt-3">
                                <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="text-center">
                                <p class="mb-0">Don't have an account? 
                                    <a href="register.php" class="text-decoration-none fw-bold">Register here</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- jQuery and Validation -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

<style>
.error {
    color: #dc3545;
    font-size: 0.875em;
    margin-top: 0.25rem;
}
.is-invalid {
    border-color: #dc3545 !important;
}
.invalid-feedback {
    display: block !important;
}
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
</style>

<script>
$(document).ready(function() {
    // Login Form Validation
    $("#loginForm").validate({
        rules: {
            loginEmail: {
                required: true,
                email: true,
                maxlength: 255
            },
            loginPassword: {
                required: true,
                minlength: 6
            }
        },
        messages: {
            loginEmail: {
                required: "Please enter your email",
                email: "Please enter a valid email address",
                maxlength: "Email is too long"
            },
            loginPassword: {
                required: "Please enter your password",
                minlength: "Password must be at least 6 characters"
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