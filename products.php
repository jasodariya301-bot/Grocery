<?php
session_start();
$page_title = "Products - Fresh Groceries Online";

include 'includes/db_config.php';
include 'includes/header.php';

// Get filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($con, $_GET['category']) : '';

$current_date = date('Y-m-d H:i:s');

// Build query with filters and active offers
$query = "SELECT p.*, 
          (SELECT o.discount_display 
           FROM offers o 
           WHERE o.status = 'active' 
           AND o.valid_from <= '$current_date' 
           AND o.valid_to >= '$current_date'
           AND (o.apply_to = 'all' 
                OR (o.apply_to = 'category' AND o.category_id = p.category)
                OR (o.apply_to = 'products' AND EXISTS (
                    SELECT 1 FROM offer_products op 
                    WHERE op.offer_id = o.id AND op.product_id = p.id
                )))
           ORDER BY 
               CASE WHEN o.discount_type = 'percentage' 
                   THEN p.price * (o.discount_value / 100)
                   ELSE o.discount_value 
               END DESC
           LIMIT 1) as best_offer_text,
          (SELECT o.discount_type 
           FROM offers o 
           WHERE o.status = 'active' 
           AND o.valid_from <= '$current_date' 
           AND o.valid_to >= '$current_date'
           AND (o.apply_to = 'all' 
                OR (o.apply_to = 'category' AND o.category_id = p.category)
                OR (o.apply_to = 'products' AND EXISTS (
                    SELECT 1 FROM offer_products op 
                    WHERE op.offer_id = o.id AND op.product_id = p.id
                )))
           ORDER BY 
               CASE WHEN o.discount_type = 'percentage' 
                   THEN p.price * (o.discount_value / 100)
                   ELSE o.discount_value 
               END DESC
           LIMIT 1) as discount_type,
          (SELECT o.discount_value 
           FROM offers o 
           WHERE o.status = 'active' 
           AND o.valid_from <= '$current_date' 
           AND o.valid_to >= '$current_date'
           AND (o.apply_to = 'all' 
                OR (o.apply_to = 'category' AND o.category_id = p.category)
                OR (o.apply_to = 'products' AND EXISTS (
                    SELECT 1 FROM offer_products op 
                    WHERE op.offer_id = o.id AND op.product_id = p.id
                )))
           ORDER BY 
               CASE WHEN o.discount_type = 'percentage' 
                   THEN p.price * (o.discount_value / 100)
                   ELSE o.discount_value 
               END DESC
           LIMIT 1) as discount_value
          FROM products p 
          WHERE p.status='in-stock'";

// Add search condition
if(!empty($search)) {
    $query .= " AND (p.product_name LIKE '%$search%' OR p.description LIKE '%$search%' OR p.category LIKE '%$search%')";
}

// Add category condition
if(!empty($category)) {
    $query .= " AND p.category = '$category'";
}

$query .= " ORDER BY p.id DESC";

$result = mysqli_query($con, $query);

// Check query
if (!$result) {
    die("Query Failed: " . mysqli_error($con));
}

// Get all categories for filter
$cat_query = "SELECT DISTINCT category FROM products ORDER BY category";
$cat_result = mysqli_query($con, $cat_query);
?>

