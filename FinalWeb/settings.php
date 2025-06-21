<?php
// filepath: c:\xampp\htdocs\FinalWeb\settings.php
session_start();
include("connections.php");

$user_id = $_SESSION['user_id'] ?? 1; // Replace with actual session logic

// 1. Load settings from DB (or use defaults)
$default_settings = [
    'theme_preference' => 'System',
    'email_on_campus_announcements' => 1,
    'email_on_department_announcements' => 1,
    'email_on_student_announcements' => 1,
    'email_on_mentions' => 1,
];
$settings = $default_settings;

$stmt = $con->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $settings = array_merge($settings, $row);
}
$stmt->close();

// 2. Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['theme_preference'] = $_POST['theme'] ?? $settings['theme_preference'];
    $settings['email_on_campus_announcements'] = isset($_POST['email_on_campus_announcements']) ? 1 : 0;
    $settings['email_on_department_announcements'] = isset($_POST['email_on_department_announcements']) ? 1 : 0;
    $settings['email_on_student_announcements'] = isset($_POST['email_on_student_announcements']) ? 1 : 0;
    $settings['email_on_mentions'] = isset($_POST['email_on_mentions']) ? 1 : 0;

    // Upsert (insert or update)
    $stmt = $con->prepare(
        "INSERT INTO user_settings 
        (user_id, theme_preference, email_on_campus_announcements, email_on_department_announcements, email_on_student_announcements, email_on_mentions)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            theme_preference=VALUES(theme_preference),
            email_on_campus_announcements=VALUES(email_on_campus_announcements),
            email_on_department_announcements=VALUES(email_on_department_announcements),
            email_on_student_announcements=VALUES(email_on_student_announcements),
            email_on_mentions=VALUES(email_on_mentions)"
    );
    $stmt->bind_param(
        "isiiii",
        $user_id,
        $settings['theme_preference'],
        $settings['email_on_campus_announcements'],
        $settings['email_on_department_announcements'],
        $settings['email_on_student_announcements'],
        $settings['email_on_mentions']
    );
    $stmt->execute();
    $stmt->close();
    $success_message = "Settings updated!";
}

// For theme class (convert DB value to class)
$theme = strtolower($settings['theme_preference']);
$theme = $theme === 'system' ? 'system' : ($theme === 'dark' ? 'dark' : 'light');

$user = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'student_number' => '',
    'department' => '',
    'date_of_birth' => ''
];

$stmt = $con->prepare("
    SELECT u.first_name, u.last_name, u.email, u.student_number, u.department_id, u.date_of_birth, d.department_code, d.department_name
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.department_id
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $user = $row;
}
$stmt->close();

$show_password_modal = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['showChangePassword'])) {
    $show_password_modal = true;
}

