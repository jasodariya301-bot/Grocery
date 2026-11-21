<?php
$page_title = "Register - Fresh Groceries Online";
include 'includes/db_config.php';
include 'includes/header.php';
include 'includes/mail_config.php'; // Add this line

// Initialize variables
$errors = [];
$success_message = '';

// Process registration form submission
if (isset($_POST['register_submit'])) {
    
    // Get form data (your existing code remains the same)
    $first_name = mysqli_real_escape_string($con, $_POST['registerFirstName']);
    $last_name = mysqli_real_escape_string($con, $_POST['registerLastName']);
    $email = mysqli_real_escape_string($con, $_POST['registerEmail']);
    $mobile = mysqli_real_escape_string($con, $_POST['registerPhone']);
    $password = $_POST['registerPassword'];
    $confirm_password = $_POST['registerConfirmPassword'];
    $gender = mysqli_real_escape_string($con, $_POST['registerGender']);
    $agree_terms = isset($_POST['agreeTerms']) ? 1 : 0;
    
    // Server-side validation
    if (empty($first_name)) {
        $errors['first_name'] = "First name is required";
    }
    
    if (empty($last_name)) {
        $errors['last_name'] = "Last name is required";
    }
    
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }
    
    if (empty($mobile)) {
        $errors['mobile'] = "Mobile number is required";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors['mobile'] = "Mobile number must be 10 digits";
    }
    
    if (empty($password)) {
        $errors['password'] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters";
    }
    
    if (empty($confirm_password)) {
        $errors['confirm_password'] = "Please confirm password";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }
    
    if (empty($gender)) {
        $errors['gender'] = "Please select gender";
    }
    
    if (!$agree_terms) {
        $errors['agree_terms'] = "You must agree to terms & conditions";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $check_email_query = "SELECT id FROM register WHERE email = '$email'";
        $check_email_result = mysqli_query($con, $check_email_query);
        
        if (mysqli_num_rows($check_email_result) > 0) {
            $errors['email'] = "Email already registered";
        }
    }
    
 if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate token for email verification
        $token = md5(uniqid() . $email . time());
        
        // Insert query
        $insert_query = "INSERT INTO register (first_name, last_name, email, password, mobile, gender, token, status, created_at) 
                         VALUES ('$first_name', '$last_name', '$email', '$hashed_password', '$mobile', '$gender', '$token', 'Inactive', NOW())";
        
        // After successful insertion
        if (mysqli_query($con, $insert_query)) {
            
            // Send verification email
            $email_result = sendVerificationEmail($email, $first_name, $token);
            
            if ($email_result['success']) {
                $success_message = "Registration successful! Please check your email to verify your account. The verification link will expire in 24 hours.";
            } else {
                $success_message = "Registration successful! But we couldn't send the verification email. Please contact support or try to <a href='resend-verification.php?email=" . urlencode($email) . "'>resend verification email</a>.";
                // Log the error for debugging
                error_log("Failed to send verification email to: $email - " . $email_result['message']);
            }
            
            // Clear form data after successful registration
            $_POST = array();
        } else {
            $errors['database'] = "Registration failed: " . mysqli_error($con);
        }
    }
}
?>


