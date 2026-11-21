<?php

// Razorpay Configuration using Composer SDK
require_once __DIR__ . '/../vendor/autoload.php';

use Razorpay\Api\Api;

define('RAZORPAY_KEY_ID', '');
define('RAZORPAY_KEY_SECRET', '');

// Check if keys are configured
// Initialize Razorpay API
function getRazorpayApi() {
    return new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
}
?>