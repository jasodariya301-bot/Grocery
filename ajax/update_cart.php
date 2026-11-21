<?php
session_start();
require_once '../includes/db_config.php';
require_once '../includes/cart_functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

$user_id = $_SESSION['user_id'];
$result = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'update':
        if ($product_id > 0 && $quantity > 0) {
            $result = updateCartQuantity($user_id, $product_id, $quantity);
        } else {
            $result = ['success' => false, 'message' => 'Invalid product or quantity'];
        }
        break;
        
    case 'remove':
        if ($product_id > 0) {
            $result = removeFromCart($user_id, $product_id);
        } else {
            $result = ['success' => false, 'message' => 'Invalid product'];
        }
        break;
        
    case 'clear':
        $result = clearCart($user_id);
        if ($result['success']) {
            $result['message'] = 'Cart cleared successfully';
        }
        break;
        
    default:
        $result = ['success' => false, 'message' => 'Invalid action'];
}

// If operation was successful, return updated cart data
if ($result['success']) {
    $cart_items = getCartItems($user_id);
    $totals = calculateCartTotals($cart_items);
    $cart_count = getCartCount($user_id);
    
    // Calculate item-wise subtotals
    $item_subtotals = [];
    foreach ($cart_items as $item) {
        $item_subtotals[] = [
            'product_id' => $item['product_id'],
            'subtotal' => $item['price'] * $item['quantity'],
            'quantity' => $item['quantity']
        ];
    }
    
    $result['totals'] = $totals;
    $result['items'] = $item_subtotals;
    $result['cart_count'] = $cart_count;
    $result['cart_items'] = $cart_items;
}

echo json_encode($result);
exit();
?>