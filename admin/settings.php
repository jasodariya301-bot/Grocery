<?php
session_start();

// Check if admin is logged in


include('includes/sidebar.php');
include('includes/header.php');
include('../includes/db_config.php');

// Handle form submissions
$success_message = '';
$error_message = '';

// Save shop details
if (isset($_POST['save_shop_details'])) {
    $settings = [
        'shop_name' => mysqli_real_escape_string($con, $_POST['shop_name']),
        'shop_tagline' => mysqli_real_escape_string($con, $_POST['shop_tagline']),
        'shop_address' => mysqli_real_escape_string($con, $_POST['shop_address']),
        'shop_phone' => mysqli_real_escape_string($con, $_POST['shop_phone']),
        'shop_email' => mysqli_real_escape_string($con, $_POST['shop_email'])
    ];
    
    foreach ($settings as $key => $value) {
        $update_query = "INSERT INTO settings (setting_key, setting_value) 
                         VALUES (?, ?) 
                         ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, "sss", $key, $value, $value);
        mysqli_stmt_execute($stmt);
    }
    
    $success_message = "Shop details saved successfully!";
}

// Save social media links
if (isset($_POST['save_social_links'])) {
    $social_links = [
        'facebook_url' => mysqli_real_escape_string($con, $_POST['facebook_url']),
        'twitter_url' => mysqli_real_escape_string($con, $_POST['twitter_url']),
        'instagram_url' => mysqli_real_escape_string($con, $_POST['instagram_url']),
        'linkedin_url' => mysqli_real_escape_string($con, $_POST['linkedin_url']),
        'youtube_url' => mysqli_real_escape_string($con, $_POST['youtube_url'])
    ];
    
    foreach ($social_links as $key => $value) {
        $update_query = "INSERT INTO settings (setting_key, setting_value) 
                         VALUES (?, ?) 
                         ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, "sss", $key, $value, $value);
        mysqli_stmt_execute($stmt);
    }
    
    $success_message = "Social media links saved successfully!";
}

// Save opening hours
if (isset($_POST['save_opening_hours'])) {
    $opening_hours = mysqli_real_escape_string($con, $_POST['opening_hours']);
    
    $update_query = "INSERT INTO settings (setting_key, setting_value) 
                     VALUES ('opening_hours', ?) 
                     ON DUPLICATE KEY UPDATE setting_value = ?";
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, "ss", $opening_hours, $opening_hours);
    mysqli_stmt_execute($stmt);
    
    $success_message = "Opening hours saved successfully!";
}

// Save footer settings
if (isset($_POST['save_footer_settings'])) {
    $footer_settings = [
        'copyright_text' => mysqli_real_escape_string($con, $_POST['copyright_text']),
        'privacy_policy_url' => mysqli_real_escape_string($con, $_POST['privacy_policy_url']),
        'terms_url' => mysqli_real_escape_string($con, $_POST['terms_url']),
        'refund_policy_url' => mysqli_real_escape_string($con, $_POST['refund_policy_url'])
    ];
    
    foreach ($footer_settings as $key => $value) {
        $update_query = "INSERT INTO settings (setting_key, setting_value) 
                         VALUES (?, ?) 
                         ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, "sss", $key, $value, $value);
        mysqli_stmt_execute($stmt);
    }
    
    $success_message = "Footer settings saved successfully!";
}

