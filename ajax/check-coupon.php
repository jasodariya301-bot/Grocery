<?php
require_once '../../includes/db_config.php';

if (isset($_POST['coupon_code'])) {
    $coupon = mysqli_real_escape_string($con, $_POST['coupon_code']);
    
    $query = "SELECT id FROM offers WHERE coupon_code = '$coupon'";
    $result = mysqli_query($con, $query);
    
    if (mysqli_num_rows($result) > 0) {
        echo 'false'; // Coupon exists
    } else {
        echo 'true'; // Coupon available
    }
}
?>