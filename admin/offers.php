<?php 
include('includes/sidebar.php');
include('includes/header.php');
include('../includes/db_config.php');

// Fetch offers from database
$query = "SELECT * FROM offers ORDER BY created_at DESC";
$result = mysqli_query($con, $query);

// Get statistics with proper date handling
$current_date = date('Y-m-d H:i:s');

$active_query = "SELECT COUNT(*) as count FROM offers 
                 WHERE status='active' 
                 AND valid_from <= '$current_date' 
                 AND valid_to >= '$current_date'";
$active_result = mysqli_query($con, $active_query);
$active_count = mysqli_fetch_assoc($active_result)['count'] ?? 0;

$expiring_query = "SELECT COUNT(*) as count FROM offers 
                   WHERE status='active' 
                   AND valid_to BETWEEN '$current_date' 
                   AND DATE_ADD('$current_date', INTERVAL 7 DAY)";
$expiring_result = mysqli_query($con, $expiring_query);
$expiring_count = mysqli_fetch_assoc($expiring_result)['count'] ?? 0;

$total_used = "SELECT COALESCE(SUM(used_count), 0) as total FROM offers";
$total_result = mysqli_query($con, $total_used);
$total_used_count = mysqli_fetch_assoc($total_result)['total'] ?? 0;

// Check for success/error messages
$message = '';
$message_type = '';
if (isset($_SESSION['offer_message'])) {
    $message = $_SESSION['offer_message'];
    $message_type = $_SESSION['offer_message_type'] ?? 'success';
    unset($_SESSION['offer_message']);
    unset($_SESSION['offer_message_type']);
}
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tags text-success me-2"></i>Offers & Promotions</h2>
        <a href="create-offer.php" class="btn btn-success">
            <i class="fas fa-plus-circle me-2"></i>Create New Offer
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-tags fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?php echo $active_count; ?></h3>
                            <p class="text-muted mb-0">Active Offers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?php echo $expiring_count; ?></h3>
                            <p class="text-muted mb-0">Expiring Soon</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-chart-line fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?php echo number_format($total_used_count); ?></h3>
                            <p class="text-muted mb-0">Total Redemptions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-success filter-btn active" data-filter="all">
                    All Offers
                </button>
                <button type="button" class="btn btn-outline-success filter-btn" data-filter="active">
                    Active
                </button>
                <button type="button" class="btn btn-outline-success filter-btn" data-filter="upcoming">
                    Upcoming
                </button>
                <button type="button" class="btn btn-outline-success filter-btn" data-filter="expired">
                    Expired
                </button>
            </div>
        </div>
    </div>

    <!-- Offers Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="offersTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="12%">Coupon Code</th>
                            <th width="18%">Offer Title</th>
                            <th width="10%">Discount</th>
                            <th width="12%">Valid From</th>
                            <th width="12%">Valid To</th>
                            <th width="10%">Usage</th>
                            <th width="10%">Status</th>
                            <th width="11%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sr_no = 1;
                        if(mysqli_num_rows($result) > 0):
                            while($offer = mysqli_fetch_assoc($result)): 
                                $now = date('Y-m-d H:i:s');
                                $status_class = 'secondary';
                                $status_text = 'Inactive';
                                $row_class = '';
                                
                                if($offer['status'] == 'active') {
                                    if($offer['valid_from'] <= $now && $offer['valid_to'] >= $now) {
                                        $status_class = 'success';
                                        $status_text = 'Active';
                                        $row_class = 'table-success';
                                    } elseif($offer['valid_from'] > $now) {
                                        $status_class = 'info';
                                        $status_text = 'Upcoming';
                                        $row_class = 'table-info';
                                    } elseif($offer['valid_to'] < $now) {
                                        $status_class = 'danger';
                                        $status_text = 'Expired';
                                        $row_class = 'table-danger';
                                    }
                                } else {
                                    $row_class = 'table-secondary';
                                }
                                
                                $discount_display = $offer['discount_type'] == 'percentage' ? 
                                    $offer['discount_value'].'% OFF' : 
                                    '₹'.$offer['discount_value'].' OFF';
                                    
                                $usage_text = $offer['used_count'] . ' / ' . 
                                    ($offer['usage_limit'] > 0 ? $offer['usage_limit'] : '∞');
                                    
                                // Usage percentage for progress bar
                                $usage_percent = 0;
                                if($offer['usage_limit'] > 0) {
                                    $usage_percent = ($offer['used_count'] / $offer['usage_limit']) * 100;
                                }
                        ?>
                        <tr class="offer-row <?php echo $row_class; ?>" 
                            data-status="<?php echo strtolower($status_text); ?>">
                            <td><?php echo $sr_no++; ?></td>
                            <td>
                                <span class="fw-bold text-primary">
                                    <?php echo htmlspecialchars($offer['coupon_code']); ?>
                                </span>
                                <button class="btn btn-link btn-sm p-0 ms-1" 
                                        onclick="copyCoupon('<?php echo $offer['coupon_code']; ?>')"
                                        title="Copy Code">
                                    <i class="far fa-copy"></i>
                                </button>
                            </td>
                            <td><?php echo htmlspecialchars($offer['offer_title']); ?></td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo $discount_display; ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($offer['valid_from'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($offer['valid_to'])); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2 small"><?php echo $usage_text; ?></span>
                                    <?php if($offer['usage_limit'] > 0): ?>
                                    <div class="progress flex-grow-1" style="height: 5px;">
                                        <div class="progress-bar bg-success" 
                                             style="width: <?php echo $usage_percent; ?>%"></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit-offer.php?id=<?php echo $offer['id']; ?>" 
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- Replace the existing delete button with this -->
<button class="btn btn-outline-danger btn-delete" 
        data-id="<?php echo $offer['id']; ?>" 
        data-title="<?php echo htmlspecialchars($offer['offer_title']); ?>"
        title="Delete">
    <i class="fas fa-trash"></i>
</button>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                <h5>No Offers Found</h5>
                                <p class="text-muted">Create your first offer to attract customers!</p>
                                <a href="create-offer.php" class="btn btn-success mt-2">
                                    <i class="fas fa-plus me-2"></i>Create Offer
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
// Copy coupon code to clipboard
function copyCoupon(code) {
    navigator.clipboard.writeText(code).then(function() {
        // Show toast notification
        showToast('Coupon code copied: ' + code, 'success');
    }).catch(function() {
        // Fallback for older browsers
        prompt('Copy this coupon code:', code);
    });
}

// Delete offer with SweetAlert style confirmation
document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for delete buttons
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.btn-delete');
        if (!deleteBtn) return;
        
        e.preventDefault();
        
        const offerId = deleteBtn.dataset.id;
        const offerTitle = deleteBtn.dataset.title;
        
        if (confirm('Are you sure you want to delete the offer "' + offerTitle + '"?\nThis action cannot be undone.')) {
            deleteOfferAjax(offerId, deleteBtn);
        }
    });
});

