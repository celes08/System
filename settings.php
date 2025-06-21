<?php
// PHP SCRIPT START
session_start();

// Simulated user data (replace with DB logic in production for persistent storage)
$user = [
    'firstName' => $_SESSION['firstName'] ?? 'John',
    'lastName' => $_SESSION['lastName'] ?? 'Doe',
    'email' => 'john.doe@cvsu.edu.ph',
    'studentNumber' => '202312345',
    'department' => $_SESSION['department'] ?? 'DIT',
    'dateOfBirth' => $_SESSION['dateOfBirth'] ?? '1995',
    'theme' => $_SESSION['theme'] ?? 'system',
    'compactMode' => $_SESSION['compactMode'] ?? false,
    'highContrast' => $_SESSION['highContrast'] ?? false,
    // Add notification settings to the user array
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

// Handle POST requests for form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Account Information changes
    if (isset($_POST['saveAccountChanges'])) {
        $_SESSION['firstName'] = $_POST['firstName'];
        $_SESSION['lastName'] = $_POST['lastName'];
        $_SESSION['department'] = $_POST['department'];
        $_SESSION['dateOfBirth'] = $_POST['dateOfBirth'];
        
        $user['firstName'] = $_POST['firstName'];
        $user['lastName'] = $_POST['lastName'];
        $user['department'] = $_POST['department'];
        $user['dateOfBirth'] = $_POST['dateOfBirth'];
        $successMsg = 'Account information updated successfully!';
    }
    // Handle request to show Change Password modal
    if (isset($_POST['showChangePassword'])) {
        $showPasswordModal = true;
    }
    // Handle Change Password submission
    if (isset($_POST['changePassword'])) {
        // Password change logic here (validate and update password)
        // In a real application, you would add server-side validation against a hashed password
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmNewPassword = $_POST['confirmNewPassword'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
            $errorMsg = 'All password fields are required.';
        } elseif ($newPassword !== $confirmNewPassword) {
            $errorMsg = 'New passwords do not match.';
        } elseif (strlen($newPassword) < 6) {
            $errorMsg = 'New password must be at least 6 characters long.';
        } else {
            // Simulate successful password change (replace with actual password hashing and update)
            $successMsg = 'Password changed successfully!';
            $showPasswordModal = false; // Close modal on success
        }
    }
    // Handle Cancel Password Change action
    if (isset($_POST['cancelPasswordChange'])) {
        $showPasswordModal = false;
    }
    // Handle Appearance settings changes
    if (isset($_POST['saveAppearance'])) {
        $_SESSION['theme'] = $_POST['theme'];
        $_SESSION['compactMode'] = isset($_POST['compactMode']);
        $_SESSION['highContrast'] = isset($_POST['highContrast']);
        
        $user['theme'] = $_POST['theme'];
        $user['compactMode'] = isset($_POST['compactMode']);
        $user['highContrast'] = isset($_POST['highContrast']);
        $successMsg = 'Appearance settings updated!';
    }
    // Handle Notifications settings changes
    if (isset($_POST['saveNotifications'])) {
        $_SESSION['emailAllAnnouncements'] = isset($_POST['emailAllAnnouncements']);
        $_SESSION['emailDepartmentOnly'] = isset($_POST['emailDepartmentOnly']);
        $_SESSION['emailMentions'] = isset($_POST['emailMentions']);
        $_SESSION['browserNotifications'] = isset($_POST['browserNotifications']);
        $_SESSION['soundNotifications'] = isset($_POST['soundNotifications']);
        $_SESSION['notificationFrequency'] = $_POST['notificationFrequency'] ?? 'instant';

        // Update the $user array to reflect changes immediately
        $user['emailAllAnnouncements'] = $_SESSION['emailAllAnnouncements'];
        $user['emailDepartmentOnly'] = $_SESSION['emailDepartmentOnly'];
        $user['emailMentions'] = $_SESSION['emailMentions'];
        $user['browserNotifications'] = $_SESSION['browserNotifications'];
        $user['soundNotifications'] = $_SESSION['soundNotifications'];
        $user['notificationFrequency'] = $_SESSION['notificationFrequency'];

        $successMsg = 'Notification settings updated successfully!';
    }
}
// PHP SCRIPT END
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CVSU Department Bulletin Board System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* BASE STYLES */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            position: relative;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        /* THEME VARIABLES */
        :root {
            --bg-color: #f5f5f5;
            --text-color: #333333;
            --content-bg: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #e1e5e9;
            --accent-color: #007bff; /* General accent, not specific to green */

            /* Sidebar specific variables for light mode (default green sidebar) */
            --sidebar-bg: #1b4332;
            --sidebar-text-primary: white;
            --sidebar-text-secondary: rgba(255, 255, 255, 0.6);
            --sidebar-hover-bg: rgba(255, 255, 255, 0.05);
            --sidebar-active-bg: rgba(255, 255, 255, 0.1);
            --sidebar-border: rgba(255, 255, 255, 0.1);
            --post-button-bg: white;
            --post-button-text: #1b4332;
            --post-button-hover-bg: #f0f0f0;
            --user-profile-bg: rgba(255, 255, 255, 0.05);
        }

        .dark-theme {
            --bg-color: #1a1a1a;
            --text-color: #ffffff;
            --content-bg: #2d2d2d;
            --card-bg: #333333;
            --border-color: #444444;
            --accent-color: #4dabf7; /* For general use elements that follow theme */

            /* Sidebar specific variables for dark mode (still green sidebar, but adjusting internal elements) */
            --sidebar-bg: #1b4332; /* Remains green */
            --sidebar-text-primary: #ffffff;
            --sidebar-text-secondary: rgba(255, 255, 255, 0.8); /* Slightly brighter secondary text */
            --sidebar-hover-bg: rgba(255, 255, 255, 0.1); /* Slightly more opaque hover */
            --sidebar-active-bg: rgba(255, 255, 255, 0.2); /* Slightly more opaque active */
            --sidebar-border: rgba(255, 255, 255, 0.2); /* More visible border in dark mode */
            --post-button-bg: #4a4a4a; /* Darker button background */
            --post-button-text: #ffffff; /* White text on darker button */
            --post-button-hover-bg: #5a5a5a; /* Hover for darker button */
            --user-profile-bg: rgba(255, 255, 255, 0.15); /* More visible user profile background */
        }

        .high-contrast {
            --bg-color: #000000;
            --text-color: #ffffff;
            --content-bg: #000000;
            --card-bg: #000000;
            --border-color: #ffffff;
            --accent-color: #ffff00;

            /* High contrast adjustments for sidebar */
            --sidebar-bg: #000000;
            --sidebar-text-primary: #ffffff;
            --sidebar-text-secondary: #ffffff;
            --sidebar-hover-bg: #333333;
            --sidebar-active-bg: #666666;
            --sidebar-border: #ffffff;
            --post-button-bg: #ffffff;
            --post-button-text: #000000;
            --post-button-hover-bg: #e0e0e0;
            --user-profile-bg: #333333;
        }

        /* DASHBOARD LAYOUT */
        body.dashboard-body {
            display: flex;
            height: 100%;
            overflow-y: auto; /* Allows overall page scrolling */
            background-color: var(--bg-color);
        }

        .dashboard-container {
            display: flex;
            width: 100%;
        }

        /* SIDEBAR STYLES */
        .sidebar {
            width: 280px;
            background-color: var(--sidebar-bg); /* Use variable */
            color: var(--sidebar-text-primary); /* Use variable */
            display: flex;
            flex-direction: column;
            height: 100%;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid var(--sidebar-border); /* Use variable */
            background-color: var(--sidebar-bg); /* Use variable */
            flex-shrink: 0; /* Prevents shrinking */
        }

        .sidebar-logo {
            width: 80px;
            height: 80px;
            object-fit: contain; /* Ensures the entire logo is visible */
        }

        .sidebar-main-scrollable-area {
            flex-grow: 1; /* Takes remaining vertical space */
            overflow-y: auto; /* Enables scrolling for nav and post button */
            padding: 20px 0;
            background-color: var(--sidebar-bg); /* Use variable */
            scrollbar-width: thin;
            scrollbar-color: var(--sidebar-hover-bg) var(--sidebar-bg); /* Custom scrollbar for better visibility */
        }

        .sidebar-main-scrollable-area::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar-main-scrollable-area::-webkit-scrollbar-track {
            background: var(--sidebar-bg);
            border-radius: 4px;
        }

        .sidebar-main-scrollable-area::-webkit-scrollbar-thumb {
            background: var(--sidebar-hover-bg);
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        .sidebar-main-scrollable-area::-webkit-scrollbar-thumb:hover {
            background: var(--sidebar-active-bg);
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin-bottom: 5px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--sidebar-text-primary); /* Use variable */
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .sidebar-nav a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar-nav li.active a {
            background-color: var(--sidebar-active-bg); /* Use variable */
            font-weight: bold;
        }

        .sidebar-nav a:hover {
            background-color: var(--sidebar-hover-bg); /* Use variable */
        }

        .post-button-container {
            padding: 0 20px 20px;
            flex-shrink: 0; /* Prevents shrinking */
        }

        .post-button {
            width: 100%;
            padding: 12px;
            background-color: white; /* Use variable */
            color: #1b4332; /* Use variable */
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }

        .post-button i {
            margin-right: 8px;
        }

        .post-button:hover {
            background-color: var(--post-button-hover-bg); /* Use variable */
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid var(--sidebar-border); /* Use variable */
            background-color: var(--sidebar-bg); /* Use variable */
            flex-shrink: 0; /* Prevents shrinking */
        }

        .sidebar-footer ul {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
        }

        .sidebar-footer li {
            margin-bottom: 10px;
        }

        .sidebar-footer a {
            color: var(--sidebar-text-secondary); /* Use variable */
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar-footer a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer a:hover {
            color: var(--sidebar-text-primary); /* Use variable for hover */
        }

        .user-profile {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: var(--user-profile-bg); /* Use variable */
            border-radius: 8px;
            cursor: pointer;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 10px;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            flex-grow: 1;
        }

        .user-info h4 {
            margin: 0;
            font-size: 14px;
            color: var(--sidebar-text-primary); /* Use variable */
        }

        .user-info p {
            margin: 0;
            font-size: 12px;
            color: var(--sidebar-text-secondary); /* Use variable */
        }

        .user-profile i {
            font-size: 12px;
            color: var(--sidebar-text-primary); /* Ensures arrow also adapts */
        }

        /* MAIN CONTENT STYLES */
        .settings-main-content {
            flex-grow: 1;
            margin-left: 280px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .settings-header {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 30px 50px;
            flex-shrink: 0;
        }
          
        .settings-header h1 {
            margin: 0 0 8px 0;
            font-size: 32px;
            font-weight: 700;
            color: var(--text-color);
        }
          
        .settings-header p {
            margin: 0;
            font-size: 16px;
            color: #6c757d; /* Keep fixed for consistent description grey */
        }
          
        .settings-content {
            flex: 1;
            overflow-y: auto;
            padding: 0;
            background-color: var(--content-bg);
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 var(--content-bg);
        }
          
        .settings-content::-webkit-scrollbar {
            width: 8px;
        }
        .settings-content::-webkit-scrollbar-thumb {
            background: #c1c1c1; /* Can be themed with var(--border-color) or a new scrollbar thumb var */
            border-radius: 4px;
        }
        .settings-content::-webkit-scrollbar-track {
            background: var(--content-bg);
            border-radius: 4px;
        }
          
        /* SETTINGS SECTIONS */
        .settings-section {
            background-color: var(--card-bg);
            margin: 20px 50px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }
          
        .settings-section:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
          
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            cursor: pointer;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.3s ease;
        }
          
        .section-header:hover {
            background-color: var(--content-bg);
        }
          
        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
          
        .section-title i {
            font-size: 20px;
            color: #1b4332; /* Keep icon green for branding, or make it variable */
        }
          
        .section-title h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
        }
          
        .section-arrow {
            font-size: 16px;
            color: #6c757d;
            transition: transform 0.3s ease;
        }
          
        .section-header.active .section-arrow {
            transform: rotate(180deg);
        }
          
        .section-content {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }
          
        .section-content.active {
            max-height: 2000px; /* Sufficiently large for content */
            padding: 24px;
        }
          
        /* ACCOUNT INFORMATION STYLES */
        .account-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
          
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
          
        .info-item label {
            font-size: 14px;
            font-weight: 600;
            color: #495057; /* Keep fixed grey for consistency */
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
          
        .info-value {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background-color: var(--content-bg);
            border-radius: 8px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
          
        .info-value:hover {
            background-color: #e9ecef; /* Keep fixed light hover for readability in light mode */
        }
          
        .info-value span {
            font-size: 16px;
            color: var(--text-color);
            font-weight: 500;
        }
          
        .readonly-badge {
            background-color: #6c757d; /* Keep fixed grey for badge */
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
          
        .edit-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            background-color: var(--card-bg);
            color: var(--text-color);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
          
        .edit-input:focus {
            outline: none;
            border-color: #1b4332; /* Keep green focus border */
            box-shadow: 0 0 0 3px rgba(27, 67, 50, 0.1);
        }

        .info-item select {
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%20viewBox%3D%220%200%20292.4%20292.4%22%3E%3Cpath%20fill%3D%22%236c757d%22%20d%3D%22M287%2C118.8L146.2%2C259.6L5.4%2C118.8c-2.8-2.8-4.3-6.6-4.3-10.8s1.5-8%2C4.3-10.8l8.5-8.5c2.8-2.8%2C6.6-4.3%2C10.8-4.3s8%2C1.5%2C10.8%2C4.3l111.9%2C111.9L257.4%2C88.7c2.8-2.8%2C6.6-4.3%2C10.8-4.3s8%2C1.5%2C10.8%2C4.3l8.5%2C8.5c2.8%2C2.8%2C4.3%2C6.6%2C4.3%2C10.8S289.8%2C116%2C287%2C118.8z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
            padding-right: 40px;
        }
        .dark-theme .info-item select {
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%20viewBox%3D%220%200%20292.4%20292.4%22%3E%3Cpath%20fill%3D%22%23ffffff%22%20d%3D%22M287%2C118.8L146.2%2C259.6L5.4%2C118.8c-2.8-2.8-4.3-6.6-4.3-10.8s1.5-8%2C4.3-10.8l8.5-8.5c2.8-2.8%2C6.6-4.3%2C10.8-4.3s8%2C1.5%2C10.8%2C4.3l111.9%2C111.9L257.4%2C88.7c2.8-2.8%2C6.6-4.3%2C10.8-4.3s8%2C1.5%2C10.8%2C4.3l8.5%2C8.5c2.8%2C2.8%2C4.3%2C6.6%2C4.3%2C10.8S289.8%2C116%2C287%2C118.8z%22%2F%3E%3C%2Fsvg%3E');
        }
          
        .section-actions {
            display: flex;
            gap: 12px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
            justify-content: flex-end;
        }
          
        .save-changes-btn, .change-password-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
          
        .save-changes-btn {
            background-color: #1b4332; /* Keep fixed green */
            color: white;
        }
          
        .save-changes-btn:hover:not(:disabled) {
            background-color: #0f2419; /* Keep fixed darker green */
            transform: translateY(-1px);
        }
          
        .save-changes-btn:disabled {
            background-color: #adb5bd;
            cursor: not-allowed;
            transform: none;
        }
          
        .change-password-btn {
            background-color: #6c757d; /* Keep fixed grey */
            color: white;
        }
          
        .change-password-btn:hover {
            background-color: #5a6268; /* Keep fixed darker grey */
            transform: translateY(-1px);
        }
          
        /* APPEARANCE STYLES */
        .appearance-options {
            display: flex;
            flex-direction: column;
            gap: 32px;
        }
          
        .theme-selection h4, .other-appearance-settings h4, .notification-category h4 {
            margin: 0 0 8px 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
        }
          
        .theme-selection p, .notification-category p {
            margin: 0 0 20px 0;
            color: #6c757d;
        }
          
        .theme-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
          
        .theme-option {
            position: relative;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: var(--card-bg);
        }
          
        .theme-option:hover {
            border-color: #1b4332; /* Keep green hover border */
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(27, 67, 50, 0.1);
        }

        .theme-option.selected {
            border-color: #1b4332; /* Keep green border when selected */
            background-color: rgba(27, 67, 50, 0.05); /* Keep light green tint when selected */
            box-shadow: 0 4px 12px rgba(27, 67, 50, 0.1);
        }
          
        .theme-option input[type="radio"] {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 20px;
            height: 20px;
            accent-color: #1b4332; /* Keep green radio button */
        }
          
        .theme-preview {
            width: 100%;
            height: 80px;
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
          
        .preview-header {
            height: 20px;
            background-color: #f8f9fa;
        }
          
        .preview-content {
            display: flex;
            height: 60px;
        }
          
        .preview-sidebar {
            width: 30%;
            background-color: #e9ecef;
        }
          
        .preview-main {
            flex: 1;
            background-color: #ffffff;
        }
          
        .dark-preview .preview-header {
            background-color: #2d3748;
        }
          
        .dark-preview .preview-sidebar {
            background-color: #1a202c;
        }
          
        .dark-preview .preview-main {
            background-color: #2d3748;
        }
          
        .system-preview .preview-header {
            background: linear-gradient(90deg, #f8f9fa 50%, #2d3748 50%);
        }
          
        .system-preview .preview-sidebar {
            background: linear-gradient(90deg, #e9ecef 50%, #1a202c 50%);
        }
          
        .system-preview .preview-main {
            background: linear-gradient(90deg, #ffffff 50%, #2d3748 50%);
        }
          
        .theme-info h5 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
        }
          
        .theme-info p {
            margin: 0;
            font-size: 14px;
            color: #6c757d;
        }
          
        .setting-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 0;
            border-bottom: 1px solid var(--border-color);
        }
          
        .setting-item:last-child {
            border-bottom: none;
        }
          
        .setting-info label {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 4px;
            display: block;
        }
          
        .setting-info p {
            margin: 0;
            font-size: 14px;
            color: #6c757d;
        }
          
        /* TOGGLE SWITCH */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
          
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
          
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.3s;
            border-radius: 24px;
        }
          
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }
          
        input:checked + .toggle-slider {
            background-color: #1b4332; /* Keep green when checked */
        }
          
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        .toggle-switch input[disabled] + .toggle-slider {
            background-color: #e0e0e0;
            cursor: not-allowed;
        }

        .toggle-switch input[disabled] + .toggle-slider:before {
            background-color: #bdbdbd;
        }
          
        /* NOTIFICATIONS SETTINGS */
        .notifications-settings {
            display: flex;
            flex-direction: column;
            gap: 32px;
        }
          
        .notification-category {
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border-color);
        }
          
        .notification-category:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
          
        .frequency-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
          
        .frequency-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: var(--card-bg);
            color: var(--text-color);
        }
          
        .frequency-option:hover {
            border-color: #1b4332; /* Keep green hover border */
            background-color: rgba(27, 67, 50, 0.05);
        }
          
        .frequency-option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #1b4332; /* Keep green radio button */
        }
          
        .frequency-info h5 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
        }
          
        .frequency-info p {
            margin: 0;
            font-size: 14px;
            color: #6c757d;
        }
          
        /* MODAL STYLES */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
          
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
          
        .modal-content {
            background: var(--card-bg);
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.9) translateY(20px);
            transition: transform 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
          
        .modal-overlay.active .modal-content {
            transform: scale(1) translateY(0);
        }
          
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 24px 0 24px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 24px;
        }
          
        .modal-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
        }
          
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            color: #6c757d;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
          
        .modal-close:hover {
            background-color: var(--content-bg);
            color: var(--text-color);
        }
          
        .modal-body {
            padding: 0 24px 24px 24px;
        }
          
        /* FORM STYLES WITHIN MODAL */
        .form-group {
            margin-bottom: 20px;
        }
          
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 14px;
        }
          
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
            background-color: var(--card-bg);
            color: var(--text-color);
        }
          
        .form-group input:focus {
            outline: none;
            border-color: #1b4332;
            box-shadow: 0 0 0 3px rgba(27, 67, 50, 0.1);
        }
          
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }
          
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }
          
        .cancel-btn, .save-btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
          
        .cancel-btn {
            background-color: #6c757d;
            color: white;
        }
          
        .cancel-btn:hover {
            background-color: #5a6268;
        }
          
        .save-btn {
            background-color: #1b4332;
            color: white;
        }
          
        .save-btn:hover {
            background-color: #0f2419;
        }
          
        /* NOTIFICATION TOAST */
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 4x 12px rgba(0, 0, 0, 0.15);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            min-width: 300px;
        }
          
        .notification-toast.show {
            transform: translateX(0);
        }
          
        .notification-toast.success {
            border-left: 4px solid #28a745;
        }
          
        .notification-toast.error {
            border-left: 4px solid #dc3545;
        }
          
        .toast-content {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }
          
        .toast-icon {
            font-size: 16px;
        }
          
        .notification-toast.success .toast-icon {
            color: #28a745;
        }
          
        .notification-toast.error .toast-icon {
            color: #dc3545;
        }
          
        .toast-message {
            font-size: 14px;
            color: var(--text-color);
        }
          
        .toast-close {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
          
        .toast-close:hover {
            background-color: var(--content-bg);
            color: var(--text-color);
        }
          
        /* CUSTOM SCROLLBAR */
        .settings-content::-webkit-scrollbar {
            width: 8px;
        }
          
        .settings-content::-webkit-scrollbar-track {
            background: var(--content-bg);
            border-radius: 4px;
        }
          
        .settings-content::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
            transition: background 0.3s ease;
        }
          
        .settings-content::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* COMPACT MODE */
        .compact-mode .settings-header { padding: 20px 30px; }
        .compact-mode .settings-header h1 { font-size: 28px; }
        .compact-mode .settings-header p { font-size: 14px; }
        .compact-mode .settings-section { margin: 15px 30px; }
        .compact-mode .section-header { padding: 15px 20px; }
        .compact-mode .section-title h3 { font-size: 16px; }
        .compact-mode .section-content.active { padding: 15px 20px; }
        .compact-mode .account-info-grid { gap: 16px; margin-bottom: 24px; }
        .compact-mode .info-item label { font-size: 12px; }
        .compact-mode .info-value, .compact-mode .edit-input { padding: 10px 14px; font-size: 14px; }
        .compact-mode .readonly-badge { font-size: 10px; padding: 1px 6px; }
        .compact-mode .section-actions { padding-top: 16px; }
        .compact-mode .save-changes-btn, .compact-mode .change-password-btn, .compact-mode .cancel-btn, .compact-mode .save-btn { padding: 10px 16px; font-size: 13px; }
        .compact-mode .theme-selection h4, .compact-mode .other-appearance-settings h4, .compact-mode .notification-category h4 { font-size: 16px; }
        .compact-mode .theme-selection p, .compact-mode .notification-category p { font-size: 13px; margin-bottom: 15px; }
        .compact-mode .theme-options { gap: 12px; }
        .compact-mode .theme-option { padding: 12px; }
        .compact-mode .theme-preview { height: 60px; margin-bottom: 8px; }
        .compact-mode .theme-info h5 { font-size: 14px; }
        .compact-mode .theme-info p { font-size: 12px; }
        .compact-mode .setting-item { padding: 12px 0; }
        .compact-mode .setting-info label { font-size: 14px; }
        .compact-mode .setting-info p { font-size: 12px; }
        .compact-mode .toggle-switch { width: 40px; height: 20px; }
        .compact-mode .toggle-slider:before { height: 14px; width: 14px; left: 3px; bottom: 3px; }
        .compact-mode input:checked + .toggle-slider:before { transform: translateX(20px); }
        .compact-mode .notification-category { padding-bottom: 16px; }
        .compact-mode .frequency-option { padding: 12px; font-size: 13px; }


        /* RESPONSIVE DESIGN */
        @media (max-width: 992px) {
            .sidebar { width: 240px; }
            .settings-main-content { margin-left: 240px; width: calc(100% - 240px); }
            .settings-header { padding: 25px 40px; }
            .settings-section { margin: 15px 40px; }
        }

        @media (max-width: 768px) {
            .dashboard-container { flex-direction: column; }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                overflow-y: visible; /* Allow scrolling for sidebar on small screens if content overflows */
            }
            .settings-main-content {
                margin-left: 0;
                width: 100%;
                height: auto;
                overflow-y: visible;
            }
            .calendar-sidebar { width: 100%; height: 300px; } /* This class seems unused but kept as is. */
            .settings-header { padding: 20px 30px; }
            .settings-header h1 { font-size: 28px; }
            .settings-section { margin: 15px 30px; }
            .account-info-grid { grid-template-columns: 1fr; gap: 16px; }
            .theme-options { grid-template-columns: 1fr; }
            .section-actions { flex-direction: column; }
            .form-actions { flex-direction: column; }
            .frequency-options { gap: 8px; }
        }

        @media (max-width: 480px) {
            .settings-main-content { width: 100%; }
            .settings-header { padding: 16px 20px; }
            .settings-header h1 { font-size: 24px; }
            .settings-section { margin: 12px 20px; }
            .section-header { padding: 16px 20px; }
            .section-content.active { padding: 20px; }
            .modal-content { width: 95%; margin: 20px; }
        }
    </style>
