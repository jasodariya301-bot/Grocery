<?php 
session_start();
include('includes/sidebar.php');
include('includes/header.php');

// Handle form submission

    include('../includes/db_config.php');

      $cat_query = "SELECT * FROM product_categories WHERE status='Active' ORDER BY category_name ASC";
$cat_result = mysqli_query($con, $cat_query);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $weight = mysqli_real_escape_string($con, $_POST['weight']);
    $stock = mysqli_real_escape_string($con, $_POST['stock']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $description = mysqli_real_escape_string($con, $_POST['description']);

  
    // Handle file upload
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/products/";
        
        // Create directory if not exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        
        // Check if image file is valid
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // File uploaded successfully
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error = "File is not an image.";
        }
    }
    
    // Insert into database
    $query = "INSERT INTO products (product_name, category, price, weight, stock, description, image, status) 
              VALUES ('$product_name', '$category', '$price', '$weight', '$stock', '$description', '$image', '$status')";
    
    if(mysqli_query($con, $query)) {
        $_SESSION['success'] = "Product added successfully!";
        header("Location: products.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($con);
    }
}
?>

<style>
small.text-danger {
    font-weight: 500;
}
</style>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2>Add New Product</h2>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <?php if(isset($error)) { ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Product Information</h5>
        </div>
        <div class="card-body">
            <form id="productForm" action="" method="POST" enctype="multipart/form-data">

                <div class="row">
                    <!-- Product Name -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" class="form-control" placeholder="Enter product name" required>
                        <small class="text-muted">Maximum 255 characters</small>
                    </div>

                    <!-- Category -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
    <option value="">-- Select Category --</option>

    <?php while($cat = mysqli_fetch_assoc($cat_result)) { ?>
        <option value="<?php echo $cat['category_name']; ?>">
            <?php echo ucfirst($cat['category_name']); ?>
        </option>
    <?php } ?>

</select>
                    </div>
                </div>

                <div class="row">
                    <!-- Price -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="price" step="0.01" min="0.01" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>

                    <!-- Weight -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Weight / Quantity <span class="text-danger">*</span></label>
                        <input type="text" name="weight" class="form-control" placeholder="e.g., 500gm, 1kg, 1L, 10pcs" required>
                        <small class="text-muted">Enter with unit (gm, kg, L, pcs)</small>
                    </div>

                    <!-- Stock -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control" placeholder="0" min="0" required>
                    </div>
                </div>

                <div class="row">
                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="in-stock" selected>In Stock</option>
                            <option value="out-of-stock">Out of Stock</option>
                        </select>
                    </div>

                    <!-- Image -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Image <span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                        <small class="text-muted">Allowed: JPG, JPEG, PNG, WEBP, GIF (Max: 2MB)</small>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">Product Description <span class="text-danger">*</span></label>
                    <textarea name="description" rows="5" class="form-control" placeholder="Enter detailed product description..." required></textarea>
                    <small class="text-muted">Minimum 10 characters</small>
                </div>

                <!-- Additional Options (Optional) -->
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="featuredProduct" name="featured">
                        <label class="form-check-label" for="featuredProduct">Mark as Featured Product</label>
                    </div>
                </div>

                <hr>

                <div class="text-end">
                    <button type="reset" class="btn btn-light me-2">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Product
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Preview Image Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="Preview" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script>
$(document).ready(function () {
    
    // Image preview functionality
    $('input[name="image"]').change(function(e) {
        var file = e.target.files[0];
        if(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                // Show preview in a small thumbnail
                var preview = $('<img src="'+e.target.result+'" width="100" class="mt-2 rounded">');
                $('.image-preview').remove();
                $(this).after('<div class="image-preview mt-2"></div>');
                $('.image-preview').html(preview);
                
                // Add click to enlarge
                preview.click(function() {
                    $('#previewImage').attr('src', e.target.result);
                    $('#imagePreviewModal').modal('show');
                });
            }.bind(this);
            reader.readAsDataURL(file);
        }
    });

    // Form validation
    $("#productForm").validate({
        rules: {
            product_name: {
                required: true,
                minlength: 3,
                maxlength: 255
            },
            category: {
                required: true
            },
            price: {
                required: true,
                number: true,
                min: 0.01,
                max: 999999.99
            },
            weight: {
                required: true,
                minlength: 2,
                maxlength: 50
            },
            stock: {
                required: true,
                digits: true,
                min: 0,
                max: 999999
            },
            image: {
                required: true,
                extension: "jpg|jpeg|png|webp|gif",
                filesize: 2048000 // 2MB in bytes
            },
            description: {
                required: true,
                minlength: 10,
                maxlength: 5000
            }
        },
        messages: {
            product_name: {
                required: "Please enter product name",
                minlength: "Product name must be at least 3 characters"
            },
            category: "Please select a category",
            price: {
                required: "Please enter price",
                number: "Please enter a valid price",
                min: "Price must be greater than 0"
            },
            weight: "Please enter weight/quantity",
            stock: {
                required: "Please enter stock quantity",
                digits: "Please enter a valid number",
                min: "Stock cannot be negative"
            },
            image: {
                required: "Please select a product image",
                extension: "Please upload valid image file (JPG, JPEG, PNG, WEBP, GIF)"
            },
            description: {
                required: "Please enter product description",
                minlength: "Description must be at least 10 characters"
            }
        },
        errorElement: "small",
        errorClass: "text-danger",
        highlight: function(element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function(element) {
            $(element).removeClass("is-invalid");
        },
        submitHandler: function(form) {
            // Show loading state
            $('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
            form.submit();
        }
    });

    // Custom method for file size validation
    $.validator.addMethod('filesize', function(value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    }, 'File size must be less than 2MB');

    // Auto-format price input
    $('input[name="price"]').on('blur', function() {
        var value = parseFloat($(this).val());
        if(!isNaN(value)) {
            $(this).val(value.toFixed(2));
        }
    });

    // Confirm before reset
    $('button[type="reset"]').click(function(e) {
        if(!confirm('Are you sure you want to reset all fields?')) {
            e.preventDefault();
        }
    });
});
</script>

<?php include('includes/footer.php'); ?>