<?php
session_start();
$page_title = "My Orders - BharatMart";
include 'includes/header.php';
require_once 'includes/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user orders
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-shopping-bag text-success"></i> My Orders
    </h2>
    
    <?php if (empty($orders)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h4>No Orders Yet</h4>
                <p class="text-muted">You haven't placed any orders yet.</p>
                <a href="products.php" class="btn btn-success mt-3">
                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($orders as $order): ?>
                <div class="col-12 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <h6 class="mb-1"><?php echo $order['order_number']; ?></h6>
                                    <small class="text-muted">
                                        <?php echo date('d M Y', strtotime($order['order_date'])); ?>
                                    </small>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted">Total:</span>
                                    <strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong>
                                </div>
                                <div class="col-md-2">
                                    <span class="text-muted">Payment:</span>
                                    <strong><?php echo $order['payment_method']; ?></strong>
                                </div>
                                <div class="col-md-2">
                                    <?php
                                    $status_class = '';
                                    switch($order['order_status']) {
                                        case 'pending':
                                            $status_class = 'bg-warning';
                                            break;
                                        case 'confirmed':
                                            $status_class = 'bg-info';
                                            break;
                                        case 'preparing':
                                            $status_class = 'bg-primary';
                                            break;
                                        case 'ready':
                                            $status_class = 'bg-info';
                                            break;
                                        case 'delivered':
                                            $status_class = 'bg-success';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'bg-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="order-details.php?order_id=<?php echo $order['id']; ?>" 
                                       class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                    <?php if ($order['order_status'] == 'pending'): ?>
                                        <button class="btn btn-outline-danger btn-sm" 
                                                onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) {
        return;
    }
    
    fetch('ajax/cancel_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_id=' + orderId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to cancel order');
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>