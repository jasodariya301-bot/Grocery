<?php
session_start();
include 'includes/header.php';

if (!isset($_SESSION['last_order'])) {
    header("Location: index.php");
    exit();
}

$order = $_SESSION['last_order'];
unset($_SESSION['last_order']);
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 70px;"></i>
                    </div>

                    <h2 class="fw-bold text-success mb-3">
                        Order Placed Successfully!
                    </h2>

                    <p class="text-muted mb-4">
                        Thank you for shopping with us. Your order has been placed successfully.
                    </p>

                    <div class="bg-light rounded p-4 mb-4 text-start">
                        <div class="row mb-2">
                            <div class="col-6 text-muted">Order ID:</div>
                            <div class="col-6 fw-semibold"><?php echo $order['order_number']; ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Payment Method:</div>
                            <div class="col-6 fw-semibold"><?php echo $order['payment_method']; ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Total Amount:</div>
                            <div class="col-6 fw-semibold text-success">₹<?php echo number_format($order['grand_total'], 2); ?></div>
                        </div>

                        <div class="row">
                            <div class="col-6 text-muted">Estimated Delivery:</div>
                            <div class="col-6 fw-semibold text-success">
                                2 - 3 Business Days
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="index.php" class="btn btn-success px-4">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                        <a href="my-orders.php" class="btn btn-outline-dark px-4">
                            <i class="fas fa-box me-2"></i>View Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>