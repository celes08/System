<?php
include_once "theme-manager.php";
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reported Posts - Admin Portal</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?php echo getThemeClasses(); ?>">
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <img src="img/logo.png" alt="CVSU Logo" class="logo">
                <h1>Reported Posts</h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="reported-posts-container" id="reportedPostsContainer">
                <!-- Reported posts will be populated here -->
            </div>

            <div class="back-button">
                <a href="admin-dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to admin page
                </a>
            </div>
        </main>
    </div>

    <!-- Suspension Modal -->
    <div class="modal" id="suspensionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Suspend User</h3>
                <span class="modal-close" id="suspensionModalClose">&times;</span>
            </div>
            <div class="suspension-details">
                <p>Select suspension duration for user: <strong id="suspensionUserName"></strong></p>
                <div class="suspension-options">
                    <label class="radio-option">
                        <input type="radio" name="suspensionDuration" value="3days">
                        <span>3 Days</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="suspensionDuration" value="1week">
                        <span>1 Week</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="suspensionDuration" value="permanent">
                        <span>Permanent Ban</span>
                    </label>
                </div>
                <div class="form-group">
                    <label for="suspensionReason">Reason for Suspension</label>
                    <textarea id="suspensionReason" rows="3" placeholder="Enter reason for suspension..."></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="closeSuspensionModal()">Cancel</button>
                <button class="btn-danger" onclick="confirmSuspension()">
                    <i class="fas fa-ban"></i> Suspend User
                </button>
            </div>
        </div>
    </div>

    <script src="reported-posts.js"></script>
</body>
</html>
