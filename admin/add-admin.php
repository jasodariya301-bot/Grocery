<?php
// Start session at the very top
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error'] = "Please login to access this page";
    header("Location: admin_login.php");
    exit();
}

// Now only checking for 'admin' role (no super_admin)
if ($_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "You don't have permission to access this page";
    header("Location: index.php");
    exit();
}

// Include database connection and email configuration
include __DIR__ . '/../includes/db_config.php';
include __DIR__ . '/../includes/mail_config.php';
include 'includes/sidebar.php';

// Initialize variables
$first_name = $last_name = $email = $mobile = $gender = '';
$errors = [];
$success_message = '';

// Process form submission
if (isset($_POST['add_admin'])) {
    
    // Get and sanitize form data
    $first_name = mysqli_real_escape_string($con, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($con, trim($_POST['last_name']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $mobile = mysqli_real_escape_string($con, trim($_POST['mobile']));
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    // Role is always 'admin' now
    $role = 'admin';
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($first_name)) {
        $errors['first_name'] = "First name is required";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $first_name)) {
        $errors['first_name'] = "First name can only contain letters and spaces";
    }
    
    if (empty($last_name)) {
        $errors['last_name'] = "Last name is required";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $last_name)) {
        $errors['last_name'] = "Last name can only contain letters and spaces";
    }
    
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address";
    }
    
    if (empty($mobile)) {
        $errors['mobile'] = "Mobile number is required";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors['mobile'] = "Mobile number must be 10 digits";
    }
    
    if (empty($gender)) {
        $errors['gender'] = "Please select gender";
    }
    
    if (empty($password)) {
        $errors['password'] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $check_email = "SELECT id FROM register WHERE email = '$email'";
        $result = mysqli_query($con, $check_email);
        
        if (mysqli_num_rows($result) > 0) {
            $errors['email'] = "Email already exists in the system";
        }
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate token
        $token = md5(uniqid() . $email . time());
        
        // Insert query - role is always 'admin'
        $insert_query = "INSERT INTO register (
            first_name, 
            last_name, 
            email, 
            password, 
            mobile, 
            gender, 
            role, 
            status, 
            token, 
            created_at
        ) VALUES (
            '$first_name', 
            '$last_name', 
            '$email', 
            '$hashed_password', 
            '$mobile', 
            '$gender', 
            'admin', 
            'Active', 
            '$token', 
            NOW()
        )";
        
        if (mysqli_query($con, $insert_query)) {
            $admin_id = mysqli_insert_id($con);
            
            // Log the action if table exists
            $check_table = mysqli_query($con, "SHOW TABLES LIKE 'admin_logs'");
            if (mysqli_num_rows($check_table) > 0) {
                $log_query = "INSERT INTO admin_logs (admin_id, action, ip_address) 
                             VALUES (
                                {$_SESSION['admin_id']}, 
                                'Added new admin: $email (ID: $admin_id)', 
                                '{$_SERVER['REMOTE_ADDR']}'
                             )";
                mysqli_query($con, $log_query);
            }
            
            // Send email with credentials
            $email_sent = sendAdminCredentialsEmail($email, $first_name, $last_name, $password, $role);
            
            if ($email_sent['success']) {
                $success_message = "Admin added successfully! Login credentials have been sent to $email";
            } else {
                $success_message = "Admin added successfully! But failed to send email notification. Please provide credentials manually.";
                $errors['email_warning'] = $email_sent['message'];
            }
            
            // Clear form data
            $first_name = $last_name = $email = $mobile = $gender = '';
            
        } else {
            $errors['database'] = "Error adding admin: " . mysqli_error($con);
        }
    }
}

