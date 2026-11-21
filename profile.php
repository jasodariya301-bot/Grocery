<?php
session_start();
$page_title = "My Profile - Fresh Groceries Online";
include 'includes/db_config.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch user details from database
$query = "SELECT first_name, last_name, email, mobile, gender, profile_picture, created_at 
          FROM register WHERE id = $user_id";
$result = mysqli_query($con, $query);
$user = mysqli_fetch_assoc($result);

// Handle profile update form submission
if (isset($_POST['update_profile'])) {
    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
    $mobile = mysqli_real_escape_string($con, $_POST['mobile']);
    
    // Validation
    $errors = [];
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($mobile)) $errors[] = "Mobile number is required";
    elseif (!preg_match('/^[0-9]{10}$/', $mobile)) $errors[] = "Mobile number must be 10 digits";
    
    // Handle profile picture upload
    $profile_pic = $user['profile_picture']; // Keep existing by default
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        $file_size = $_FILES['profile_picture']['size'];
        
        if (in_array($file_type, $allowed_types) && $file_size <= 2 * 1024 * 1024) { // Max 2MB
            $upload_dir = 'uploads/profiles/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                // Delete old profile picture if exists
                if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
                    unlink($user['profile_picture']);
                }
                $profile_pic = $upload_path;
            }
        } else {
            $errors[] = "Invalid file type or size too large (max 2MB, JPG/PNG/GIF only)";
        }
    }
    
    if (empty($errors)) {
        $update_query = "UPDATE register SET 
                        first_name = '$first_name', 
                        last_name = '$last_name', 
                        mobile = '$mobile', 
                        profile_picture = '$profile_pic' 
                        WHERE id = $user_id";
        
        if (mysqli_query($con, $update_query)) {
            $success_message = "Profile updated successfully!";
            // Update session name
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $_SESSION['user_profile'] = $profile_pic;
            // Refresh user data
            $result = mysqli_query($con, $query);
            $user = mysqli_fetch_assoc($result);
        } else {
            $error_message = "Error updating profile: " . mysqli_error($con);
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<!-- Profile Page Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <!-- Sidebar -->
                <div class="card shadow mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                                <img src="<?php echo $user['profile_picture']; ?>" alt="Profile" 
                                     class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 150px; height: 150px; font-size: 3rem;">
                                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h5><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                        <p class="text-muted">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
                
                <div class="card shadow">
                    <div class="list-group list-group-flush">
                        <a href="profile.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-user me-2"></i>My Profile
                        </a>
                        <a href="orders.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-bag me-2"></i>My Orders
                        </a>
                        
                        <a href="change-password.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-key me-2"></i>Change Password
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="mb-4">Profile Information</h4>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" 
                                           value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" 
                                           value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="tel" class="form-control" name="mobile" 
                                       value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Gender</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['gender']); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" class="form-control" name="profile_picture" accept="image/*">
                                <small class="text-muted">Max size 2MB. Allowed: JPG, PNG, GIF</small>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>