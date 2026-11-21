<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

include '../includes/db_config.php';

// Process login form submission
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please enter email and password";
        header("Location: admin_login.php");
        exit();
    }
    
    // Check user credentials
    $query = "SELECT * FROM register WHERE email = '$email' AND (role = 'admin' OR role = 'super_admin') AND status = 'Active'";
    $result = mysqli_query($con, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_logged_in'] = true;
            
            // Log admin login (check if table exists first)
            $check_table = mysqli_query($con, "SHOW TABLES LIKE 'admin_logs'");
            if (mysqli_num_rows($check_table) > 0) {
                $admin_id = $user['id'];
                $ip_address = $_SERVER['REMOTE_ADDR'];
                mysqli_query($con, "INSERT INTO admin_logs (admin_id, action, ip_address) VALUES ($admin_id, 'Login', '$ip_address')");
            }
            
            // Redirect to dashboard
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid password";
        }
    } else {
        $_SESSION['error'] = "Invalid email or you don't have admin access";
    }
    
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - BharatMart</title>
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<link rel="stylesheet" href="admin-css/admin-styles.css"> 
<style>
    
body{
background:#f4f6f9;
font-family:Arial;
}

.login-box{
width:400px;
margin:120px auto;
background:white;
padding:30px;
border-radius:10px;
box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.error{
color:red;
font-size:14px;
}


</style>
</head>
<body>
    <div class="login-box">
        <div class="text-center">
            <i class="fas fa-shopping-basket brand-icon"></i>
            <h3>Admin Login</h3>
        </div>
        
        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <i class='fas fa-exclamation-circle me-2'></i>" . htmlspecialchars($_SESSION['error']) . "
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
            unset($_SESSION['error']);
        }
        
        if (isset($_SESSION['success'])) {
            echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <i class='fas fa-check-circle me-2'></i>" . htmlspecialchars($_SESSION['success']) . "
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
            unset($_SESSION['success']);
        }
        ?>
        
        <form id="loginForm" method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                <label class="form-check-label" for="rememberMe">Remember me for 30 days</label>
            </div>
            
            <button type="submit" name="login" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>Login to Dashboard
            </button>
            
            
            <div class="login-footer">
                <a href="../index.php"><i class="fas fa-home me-1"></i>Back to Website</a>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $("#loginForm").validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true,
                        minlength: 5
                    }
                },
                messages: {
                    email: {
                        required: "Please enter your email",
                        email: "Please enter a valid email address"
                    },
                    password: {
                        required: "Please enter your password",
                        minlength: "Password must be at least 5 characters"
                    }
                },
                errorElement: 'div',
                errorPlacement: function(error, element) {
                    error.addClass('error');
                    element.closest('.mb-3').append(error);
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                }
            });
            
            // Add input event to remove error on typing
            $('.form-control').on('input', function() {
                if ($(this).valid()) {
                    $(this).removeClass('is-invalid');
                    $(this).next('.error').remove();
                }
            });
        });
    </script>
</body>
</html>