// Function to send admin credentials email
function sendAdminCredentialsEmail($email, $first_name, $last_name, $password, $role) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Disable debug output for production
        $mail->isSMTP();                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';       // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                   // Enable SMTP authentication
        $mail->Username   = 'priyankmalviya1234@gmail.com';  // SMTP username
        $mail->Password   = 'fzuk jvot tskc vzqi';  // SMTP password (App Password)
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587;                    // TCP port to connect to 
        
        // Recipients
        $mail->setFrom('noreply@freshgroceries.com', 'Fresh Groceries Admin');
        $mail->addAddress($email, $first_name . ' ' . $last_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Admin Account Credentials - Fresh Groceries';
        
        // Get login URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . $host;
        $project_folder = '/Grocery-website'; // Adjust this to your project folder
        $login_url = $base_url . $project_folder . "/admin/admin_login.php";
        
        // Email body
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 20px; background: #f9f9f9; border: 1px solid #ddd; }
                .credentials { background: #fff; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
                .credential-item { margin: 10px 0; padding: 5px; }
                .credential-label { font-weight: bold; color: #28a745; display: inline-block; width: 100px; }
                .credential-value { font-family: monospace; font-size: 14px; background: #f5f5f5; padding: 3px 8px; border-radius: 3px; }
                .button { display: inline-block; padding: 12px 30px; background: #28a745; 
                         color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                .button:hover { background: #218838; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Fresh Groceries Admin Panel!</h1>
                </div>
                <div class='content'>
                    <h2>Hello " . htmlspecialchars($first_name) . " " . htmlspecialchars($last_name) . ",</h2>
                    <p>Your administrator account has been created successfully. Below are your login credentials:</p>
                    
                    <div class='credentials'>
                        <div class='credential-item'>
                            <span class='credential-label'>Email:</span>
                            <span class='credential-value'>" . htmlspecialchars($email) . "</span>
                        </div>
                        <div class='credential-item'>
                            <span class='credential-label'>Password:</span>
                            <span class='credential-value'>" . htmlspecialchars($password) . "</span>
                        </div>
                        <div class='credential-item'>
                            <span class='credential-label'>Role:</span>
                            <span class='credential-value'>" . ucfirst(htmlspecialchars($role)) . "</span>
                        </div>
                    </div>
                    
                    <div style='text-align: center;'>
                        <a href='$login_url' class='button'>Login to Admin Panel</a>
                    </div>
                    
                    <div class='warning'>
                        <strong><i class='fas fa-exclamation-triangle'></i> Important Security Information:</strong><br>
                        • Please change your password immediately after first login.<br>
                        • Do not share your credentials with anyone.<br>
                        • For security reasons, the admin panel URL should be kept confidential.<br>
                        • If you didn't request this account, please contact the system administrator immediately.
                    </div>
                    
                    <p><strong>Login URL:</strong><br>
                    <code>$login_url</code></p>
                    
                    <p><strong>Getting Started:</strong><br>
                    As an administrator, you can:<br>
                    • Manage products and inventory<br>
                    • Process and track orders<br>
                    • Manage customer accounts<br>
                    • View sales reports and analytics<br>
                    • Manage offers and discounts</p>
                    
                    <p>If you have any questions or need assistance, please contact your system administrator.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Fresh Groceries. All rights reserved.</p>
                    <p>This is an automated message, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Plain text version
        $mail->AltBody = "Hello " . $first_name . " " . $last_name . ",\n\n" .
                         "Your administrator account has been created successfully. Below are your login credentials:\n\n" .
                         "Email: $email\n" .
                         "Password: $password\n" .
                         "Role: " . ucfirst($role) . "\n\n" .
                         "Login URL: $login_url\n\n" .
                         "Important Security Information:\n" .
                         "• Please change your password immediately after first login.\n" .
                         "• Do not share your credentials with anyone.\n" .
                         "• For security reasons, the admin panel URL should be kept confidential.\n\n" .
                         "Getting Started:\n" .
                         "As an administrator, you can:\n" .
                         "• Manage products and inventory\n" .
                         "• Process and track orders\n" .
                         "• Manage customer accounts\n" .
                         "• View sales reports and analytics\n" .
                         "• Manage offers and discounts\n\n" .
                         "If you have any questions or need assistance, please contact your system administrator.\n\n" .
                         "This is an automated message, please do not reply.\n\n" .
                         "© " . date('Y') . " Fresh Groceries. All rights reserved.";
        
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        error_log("Admin Credentials Mailer Error: " . $mail->ErrorInfo);
        return ['success' => false, 'message' => 'Failed to send email: ' . $mail->ErrorInfo];
    }
}

// Include sidebar
include 'includes/sidebar.php';
include 'includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center mb-4">
                <h4 class="page-title">Add New Administrator</h4>
                <a href="manage-admins.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Admins
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-shield me-2"></i>Admin Registration Form
                    </h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Success Message -->
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Email Warning -->
                    <?php if (!empty($errors['email_warning'])): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $errors['email_warning']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Error Message -->
                    <?php if (!empty($errors['database'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $errors['database']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Add Admin Form -->
                    <form method="POST" action="" id="addAdminForm" class="needs-validation" novalidate>
                        
                        <!-- Name Fields -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    First Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="<?php echo htmlspecialchars($first_name); ?>"
                                       placeholder="Enter first name"
                                       required>
                                <?php if (isset($errors['first_name'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['first_name']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    Last Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="<?php echo htmlspecialchars($last_name); ?>"
                                       placeholder="Enter last name"
                                       required>
                                <?php if (isset($errors['last_name'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['last_name']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($email); ?>"
                                   placeholder="Enter email address"
                                   required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Mobile -->
                        <div class="mb-3">
                            <label for="mobile" class="form-label">
                                Mobile Number <span class="text-danger">*</span>
                            </label>
                            <input type="tel" 
                                   class="form-control <?php echo isset($errors['mobile']) ? 'is-invalid' : ''; ?>" 
                                   id="mobile" 
                                   name="mobile" 
                                   value="<?php echo htmlspecialchars($mobile); ?>"
                                   placeholder="Enter 10 digit mobile number"
                                   maxlength="10"
                                   pattern="[0-9]{10}"
                                   required>
                            <?php if (isset($errors['mobile'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['mobile']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Gender -->
                        <div class="mb-3">
                            <label class="form-label">
                                Gender <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?php echo isset($errors['gender']) ? 'is-invalid' : ''; ?>" 
                                    name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <?php if (isset($errors['gender'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['gender']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Hidden role field (always admin) -->
                        <input type="hidden" name="role" value="admin">
                        
                        <!-- Role info display -->
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control bg-light" value="Admin" readonly disabled>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                All new admins are created with 'admin' role
                            </small>
                        </div>
                        
                        <!-- Password -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter password"
                                       required>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                                <?php endif; ?>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       placeholder="Confirm password"
                                       required>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Email Notification Info -->
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-envelope me-2"></i>
                            <strong>Email Notification:</strong> The admin's login credentials will be automatically sent to the provided email address.
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="reset" class="btn btn-light me-md-2">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                            <button type="submit" name="add_admin" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Add Admin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Information Card -->
            <div class="card mt-4 bg-light">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-shield-alt text-primary me-2"></i>
                        Admin Information
                    </h6>
                    <ul class="mb-0 small">
                        <li class="mb-2">
                            <strong class="text-primary">Admin Role:</strong> 
                            All admins have the same privileges to manage products, categories, orders, customers, and offers.
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope text-info me-1"></i>
                            <strong>Email Notification:</strong> An email with login credentials will be sent to the new admin automatically.
                        </li>
                        <li>
                            <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                            New admins are created with "Active" status by default.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery Validation -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

<script>
$(document).ready(function() {
    $("#addAdminForm").validate({
        rules: {
            first_name: {
                required: true,
                minlength: 2,
                pattern: /^[a-zA-Z\s]+$/
            },
            last_name: {
                required: true,
                minlength: 2,
                pattern: /^[a-zA-Z\s]+$/
            },
            email: {
                required: true,
                email: true
            },
            mobile: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10
            },
            gender: {
                required: true
            },
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
            first_name: {
                required: "Please enter first name",
                minlength: "First name must be at least 2 characters",
                pattern: "Only letters and spaces allowed"
            },
            last_name: {
                required: "Please enter last name",
                minlength: "Last name must be at least 2 characters",
                pattern: "Only letters and spaces allowed"
            },
            email: {
                required: "Please enter email address",
                email: "Please enter a valid email address"
            },
            mobile: {
                required: "Please enter mobile number",
                digits: "Please enter only digits",
                minlength: "Mobile number must be 10 digits",
                maxlength: "Mobile number must be 10 digits"
            },
            gender: {
                required: "Please select gender"
            },
            password: {
                required: "Please enter password",
                minlength: "Password must be at least 6 characters"
            },
            confirm_password: {
                required: "Please confirm password",
                equalTo: "Passwords do not match"
            }
        },
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function(element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        }
    });
    
    // Restrict mobile number to digits only
    $('#mobile').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>