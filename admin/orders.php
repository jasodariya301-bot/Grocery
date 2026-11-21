<?php 
include('includes/sidebar.php');
include('includes/header.php');
require_once '../includes/db_config.php';

// Fetch orders with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query with filters
$where_clauses = [];
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = mysqli_real_escape_string($con, $_GET['status']);
    $where_clauses[] = "o.order_status = '$status'";
}
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $date_from = mysqli_real_escape_string($con, $_GET['date_from']);
    $where_clauses[] = "DATE(o.order_date) >= '$date_from'";
}
if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $date_to = mysqli_real_escape_string($con, $_GET['date_to']);
    $where_clauses[] = "DATE(o.order_date) <= '$date_to'";
}

$where_sql = empty($where_clauses) ? '' : 'WHERE ' . implode(' AND ', $where_clauses);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM orders o $where_sql";
$count_result = mysqli_query($con, $count_query);
$total_orders = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_orders / $limit);

// Fetch orders
$query = "SELECT o.*, 
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items 
          FROM orders o 
          $where_sql 
          ORDER BY o.order_date DESC 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($con, $query);
?>

<div class="content-wrapper">
    <div class="page-header">
        <h2>Order Management</h2>
    </div>

    <!-- Order Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="orders.php" class="row g-3">
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo isset($_GET['status']) && $_GET['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="preparing" <?php echo isset($_GET['status']) && $_GET['status'] == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                        <option value="ready" <?php echo isset($_GET['status']) && $_GET['status'] == 'ready' ? 'selected' : ''; ?>>Ready for Pickup</option>
                        <option value="delivered" <?php echo isset($_GET['status']) && $_GET['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date_from" placeholder="From Date" 
                           value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date_to" placeholder="To Date"
                           value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ''; ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="orders.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Sr no</th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sr_no = $offset + 1;
                        while ($order = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?php echo $sr_no++; ?></td>
                            <td><?php echo $order['order_number']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                <small class="text-muted"><?php echo $order['customer_mobile']; ?></small>
                            </td>
                            <td><?php echo $order['total_items']; ?> items</td>
                            <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo $order['payment_method']; ?></td>
                            <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                            <td>
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
                            </td>
                            <td>
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if (mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">No orders found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>