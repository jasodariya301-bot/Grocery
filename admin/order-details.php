<?php 
include('includes/sidebar.php');
include('includes/header.php');
require_once '../includes/db_config.php';

// Check order id
if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_GET['id']);

// Fetch order details
$order_query = "SELECT * FROM orders WHERE id = ?";
$stmt = mysqli_prepare($con, $order_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Fetch order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = mysqli_prepare($con, $items_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2>Order Details - <?php echo $order['order_number']; ?></h2>
        <a href="orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    <div class="row">
        <!-- LEFT SIDE -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p class="mb-1"><strong>Mobile:</strong> <?php echo $order['customer_mobile']; ?></p>
                            <p class="mb-0"><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
                        </div>

                        <div class="col-md-6">
                            <h6>Order Info</h6>
                            <p class="mb-1"><strong>Order Date:</strong> <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></p>
                            <p class="mb-1"><strong>Payment Method:</strong> <?php echo $order['payment_method']; ?></p>
                            <p class="mb-1"><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
                            <p class="mb-1">
                                <strong>Order Status:</strong> 
                                <?php
                                $status_class = '';
                                switch($order['order_status']) {
                                    case 'pending': $status_class = 'bg-warning'; break;
                                    case 'confirmed': $status_class = 'bg-info'; break;
                                    case 'preparing': $status_class = 'bg-primary'; break;
                                    case 'ready': $status_class = 'bg-info'; break;
                                    case 'delivered': $status_class = 'bg-success'; break;
                                    case 'cancelled': $status_class = 'bg-danger'; break;
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Weight/L</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo $item['weight']; ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                    <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td>₹<?php echo number_format($order['subtotal'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>GST (18%):</strong></td>
                                    <td>₹<?php echo number_format($order['gst_amount'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                                    <td><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE STATUS PANEL -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Update Order Status</h6>
                </div>
                <div class="card-body">
                    <form action="update-order-status.php" method="POST" id="statusForm">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Select Status</label>
                            <select name="status" class="form-select" required>
                                <option value="">Choose status...</option>
                                <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $order['order_status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="preparing" <?php echo $order['order_status'] == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="ready" <?php echo $order['order_status'] == 'ready' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Status
                            </button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i> 
                        Changing status to "Cancelled" will restore product stock automatically.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('statusForm').addEventListener('submit', function(e) {
    var status = this.querySelector('select[name="status"]').value;
    if (status === 'cancelled') {
        if (!confirm('Are you sure you want to cancel this order? Product stock will be restored.')) {
            e.preventDefault();
        }
    }
});
</script>

<?php include('includes/footer.php'); ?>