<!-- Products Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search Box -->
                        <h6>Search Products</h6>
                        <div class="mb-3">
                            <input type="text" id="sidebarSearch" class="form-control" 
                                   placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <hr>
                        
                        <!-- Dynamic Category Filter -->
                        <h6>Category</h6>
                        <div class="form-check">
                            <input class="form-check-input category-filter" type="radio" 
                                   name="categoryFilter" value="all" id="catAll"
                                   <?php echo empty($category) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="catAll">All Categories</label>
                        </div>

                        <?php
                        if(mysqli_num_rows($cat_result) > 0) {
                            while($cat = mysqli_fetch_assoc($cat_result)) {
                                $cat_name = $cat['category'];
                                $checked = ($category == $cat_name) ? 'checked' : '';
                        ?>
                        <div class="form-check">
                            <input class="form-check-input category-filter" type="radio" 
                                   name="categoryFilter" value="<?php echo htmlspecialchars($cat_name); ?>" 
                                   id="cat<?php echo md5($cat_name); ?>"
                                   <?php echo $checked; ?>>
                            <label class="form-check-label" for="cat<?php echo md5($cat_name); ?>">
                                <?php echo ucfirst($cat_name); ?>
                            </label>
                        </div>
                        <?php } } ?>

                        <hr>

                        <!-- Price Filter -->
                        <h6>Price Range</h6>
                        <div class="form-check">
                            <input class="form-check-input price-filter" type="radio" 
                                   name="priceFilter" value="all" id="priceAll" checked>
                            <label class="form-check-label" for="priceAll">All Prices</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input price-filter" type="radio" 
                                   name="priceFilter" value="low" id="priceLow">
                            <label class="form-check-label" for="priceLow">Under ₹100</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input price-filter" type="radio" 
                                   name="priceFilter" value="mid" id="priceMid">
                            <label class="form-check-label" for="priceMid">₹100 - ₹500</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input price-filter" type="radio" 
                                   name="priceFilter" value="high" id="priceHigh">
                            <label class="form-check-label" for="priceHigh">Above ₹500</label>
                        </div>
                        
                        <hr>
                        
                        <!-- Filter Buttons -->
                        <button class="btn btn-success w-100 mb-2" onclick="applyFilters()">
                            Apply Filters
                        </button>
                        <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                            Reset All
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Results Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0">
                            <?php if(!empty($search)) { ?>
                                Search Results for: "<?php echo htmlspecialchars($search); ?>"
                            <?php } elseif(!empty($category)) { ?>
                                Category: <?php echo ucfirst($category); ?>
                            <?php } else { ?>
                                All Products
                            <?php } ?>
                        </h4>
                        <p class="text-muted mb-0">
                            <?php echo mysqli_num_rows($result); ?> products found
                        </p>
                    </div>
                    
                    <select class="form-select w-auto" id="sortSelect">
                        <option value="default">Sort by</option>
                        <option value="low-high">Price: Low to High</option>
                        <option value="high-low">Price: High to Low</option>
                        <option value="name-asc">Name: A to Z</option>
                        <option value="name-desc">Name: Z to A</option>
                        <option value="discount">Biggest Discount</option>
                    </select>
                </div>

                <!-- Products Grid -->
                <div class="row g-4" id="productsGrid">
                    <?php if(mysqli_num_rows($result) > 0) { ?>
                        
                        <?php while($row = mysqli_fetch_assoc($result)) { 
                            // Calculate discounted price
                            $original_price = $row['price'];
                            $discounted_price = $original_price;
                            $has_discount = false;
                            $discount_percentage = 0;
                            
                            if(!empty($row['discount_type']) && !empty($row['discount_value'])) {
                                $has_discount = true;
                                if($row['discount_type'] == 'percentage') {
                                    $discount_amount = $original_price * ($row['discount_value'] / 100);
                                    $discounted_price = $original_price - $discount_amount;
                                    $discount_percentage = $row['discount_value'];
                                } else {
                                    $discount_amount = $row['discount_value'];
                                    $discounted_price = $original_price - $discount_amount;
                                    $discount_percentage = round(($discount_amount / $original_price) * 100);
                                }
                                if($discounted_price < 0) $discounted_price = 0;
                            }
                            
                            // Generate OFF tag text
                            $off_tag = '';
                            if($has_discount) {
                                if(!empty($row['best_offer_text'])) {
                                    $off_tag = $row['best_offer_text'];
                                } else {
                                    $off_tag = $discount_percentage . '% OFF';
                                }
                            }
                        ?>
                        <div class="col-6 col-md-4 col-lg-3 product-item"
                             data-category="<?php echo strtolower($row['category']); ?>"
                             data-price="<?php echo $discounted_price; ?>"
                             data-original-price="<?php echo $original_price; ?>"
                             data-name="<?php echo strtolower($row['product_name']); ?>"
                             data-discount="<?php echo $has_discount ? $discount_percentage : '0'; ?>">
                            <div class="card h-100 shadow-sm position-relative border-0">
                                
                                <!-- OFF Tag / Discount Badge - TOP LEFT -->
                                <?php if($has_discount): ?>
                                <div class="position-absolute" style="top: 12px; left: 12px; z-index: 10;">
                                    <span class="off-tag">
                                        <i class="fas fa-tag me-1"></i>
                                        <?php echo htmlspecialchars($off_tag); ?>
                                    </span>
                                </div>
                                <?php endif; ?>

                                <!-- Stock Badge - TOP RIGHT -->
                                <?php if($row['stock'] < 10 && $row['stock'] > 0) { ?>
                                    <span class="badge bg-warning text-dark position-absolute" 
                                          style="top:12px; right:12px; z-index:10; font-size:0.7rem; padding:5px 8px;">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Only <?php echo $row['stock']; ?> left
                                    </span>
                                <?php } ?>
                                
                                <!-- Out of Stock Overlay -->
                                <?php if($row['stock'] == 0) { ?>
                                    <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center" 
                                         style="background: rgba(255,255,255,0.7); z-index:5; border-radius:8px;">
                                        <span class="badge bg-secondary px-3 py-2">Out of Stock</span>
                                    </div>
                                <?php } ?>

                                <!-- Product Image -->
                                <div class="product-image-wrapper">
                                    <?php if(!empty($row['image']) && file_exists("uploads/products/".$row['image'])) { ?>
                                        <img src="uploads/products/<?php echo $row['image']; ?>" 
                                             class="card-img-top product-image" 
                                             alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                                    <?php } else { ?>
                                        <img src="assets/images/products/default-product.jpg" 
                                             class="card-img-top product-image" 
                                             alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                                    <?php } ?>
                                </div>

                                <!-- Product Content -->
                                <div class="card-body text-center px-2 pb-3">
                                    <!-- Category -->
                                    <span class="product-category">
                                        <?php echo ucfirst($row['category']); ?>
                                    </span>
                                    
                                    <!-- Product Name -->
                                    <h6 class="product-title mb-2">
                                        <?php echo htmlspecialchars($row['product_name']); ?>
                                    </h6>
                                    
                                    <!-- Weight -->
                                    <p class="product-weight mb-2">
                                        <?php echo $row['weight']; ?>
                                    </p>
                                    
                                    <!-- Price Display with Discount -->
                                    <?php if($has_discount): ?>
                                    <div class="price-container mb-2">
                                        <span class="current-price">
                                            ₹<?php echo number_format($discounted_price, 2); ?>
                                        </span>
                                        <span class="original-price">
                                            ₹<?php echo number_format($original_price, 2); ?>
                                        </span>
                                    </div>
                                    <div class="savings-badge">
                                        Save ₹<?php echo number_format($original_price - $discounted_price, 2); ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="price-container mb-2">
                                        <span class="current-price">
                                            ₹<?php echo number_format($original_price, 2); ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Action Button -->
                                   <div class="d-grid gap-2">
                                        <a href="product-detail.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-success btn-sm">
                                           View Details
                                        </a>
                                      
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                    <?php } else { ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5>No Products Found</h5>
                            <p class="text-muted">Try adjusting your search or filter criteria</p>
                            <button class="btn btn-success" onclick="resetFilters()">
                                View All Products
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CSS Styles for OFF Tag and Product Cards -->
<style>
/* OFF Tag Style */
.off-tag {
    background: linear-gradient(135deg, #d90a3a 0%, #c32206 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(255, 75, 43, 0.3);
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    animation: pulse 2s infinite;
}

.off-tag i {
    font-size: 0.7rem;
}

@keyframes pulse {
    0% {
        box-shadow: 0 4px 15px rgba(255, 75, 43, 0.3);
    }
    50% {
        box-shadow: 0 4px 20px rgba(255, 75, 43, 0.6);
    }
    100% {
        box-shadow: 0 4px 15px rgba(255, 75, 43, 0.3);
    }
}


</style>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$(document).ready(function(){
    
    // Sidebar search
    $('#sidebarSearch').on('keypress', function(e){
        if(e.which === 13) {
            applyFilters();
        }
    });
    
    // Category filter click
    $('.category-filter').change(function(){
        applyFilters();
    });
    
    // Sorting
    $('#sortSelect').change(function(){
        sortProducts($(this).val());
    });
    
    // Price filter
    $('.price-filter').change(function(){
        filterByPrice();
    });
});

function applyFilters() {
    var search = $('#sidebarSearch').val();
    var category = $('input[name="categoryFilter"]:checked').val();
    
    var url = 'products.php?';
    var params = [];
    
    if(search) params.push('search=' + encodeURIComponent(search));
    if(category && category !== 'all') params.push('category=' + encodeURIComponent(category));
    
    window.location.href = url + params.join('&');
}

function resetFilters() {
    window.location.href = 'products.php';
}

function filterByPrice() {
    var priceFilter = $('input[name="priceFilter"]:checked').val();
    
    $('.product-item').each(function(){
        var item = $(this);
        var price = parseFloat(item.data('price'));
        var show = true;
        
        if(priceFilter === 'low' && price > 100) show = false;
        else if(priceFilter === 'mid' && (price < 100 || price > 500)) show = false;
        else if(priceFilter === 'high' && price < 500) show = false;
        
        if(show) {
            item.show();
            item.addClass('animate__animated animate__fadeIn');
        } else {
            item.hide();
        }
    });
}

function sortProducts(type) {
    var items = $('.product-item').get();
    
    items.sort(function(a, b){
        var $a = $(a);
        var $b = $(b);
        
        switch(type) {
            case 'low-high':
                return parseFloat($a.data('price')) - parseFloat($b.data('price'));
            case 'high-low':
                return parseFloat($b.data('price')) - parseFloat($a.data('price'));
            case 'name-asc':
                return $a.data('name').localeCompare($b.data('name'));
            case 'name-desc':
                return $b.data('name').localeCompare($a.data('name'));
            case 'discount':
                return parseFloat($b.data('discount')) - parseFloat($a.data('discount'));
            default:
                return 0;
        }
    });
    
    $.each(items, function(i, item){
        $('#productsGrid').append(item);
    });
}

// Add to cart function (if needed)
function addToCart(productId) {
    <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
        window.location.href = "login.php?redirect=products.php";
        return;
    <?php endif; ?>
    
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=1'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('✅ Added to cart!', 'success');
        } else {
            showToast('❌ ' + data.message, 'error');
        }
    });
}

function showToast(message, type) {
    // Simple toast implementation
    var toast = $('<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">' +
        '<div class="toast show" role="alert">' +
        '<div class="toast-body bg-' + (type === 'success' ? 'success' : 'danger') + ' text-white rounded">' +
        message +
        '</div></div></div>');
    
    $('body').append(toast);
    setTimeout(function() {
        toast.fadeOut(function() {
            $(this).remove();
        });
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>