// Fetch all settings
$settings_query = "SELECT setting_key, setting_value FROM settings";
$settings_result = mysqli_query($con, $settings_query);
$settings = [];
while ($row = mysqli_fetch_assoc($settings_result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Set default values if not set
$default_settings = [
    'shop_name' => 'BharatMart',
    'shop_tagline' => 'Your Fresh Grocery Store',
    'shop_address' => '123 Grocery Street, City, State 123456',
    'shop_phone' => '+91 88490-05979',
    'shop_email' => 'info@BharatMart.com',
    'facebook_url' => '#',
    'twitter_url' => '#',
    'instagram_url' => '#',
    'linkedin_url' => '#',
    'youtube_url' => '#',
    'opening_hours' => 'Mon-Sat: 8AM-10PM',
    'copyright_text' => '&copy; 2026 BharatMart. All rights reserved.',
    'privacy_policy_url' => '#',
    'terms_url' => '#',
    'refund_policy_url' => '#'
];

foreach ($default_settings as $key => $default_value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $default_value;
    }
}
?>

<!-- Page Content -->
<div class="content-wrapper">
    <div class="page-header">
        <h2><i class="fas fa-cog me-2"></i>Settings</h2>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
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

    <div class="tab-content" id="settingsTabContent">
        <!-- Shop Details Tab -->
        <div class="tab-pane fade show active" id="shop" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-store me-2"></i>Shop Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shop Name *</label>
                                <input type="text" class="form-control" name="shop_name" 
                                       value="<?php echo htmlspecialchars($settings['shop_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tagline</label>
                                <input type="text" class="form-control" name="shop_tagline" 
                                       value="<?php echo htmlspecialchars($settings['shop_tagline']); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address *</label>
                            <textarea class="form-control" name="shop_address" rows="2" required><?php echo htmlspecialchars($settings['shop_address']); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" name="shop_phone" 
                                       value="<?php echo htmlspecialchars($settings['shop_phone']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="shop_email" 
                                       value="<?php echo htmlspecialchars($settings['shop_email']); ?>" required>
                            </div>
                        </div>
                        <button type="submit" name="save_shop_details" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Shop Details
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Social Media Tab -->
        <div class="tab-pane fade" id="social" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-share-alt me-2"></i>Social Media Links</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-facebook-f me-2 text-primary"></i>Facebook URL</label>
                            <input type="url" class="form-control" name="facebook_url" 
                                   value="<?php echo htmlspecialchars($settings['facebook_url']); ?>" 
                                   placeholder="https://facebook.com/yourpage">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-twitter me-2 text-info"></i>Twitter URL</label>
                            <input type="url" class="form-control" name="twitter_url" 
                                   value="<?php echo htmlspecialchars($settings['twitter_url']); ?>"
                                   placeholder="https://twitter.com/yourhandle">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-instagram me-2 text-danger"></i>Instagram URL</label>
                            <input type="url" class="form-control" name="instagram_url" 
                                   value="<?php echo htmlspecialchars($settings['instagram_url']); ?>"
                                   placeholder="https://instagram.com/yourprofile">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-linkedin-in me-2 text-primary"></i>LinkedIn URL</label>
                            <input type="url" class="form-control" name="linkedin_url" 
                                   value="<?php echo htmlspecialchars($settings['linkedin_url']); ?>"
                                   placeholder="https://linkedin.com/company/yourcompany">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-youtube me-2 text-danger"></i>YouTube URL</label>
                            <input type="url" class="form-control" name="youtube_url" 
                                   value="<?php echo htmlspecialchars($settings['youtube_url']); ?>"
                                   placeholder="https://youtube.com/c/yourchannel">
                        </div>
                        <button type="submit" name="save_social_links" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Social Links
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Opening Hours Tab -->
        <div class="tab-pane fade" id="hours" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-clock me-2"></i>Opening Hours</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Business Hours</label>
                            <textarea class="form-control" name="opening_hours" rows="4" 
                                      placeholder="Mon-Fri: 9AM-9PM&#10;Sat: 10AM-8PM&#10;Sun: Closed"><?php echo htmlspecialchars($settings['opening_hours']); ?></textarea>
                            <small class="text-muted">Enter your business hours (one per line)</small>
                        </div>
                        <button type="submit" name="save_opening_hours" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Opening Hours
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer Settings Tab -->
        <div class="tab-pane fade" id="footer" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-file-alt me-2"></i>Footer Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Copyright Text</label>
                            <input type="text" class="form-control" name="copyright_text" 
                                   value="<?php echo htmlspecialchars($settings['copyright_text']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Privacy Policy URL</label>
                            <input type="url" class="form-control" name="privacy_policy_url" 
                                   value="<?php echo htmlspecialchars($settings['privacy_policy_url']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Terms & Conditions URL</label>
                            <input type="url" class="form-control" name="terms_url" 
                                   value="<?php echo htmlspecialchars($settings['terms_url']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Refund Policy URL</label>
                            <input type="url" class="form-control" name="refund_policy_url" 
                                   value="<?php echo htmlspecialchars($settings['refund_policy_url']); ?>">
                        </div>
                        <button type="submit" name="save_footer_settings" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Footer Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>

<?php include('includes/footer.php'); ?>