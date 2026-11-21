<?php
session_start();


include('includes/sidebar.php');
include('includes/header.php');
include('../includes/db_config.php');

$success_message = '';
$error_message = '';

// Handle about page settings update
if (isset($_POST['save_about_settings'])) {
    $about_settings = [
        'about_page_title' => mysqli_real_escape_string($con, $_POST['about_page_title']),
        'about_page_subtitle' => mysqli_real_escape_string($con, $_POST['about_page_subtitle']),
        'about_story_title' => mysqli_real_escape_string($con, $_POST['about_story_title']),
        'about_story_content' => mysqli_real_escape_string($con, $_POST['about_story_content']),
        'about_story_image' => mysqli_real_escape_string($con, $_POST['about_story_image']),
        'about_stat_1_number' => mysqli_real_escape_string($con, $_POST['about_stat_1_number']),
        'about_stat_1_label' => mysqli_real_escape_string($con, $_POST['about_stat_1_label']),
        'about_stat_2_number' => mysqli_real_escape_string($con, $_POST['about_stat_2_number']),
        'about_stat_2_label' => mysqli_real_escape_string($con, $_POST['about_stat_2_label']),
        'about_stat_3_number' => mysqli_real_escape_string($con, $_POST['about_stat_3_number']),
        'about_stat_3_label' => mysqli_real_escape_string($con, $_POST['about_stat_3_label']),
        'about_mission_title' => mysqli_real_escape_string($con, $_POST['about_mission_title']),
        'about_mission_content' => mysqli_real_escape_string($con, $_POST['about_mission_content']),
        'about_vision_title' => mysqli_real_escape_string($con, $_POST['about_vision_title']),
        'about_vision_content' => mysqli_real_escape_string($con, $_POST['about_vision_content']),
        'about_cta_title' => mysqli_real_escape_string($con, $_POST['about_cta_title']),
        'about_cta_content' => mysqli_real_escape_string($con, $_POST['about_cta_content'])
    ];
    
    foreach ($about_settings as $key => $value) {
        $update_query = "INSERT INTO settings (setting_key, setting_value) 
                         VALUES (?, ?) 
                         ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, "sss", $key, $value, $value);
        mysqli_stmt_execute($stmt);
    }
    
    $success_message = "About page settings saved successfully!";
}

// Handle values settings update
if (isset($_POST['save_values'])) {
    $value_titles = $_POST['value_title'];
    $value_icons = $_POST['value_icon'];
    $value_descriptions = $_POST['value_description'];
    
    // Clear existing values
    $clear_query = "DELETE FROM about_values";
    mysqli_query($con, $clear_query);
    
    // Insert new values
    for ($i = 0; $i < count($value_titles); $i++) {
        if (!empty($value_titles[$i])) {
            $title = mysqli_real_escape_string($con, $value_titles[$i]);
            $icon = mysqli_real_escape_string($con, $value_icons[$i]);
            $description = mysqli_real_escape_string($con, $value_descriptions[$i]);
            
            $insert_query = "INSERT INTO about_values (title, icon, description, display_order) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($stmt, "sssi", $title, $icon, $description, $i);
            mysqli_stmt_execute($stmt);
        }
    }
    
    $success_message = "Values saved successfully!";
}

// Handle why choose us items update
if (isset($_POST['save_features'])) {
    $feature_titles = $_POST['feature_title'];
    $feature_icons = $_POST['feature_icon'];
    $feature_descriptions = $_POST['feature_description'];
    
    // Clear existing features
    $clear_query = "DELETE FROM about_features";
    mysqli_query($con, $clear_query);
    
    // Insert new features
    for ($i = 0; $i < count($feature_titles); $i++) {
        if (!empty($feature_titles[$i])) {
            $title = mysqli_real_escape_string($con, $feature_titles[$i]);
            $icon = mysqli_real_escape_string($con, $feature_icons[$i]);
            $description = mysqli_real_escape_string($con, $feature_descriptions[$i]);
            
            $insert_query = "INSERT INTO about_features (title, icon, description, display_order) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($stmt, "sssi", $title, $icon, $description, $i);
            mysqli_stmt_execute($stmt);
        }
    }
    
    $success_message = "Features saved successfully!";
}

