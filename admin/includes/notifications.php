<?php
// admin/includes/notifications.php

function displayLowStockAlert() {
    global $con;
    
    // Get low stock products
    $query = "SELECT id, product_name, stock, category FROM products 
              WHERE stock <= 10 AND stock > 0 AND status = 'in-stock' 
              ORDER BY stock ASC";
    $result = mysqli_query($con, $query);
    
    if (mysqli_num_rows($result) == 0) {
        return;
    }
    
    $count = mysqli_num_rows($result);
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    ?>
    
    <!-- Low Stock Alert Bar -->
    <div class="low-stock-alert">
        <div class="alert alert-warning alert-dismissible fade show m-0 rounded-0 border-0" role="alert">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong>⚠️ Low Stock Alert!</strong> 
                        <?php if ($count == 1): ?>
                            <strong><?php echo $products[0]['product_name']; ?></strong> has only 
                            <span class="badge bg-danger"><?php echo $products[0]['stock']; ?></span> units left.
                        <?php else: ?>
                            <strong><?php echo $count; ?> products</strong> are running low on stock.
                        <?php endif; ?>
                    </div>
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-warning me-2" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#lowStockDetails" aria-expanded="false">
                            <i class="fas fa-list"></i> View Details
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                
                <!-- Expandable Details -->
                <div class="collapse mt-3" id="lowStockDetails">
                    <div class="card card-body bg-light">
                        <h6 class="mb-3">Products Needing Restock:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Current Stock</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                        <td><?php echo ucfirst($product['category']); ?></td>
                                        <td>
                                            <?php if ($product['stock'] <= 3): ?>
                                                <span class="badge bg-danger"><?php echo $product['stock']; ?> units</span>
                                            <?php elseif ($product['stock'] <= 5): ?>
                                                <span class="badge bg-warning text-dark"><?php echo $product['stock']; ?> units</span>
                                            <?php else: ?>
                                                <span class="badge bg-info"><?php echo $product['stock']; ?> units</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($product['stock'] <= 3): ?>
                                                <span class="text-danger"><i class="fas fa-circle"></i> Critical</span>
                                            <?php elseif ($product['stock'] <= 5): ?>
                                                <span class="text-warning"><i class="fas fa-circle"></i> Low</span>
                                            <?php else: ?>
                                                <span class="text-info"><i class="fas fa-circle"></i> Moderate</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Update Stock
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <a href="products.php?filter=low_stock" class="btn btn-warning">
                                <i class="fas fa-boxes"></i> Manage All Low Stock Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .low-stock-alert .alert {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            transform: translateY(-100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .low-stock-alert .table td {
        vertical-align: middle;
    }
    </style>
    
    <?php
}
?>