$departments = [];
$result = $con->query("SELECT department_id, department_code, department_name FROM departments");
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="settings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body theme-<?php echo htmlspecialchars($theme); ?>">
    <div class="dashboard-container" id="dashboardContainer">
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="img/logo.png" alt="CVSU Logo" class="sidebar-logo">
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li>
                        <a href="organizational-chart.php"><i class="fas fa-sitemap"></i> Organizational Chart</a>
                    </li>
                    <li>
                        <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                    </li>
                    <li>
                        <a href="bookmarks.php"><i class="fas fa-bookmark"></i> Bookmarks</a>
                    </li>
                    <li>
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    </li>
                </ul>
            </nav>
            <div class="post-button-container">
                <button class="post-button" id="postButton" disabled>
                    <i class="fas fa-plus"></i> Post
                </button>
            </div>
            <div class="sidebar-footer">
                <ul>
                    <li class="active"><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="help.php"><i class="fas fa-question-circle"></i> Help</a></li>
                    <li><a href="index.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
                </ul>
                <div class="user-profile">
                    <div class="user-avatar">
                        <img src="img/avatar-placeholder.png" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($user['first_name']); ?></h4>
                        <p>@person</p>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </aside>
        <!-- Main Content Area -->
        <main class="settings-main-content">
            <header class="settings-header">
                <h1>Settings</h1>
                <p>Manage your account preferences and application settings</p>
            </header>
            <div class="settings-content">
                <!-- Account Information Section -->
                <form method="post" class="settings-section" style="<?php echo ($active_section === 'account') ? '' : 'display:none;'; ?>">
                    <div class="section-header" data-section="account">
                        <div class="section-title">
                            <a href="settings.php?section=account" style="color:inherit;text-decoration:none;display:block;">
                                <h3><i class="fas fa-user-circle"></i> Account Information</h3>
                            </a>
                        </div>
                    </div>
                    <div class="section-content" id="account-content">
                        <div class="account-info-grid">
                            <div class="info-item">
                                <label>First Name</label>
                                <div class="info-value">
                                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                                </div>
                            </div>
                            <div class="info-item">
                                <label>Last Name</label>
                                <div class="info-value">
                                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                                </div>
                            </div>
                            <div class="info-item">
                                <label>Email Address</label>
                                <div class="info-value">
                                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                                    <span class="readonly-badge">Read Only</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <label>Student Number</label>
                                <div class="info-value">
                                    <span><?php echo htmlspecialchars($user['student_number']); ?></span>
                                    <span class="readonly-badge">Read Only</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <label>Department</label>
                                <div class="info-value">
                                    <select name="department_id">
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept['department_id']; ?>" <?php if($user['department_id'] == $dept['department_id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($dept['department_name']) . " (" . htmlspecialchars($dept['department_code']) . ")"; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="info-item">
                                <label>Date of Birth</label>
                                <div class="info-value">
                                    <input type="text" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="section-actions">
                            <button class="save-changes-btn" name="saveAccountChanges" type="submit">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                            <button class="change-password-btn" name="showChangePassword" type="submit">
                                <i class="fas fa-key"></i>
                                Change Password
                            </button>
                        </div>
                        <?php if ($success_message): ?>
                            <div class="success-message"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                    </div>
                </form>
                <!-- Appearance Section -->
                <form method="post" class="settings-section" style="<?php echo ($active_section === 'appearance') ? '' : 'display:none;'; ?>">
                    <div class="section-header" data-section="appearance">
                        <div class="section-title">
                            <i class="fas fa-palette"></i>
                            <h3><a href="settings.php?section=appearance" style="color:inherit;text-decoration:none;">Appearance</a></h3>
                        </div>
                    </div>
                    <div class="section-content" id="appearance-content">
                        <div class="appearance-options">
                            <div class="theme-selection">
                                <h4>Theme Preference</h4>
                                <p>Choose how the application looks to you</p>
                                <div class="theme-options">
                                    <label>
                                        <input type="radio" name="theme" value="Light" <?php if($settings['theme_preference']=='Light') echo 'checked'; ?> onchange="this.form.submit();">
                                        Light Mode
                                    </label>
                                    <label>
                                        <input type="radio" name="theme" value="Dark" <?php if($settings['theme_preference']=='Dark') echo 'checked'; ?> onchange="this.form.submit();">
                                        Dark Mode
                                    </label>
                                    <label>
                                        <input type="radio" name="theme" value="System" <?php if($settings['theme_preference']=='System') echo 'checked'; ?> onchange="this.form.submit();">
                                        System Default
                                    </label>
                                </div>
                            </div>
                            <div class="other-appearance-settings">
                                <h4>Display Options</h4>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>Compact Mode</label>
                                        <p>Show more content by reducing spacing</p>
                                    </div>
                                    <input type="checkbox" name="compactMode" disabled>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>High Contrast</label>
                                        <p>Increase contrast for better visibility</p>
                                    </div>
                                    <input type="checkbox" name="highContrast" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- Notifications Section -->
                <form method="post" class="settings-section" style="<?php echo ($active_section === 'notifications') ? '' : 'display:none;'; ?>">
                    <div class="section-header" data-section="notifications">
                        <div class="section-title">
                            <i class="fas fa-bell"></i>
                            <h3><a href="settings.php?section=notifications" style="color:inherit;text-decoration:none;">Notifications</a></h3>
                        </div>
                    </div>
                    <div class="section-content" id="notifications-content">
                        <div class="notifications-settings">
                            <div class="notification-category">
                                <h4>Email Notifications</h4>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>All Announcements</label>
                                        <p>Receive emails for all new announcements</p>
                                    </div>
                                    <input type="checkbox" name="email_on_campus_announcements" <?php if($settings['email_on_campus_announcements']) echo 'checked'; ?>>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>Department Announcements Only</label>
                                        <p>Only receive emails from your department</p>
                                    </div>
                                    <input type="checkbox" name="email_on_department_announcements" <?php if($settings['email_on_department_announcements']) echo 'checked'; ?>>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>Student Announcements</label>
                                        <p>Receive emails for student announcements</p>
                                    </div>
                                    <input type="checkbox" name="email_on_student_announcements" <?php if($settings['email_on_student_announcements']) echo 'checked'; ?>>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>Mentions and Replies</label>
                                        <p>Get notified when someone mentions you or replies to your posts</p>
                                    </div>
                                    <input type="checkbox" name="email_on_mentions" <?php if($settings['email_on_mentions']) echo 'checked'; ?>>
                                </div>
                            </div>
                            <!-- You can add more notification categories here if you add columns to your table -->
                        </div>
                        <div class="section-actions">
                            <button class="save-changes-btn" type="submit">
                                <i class="fas fa-save"></i>
                                Save Notification Settings
                            </button>
                        </div>
                        <?php if ($success_message): ?>
                            <div class="success-message"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                    </div>
                </form>
                <!-- Change Password Modal (PHP version: show inline if requested) -->
                <?php if ($show_password_modal): ?>
                <div class="modal-overlay active" id="changePasswordModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Change Password</h2>
                        </div>
                        <div class="modal-body">
                            <form method="post">
                                <div class="form-group">
                                    <label for="currentPassword">Current Password</label>
                                    <input type="password" id="currentPassword" name="currentPassword" placeholder="Enter current password" required>
                                </div>
                                <div class="form-group">
                                    <label for="newPassword">New Password</label>
                                    <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirmNewPassword">Confirm New Password</label>
                                    <input type="password" id="confirmNewPassword" name="confirmNewPassword" placeholder="Confirm new password" required>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="cancel-btn" name="cancelPasswordChange">Cancel</button>
                                    <button type="submit" class="save-btn" name="changePassword">
                                        <i class="fas fa-save"></i>
                                        Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

<style>
.theme-light { /* Default styles */ }
.theme-dark {
    background: #181818 !important;
    color: #f5f5f5 !important;
}
.theme-system {
    /* You can use prefers-color-scheme media query if you want */
}
</style>
