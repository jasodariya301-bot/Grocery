<?php
session_start();
include 'includes/db_config.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    echo "Product not found!";
    exit();
}

$product_id = intval($_GET['id']);
$current_date = date('Y-m-d H:i:s');

// Fetch product with applicable offers
$query = "SELECT p.*, 
          (SELECT GROUP_CONCAT(DISTINCT 
           CONCAT(o.id, ':', o.offer_title, ':', o.discount_type, ':', o.discount_value, ':', 
                  COALESCE(o.discount_display, 
                      CASE WHEN o.discount_type = 'percentage' 
                          THEN CONCAT(o.discount_value, '% OFF')
                          ELSE CONCAT('₹', o.discount_value, ' OFF')
                      END)) 
           SEPARATOR '||')
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
          ) as applicable_offers
          FROM products p 
          WHERE p.id = $product_id";

$result = mysqli_query($con, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Product not found!";
    exit();
}

$product = mysqli_fetch_assoc($result);

// Calculate best discount
$original_price = $product['price'];
$discounted_price = $original_price;
$best_offer = null;
$discount_amount = 0;
$available_offers = [];

if($product['applicable_offers']) {
    $offers = explode('||', $product['applicable_offers']);
    $max_discount = 0;
    
    foreach($offers as $offer_str) {
        $offer_parts = explode(':', $offer_str);
        $offer_id = $offer_parts[0];
        $offer_title = $offer_parts[1];
        $offer_type = $offer_parts[2];
        $offer_value = floatval($offer_parts[3]);
        $offer_display = $offer_parts[4];
        $offer_amount = 0;
        
        if($offer_type == 'percentage') {
            $offer_amount = $original_price * ($offer_value / 100);
        } else {
            $offer_amount = $offer_value;
        }
        
        $available_offers[] = [
            'id' => $offer_id,
            'title' => $offer_title,
            'type' => $offer_type,
            'value' => $offer_value,
            'display' => $offer_display,
            'amount' => $offer_amount
        ];
        
        if($offer_amount > $max_discount) {
            $max_discount = $offer_amount;
            $best_offer = end($available_offers);
        }
    }
    
    $discounted_price = $original_price - $max_discount;
    if($discounted_price < 0) $discounted_price = 0;
    $discount_amount = $max_discount;
}
?>

