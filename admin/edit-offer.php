<?php 
include('includes/sidebar.php');
include('includes/header.php');
include('../includes/db_config.php');

// Check if ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Offer ID not provided!";
    header('Location: offer.php');
    exit();
}

$offer_id = intval($_GET['id']);

// Fetch offer details
$query = "SELECT * FROM offers WHERE id = $offer_id";
$result = mysqli_query($con, $query);

if(!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Offer not found!";
    header('Location: offer.php');
    exit();
}

$offer = mysqli_fetch_assoc($result);

// Format dates for datetime-local input
$valid_from = date('Y-m-d\TH:i', strtotime($offer['valid_from']));
$valid_to = date('Y-m-d\TH:i', strtotime($offer['valid_to']));

// Generate discount display if not set (for backward compatibility)
if(!isset($offer['discount_display']) || empty($offer['discount_display'])) {
    if($offer['discount_type'] == 'percentage') {
        $offer['discount_display'] = $offer['discount_value'] . '% OFF';
    } else {
        $offer['discount_display'] = '₹' . $offer['discount_value'] . ' OFF';
    }
}

// Fetch categories for dropdown
$cat_query = "SELECT DISTINCT category FROM products WHERE status='in-stock'";
$cat_result = mysqli_query($con, $cat_query);

// Fetch products for multiple select
$prod_query = "SELECT id, product_name FROM products WHERE status='in-stock' ORDER BY product_name";
$prod_result = mysqli_query($con, $prod_query);

// Fetch already selected products for this offer
$selected_products = [];
if($offer['apply_to'] == 'products') {
    $selected_query = "SELECT product_id FROM offer_products WHERE offer_id = $offer_id";
    $selected_result = mysqli_query($con, $selected_query);
    while($row = mysqli_fetch_assoc($selected_result)) {
        $selected_products[] = $row['product_id'];
    }
}
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Offer</h2>
        <a href="offer.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="editOfferForm" action="update-offer.php" method="POST">
                <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">

                <div class="row">
                    <!-- Offer Title -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Offer Title *</label>
                        <input type="text" name="offer_title" class="form-control"
                               value="<?php echo htmlspecialchars($offer['offer_title']); ?>" required>
                    </div>

                    <!-- Discount Display Text -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Discount Display Text *</label>
                        <input type="text" name="discount_display" class="form-control" 
                               value="<?php echo htmlspecialchars($offer['discount_display'] ?? ''); ?>" required>
                        <small class="text-muted">This text will be shown on product cards</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control"><?php echo htmlspecialchars($offer['description']); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Discount Type *</label>
                        <select name="discount_type" class="form-select" required id="discount_type">
                            <option value="">Select Type</option>
                            <option value="percentage" <?php echo $offer['discount_type'] == 'percentage' ? 'selected' : ''; ?>>
                                Percentage (%)
                            </option>
                            <option value="flat" <?php echo $offer['discount_type'] == 'flat' ? 'selected' : ''; ?>>
                                Flat Amount (₹)
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Discount Value *</label>
                        <input type="number" name="discount_value" step="0.01" class="form-control" 
                               value="<?php echo $offer['discount_value']; ?>" required id="discount_value">
                        <small id="discountHint" class="text-muted">Enter discount value</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Minimum Purchase</label>
                        <input type="number" name="min_purchase" step="0.01"
                               class="form-control" value="<?php echo $offer['min_purchase']; ?>">
                        <small class="text-muted">0 = No minimum</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Valid From *</label>
                        <input type="datetime-local" name="valid_from"
                               class="form-control" value="<?php echo $valid_from; ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Valid To *</label>
                        <input type="datetime-local" name="valid_to"
                               class="form-control" value="<?php echo $valid_to; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Usage Limit</label>
                        <input type="number" name="usage_limit" class="form-control" 
                               value="<?php echo $offer['usage_limit']; ?>" min="0">
                        <small class="text-muted">0 = Unlimited | Used: <?php echo $offer['used_count']; ?> times</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apply To *</label>
                        <select name="apply_to" class="form-select" required id="apply_to">
                            <option value="all" <?php echo $offer['apply_to'] == 'all' ? 'selected' : ''; ?>>
                                All Products
                            </option>
                            <option value="category" <?php echo $offer['apply_to'] == 'category' ? 'selected' : ''; ?>>
                                Specific Category
                            </option>
                            <option value="products" <?php echo $offer['apply_to'] == 'products' ? 'selected' : ''; ?>>
                                Specific Products
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Category Selection -->
                <div class="mb-3" id="category_div" style="display: <?php echo $offer['apply_to'] == 'category' ? 'block' : 'none'; ?>;">
                    <label class="form-label">Select Category</label>
                    <select name="category_id" class="form-select" id="category_select">
                        <option value="">Choose Category</option>
                        <?php 
                        mysqli_data_seek($cat_result, 0);
                        while($cat = mysqli_fetch_assoc($cat_result)): 
                        ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                            <?php echo ($offer['category_id'] == $cat['category']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($cat['category']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Products Selection -->
                <div class="mb-3" id="products_div" style="display: <?php echo $offer['apply_to'] == 'products' ? 'block' : 'none'; ?>;">
                    <label class="form-label">Select Products (Ctrl+Click for multiple)</label>
                    <select name="product_ids[]" class="form-select" multiple size="6" id="products_select">
                        <?php 
                        mysqli_data_seek($prod_result, 0);
                        while($prod = mysqli_fetch_assoc($prod_result)): 
                        ?>
                        <option value="<?php echo $prod['id']; ?>"
                            <?php echo in_array($prod['id'], $selected_products) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($prod['product_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-muted">Hold Ctrl to select multiple products</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo $offer['status'] == 'active' ? 'selected' : ''; ?>>
                                Active
                            </option>
                            <option value="inactive" <?php echo $offer['status'] == 'inactive' ? 'selected' : ''; ?>>
                                Inactive
                            </option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Created Date</label>
                        <input type="text" class="form-control" 
                               value="<?php echo date('d M Y, h:i A', strtotime($offer['created_at'])); ?>" 
                               readonly disabled>
                    </div>
                </div>

                <!-- Offer Preview -->
                <div class="card bg-light mt-3 mb-3">
                    <div class="card-body">
                        <h6 class="mb-3">Offer Preview</h6>
                        <div id="offerPreview" class="d-flex align-items-center">
                            <span class="badge bg-danger px-3 py-2" id="previewBadge">
                                <i class="fas fa-tag me-1"></i>
                                <span id="previewText"><?php echo htmlspecialchars($offer['discount_display'] ?? 'Enter discount details'); ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <a href="offer.php" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Update Offer
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
        } else {
            $('#discountHint').text('Enter amount in ₹');
            $('#discount_value').removeAttr('max');
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
    $("#editOfferForm").validate({
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