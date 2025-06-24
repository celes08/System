<?php
include("user_session.php");
requireLogin();

$user = [
    'firstName' => $currentUser['first_name'],
    'lastName' => $currentUser['last_name'],
    'email' => $currentUser['email'],
    'studentNumber' => $currentUser['student_number'],
    'department' => $currentUser['department'],
    'dateOfBirth' => $currentUser['date_of_birth'],
    'theme' => $_SESSION['theme'] ?? 'system',
    'compactMode' => $_SESSION['compactMode'] ?? false,
    'highContrast' => $_SESSION['highContrast'] ?? false,
    'emailAllAnnouncements' => $_SESSION['emailAllAnnouncements'] ?? true,
    'emailDepartmentOnly' => $_SESSION['emailDepartmentOnly'] ?? false,
    'emailMentions' => $_SESSION['emailMentions'] ?? true,
    'browserNotifications' => $_SESSION['browserNotifications'] ?? true,
    'soundNotifications' => $_SESSION['soundNotifications'] ?? false,
    'notificationFrequency' => $_SESSION['notificationFrequency'] ?? 'instant',
];

$successMsg = '';
$errorMsg = '';
$showPasswordModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['saveAccountChanges'])) {
        $userId = $currentUser['user_id'];
        $newFirstName = $_POST['firstName'];
        $newLastName = $_POST['lastName'];
        $newDepartment = $_POST['department'];
        $newDateOfBirth = $_POST['dateOfBirth'];
        
        $updateStmt = $con->prepare("UPDATE signuptbl SET first_name = ?, last_name = ?, department = ?, date_of_birth = ? WHERE user_id = ?");
        $updateStmt->bind_param("ssssi", $newFirstName, $newLastName, $newDepartment, $newDateOfBirth, $userId);
        
        if ($updateStmt->execute()) {
            $successMsg = 'Account information updated successfully!';
            $currentUser = getUserData();
            $user = array_merge($user, [
                'firstName' => $currentUser['first_name'],
                'lastName' => $currentUser['last_name'],
                'department' => $currentUser['department'],
                'dateOfBirth' => $currentUser['date_of_birth'],
            ]);
        } else {
            $errorMsg = 'Error updating account information.';
        }
        $updateStmt->close();
    }
    
    if (isset($_POST['showChangePassword'])) {
        $showPasswordModal = true;
    }
    
    if (isset($_POST['changePassword'])) {
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmNewPassword = $_POST['confirmNewPassword'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
            $errorMsg = 'All password fields are required.';
        } elseif ($newPassword !== $confirmNewPassword) {
            $errorMsg = 'New passwords do not match.';
        } else {
            $successMsg = 'Password changed successfully!';
            $showPasswordModal = false; 
        }
    }
    
    if (isset($_POST['cancelPasswordChange'])) {
        $showPasswordModal = false;
    }

    if (isset($_POST['saveAppearance'])) {
        $_SESSION['theme'] = $_POST['theme'];
        $_SESSION['compactMode'] = isset($_POST['compactMode']);
        $_SESSION['highContrast'] = isset($_POST['highContrast']);
        $user['theme'] = $_POST['theme'];
        $user['compactMode'] = isset($_POST['compactMode']);
        $user['highContrast'] = isset($_POST['highContrast']);
        $successMsg = 'Appearance settings updated!';
    }

    if (isset($_POST['saveNotifications'])) {
        $_SESSION['emailAllAnnouncements'] = isset($_POST['emailAllAnnouncements']);
        $_SESSION['emailDepartmentOnly'] = isset($_POST['emailDepartmentOnly']);
        $_SESSION['emailMentions'] = isset($_POST['emailMentions']);
        $_SESSION['browserNotifications'] = isset($_POST['browserNotifications']);
        $_SESSION['soundNotifications'] = isset($_POST['soundNotifications']);
        $_SESSION['notificationFrequency'] = $_POST['notificationFrequency'] ?? 'instant';
        
        $user['emailAllAnnouncements'] = $_SESSION['emailAllAnnouncements'];
        $user['emailDepartmentOnly'] = $_SESSION['emailDepartmentOnly'];
        $user['emailMentions'] = $_SESSION['emailMentions'];
        $user['browserNotifications'] = $_SESSION['browserNotifications'];
        $user['soundNotifications'] = $_SESSION['soundNotifications'];
        $user['notificationFrequency'] = $_SESSION['notificationFrequency'];

        $successMsg = 'Notification settings updated successfully!';
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo $user['theme'] === 'dark' ? 'dark-theme' : ($user['theme'] === 'light' ? 'light-theme' : ''); ?> <?php echo $user['highContrast'] ? 'high-contrast' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css"> 
    <link rel="stylesheet" href="settings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="img/logo.png" alt="CVSU Logo" class="sidebar-logo">
            </div>
            <div class="sidebar-main-scrollable-area">
                <nav class="sidebar-nav">
                    <ul>
                        <li><a href="dashboard.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="organizational-chart.php"><i class="fas fa-sitemap"></i> Organizational Chart</a></li>
                        <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                        <li><a href="bookmarks.php"><i class="fas fa-bookmark"></i> Bookmarks</a></li>
                        <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    </ul>
                </nav>
                <div class="post-button-container">
                    <button class="post-button" id="postButton"><i class="fas fa-plus"></i> Post</button>
                </div>
            </div>
            <div class="sidebar-footer">
                <ul>
                    <li class="active"><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="help.php"><i class="fas fa-question-circle"></i> Help</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
                </ul>
                <div class="user-profile">
                    <div class="user-avatar">
                        <?php
                        $profilePic = $currentUser['profile_picture'] ?? '';
                        if ($profilePic) {
                            $profilePic = preg_replace('#^uploads/#', '', $profilePic);
                            $imgSrc = 'uploads/' . htmlspecialchars($profilePic);
                        } else {
                            $initials = htmlspecialchars(substr($currentUser['fullName'], 0, 1));
                            $imgSrc = 'https://placehold.co/36x36/cccccc/000000?text=' . $initials;
                        }
                        ?>
                        <img src="<?php echo $imgSrc; ?>" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($currentUser['fullName']); ?></h4>
                        <p><?php echo htmlspecialchars($currentUser['username']); ?></p>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </aside>

        <main class="settings-main-content">
            <div class="settings-header">
                <h1>Settings</h1>
            </div>
            
            <div class="settings-tabs">
                <button class="settings-tab active" data-tab="account"><i class="fas fa-user-cog"></i> Account</button>
                <button class="settings-tab" data-tab="appearance"><i class="fas fa-paint-brush"></i> Appearance</button>
                <button class="settings-tab" data-tab="notifications"><i class="fas fa-bell"></i> Notifications</button>
                <button class="settings-tab" data-tab="privacy"><i class="fas fa-shield-alt"></i> Privacy & Security</button>
                <button class="settings-tab" data-tab="muted"><i class="fas fa-volume-mute"></i> Muted Words</button>
            </div>

            <div class="settings-content">
                <div class="tab-pane active" id="account-content">
                    <form method="POST">
                        <div class="settings-card">
                            <h2 class="card-title">Account Information</h2>
                            <div class="card-content">
                                <div class="form-grid">
                                    <div class="form-group"><label for="firstName">First Name</label><input type="text" id="firstName" name="firstName" class="edit-input" value="<?php echo htmlspecialchars($user['firstName']); ?>"></div>
                                    <div class="form-group"><label for="lastName">Last Name</label><input type="text" id="lastName" name="lastName" class="edit-input" value="<?php echo htmlspecialchars($user['lastName']); ?>"></div>
                                    <div class="form-group"><label for="email">Email Address</label><input type="email" id="email" class="display-input" value="<?php echo htmlspecialchars($user['email']); ?>" readonly></div>
                                    <div class="form-group"><label for="studentNumber">Student Number</label><span id="display-studentNumber" class="display-input"><?php echo htmlspecialchars($user['studentNumber']); ?></span></div>
                                    <div class="form-group"><label for="department">Department</label><select id="department" name="department" class="edit-input"><?php $depts = ['DIT', 'DAS', 'TED', 'DOM']; foreach ($depts as $dept): ?><option value="<?php echo $dept; ?>" <?php if($user['department'] == $dept) echo 'selected';?>><?php echo $dept; ?></option><?php endforeach; ?></select></div>
                                    <div class="form-group"><label for="dateOfBirth">Year of Birth</label><select id="dateOfBirth" name="dateOfBirth" class="edit-input"><?php for($i = date('Y'); $i >= 1950; $i--): ?><option value="<?php echo $i; ?>" <?php if($user['dateOfBirth'] == $i) echo 'selected';?>><?php echo $i; ?></option><?php endfor; ?></select></div>
                                </div>
                            </div>
                            <div class="card-footer"><button type="submit" name="saveAccountChanges" class="btn-primary">Save Changes</button></div>
                        </div>
                    </form>
                    <div class="settings-card">
                        <h2 class="card-title">Change Password</h2>
                        <div class="card-content"><p class="card-description">For security, you must enter your current password to change it.</p></div>
                        <div class="card-footer"><form method="POST"><button type="submit" name="showChangePassword" class="btn-secondary">Change Password</button></form></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php if ($showPasswordModal): ?>
    <div class="modal-overlay active">
        <div class="modal-content">
            <h2 class="modal-title">Change Password</h2>
            <form method="POST">
                <div class="form-group"><label for="currentPassword">Current Password</label><input type="password" id="currentPassword" name="currentPassword" required></div>
                <div class="form-group"><label for="newPassword">New Password</label><input type="password" id="newPassword" name="newPassword" required></div>
                <div class="form-group"><label for="confirmNewPassword">Confirm New Password</label><input type="password" id="confirmNewPassword" name="confirmNewPassword" required></div>
                <div class="modal-actions">
                    <button type="submit" name="cancelPasswordChange" class="btn-secondary">Cancel</button>
                    <button type="submit" name="changePassword" class="btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="settings.js"></script>
</body>
</html> 