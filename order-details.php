<?php
session_start();
$page_title = "Order Details - BharatMart";
include 'includes/header.php';
require_once 'includes/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Location: My-orders.php');
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Fetch order details with user verification
$order_query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($con, $order_query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result);

// If order not found or doesn't belong to user
if (!$order) {
    header('Location: My-orders.php');
    exit();
}

// Fetch order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = mysqli_prepare($con, $items_query);
mysqli_stmt_bind_param($items_stmt, "i", $order_id);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);

$order_items = [];
$subtotal = 0;
while ($item = mysqli_fetch_assoc($items_result)) {
    $order_items[] = $item;
    $subtotal += $item['subtotal'];
}

// Calculate GST and delivery charge
$gst_amount = $order['gst_amount'] ?? ($subtotal * 0.05);
$delivery_charge = ($subtotal < 500) ? 50 : 0; // Free delivery for orders above ₹500

// Status badge class
$status_class = '';
$status_text = '';
switch($order['order_status']) {
    case 'pending':
        $status_class = 'bg-warning text-dark';
        $status_text = 'Pending';
        break;
    case 'confirmed':
        $status_class = 'bg-info';
        $status_text = 'Confirmed';
        break;
    case 'preparing':
        $status_class = 'bg-primary';
        $status_text = 'Preparing';
        break;
    case 'ready':
        $status_class = 'bg-info';
        $status_text = 'Ready for Delivery';
        break;
    case 'delivered':
        $status_class = 'bg-success';
        $status_text = 'Delivered';
        break;
    case 'cancelled':
        $status_class = 'bg-danger';
        $status_text = 'Cancelled';
        break;
    default:
        $status_class = 'bg-secondary';
        $status_text = ucfirst($order['order_status']);
}

// Payment status badge
$payment_status_class = $order['payment_status'] == 'paid' ? 'bg-success' : 'bg-warning text-dark';
$payment_status_text = $order['payment_status'] == 'paid' ? 'Paid' : 'Pending';

// Estimated delivery date (2-3 business days from order date)
$order_date = new DateTime($order['order_date']);
$delivery_date = clone $order_date;
$delivery_date->modify('+3 days');
// Skip weekends
while ($delivery_date->format('N') >= 6) {
    $delivery_date->modify('+1 day');
}
?>

