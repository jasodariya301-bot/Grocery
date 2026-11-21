<?php
session_start();
require_once 'includes/db_config.php';
require_once 'includes/razorpay_config.php';

header('Content-Type: application/json');

try {
    // Check login
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Please login to continue');
    }

    $user_id = $_SESSION['user_id'];

    // Get form data
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $mobile = mysqli_real_escape_string($con, $_POST['mobile']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $payment_method = mysqli_real_escape_string($con, $_POST['payment_method']);

    // Validation
    if (empty($name) || empty($email) || empty($mobile) || empty($address)) {
        throw new Exception('All fields are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        throw new Exception('Invalid mobile number');
    }

    $payment_status = 'pending';
    $razorpay_payment_id = null;
    $razorpay_order_id = null;

    // Handle Online Payment
    if ($payment_method == 'Online') {
        $razorpay_payment_id = $_POST['razorpay_payment_id'] ?? null;
        $razorpay_order_id = $_POST['razorpay_order_id'] ?? null;
        $razorpay_signature = $_POST['razorpay_signature'] ?? null;

        if (!$razorpay_payment_id || !$razorpay_order_id || !$razorpay_signature) {
            throw new Exception('Missing payment information');
        }

        // Verify payment signature using SDK
        $api = getRazorpayApi();
        
        $attributes = [
            'razorpay_order_id' => $razorpay_order_id,
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_signature' => $razorpay_signature
        ];

        try {
            $api->utility->verifyPaymentSignature($attributes);
            $payment_status = 'paid';
        } catch (Exception $e) {
            throw new Exception('Payment verification failed: ' . $e->getMessage());
        }
    }

    // Get cart items
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

    $cart_items = [];
    $subtotal = 0;
    
    while ($item = mysqli_fetch_assoc($cart_result)) {
        // Check stock
        if ($item['stock'] < $item['quantity']) {
            throw new Exception("Sorry, only {$item['stock']} units of '{$item['product_name']}' are available.");
        }
        $cart_items[] = $item;
        $subtotal += $item['price'] * $item['quantity'];
    }

    $gst_amount = $subtotal * 0.05;
    $grand_total = $subtotal + $gst_amount;
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    // Begin transaction
    mysqli_begin_transaction($con);

    // Insert order
    $order_query = "INSERT INTO orders (
        user_id, order_number, customer_name, customer_mobile, 
        customer_address, payment_method, payment_status, 
        subtotal, gst_amount, total_amount, 
        razorpay_payment_id, razorpay_order_id, order_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = mysqli_prepare($con, $order_query);
    mysqli_stmt_bind_param($stmt, "issssssdddss", 
        $user_id, $order_number, $name, $mobile, $address, 
        $payment_method, $payment_status, $subtotal, $gst_amount, $grand_total,
        $razorpay_payment_id, $razorpay_order_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to create order: ' . mysqli_error($con));
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
            throw new Exception('Failed to add order items');
        }

        // Update stock
        $update_stock = "UPDATE products SET stock = stock - ?, 
                        status = CASE WHEN (stock - ?) <= 0 THEN 'out-of-stock' ELSE 'in-stock' END 
                        WHERE id = ?";
        $stock_stmt = mysqli_prepare($con, $update_stock);
        mysqli_stmt_bind_param($stock_stmt, "iii", $item['quantity'], $item['quantity'], $item['product_id']);
        mysqli_stmt_execute($stock_stmt);
        mysqli_stmt_close($stock_stmt);
    }

    // Clear cart
    $clear_cart = "DELETE FROM cart WHERE user_id = ?";
    $clear_stmt = mysqli_prepare($con, $clear_cart);
    mysqli_stmt_bind_param($clear_stmt, "i", $user_id);
    mysqli_stmt_execute($clear_stmt);

    // Commit transaction
    mysqli_commit($con);

    // Success response
    $_SESSION['last_order'] = [
        'order_id' => $order_id,
        'order_number' => $order_number,
        'total' => $grand_total
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'redirect' => 'order-confirmation.php?order_id=' . $order_id
    ]);

} catch (Exception $e) {
    if (isset($con) && mysqli_ping($con)) {
        mysqli_rollback($con);
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>