<?php
session_start();
require_once 'includes/db_config.php';

$page_title = "Order Confirmation - BharatMart";
include 'includes/header.php';

// Get order ID from URL or session
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id && isset($_SESSION['last_order'])) {
    $order_id = $_SESSION['last_order']['order_id'];
}

if (!$order_id) {
    header('Location: index.php');
    exit();
}

// Fetch order details
$query = "SELECT * FROM orders WHERE id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header('Location: index.php');
    exit();
}

// Fetch order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = mysqli_prepare($con, $items_query);
mysqli_stmt_bind_param($items_stmt, "i", $order_id);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);

unset($_SESSION['last_order']);
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <?php if ($order['payment_status'] == 'paid'): ?>
                            <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                        <?php else: ?>
                            <i class="fas fa-clock text-warning" style="font-size: 80px;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <h2 class="mb-3">Thank You for Your Order!</h2>
                    <p class="text-muted mb-4">
                        Your order has been placed successfully. You will receive a confirmation email shortly.
                    </p>
                    
                    <div class="alert alert-success mb-4">
                        <h5 class="mb-0">Order Number: <?php echo $order['order_number']; ?></h5>
                        <small>Total Amount: ₹<?php echo number_format($order['total_amount'], 2); ?></small>
                        <br>
                        <small>Payment Method: <?php echo $order['payment_method']; ?></small>
                        <?php if ($order['payment_method'] == 'Online'): ?>
                            <br>
                            <small class="text-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                Payment Status: <?php echo ucfirst($order['payment_status']); ?>
                            </small>
                            <?php if ($order['razorpay_payment_id']): ?>
                                <br>
                                <small>Payment ID: <?php echo $order['razorpay_payment_id']; ?></small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="bg-light rounded p-3 mb-4 text-start">
                        <h6 class="mb-3">Order Items</h6>
                        <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo $item['quantity']; ?> x <?php echo htmlspecialchars($item['product_name']); ?></span>
                            <span>₹<?php echo number_format($item['subtotal'], 2); ?></span>
                        </div>
                        <?php endwhile; ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($order['subtotal'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>GST</span>
                            <span>₹<?php echo number_format($order['gst_amount'], 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <a href="My-orders.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-list me-2"></i>View My Orders
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="products.php" class="btn btn-success w-100">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="fas fa-phone me-1"></i> Need help? Call us at: +91 1234567890
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>