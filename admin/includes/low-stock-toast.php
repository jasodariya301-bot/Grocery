<?php
// admin/includes/low-stock-toast.php

function showLowStockToast() {
    global $con;
    
    $query = "SELECT COUNT(*) as count FROM products WHERE stock <= 5 AND stock > 0 AND status = 'in-stock'";
    $result = mysqli_query($con, $query);
    $data = mysqli_fetch_assoc($result);
    $critical_count = $data['count'];
    
    if ($critical_count == 0) {
        return;
    }
    ?>
    
    <!-- Low Stock Toast Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="lowStockToast">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Critical Stock Alert</strong>
                <small>Just now</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <p class="mb-2">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <strong><?php echo $critical_count; ?> product(s)</strong> have critically low stock (≤5 units)!
                </p>
                <a href="products.php?filter=critical" class="btn btn-danger btn-sm w-100">
                    <i class="fas fa-box"></i> Restock Now
                </a>
            </div>
        </div>
    </div>
    
    <script>
    // Auto-hide toast after 10 seconds
    setTimeout(function() {
        var toast = document.getElementById('lowStockToast');
        if (toast) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }
    }, 10000);
    </script>
    
    <?php
}
?>