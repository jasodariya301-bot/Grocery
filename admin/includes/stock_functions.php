<?php
// includes/stock_functions.php

/**
 * Check and update product stock status
 */
function updateProductStockStatus($product_id) {
    global $con;
    
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
    global $con;
    
    $query = "SELECT * FROM products WHERE stock <= ? AND stock > 0 AND status = 'in-stock' ORDER BY stock ASC";
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
 * Bulk update stock for multiple products
 */
function bulkUpdateStock($updates) {
    global $con;
    
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
    global $con;
    
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
 * Log stock change to history
 */
function logStockChange($product_id, $previous_stock, $new_stock, $change_type, $reference_id = null, $notes = '', $changed_by = null) {
    global $con;
    
    $change_amount = $new_stock - $previous_stock;
    
    $query = "INSERT INTO stock_history (product_id, previous_stock, new_stock, change_amount, 
              change_type, reference_id, notes, changed_by) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "iiiisisi", 
        $product_id, $previous_stock, $new_stock, $change_amount, 
        $change_type, $reference_id, $notes, $changed_by
    );
    
    return mysqli_stmt_execute($stmt);
}
?>