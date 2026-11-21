<?php 
include('includes/sidebar.php');
include('includes/header.php');
include('../includes/db_config.php');

// Insert Category
if(isset($_POST['submit'])){

    $category_name = mysqli_real_escape_string($con, $_POST['category_name']);
    $description   = mysqli_real_escape_string($con, $_POST['category_description']);
    $status        = $_POST['status'];

    // Image Upload
    $image_name = $_FILES['category_image']['name'];
    $tmp_name   = $_FILES['category_image']['tmp_name'];
    $upload_path = "../uploads/categories/";

    if(!is_dir($upload_path)){
        mkdir($upload_path, 0777, true);
    }

    $new_image_name = time() . "_" . $image_name;

    if(move_uploaded_file($tmp_name, $upload_path.$new_image_name)){

        $query = "INSERT INTO product_categories 
                  (image, category_name, description, status) 
                  VALUES 
                  ('$new_image_name','$category_name','$description','$status')";

        if(mysqli_query($con,$query)){
            echo "<script>
                    alert('Category Added Successfully');
                    window.location='categories.php';
                  </script>";
        }else{
            echo "Database Error: ".mysqli_error($con);
        }

    }else{
        echo "Image Upload Failed";
    }
}
?>

<div class="content-wrapper">
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Add New Category</h2>
            <a href="categories.php" class="btn btn-secondary">
                Back
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <form id="addCategoryForm" method="POST" enctype="multipart/form-data">

                    <div class="row">
                        <div class="col-md-8">

                            <div class="mb-3">
                                <label class="form-label">Category Name *</label>
                                <input type="text" name="category_name" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description *</label>
                                <textarea name="category_description" rows="3" class="form-control"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-select">
                                    <option value="">Select Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="mb-3">
                                <label class="form-label">Category Image *</label>
                                <input type="file" name="category_image" class="form-control">
                            </div>

                        </div>
                    </div>

                    <div class="text-end">
                        <button type="reset" class="btn btn-light">Reset</button>
                        <button type="submit" name="submit" class="btn btn-primary">
                            Save Category
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

<script>
$(document).ready(function(){

    $.validator.addMethod("filesize", function(value, element, param) {
        if(element.files.length == 0) return false;
        return element.files[0].size <= param;
    });

    $("#addCategoryForm").validate({
        rules:{
            category_name:{
                required:true,
                minlength:3,
                maxlength:100
            },
            category_description:{
                required:true,
                minlength:10
            },
            status:{
                required:true
            },
            category_image:{
                required:true,
                extension:"jpg|jpeg|png|webp",
                filesize:2097152
            }
        },
        messages:{
            category_name:"Please enter valid category name",
            category_description:"Please enter description (min 10 characters)",
            status:"Please select status",
            category_image:"Upload JPG, PNG, WEBP (Max 2MB)"
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