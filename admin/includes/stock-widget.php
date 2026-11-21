<?php
// includes/stock_functions.php

/**
 * Get database connection
 */
function getDBConnection() {
    if (!isset($GLOBALS['con'])) {
        include_once __DIR__ . '/db_config.php';
    }
    return $GLOBALS['con'];
}

/**
 * Check and update product stock status
 */
function updateProductStockStatus($product_id) {
    $con = getDBConnection();
    
    $update = "UPDATE products SET 
               status = CASE 
                   WHEN stock <= 0 THEN 'out-of-stock' 
                   ELSE 'in-stock' 
               END 
               WHERE id = ?";
    
    $stmt = mysqli_prepare($con, $update);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Get low stock products (less than threshold)
 */
function getLowStockProducts($threshold = 10) {
    $con = getDBConnection();
    
    $query = "SELECT id, product_name, stock, category, price, weight, image 
              FROM products 
              WHERE stock <= ? AND stock > 0 AND status = 'in-stock' 
              ORDER BY stock ASC";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $threshold);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get out of stock products
 */
function getOutOfStockProducts() {
    $con = getDBConnection();
    
    $query = "SELECT id, product_name, category FROM products 
              WHERE stock <= 0 OR status = 'out-of-stock' 
              ORDER BY product_name ASC";
    
    $result = mysqli_query($con, $query);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get stock statistics
 */
function getStockStatistics() {
    $con = getDBConnection();
    
    $query = "SELECT 
        COUNT(*) as total_products,
        SUM(CASE WHEN stock = 0 OR status = 'out-of-stock' THEN 1 ELSE 0 END) as out_of_stock,
        SUM(CASE WHEN stock > 0 AND stock <= 5 THEN 1 ELSE 0 END) as critical,
        SUM(CASE WHEN stock > 5 AND stock <= 10 THEN 1 ELSE 0 END) as low,
        SUM(CASE WHEN stock > 10 THEN 1 ELSE 0 END) as healthy,
        SUM(stock) as total_stock
        FROM products";
    
    $result = mysqli_query($con, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Bulk update stock for multiple products
 */
function bulkUpdateStock($updates) {
    $con = getDBConnection();
    
    mysqli_begin_transaction($con);
    
    try {
        foreach ($updates as $product_id => $quantity_change) {
            $update = "UPDATE products SET stock = stock + ?, 
                      status = CASE WHEN (stock + ?) <= 0 THEN 'out-of-stock' ELSE 'in-stock' END 
                      WHERE id = ?";
            $stmt = mysqli_prepare($con, $update);
            mysqli_stmt_bind_param($stmt, "iii", $quantity_change, $quantity_change, $product_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to update stock for product ID: $product_id");
            }
            mysqli_stmt_close($stmt);
        }
        
        mysqli_commit($con);
        return ['success' => true, 'message' => 'Stock updated successfully'];
        
    } catch (Exception $e) {
        mysqli_rollback($con);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Validate stock before order
 */
function validateOrderStock($cart_items) {
    $con = getDBConnection();
    
    $errors = [];
    
    foreach ($cart_items as $item) {
        $query = "SELECT product_name, stock FROM products WHERE id = ? AND status = 'in-stock'";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $item['product_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        
        if (!$product) {
            $errors[] = "Product '{$item['product_name']}' is no longer available.";
        } elseif ($product['stock'] < $item['quantity']) {
            $errors[] = "Only {$product['stock']} units of '{$product['product_name']}' are available.";
        }
        mysqli_stmt_close($stmt);
    }
    
    return $errors;
}

/**
 * Update stock after order
 */
function updateStockAfterOrder($order_id, $operation = 'deduct') {
    $con = getDBConnection();
    
    $items_query = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $stmt = mysqli_prepare($con, $items_query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($item = mysqli_fetch_assoc($result)) {
        if ($operation == 'deduct') {
            $update = "UPDATE products SET stock = stock - ?, 
                      status = CASE WHEN (stock - ?) <= 0 THEN 'out-of-stock' ELSE 'in-stock' END 
                      WHERE id = ?";
        } else {
            $update = "UPDATE products SET stock = stock + ?, 
                      status = 'in-stock' 
                      WHERE id = ?";
        }
        
        $update_stmt = mysqli_prepare($con, $update);
        
        if ($operation == 'deduct') {
            mysqli_stmt_bind_param($update_stmt, "iii", $item['quantity'], $item['quantity'], $item['product_id']);
        } else {
            mysqli_stmt_bind_param($update_stmt, "ii", $item['quantity'], $item['product_id']);
        }
        
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    }
    
    return true;
}

/**
 * Get stock history for a product
 */
function getProductStockHistory($product_id, $limit = 10) {
    $con = getDBConnection();
    
    // Check if stock_history table exists
    $check_table = mysqli_query($con, "SHOW TABLES LIKE 'stock_history'");
    
    if (mysqli_num_rows($check_table) == 0) {
        return [];
    }
    
    $query = "SELECT * FROM stock_history 
              WHERE product_id = ? 
              ORDER BY changed_date DESC 
              LIMIT ?";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ii", $product_id, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $history = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $history[] = $row;
    }
    
    return $history;
}

/**
 * Check if product needs restock alert
 */
function needsRestockAlert($threshold = 10) {
    $con = getDBConnection();
    
    $query = "SELECT COUNT(*) as count FROM products 
              WHERE stock <= ? AND stock > 0 AND status = 'in-stock'";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $threshold);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    
    return $data['count'] > 0;
}

/**
 * Get restock recommendations
 */
function getRestockRecommendations() {
    $con = getDBConnection();
    
    // Get products that are out of stock or low stock with high order frequency
    $query = "SELECT p.id, p.product_name, p.stock, p.category,
              COUNT(oi.id) as order_frequency
              FROM products p
              LEFT JOIN order_items oi ON p.id = oi.product_id
              WHERE p.stock <= 10 OR p.status = 'out-of-stock'
              GROUP BY p.id
              ORDER BY order_frequency DESC, p.stock ASC
              LIMIT 10";
    
    $result = mysqli_query($con, $query);
    
    $recommendations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $recommendations[] = $row;
    }
    
    return $recommendations;
}
?>