<div class="container my-5">

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['order_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['order_message'];
            unset($_SESSION['order_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            <i class="fas fa-receipt text-success me-2"></i>Order Details
        </h2>

        <div>
            <?php if ($order['order_status'] == 'delivered'): ?>
                <button class="btn btn-outline-success me-2" onclick="window.print()">
                    <i class="fas fa-print me-1"></i>Print Invoice
                </button>
            <?php endif; ?>
            <a href="My-orders.php" class="btn btn-outline-dark">
                <i class="fas fa-arrow-left me-1"></i>Back to Orders
            </a>
        </div>
    </div>

    <!-- Order Info Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                    <p class="mb-1"><strong>Order Date:</strong> <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></p>
                    <p class="mb-0">
                        <strong>Order Status:</strong> 
                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    </p>
                </div>

                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <p class="mb-1">
                        <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?>
                    </p>
                    <p class="mb-1">
                        <strong>Payment Status:</strong> 
                        <span class="badge <?php echo $payment_status_class; ?>"><?php echo $payment_status_text; ?></span>
                    </p>
                    <?php if ($order['payment_method'] == 'Online' && $order['payment_status'] == 'paid'): ?>
                        <p class="mb-0 small text-muted">
                            <strong>Payment ID:</strong> <?php echo htmlspecialchars($order['razorpay_payment_id']); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($order['order_status'] != 'cancelled' && $order['order_status'] != 'delivered'): ?>
                        <p class="mb-0">
                            <strong>Estimated Delivery:</strong> 
                            <?php echo $delivery_date->format('d M Y'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Delivery Address -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">
                <i class="fas fa-map-marker-alt text-success me-2"></i>Delivery Address
            </h5>
            <p class="mb-0">
                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?><br>
                <strong>Mobile:</strong> <?php echo htmlspecialchars($order['customer_mobile']); ?>
            </p>
        </div>
    </div>

    <!-- Product List -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">
                <i class="fas fa-shopping-basket text-success me-2"></i>Ordered Items
            </h5>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Weight</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($item['weight']); ?></td>
                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td class="fw-semibold text-end">₹<?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Order Summary -->
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="row justify-content-end">
                <div class="col-md-5">

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>GST (5%)</span>
                        <span>₹<?php echo number_format($gst_amount, 2); ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Charge</span>
                        <?php if ($delivery_charge > 0): ?>
                            <span>₹<?php echo number_format($delivery_charge, 2); ?></span>
                        <?php else: ?>
                            <span class="text-success">FREE</span>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount</span>
                        <span>-₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span class="text-success">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>

                    <?php if ($order['order_status'] == 'pending'): ?>
                    <div class="mt-3">
                        <button class="btn btn-outline-danger w-100" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                            <i class="fas fa-times me-2"></i>Cancel Order
                        </button>
                    </div>
                    <?php endif; ?>

                    <?php if ($order['order_status'] == 'delivered'): ?>
                    <div class="mt-3">
                        <button class="btn btn-success w-100" onclick="reorderItems(<?php echo $order['id']; ?>)">
                            <i class="fas fa-redo me-2"></i>Order Again
                        </button>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

    <!-- Order Timeline -->
    <?php if ($order['order_status'] != 'cancelled'): ?>
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h5 class="fw-bold mb-4">Order Timeline</h5>
            
            <div class="timeline">
                <?php
                $timeline_steps = [
                    'pending' => ['icon' => 'clock', 'text' => 'Order Placed', 'date' => $order['order_date']],
                    'confirmed' => ['icon' => 'check-circle', 'text' => 'Order Confirmed', 'date' => null],
                    'preparing' => ['icon' => 'utensils', 'text' => 'Preparing', 'date' => null],
                    'ready' => ['icon' => 'box', 'text' => 'Ready for Delivery', 'date' => null],
                    'delivered' => ['icon' => 'truck', 'text' => 'Delivered', 'date' => $order['updated_date'] ?? null]
                ];
                
                $current_status_found = false;
                
                foreach ($timeline_steps as $status => $step):
                    $is_completed = false;
                    $is_current = false;
                    
                    if (!$current_status_found) {
                        if ($order['order_status'] == $status) {
                            $is_current = true;
                            $current_status_found = true;
                        } else {
                            $is_completed = true;
                        }
                    }
                    
                    $icon_class = $is_completed || $is_current ? 'text-success' : 'text-muted';
                    $line_class = $is_completed ? 'bg-success' : 'bg-light';
                ?>
                <div class="timeline-item d-flex mb-3">
                    <div class="timeline-icon me-3">
                        <i class="fas fa-<?php echo $step['icon']; ?> fa-lg <?php echo $icon_class; ?>"></i>
                    </div>
                    <div class="timeline-content flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <span class="<?php echo $is_completed || $is_current ? 'fw-bold' : 'text-muted'; ?>">
                                <?php echo $step['text']; ?>
                            </span>
                            <?php if ($is_completed || $is_current): ?>
                                <small class="text-muted">
                                    <?php 
                                    if ($step['date']) {
                                        echo date('d M Y, h:i A', strtotime($step['date']));
                                    } elseif ($is_current) {
                                        echo 'In Progress';
                                    } else {
                                        echo 'Completed';
                                    }
                                    ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<style>
.timeline-item {
    position: relative;
}
.timeline-icon {
    width: 40px;
    text-align: center;
}
@media print {
    .btn, .navbar, footer, .timeline {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>

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
    })
    .catch(error => {
        alert('Error cancelling order. Please try again.');
    });
}

function reorderItems(orderId) {
    if (!confirm('Add all items from this order to cart?')) {
        return;
    }
    
    fetch('ajax/reorder.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_id=' + orderId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'cart.php';
        } else {
            alert(data.message || 'Failed to reorder items');
        }
    })
    .catch(error => {
        alert('Error processing request. Please try again.');
    });
}
</script>

<?php include 'includes/footer.php'; ?>