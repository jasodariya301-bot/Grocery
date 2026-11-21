<?php
$page_title = "About - Fresh Groceries Online";
include 'includes/header.php';
include 'includes/db_config.php';

// Fetch all about page settings
$settings_query = "SELECT setting_key, setting_value FROM settings";
$settings_result = mysqli_query($con, $settings_query);
$settings = [];
while ($row = mysqli_fetch_assoc($settings_result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Fetch about values


    

// Set default values if database is empty
$page_title = $settings['about_page_title'] ?? 'About BharatMart';
$page_subtitle = $settings['about_page_subtitle'] ?? 'Your trusted partner for fresh, quality groceries delivered right to your doorstep.';
$story_title = $settings['about_story_title'] ?? 'Our Story';
$story_content = $settings['about_story_content'] ?? 'BharatMart was founded with a simple mission: to make fresh, quality groceries accessible to everyone. Started in 2020, we\'ve grown from a small local service to a trusted name in grocery delivery. We work directly with local farmers and suppliers to ensure you get the freshest produce at the best prices. Our commitment to quality, customer service, and community has made us the preferred choice for thousands of families who value freshness and convenience.';
$story_image = $settings['about_story_image'] ?? 'https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=800';
$stat_1_number = $settings['about_stat_1_number'] ?? '50K+';
$stat_1_label = $settings['about_stat_1_label'] ?? 'Happy Customers';
$stat_2_number = $settings['about_stat_2_number'] ?? '500+';
$stat_2_label = $settings['about_stat_2_label'] ?? 'Products';
$stat_3_number = $settings['about_stat_3_number'] ?? '24/7';
$stat_3_label = $settings['about_stat_3_label'] ?? 'Support';
$mission_title = $settings['about_mission_title'] ?? 'Our Mission';
$mission_content = $settings['about_mission_content'] ?? 'To provide fresh, high-quality groceries to every household while supporting local farmers and suppliers. We aim to make grocery shopping convenient, affordable, and sustainable.';
$vision_title = $settings['about_vision_title'] ?? 'Our Vision';
$vision_content = $settings['about_vision_content'] ?? 'To become the leading grocery delivery service in the region by maintaining the highest standards of quality, customer service, and community engagement. We envision a future where everyone has access to fresh, healthy food.';
$cta_title = $settings['about_cta_title'] ?? 'Ready to Start Shopping?';
$cta_content = $settings['about_cta_content'] ?? 'Join thousands of satisfied customers and experience the BharatMart difference!';

// If no values in database, use defaults
if (empty($about_values)) {
    $about_values = [
        ['title' => 'Freshness', 'icon' => 'fa-leaf', 'description' => 'We source the freshest products directly from farms and ensure they reach you at peak quality.'],
        ['title' => 'Quality', 'icon' => 'fa-heart', 'description' => 'Every product is carefully selected and inspected to meet our high quality standards.'],
        ['title' => 'Trust', 'icon' => 'fa-handshake', 'description' => 'We build lasting relationships with our customers through transparency and reliability.'],
        ['title' => 'Fast Delivery', 'icon' => 'fa-truck', 'description' => 'Same-day delivery available. Get your groceries delivered within 2 hours!'],
        ['title' => 'Customer First', 'icon' => 'fa-users', 'description' => 'Your satisfaction is our priority. We\'re here to serve you better every day.'],
        ['title' => 'Sustainability', 'icon' => 'fa-seedling', 'description' => 'We\'re committed to eco-friendly practices and supporting sustainable agriculture.']
    ];
}

if (empty($about_features)) {
    $about_features = [
        ['title' => 'Farm Fresh Products', 'icon' => 'fa-check-circle', 'description' => 'Direct from local farms to your door - guaranteed freshness!'],
        ['title' => 'Best Prices', 'icon' => 'fa-check-circle', 'description' => 'Competitive pricing with regular discounts and special offers.'],
        ['title' => 'Easy Ordering', 'icon' => 'fa-check-circle', 'description' => 'Simple, user-friendly website with quick checkout process.'],
        ['title' => 'Secure Payment', 'icon' => 'fa-check-circle', 'description' => 'Multiple payment options including COD for your convenience.'],
        ['title' => '24/7 Support', 'icon' => 'fa-check-circle', 'description' => 'Round-the-clock customer support to assist you anytime.'],
        ['title' => 'Free Delivery', 'icon' => 'fa-check-circle', 'description' => 'Free delivery on orders above $50. Fast and reliable service.']
    ];
}
?>

<!-- Page Header -->
<section class="page-header bg-success text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($page_title); ?></h1>
                <p class="lead"><?php echo htmlspecialchars($page_subtitle); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-5">
    <div class="container">
        <!-- Our Story -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="<?php echo htmlspecialchars($story_image); ?>" alt="Fresh Groceries" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6">
                <h2 class="mb-4 text-success"><?php echo htmlspecialchars($story_title); ?></h2>
                <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($story_content)); ?></p>
                <div class="row mt-4">
                    <div class="col-4 text-center">
                        <div class="stat-box">
                            <h3 class="text-success mb-1"><?php echo htmlspecialchars($stat_1_number); ?></h3>
                            <p class="text-muted mb-0 small"><?php echo htmlspecialchars($stat_1_label); ?></p>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="stat-box">
                            <h3 class="text-success mb-1"><?php echo htmlspecialchars($stat_2_number); ?></h3>
                            <p class="text-muted mb-0 small"><?php echo htmlspecialchars($stat_2_label); ?></p>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="stat-box">
                            <h3 class="text-success mb-1"><?php echo htmlspecialchars($stat_3_number); ?></h3>
                            <p class="text-muted mb-0 small"><?php echo htmlspecialchars($stat_3_label); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mission & Vision -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body p-4">
                        <div class="icon-box bg-success text-white mb-3">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h4 class="mb-3"><?php echo htmlspecialchars($mission_title); ?></h4>
                        <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($mission_content)); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body p-4">
                        <div class="icon-box bg-success text-white mb-3">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h4 class="mb-3"><?php echo htmlspecialchars($vision_title); ?></h4>
                        <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($vision_content)); ?></p>
                    </div>
                </div>
            </div>
        </div>

 

 

        <!-- Call to Action -->
        <div class="text-center bg-success text-white rounded p-5">
            <h2 class="mb-3"><?php echo htmlspecialchars($cta_title); ?></h2>
            <p class="lead mb-4"><?php echo htmlspecialchars($cta_content); ?></p>
            <a href="products.php" class="btn btn-light btn-lg me-3">
                <i class="fas fa-shopping-bag me-2"></i>Shop Now
            </a>
            <a href="contact.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-envelope me-2"></i>Contact Us
            </a>
        </div>
    </div>
</section>


<?php include 'includes/footer.php'; ?>