<?php
require_once 'auth_check.php';
require_once '../includes/db_config.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$page_title = "Contact Messages - Admin Dashboard";
$success_message = '';
$error_message = '';

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'])) {
    $message_id = (int)$_POST['message_id'];
    $reply = mysqli_real_escape_string($con, trim($_POST['reply']));
    $admin_id = $_SESSION['admin_id'];
    
    if (!empty($reply)) {
        // Get original message details
        $get_msg = "SELECT * FROM contact_messages WHERE id = $message_id";
        $result = mysqli_query($con, $get_msg);
        $message = mysqli_fetch_assoc($result);
        
        if ($message) {
            // Update the message with reply
            $update_query = "UPDATE contact_messages SET 
                            admin_reply = '$reply', 
                            replied_by = $admin_id, 
                            replied_at = NOW(), 
                            status = 'replied' 
                            WHERE id = $message_id";
            
            if (mysqli_query($con, $update_query)) {
                // Send reply email to user
                if (sendReplyEmail($message['email'], $message['name'], $reply, $message['subject'])) {
                    $success_message = "Reply sent successfully! Email notification has been sent to the user.";
                } else {
                    $success_message = "Reply saved but email notification failed to send.";
                }
            } else {
                $error_message = "Failed to save reply.";
            }
        }
    } else {
        $error_message = "Please enter a reply message.";
    }
}

