<?php
session_start();
include('../includes/db_config.php');

if(isset($_POST['customer_id']) && isset($_POST['action'])) {
    $customer_id = intval($_POST['customer_id']);
    $action = $_POST['action'];
    
    if($action == 'active') {
        $new_status = 'Active';
        $message = 'Customer has been activated successfully.';
        $alert_type = 'success';
    } elseif($action == 'inactive') {
        $new_status = 'Inactive';
        $message = 'Customer has been set to inactive successfully.';
        $alert_type = 'warning';
    } else {
        header('Location: customers.php');
        exit();
    }
    
    $update_query = "UPDATE register SET status = '$new_status' WHERE id = $customer_id AND role = 'user'";
    
    if(mysqli_query($con, $update_query)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $alert_type;
    } else {
        $_SESSION['message'] = 'Error updating customer status: ' . mysqli_error($con);
        $_SESSION['message_type'] = 'danger';
    }
    
    header('Location: view-customer.php?id=' . $customer_id);
    exit();
} else {
    header('Location: customers.php');
    exit();
}
?>