// Fetch all settings
$settings_query = "SELECT setting_key, setting_value FROM settings";
$settings_result = mysqli_query($con, $settings_query);
$settings = [];
while ($row = mysqli_fetch_assoc($settings_result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Fetch about values
$values_query = "SELECT * FROM about_values ORDER BY display_order";
$values_result = mysqli_query($con, $values_query);
$about_values = [];
while ($row = mysqli_fetch_assoc($values_result)) {
    $about_values[] = $row;
}

// Fetch about features
$features_query = "SELECT * FROM about_features ORDER BY display_order";
$features_result = mysqli_query($con, $features_query);
$about_features = [];
while ($row = mysqli_fetch_assoc($features_result)) {
    $about_features[] = $row;
}

// Default values if empty
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

<!-- Page Content -->
<div class="content-wrapper">
    <div class="page-header">
        <h2><i class="fas fa-info-circle me-2"></i>About Page Settings</h2>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

 <!-- Settings Tabs -->
<!-- Settings Tabs -->
<ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-store me-2"></i> Shop Details
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="about-settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about-settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-info-circle me-2"></i> About Page
        </a>
    </li>
</ul>

<!-- About Page Sub Tabs -->
<ul class="nav nav-tabs mb-4" id="aboutSettingsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
            <i class="fas fa-cog me-2"></i> General Settings
        </button>
    </li>
   
</ul>

    <div class="tab-content" id="aboutSettingsTabContent">
        <!-- General Settings Tab -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-cog me-2"></i>General Page Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Page Title</label>
                            <input type="text" class="form-control" name="about_page_title" 
                                   value="<?php echo htmlspecialchars($settings['about_page_title'] ?? 'About BharatMart'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Page Subtitle</label>
                            <input type="text" class="form-control" name="about_page_subtitle" 
                                   value="<?php echo htmlspecialchars($settings['about_page_subtitle'] ?? 'Your trusted partner for fresh, quality groceries delivered right to your doorstep.'); ?>">
                        </div>
                        <hr>
                        <h6 class="mb-3">Story Section</h6>
                        <div class="mb-3">
                            <label class="form-label">Story Title</label>
                            <input type="text" class="form-control" name="about_story_title" 
                                   value="<?php echo htmlspecialchars($settings['about_story_title'] ?? 'Our Story'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Story Content</label>
                            <textarea class="form-control" name="about_story_content" rows="6"><?php echo htmlspecialchars($settings['about_story_content'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Story Image URL</label>
                            <input type="url" class="form-control" name="about_story_image" 
                                   value="<?php echo htmlspecialchars($settings['about_story_image'] ?? 'https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=800'); ?>">
                        </div>
                        <hr>
                        <h6 class="mb-3">Statistics Section</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stat 1 Number</label>
                                <input type="text" class="form-control" name="about_stat_1_number" 
                                       value="<?php echo htmlspecialchars($settings['about_stat_1_number'] ?? '50K+'); ?>">
                                <label class="form-label mt-2">Stat 1 Label</label>
                                <input type="text" class="form-control" name="about_stat_1_label" 
                                       value="<?php echo htmlspecialchars($settings['about_stat_1_label'] ?? 'Happy Customers'); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stat 2 Number</label>
                                <input type="text" class="form-control" name="about_stat_2_number" 
                                       value="<?php echo htmlspecialchars($settings['about_stat_2_number'] ?? '500+'); ?>">
                                <label class="form-label mt-2">Stat 2 Label</label>
                                <input type="text" class="form-control" name="about_stat_2_label" 
                                       value="<?php echo htmlspecialchars($settings['about_stat_2_label'] ?? 'Products'); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stat 3 Number</label>
                                <input type="text" class="form-control" name="about_stat_3_number" 
                                       value="<?php echo htmlspecialchars($settings['about_stat_3_number'] ?? '24/7'); ?>">
                                <label class="form-label mt-2">Stat 3 Label</label>
                                <input type="text" class="form-control" name="about_stat_3_label" 
                                       value="<?php echo htmlspecialchars($settings['about_stat_3_label'] ?? 'Support'); ?>">
                            </div>
                        </div>
                        <hr>
                        <h6 class="mb-3">Mission & Vision</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mission Title</label>
                                <input type="text" class="form-control" name="about_mission_title" 
                                       value="<?php echo htmlspecialchars($settings['about_mission_title'] ?? 'Our Mission'); ?>">
                                <label class="form-label mt-2">Mission Content</label>
                                <textarea class="form-control" name="about_mission_content" rows="4"><?php echo htmlspecialchars($settings['about_mission_content'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vision Title</label>
                                <input type="text" class="form-control" name="about_vision_title" 
                                       value="<?php echo htmlspecialchars($settings['about_vision_title'] ?? 'Our Vision'); ?>">
                                <label class="form-label mt-2">Vision Content</label>
                                <textarea class="form-control" name="about_vision_content" rows="4"><?php echo htmlspecialchars($settings['about_vision_content'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <hr>
                        <h6 class="mb-3">Call to Action Section</h6>
                        <div class="mb-3">
                            <label class="form-label">CTA Title</label>
                            <input type="text" class="form-control" name="about_cta_title" 
                                   value="<?php echo htmlspecialchars($settings['about_cta_title'] ?? 'Ready to Start Shopping?'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">CTA Content</label>
                            <textarea class="form-control" name="about_cta_content" rows="2"><?php echo htmlspecialchars($settings['about_cta_content'] ?? 'Join thousands of satisfied customers and experience the BharatMart difference!'); ?></textarea>
                        </div>
                        <button type="submit" name="save_about_settings" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save General Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>



     
    </div>
</div>



<script>
// Add new value item
document.getElementById('addValue').addEventListener('click', function() {
    const container = document.getElementById('valuesContainer');
    const newItem = document.createElement('div');
    newItem.className = 'value-item card mb-3';
    newItem.innerHTML = `
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="value_title[]" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Icon (Font Awesome)</label>
                    <input type="text" class="form-control" name="value_icon[]" placeholder="fa-leaf" required>
                    <small class="text-muted">Use Font Awesome icons (e.g., fa-leaf, fa-heart)</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="value_description[]" rows="2" required></textarea>
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-value">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    
    // Add remove functionality to new button
    newItem.querySelector('.remove-value').addEventListener('click', function() {
        newItem.remove();
    });
});

// Add new feature item
document.getElementById('addFeature').addEventListener('click', function() {
    const container = document.getElementById('featuresContainer');
    const newItem = document.createElement('div');
    newItem.className = 'feature-item card mb-3';
    newItem.innerHTML = `
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="feature_title[]" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Icon (Font Awesome)</label>
                    <input type="text" class="form-control" name="feature_icon[]" placeholder="fa-check-circle" required>
                    <small class="text-muted">Use Font Awesome icons (e.g., fa-check-circle)</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="feature_description[]" rows="2" required></textarea>
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-feature">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    
    // Add remove functionality to new button
    newItem.querySelector('.remove-feature').addEventListener('click', function() {
        newItem.remove();
    });
});

// Add remove functionality to existing remove buttons
document.querySelectorAll('.remove-value').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.value-item').remove();
    });
});

document.querySelectorAll('.remove-feature').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.feature-item').remove();
    });
});
</script>

<?php include('includes/footer.php'); ?>