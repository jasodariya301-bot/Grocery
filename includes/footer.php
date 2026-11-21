<?php
// Include database connection to fetch settings
include_once 'includes/db_config.php';

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

<!-- Footer -->
<footer class="footer-custom">
    <div class="footer-top">
        <div class="container py-5">
            <div class="row g-4">
                <!-- Company Info -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget">
                        <div class="footer-logo mb-3">
                            <i class="fas fa-shopping-basket me-2"></i>
                            <span class="footer-brand"><?php echo htmlspecialchars($settings['shop_name']); ?></span>
                        </div>
                        <p class="footer-text mb-3"><?php echo htmlspecialchars($settings['shop_tagline']); ?></p>
                        <div class="social-links">
                            <?php if ($settings['facebook_url'] != '#'): ?>
                                <a href="<?php echo htmlspecialchars($settings['facebook_url']); ?>" class="social-link" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <?php endif; ?>
                            <?php if ($settings['twitter_url'] != '#'): ?>
                                <a href="<?php echo htmlspecialchars($settings['twitter_url']); ?>" class="social-link" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if ($settings['instagram_url'] != '#'): ?>
                                <a href="<?php echo htmlspecialchars($settings['instagram_url']); ?>" class="social-link" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <?php endif; ?>
                            <?php if ($settings['linkedin_url'] != '#'): ?>
                                <a href="<?php echo htmlspecialchars($settings['linkedin_url']); ?>" class="social-link" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <?php endif; ?>
                            <?php if ($settings['youtube_url'] != '#'): ?>
                                <a href="<?php echo htmlspecialchars($settings['youtube_url']); ?>" class="social-link" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="footer-title mb-3">Quick Links</h5>
                        <ul class="footer-links">
                            <li><a href="index.php"><i class="fas fa-chevron-right me-2"></i>Home</a></li>
                            <li><a href="products.php"><i class="fas fa-chevron-right me-2"></i>Products</a></li>
                            <li><a href="cart.php"><i class="fas fa-chevron-right me-2"></i>Shopping Cart</a></li>
                            <li><a href="login.php"><i class="fas fa-chevron-right me-2"></i>Login</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Categories -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="footer-title mb-3">Categories</h5>
                        <ul class="footer-links">
                            <li><a href=""><i class="fas fa-chevron-right me-2"></i>Vegetables</a></li>
                            <li><a href=""><i class="fas fa-chevron-right me-2"></i>Fruits</a></li>
                            <li><a href=""><i class="fas fa-chevron-right me-2"></i>Dairy</a></li>
                            <li><a href=""><i class="fas fa-chevron-right me-2"></i>Snacks</a></li>
                            <li><a href=""><i class="fas fa-chevron-right me-2"></i>Beverages</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="footer-title mb-3">Contact Us</h5>
                        <ul class="footer-contact">
                            <li>
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <span><?php echo htmlspecialchars($settings['shop_address']); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-phone me-2"></i>
                                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $settings['shop_phone']); ?>"><?php echo htmlspecialchars($settings['shop_phone']); ?></a>
                            </li>
                            <li>
                                <i class="fas fa-envelope me-2"></i>
                                <a href="mailto:<?php echo htmlspecialchars($settings['shop_email']); ?>"><?php echo htmlspecialchars($settings['shop_email']); ?></a>
                            </li>
                            <li>
                                <i class="fas fa-clock me-2"></i>
                                <span><?php echo nl2br(htmlspecialchars($settings['opening_hours'])); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="footer-title mb-3">Newsletter</h5>
                        <p class="footer-text mb-3">Subscribe to get updates on new products and special offers!</p>
                        <form class="newsletter-form" id="newsletterForm">
                            <div class="input-group mb-3">
                                <input type="email" class="form-control" placeholder="Enter your email" required>
                                <button class="btn btn-success" type="submit">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                        <div class="payment-methods mt-3">
                            <p class="mb-2"><small>We Accept:</small></p>
                            <div class="payment-icons">
                                <i class="fab fa-cc-visa"></i>
                                <i class="fab fa-cc-mastercard"></i>
                                <i class="fab fa-cc-paypal"></i>
                                <i class="fab fa-cc-amex"></i>
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container py-3">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0"><?php echo $settings['copyright_text']; ?></p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="footer-bottom-links">
                        <a href="<?php echo htmlspecialchars($settings['privacy_policy_url']); ?>">Privacy Policy</a>
                        <span class="mx-2">|</span>
                        <a href="<?php echo htmlspecialchars($settings['terms_url']); ?>">Terms & Conditions</a>
                        <span class="mx-2">|</span>
                        <a href="<?php echo htmlspecialchars($settings['refund_policy_url']); ?>">Refund Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Custom JS -->
<script src="assets/js/main.js"></script>
</body>
</html>