<?php
session_start();
require_once 'includes/db_config.php';
require_once 'includes/cart_functions.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php?redirect=cart.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle add to cart from URL parameters
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $quantity = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
    $result = addToCart($user_id, $product_id, $quantity);
}

// Get cart items
$cart_items = getCartItems($user_id);
$totals = calculateCartTotals($cart_items);
$cart_count = getCartCount($user_id);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-shopping-cart text-success"></i> Shopping Cart
        <?php if ($cart_count > 0): ?>
            <span class="badge bg-success ms-2 cart-count-badge"><?php echo $cart_count; ?> items</span>
        <?php endif; ?>
    </h2>

    <?php if (empty($cart_items)): ?>
        <!-- Empty Cart -->
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Looks like you haven't added anything to your cart yet.</p>
            <a href="products.php" class="btn btn-success btn-lg mt-3">
                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th width="150">Quantity</th>
                                        <th>Subtotal</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="cart-items-body">
                                    <?php foreach ($cart_items as $item): ?>
                                    <tr id="cart-row-<?php echo $item['product_id']; ?>" data-product-id="<?php echo $item['product_id']; ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if(!empty($item['image']) && file_exists("uploads/products/".$item['image'])): ?>
                                                    <img src="uploads/products/<?php echo $item['image']; ?>" 
                                                         width="60" height="60" 
                                                         style="object-fit: cover;" 
                                                         class="rounded me-3">
                                                <?php else: ?>
                                                    <img src="assets/images/products/default-product.jpg" 
                                                         width="60" height="60" 
                                                         style="object-fit: cover;" 
                                                         class="rounded me-3">
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($item['weight']); ?></small>
                                                    <?php if ($item['stock'] < 10): ?>
                                                        <br><small class="text-warning">Only <?php echo $item['stock']; ?> left</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="item-price">₹<?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <button class="btn btn-outline-secondary qty-btn" 
                                                        type="button"
                                                        data-product-id="<?php echo $item['product_id']; ?>"
                                                        data-action="decrease">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" 
                                                       class="form-control text-center cart-qty-input" 
                                                       id="qty-<?php echo $item['product_id']; ?>"
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" 
                                                       max="<?php echo $item['stock']; ?>"
                                                       data-product-id="<?php echo $item['product_id']; ?>"
                                                       data-price="<?php echo $item['price']; ?>"
                                                       readonly>
                                                <button class="btn btn-outline-secondary qty-btn" 
                                                        type="button"
                                                        data-product-id="<?php echo $item['product_id']; ?>"
                                                        data-action="increase">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="item-subtotal" id="subtotal-<?php echo $item['product_id']; ?>">
                                            ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger remove-item-btn" 
                                                    data-product-id="<?php echo $item['product_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="products.php" class="btn btn-outline-success">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                    <button class="btn btn-outline-danger" id="clear-cart-btn">
                        <i class="fas fa-trash me-2"></i>Clear Cart
                    </button>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm position-sticky" style="top: 100px;">
                    <div class="card-body">
                        <h5 class="mb-3">Cart Summary</h5>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span id="cart-subtotal">₹<?php echo number_format($totals['subtotal'], 2); ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>GST (18%)</span>
                            <span id="cart-gst">₹<?php echo number_format($totals['gst_amount'], 2); ?></span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between fw-bold text-success fs-5">
                            <span>Grand Total</span>
                            <span id="cart-grand-total">₹<?php echo number_format($totals['grand_total'], 2); ?></span>
                        </div>

                        <a href="checkout.php" class="btn btn-success w-100 mt-4">
                            <i class="fas fa-lock me-2"></i>Proceed to Checkout
                        </a>
                        
                        <div class="mt-3 text-center small text-muted">
                            <i class="fas fa-shield-alt me-1"></i>Secure Checkout
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Loading Spinner -->
<div id="cart-loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Quantity decrease buttons
    document.querySelectorAll('.qty-btn[data-action="decrease"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const input = document.getElementById('qty-' + productId);
            let newQty = parseInt(input.value) - 1;
            if (newQty >= 1) {
                updateCartItem(productId, newQty);
            }
        });
    });
    
    // Quantity increase buttons
    document.querySelectorAll('.qty-btn[data-action="increase"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const input = document.getElementById('qty-' + productId);
            const maxStock = parseInt(input.max);
            let newQty = parseInt(input.value) + 1;
            if (newQty <= maxStock) {
                updateCartItem(productId, newQty);
            } else {
                showNotification('Maximum available stock reached!', 'warning');
            }
        });
    });
    
    // Remove item buttons
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            removeCartItem(productId);
        });
    });
    
    // Clear cart button
    document.getElementById('clear-cart-btn')?.addEventListener('click', function() {
        if (confirm('Are you sure you want to remove all items from your cart?')) {
            clearAllCart();
        }
    });
});

