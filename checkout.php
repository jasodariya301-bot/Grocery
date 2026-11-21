<?php 
include('includes/header.php');
require_once 'includes/razorpay_config.php';

// After creating order, record coupon usage if applicable
if(isset($_SESSION['applied_coupon']) && isset($order_id)) {
    $coupon = $_SESSION['applied_coupon'];
    
    // Record coupon usage
    $usage_query = "INSERT INTO coupon_usage (offer_id, user_id, order_id, discount_amount) 
                    VALUES ({$coupon['id']}, $user_id, $order_id, {$coupon['discount']})";
    mysqli_query($con, $usage_query);
    
    // Update offer used count
    $update_query = "UPDATE offers SET used_count = used_count + 1 WHERE id = {$coupon['id']}";
    mysqli_query($con, $update_query);
    
    // Clear applied coupon from session
    unset($_SESSION['applied_coupon']);
}
?>

<!-- Add Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<div class="container my-5">
    <h2 class="mb-4 fw-bold">
        <i class="fas fa-credit-card text-success"></i> Checkout
    </h2>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">

             <form id="checkoutForm" method="POST" novalidate>
                <!-- Full Name -->
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" id="customerName" class="form-control" placeholder="Enter full name" required>
                </div>

                <!-- Email (Required for Razorpay) -->
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" id="customerEmail" class="form-control" placeholder="Enter email address" required>
                </div>

                <!-- Mobile -->
                <div class="mb-3">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile" id="customerMobile" class="form-control" placeholder="10-digit mobile number" required>
                </div>

                <!-- Address -->
                <div class="mb-3">
                    <label class="form-label">Delivery Address</label>
                    <textarea name="address" id="customerAddress" class="form-control" rows="3" placeholder="Enter full address" required></textarea>
                </div>

                <!-- Payment Method -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Payment Method</label>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="COD" checked>
                        <label class="form-check-label" for="cod">
                            <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery (COD)
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="online" value="Online">
                        <label class="form-check-label" for="online">
                            <i class="fas fa-credit-card me-2"></i>Online Payment (UPI / Card / NetBanking)
                        </label>
                    </div>

                    <!-- Error message placeholder -->
                    <small class="text-danger d-none" id="paymentError">
                        Please select a payment method
                    </small>
                </div>

                <!-- Hidden field for Razorpay Payment ID -->
                <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
                <input type="hidden" name="razorpay_signature" id="razorpay_signature">

                <!-- Place Order Button -->
                <button type="submit" id="placeOrderBtn" class="btn btn-success w-100 py-2">
                    <i class="fas fa-check-circle me-2"></i>Place Order
                </button>

                <!-- Loading Spinner (Hidden by default) -->
                <div id="loadingSpinner" class="text-center d-none mt-3">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Processing your order...</p>
                </div>

            </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
    let razorpayOrderId = null;
    let isProcessing = false;

    $("#checkoutForm").on("submit", function (e) {
        e.preventDefault();
        
        if (isProcessing) return;
        
        let isValid = validateForm();
        
        if (isValid) {
            let paymentMethod = $("input[name='payment_method']:checked").val();
            
            if (paymentMethod === 'Online') {
                // Handle Online Payment
                processOnlinePayment();
            } else {
                // Handle COD
                processCODOrder();
            }
        }
    });

    function validateForm() {
        let isValid = true;
        $(".error-msg").remove();
        $(".form-control").removeClass("is-invalid");
        $("#paymentError").addClass("d-none");

        let name = $("#customerName").val().trim();
        let email = $("#customerEmail").val().trim();
        let mobile = $("#customerMobile").val().trim();
        let address = $("#customerAddress").val().trim();
        let payment = $("input[name='payment_method']:checked").val();

        // Name validation
        if (name === "") {
            isValid = false;
            $("#customerName").addClass("is-invalid")
                .after("<small class='text-danger error-msg'>Full Name is required</small>");
        }

        // Email validation
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email === "") {
            isValid = false;
            $("#customerEmail").addClass("is-invalid")
                .after("<small class='text-danger error-msg'>Email is required</small>");
        } else if (!emailPattern.test(email)) {
            isValid = false;
            $("#customerEmail").addClass("is-invalid")
                .after("<small class='text-danger error-msg'>Enter valid email address</small>");
        }

        // Mobile validation
        let mobilePattern = /^[0-9]{10}$/;
        if (mobile === "") {
            isValid = false;
            $("#customerMobile").addClass("is-invalid")
                .after("<small class='text-danger error-msg'>Mobile number is required</small>");
        } else if (!mobilePattern.test(mobile)) {
            isValid = false;
            $("#customerMobile").addClass("is-invalid")
                .after("<small class='text-danger error-msg'>Enter valid 10-digit mobile number</small>");
        }

        // Address validation
        if (address === "") {
            isValid = false;
            $("#customerAddress").addClass("is-invalid")
                .after("<small class='text-danger error-msg'>Delivery address is required</small>");
        }

        // Payment validation
        if (!payment) {
            isValid = false;
            $("#paymentError").removeClass("d-none");
        }

        return isValid;
    }

    function processOnlinePayment() {
        isProcessing = true;
        showLoading(true);
        
        // Create Razorpay Order
        $.ajax({
            url: 'create_razorpay_order.php',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    razorpayOrderId = response.order_id;
                    initiateRazorpayPayment(response);
                } else {
                    showLoading(false);
                    alert('Error: ' + response.message);
                    isProcessing = false;
                }
            },
            error: function() {
                showLoading(false);
                alert('Error creating payment order. Please try again.');
                isProcessing = false;
            }
        });
    }

    function initiateRazorpayPayment(orderData) {
        showLoading(false);
        
        var options = {
            "key": orderData.key_id,
            "amount": orderData.amount,
            "currency": orderData.currency,
            "name": "BharatMart Grocery",
            "description": "Order Payment",
            "image": "assets/images/logo.png",
            "order_id": orderData.order_id,
            "handler": function (response) {
                // Payment successful
                handlePaymentSuccess(response);
            },
            "prefill": {
                "name": $("#customerName").val(),
                "email": $("#customerEmail").val(),
                "contact": $("#customerMobile").val()
            },
            "notes": {
                "address": $("#customerAddress").val()
            },
            "theme": {
                "color": "#28a745"
            },
            "modal": {
                "ondismiss": function() {
                    isProcessing = false;
                    showLoading(false);
                    alert('Payment cancelled. You can try again or choose COD.');
                }
            }
        };
        
        var rzp = new Razorpay(options);
        
        rzp.on('payment.failed', function (response) {
            isProcessing = false;
            showLoading(false);
            alert('Payment Failed: ' + response.error.description);
            console.log(response.error);
        });
        
        rzp.open();
    }

    function handlePaymentSuccess(response) {
        showLoading(true);
        
        // Set payment details
        $("#razorpay_payment_id").val(response.razorpay_payment_id);
        $("#razorpay_order_id").val(response.razorpay_order_id);
        $("#razorpay_signature").val(response.razorpay_signature);
        
        // Submit order with payment details
        submitOrderWithPayment();
    }

    function submitOrderWithPayment() {
        let formData = {
            name: $("#customerName").val(),
            email: $("#customerEmail").val(),
            mobile: $("#customerMobile").val(),
            address: $("#customerAddress").val(),
            payment_method: 'Online',
            razorpay_payment_id: $("#razorpay_payment_id").val(),
            razorpay_order_id: $("#razorpay_order_id").val(),
            razorpay_signature: $("#razorpay_signature").val()
        };
        
        $.ajax({
            url: 'place_order.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    showLoading(false);
                    alert('Error: ' + response.message);
                    isProcessing = false;
                }
            },
            error: function() {
                showLoading(false);
                alert('Error processing order. Please contact support.');
                isProcessing = false;
            }
        });
    }

    function processCODOrder() {
        isProcessing = true;
        showLoading(true);
        
        let formData = {
            name: $("#customerName").val(),
            email: $("#customerEmail").val(),
            mobile: $("#customerMobile").val(),
            address: $("#customerAddress").val(),
            payment_method: 'COD'
        };
        
        $.ajax({
            url: 'place_order.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    showLoading(false);
                    alert('Error: ' + response.message);
                    isProcessing = false;
                }
            },
            error: function() {
                showLoading(false);
                alert('Error processing order. Please try again.');
                isProcessing = false;
            }
        });
    }

    function showLoading(show) {
        if (show) {
            $("#placeOrderBtn").prop('disabled', true);
            $("#loadingSpinner").removeClass('d-none');
            $("#checkoutForm input, #checkoutForm textarea, #checkoutForm button").prop('disabled', true);
        } else {
            $("#placeOrderBtn").prop('disabled', false);
            $("#loadingSpinner").addClass('d-none');
            $("#checkoutForm input, #checkoutForm textarea").prop('disabled', false);
            $("input[name='payment_method']").prop('disabled', false);
        }
    }
});
</script>