<!-- Register Section -->
<section class="py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <!-- Display success or error messages -->
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors['database'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $errors['database']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Create Account</h3>
                        
                        <form id="registerForm" method="POST" action="" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                           id="registerFirstName" name="registerFirstName" 
                                           placeholder="First Name" 
                                           value="<?php echo isset($_POST['registerFirstName']) ? htmlspecialchars($_POST['registerFirstName']) : ''; ?>"
                                           required>
                                    <?php if (isset($errors['first_name'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['first_name']; ?></div>
                                    <?php endif; ?>
                                    <div class="error-message text-danger" id="error-register-fname"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                           id="registerLastName" name="registerLastName" 
                                           placeholder="Last Name" 
                                           value="<?php echo isset($_POST['registerLastName']) ? htmlspecialchars($_POST['registerLastName']) : ''; ?>"
                                           required>
                                    <?php if (isset($errors['last_name'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['last_name']; ?></div>
                                    <?php endif; ?>
                                    <div class="error-message text-danger" id="error-register-lname"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="registerEmail" name="registerEmail" 
                                       placeholder="Enter your email" 
                                       value="<?php echo isset($_POST['registerEmail']) ? htmlspecialchars($_POST['registerEmail']) : ''; ?>"
                                       required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                                <div class="error-message text-danger" id="error-register-email"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control <?php echo isset($errors['mobile']) ? 'is-invalid' : ''; ?>" 
                                       id="registerPhone" name="registerPhone" 
                                       placeholder="Enter your phone" 
                                       value="<?php echo isset($_POST['registerPhone']) ? htmlspecialchars($_POST['registerPhone']) : ''; ?>"
                                       required>
                                <?php if (isset($errors['mobile'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['mobile']; ?></div>
                                <?php endif; ?>
                                <div class="error-message text-danger" id="error-register-phone"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                       id="registerPassword" name="registerPassword" 
                                       placeholder="Create password" required>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                                <?php endif; ?>
                                <div class="error-message text-danger" id="error-register-password"></div>
                                <small class="text-muted">Password must be at least 6 characters</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                       id="registerConfirmPassword" name="registerConfirmPassword" 
                                       placeholder="Confirm password" required>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                                <?php endif; ?>
                                <div class="error-message text-danger" id="error-register-confirm-password"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-control <?php echo isset($errors['gender']) ? 'is-invalid' : ''; ?>" 
                                        id="registerGender" name="registerGender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo (isset($_POST['registerGender']) && $_POST['registerGender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo (isset($_POST['registerGender']) && $_POST['registerGender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo (isset($_POST['registerGender']) && $_POST['registerGender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                                <?php if (isset($errors['gender'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['gender']; ?></div>
                                <?php endif; ?>
                                <div class="error-message text-danger" id="error-register-gender"></div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input <?php echo isset($errors['agree_terms']) ? 'is-invalid' : ''; ?>" 
                                       id="agreeTerms" name="agreeTerms" <?php echo isset($_POST['agreeTerms']) ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="agreeTerms">I agree to the <a href="terms.php">Terms & Conditions</a> <span class="text-danger">*</span></label>
                                <?php if (isset($errors['agree_terms'])): ?>
                                    <div class="invalid-feedback d-block"><?php echo $errors['agree_terms']; ?></div>
                                <?php endif; ?>
                                <div class="error-message text-danger" id="error-agree-terms"></div>
                            </div>
                            
                            <button type="submit" name="register_submit" class="btn btn-success w-100 mb-3" id="registerBtn">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                            
                            <hr class="my-4">
                            
                            <div class="text-center">
                                <p class="mb-0">Already have an account? 
                                    <a href="login.php" class="text-decoration-none fw-bold">Login here</a>
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
    // Register Form Validation
    $("#registerForm").validate({
        rules: {
            registerFirstName: {
                required: true,
                minlength: 2,
                pattern: /^[A-Za-z\s]+$/
            },
            registerLastName: {
                required: true,
                minlength: 2,
                pattern: /^[A-Za-z\s]+$/
            },
            registerEmail: {
                required: true,
                email: true,
                maxlength: 255
            },
            registerPhone: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10
            },
            registerPassword: {
                required: true,
                minlength: 6,
                maxlength: 50
            },
            registerConfirmPassword: {
                required: true,
                equalTo: "#registerPassword"
            },
            registerGender: {
                required: true
            },
            agreeTerms: {
                required: true
            }
        },
        messages: {
            registerFirstName: {
                required: "Please enter your first name",
                minlength: "Name must be at least 2 characters",
                pattern: "Only letters allowed"
            },
            registerLastName: {
                required: "Please enter your last name",
                minlength: "Name must be at least 2 characters",
                pattern: "Only letters allowed"
            },
            registerEmail: {
                required: "Please enter your email",
                email: "Please enter a valid email",
                maxlength: "Email is too long"
            },
            registerPhone: {
                required: "Please enter your phone number",
                digits: "Please enter only digits",
                minlength: "Please enter 10 digits",
                maxlength: "Please enter 10 digits"
            },
            registerPassword: {
                required: "Please enter a password",
                minlength: "Password must be at least 6 characters"
            },
            registerConfirmPassword: {
                required: "Please confirm your password",
                equalTo: "Passwords do not match"
            },
            registerGender: {
                required: "Please select your gender"
            },
            agreeTerms: {
                required: "You must agree to terms & conditions"
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