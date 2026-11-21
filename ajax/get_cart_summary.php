<?php
session_start();
require_once '../includes/db_config.php';
require_once '../includes/cart_functions.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo '<p class="text-danger">Please login to continue</p>';
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($user_id);
$totals = calculateCartTotals($cart_items);

if (empty($cart_items)) {
    echo '<p class="text-muted">Your cart is empty</p>';
    exit();
}
?>

<div id="cart-totals-data" data-totals='<?php echo json_encode($totals); ?>'></div>

<div class="cart-items-summary mb-3">
    <?php foreach ($cart_items as $item): ?>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="d-flex align-items-center">
            <?php if(!empty($item['image']) && file_exists("../uploads/products/".$item['image'])): ?>
                <img src="uploads/products/<?php echo $item['image']; ?>" 
                     width="40" height="40" style="object-fit: cover;" class="rounded me-2">
            <?php endif; ?>
            <div>
                <small class="fw-medium"><?php echo htmlspecialchars($item['product_name']); ?></small>
                <small class="text-muted d-block"><?php echo $item['quantity']; ?> x ₹<?php echo number_format($item['price'], 2); ?></small>
            </div>
        </div>
        <small class="fw-medium">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></small>
    </div>
    <?php endforeach; ?>
</div>

<div class="d-flex justify-content-between mb-2">
    <span>Subtotal</span>
    <span>₹<?php echo number_format($totals['subtotal'], 2); ?></span>
</div>

<div class="d-flex justify-content-between mb-2">
    <span>GST (18%)</span>
    <span>₹<?php echo number_format($totals['gst_amount'], 2); ?></span>
</div>

<?php if (isset($_SESSION['applied_coupon'])): 
    $coupon = $_SESSION['applied_coupon'];
    $discount = $coupon['discount'];
?>
<div class="d-flex justify-content-between mb-2 text-success">
    <span>
        Discount 
        <span class="badge bg-success"><?php echo $coupon['code']; ?></span>
    </span>
    <span>-₹<?php echo number_format($discount, 2); ?></span>
</div>
<?php 
    $totals['grand_total'] = $totals['grand_total'] - $discount;
endif; 
?>

<hr>

<div class="d-flex justify-content-between fw-bold text-success fs-5">
    <span>Grand Total</span>
    <span>₹<?php echo number_format($totals['grand_total'], 2); ?></span>
</div>