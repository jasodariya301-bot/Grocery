<?php
// Manual include of PHPMailer files (without Composer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files manually
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';


function sendVerificationEmail($email, $first_name, $token) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Enable verbose debug output (set to 0 for production)
        $mail->isSMTP();                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';       // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                    // Enable SMTP authentication
        $mail->Username   = '';  // SMTP username (your Gmail)
        $mail->Password   = '';     // SMTP password (Gmail App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587;                      // TCP port to connect to 
        
        // Recipients
        $mail->setFrom('noreply@freshgroceries.com', 'Fresh Groceries');
        $mail->addAddress($email, $first_name);       // Add a recipient
        
        // Content
        $mail->isHTML(true);                           // Set email format to HTML
        $mail->Subject = 'Verify Your Email - Fresh Groceries';
        
        // Get base URL - Fix for your local path
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . $host;
        
        // Important: Add the correct path to your project
        $project_folder = '/Grocery-website'; // Change this to your actual project folder name
        $verification_link = $base_url . $project_folder . "/verify-email.php?token=" . urlencode($token);
        
        // Email body
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 20px; background: #f9f9f9; border: 1px solid #ddd; }
                .button { display: inline-block; padding: 12px 30px; background: #28a745; 
                         color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                .button:hover { background: #218838; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .link-box { background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; word-break: break-all; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Fresh Groceries!</h1>
                </div>
                <div class='content'>
                    <h2>Hello $first_name,</h2>
                    <p>Thank you for registering with Fresh Groceries! Please verify your email address to activate your account.</p>
                    
                    <div style='text-align: center;'>
                        <a href='$verification_link' class='button'>Verify Email Address</a>
                    </div>
                    
                    <p><strong>Or copy and paste this link in your browser:</strong></p>
                    <div class='link-box'>
                        $verification_link
                    </div>
                    
                    <p><strong>Note:</strong> This link will expire in 24 hours for security reasons.</p>
                    
                    <p>If you didn't create an account with Fresh Groceries, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Fresh Groceries. All rights reserved.</p>
                    <p>This is an automated message, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Hello $first_name,\n\n" .
                         "Thank you for registering with Fresh Groceries! Please verify your email address to activate your account.\n\n" .
                         "Click this link to verify: $verification_link\n\n" .
                         "If you didn't create an account, please ignore this email.";
        
        $mail->send();
        return ['success' => true, 'message' => 'Verification email sent successfully'];
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return ['success' => false, 'message' => 'Failed to send verification email: ' . $mail->ErrorInfo];
    }

    // Add this function to your existing mail_config.php file

}
function sendResetOTPEmail($email, $first_name, $otp, $subject, $htmlMessage) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'priyankmalviya1234@gmail.com';
        $mail->Password   = 'fzuk jvot tskc vzqi';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('noreply@freshgroceries.com', 'Fresh Groceries');
        $mail->addAddress($email, $first_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlMessage;
        
        // Plain text version
        $mail->AltBody = "Hello $first_name,\n\n" .
                         "Your OTP for password reset is: $otp\n\n" .
                         "This OTP is valid for 15 minutes.\n\n" .
                         "If you didn't request this, please ignore this email.";
        
        $mail->send();
        return ['success' => true, 'message' => 'OTP email sent successfully'];
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return ['success' => false, 'message' => 'Failed to send OTP email: ' . $mail->ErrorInfo];
    }
}
?>
    