// Function to send reply email to user
function sendReplyEmail($user_email, $user_name, $reply, $subject) {
    require_once '../includes/mail_config.php';
    
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
        $mail->setFrom('priyankmalviya1234@gmail.com', 'Fresh Groceries Support');
        $mail->addAddress($user_email, $user_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Re: $subject";
        
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .reply-box { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #28a745; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Fresh Groceries Support Team</h2>
                </div>
                <div class='content'>
                    <h3>Hello $user_name,</h3>
                    <p>Thank you for contacting us. Here's our response to your inquiry:</p>
                    
                    <div class='reply-box'>
                        " . nl2br($reply) . "
                    </div>
                    
                    <p>If you have any further questions, feel free to reply to this email or contact us through our website.</p>
                    
                    <p>Best regards,<br>
                    <strong>Fresh Groceries Support Team</strong></p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Fresh Groceries. All rights reserved.</p>
                    <p>This is an automated response, please do not reply directly to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Hello $user_name,\n\nThank you for contacting us. Here's our response:\n\n$reply\n\nBest regards,\nFresh Groceries Support Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Get message details if viewing single message
$message_detail = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM contact_messages WHERE id = $id";
    $result = mysqli_query($con, $query);
    $message_detail = mysqli_fetch_assoc($result);
}

// Get statistics
$total_query = mysqli_query($con, "SELECT COUNT(*) as total FROM contact_messages");
$total = mysqli_fetch_assoc($total_query)['total'];

$pending_query = mysqli_query($con, "SELECT COUNT(*) as pending FROM contact_messages WHERE status='pending'");
$pending = mysqli_fetch_assoc($pending_query)['pending'];

$replied_query = mysqli_query($con, "SELECT COUNT(*) as replied FROM contact_messages WHERE status='replied'");
$replied = mysqli_fetch_assoc($replied_query)['replied'];

// Get all messages for listing
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($con, $_GET['status']) : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';

$where_conditions = [];
if ($status_filter != 'all') {
    $where_conditions[] = "status = '$status_filter'";
}
if (!empty($search)) {
    $where_conditions[] = "(name LIKE '%$search%' OR email LIKE '%$search%' OR message LIKE '%$search%')";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$messages_query = "SELECT * FROM contact_messages $where_clause ORDER BY created_at DESC";
$messages_result = mysqli_query($con, $messages_query);
$total_messages = mysqli_num_rows($messages_result);
?>


<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-envelope me-2"></i>Contact Messages</h2>
            <p class="text-muted">Manage and respond to customer inquiries</p>
        </div>
        <a href="contact-messages.php" class="btn btn-primary">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h3><?php echo $total; ?></h3>
                    <p class="mb-0 text-muted">Total Messages</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3><?php echo $pending; ?></h3>
                    <p class="mb-0 text-muted">Pending Replies</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3><?php echo $replied; ?></h3>
                    <p class="mb-0 text-muted">Replied</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($message_detail): ?>
        <!-- View Single Message -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope-open-text me-2 text-success"></i>
                        Message Details
                    </h5>
                    <a href="contact-messages.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted mb-1">Sender Information</label>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-1"><strong><i class="fas fa-user me-2"></i>Name:</strong> <?php echo htmlspecialchars($message_detail['name']); ?></p>
                                <p class="mb-1"><strong><i class="fas fa-envelope me-2"></i>Email:</strong> <a href="mailto:<?php echo $message_detail['email']; ?>"><?php echo $message_detail['email']; ?></a></p>
                                <p class="mb-0"><strong><i class="fas fa-phone me-2"></i>Phone:</strong> <?php echo $message_detail['phone'] ?: 'Not provided'; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted mb-1">Message Information</label>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-1"><strong><i class="fas fa-tag me-2"></i>Subject:</strong> <?php echo ucfirst($message_detail['subject']); ?></p>
                                <p class="mb-1"><strong><i class="fas fa-calendar me-2"></i>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($message_detail['created_at'])); ?></p>
                                <p class="mb-0"><strong><i class="fas fa-flag me-2"></i>Status:</strong> 
                                    <span class="status-badge status-<?php echo $message_detail['status']; ?>">
                                        <?php echo ucfirst($message_detail['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="text-muted mb-2">Customer Message</label>
                    <div class="border rounded p-4 bg-white">
                        <?php echo nl2br(htmlspecialchars($message_detail['message'])); ?>
                    </div>
                </div>
                
                <?php if ($message_detail['admin_reply']): ?>
                    <div class="reply-box">
                        <h6><i class="fas fa-reply me-2"></i>Your Reply:</h6>
                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($message_detail['admin_reply'])); ?></p>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>Replied on: <?php echo date('F j, Y, g:i a', strtotime($message_detail['replied_at'])); ?>
                        </small>
                    </div>
                <?php endif; ?>
                
                <?php if ($message_detail['status'] != 'closed'): ?>
                    <div class="mt-4">
                        <h6><i class="fas fa-pen me-2"></i>Send Reply:</h6>
                        <form method="POST" action="">
                            <input type="hidden" name="message_id" value="<?php echo $message_detail['id']; ?>">
                            <textarea class="form-control mb-3" name="reply" rows="5" placeholder="Type your reply here..." required></textarea>
                            <button type="submit" name="reply_message" class="btn btn-success">
                                <i class="fas fa-paper-plane me-2"></i>Send Reply
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>This conversation has been closed.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Messages</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="replied" <?php echo $status_filter == 'replied' ? 'selected' : ''; ?>>Replied</option>
                                <option value="closed" <?php echo $status_filter == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, email or message..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">All Contact Messages</h5>
            </div>
            <div class="card-body">
                <?php if ($total_messages > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($message = mysqli_fetch_assoc($messages_result)): ?>
                                <tr>
                                    <td>#<?php echo $message['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo $message['email']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($message['email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="message-subject">
                                            <?php echo ucfirst($message['subject']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="message-preview" title="<?php echo htmlspecialchars($message['message']); ?>">
                                            <?php echo htmlspecialchars(substr($message['message'], 0, 50)); ?>...
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $message['status']; ?>">
                                            <?php echo ucfirst($message['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo date('M d, Y', strtotime($message['created_at'])); ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($message['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <a href="contact-messages.php?id=<?php echo $message['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No messages found</h5>
                        <p class="text-muted">There are no contact messages matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>