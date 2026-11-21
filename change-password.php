<?php
session_start();
include 'includes/db_config.php';
include 'includes/header.php';

// Check login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

if (isset($_POST['change_password'])) {

    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Step 1: Fetch current password from DB
    $query = "SELECT password FROM register WHERE id = $user_id";
    $result = mysqli_query($con, $query);
    $user = mysqli_fetch_assoc($result);

    // Step 2: Verify old password
    if (!password_verify($old_password, $user['password'])) {
        $error = "Old password is incorrect!";
    }
    // Step 3: Check new password match
    elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match!";
    }
    // Step 4: Password length validation
    elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters!";
    }
    else {
        // Step 5: Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Step 6: Update password
        $update = "UPDATE register SET password = '$hashed_password' WHERE id = $user_id";

        if (mysqli_query($con, $update)) {
            $success = "Password changed successfully!";
        } else {
            $error = "Error updating password!";
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="mb-4">Change Password</h4>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Old Password</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-success">
                            Change Password
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>