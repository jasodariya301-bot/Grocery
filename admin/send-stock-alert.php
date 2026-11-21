<?php
// admin/send-stock-alert.php
require_once '../includes/db_config.php';

function sendLowStockEmailAlert() {
    global $con;
    
    // Get critical low stock products
    $query = "SELECT product_name, stock FROM products 
              WHERE stock <= 5 AND stock > 0 AND status = 'in-stock'";
    $result = mysqli_query($con, $query);
    
    if (mysqli_num_rows($result) == 0) {
        return;
    }
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    // Email content
    $to = "admin@bharatmart.com";
    $subject = "⚠️ URGENT: Low Stock Alert - BharatMart";
    
    $message = "<html><body>";
    $message .= "<h2>Low Stock Alert</h2>";
    $message .= "<p>The following products have critically low stock:</p>";
    $message .= "<table border='1' cellpadding='5'>";
    $message .= "<tr><th>Product</th><th>Current Stock</th></tr>";
    
    foreach ($products as $product) {
        $message .= "<tr>";
        $message .= "<td>" . htmlspecialchars($product['product_name']) . "</td>";
        $message .= "<td style='color:red'><strong>" . $product['stock'] . " units</strong></td>";
        $message .= "</tr>";
    }
    
    $message .= "</table>";
    $message .= "<p>Please restock these items immediately.</p>";
    $message .= "<p><a href='http://localhost/Grocery-website/admin/products.php'>View in Admin Panel</a></p>";
    $message .= "</body></html>";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: alerts@bharatmart.com" . "\r\n";
    
    mail($to, $subject, $message, $headers);
}

// Run this check (you can set up a cron job to run this daily)
sendLowStockEmailAlert();
?>