<?php
// forgot-password.php

include("connections.php"); // Make sure this sets up $con as your MySQLi connection

$success_message = '';
$error_message = '';

// Example: Get reset token from URL (for real use, you should send a token via email)
$token = $_GET['token'] ?? '';

if (!$token) {
    $error_message = "Invalid or missing password reset token.";
} else {
    // 1. Check if token exists and is not used/expired
    $stmt = $con->prepare("SELECT user_id, expires_at, is_used FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows !== 1) {
        $error_message = "Invalid or expired password reset link.";
    } else {
        $stmt->bind_result($userId, $expiresAt, $isUsed);
        $stmt->fetch();

        // Check if already used or expired
        if ($isUsed) {
            $error_message = "This password reset link has already been used.";
        } elseif (strtotime($expiresAt) < time()) {
            $error_message = "This password reset link has expired.";
        } else {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $newPassword = $_POST['newPassword'] ?? '';
                $confirmPassword = $_POST['confirmPassword'] ?? '';

                // Basic validation
                if (empty($newPassword) || empty($confirmPassword)) {
                    $error_message = "Both fields are required.";
                } elseif ($newPassword !== $confirmPassword) {
                    $error_message = "Passwords do not match.";
                } elseif (strlen($newPassword) < 6) {
                    $error_message = "Password must be at least 6 characters.";
                } else {
                    // Update password in users table
                    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                    $update = $con->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                    $update->bind_param("si", $hashed, $userId);
                    $update->execute();
                    $update->close();

                    // Mark reset as used
                    $markUsed = $con->prepare("UPDATE password_resets SET is_used = 1 WHERE token = ?");
                    $markUsed->bind_param("s", $token);
                    $markUsed->execute();
                    $markUsed->close();

                    $success_message = "Password reset successful! You may now <a href='index.php'>log in</a>.";
                }
            }
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .back-to-login {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #2d6a2d;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        .back-to-login:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Background Image -->
    <img src="img/Silang-Campus-scaled.jpg" alt="Campus aerial view" class="background-image">

    <!-- Main Container -->
    <div class="main-container">
        <!-- Left Panel -->
        <div class="left-panel">
            <div class="logo-container">
                <img src="img/logo.png" alt="CVSU Logo" class="logo">
            </div>
            <h1>Welcome to CVSU's Department Bulletin Board System</h1>
            <p>Stay updated with the latest announcements from all departments</p>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <div class="form-container">
                <h2>REQUEST CHANGE PASSWORD</h2>

                <?php if ($success_message): ?>
                    <div class="notification success" style="display:block;">
                        <?php echo $success_message; ?>
                    </div>
                <?php elseif ($error_message): ?>
                    <div class="notification error" style="display:block;">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (!$success_message): ?>
                <form id="resetPasswordForm" method="POST" action="">
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required>
                    </div>

                    <button type="submit" class="login-button" id="submitButton">
                        <span class="button-text">Submit</span>
                    </button>
                    
                    <a href="index.php" class="back-to-login">BACK TO LOG IN</a>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>© 2025 School Bulletin Board System. All rights reserved.</p>
    </div>
</body>
</html>