<?php
session_start();
include('includes/db_config.php');

// Test Razorpay API connection
$key_id = "rzp_test_SdgIYwPRF3qQzF"; // Replace with your actual key
$key_secret = "gPYo0IndbBkLX5EHSaDoQ7c9"; // Replace with your actual secret

echo "<h3>Testing Razorpay API Connection:</h3>";

// Test 1: Check if API keys are set
if ($key_id == "rzp_test_SdgIYwPRF3qQzF" || $key_secret == "gPYo0IndbBkLX5EHSaDoQ7c9") {
    echo "<p style='color:red;'>❌ Error: Please set your actual Razorpay API keys in test_razorpay.php</p>";
} else {
    echo "<p style='color:green;'>✅ API keys are set</p>";
}

// Test 2: Try to create a test order
$url = "https://api.razorpay.com/v1/orders";
$amount = 100; // ₹1.00
$amount_in_paise = $amount * 100;

$data = [
    'amount' => $amount_in_paise,
    'currency' => 'INR',
    'receipt' => 'test_' . time(),
    'payment_capture' => 1
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For testing only

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<h4>API Response:</h4>";
echo "HTTP Code: " . $http_code . "<br>";

if ($curl_error) {
    echo "<p style='color:red;'>❌ CURL Error: " . $curl_error . "</p>";
} else {
    echo "<p style='color:green;'>✅ CURL request successful</p>";
}

if ($http_code == 200) {
    $order = json_decode($response, true);
    echo "<p style='color:green;'>✅ Successfully created test order</p>";
    echo "<pre>";
    print_r($order);
    echo "</pre>";
} else {
    echo "<p style='color:red;'>❌ Failed to create order</p>";
    echo "Error Response: " . $response . "<br>";
    
    $error_data = json_decode($response, true);
    if (isset($error_data['error']['description'])) {
        echo "Error Description: " . $error_data['error']['description'] . "<br>";
    }
}

// Test 3: Check PHP configuration
echo "<h4>PHP Configuration:</h4>";
echo "cURL enabled: " . (function_exists('curl_version') ? '✅ Yes' : '❌ No') . "<br>";
echo "OpenSSL enabled: " . (extension_loaded('openssl') ? '✅ Yes' : '❌ No') . "<br>";
echo "JSON enabled: " . (function_exists('json_encode') ? '✅ Yes' : '❌ No') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test 4: Check server connectivity
echo "<h4>Server Connectivity:</h4>";
$connected = @fsockopen("api.razorpay.com", 443, $errno, $errstr, 5);
if ($connected) {
    echo "<p style='color:green;'>✅ Can connect to api.razorpay.com:443</p>";
    fclose($connected);
} else {
    echo "<p style='color:red;'>❌ Cannot connect to Razorpay API (Error: $errstr)</p>";
    echo "This might be due to firewall or network restrictions.<br>";
}
?>