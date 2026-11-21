<?php 
include('includes/sidebar.php');
include('includes/header.php');
include('../includes/db_config.php');

// Get customer ID from URL
$customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($customer_id == 0) {
    header('Location: customers.php');
    exit();
}

// Fetch customer details
$query = "SELECT * FROM register WHERE id = $customer_id AND role = 'user'";
$result = mysqli_query($con, $query);

if(mysqli_num_rows($result) == 0) {
    header('Location: customers.php');
    exit();
}

$customer = mysqli_fetch_assoc($result);
?>
<?php
if(isset($_SESSION['message'])) {
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
    echo '<div class="alert alert-' . $message_type . ' alert-dismissible fade show" role="alert">
            ' . $_SESSION['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<div class="content-wrapper">
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Customer Details</h2>
            <a href="customers.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="row">

            <!-- LEFT SIDE - PROFILE -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">

                     <?php
// In view-customer.php, after fetching the customer data
$profile_image = '../uploads/profiles/default-avatar.jpg';
if(!empty($customer['profile_picture']) && file_exists('../uploads/profiles/' . $customer['profile_picture'])) {
    $profile_image = '../uploads/profiles/' . $customer['profile_picture'];
}
?>
<img src="<?php echo $profile_image; ?>"
     class="rounded-circle mb-3"
     width="150" 
     height="150"
     style="object-fit: cover; border: 3px solid #ddd;">
                                                                    
                        <h4><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h4>
                        
                        <?php if($customer['status'] == 'Active'): ?>
                            <span class="badge bg-success" style="font-size: 14px; padding: 5px 15px;">Active</span>
                        <?php else: ?>
                            <span class="badge bg-warning" style="font-size: 14px; padding: 5px 15px;">Inactive</span>
                        <?php endif; ?>

                        <hr>

                        <div class="text-start">
                            <p class="mb-2"><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                            <p class="mb-2"><strong><i class="fas fa-phone"></i> Phone:</strong> <?php echo $customer['mobile']; ?></p>
                            <p class="mb-2"><strong><i class="fas fa-venus-mars"></i> Gender:</strong> <?php echo ucfirst($customer['gender']); ?></p>
                            <p class="mb-2"><strong><i class="fas fa-calendar"></i> Member Since:</strong> <?php echo date('d M Y', strtotime($customer['created_at'])); ?></p>
                        </div>
                        
                        <hr>

                        <form method="POST" action="update_customer_status.php">
                            <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                            <input type="hidden" name="current_status" value="<?php echo $customer['status']; ?>">
                            
                            <?php if($customer['status'] == 'Active'): ?>
                                <button type="submit" name="action" value="inactive" class="btn btn-warning w-100" onclick="return confirm('Are you sure you want to set this customer as Inactive?')">
                                    <i class="fas fa-user-slash"></i> Set Inactive
                                </button>
                            <?php else: ?>
                                <button type="submit" name="action" value="active" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to set this customer as Active?')">
                                    <i class="fas fa-user-check"></i> Set Active
                                </button>
                            <?php endif; ?>
                        </form>

                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE - INFO -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <label class="text-muted">First Name</label>
                                    <p class="fw-bold"><?php echo htmlspecialchars($customer['first_name']); ?></p>
                                </div>
                                <div class="info-box mt-3">
                                    <label class="text-muted">Email Address</label>
                                    <p class="fw-bold"><?php echo htmlspecialchars($customer['email']); ?></p>
                                </div>
                                <div class="info-box mt-3">
                                    <label class="text-muted">Gender</label>
                                    <p class="fw-bold"><?php echo ucfirst($customer['gender']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <label class="text-muted">Last Name</label>
                                    <p class="fw-bold"><?php echo htmlspecialchars($customer['last_name']); ?></p>
                                </div>
                                <div class="info-box mt-3">
                                    <label class="text-muted">Mobile Number</label>
                                    <p class="fw-bold"><?php echo $customer['mobile']; ?></p>
                                </div>
                                <div class="info-box mt-3">
                                    <label class="text-muted">Account Status</label>
                                    <p class="fw-bold">
                                        <?php if($customer['status'] == 'Active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Inactive</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if(!empty($customer['token'])): ?>
                        <hr>
                        <div class="alert alert-info">
                            <strong><i class="fas fa-key"></i> Verification Token:</strong> 
                            <code><?php echo $customer['token']; ?></code>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Additional Information Card -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Account Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <strong>Account Created:</strong><br>
                                    <?php echo date('l, d F Y', strtotime($customer['created_at'])); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <strong>Account ID:</strong><br>
                                    #<?php echo $customer['id']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<?php include('includes/footer.php'); ?>