<!-- Product Details -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Product Image -->
            <div class="col-md-5">
                <div class="card shadow-sm position-relative">
                    <!-- Best Offer Badge -->
                    <?php if($best_offer): ?>
                    <div class="position-absolute" style="top: 15px; left: 15px; z-index: 10;">
                        <span class="badge bg-danger px-4 py-2 fs-6 shadow">
                            <i class="fas fa-tag me-2"></i>
                            <?php echo htmlspecialchars($best_offer['display']); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Stock Badge -->
                    <?php if($product['stock'] < 10 && $product['stock'] > 0): ?>
                    <span class="badge bg-warning text-dark position-absolute" 
                          style="top:15px; right:15px; z-index:10; font-size:0.9rem;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Only <?php echo $product['stock']; ?> left
                    </span>
                    <?php endif; ?>
                    
                    <?php if(!empty($product['image']) && file_exists("uploads/products/".$product['image'])): ?>
                        <img src="uploads/products/<?php echo $product['image']; ?>" 
                             class="img-fluid rounded">
                    <?php else: ?>
                        <img src="assets/images/products/default-product.jpg" 
                             class="img-fluid rounded">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                        <li class="breadcrumb-item">
                            <a href="products.php?category=<?php echo urlencode($product['category']); ?>">
                                <?php echo ucfirst($product['category']); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['product_name']); ?></li>
                    </ol>
                </nav>
                
                <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($product['product_name']); ?></h2>
                <p class="text-muted mb-3">
                    <i class="fas fa-box me-2"></i>Weight: <?php echo $product['weight']; ?>
                </p>
                
                <!-- Price Display with Discount -->
                <div class="mb-4 p-3 bg-light rounded">
                    <?php if($best_offer): ?>
                    <div class="d-flex align-items-center mb-2">
                        <h2 class="text-success fw-bold mb-0 me-3">
                            ₹<?php echo number_format($discounted_price, 2); ?>
                        </h2>
                        <span class="text-muted fs-5">
                            <del>₹<?php echo number_format($original_price, 2); ?></del>
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success me-3 px-3 py-2">
                            <i class="fas fa-piggy-bank me-1"></i>
                            You Save: ₹<?php echo number_format($discount_amount, 2); ?>
                        </span>
                        <span class="text-muted">
                            (<?php echo round(($discount_amount / $original_price) * 100); ?>% OFF)
                        </span>
                    </div>
                    <?php else: ?>
                    <h2 class="text-success fw-bold mb-0">
                        ₹<?php echo number_format($original_price, 2); ?>
                    </h2>
                    <?php endif; ?>
                </div>
                
                <!-- Available Offers Section -->
                <?php if(!empty($available_offers)): ?>
                <div class="card mb-4 border-success">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-tags me-2"></i>Available Offers
                    </div>
                    <div class="card-body">
                        <?php foreach($available_offers as $index => $offer): ?>
                        <div class="offer-item mb-2 pb-2 <?php echo $index < count($available_offers)-1 ? 'border-bottom' : ''; ?>">
                            <div class="d-flex align-items-center">
                                <div class="offer-badge bg-danger text-white px-3 py-2 rounded me-3 text-center" 
                                     style="min-width: 100px;">
                                    <small class="d-block">OFFER</small>
                                    <strong><?php echo htmlspecialchars($offer['display']); ?></strong>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($offer['title']); ?></strong><br>
                                    <small class="text-muted">
                                        <?php if($offer['type'] == 'percentage'): ?>
                                            Get <?php echo $offer['value']; ?>% off on this product
                                        <?php else: ?>
                                            Get flat ₹<?php echo $offer['value']; ?> off on this product
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Product Description -->
                <div class="mb-4">
                    <h5 class="fw-bold mb-2">Description</h5>
                    <p class="text-muted">
                        <?php echo !empty($product['description']) ? 
                            nl2br(htmlspecialchars($product['description'])) : 
                            "No description available."; ?>
                    </p>
                </div>
                
                <!-- Stock Status -->
                <div class="mb-4">
                    <?php if($product['stock'] > 10): ?>
                        <span class="text-success">
                            <i class="fas fa-check-circle me-1"></i>In Stock
                        </span>
                    <?php elseif($product['stock'] > 0): ?>
                        <span class="text-warning">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Low Stock - Only <?php echo $product['stock']; ?> left
                        </span>
                    <?php else: ?>
                        <span class="text-danger">
                            <i class="fas fa-times-circle me-1"></i>Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Quantity Selector -->
                <?php if($product['stock'] > 0): ?>
                <div class="row align-items-end mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Quantity</label>
                        <div class="input-group" style="width: 140px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="updateQty(-1)">-</button>
                            <input type="number" id="qty" class="form-control text-center" 
                                   value="1" min="1" max="<?php echo $product['stock']; ?>" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="updateQty(1)">+</button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="d-flex gap-3">
                    <?php if($product['stock'] > 0): ?>
                    <button class="btn btn-success btn-lg px-4" onclick="addToCart(<?php echo $product['id']; ?>)">
                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                    </button>
                    <button class="btn btn-warning btn-lg px-4" onclick="buyNow(<?php echo $product['id']; ?>)">
                        <i class="fas fa-bolt me-2"></i>Buy Now
                    </button>
                    <?php else: ?>
                    <button class="btn btn-secondary btn-lg px-4" disabled>
                        <i class="fas fa-ban me-2"></i>Out of Stock
                    </button>
                    <?php endif; ?>
                    <a href="products.php" class="btn btn-outline-secondary btn-lg px-4">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function updateQty(change) {
    var qtyInput = document.getElementById('qty');
    var currentQty = parseInt(qtyInput.value);
    var newQty = currentQty + change;
    var maxStock = <?php echo $product['stock']; ?>;
    
    if (newQty >= 1 && newQty <= maxStock) {
        qtyInput.value = newQty;
    }
}

function addToCart(id) {
    var qty = document.getElementById('qty').value;
    
    <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
        window.location.href = "login.php?redirect=product-detail.php?id=" + id;
        return;
    <?php endif; ?>
    
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + id + '&quantity=' + qty
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            showNotification('✅ ' + data.message, 'success');
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Something went wrong', 'error');
    });
}

function buyNow(id) {
    addToCart(id);
    setTimeout(function() {
        window.location.href = 'checkout.php';
    }, 1000);
}

function updateCartCount(count) {
    var cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
}

function showNotification(message, type) {
    var notification = document.createElement('div');
    notification.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + 
                            ' position-fixed top-0 end-0 m-3 shadow';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.innerHTML = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}
</script>

<style>
.offer-badge {
    font-family: 'Courier New', monospace;
    letter-spacing: 0.5px;
}
.breadcrumb {
    font-size: 0.9rem;
}
.breadcrumb a {
    color: #6c757d;
    text-decoration: none;
}
.breadcrumb a:hover {
    color: #198754;
}
</style>

<?php include 'includes/footer.php'; ?>