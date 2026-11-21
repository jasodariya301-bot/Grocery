<?php 
include('includes/sidebar.php');
include('includes/header.php');
include('../includes/db_config.php');

// =============================
// GET CATEGORY BY ID
// =============================
if(!isset($_GET['id'])){
    header("Location: categories.php");
    exit();
}

$id = intval($_GET['id']);

$get_query = mysqli_query($con,"SELECT * FROM product_categories WHERE id='$id'");
$data = mysqli_fetch_assoc($get_query);

if(!$data){
    header("Location: categories.php");
    exit();
}

// =============================
// UPDATE CATEGORY
// =============================
if(isset($_POST['update'])){

    $category_name = mysqli_real_escape_string($con,$_POST['category_name']);
    $description   = mysqli_real_escape_string($con,$_POST['category_description']);
    $status        = $_POST['status'];

    $upload_path = "../uploads/categories/";

    // Check if new image uploaded
    if(!empty($_FILES['category_image']['name'])){

        $image_name = $_FILES['category_image']['name'];
        $tmp_name   = $_FILES['category_image']['tmp_name'];
        $new_image_name = time()."_".$image_name;

        if(move_uploaded_file($tmp_name,$upload_path.$new_image_name)){

            // Delete old image
            if(!empty($data['image']) && file_exists($upload_path.$data['image'])){
                unlink($upload_path.$data['image']);
            }

            $update = "UPDATE product_categories SET
                       image='$new_image_name',
                       category_name='$category_name',
                       description='$description',
                       status='$status'
                       WHERE id='$id'";
        }

    } else {

        // No new image
        $update = "UPDATE product_categories SET
                   category_name='$category_name',
                   description='$description',
                   status='$status'
                   WHERE id='$id'";
    }

    if(mysqli_query($con,$update)){
        echo "<script>
                alert('Category Updated Successfully');
                window.location='categories.php';
              </script>";
    } else {
        echo "Update Failed: ".mysqli_error($con);
    }
}
?>

<div class="content-wrapper">
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Category</h2>
            <a href="categories.php" class="btn btn-secondary">
                Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <form method="POST" enctype="multipart/form-data">

                    <div class="row">
                        <div class="col-md-8">

                            <div class="mb-3">
                                <label class="form-label">Category Name *</label>
                                <input type="text" name="category_name"
                                       class="form-control"
                                       value="<?php echo $data['category_name']; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="category_description"
                                          class="form-control"
                                          rows="3"><?php echo $data['description']; ?></textarea>
                            </div>

                            <div class="row">
                        

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-select">
                                        <option value="active" selected <?php if($data['status']=='Active') echo 'selected'; ?>>Active</option>
                                        <option value="inactive" <?php if($data['status']=='Inactive') echo 'selected'; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="mb-3">
                                <label class="form-label">Category Image *</label>
                                <?php if(!empty($data['image'])){ ?>
                                <img src="../uploads/categories/<?php echo $data['image']; ?>"
                                     width="120" style="border-radius:6px;">
                            <?php } else { ?>
                                <p>No Image</p>
                            <?php } ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Featured</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" checked>
                                    <label class="form-check-label">Show on Homepage</label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="text-end">
                        <button type="reset" class="btn btn-light">Reset</button>
                        <button type="submit" name="update" class="btn btn-primary">
                        Update Category
                    </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>


<script>
$(document).ready(function(){

    $("#addCategoryForm, #editCategoryForm").validate({
        rules:{
            category_name:{
                required:true,
                minlength:3
            },
            status:{
                required:true
            },
            category_image:{
                required:true
            }
        },
        messages:{
            category_name:"Please enter category name",
            status:"Please select status",
            category_image:"Please upload category image"
        },
        errorClass:"text-danger",
        errorElement:"small",
        highlight:function(element){
            $(element).addClass("is-invalid");
        },
        unhighlight:function(element){
            $(element).removeClass("is-invalid");
        }
    });

});
</script>
