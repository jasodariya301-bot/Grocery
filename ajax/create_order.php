<?php
session_start();
require_once '../includes/db_config.php';
require_once '../includes/cart_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get form data
$name = mysqli_real_escape_string($con, $_POST['name']);
$mobile = mysqli_real_escape_string($con, $_POST['mobile']);
$email = mysqli_real_escape_string($con, $_POST['email']);
$address = mysqli_real_escape_string($con, $_POST['address']);
$city = mysqli_real_escape_string($con, $_POST['city']);
$pincode = mysqli_real_escape_string($con, $_POST['pincode']);
$payment_method = 'COD';

// Combine address
$full_address = "$address, $city - $pincode";

// Get cart items
$cart_items = getCartItems($user_id);

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
    exit();
}

// Calculate totals
$totals = calculateCartTotals($cart_items);
$grand_total = $totals['grand_total'];

// Apply coupon discount if any
if (isset($_SESSION['applied_coupon'])) {
    $grand_total = $grand_total - $_SESSION['applied_coupon']['discount'];
}

mysqli_begin_transaction($con);

try {
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Insert order
    $order_query = "INSERT INTO orders (user_id, order_number, customer_name, customer_mobile, 
                    customer_address, payment_method, payment_status, subtotal, gst_amount, 
                    total_amount, order_status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, 'pending')";
    
    $stmt = mysqli_prepare($con, $order_query);
    mysqli_stmt_bind_param($stmt, "isssssddd", 
        $user_id, $order_number, $name, $mobile, $full_address, $payment_method,
        $totals['subtotal'], $totals['gst_amount'], $grand_total
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to create order");
    }
    
    $order_id = mysqli_insert_id($con);
    
    // Insert order items and update stock
    $item_query = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, weight, subtotal) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $item_stmt = mysqli_prepare($con, $item_query);
    
    foreach ($cart_items as $item) {
        $item_subtotal = $item['price'] * $item['quantity'];
        
        mysqli_stmt_bind_param($item_stmt, "iisidid", 
            $order_id, $item['product_id'], $item['product_name'], 
            $item['quantity'], $item['price'], $item['weight'], $item_subtotal
        );
        
        if (!mysqli_stmt_execute($item_stmt)) {
            throw new Exception("Failed to add order items");
        }
        
        // Update product stock
        $update_stock = "UPDATE products SET stock = stock - ?, 
                        status = CASE WHEN (stock - ?) <= 0 THEN 'out-of-stock' ELSE 'in-stock' END 
                        WHERE id = ?";
        $stock_stmt = mysqli_prepare($con, $update_stock);
        mysqli_stmt_bind_param($stock_stmt, "iii", $item['quantity'], $item['quantity'], $item['product_id']);
        
        if (!mysqli_stmt_execute($stock_stmt)) {
            throw new Exception("Failed to update stock");
        }
        mysqli_stmt_close($stock_stmt);
    }
    
    // Record coupon usage if applicable
    if (isset($_SESSION['applied_coupon'])) {
        $coupon = $_SESSION['applied_coupon'];
        
        $usage_query = "INSERT INTO coupon_usage (offer_id, user_id, order_id, discount_amount) 
                        VALUES (?, ?, ?, ?)";
        $usage_stmt = mysqli_prepare($con, $usage_query);
        mysqli_stmt_bind_param($usage_stmt, "iiid", $coupon['id'], $user_id, $order_id, $coupon['discount']);
        mysqli_stmt_execute($usage_stmt);
        
        // Update offer used count
        $update_offer = "UPDATE offers SET used_count = used_count + 1 WHERE id = ?";
        $offer_stmt = mysqli_prepare($con, $update_offer);
        mysqli_stmt_bind_param($offer_stmt, "i", $coupon['id']);
        mysqli_stmt_execute($offer_stmt);
        
        unset($_SESSION['applied_coupon']);
    }
    
    // Clear cart
    clearCart($user_id);
    
    mysqli_commit($con);
    
    // Store order info in session
    $_SESSION['last_order'] = [
        'order_id' => $order_id,
        'order_number' => $order_number,
        'total' => $grand_total
    ];
    
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Order placed successfully'
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($con);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>