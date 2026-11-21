<?php
session_start();
include('../includes/db_config.php');

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get image name to delete file
    $query = "SELECT image FROM products WHERE id = $id";
    $result = mysqli_query($con, $query);
    $product = mysqli_fetch_assoc($result);
    
    // Delete from database
    $delete_query = "DELETE FROM products WHERE id = $id";
    
    if(mysqli_query($con, $delete_query)) {
        // Delete image file
        if(!empty($product['image']) && file_exists("../uploads/products/".$product['image'])) {
            unlink("../uploads/products/".$product['image']);
        }
        $_SESSION['success'] = "Product deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting product: " . mysqli_error($con);
    }
}

header("Location: products.php");
exit();
?>