// Update cart item quantity
function updateCartItem(productId, quantity) {
    showLoader();
    
    fetch('ajax/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update&product_id=' + productId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        
        if (data.success) {
            // Update quantity input
            const qtyInput = document.getElementById('qty-' + productId);
            if (qtyInput) {
                qtyInput.value = quantity;
            }
            
            // Update item subtotal
            const itemData = data.items.find(item => item.product_id == productId);
            if (itemData) {
                const subtotalElement = document.getElementById('subtotal-' + productId);
                if (subtotalElement) {
                    subtotalElement.textContent = '₹' + itemData.subtotal.toFixed(2);
                }
            }
            
            // Update cart totals
            updateCartTotals(data.totals);
            
            // Update cart count in header
            updateCartCount(data.cart_count);
            
            showNotification('Cart updated successfully!', 'success');
        } else {
            showNotification(data.message || 'Failed to update cart', 'error');
        }
    })
    .catch(error => {
        hideLoader();
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

// Remove item from cart
function removeCartItem(productId) {
    if (!confirm('Remove this item from cart?')) return;
    
    showLoader();
    
    fetch('ajax/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=remove&product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        
        if (data.success) {
            // Remove the row from table
            const row = document.getElementById('cart-row-' + productId);
            if (row) {
                row.remove();
            }
            
            // Update cart totals
            updateCartTotals(data.totals);
            
            // Update cart count
            updateCartCount(data.cart_count);
            
            showNotification('Item removed from cart', 'success');
            
            // Check if cart is empty
            const remainingRows = document.querySelectorAll('#cart-items-body tr');
            if (remainingRows.length === 0) {
                setTimeout(() => location.reload(), 500);
            }
        } else {
            showNotification(data.message || 'Failed to remove item', 'error');
        }
    })
    .catch(error => {
        hideLoader();
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

// Clear entire cart
function clearAllCart() {
    const rows = document.querySelectorAll('#cart-items-body tr');
    if (rows.length === 0) return;
    
    showLoader();
    
    // Get all product IDs
    const productIds = Array.from(rows).map(row => row.dataset.productId);
    
    // Remove items sequentially
    const removePromises = productIds.map(id => 
        fetch('ajax/update_cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=remove&product_id=' + id
        }).then(res => res.json())
    );
    
    Promise.all(removePromises)
        .then(results => {
            hideLoader();
            showNotification('Cart cleared successfully', 'success');
            setTimeout(() => location.reload(), 500);
        })
        .catch(error => {
            hideLoader();
            console.error('Error:', error);
            showNotification('Failed to clear cart', 'error');
        });
}

// Update cart totals display
function updateCartTotals(totals) {
    if (totals) {
        document.getElementById('cart-subtotal').textContent = '₹' + totals.subtotal.toFixed(2);
        document.getElementById('cart-gst').textContent = '₹' + totals.gst_amount.toFixed(2);
        document.getElementById('cart-grand-total').textContent = '₹' + totals.grand_total.toFixed(2);
    }
}

// Update cart count display
function updateCartCount(count) {
    // Update badge in cart page
    const badge = document.querySelector('.cart-count-badge');
    if (badge) {
        badge.textContent = count + ' items';
    }
    
    // Update header cart count if exists
    const headerCartCount = document.querySelector('.cart-count');
    if (headerCartCount) {
        headerCartCount.textContent = count;
    }
}

// Show loader
function showLoader() {
    document.getElementById('cart-loader').style.display = 'block';
}

// Hide loader
function hideLoader() {
    document.getElementById('cart-loader').style.display = 'none';
}

// Show notification
function showNotification(message, type = 'info') {
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };
    
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colors[type] || colors.info};
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>

<?php include 'includes/footer.php'; ?>