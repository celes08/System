<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Additional styles specific to forgot password page */
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

                <form id="resetPasswordForm">
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                        <span class="error-message" id="newPasswordError"></span>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required>
                        <span class="error-message" id="confirmPasswordError"></span>
                    </div>

                    <button type="submit" class="login-button" id="submitButton">
                        <span class="button-text">Submit</span>
                        <span class="loading-spinner" id="loadingSpinner"></span>
                    </button>
                    
                    <a href="index.html" class="back-to-login">BACK TO LOG IN</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>© 2025 School Bulletin Board System. All rights reserved.</p>
    </div>

    <!-- Success/Error Messages -->
    <div class="notification" id="notification">
        <span id="notificationMessage"></span>
        <button class="notification-close" id="notificationClose">&times;</button>
    </div>

    <script src="script.js"></script>
</body>
</html>