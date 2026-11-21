<?php 
include('includes/sidebar.php');
include('includes/header.php');
include('../includes/db_config.php');

$page = 'categories';

$query = "SELECT pc.*, 
          (SELECT COUNT(*) FROM products p 
           WHERE p.category = pc.category_name) AS product_count
          FROM product_categories pc
          ORDER BY pc.id DESC";
$result = mysqli_query($con, $query);$total_query = mysqli_query($con,"SELECT COUNT(*) as total FROM product_categories");
$total = mysqli_fetch_assoc($total_query)['total'];

$active_query = mysqli_query($con,"SELECT COUNT(*) as active FROM product_categories WHERE status='Active'");
$active = mysqli_fetch_assoc($active_query)['active'];

$inactive_query = mysqli_query($con,"SELECT COUNT(*) as inactive FROM product_categories WHERE status='Inactive'");
$inactive = mysqli_fetch_assoc($inactive_query)['inactive'];

$query = "SELECT pc.*, 
          (SELECT COUNT(*) FROM products p WHERE p.category = pc.category_name) as product_count
          FROM product_categories pc
          ORDER BY pc.id DESC";
?>

        <div class="content-wrapper">
            <div class="page-header">
                <div>
                    <h2>Category Management</h2>
                    <p class="text-muted mb-0">Manage product categories and organization</p>
                </div>
                <a href="add-category.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add New Category
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stat-card stat-primary">
                        <div class="stat-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $total; ?></h3>
                            <p>Total Categories</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card stat-success">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="active-categories"><?php echo $active; ?></h3>
                            <p>Active Categories</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card stat-warning">
                        <div class="stat-icon">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="inactive-categories"><?php echo $inactive; ?></h3>
                            <p>Inactive Categories</p>
                        </div>
                    </div>
                </div>
              </div>
            </div>

            <!-- Categories Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Categories</h5>
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                           
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="filterCategories('all')">All Categories</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterCategories('active')">Active Only</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterCategories('inactive')">Inactive Only</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="exportCategories()"><i class="fas fa-download"></i> Export to CSV</a></li>
                            </ul>
                        </div>
                       
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th width="60">Icon</th>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th>Products</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                             <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>

                            <td>
                                <?php if(!empty($row['image'])) { ?>
                                    <img src="../uploads/categories/<?php echo $row['image']; ?>" 
                                         width="60" height="60"
                                         style="object-fit:cover;border-radius:6px;">
                                <?php } else { ?>
                                    <span class="text-muted">No Image</span>
                                <?php } ?>
                            </td>

                            <td><strong><?php echo $row['category_name']; ?></strong></td>

                            <td><?php echo $row['description']; ?></td>

                            <td>
                                <span class="badge bg-info">
                                    <?php echo $row['product_count']; ?>
                                </span>
                            </td>

                            <td>
                                <?php if($row['status'] == 'Active'){ ?>
                                    <span class="badge bg-success">Active</span>
                                <?php } else { ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php } ?>
                            </td>

                            <td>
                                <?php echo date("d M Y", strtotime($row['created_date'])); ?>
                            </td>

                            <td>
                                <a href="edit-category.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="delete-category.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to delete this category?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>


                        </table>
                    </div>
                    
                  
                </div>
            </div>
        </div>
    </div>




    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="admin-script.js"></script>
    <script>
  $(document).ready(function() {
    $('#categoriesTable').DataTable();
});
<?php include('includes/footer.php');

?>
