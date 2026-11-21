<?php
session_start();
require_once 'includes/db_config.php';
require_once 'includes/razorpay_config.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Please login to continue');
    }

    $user_id = $_SESSION['user_id'];

    // Get cart items directly (no need for cart_functions.php)
    $cart_query = "SELECT c.*, p.product_name, p.price, p.weight, p.stock 
                   FROM cart c 
                   JOIN products p ON c.product_id = p.id 
                   WHERE c.user_id = ?";
    
    $stmt = mysqli_prepare($con, $cart_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $cart_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($cart_result) == 0) {
        throw new Exception('Your cart is empty');
    }

    // Calculate totals
    $subtotal = 0;
    $cart_items = [];
    
    while ($item = mysqli_fetch_assoc($cart_result)) {
        $cart_items[] = $item;
        $subtotal += $item['price'] * $item['quantity'];
    }

    $gst_amount = $subtotal * 0.05; // 5% GST
    $grand_total = $subtotal + $gst_amount;
    $amount_in_paise = round($grand_total * 100);

    if ($amount_in_paise < 100) {
        throw new Exception('Minimum order amount should be ₹1');
    }

    // Create Razorpay Order using SDK
    $api = getRazorpayApi();
    
    $orderData = [
        'receipt'         => 'rcpt_' . time() . '_' . $user_id,
        'amount'          => $amount_in_paise,
        'currency'        => 'INR',
        'payment_capture' => 1 // Auto capture
    ];

    $razorpayOrder = $api->order->create($orderData);

    // Store order info in session for later verification
    $_SESSION['razorpay_order_id'] = $razorpayOrder['id'];
    $_SESSION['order_amount'] = $grand_total;
    $_SESSION['cart_items'] = $cart_items;

    echo json_encode([
        'success'   => true,
        'order_id'  => $razorpayOrder['id'],
        'amount'    => $razorpayOrder['amount'],
        'currency'  => $razorpayOrder['currency'],
        'key_id'    => RAZORPAY_KEY_ID
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>