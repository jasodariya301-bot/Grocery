<?php
$page_title = "Contact - Fresh Groceries Online";
include 'includes/header.php';
include 'includes/db_config.php';

// Handle form submission
$success_message = '';
$error_message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_contact'])) {
    // Sanitize and validate inputs
    $name = mysqli_real_escape_string($con, trim($_POST['name']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $phone = mysqli_real_escape_string($con, trim($_POST['phone']));
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $message = mysqli_real_escape_string($con, trim($_POST['message']));
    
    // Validation
    if (empty($name)) {
        $errors['name'] = "Name is required";
    }
    
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }
    
    if (empty($subject)) {
        $errors['subject'] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors['message'] = "Message is required";
    }
    
    // Phone validation (optional but if provided, validate format)
    if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors['phone'] = "Phone number must be 10 digits";
    }
    
    if (empty($errors)) {
        // Insert into database
        $insert_query = "INSERT INTO contact_messages (name, email, phone, subject, message, status) 
                         VALUES ('$name', '$email', '$phone', '$subject', '$message', 'pending')";
        
        if (mysqli_query($con, $insert_query)) {
            $contact_id = mysqli_insert_id($con);
            
            // Send email to admin using SMTP
            if (sendAdminNotificationEmail($name, $email, $phone, $subject, $message, $contact_id)) {
                $success_message = "Thank you for contacting us! We'll get back to you soon.";
            } else {
                $success_message = "Your message has been sent! We'll get back to you soon.";
            }
            
            // Clear form data on success
            $_POST = array();
            // Reset errors
            $errors = array();
        } else {
            $error_message = "Failed to send message. Please try again.";
        }
    }
}

