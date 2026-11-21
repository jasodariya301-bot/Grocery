<?php
// includes/cart_functions.php

/**
 * Get database connection
 */
function getDBConnection() {
    // Include db_config if not already included
    if (!isset($GLOBALS['con'])) {
        include_once __DIR__ . '/db_config.php';
    }
    return $GLOBALS['con'];
}

/**
 * Add item to cart
 */
function addToCart($user_id, $product_id, $quantity) {
    $con = getDBConnection();
    
    // Check if product exists and is in stock
    $check_product = "SELECT id, stock FROM products WHERE id = ? AND status = 'in-stock'";
    $stmt = mysqli_prepare($con, $check_product);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        return ['success' => false, 'message' => 'Product not available'];
    }
    
    $product = mysqli_fetch_assoc($result);
    
    // Check if product already in cart
    $check_cart = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($con, $check_cart);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);
    $cart_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($cart_result) > 0) {
        // Update quantity
        $cart_item = mysqli_fetch_assoc($cart_result);
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        // Check stock
        if ($new_quantity > $product['stock']) {
            return ['success' => false, 'message' => 'Not enough stock available'];
        }
        
        $update = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $update);
        mysqli_stmt_bind_param($stmt, "ii", $new_quantity, $cart_item['id']);
        
        if (mysqli_stmt_execute($stmt)) {
            return ['success' => true, 'message' => 'Cart updated successfully'];
        }
    } else {
        // Insert new item
        if ($quantity > $product['stock']) {
            return ['success' => false, 'message' => 'Not enough stock available'];
        }
        
        $insert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($con, $insert);
        mysqli_stmt_bind_param($stmt, "iii", $user_id, $product_id, $quantity);
        
        if (mysqli_stmt_execute($stmt)) {
            return ['success' => true, 'message' => 'Product added to cart'];
        }
    }
    
    return ['success' => false, 'message' => 'Failed to add to cart'];
}

/**
 * Get cart items for a user
 */
function getCartItems($user_id) {
    $con = getDBConnection();
    
    $query = "SELECT c.*, p.product_name, p.price, p.weight, p.image, p.stock 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = ? 
              ORDER BY c.added_date DESC";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    return $items;
}

/**
 * Update cart item quantity
 */
function updateCartQuantity($user_id, $product_id, $quantity) {
    $con = getDBConnection();
    
    // Check stock
    $check_stock = "SELECT stock FROM products WHERE id = ?";
    $stmt = mysqli_prepare($con, $check_stock);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    
    if ($quantity > $product['stock']) {
        return ['success' => false, 'message' => 'Not enough stock available'];
    }
    
    if ($quantity <= 0) {
        // Remove item
        return removeFromCart($user_id, $product_id);
    }
    
    $update = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($con, $update);
    mysqli_stmt_bind_param($stmt, "iii", $quantity, $user_id, $product_id);
    
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'message' => 'Cart updated'];
    }
    
    return ['success' => false, 'message' => 'Update failed'];
}

/**
 * Remove item from cart
 */
function removeFromCart($user_id, $product_id) {
    $con = getDBConnection();
    
    $delete = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($con, $delete);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'message' => 'Item removed'];
    }
    
    return ['success' => false, 'message' => 'Failed to remove item'];
}

/**
 * Get cart count for user
 */
function getCartCount($user_id) {
    $con = getDBConnection();
    
    $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    return $row['total'] ?? 0;
}

/**
 * Clear user's cart
 */
function clearCart($user_id) {
    $con = getDBConnection();
    
    $delete = "DELETE FROM cart WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $delete);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Calculate cart totals
 */
function calculateCartTotals($cart_items) {
    $subtotal = 0;
    
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $gst_rate = 0.18;
    $gst_amount = $subtotal * $gst_rate;
    $grand_total = $subtotal + $gst_amount;
    
    return [
        'subtotal' => $subtotal,
        'gst_amount' => $gst_amount,
        'grand_total' => $grand_total
    ];
}
?>