<?php 
include('includes/sidebar.php');
include('includes/header.php');
include('../includes/db_config.php');

// Fetch all products from database
$query = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($con, $query);

// Get statistics
$total_query = mysqli_query($con, "SELECT COUNT(*) as total FROM products");
$total = mysqli_fetch_assoc($total_query)['total'];

$in_stock_query = mysqli_query($con, "SELECT COUNT(*) as instock FROM products WHERE status='in-stock'");
$in_stock = mysqli_fetch_assoc($in_stock_query)['instock'];

$out_stock_query = mysqli_query($con, "SELECT COUNT(*) as outstock FROM products WHERE status='out-of-stock'");
$out_stock = mysqli_fetch_assoc($out_stock_query)['outstock'];
?>
<!-- Low Stock Alert -->
<?php
require_once 'includes/stock_functions.php';
$low_stock_products = getLowStockProducts(10);

if (!empty($low_stock_products)):
?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Low Stock Alert!</strong> The following products are running low:
    <ul class="mb-0 mt-2">
        <?php foreach(array_slice($low_stock_products, 0, 5) as $product): ?>
        <li>
            <?php echo htmlspecialchars($product['product_name']); ?> - 
            <strong><?php echo $product['stock']; ?> units left</strong>
            <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning ms-2">
                Update Stock
            </a>
        </li>
        <?php endforeach; ?>
        <?php if(count($low_stock_products) > 5): ?>
        <li>...and <?php echo count($low_stock_products) - 5; ?> more products</li>
        <?php endif; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<!-- <style>
    table img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        transition: transform 0.3s ease;
    }
    table img:hover {
        transform: scale(1.5);
        cursor: pointer;
    }
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .stat-primary .stat-icon { background: #e3f2fd; color: #1976d2; }
    .stat-success .stat-icon { background: #e8f5e9; color: #388e3c; }
    .stat-warning .stat-icon { background: #fff3e0; color: #f57c00; }
</style> -->

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Product Management</h2>
            <p class="text-muted">Manage your grocery products inventory</p>
        </div>
        <a href="add-product.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div>
                    <h3><?php echo $total; ?></h3>
                    <p class="mb-0 text-muted">Total Products</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3><?php echo $in_stock; ?></h3>
                    <p class="mb-0 text-muted">In Stock</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <h3><?php echo $out_stock; ?></h3>
                    <p class="mb-0 text-muted">Out of Stock</p>
                </div>
            </div>
        </div>
    </div>
<!-- Add this to the filter section in admin/products.php -->
<div class="col-md-3">
    <select class="form-select" id="stock-filter">
        <option value="">All Stock Levels</option>
        <option value="out">Out of Stock</option>
        <option value="critical">Critical (≤5)</option>
        <option value="low">Low Stock (≤10)</option>
        <option value="healthy">Healthy Stock (>10)</option>
    </select>
</div>
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" id="category-filter">
                        <option value="">All Categories</option>
                        <?php
                        $cat_query = "SELECT DISTINCT category FROM products ORDER BY category";
                        $cat_result = mysqli_query($con, $cat_query);
                        while($cat = mysqli_fetch_assoc($cat_result)) {
                            echo "<option value='".$cat['category']."'>".ucfirst($cat['category'])."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="status-filter">
                        <option value="">All Status</option>
                        <option value="in-stock">In Stock</option>
                        <option value="out-of-stock">Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="search-product" placeholder="Search products...">
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Products</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="productsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Weight/L</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result) > 0) { ?>
                            <?php while($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <?php if(!empty($row['image']) && file_exists("../uploads/products/".$row['image'])) { ?>
                                        <img src="../uploads/products/<?php echo $row['image']; ?>" 
                                             alt="<?php echo $row['product_name']; ?>">
                                    <?php } else { ?>
                                        <img src="../assets/images/products/default-product.jpg" 
                                             alt="Default Image">
                                    <?php } ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['product_name']); ?></strong>
                                    <?php if(!empty($row['description'])) { ?>
                                        <br><small class="text-muted"><?php echo substr($row['description'], 0, 30); ?>...</small>
                                    <?php } ?>
                                </td>
                                <td><?php echo ucfirst($row['category']); ?></td>
                                <td><strong>₹<?php echo number_format($row['price'], 2); ?></strong></td>
                                <td><?php echo $row['weight']; ?></td>
                                <td>
                                    <?php if($row['stock'] > 10) { ?>
                                        <span class="badge bg-success"><?php echo $row['stock']; ?> in stock</span>
                                    <?php } elseif($row['stock'] > 0) { ?>
                                        <span class="badge bg-warning"><?php echo $row['stock']; ?> left</span>
                                    <?php } else { ?>
                                        <span class="badge bg-danger">Out of stock</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if($row['status'] == 'in-stock') { ?>
                                        <span class="badge bg-success">In Stock</span>
                                    <?php } else { ?>
                                        <span class="badge bg-danger">Out of Stock</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <a href="edit-product.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete-product.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Are you sure you want to delete this product?')"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <h5>No Products Found</h5>
                                    <p class="text-muted">Click on "Add New Product" to create your first product.</p>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for filtering -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Filter by category
    $('#category-filter').change(function() {
        filterTable();
    });
    
    // Filter by status
    $('#status-filter').change(function() {
        filterTable();
    });
    
    // Search products
    $('#search-product').on('keyup', function() {
        filterTable();
    });
    
    function filterTable() {
        var category = $('#category-filter').val().toLowerCase();
        var status = $('#status-filter').val().toLowerCase();
        var search = $('#search-product').val().toLowerCase();
        
        $('#productsTable tbody tr').each(function() {
            var row = $(this);
            var productName = row.find('td:eq(2)').text().toLowerCase();
            var productCategory = row.find('td:eq(3)').text().toLowerCase();
            var productStatus = row.find('td:eq(7)').text().toLowerCase().trim();
            
            var categoryMatch = category === '' || productCategory.includes(category);
            var statusMatch = status === '' || productStatus.includes(status.replace('-', ' '));
            var searchMatch = search === '' || productName.includes(search);
            
            if (categoryMatch && statusMatch && searchMatch) {
                row.show();
            } else {
                row.hide();
            }
        });
    }
});
// Add to existing filter function
$('#stock-filter').change(function() {
    filterTable();
});

function filterTable() {
    var category = $('#category-filter').val().toLowerCase();
    var status = $('#status-filter').val().toLowerCase();
    var stockFilter = $('#stock-filter').val();
    var search = $('#search-product').val().toLowerCase();
    
    $('#productsTable tbody tr').each(function() {
        var row = $(this);
        var stockText = row.find('td:eq(6)').text();
        var stock = parseInt(stockText.match(/\d+/)) || 0;
        
        var stockMatch = true;
        if (stockFilter === 'out') {
            stockMatch = stock === 0;
        } else if (stockFilter === 'critical') {
            stockMatch = stock > 0 && stock <= 5;
        } else if (stockFilter === 'low') {
            stockMatch = stock > 0 && stock <= 10;
        } else if (stockFilter === 'healthy') {
            stockMatch = stock > 10;
        }
        
        // ... rest of your filter logic ...
        
        if (categoryMatch && statusMatch && stockMatch && searchMatch) {
            row.show();
        } else {
            row.hide();
        }
    });
}
</script>

<?php include('includes/footer.php'); ?>