// Function to send email notification to admin
function sendAdminNotificationEmail($name, $email, $phone, $subject, $message, $contact_id) {
    require_once 'includes/mail_config.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'priyankmalviya1234@gmail.com';
        $mail->Password = 'fzuk jvot tskc vzqi'; // Your Gmail app password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('priyankmalviya1234@gmail.com', 'Fresh Groceries');
        $mail->addAddress('priyankmalviya1234@gmail.com'); // Admin email
        $mail->addReplyTo($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Contact Message: $subject";
        
        // Get base URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . $host;
        $project_folder = '/Grocery-website';
        $admin_link = $base_url . $project_folder . "/admin/contact-messages.php?id=" . $contact_id;
        
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .info-box { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #28a745; }
                .button { display: inline-block; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>New Contact Form Submission</h2>
                </div>
                <div class='content'>
                    <h3>Contact Details:</h3>
                    <div class='info-box'>
                        <p><strong>Name:</strong> $name</p>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Phone:</strong> " . ($phone ?: 'Not provided') . "</p>
                        <p><strong>Subject:</strong> $subject</p>
                        <p><strong>Message:</strong></p>
                        <p>" . nl2br($message) . "</p>
                    </div>
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='$admin_link' class='button'>View & Reply in Admin Panel</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "New Contact Message\n\nName: $name\nEmail: $email\nPhone: $phone\nSubject: $subject\n\nMessage:\n$message";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
            display: block;
        }
        .alert-custom {
            border-radius: 10px;
            padding: 15px 20px;
        }
        .is-invalid {
            border-color: #dc3545;
        }
        .form-control:focus, .form-select:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-success {
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
    </style>
</head>
<body>

   
    <!-- Page Header -->
    <section class="page-header bg-success text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3">Contact Us</h1>
                    <p class="lead">We'd love to hear from you! Get in touch with us and we'll respond as soon as possible.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <!-- Contact Information -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body p-4">
                            <h3 class="mb-4 text-success">Get in Touch</h3>
                            <p class="text-muted mb-4">Have questions? We're here to help! Reach out to us through any of the channels below.</p>
                            
                            <!-- Contact Items -->
                            <div class="contact-item mb-4">
                                <div class="contact-icon bg-success text-white">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-details">
                                    <h5>Address</h5>
                                    <p class="text-muted mb-0">123 Grocery Street<br>City, State 123456<br>United States</p>
                                </div>
                            </div>

                            <div class="contact-item mb-4">
                                <div class="contact-icon bg-success text-white">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-details">
                                    <h5>Phone</h5>
                                    <p class="text-muted mb-0">
                                        <a href="tel:+1234567890" class="text-decoration-none text-muted">+1 (234) 567-890</a><br>
                                        <a href="tel:+1234567891" class="text-decoration-none text-muted">+1 (234) 567-891</a>
                                    </p>
                                </div>
                            </div>

                            <div class="contact-item mb-4">
                                <div class="contact-icon bg-success text-white">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-details">
                                    <h5>Email</h5>
                                    <p class="text-muted mb-0">
                                        <a href="mailto:info@freshgrocer.com" class="text-decoration-none text-muted">info@freshgrocer.com</a><br>
                                        <a href="mailto:support@freshgrocer.com" class="text-decoration-none text-muted">support@freshgrocer.com</a>
                                    </p>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="contact-icon bg-success text-white">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="contact-details">
                                    <h5>Business Hours</h5>
                                    <p class="text-muted mb-0">
                                        Monday - Friday: 8:00 AM - 10:00 PM<br>
                                        Saturday: 9:00 AM - 10:00 PM<br>
                                        Sunday: 10:00 AM - 8:00 PM
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h3 class="mb-4 text-success">Send us a Message</h3>
                            <form id="contactForm" method="POST" action="" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contactName" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                               id="contactName" 
                                               name="name" 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                               required>
                                        <?php if (isset($errors['name'])): ?>
                                            <div class="error-message"><?php echo $errors['name']; ?></div>
                                        <?php else: ?>
                                            <div class="error-message" id="error-contact-name"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contactEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" 
                                               class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                               id="contactEmail" 
                                               name="email" 
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                               required>
                                        <?php if (isset($errors['email'])): ?>
                                            <div class="error-message"><?php echo $errors['email']; ?></div>
                                        <?php else: ?>
                                            <div class="error-message" id="error-contact-email"></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contactPhone" class="form-label">Phone Number</label>
                                        <input type="tel" 
                                               class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                               id="contactPhone" 
                                               name="phone" 
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                               pattern="[0-9]{10}"
                                               maxlength="10">
                                        <?php if (isset($errors['phone'])): ?>
                                            <div class="error-message"><?php echo $errors['phone']; ?></div>
                                        <?php else: ?>
                                            <div class="error-message" id="error-contact-phone"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contactSubject" class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select class="form-select <?php echo isset($errors['subject']) ? 'is-invalid' : ''; ?>" 
                                                id="contactSubject" 
                                                name="subject" 
                                                required>
                                            <option value="">Select a subject</option>
                                            <option value="general" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'general') ? 'selected' : ''; ?>>General Inquiry</option>
                                            <option value="order" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'order') ? 'selected' : ''; ?>>Order Related</option>
                                            <option value="complaint" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'complaint') ? 'selected' : ''; ?>>Complaint</option>
                                            <option value="feedback" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'feedback') ? 'selected' : ''; ?>>Feedback</option>
                                            <option value="partnership" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'partnership') ? 'selected' : ''; ?>>Partnership</option>
                                            <option value="other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                        <?php if (isset($errors['subject'])): ?>
                                            <div class="error-message"><?php echo $errors['subject']; ?></div>
                                        <?php else: ?>
                                            <div class="error-message" id="error-contact-subject"></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="contactMessage" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>" 
                                              id="contactMessage" 
                                              name="message" 
                                              rows="6" 
                                              placeholder="Enter your message here..." 
                                              required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                    <?php if (isset($errors['message'])): ?>
                                        <div class="error-message"><?php echo $errors['message']; ?></div>
                                    <?php else: ?>
                                        <div class="error-message" id="error-contact-message"></div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" name="submit_contact" class="btn btn-success btn-lg px-5">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="map-container">
                                <iframe 
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3695.660014329865!2d70.98290863004077!3d22.1389349!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39584ff1006cc259%3A0x6b88af9b2a71110a!2sKB%20Stores%20%26%20Gadgets!5e0!3m2!1str!2sin!4v1771122248475!5m2!1str!2sin"
                                    width="100%" 
                                    height="450" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy" 
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Client-side validation with real-time feedback
    (function() {
        'use strict';
        
        const form = document.getElementById('contactForm');
        
        // Real-time validation for each field
        const nameInput = document.getElementById('contactName');
        const emailInput = document.getElementById('contactEmail');
        const phoneInput = document.getElementById('contactPhone');
        const subjectSelect = document.getElementById('contactSubject');
        const messageTextarea = document.getElementById('contactMessage');
        
        // Name validation
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                const errorDiv = document.getElementById('error-contact-name');
                if (this.value.trim() === '') {
                    errorDiv.innerHTML = 'Please enter your full name';
                    this.classList.add('is-invalid');
                } else {
                    errorDiv.innerHTML = '';
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        }
        
        // Email validation
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                const errorDiv = document.getElementById('error-contact-email');
                const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
                if (this.value.trim() === '') {
                    errorDiv.innerHTML = 'Please enter your email address';
                    this.classList.add('is-invalid');
                } else if (!emailRegex.test(this.value)) {
                    errorDiv.innerHTML = 'Please enter a valid email address';
                    this.classList.add('is-invalid');
                } else {
                    errorDiv.innerHTML = '';
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        }
        
        // Phone validation (optional)
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                const errorDiv = document.getElementById('error-contact-phone');
                const phoneRegex = /^[0-9]{10}$/;
                if (this.value.trim() !== '' && !phoneRegex.test(this.value)) {
                    errorDiv.innerHTML = 'Phone number must be 10 digits';
                    this.classList.add('is-invalid');
                } else {
                    errorDiv.innerHTML = '';
                    this.classList.remove('is-invalid');
                    if (this.value.trim() !== '') {
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                    }
                }
            });
        }
        
        // Subject validation
        if (subjectSelect) {
            subjectSelect.addEventListener('change', function() {
                const errorDiv = document.getElementById('error-contact-subject');
                if (this.value === '') {
                    errorDiv.innerHTML = 'Please select a subject';
                    this.classList.add('is-invalid');
                } else {
                    errorDiv.innerHTML = '';
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        }
        
        // Message validation
        if (messageTextarea) {
            messageTextarea.addEventListener('input', function() {
                const errorDiv = document.getElementById('error-contact-message');
                if (this.value.trim() === '') {
                    errorDiv.innerHTML = 'Please enter your message';
                    this.classList.add('is-invalid');
                } else {
                    errorDiv.innerHTML = '';
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        }
        
        // Form submit validation
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Clear all previous error messages
            document.querySelectorAll('.error-message').forEach(el => el.innerHTML = '');
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Validate name
            const name = nameInput.value.trim();
            if (name === '') {
                document.getElementById('error-contact-name').innerHTML = 'Please enter your full name';
                nameInput.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate email
            const email = emailInput.value.trim();
            const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
            if (email === '') {
                document.getElementById('error-contact-email').innerHTML = 'Please enter your email address';
                emailInput.classList.add('is-invalid');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                document.getElementById('error-contact-email').innerHTML = 'Please enter a valid email address';
                emailInput.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate phone (if provided)
            const phone = phoneInput.value.trim();
            const phoneRegex = /^[0-9]{10}$/;
            if (phone !== '' && !phoneRegex.test(phone)) {
                document.getElementById('error-contact-phone').innerHTML = 'Phone number must be 10 digits';
                phoneInput.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate subject
            const subject = subjectSelect.value;
            if (subject === '') {
                document.getElementById('error-contact-subject').innerHTML = 'Please select a subject';
                subjectSelect.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate message
            const message = messageTextarea.value.trim();
            if (message === '') {
                document.getElementById('error-contact-message').innerHTML = 'Please enter your message';
                messageTextarea.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
                // Scroll to the first error
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    })();
    </script>
</body>
</html>