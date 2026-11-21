<?php
session_start();
include('../includes/db_config.php');


// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create-offer.php');
    exit();
}

// Get form data
$offer_title = mysqli_real_escape_string($con, $_POST['offer_title']);
$discount_display = mysqli_real_escape_string($con, $_POST['discount_display']);
$description = mysqli_real_escape_string($con, $_POST['description'] ?? '');
$discount_type = mysqli_real_escape_string($con, $_POST['discount_type']);
$discount_value = floatval($_POST['discount_value']);
$min_purchase = floatval($_POST['min_purchase'] ?? 0);
$valid_from = mysqli_real_escape_string($con, $_POST['valid_from']);
$valid_to = mysqli_real_escape_string($con, $_POST['valid_to']);
$usage_limit = intval($_POST['usage_limit'] ?? 0);
$apply_to = mysqli_real_escape_string($con, $_POST['apply_to']);
$status = mysqli_real_escape_string($con, $_POST['status'] ?? 'active');

// Generate a unique coupon code automatically (since form doesn't have this field)
$coupon_code = 'OFFER_' . strtoupper(substr(md5(uniqid()), 0, 8));

// Handle category_id
$category_id_value = "NULL";
if ($apply_to == 'category' && isset($_POST['category_id']) && !empty($_POST['category_id'])) {
    $category_id_value = "'" . mysqli_real_escape_string($con, $_POST['category_id']) . "'";
}

// Validate required fields
$errors = [];

if (empty($offer_title)) {
    $errors[] = "Offer title is required";
}

if (empty($discount_display)) {
    $errors[] = "Discount display text is required";
}

if (empty($discount_type)) {
    $errors[] = "Discount type is required";
}

if ($discount_value <= 0) {
    $errors[] = "Discount value must be greater than 0";
}

if ($discount_type == 'percentage' && $discount_value > 100) {
    $errors[] = "Percentage discount cannot exceed 100%";
}

if (empty($valid_from)) {
    $errors[] = "Valid from date is required";
}

if (empty($valid_to)) {
    $errors[] = "Valid to date is required";
}

if (strtotime($valid_from) >= strtotime($valid_to)) {
    $errors[] = "Valid to date must be after valid from date";
}

if (empty($apply_to)) {
    $errors[] = "Apply to field is required";
}

if ($apply_to == 'category' && $category_id_value == "NULL") {
    $errors[] = "Please select a category";
}

if ($apply_to == 'products' && (!isset($_POST['product_ids']) || empty($_POST['product_ids']))) {
    $errors[] = "Please select at least one product";
}

// If errors exist, redirect back with error message
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: create-offer.php');
    exit();
}

// Begin transaction
mysqli_begin_transaction($con);

try {
    // Insert offer into database (using your exact table structure)
    $insert_query = "INSERT INTO offers (
        coupon_code,
        offer_title, 
        discount_display, 
        description, 
        discount_type, 
        discount_value, 
        min_purchase, 
        valid_from, 
        valid_to, 
        usage_limit, 
        used_count,
        apply_to, 
        category_id, 
        status, 
        created_at
    ) VALUES (
        '$coupon_code',
        '$offer_title',
        '$discount_display',
        '$description',
        '$discount_type',
        $discount_value,
        $min_purchase,
        '$valid_from',
        '$valid_to',
        $usage_limit,
        0,
        '$apply_to',
        $category_id_value,
        '$status',
        NOW()
    )";

    if (!mysqli_query($con, $insert_query)) {
        throw new Exception("Insert failed: " . mysqli_error($con));
    }
    
    $offer_id = mysqli_insert_id($con);
    
    // If apply_to is 'products', insert into offer_products table
    if ($apply_to == 'products' && isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
        foreach ($_POST['product_ids'] as $product_id) {
            $product_id = intval($product_id);
            if ($product_id > 0) {
                $product_insert_query = "INSERT INTO offer_products (offer_id, product_id) VALUES ($offer_id, $product_id)";
                if (!mysqli_query($con, $product_insert_query)) {
                    throw new Exception("Product insert failed: " . mysqli_error($con));
                }
            }
        }
    }
    
    // Commit transaction
    mysqli_commit($con);
    
    // Set success message
    $_SESSION['success'] = "Offer created successfully!";
    header('Location: offers.php');
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($con);
    
    // Set error message
    $_SESSION['error'] = "Error creating offer: " . $e->getMessage();
    header('Location: create-offer.php');
    exit();
}
?>