function deleteOfferAjax(offerId, button) {
    // Show loading state
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    // Send AJAX request
    fetch('delete-offer-ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + offerId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the row from table
            const row = button.closest('tr');
            row.style.transition = 'opacity 0.3s';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
            
            // Show success message
            showToast(data.message, 'success');
            
            // Update statistics if needed
            if (data.stats) {
                updateStats(data.stats);
            }
        } else {
            // Show error message
            showToast(data.message, 'danger');
            
            // Restore button
            button.innerHTML = originalHtml;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the offer', 'danger');
        
        // Restore button
        button.innerHTML = originalHtml;
        button.disabled = false;
    });
}

function showToast(message, type) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.style.animation = 'slideIn 0.3s ease';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function updateStats(stats) {
    // Update active offers count
    if (stats.active_count !== undefined) {
        document.querySelector('.bg-success .flex-grow-1 h3').textContent = stats.active_count;
    }
    
    // Update expiring count
    if (stats.expiring_count !== undefined) {
        document.querySelector('.bg-warning .flex-grow-1 h3').textContent = stats.expiring_count;
    }
    
    // Update total redemptions
    if (stats.total_used !== undefined) {
        document.querySelector('.bg-info .flex-grow-1 h3').textContent = stats.total_used;
    }
}

// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const rows = document.querySelectorAll('.offer-row');
        
        rows.forEach(row => {
            if (filter === 'all') {
                row.style.display = '';
            } else {
                const status = row.dataset.status;
                row.style.display = (status === filter) ? '' : 'none';
            }
        });
    });
});
</script>



<style>
.offer-row {
    transition: background-color 0.3s ease;
}
.offer-row:hover {
    background-color: #f8f9fa !important;
}
.progress {
    background-color: #e9ecef;
}
.btn-group .btn {
    border-radius: 0;
}
.btn-group .btn:first-child {
    border-radius: 0.375rem 0 0 0.375rem;
}
.btn-group .btn:last-child {
    border-radius: 0 0.375rem 0.375rem 0;
}
</style>

<?php include('includes/footer.php'); ?>