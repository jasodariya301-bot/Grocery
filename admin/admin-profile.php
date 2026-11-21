<?php
session_start();
require_once 'auth_check.php';
include '../includes/db_config.php';
include('includes/sidebar.php');
include('includes/header.php');

// Get admin details
$admin_id = $_SESSION['admin_id'];
$query = "SELECT * FROM register WHERE id = '$admin_id'";
$result = mysqli_query($con, $query);
$admin = mysqli_fetch_assoc($result);

$success_message = '';
$error_message = '';

// Handle profile update with image upload
if (isset($_POST['update_profile'])) {
    $first_name = mysqli_real_escape_string($con, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($con, trim($_POST['last_name']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $mobile = mysqli_real_escape_string($con, trim($_POST['mobile']));
    
    // Validation
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = "First name is required";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $first_name)) {
        $errors[] = "First name can only contain letters and spaces";
    }
    
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $last_name)) {
        $errors[] = "Last name can only contain letters and spaces";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($mobile)) {
        $errors[] = "Mobile number is required";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors[] = "Mobile number must be 10 digits";
    }
    
    if (empty($gender)) {
        $errors[] = "Please select gender";
    }
    
    // Check if email already exists for other users
    $check_email = "SELECT id FROM register WHERE email = '$email' AND id != '$admin_id'";
    $email_check = mysqli_query($con, $check_email);
    if (mysqli_num_rows($email_check) > 0) {
        $errors[] = "Email already exists in the system";
    }
    
    // Handle profile picture upload
    $profile_picture = $admin['profile_picture']; // Keep existing by default
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "../uploads/profile_pictures/";
        
        // Create directory if not exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = "admin_" . $admin_id . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Check file size (max 5MB)
            if ($_FILES["profile_picture"]["size"] <= 5 * 1024 * 1024) {
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                    // Delete old profile picture if exists
                    if (!empty($admin['profile_picture']) && file_exists($target_dir . $admin['profile_picture'])) {
                        unlink($target_dir . $admin['profile_picture']);
                    }
                    $profile_picture = $new_filename;
                } else {
                    $errors[] = "Error uploading profile picture";
                }
            } else {
                $errors[] = "File size too large. Maximum 5MB allowed";
            }
        } else {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed";
        }
    }
    
    if (empty($errors)) {
        $update_query = "UPDATE register SET 
                        first_name = '$first_name',
                        last_name = '$last_name',
                        email = '$email',
                        gender = '$gender',
                        mobile = '$mobile',
                        profile_picture = '$profile_picture'
                        WHERE id = '$admin_id'";
        
        if (mysqli_query($con, $update_query)) {
            $success_message = "Profile updated successfully!";
            // Refresh admin data
            $query = "SELECT * FROM register WHERE id = '$admin_id'";
            $result = mysqli_query($con, $query);
            $admin = mysqli_fetch_assoc($result);
        } else {
            $error_message = "Error updating profile: " . mysqli_error($con);
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Verify current password
    if (!password_verify($current_password, $admin['password'])) {
        $errors[] = "Current password is incorrect";
    }
    
    if (strlen($new_password) < 6) {
        $errors[] = "New password must be at least 6 characters";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "New password and confirm password do not match";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_password = "UPDATE register SET password = '$hashed_password' WHERE id = '$admin_id'";
        
        if (mysqli_query($con, $update_password)) {
            $success_message = "Password changed successfully!";
        } else {
            $error_message = "Error changing password: " . mysqli_error($con);
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<style>
    /* Profile Page Styles */
    .profile-header {
        background: linear-gradient(135deg, #28a745 0%, #28a745 100%);
        border-radius: 10px;
        padding: 30px;
        color: white;
        margin-bottom: 30px;
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: bold;
        position: relative;
        overflow: hidden;
    }
    
    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .profile-avatar .no-image {
        font-size: 48px;
        font-weight: bold;
    }
    
    .profile-info h3 {
        margin: 0 0 5px 0;
        font-size: 24px;
    }
    
    .profile-info p {
        margin: 0;
        opacity: 0.9;
    }
    
    .profile-card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }
    
    .profile-card h5 {
        margin-bottom: 20px;
        color: #333;
        font-weight: 600;
        border-bottom: 2px solid #28a745;
        padding-bottom: 10px;
        display: inline-block;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        font-weight: 500;
        color: #555;
        margin-bottom: 8px;
        display: block;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        border-color: #28a745;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #28a745 0%, #28a745 100%);
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 500;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-secondary {
        background: #6c757d;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
    }
    
    .alert {
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .info-text {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        color: #666;
        font-size: 14px;
        margin-top: 15px;
    }
    
    .info-text i {
        color: #28a745;
        margin-right: 8px;
    }
    
    .current-image {
        margin-top: 10px;
        text-align: center;
    }
    
    .current-image img {
        max-width: 100px;
        border-radius: 50%;
        border: 3px solid #28a745;
        padding: 3px;
    }
    
    .image-preview {
        margin-top: 10px;
        text-align: center;
    }
    
    .image-preview img {
        max-width: 100px;
        border-radius: 50%;
        border: 2px solid #ddd;
        padding: 2px;
    }
    
    .badge {
        padding: 5px 10px;
        font-size: 12px;
    }
</style>

<div class="content-wrapper">
    <!-- Page Header -->
    <div class="profile-header">
        <div class="d-flex align-items-center">
            <div class="profile-avatar me-4">
                <?php 
                if (!empty($admin['profile_picture']) && file_exists("../uploads/profile_pictures/" . $admin['profile_picture'])) {
                    echo '<img src="../uploads/profile_pictures/' . $admin['profile_picture'] . '" alt="Profile Picture">';
                } else {
                    $initials = strtoupper(substr($admin['first_name'], 0, 1) . substr($admin['last_name'], 0, 1));
                    echo '<div class="no-image">' . $initials . '</div>';
                }
                ?>
            </div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></h3>
                <p><i class="fas fa-envelope me-2"></i><?php echo $admin['email']; ?></p>
                <p><i class="fas fa-phone me-2"></i><?php echo !empty($admin['mobile']) ? $admin['mobile'] : 'Not provided'; ?></p>
                <p><i class="fas fa-venus-mars me-2"></i><?php echo !empty($admin['gender']) ? $admin['gender'] : 'Not specified'; ?></p>
                <p><i class="fas fa-user-tag me-2"></i>Role: <?php echo ucfirst($admin['role']); ?></p>
                <p><i class="fas fa-calendar-alt me-2"></i>Member since: <?php echo date('F d, Y', strtotime($admin['created_at'])); ?></p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <!-- Edit Profile Card -->
            <div class="profile-card">
                <h5><i class="fas fa-user-edit me-2"></i>Edit Profile Information</h5>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="profileForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($admin['first_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($admin['last_name']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mobile">Mobile Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="mobile" name="mobile" 
                                       value="<?php echo htmlspecialchars($admin['mobile'] ?? ''); ?>" 
                                       placeholder="Enter 10 digit mobile number" maxlength="10" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender <span class="text-danger">*</span></label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($admin['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($admin['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($admin['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="profile_picture">Profile Picture</label>
                                <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                                <small class="text-muted">Max size: 5MB. Allowed: JPG, JPEG, PNG, GIF</small>
                                
                                <?php if (!empty($admin['profile_picture']) && file_exists("../uploads/profile_pictures/" . $admin['profile_picture'])): ?>
                                    <div class="current-image mt-2">
                                        <p class="mb-1">Current Image:</p>
                                        <img src="../uploads/profile_pictures/<?php echo $admin['profile_picture']; ?>" 
                                             alt="Profile Picture" style="width: 80px; height: 80px; border-radius: 50%;">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="image-preview mt-2" id="imagePreview" style="display: none;">
                                    <p class="mb-1">New Image Preview:</p>
                                    <img id="previewImg" src="#" alt="Preview" style="width: 80px; height: 80px; border-radius: 50%;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-text">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Your role and account status cannot be changed. Contact a Super Admin if you need to modify these details.
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>
        
        <div class="col-lg-6">
            <!-- Change Password Card -->
            <div class="profile-card">
                <h5><i class="fas fa-key me-2"></i>Change Password</h5>
                
                <form method="POST" action="" id="passwordForm">
                    <div class="form-group">
                        <label for="current_password">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="info-text">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Password Guidelines:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Use at least 6 characters</li>
                            <li>Use a mix of letters and numbers</li>
                            <li>Avoid using common words or personal information</li>
                        </ul>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key me-2"></i>Change Password
                    </button>
                </form>
            </div>
            
            <!-- Account Information Card -->
            <div class="profile-card">
                <h5><i class="fas fa-chart-line me-2"></i>Account Information</h5>
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Account Status</small>
                        <div>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i><?php echo $admin['status']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Admin Role</small>
                        <div>
                            <span class="badge bg-primary">
                                <i class="fas fa-user-shield me-1"></i><?php echo ucfirst($admin['role']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <small class="text-muted">Account Created</small>
                        <div><?php echo date('F d, Y h:i A', strtotime($admin['created_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

<script>
$(document).ready(function() {
    // Profile form validation
    $("#profileForm").validate({
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
    
    // Password form validation
    $("#passwordForm").validate({
        rules: {
            current_password: {
                required: true
            },
            new_password: {
                required: true,
                minlength: 6
            },
            confirm_password: {
                required: true,
                equalTo: "#new_password"
            }
        },
        messages: {
            current_password: {
                required: "Please enter current password"
            },
            new_password: {
                required: "Please enter new password",
                minlength: "Password must be at least 6 characters"
            },
            confirm_password: {
                required: "Please confirm new password",
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
    
    // Image preview
    $('#profile_picture').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
            }
            reader.readAsDataURL(file);
        } else {
            $('#imagePreview').hide();
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include 'includes/footer.php'; ?>