</head>
<body class="<?php
$bodyClasses = ['dashboard-body'];
if ($user['theme'] === 'dark') $bodyClasses[] = 'dark-theme';
if ($user['theme'] === 'light') $bodyClasses[] = 'light-theme';
if ($user['theme'] === 'system') $bodyClasses[] = 'system-theme';
if ($user['highContrast']) $bodyClasses[] = 'high-contrast';
if ($user['compactMode']) $bodyClasses[] = 'compact-mode';
echo implode(' ', $bodyClasses);
?>">
    <div class="dashboard-container" id="dashboardContainer">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="img/logo.png" alt="Cavite State University Logo" class="sidebar-logo">
            </div>
            <!-- Scrollable area for main navigation and post button -->
            <div class="sidebar-main-scrollable-area">
                <nav class="sidebar-nav">
                    <ul>
                        <li><a href="#"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="#"><i class="fas fa-sitemap"></i> Organizational Chart</a></li>
                        <li><a href="#"><i class="fas fa-bell"></i> Notifications</a></li>
                        <li><a href="#"><i class="fas fa-bookmark"></i> Bookmarks</a></li>
                        <li><a href="#"><i class="fas fa-user"></i> Profile</a></li>
                    </ul>
                </nav>
                <div class="post-button-container">
                    <button class="post-button">
                        <i class="fas fa-plus"></i> Post
                    </button>
                </div>
                <nav class="sidebar-nav">
                    <ul>
                        <li class="active"><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><a href="#"><i class="fas fa-question-circle"></i> Help</a></li>
                        <li><a href="#"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
                    </ul>
                </nav>
            </div>
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">
                        <img src="https://placehold.co/36x36/cccccc/000000?text=JD" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <h4>John Doe</h4>
                        <p>202312345</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="settings-main-content">
            <?php if ($successMsg || $errorMsg): // Show toast if any message exists from PHP ?>
                <div class="notification-toast show <?php echo $successMsg ? 'success' : 'error'; ?>" id="notificationToast">
                    <div class="toast-content">
                        <i class="toast-icon fas <?php echo $successMsg ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        <span class="toast-message"><?php echo $successMsg ?: $errorMsg; ?></span>
                    </div>
                    <button class="toast-close"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?>

            <header class="settings-header">
                <h1>Settings</h1>
                <p>Manage your account preferences and application settings</p>
            </header>
            <div class="settings-content">
                <!-- ACCOUNT INFORMATION SECTION -->
                <div class="settings-section">
                    <div class="section-header active" data-section="account">
                        <div class="section-title">
                            <i class="fas fa-user-circle"></i>
                            <h3>Account Information</h3>
                        </div>
                        <i class="fas fa-chevron-down section-arrow"></i>
                    </div>
                    <div class="section-content active" id="account-content">
                        <form method="post">
                            <div class="account-info-grid">
                                <div class="info-item">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="firstName" class="edit-input" value="<?php echo htmlspecialchars($user['firstName']); ?>">
                                </div>
                                <div class="info-item">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" class="edit-input" value="<?php echo htmlspecialchars($user['lastName']); ?>">
                                </div>
                                <div class="info-item">
                                    <label>Email Address</label>
                                    <div class="info-value">
                                        <span id="display-email"><?php echo htmlspecialchars($user['email']); ?></span>
                                        <span class="readonly-badge">Read Only</span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <label>Student Number</label>
                                    <div class="info-value">
                                        <span id="display-studentNumber"><?php echo htmlspecialchars($user['studentNumber']); ?></span>
                                        <span class="readonly-badge">Read Only</span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <label for="department">Department</label>
                                    <select id="department" name="department" class="edit-input">
                                        <option value="DIT" <?php if($user['department']==='DIT')echo 'selected';?>>Department of Information Technology (DIT)</option>
                                        <option value="DOM" <?php if($user['department']==='DOM')echo 'selected';?>>Department of Management (DOM)</option>
                                        <option value="DAS" <?php if($user['department']==='DAS')echo 'selected';?>>Department of Arts and Sciences (DAS)</option>
                                        <option value="TED" <?php if($user['department']==='TED')echo 'selected';?>>Teacher Education Department (TED)</option>
                                    </select>
                                </div>
                                <div class="info-item">
                                    <label for="dateOfBirth">Date of Birth</label>
                                    <select id="dateOfBirth" name="dateOfBirth" class="edit-input">
                                        <?php for($i=(int)date('Y');$i>=1900;$i--): ?>
                                            <option value="<?php echo $i; ?>" <?php if($user['dateOfBirth']==$i)echo 'selected';?>><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="section-actions">
                                <button class="save-changes-btn" name="saveAccountChanges" type="submit" id="saveAccountChanges">
                                    <i class="fas fa-save"></i>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                        <form method="post" style="margin-top:1rem;">
                            <div class="section-actions">
                                <button class="change-password-btn" name="showChangePassword" type="submit" id="changePasswordBtn">
                                    <i class="fas fa-key"></i>
                                    Change Password
                                </button>
                            </div>
                        </form>
                        <?php if($showPasswordModal): ?>
                        <div class="modal-overlay active" id="changePasswordModal">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2>Change Password</h2>
                                    <button class="modal-close" type="button" id="changePasswordModalClose"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" id="changePasswordForm">
                                        <div class="form-group">
                                            <label for="currentPassword">Current Password</label>
                                            <input type="password" id="currentPassword" name="currentPassword" required>
                                            <span class="error-message" id="currentPasswordError"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="newPassword">New Password</label>
                                            <input type="password" id="newPassword" name="newPassword" required>
                                            <span class="error-message" id="newPasswordError"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="confirmNewPassword">Confirm New Password</label>
                                            <input type="password" id="confirmNewPassword" name="confirmNewPassword" required>
                                            <span class="error-message" id="confirmNewPasswordError"></span>
                                        </div>
                                        <div class="form-actions">
                                            <button class="cancel-btn" name="cancelPasswordChange" type="submit">Cancel</button>
                                            <button class="save-btn" name="changePassword" type="submit">
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
                </div>
                <!-- APPEARANCE SECTION -->
                <div class="settings-section">
                    <div class="section-header active" data-section="appearance">
                        <div class="section-title">
                            <i class="fas fa-palette"></i>
                            <h3>Appearance</h3>
                        </div>
                        <i class="fas fa-chevron-down section-arrow"></i>
                    </div>
                    <div class="section-content active" id="appearance-content">
                        <form method="post">
                        <div class="appearance-options">
                            <div class="theme-selection">
                                <h4>Theme Preference</h4>
                                <p>Choose how the application looks to you</p>
                                <div class="theme-options">
                                    <label class="theme-option<?php if($user['theme']==='light')echo ' selected';?>">
                                        <div class="theme-preview light-preview">
                                            <div class="preview-header"></div>
                                            <div class="preview-content">
                                                <div class="preview-sidebar"></div>
                                                <div class="preview-main"></div>
                                            </div>
                                        </div>
                                        <div class="theme-info">
                                            <h5>Light Mode</h5>
                                            <p>Clean and bright interface</p>
                                        </div>
                                        <input type="radio" name="theme" value="light" <?php if($user['theme']==='light')echo 'checked';?>>
                                    </label>
                                    <label class="theme-option<?php if($user['theme']==='dark')echo ' selected';?>">
                                        <div class="theme-preview dark-preview">
                                            <div class="preview-header"></div>
                                            <div class="preview-content">
                                                <div class="preview-sidebar"></div>
                                                <div class="preview-main"></div>
                                            </div>
                                        </div>
                                        <div class="theme-info">
                                            <h5>Dark Mode</h5>
                                            <p>Easy on the eyes in low light</p>
                                        </div>
                                        <input type="radio" name="theme" value="dark" <?php if($user['theme']==='dark')echo 'checked';?>>
                                    </label>
                                    <label class="theme-option<?php if($user['theme']==='system')echo ' selected';?>">
                                        <div class="theme-preview system-preview">
                                            <div class="preview-header"></div>
                                            <div class="preview-content">
                                                <div class="preview-sidebar"></div>
                                                <div class="preview-main"></div>
                                            </div>
                                        </div>
                                        <div class="theme-info">
                                            <h5>System Default</h5>
                                            <p>Matches your device settings</p>
                                        </div>
                                        <input type="radio" name="theme" value="system" <?php if($user['theme']==='system')echo 'checked';?>>
                                    </label>
                                </div>
                            </div>
                            <div class="other-appearance-settings">
                                <h4>Display Options</h4>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label for="compactMode">Compact Mode</label>
                                        <p>Show more content by reducing spacing</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="compactMode" name="compactMode" <?php if($user['compactMode'])echo 'checked';?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label for="highContrast">High Contrast</label>
                                        <p>Increase contrast for better visibility</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="highContrast" name="highContrast" <?php if($user['highContrast'])echo 'checked';?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="section-actions">
                            <button class="save-changes-btn" name="saveAppearance" type="submit">
                                <i class="fas fa-save"></i>
                                Save Appearance
                            </button>
                        </div>
                        </form>
                    </div>
                </div>
                <!-- NOTIFICATIONS SECTION -->
                <div class="settings-section">
                    <div class="section-header active" data-section="notifications">
                        <div class="section-title">
                            <i class="fas fa-bell"></i>
                            <h3>Notifications</h3>
                        </div>
                        <i class="fas fa-chevron-down section-arrow"></i>
                    </div>
                    <div class="section-content active" id="notifications-content">
                        <form method="post">
                            <div class="notifications-settings">
                                <!-- Email Notifications -->
                                <div class="notification-category">
                                    <h4>Email Notifications</h4>
                                    <p>Control which notifications you receive via email.</p>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="emailAllAnnouncements">All Announcements</label>
                                            <p>Receive emails for all new announcements.</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="emailAllAnnouncements" name="emailAllAnnouncements" <?php if($user['emailAllAnnouncements']) echo 'checked'; ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="emailDepartmentOnly">Department-Specific Announcements</label>
                                            <p>Receive emails only for announcements relevant to your department.</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="emailDepartmentOnly" name="emailDepartmentOnly" <?php if($user['emailDepartmentOnly']) echo 'checked'; ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="emailMentions">Mentions and Replies</label>
                                            <p>Get an email when someone mentions you or replies to your post.</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="emailMentions" name="emailMentions" <?php if($user['emailMentions']) echo 'checked'; ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <!-- In-App & Browser Notifications -->
                                <div class="notification-category">
                                    <h4>In-App & Browser Notifications</h4>
                                    <p>Manage notifications within the application and your web browser.</p>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="browserNotifications">Browser Notifications</label>
                                            <p>Allow notifications to appear on your desktop, even when the browser is closed.</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="browserNotifications" name="browserNotifications" <?php if($user['browserNotifications']) echo 'checked'; ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="soundNotifications">Sound Notifications</label>
                                            <p>Play a sound when you receive a new notification.</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="soundNotifications" name="soundNotifications" <?php if($user['soundNotifications']) echo 'checked'; ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Notification Frequency -->
                                <div class="notification-category">
                                    <h4>Notification Frequency</h4>
                                    <p>How often would you like to receive summarized notifications?</p>
                                    <div class="frequency-options">
                                        <label class="frequency-option">
                                            <input type="radio" name="notificationFrequency" value="instant" <?php if($user['notificationFrequency'] === 'instant') echo 'checked'; ?>>
                                            <div class="frequency-info">
                                                <h5>Instant</h5>
                                                <p>Receive notifications as they happen.</p>
                                            </div>
                                        </label>
                                        <label class="frequency-option">
                                            <input type="radio" name="notificationFrequency" value="daily" <?php if($user['notificationFrequency'] === 'daily') echo 'checked'; ?>>
                                            <div class="frequency-info">
                                                <h5>Daily Digest</h5>
                                                <p>Get a summary of all notifications once a day.</p>
                                            </div>
                                        </label>
                                        <label class="frequency-option">
                                            <input type="radio" name="notificationFrequency" value="weekly" <?php if($user['notificationFrequency'] === 'weekly') echo 'checked'; ?>>
                                            <div class="frequency-info">
                                                <h5>Weekly Digest</h5>
                                                <p>Receive a weekly summary of all notifications.</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="section-actions">
                                <button class="save-changes-btn" name="saveNotifications" type="submit">
                                    <i class="fas fa-save"></i>
                                    Save Notifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- JAVASCRIPT -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Initial user data and preferences passed from PHP
            const initialUserData = {
                firstName: "<?php echo htmlspecialchars($user['firstName']); ?>",
                lastName: "<?php echo htmlspecialchars($user['lastName']); ?>",
                email: "<?php echo htmlspecialchars($user['email']); ?>",
                studentNumber: "<?php echo htmlspecialchars($user['studentNumber']); ?>",
                department: "<?php echo htmlspecialchars($user['department']); ?>",
                dateOfBirth: "<?php echo htmlspecialchars($user['dateOfBirth']); ?>",
                theme: "<?php echo htmlspecialchars($user['theme']); ?>",
                compactMode: <?php echo $user['compactMode'] ? 'true' : 'false'; ?>,
                highContrast: <?php echo $user['highContrast'] ? 'true' : 'false'; ?>,
                // New notification preferences for JS to know initial state
                emailAllAnnouncements: <?php echo $user['emailAllAnnouncements'] ? 'true' : 'false'; ?>,
                emailDepartmentOnly: <?php echo $user['emailDepartmentOnly'] ? 'true' : 'false'; ?>,
                emailMentions: <?php echo $user['emailMentions'] ? 'true' : 'false'; ?>,
                browserNotifications: <?php echo $user['browserNotifications'] ? 'true' : 'false'; ?>,
                soundNotifications: <?php echo $user['soundNotifications'] ? 'true' : 'false'; ?>,
                notificationFrequency: "<?php echo htmlspecialchars($user['notificationFrequency']); ?>"
            };

            initializeSettings();
            setupEventListeners(initialUserData);

            // Handle initial toast display based on PHP messages
            const successMsg = "<?php echo $successMsg; ?>";
            const errorMsg = "<?php echo $errorMsg; ?>";
            if (successMsg) {
                showNotification(successMsg, "success");
            } else if (errorMsg) {
                showNotification(errorMsg, "error");
            }
        });

        // Initial setup for settings (currently a placeholder as PHP handles initial DOM population)
        function initializeSettings() {
            // No specific JS initialization needed for these parts anymore.
        }

        // Event listener setup
        function setupEventListeners(initialUserData) {
            // Section toggle functionality
            const sectionHeaders = document.querySelectorAll(".settings-section .section-header");
            sectionHeaders.forEach((header) => {
                header.addEventListener("click", function () {
                    const content = this.nextElementSibling;
                    this.classList.toggle('active');
                    content.classList.toggle('active');
                    
                    const toggleIcon = this.querySelector('.section-arrow');
                    if (toggleIcon) {
                        if (this.classList.contains('active')) {
                            toggleIcon.style.transform = 'rotate(180deg)';
                        } else {
                            toggleIcon.style.transform = 'rotate(0deg)';
                        }
                    }
                });
                // Ensure initial arrow direction for sections active by default in HTML
                if (header.classList.contains('active')) {
                    const toggleIcon = header.querySelector('.section-arrow');
                    if (toggleIcon) {
                        toggleIcon.style.transform = 'rotate(180deg)';
                    }
                }
            });

            // Theme selection (client-side visual update only, submission handled by PHP form)
            const themeOptions = document.querySelectorAll('input[name="theme"]');
            themeOptions.forEach((option) => {
                option.addEventListener("change", function () {
                    if (this.checked) {
                        // Apply theme visually based on selected radio button
                        applyTheme(this.value, initialUserData.compactMode, initialUserData.highContrast);
                    }
                    // Update visual 'selected' class on the theme option label
                    document.querySelectorAll('.theme-option').forEach(opt => opt.classList.remove('selected'));
                    this.closest('.theme-option').classList.add('selected');
                });
            });

            // Toggle switches (client-side visual update only, submission handled by PHP form)
            const toggleSwitches = document.querySelectorAll(".toggle-switch input");
            toggleSwitches.forEach((toggle) => {
                // Attach change listener only for non-disabled toggles if needed for client-side state
                if (!toggle.disabled) {
                    toggle.addEventListener("change", function () {
                        const setting = this.id;
                        const value = this.checked;
                        handleToggleChange(setting, value);
                    });
                }
            });

            // Password Modal setup
            setupChangePasswordModal();
            const changePasswordBtn = document.getElementById("changePasswordBtn");
            if (changePasswordBtn) {
                changePasswordBtn.addEventListener("click", (e) => {
                    e.preventDefault(); // Prevent immediate form submission to let JS handle modal open
                    openChangePasswordModal();
                });
            }
            
            // Notification frequency radio buttons
            const notificationFrequencyRadios = document.querySelectorAll('input[name="notificationFrequency"]');
            notificationFrequencyRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // This is handled by PHP form submission, but if client-side validation/feedback is needed,
                    // it would go here. For now, it just ensures the 'checked' state is visually updated.
                });
            });


            // Initial application of theme and toggles based on PHP state
            applyTheme(initialUserData.theme, initialUserData.compactMode, initialUserData.highContrast);
        }

        /* MODAL FUNCTIONS */
        function setupChangePasswordModal() {
            const modal = document.getElementById("changePasswordModal");
            if (!modal) return;

            const closeBtn = document.getElementById("changePasswordModalClose");
            const form = document.getElementById("changePasswordForm");

            if (closeBtn) {
                closeBtn.addEventListener("click", closeChangePasswordModal);
            }
            
            // Click outside modal to close
            modal.addEventListener("click", (e) => {
                if (e.target === modal) {
                    closeChangePasswordModal();
                }
            });

            // Client-side validation for the password form before PHP submission
            if (form) {
                form.addEventListener("submit", handlePasswordChangeClient);
            }
        }

        function openChangePasswordModal() {
            const modal = document.getElementById("changePasswordModal");
            if (modal) {
                modal.classList.add("active");
                document.body.style.overflow = "hidden"; // Prevent background scroll
                clearPasswordErrors(); // Clear previous errors when opening
            }
        }

        function closeChangePasswordModal() {
            const modal = document.getElementById("changePasswordModal");
            if (modal) {
                modal.classList.remove("active");
                document.body.style.overflow = ""; // Restore background scroll
                const passwordForm = document.getElementById("changePasswordForm");
                if (passwordForm) {
                    passwordForm.reset(); // Clear form fields
                }
                clearPasswordErrors(); // Clear errors
            }
        }

        function handlePasswordChangeClient(e) {
            const currentPassword = document.getElementById("currentPassword").value;
            const newPassword = document.getElementById("newPassword").value;
            const confirmPassword = document.getElementById("confirmNewPassword").value;

            clearPasswordErrors();
            let hasErrors = false;

            if (!currentPassword) {
                showPasswordError("currentPassword", "Current password is required.");
                hasErrors = true;
            }
            if (!newPassword) {
                showPasswordError("newPassword", "New password is required.");
                hasErrors = true;
            } else if (newPassword.length < 6) {
                showPasswordError("newPassword", "Password must be at least 6 characters.");
                hasErrors = true;
            }
            if (!confirmPassword) {
                showPasswordError("confirmNewPassword", "Please confirm your new password.");
                hasErrors = true;
            } else if (newPassword !== confirmPassword) {
                showPasswordError("confirmNewPassword", "Passwords do not match.");
                hasErrors = true;
            }

            if (hasErrors) {
                e.preventDefault(); // Stop form submission if client-side errors exist
                showNotification("Please fix the password errors.", "error");
            }
        }

        function showPasswordError(fieldId, message) {
            const errorElement = document.getElementById(`${fieldId}Error`);
            if (errorElement) {
                errorElement.textContent = message;
                const inputElement = document.getElementById(fieldId);
                if (inputElement) {
                    inputElement.classList.add('error'); // Add error class for styling
                }
            }
        }

        function clearPasswordErrors() {
            const errorElements = document.querySelectorAll("#changePasswordModal .error-message");
            errorElements.forEach((element) => {
                element.textContent = "";
            });
            const inputElements = document.querySelectorAll("#changePasswordModal input[type='password']");
            inputElements.forEach((input) => {
                input.classList.remove('error'); // Remove error class
            });
        }

        /* THEME & APPEARANCE FUNCTIONS */
        function applyTheme(theme, isCompactMode, isHighContrast) {
            const body = document.body;
            const root = document.documentElement;

            body.classList.remove("light-theme", "dark-theme", "system-theme");

            if (theme === "dark") {
                body.classList.add("dark-theme");
            } else if (theme === "system") {
                body.classList.add("system-theme");
                if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
                    body.classList.add("dark-theme");
                } else {
                    body.classList.add("light-theme");
                }
            } else { // 'light' theme
                body.classList.add("light-theme");
            }

            if (isCompactMode) {
                body.classList.add("compact-mode");
            } else {
                body.classList.remove("compact-mode");
            }

            if (isHighContrast) {
                body.classList.add("high-contrast");
            } else {
                body.classList.remove("high-contrast");
            }
        }

        function handleToggleChange(setting, value) {
            switch (setting) {
                case "browserNotifications":
                    if (value && "Notification" in window) {
                        Notification.requestPermission();
                    }
                    break;
                case "compactMode":
                    const currentThemeRadio = document.querySelector('input[name="theme"]:checked');
                    const currentThemeValue = currentThemeRadio ? currentThemeRadio.value : 'system';
                    const currentHighContrast = document.getElementById("highContrast")?.checked;
                    applyTheme(currentThemeValue, value, currentHighContrast);
                    break;
                case "highContrast":
                    const currentThemeRadioForContrast = document.querySelector('input[name="theme"]:checked');
                    const currentThemeValueForContrast = currentThemeRadioForContrast ? currentThemeRadioForContrast.value : 'system';
                    const currentCompactMode = document.getElementById("compactMode")?.checked;
                    applyTheme(currentThemeValueForContrast, currentCompactMode, value);
                    break;
            }
        }

        // Listen for system theme changes
        if (window.matchMedia) {
            window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
                const themeRadio = document.querySelector('input[name="theme"]:checked');
                if (themeRadio && themeRadio.value === "system") {
                    const currentCompactMode = document.getElementById("compactMode")?.checked;
                    const currentHighContrast = document.getElementById("highContrast")?.checked;
                    applyTheme("system", currentCompactMode, currentHighContrast);
                }
            });
        }

        /* NOTIFICATION TOAST FUNCTIONS */
        function showNotification(message, type = "success") {
            const toast = document.getElementById("notificationToast");
            if (!toast) return;

            const icon = toast.querySelector(".toast-icon");
            const messageElement = toast.querySelector(".toast-message");
            const closeBtn = toast.querySelector(".toast-close");

            messageElement.textContent = message;
            toast.classList.remove("success", "error");
            toast.classList.add(type);

            if (type === "success") {
                icon.className = "toast-icon fas fa-check-circle";
            } else {
                icon.className = "toast-icon fas fa-times-circle";
            }

            toast.classList.add("show");

            setTimeout(() => {
                toast.classList.remove("show");
            }, 5000);

            if (closeBtn && !closeBtn.dataset.listenerAttached) {
                closeBtn.addEventListener("click", () => {
                    toast.classList.remove("show");
                });
                closeBtn.dataset.listenerAttached = 'true';
            }
        }

        /* UTILITY FUNCTIONS */
        function getDepartmentFullName(code) {
            const departments = {
                DIT: "Department of Information Technology (DIT)",
                DOM: "Department of Management (DOM)",
                DAS: "Department of Arts and Sciences (DAS)",
                TED: "Teacher Education Department (TED)",
            };
            return departments[code] || code;
        }
    </script>
</body>
</html>
