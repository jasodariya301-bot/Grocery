<?php 
include('includes/sidebar.php');
include('includes/header.php');
include('../includes/db_config.php');

// Fetch categories for dropdown
$cat_query = "SELECT DISTINCT category FROM products WHERE status='in-stock'";
$cat_result = mysqli_query($con, $cat_query);

// Fetch products for multiple select
$prod_query = "SELECT id, product_name FROM products WHERE status='in-stock' ORDER BY product_name";
$prod_result = mysqli_query($con, $prod_query);
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2>Create New Offer</h2>
        <a href="offer.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="offerForm" action="insert-offer.php" method="POST">
                <div class="row">
                    <!-- Offer Title -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Offer Title *</label>
                        <input type="text" name="offer_title" class="form-control" 
                               placeholder="e.g., Summer Special 50% OFF" required>
                        <small class="text-muted">Display title for the offer</small>
                    </div>

                    <!-- Discount Display Text -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Discount Display Text *</label>
                        <input type="text" name="discount_display" class="form-control" 
                               placeholder="e.g., 50% OFF or ₹500 OFF" required>
                        <small class="text-muted">This text will be shown on product cards</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control" 
                              placeholder="Describe the offer details..."></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Discount Type *</label>
                        <select name="discount_type" class="form-select" required id="discount_type">
                            <option value="">Select Type</option>
                            <option value="percentage">Percentage (%)</option>
                            <option value="flat">Flat Amount (₹)</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Discount Value *</label>
                        <input type="number" name="discount_value" step="0.01" 
                               class="form-control" required id="discount_value"
                               placeholder="Enter value">
                        <small id="discountHint" class="text-muted">Enter discount value</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Minimum Purchase</label>
                        <input type="number" name="min_purchase" step="0.01" 
                               class="form-control" value="0">
                        <small class="text-muted">0 = No minimum</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Valid From *</label>
                        <input type="datetime-local" name="valid_from" 
                               class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Valid To *</label>
                        <input type="datetime-local" name="valid_to" 
                               class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Usage Limit</label>
                        <input type="number" name="usage_limit" class="form-control" 
                               value="0" min="0">
                        <small class="text-muted">0 = Unlimited usage</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apply To *</label>
                        <select name="apply_to" class="form-select" required id="apply_to">
                            <option value="all">All Products</option>
                            <option value="category">Specific Category</option>
                            <option value="products">Specific Products</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3" id="category_div" style="display: none;">
                    <label class="form-label">Select Category</label>
                    <select name="category_id" class="form-select" id="category_select">
                        <option value="">Choose Category</option>
                        <?php while($cat = mysqli_fetch_assoc($cat_result)): ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>">
                            <?php echo ucfirst($cat['category']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3" id="products_div" style="display: none;">
                    <label class="form-label">Select Products (Ctrl+Click for multiple)</label>
                    <select name="product_ids[]" class="form-select" multiple size="5" id="products_select">
                        <?php while($prod = mysqli_fetch_assoc($prod_result)): ?>
                        <option value="<?php echo $prod['id']; ?>">
                            <?php echo htmlspecialchars($prod['product_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-muted">Hold Ctrl to select multiple products</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Offer Preview -->
                <div class="card bg-light mt-3 mb-3">
                    <div class="card-body">
                        <h6 class="mb-3">Offer Preview</h6>
                        <div id="offerPreview" class="d-flex align-items-center">
                            <span class="badge bg-danger px-3 py-2" id="previewBadge">
                                <i class="fas fa-tag me-1"></i>
                                <span id="previewText">Enter discount details</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="reset" class="btn btn-light">Reset</button>
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Offer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // Update preview when discount details change
    function updatePreview() {
        var displayText = $('input[name="discount_display"]').val();
        var discountType = $('#discount_type').val();
        var discountValue = $('#discount_value').val();
        
        if (displayText) {
            $('#previewText').text(displayText);
        } else if (discountType && discountValue) {
            if (discountType === 'percentage') {
                $('#previewText').text(discountValue + '% OFF');
                $('input[name="discount_display"]').val(discountValue + '% OFF');
            } else {
                $('#previewText').text('₹' + discountValue + ' OFF');
                $('input[name="discount_display"]').val('₹' + discountValue + ' OFF');
            }
        } else {
            $('#previewText').text('Enter discount details');
        }
    }
    
    // Update hint text based on discount type
    $('#discount_type').change(function(){
        var type = $(this).val();
        if (type === 'percentage') {
            $('#discountHint').text('Enter percentage (1-100)');
            $('#discount_value').attr('max', 100);
            $('#discount_value').attr('placeholder', 'e.g., 50');
        } else {
            $('#discountHint').text('Enter amount in ₹');
            $('#discount_value').removeAttr('max');
            $('#discount_value').attr('placeholder', 'e.g., 500');
        }
        updatePreview();
    });
    
    $('input[name="discount_display"]').on('input', updatePreview);
    $('#discount_value').on('input', updatePreview);
    
    // Toggle apply to options
    $('#apply_to').change(function(){
        var value = $(this).val();
        $('#category_div').hide();
        $('#products_div').hide();
        
        if(value == 'category') {
            $('#category_div').show();
            $('#category_select').prop('required', true);
            $('#products_select').prop('required', false);
        } else if(value == 'products') {
            $('#products_div').show();
            $('#products_select').prop('required', true);
            $('#category_select').prop('required', false);
        } else {
            $('#category_select').prop('required', false);
            $('#products_select').prop('required', false);
        }
    });

    // Form validation
    $("#offerForm").validate({
        rules: {
            offer_title: { required: true, minlength: 3 },
            discount_display: { required: true },
            discount_type: { required: true },
            discount_value: { 
                required: true, 
                number: true, 
                min: 0.01 
            },
            valid_from: { required: true },
            valid_to: { required: true },
            apply_to: { required: true }
        },
        messages: {
            offer_title: "Please enter offer title",
            discount_display: "Please enter discount display text",
            discount_type: "Select discount type",
            discount_value: {
                required: "Enter discount value",
                number: "Enter a valid number",
                min: "Discount must be greater than 0"
            },
            valid_from: "Select start date",
            valid_to: "Select end date",
            apply_to: "Select application scope"
        },
        errorClass: "text-danger",
        errorElement: "small",
        highlight: function(e){ $(e).addClass("is-invalid"); },
        unhighlight: function(e){ $(e).removeClass("is-invalid"); }
    });
    
    // Custom date range validation
    $.validator.addMethod("dateRange", function(value, element) {
        var validFrom = $('input[name="valid_from"]').val();
        var validTo = $('input[name="valid_to"]').val();
        if(validFrom && validTo) {
            return new Date(validFrom) < new Date(validTo);
        }
        return true;
    }, "End date must be after start date");
    
    $('input[name="valid_to"]').rules("add", { dateRange: true });
    
    // Custom percentage validation
    $.validator.addMethod("percentageMax", function(value, element) {
        if ($('#discount_type').val() === 'percentage') {
            return parseFloat(value) <= 100;
        }
        return true;
    }, "Percentage cannot exceed 100%");
    
    $('#discount_value').rules("add", { percentageMax: true });
});
</script>

<?php include('includes/footer.php'); ?>