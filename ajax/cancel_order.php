<?php
session_start();
require_once '../includes/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_POST['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order']);
    exit();
}

// Check if order belongs to user and is pending
$check_query = "SELECT id, order_status FROM orders WHERE id = ? AND user_id = ? AND order_status = 'pending'";
$stmt = mysqli_prepare($con, $check_query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled']);
    exit();
}

// Begin transaction
mysqli_begin_transaction($con);

try {
    // Update order status
    $update_query = "UPDATE orders SET order_status = 'cancelled', updated_date = NOW() WHERE id = ?";
    $update_stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($update_stmt, "i", $order_id);
    mysqli_stmt_execute($update_stmt);
    
    // Restore stock
    $items_query = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $items_stmt = mysqli_prepare($con, $items_query);
    mysqli_stmt_bind_param($items_stmt, "i", $order_id);
    mysqli_stmt_execute($items_stmt);
    $items_result = mysqli_stmt_get_result($items_stmt);
    
    while ($item = mysqli_fetch_assoc($items_result)) {
        $restore_query = "UPDATE products SET stock = stock + ?, 
                         status = 'in-stock' WHERE id = ?";
        $restore_stmt = mysqli_prepare($con, $restore_query);
        mysqli_stmt_bind_param($restore_stmt, "ii", $item['quantity'], $item['product_id']);
        mysqli_stmt_execute($restore_stmt);
    }
    
    mysqli_commit($con);
    
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    
} catch (Exception $e) {
    mysqli_rollback($con);
    echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
}
?>