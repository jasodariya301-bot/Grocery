<?php
include('../includes/db_config.php');
session_start();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $offer_id = intval($_POST['offer_id']);
    $coupon_code = mysqli_real_escape_string($con, $_POST['coupon_code']);
    $offer_title = mysqli_real_escape_string($con, $_POST['offer_title']);
    $description = mysqli_real_escape_string($con, $_POST['description'] ?? '');
    $discount_type = mysqli_real_escape_string($con, $_POST['discount_type']);
    $discount_value = floatval($_POST['discount_value']);
    $min_purchase = floatval($_POST['min_purchase'] ?? 0);
    $valid_from = mysqli_real_escape_string($con, $_POST['valid_from']);
    $valid_to = mysqli_real_escape_string($con, $_POST['valid_to']);
    $usage_limit = intval($_POST['usage_limit'] ?? 0);
    $apply_to = mysqli_real_escape_string($con, $_POST['apply_to']);
    $status = mysqli_real_escape_string($con, $_POST['status'] ?? 'active');
    
    // Handle category_id properly
    $category_id = 'NULL';
    if($apply_to == 'category' && isset($_POST['category_id']) && !empty($_POST['category_id'])) {
        $category_id = "'" . mysqli_real_escape_string($con, $_POST['category_id']) . "'";
    }
    
    // Check if coupon code already exists for other offers
    $check_query = "SELECT id FROM offers WHERE coupon_code = '$coupon_code' AND id != $offer_id";
    $check_result = mysqli_query($con, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = "Coupon code already exists!";
        header("Location: edit-offer.php?id=$offer_id");
        exit();
    }
    
    // Update offer
    $update_query = "UPDATE offers SET 
                     coupon_code = '$coupon_code',
                     offer_title = '$offer_title',
                     description = '$description',
                     discount_type = '$discount_type',
                     discount_value = $discount_value,
                     min_purchase = $min_purchase,
                     valid_from = '$valid_from',
                     valid_to = '$valid_to',
                     usage_limit = $usage_limit,
                     apply_to = '$apply_to',
                     category_id = $category_id,
                     status = '$status'
                     WHERE id = $offer_id";
    
    if(mysqli_query($con, $update_query)) {
        // Delete existing product associations
        $delete_products = "DELETE FROM offer_products WHERE offer_id = $offer_id";
        mysqli_query($con, $delete_products);
        
        // Insert new product associations if applicable
        if($apply_to == 'products' && isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
            foreach($_POST['product_ids'] as $product_id) {
                $product_id = intval($product_id);
                if($product_id > 0) {
                    $prod_query = "INSERT INTO offer_products (offer_id, product_id) 
                                  VALUES ($offer_id, $product_id)";
                    mysqli_query($con, $prod_query);
                }
            }
        }
        
        $_SESSION['success'] = "Offer updated successfully!";
        header('Location: offers.php');
        exit();
    } else {
        $_SESSION['error'] = "Error updating offer: " . mysqli_error($con);
        header("Location: edit-offer.php?id=$offer_id");
        exit();
    }
} else {
    header('Location: offers.php');
    exit();
}
?>