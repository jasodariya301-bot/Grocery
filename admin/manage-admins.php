<?php
require_once 'auth_check.php';
include '../includes/db_config.php';

// Handle status update and deletion BEFORE any output
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($con, $_GET['id']);
    if ($_GET['action'] == 'activate') {
        mysqli_query($con, "UPDATE register SET status = 'Active' WHERE id = $id");
        $_SESSION['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i> Admin activated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>';
    } elseif ($_GET['action'] == 'deactivate') {
        mysqli_query($con, "UPDATE register SET status = 'Inactive' WHERE id = $id");
        $_SESSION['message'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <i class="fas fa-ban me-2"></i> Admin deactivated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>';
    } elseif ($_GET['action'] == 'delete' && $_SESSION['admin_id'] != $id) {
        mysqli_query($con, "DELETE FROM register WHERE id = $id AND role != 'super_admin'");
        $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-trash me-2"></i> Admin deleted successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>';
    }
    
    // Redirect back to manage-admins.php
    header("Location: manage-admins.php");
    exit();
}

// Now include header and sidebar after processing redirects
include('includes/sidebar.php');
include('includes/header.php');

$message = '';

// Check for session message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Handle add admin
if (isset($_POST['add_admin'])) {
    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $mobile = mysqli_real_escape_string($con, $_POST['mobile']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    
    $check_email = "SELECT id FROM register WHERE email = '$email'";
    $result = mysqli_query($con, $check_email);
    
    if (mysqli_num_rows($result) > 0) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> Email already exists!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    } else {
        $token = md5(uniqid() . $email . time());
        $insert = "INSERT INTO register (first_name, last_name, email, password, mobile, gender, role, status, token) 
                   VALUES ('$first_name', '$last_name', '$email', '$password', '$mobile', '$gender', '$role', 'Active', '$token')";
        
        if (mysqli_query($con, $insert)) {
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> Admin added successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
        } else {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> Error: ' . mysqli_error($con) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
        }
    }
}

// Get statistics
$total_query = mysqli_query($con, "SELECT COUNT(*) as total FROM register WHERE role IN ('admin', 'super_admin')");
$total = mysqli_fetch_assoc($total_query)['total'];

$active_query = mysqli_query($con, "SELECT COUNT(*) as active FROM register WHERE role IN ('admin', 'super_admin') AND status = 'Active'");
$active = mysqli_fetch_assoc($active_query)['active'];

$inactive_query = mysqli_query($con, "SELECT COUNT(*) as inactive FROM register WHERE role IN ('admin', 'super_admin') AND status = 'Inactive'");
$inactive = mysqli_fetch_assoc($inactive_query)['inactive'];

$super_admin_query = mysqli_query($con, "SELECT COUNT(*) as super_admin FROM register WHERE role = 'super_admin'");
$super_admin = mysqli_fetch_assoc($super_admin_query)['super_admin'];

// Fetch all admins
$query = "SELECT * FROM register WHERE role IN ('admin', 'super_admin') ORDER BY 
          CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END, id DESC";
$admins = mysqli_query($con, $query);
?>



<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-user-shield me-2"></i>Manage Administrators</h2>
            <p class="text-muted">Add, edit, and manage admin users</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="fas fa-plus me-2"></i>Add New Admin
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3><?php echo $total; ?></h3>
                    <p class="mb-0 text-muted">Total Admins</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3><?php echo $active; ?></h3>
                    <p class="mb-0 text-muted">Active Admins</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <div>
                    <h3><?php echo $inactive; ?></h3>
                    <p class="mb-0 text-muted">Inactive Admins</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filter-section">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Filter by Role</label>
                <select class="form-select" id="role-filter">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                   
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Filter by Status</label>
                <select class="form-select" id="status-filter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" id="search-admin" placeholder="Search by name or email...">
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php echo $message; ?>

    <!-- Admins Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">All Administrators</h5>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($admins) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="adminsTable">
                        <thead class="table-light">
                            32
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($admin = mysqli_fetch_assoc($admins)) { 
                                // Safely get gender with fallback
                                $gender = isset($admin['gender']) && !empty($admin['gender']) ? $admin['gender'] : 'Not specified';
                                $first_name = isset($admin['first_name']) ? $admin['first_name'] : '';
                                $last_name = isset($admin['last_name']) ? $admin['last_name'] : '';
                                $full_name = trim($first_name . ' ' . $last_name);
                                $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
                                if (empty(trim($initials))) {
                                    $initials = 'A';
                                }
                            ?>
                            <tr data-role="<?php echo strtolower($admin['role']); ?>" 
                                data-status="<?php echo strtolower($admin['status']); ?>"
                                data-name="<?php echo strtolower($full_name); ?>"
                                data-email="<?php echo strtolower($admin['email']); ?>">
                                <td><strong>#<?php echo $admin['id']; ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            <?php echo $initials; ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($full_name); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo ucfirst($gender); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo $admin['email']; ?>" class="text-decoration-none">
                                        <i class="fas fa-envelope me-1"></i>
                                        <?php echo $admin['email']; ?>
                                    </a>
                                </td>
                                <td>
                                    <i class="fas fa-phone me-1"></i>
                                    <?php echo isset($admin['mobile']) ? $admin['mobile'] : 'N/A'; ?>
                                </td>
                                <td>
                                    <span class="role-badge role-<?php echo $admin['role']; ?>">
                                        <i class="fas fa-<?php echo $admin['role'] == 'super_admin' ? 'crown' : 'user-shield'; ?> me-1"></i>
                                        <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($admin['status']); ?>">
                                        <i class="fas fa-<?php echo $admin['status'] == 'Active' ? 'check-circle' : 'clock'; ?> me-1"></i>
                                        <?php echo $admin['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                        <small><?php echo isset($admin['created_at']) ? date('M d, Y', strtotime($admin['created_at'])) : 'N/A'; ?></small>
                                    </div>
                                    <div>
                                        <i class="fas fa-clock me-1 text-muted"></i>
                                        <small class="text-muted"><?php echo isset($admin['created_at']) ? date('h:i A', strtotime($admin['created_at'])) : 'N/A'; ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                            <?php if ($admin['status'] == 'Active'): ?>
                                                <a href="?action=deactivate&id=<?php echo $admin['id']; ?>" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Deactivate Admin"
                                                   onclick="return confirm('Are you sure you want to deactivate this admin?')">
                                                    <i class="fas fa-ban"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=activate&id=<?php echo $admin['id']; ?>" 
                                                   class="btn btn-sm btn-success"
                                                   title="Activate Admin"
                                                   onclick="return confirm('Are you sure you want to activate this admin?')">
                                                    <i class="fas fa-check-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($admin['role'] != 'super_admin'): ?>
                                                <a href="?action=delete&id=<?php echo $admin['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   title="Delete Admin"
                                                   onclick="return confirm('Are you sure you want to delete this admin? This action cannot be undone!')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-user me-1"></i>Current
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                    <h5>No Administrators Found</h5>
                    <p class="text-muted">Click on "Add New Admin" to create your first administrator.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Add New Administrator
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile <span class="text-danger">*</span></label>
                            <input type="text" name="mobile" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-control" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <?php if ($_SESSION['admin_role'] == 'super_admin'): ?>
                                <option value="super_admin">Super Admin</option>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">
                            <?php if ($_SESSION['admin_role'] == 'super_admin'): ?>
                                Super Admin has full access to all features including managing other admins.
                            <?php else: ?>
                                You need Super Admin privileges to create other Super Admins.
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" name="add_admin" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Add Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for filtering -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$(document).ready(function() {
    // Filter by role
    $('#role-filter').change(function() {
        filterTable();
    });
    
    // Filter by status
    $('#status-filter').change(function() {
        filterTable();
    });
    
    // Search admins
    $('#search-admin').on('keyup', function() {
        filterTable();
    });
    
    function filterTable() {
        var role = $('#role-filter').val().toLowerCase();
        var status = $('#status-filter').val().toLowerCase();
        var search = $('#search-admin').val().toLowerCase();
        
        $('#adminsTable tbody tr').each(function() {
            var row = $(this);
            var rowRole = row.data('role');
            var rowStatus = row.data('status');
            var rowName = row.data('name');
            var rowEmail = row.data('email');
            
            var roleMatch = role === '' || rowRole === role;
            var statusMatch = status === '' || rowStatus === status;
            var searchMatch = search === '' || rowName.includes(search) || rowEmail.includes(search);
            
            if (roleMatch && statusMatch && searchMatch) {
                row.show();
            } else {
                row.hide();
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php include 'includes/footer.php'; ?>