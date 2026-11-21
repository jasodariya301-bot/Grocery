<?php 
include('includes/sidebar.php');
include('includes/header.php');
include('../includes/db_config.php'); // Include database connection

// Fetch all customers (users with role = 'user')
$query = "SELECT id, first_name, last_name, email, mobile, status, gender, profile_picture, created_at 
          FROM register 
          WHERE role = 'user' 
          ORDER BY id DESC";

$result = mysqli_query($con, $query);

// Check if query was successful
if(!$result) {
    die("Query failed: " . mysqli_error($con));
}

// Fetch statistics
$stats_query = "SELECT 
                COUNT(*) as total_customers,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_customers,
                SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) as inactive_customers,
                SUM(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as new_this_month
                FROM register 
                WHERE role = 'user'";

$stats_result = mysqli_query($con, $stats_query);

if(!$stats_result) {
    die("Stats query failed: " . mysqli_error($con));
}

$stats = mysqli_fetch_assoc($stats_result);
?>

<div class="content-wrapper">
    <div class="page-header">
        <h2>Customer Management</h2>
    </div>

    <!-- Customer Stats -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo isset($stats['total_customers']) ? $stats['total_customers'] : 0; ?></h3>
                    <p>Total Customers</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo isset($stats['active_customers']) ? $stats['active_customers'] : 0; ?></h3>
                    <p>Active Customers</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo isset($stats['new_this_month']) ? $stats['new_this_month'] : 0; ?></h3>
                    <p>New This Month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th>Registered Date</th>
                            <th>Actions</th>
                        </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <?php 
                                // FIXED: Correct path from admin folder to uploads folder
                                $profile_image_path = "../uploads/profiles/" . $row['profile_picture'];
                                
                                // Check if profile picture exists and is not empty
                                if(!empty($row['profile_picture']) && file_exists($profile_image_path)) {
                                    $img_src = $profile_image_path;
                                } else {
                                    $img_src = "../uploads/profiles/default-avatar.jpg";
                                }
                                ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td>
                                        <img src="<?php echo $img_src; ?>" 
                                            class="rounded-circle" 
                                            width="40" 
                                            height="40" 
                                            style="object-fit: cover;"
                                            alt="Profile Picture">
                                    </td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo $row['mobile']; ?></td>
                                    <td><?php echo ucfirst($row['gender']); ?></td>
                                    <td>
                                        <?php if($row['status'] == 'Active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" href="view-customer.php?id=<?php echo $row['id']; ?>">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No customers found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
include('includes/footer.php');
?>