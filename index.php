

    <?php
$page_title = "Home - Fresh Groceries Online";
include 'includes/header.php'; 
include 'includes/db_config.php';

// Fetch only active categories
$cat_query = "SELECT * FROM product_categories WHERE status='Active' ORDER BY id DESC";
$cat_result = mysqli_query($con, $cat_query);
?>

<!-- Hero Banner -->
<section class="hero-banner">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold text-white mb-4">Fresh Groceries Delivered to Your Door</h1>
                <p class="lead text-white mb-4">Shop the freshest vegetables, fruits, dairy products, and more with fast delivery!</p>
                
                <!-- Search Bar with Form -->
                <div class="search-container">
                    <form action="products.php" method="GET" id="searchForm">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" name="search" id="searchInput" placeholder="Search for products...">
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/Banner.jpg" alt="Fresh Groceries" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

    <!-- Categories -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Shop by Category</h2>
            <div class="row g-4">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="products.php?category=vegetables" class="category-card text-decoration-none">
                        <div class="card text-center h-100 shadow-sm">
                            <div class="card-body">
                                <i class="fas fa-carrot fa-3x text-warning mb-3"></i>
                                <h5 class="card-title">Vegetables</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="products.php?category=fruits" class="category-card text-decoration-none">
                        <div class="card text-center h-100 shadow-sm">
                            <div class="card-body">
                                <i class="fas fa-apple-alt fa-3x text-danger mb-3"></i>
                                <h5 class="card-title">Fruits</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="products.php?category=dairy" class="category-card text-decoration-none">
                        <div class="card text-center h-100 shadow-sm">
                            <div class="card-body">
                                <i class="fas fa-cheese fa-3x text-info mb-3"></i>
                                <h5 class="card-title">Dairy</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="products.php?category=snacks" class="category-card text-decoration-none">
                        <div class="card text-center h-100 shadow-sm">
                            <div class="card-body">
                                <i class="fas fa-cookie-bite fa-3x text-warning mb-3"></i>
                                <h5 class="card-title">Snacks</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="products.php?category=beverages" class="category-card text-decoration-none">
                        <div class="card text-center h-100 shadow-sm">
                            <div class="card-body">
                                <i class="fas fa-wine-bottle fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Beverages</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="products.php" class="category-card text-decoration-none">
                        <div class="card text-center h-100 shadow-sm">
                            <div class="card-body">
                                <i class="fas fa-th fa-3x text-secondary mb-3"></i>
                                <h5 class="card-title">All</h5>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Shop by Category</h2>
        
        <?php if(mysqli_num_rows($cat_result) > 0) { ?>
        
        <!-- Desktop/Tablet Card Slider - 4 Cards Per Slide -->
        <div id="offerCardCarousel" class="carousel slide d-none d-md-block" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $categories = [];
                while($row = mysqli_fetch_assoc($cat_result)) {
                    $categories[] = $row;
                }

                // Split into chunks of 4 (for slider)
                $chunks = array_chunk($categories, 4);
                $first = true;

                foreach($chunks as $chunk) {
                ?>
                <div class="carousel-item <?php if($first){ echo 'active'; $first=false; } ?>">
                    <div class="row g-4">
                        <?php foreach($chunk as $cat) { ?>
                        <div class="col-md-3">
                            <div class="offer-card card border-0 shadow h-100">
                                <!-- Image -->
                                <?php if(!empty($cat['image']) && file_exists("uploads/categories/".$cat['image'])) { ?>
                                    <img src="uploads/categories/<?php echo $cat['image']; ?>" 
                                         class="card-img-top" style="height:180px;object-fit:cover;">
                                <?php } else { ?>
                                    <img src="assets/images/categories/default.jpg" 
                                         class="card-img-top" style="height:180px;object-fit:cover;">
                                <?php } ?>

                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        <small><?php echo substr($cat['description'],0,40); ?>...</small>
                                    </p>
                                    <a href="products.php?category=<?php echo urlencode($cat['category_name']); ?>" 
                                       class="btn btn-sm btn-success">
                                       Shop Now
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <!-- Carousel Controls -->
            <?php if(count($chunks) > 1) { ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#offerCardCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#offerCardCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <?php } ?>
        </div>
        
        <!-- Mobile Single Card Slider -->
        <div id="mobileOfferCarousel" class="carousel slide d-md-none" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $first = true;
                mysqli_data_seek($cat_result, 0);
                while($cat = mysqli_fetch_assoc($cat_result)) {
                ?>
                <div class="carousel-item <?php if($first){ echo 'active'; $first=false; } ?>">
                    <div class="offer-card card border-0 shadow">
                        <?php if(!empty($cat['image']) && file_exists("uploads/categories/".$cat['image'])) { ?>
                            <img src="uploads/categories/<?php echo $cat['image']; ?>" 
                                 class="card-img-top" style="height:200px;object-fit:cover;">
                        <?php } else { ?>
                            <img src="assets/images/categories/default.jpg" 
                                 class="card-img-top" style="height:200px;object-fit:cover;">
                        <?php } ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($cat['category_name']); ?></h5>
                            <p class="text-muted mb-2">
                                <small><?php echo substr($cat['description'],0,40); ?>...</small>
                            </p>
                            <a href="products.php?category=<?php echo urlencode($cat['category_name']); ?>" 
                               class="btn btn-sm btn-success">Shop Now</a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <!-- Mobile Indicators -->
            <?php if(mysqli_num_rows($cat_result) > 1) { ?>
            <div class="carousel-indicators position-relative mt-4">
                <?php
                mysqli_data_seek($cat_result, 0);
                $i = 0;
                while($cat = mysqli_fetch_assoc($cat_result)) {
                ?>
                <button type="button" data-bs-target="#mobileOfferCarousel" data-bs-slide-to="<?php echo $i; ?>" 
                        class="<?php echo $i == 0 ? 'active' : ''; ?> bg-success"></button>
                <?php $i++; } ?>
            </div>
            <?php } ?>
        </div>
        
        <!-- Desktop Indicators -->
        <?php if(count($chunks) > 1) { ?>
        <div class="carousel-indicators position-relative mt-4 d-none d-md-flex">
            <?php for($i = 0; $i < count($chunks); $i++) { ?>
            <button type="button" data-bs-target="#offerCardCarousel" data-bs-slide-to="<?php echo $i; ?>" 
                    class="<?php echo $i == 0 ? 'active' : ''; ?> bg-success"></button>
            <?php } ?>
        </div>
        <?php } ?>
        
        <?php } else { ?>
        <div class="text-center py-5">
            <h4>No categories available</h4>
        </div>
        <?php } ?>
    </div>
</section>

<!-- Delivery Highlights -->
<section class="py-5 bg-success text-white">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose Us?</h2>
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <i class="fas fa-shipping-fast fa-3x mb-3"></i>
                <h4>Fast Delivery</h4>
                <p>Get your groceries delivered within 2 hours. Same-day delivery available!</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="fas fa-leaf fa-3x mb-3"></i>
                <h4>Fresh Quality</h4>
                <p>We source directly from local farmers to ensure the freshest produce.</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="fas fa-shield-alt fa-3x mb-3"></i>
                <h4>Secure Payment</h4>
                <p>Safe and secure payment options. Cash on delivery available!</p>
            </div>
        </div>
    </div>
</section>

<style>
/* Your existing styles remain the same */
.offer-card {
    transition: all 0.3s ease;
    overflow: hidden;
    height: 100%;
}
.offer-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15)!important;
}
.offer-card img {
    height: 180px;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.offer-card:hover img {
    transform: scale(1.05);
}
.col-md-3 {
    flex: 0 0 25%;
    max-width: 25%;
}
.carousel-control-prev,
.carousel-control-next {
    width: 5%;
    opacity: 1;
}
.carousel-control-prev-icon,
.carousel-control-next-icon {
    background-size: 60%;
    opacity: 0.9;
}
.carousel-control-prev-icon.bg-dark,
.carousel-control-next-icon.bg-dark {
    background-color: rgba(33, 37, 41, 0.7) !important;
}
.carousel-indicators {
    bottom: -30px;
}
.carousel-indicators [data-bs-target] {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin: 0 5px;
    border: none;
}
.carousel-indicators .active {
    background-color: #198754 !important;
}
@media (max-width: 991px) {
    .offer-card img {
        height: 160px;
    }
}
@media (max-width: 767px) {
    .offer-card img {
        height: 200px;
    }
}
.card-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}
.card-body {
    padding: 1rem;
}
.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
}
</style>

<?php include 'includes/footer.php'; ?>