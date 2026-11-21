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

// Get order items
$items_query = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
$stmt = mysqli_prepare($con, $items_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$added_count = 0;
$errors = [];

while ($item = mysqli_fetch_assoc($result)) {
    // Check if product exists and is in stock
    $product_query = "SELECT id, stock, status FROM products WHERE id = ?";
    $product_stmt = mysqli_prepare($con, $product_query);
    mysqli_stmt_bind_param($product_stmt, "i", $item['product_id']);
    mysqli_stmt_execute($product_stmt);
    $product_result = mysqli_stmt_get_result($product_stmt);
    $product = mysqli_fetch_assoc($product_result);
    
    if ($product && $product['status'] == 'in-stock' && $product['stock'] > 0) {
        // Check if already in cart
        $cart_check = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
        $cart_stmt = mysqli_prepare($con, $cart_check);
        mysqli_stmt_bind_param($cart_stmt, "ii", $user_id, $item['product_id']);
        mysqli_stmt_execute($cart_stmt);
        $cart_result = mysqli_stmt_get_result($cart_stmt);
        
        if (mysqli_num_rows($cart_result) > 0) {
            $cart_item = mysqli_fetch_assoc($cart_result);
            $new_qty = min($cart_item['quantity'] + $item['quantity'], $product['stock']);
            
            $update_cart = "UPDATE cart SET quantity = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($con, $update_cart);
            mysqli_stmt_bind_param($update_stmt, "ii", $new_qty, $cart_item['id']);
            mysqli_stmt_execute($update_stmt);
        } else {
            $insert_cart = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($con, $insert_cart);
            $qty = min($item['quantity'], $product['stock']);
            mysqli_stmt_bind_param($insert_stmt, "iii", $user_id, $item['product_id'], $qty);
            mysqli_stmt_execute($insert_stmt);
        }
        $added_count++;
    }
}

echo json_encode([
    'success' => true, 
    'message' => $added_count . ' items added to cart',
    'added' => $added_count
]);
?>