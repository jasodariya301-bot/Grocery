<?php
session_start();
require_once '../includes/db_config.php';

// Add admin authentication check here
// if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_POST['order_id']);
$new_status = mysqli_real_escape_string($con, $_POST['status']);

// Get current order status
$current_query = "SELECT order_status FROM orders WHERE id = ?";
$stmt = mysqli_prepare($con, $current_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    $_SESSION['error'] = "Order not found";
    header('Location: orders.php');
    exit();
}

$current_status = $order['order_status'];

// Begin transaction
mysqli_begin_transaction($con);

try {
    // Update order status
    $update = "UPDATE orders SET order_status = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $update);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to update status");
    }
    
    // Handle stock based on status change
    if ($new_status == 'cancelled' && $current_status != 'cancelled') {
        // Restore stock when order is cancelled
        $items_query = "SELECT product_id, quantity, product_name FROM order_items WHERE order_id = ?";
        $stmt = mysqli_prepare($con, $items_query);
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $items_result = mysqli_stmt_get_result($stmt);
        
        while ($item = mysqli_fetch_assoc($items_result)) {
            $restore = "UPDATE products SET stock = stock + ?, 
                        status = 'in-stock' 
                        WHERE id = ?";
            $restore_stmt = mysqli_prepare($con, $restore);
            mysqli_stmt_bind_param($restore_stmt, "ii", $item['quantity'], $item['product_id']);
            
            if (!mysqli_stmt_execute($restore_stmt)) {
                throw new Exception("Failed to restore stock for: " . $item['product_name']);
            }
            mysqli_stmt_close($restore_stmt);
        }
        
        $_SESSION['success'] = "Order cancelled and stock restored successfully";
        
    } elseif ($current_status == 'cancelled' && $new_status != 'cancelled') {
        // Re-deduct stock when uncancelling an order
        $items_query = "SELECT product_id, quantity, product_name FROM order_items WHERE order_id = ?";
        $stmt = mysqli_prepare($con, $items_query);
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $items_result = mysqli_stmt_get_result($stmt);
        
        while ($item = mysqli_fetch_assoc($items_result)) {
            // Check current stock
            $stock_check = "SELECT stock FROM products WHERE id = ?";
            $check_stmt = mysqli_prepare($con, $stock_check);
            mysqli_stmt_bind_param($check_stmt, "i", $item['product_id']);
            mysqli_stmt_execute($check_stmt);
            $stock_result = mysqli_stmt_get_result($check_stmt);
            $product = mysqli_fetch_assoc($stock_result);
            
            if ($product['stock'] < $item['quantity']) {
                throw new Exception("Insufficient stock for: " . $item['product_name'] . " (Available: " . $product['stock'] . ")");
            }
            mysqli_stmt_close($check_stmt);
            
            // Deduct stock
            $deduct = "UPDATE products SET stock = stock - ?, 
                      status = CASE WHEN (stock - ?) <= 0 THEN 'out-of-stock' ELSE 'in-stock' END 
                      WHERE id = ?";
            $deduct_stmt = mysqli_prepare($con, $deduct);
            mysqli_stmt_bind_param($deduct_stmt, "iii", $item['quantity'], $item['quantity'], $item['product_id']);
            
            if (!mysqli_stmt_execute($deduct_stmt)) {
                throw new Exception("Failed to deduct stock for: " . $item['product_name']);
            }
            mysqli_stmt_close($deduct_stmt);
        }
        
        $_SESSION['success'] = "Order reactivated and stock updated successfully";
        
    } else {
        $_SESSION['success'] = "Order status updated successfully";
    }
    
    mysqli_commit($con);
    
} catch (Exception $e) {
    mysqli_rollback($con);
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header('Location: order-details.php?id=' . $order_id);
exit();
?>