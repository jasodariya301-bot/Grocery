<?php 
session_start();
include('../includes/db_config.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($con, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    $_SESSION['error'] = "Product not found!";
    header("Location: products.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $weight = mysqli_real_escape_string($con, $_POST['weight']);
    $stock = mysqli_real_escape_string($con, $_POST['stock']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $description = mysqli_real_escape_string($con, $_POST['description']);

    $image = $product['image'];

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/products/";
        $image = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            if(!empty($product['image']) && file_exists($target_dir . $product['image'])) {
                unlink($target_dir . $product['image']);
            }
        }
    }

    $update_query = "UPDATE products SET 
        product_name='$product_name',
        category='$category',
        price='$price',
        weight='$weight',
        stock='$stock',
        description='$description',
        image='$image',
        status='$status'
        WHERE id=$id";

    if(mysqli_query($con, $update_query)) {
        $_SESSION['success'] = "Product updated successfully!";
        header("Location: products.php");
        exit();
    }
}

// FETCH CATEGORY AFTER LOGIC
$cat_query = "SELECT * FROM product_categories WHERE status='Active'";
$cat_result = mysqli_query($con, $cat_query);

// NOW INCLUDE UI FILES
include('includes/sidebar.php');
include('includes/header.php');
?>

<div class="content-wrapper">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Product</h2>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <?php if(isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php } ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="productForm" action="" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="product_name" class="form-control" 
                               value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category *</label>
                       <select name="category" class="form-select" required>
                            <option value="">Select Category</option>

                            <?php while($cat = mysqli_fetch_assoc($cat_result)) { ?>
                                <option value="<?php echo $cat['category_name']; ?>"
                                    <?php echo ($product['category'] == $cat['category_name']) ? 'selected' : ''; ?>>
                                    
                                    <?php echo ucfirst($cat['category_name']); ?>
                                </option>
                            <?php } ?>

                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Price (₹) *</label>
                        <input type="number" name="price" step="0.01" class="form-control" 
                               value="<?php echo $product['price']; ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Weight *</label>
                        <input type="text" name="weight" class="form-control" 
                               value="<?php echo htmlspecialchars($product['weight']); ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stock *</label>
                        <input type="number" name="stock" class="form-control" 
                               value="<?php echo $product['stock']; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select">
                            <option value="in-stock" <?php echo $product['status'] == 'in-stock' ? 'selected' : ''; ?>>In Stock</option>
                            <option value="out-of-stock" <?php echo $product['status'] == 'out-of-stock' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if(!empty($product['image'])) { ?>
                            <div class="mt-2">
                                <img src="../uploads/products/<?php echo $product['image']; ?>" 
                                     width="80" class="rounded">
                                <small class="text-muted d-block">Leave empty to keep current image</small>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea name="description" rows="4" class="form-control" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="text-end">
                    <button type="reset" class="btn btn-light">Reset</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script>
$(document).ready(function () {
    $("#productForm").validate({
        rules: {
            product_name: {
                required: true,
                minlength: 3
            },
            category: {
                required: true
            },
            price: {
                required: true,
                number: true,
                min: 0.01
            },
            weight: {
                required: true
            },
            stock: {
                required: true,
                digits: true,
                min: 0
            },
            description: {
                required: true,
                minlength: 10
            }
        },
        messages: {
            product_name: {
                required: "Product name is required",
                minlength: "Minimum 3 characters required"
            },
            price: {
                required: "Price is required",
                number: "Enter valid price",
                min: "Price must be greater than 0"
            }
        },
        errorElement: "small",
        errorClass: "text-danger"
    });
});
</script>

<?php include('includes/footer.php'); ?>