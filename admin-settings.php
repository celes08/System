<?php
// PHP SCRIPT START
session_start();

// Initialize user data and settings from session or defaults
$adminUser = [
    'firstName' => $_SESSION['admin_firstName'] ?? 'Admin',
    'lastName' => $_SESSION['admin_lastName'] ?? 'User',
    'email' => $_SESSION['admin_email'] ?? 'admin@cvsu.edu.ph',
    'adminId' => 'ADM001', // Read-only
    'role' => 'System Administrator', // Read-only
    'department' => $_SESSION['admin_department'] ?? 'Information Technology Services',
    'theme' => $_SESSION['admin_theme'] ?? 'light', // 'light', 'dark', 'system'
    'compactMode' => $_SESSION['admin_compactMode'] ?? false, // boolean
    'highContrast' => $_SESSION['admin_highContrast'] ?? false, // boolean
    // Notification settings
    'newRegistrations' => $_SESSION['admin_newRegistrations'] ?? true,
    'reportedContent' => $_SESSION['admin_reportedContent'] ?? true,
    'helpTickets' => $_SESSION['admin_helpTickets'] ?? true,
    'systemErrors' => $_SESSION['admin_systemErrors'] ?? true,
    'dailyActivity' => $_SESSION['admin_dailyActivity'] ?? false,
    'moderationReport' => $_SESSION['admin_moderationReport'] ?? false,
    'notificationMethod' => $_SESSION['admin_notificationMethod'] ?? 'email', // 'email', 'browser', 'both'
];

$successMsg = '';
$errorMsg = '';
$showPasswordModal = false;

// Handle POST requests for form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Account Information changes
    if (isset($_POST['saveAccountChanges'])) {
        $adminUser['firstName'] = $_SESSION['admin_firstName'] = $_POST['firstName'] ?? $adminUser['firstName'];
        $adminUser['lastName'] = $_SESSION['admin_lastName'] = $_POST['lastName'] ?? $adminUser['lastName'];
        $adminUser['email'] = $_SESSION['admin_email'] = $_POST['email'] ?? $adminUser['email'];
        $adminUser['department'] = $_SESSION['admin_department'] = $_POST['department'] ?? $adminUser['department'];
        $successMsg = 'Account information updated successfully!';
    }
    // Handle request to show Change Password modal (from JS submit via `showChangePassword` hidden field)
    if (isset($_POST['showChangePassword'])) {
        $showPasswordModal = true;
    }
    // Handle Change Password submission (from modal form)
    if (isset($_POST['changePassword'])) {
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmNewPassword = $_POST['confirmNewPassword'] ?? '';

        // Basic server-side validation (replace with actual authentication logic in production)
        if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
            $errorMsg = 'All password fields are required.';
            $showPasswordModal = true; // Keep modal open on error
        } elseif ($newPassword !== $confirmNewPassword) {
            $errorMsg = 'New passwords do not match.';
            $showPasswordModal = true; // Keep modal open on error
        } elseif (strlen($newPassword) < 6) {
            $errorMsg = 'New password must be at least 6 characters long.';
            $showPasswordModal = true; // Keep modal open on error
        } else {
            // Simulate successful password change (replace with actual hashing and update)
            $successMsg = 'Password changed successfully!';
            $showPasswordModal = false; // Close modal on success
        }
    }
    // Handle Cancel Password Change action (from modal form)
    if (isset($_POST['cancelPasswordChange'])) {
        $showPasswordModal = false;
    }

    // Handle Appearance settings changes
    if (isset($_POST['saveAppearance'])) {
        $adminUser['theme'] = $_SESSION['admin_theme'] = $_POST['theme'] ?? $adminUser['theme'];
        $adminUser['compactMode'] = $_SESSION['admin_compactMode'] = isset($_POST['compactMode']);
        $adminUser['highContrast'] = $_SESSION['admin_highContrast'] = isset($_POST['highContrast']);
        $successMsg = 'Appearance settings updated!';
    }

    // Handle Notifications settings changes
    if (isset($_POST['saveNotifications'])) {
        $adminUser['newRegistrations'] = $_SESSION['admin_newRegistrations'] = isset($_POST['newRegistrations']);
        $adminUser['reportedContent'] = $_SESSION['admin_reportedContent'] = isset($_POST['reportedContent']);
        $adminUser['helpTickets'] = $_SESSION['admin_helpTickets'] = isset($_POST['helpTickets']);
        $adminUser['systemErrors'] = $_SESSION['admin_systemErrors'] = isset($_POST['systemErrors']);
        $adminUser['dailyActivity'] = $_SESSION['admin_dailyActivity'] = isset($_POST['dailyActivity']);
        $adminUser['moderationReport'] = $_SESSION['admin_moderationReport'] = isset($_POST['moderationReport']);
        $adminUser['notificationMethod'] = $_SESSION['admin_notificationMethod'] = $_POST['notificationMethod'] ?? $adminUser['notificationMethod'];
        $successMsg = 'Notification settings updated successfully!';
    }
}

// Set body class based on session/user array
$bodyClasses = ['admin-body'];
if ($adminUser['theme'] === 'dark') $bodyClasses[] = 'dark-theme';
if ($adminUser['theme'] === 'light') $bodyClasses[] = 'light-theme';
if ($adminUser['theme'] === 'system') $bodyClasses[] = 'system-theme'; // Will be handled by JS for system preference
if ($adminUser['compactMode']) $bodyClasses[] = 'compact-mode';
if ($adminUser['highContrast']) $bodyClasses[] = 'high-contrast';
$bodyClass = implode(' ', $bodyClasses);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - CVSU Bulletin System</title>
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

            /* Admin-specific colors for light mode (default green header) */
            --admin-header-bg: #1b4332;
            --admin-header-text: white;
            --btn-primary-bg: #1b4332;
            --btn-primary-hover-bg: #0f2419;
            --btn-secondary-bg: #6c757d;
            --btn-secondary-hover-bg: #5a6268;
            --highlight-color: #d4edda; /* Light green for selected themes/active states */
            --input-focus-border: #1b4332;
        }

        .dark-theme {
            --bg-color: #1a1a1a;
            --text-color: #ffffff;
            --content-bg: #2d2d2d;
            --card-bg: #333333;
            --border-color: #444444;
            --accent-color: #4dabf7;

            --admin-header-bg: #1b4332; /* Remains green */
            --admin-header-text: #ffffff;
            --btn-primary-bg: #4dabf7; /* Brighter accent for dark mode primary */
            --btn-primary-hover-bg: #1e87f0;
            --btn-secondary-bg: #5a6268;
            --btn-secondary-hover-bg: #6c757d;
            --highlight-color: rgba(77, 171, 247, 0.1); /* Light blue tint for selected/active in dark mode */
            --input-focus-border: #4dabf7;
        }

        .high-contrast {
            --bg-color: #000000;
            --text-color: #ffffff;
            --content-bg: #000000;
            --card-bg: #000000;
            --border-color: #ffffff;
            --accent-color: #ffff00; /* Yellow for high contrast accents */

            --admin-header-bg: #000000;
            --admin-header-text: #ffffff;
            --btn-primary-bg: #ffffff;
            --btn-primary-hover-bg: #cccccc;
            --btn-primary-text: #000000; /* Primary button text for high contrast */
            --btn-secondary-bg: #333333;
            --btn-secondary-hover-bg: #666666;
            --highlight-color: #333333; /* Darker highlight for high contrast */
            --input-focus-border: #ffff00;
        }
        .high-contrast .btn-primary { color: var(--btn-primary-text, black); }


        /* ADMIN LAYOUT */
        body.admin-body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--bg-color);
        }

        .admin-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            flex-grow: 1;
        }

        /* HEADER STYLES */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background-color: var(--admin-header-bg);
            color: var(--admin-header-text);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
            border-bottom: 1px solid var(--border-color); /* Adjusted for theme */
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .admin-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: var(--admin-header-text);
        }

        .btn-back {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--admin-header-text);
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .btn-back:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* MAIN CONTENT STYLES */
        .admin-main {
            flex-grow: 1;
            padding: 30px;
            background-color: var(--bg-color);
            color: var(--text-color);
            overflow-y: auto;
        }

        .settings-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* SETTINGS SECTIONS */
        .settings-section {
            background-color: var(--card-bg);
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: box-shadow 0.3s ease;
            border: 1px solid var(--border-color); /* Added for better definition */
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
            background-color: var(--card-bg); /* Ensure consistent header background */
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
            color: var(--accent-color); /* Themed icon color */
        }

        .section-title h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
        }

        .section-arrow {
            font-size: 16px;
            color: var(--text-color); /* Themed arrow color */
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
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .info-item label {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
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
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .info-value:hover {
            background-color: var(--content-bg); /* No hover background for read-only values */
        }

        .info-value span {
            font-size: 16px;
            color: var(--text-color);
            font-weight: 500;
        }

        .readonly-badge {
            background-color: var(--btn-secondary-bg); /* Themed badge */
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
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: var(--card-bg);
            color: var(--text-color);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .edit-input:focus {
            outline: none;
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(var(--input-focus-border-rgb), 0.1); /* Needs RGB version of color */
        }

        /* Set a fallback for input-focus-border-rgb */
        :root { --input-focus-border-rgb: 27, 67, 50; } /* Default green */
        .dark-theme { --input-focus-border-rgb: 77, 171, 247; } /* Blue */
        .high-contrast { --input-focus-border-rgb: 255, 255, 0; } /* Yellow */


        .section-actions {
            display: flex;
            gap: 12px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
            justify-content: flex-end;
            margin-top: 24px;
        }

        .btn-primary, .btn-secondary {
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

        .btn-primary {
            background-color: var(--btn-primary-bg);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background-color: var(--btn-primary-hover-bg);
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            background-color: #adb5bd;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background-color: var(--btn-secondary-bg);
            color: white;
        }

        .btn-secondary:hover {
            background-color: var(--btn-secondary-hover-bg);
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
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .theme-option.selected {
            border-color: var(--accent-color);
            background-color: var(--highlight-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .theme-option input[type="radio"] {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 20px;
            height: 20px;
            accent-color: var(--accent-color);
        }

        .theme-preview {
            width: 100%;
            height: 80px;
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
            border: 1px solid var(--border-color);
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

        .dark-preview .preview-header { background-color: #2d3748; }
        .dark-preview .preview-sidebar { background-color: #1a202c; }
        .dark-preview .preview-main { background-color: #2d3748; }

        .system-preview .preview-header { background: linear-gradient(90deg, #f8f9fa 50%, #2d3748 50%); }
        .system-preview .preview-sidebar { background: linear-gradient(90deg, #e9ecef 50%, #1a202c 50%); }
        .system-preview .preview-main { background: linear-gradient(90deg, #ffffff 50%, #2d3748 50%); }

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
            background-color: var(--accent-color);
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
            border-color: var(--accent-color);
            background-color: var(--highlight-color);
        }
        .frequency-option input[type="radio"]:checked + .frequency-info {
            /* This is a hacky way to apply styling when radio is checked based on current HTML structure.
               Ideally, the label or parent should have the 'selected' class directly. */
            font-weight: bold; /* Example style for selected radio item */
        }


        .frequency-option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: var(--accent-color);
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
        .modal {
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

        .modal.active {
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
            padding: 24px; /* Added padding directly to content */
        }

        .modal.active .modal-content {
            transform: scale(1) translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px; /* Adjusted padding for better spacing */
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 24px;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px; /* Larger for better touch target */
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
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(var(--input-focus-border-rgb), 0.1);
        }
        .form-group .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }
        .form-group input.error {
            border-color: #dc3545;
        }


        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }

        /* NOTIFICATION TOAST */
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 10000;
            transform: translateX(120%); /* Start off-screen */
            transition: transform 0.4s ease-out;
            min-width: 280px;
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

        /* COMPACT MODE */
        .compact-mode .admin-header { padding: 15px 30px; }
        .compact-mode .admin-header h1 { font-size: 24px; }
        .compact-mode .admin-main { padding: 20px; }
        .compact-mode .settings-container { margin: 0 auto; }
        .compact-mode .settings-section { margin-bottom: 15px; }
        .compact-mode .section-header { padding: 15px 20px; }
        .compact-mode .section-title h3 { font-size: 16px; }
        .compact-mode .section-content.active { padding: 15px 20px; }
        .compact-mode .account-info-grid { gap: 16px; margin-bottom: 16px; }
        .compact-mode .info-item label { font-size: 12px; }
        .compact-mode .info-value, .compact-mode .edit-input { padding: 10px 14px; font-size: 14px; }
        .compact-mode .readonly-badge { font-size: 10px; padding: 1px 6px; }
        .compact-mode .section-actions { padding-top: 16px; margin-top: 16px; }
        .compact-mode .btn-primary, .compact-mode .btn-secondary { padding: 10px 16px; font-size: 13px; }
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
        .compact-mode .modal-content { padding: 20px; }
        .compact-mode .modal-header { padding-bottom: 15px; margin-bottom: 20px; }
        .compact-mode .modal-header h3 { font-size: 18px; }
        .compact-mode .form-group { margin-bottom: 15px; }
        .compact-mode .form-group label { font-size: 13px; }
        .compact-mode .form-group input { padding: 10px 14px; font-size: 13px; }


        /* RESPONSIVE DESIGN */
        @media (max-width: 992px) {
            .admin-header { padding: 15px 30px; }
            .admin-header h1 { font-size: 24px; }
            .admin-main { padding: 20px; }
        }

        @media (max-width: 768px) {
            .admin-header { flex-direction: column; text-align: center; gap: 15px; padding: 15px 20px; }
            .header-right { width: 100%; }
            .btn-back { width: 100%; justify-content: center; }
            .admin-main { padding: 15px; }
            .settings-container { margin: 0; }
            .settings-section { margin-bottom: 15px; }
            .account-info-grid { grid-template-columns: 1fr; gap: 16px; }
            .theme-options { grid-template-columns: 1fr; }
            .section-actions { flex-direction: column; }
            .modal-actions { flex-direction: column; gap: 8px; }
            .btn-primary, .btn-secondary { width: 100%; justify-content: center; }
        }

        @media (max-width: 480px) {
            .admin-header { padding: 10px 15px; }
            .admin-header h1 { font-size: 22px; }
            .admin-main { padding: 10px; }
            .settings-section { margin-bottom: 10px; }
            .section-header { padding: 12px 15px; }
            .section-content.active { padding: 15px; }
            .modal-content { width: 98%; margin: 10px; }
        }
    </style>
</head>
<body class="<?php echo $bodyClass; ?>">
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <!-- Using a placeholder image for logo -->
                <img src="https://placehold.co/50x50/1b4332/FFFFFF?text=CvSU" alt="CVSU Logo" class="logo">
                <h1>Admin Settings</h1>
            </div>
            <div class="header-right">
                <a href="admin-dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Notification Toast -->
            <div class="notification-toast <?php echo ($successMsg || $errorMsg) ? 'show ' . ($successMsg ? 'success' : 'error') : ''; ?>" id="notificationToast">
                <div class="toast-content">
                    <i class="toast-icon fas <?php echo $successMsg ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    <span class="toast-message"><?php echo htmlspecialchars($successMsg ?: $errorMsg); ?></span>
                </div>
                <button class="toast-close"><i class="fas fa-times"></i></button>
            </div>

            <div class="settings-container">
                <!-- Account Information Section -->
                <div class="settings-section">
                    <div class="section-header active" data-section="account">
                        <div class="section-title">
                            <i class="fas fa-user-shield"></i>
                            <h3>Admin Account Information</h3>
                        </div>
                        <i class="fas fa-chevron-down section-arrow"></i>
                    </div>
                    <div class="section-content active" id="account-content">
                        <form method="post" action="">
                            <div class="account-info-grid">
                                <div class="info-item">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="firstName" class="edit-input" value="<?php echo htmlspecialchars($adminUser['firstName']); ?>">
                                </div>
                                <div class="info-item">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" class="edit-input" value="<?php echo htmlspecialchars($adminUser['lastName']); ?>">
                                </div>
                                <div class="info-item">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" class="edit-input" value="<?php echo htmlspecialchars($adminUser['email']); ?>">
                                </div>
                                <div class="info-item">
                                    <label>Admin ID</label>
                                    <div class="info-value">
                                        <span><?php echo htmlspecialchars($adminUser['adminId']); ?></span>
                                        <span class="readonly-badge">Read Only</span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <label>Role</label>
                                    <div class="info-value">
                                        <span><?php echo htmlspecialchars($adminUser['role']); ?></span>
                                        <span class="readonly-badge">Read Only</span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <label for="department">Department</label>
                                    <input type="text" id="department" name="department" class="edit-input" value="<?php echo htmlspecialchars($adminUser['department']); ?>">
                                </div>
                            </div>
                            <div class="section-actions">
                                <button type="submit" name="saveAccountChanges" class="btn-primary" id="saveAccountChanges">
                                    <i class="fas fa-save"></i>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                        <!-- Form to trigger password modal via POST -->
                        <form method="post" action="" style="margin-top:1rem;">
                            <input type="hidden" name="showChangePassword" value="1">
                            <div class="section-actions">
                                <button type="submit" class="btn-secondary" id="changePasswordBtn">
                                    <i class="fas fa-key"></i>
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Appearance Section -->
                <div class="settings-section">
                    <div class="section-header active" data-section="appearance">
                        <div class="section-title">
                            <i class="fas fa-palette"></i>
                            <h3>Appearance</h3>
                        </div>
                        <i class="fas fa-chevron-down section-arrow"></i>
                    </div>
                    <div class="section-content active" id="appearance-content">
                        <form method="post" action="">
                            <div class="appearance-options">
                                <div class="theme-selection">
                                    <h4>Theme Preference</h4>
                                    <p>Choose how the admin portal looks</p>
                                    
                                    <div class="theme-options">
                                        <label class="theme-option <?php echo ($adminUser['theme'] === 'light' ? 'selected' : ''); ?>" for="theme-light">
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
                                            <input type="radio" name="theme" value="light" id="theme-light" <?php echo ($adminUser['theme'] === 'light' ? 'checked' : ''); ?>>
                                        </label>
                                        
                                        <label class="theme-option <?php echo ($adminUser['theme'] === 'dark' ? 'selected' : ''); ?>" for="theme-dark">
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
                                            <input type="radio" name="theme" value="dark" id="theme-dark" <?php echo ($adminUser['theme'] === 'dark' ? 'checked' : ''); ?>>
                                        </label>
                                        
                                        <label class="theme-option <?php echo ($adminUser['theme'] === 'system' ? 'selected' : ''); ?>" for="theme-system">
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
                                            <input type="radio" name="theme" value="system" id="theme-system" <?php echo ($adminUser['theme'] === 'system' ? 'checked' : ''); ?>>
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
                                            <input type="checkbox" id="compactMode" name="compactMode" <?php echo ($adminUser['compactMode'] ? 'checked' : ''); ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                    
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="highContrast">High Contrast</label>
                                            <p>Increase contrast for better visibility</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="highContrast" name="highContrast" <?php echo ($adminUser['highContrast'] ? 'checked' : ''); ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="section-actions">
                                <button type="submit" name="saveAppearance" class="btn-primary">
                                    <i class="fas fa-save"></i>
                                    Save Appearance
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Admin Notifications Section -->
                <div class="settings-section">
                    <div class="section-header active" data-section="notifications">
                        <div class="section-title">
                            <i class="fas fa-bell"></i>
                            <h3>Admin Notifications</h3>
                        </div>
                        <i class="fas fa-chevron-down section-arrow"></i>
                    </div>
                    <div class="section-content active" id="notifications-content">
                        <form method="post" action="">
                            <div class="notifications-settings">
                                <div class="notification-category">
                                    <h4>System Alerts</h4>
                                    <p>Get notified about important system events</p>
                                    
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="newRegistrations">New User Registrations</label>
                                            <p>Notify when new users register</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="newRegistrations" name="newRegistrations" <?php echo ($adminUser['newRegistrations'] ? 'checked' : ''); ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                    
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="reportedContent">Reported Content</label>
                                            <p>Immediate alerts for reported posts</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="reportedContent" name="reportedContent" <?php echo ($adminUser['reportedContent'] ? 'checked' : ''); ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                    
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="helpTickets">Help Tickets</label>
                                            <p>Notify when users submit help requests</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="helpTickets" name="helpTickets" <?php echo ($adminUser['helpTickets'] ? 'checked' : ''); ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                    
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="systemErrors">System Errors</label>
                                            <p>Critical system error notifications</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="systemErrors" name="systemErrors" <?php echo ($adminUser['systemErrors'] ? 'checked' : ''); ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="notification-category">
                                    <h4>Daily Reports</h4>
                                    <p>Automated daily summary reports</p>
                                    
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="dailyActivity">User Activity Summary</label>
                                            <p>Daily user engagement statistics</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="dailyActivity" name="dailyActivity" <?php echo ($adminUser['dailyActivity'] ? 'checked' : ''); ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                    
                                    <div class="setting-item">
                                        <div class="setting-info">
                                            <label for="moderationReport">Content Moderation Report</label>
                                            <p>Summary of moderated content</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="moderationReport" name="moderationReport" <?php echo ($adminUser['moderationReport'] ? 'checked' : ''); ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="notification-category">
                                    <h4>Notification Method</h4>
                                    <p>How you want to receive notifications</p>
                                    
                                    <div class="frequency-options">
                                        <label class="frequency-option">
                                            <input type="radio" name="notificationMethod" value="email" <?php echo ($adminUser['notificationMethod'] === 'email' ? 'checked' : ''); ?>>
                                            <div class="frequency-info">
                                                <h5>Email Only</h5>
                                                <p>Receive notifications via email</p>
                                            </div>
                                        </label>
                                        
                                        <label class="frequency-option">
                                            <input type="radio" name="notificationMethod" value="browser" <?php echo ($adminUser['notificationMethod'] === 'browser' ? 'checked' : ''); ?>>
                                            <div class="frequency-info">
                                                <h5>Browser Only</h5>
                                                <p>Show notifications in browser</p>
                                            </div>
                                        </label>
                                        
                                        <label class="frequency-option">
                                            <input type="radio" name="notificationMethod" value="both" <?php echo ($adminUser['notificationMethod'] === 'both' ? 'checked' : ''); ?>>
                                            <div class="frequency-info">
                                                <h5>Both</h5>
                                                <p>Email and browser notifications</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="section-actions">
                                <button type="submit" name="saveNotifications" class="btn-primary">
                                    <i class="fas fa-save"></i>
                                    Save Notifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Section -->
                <div class="settings-section">
                    <div class="section-header active" data-section="security">
                        <div class="section-title">
                            <i class="fas fa-shield-alt"></i>
                            <h3>Security Settings</h3>
                        </div>
                        <i class="fas fa-chevron-down section-arrow"></i>
                    </div>
                    <div class="section-content active" id="security-content">
                        <div class="security-settings">
                            <div class="setting-item">
                                <div class="setting-info">
                                    <label>Two-Factor Authentication</label>
                                    <p>Add an extra layer of security to your account</p>
                                </div>
                                <button class="btn-primary" id="setup2FA">
                                    <i class="fas fa-mobile-alt"></i>
                                    Setup 2FA
                                </button>
                            </div>
                            
                            <div class="setting-item">
                                <div class="setting-info">
                                    <label for="sessionTimeout">Session Timeout</label>
                                    <p>Automatically log out after inactivity</p>
                                </div>
                                <select id="sessionTimeout" class="edit-input" name="sessionTimeout">
                                    <option value="30">30 minutes</option>
                                    <option value="60" <?php echo (isset($_SESSION['admin_sessionTimeout']) && $_SESSION['admin_sessionTimeout'] == 60) ? 'selected' : ''; ?>>1 hour</option>
                                    <option value="120">2 hours</option>
                                    <option value="240">4 hours</option>
                                    <option value="0">Never</option>
                                </select>
                            </div>
                            
                            <div class="setting-item">
                                <div class="setting-info">
                                    <label for="loginAlerts">Login Alerts</label>
                                    <p>Get notified of new login attempts</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="loginAlerts" name="loginAlerts" <?php echo (isset($_SESSION['admin_loginAlerts']) && $_SESSION['admin_loginAlerts']) ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="section-actions">
                             <button type="submit" name="saveSecurity" class="btn-primary">
                                <i class="fas fa-save"></i>
                                Save Security
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Change Password Modal -->
    <div class="modal <?php echo $showPasswordModal ? 'active' : ''; ?>" id="changePasswordModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Change Password</h3>
                <button type="button" class="modal-close" id="changePasswordModalClose"><i class="fas fa-times"></i></button>
            </div>
            <form id="changePasswordForm" method="post" action="">
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
                <div class="modal-actions">
                    <button type="submit" name="cancelPasswordChange" class="btn-secondary">Cancel</button>
                    <button type="submit" name="changePassword" class="btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initial user data from PHP (for JS to work with consistent values)
        const initialAdminUserData = {
            theme: "<?php echo htmlspecialchars($adminUser['theme']); ?>",
            compactMode: <?php echo $adminUser['compactMode'] ? 'true' : 'false'; ?>,
            highContrast: <?php echo $adminUser['highContrast'] ? 'true' : 'false'; ?>,
            notificationMethod: "<?php echo htmlspecialchars($adminUser['notificationMethod']); ?>",
            loginAlerts: <?php echo (isset($adminUser['loginAlerts']) && $adminUser['loginAlerts']) ? 'true' : 'false'; ?>, // Added for security section
            sessionTimeout: "<?php echo htmlspecialchars($adminUser['sessionTimeout'] ?? '60'); ?>" // Added for security section
        };

        // --- Functions for common UI interactions ---

        /**
         * Shows a notification toast message.
         * @param {string} message - The message to display.
         * @param {string} type - 'success' or 'error'.
         */
        function showNotification(message, type = "success") {
            const toast = document.getElementById("notificationToast");
            if (!toast) return;

            const icon = toast.querySelector(".toast-icon");
            const messageElement = toast.querySelector(".toast-message");
            const closeBtn = toast.querySelector(".toast-close");

            // Update content and class
            messageElement.textContent = message;
            toast.classList.remove("success", "error");
            toast.classList.add(type);

            // Set icon based on type
            if (type === "success") {
                icon.className = "toast-icon fas fa-check-circle";
            } else {
                icon.className = "toast-icon fas fa-times-circle";
            }

            // Show the toast
            toast.classList.add("show");

            // Hide after 5 seconds
            setTimeout(() => {
                toast.classList.remove("show");
            }, 5000);

            // Add close button functionality if not already added
            if (closeBtn && !closeBtn.dataset.listenerAttached) {
                closeBtn.addEventListener("click", () => {
                    toast.classList.remove("show");
                });
                closeBtn.dataset.listenerAttached = 'true';
            }
        }

        /**
         * Applies the selected theme classes to the body.
         * Handles light, dark, and system themes, plus compact mode and high contrast.
         * @param {string} theme - 'light', 'dark', or 'system'.
         * @param {boolean} isCompactMode - True if compact mode is active.
         * @param {boolean} isHighContrast - True if high contrast is active.
         */
        function applyTheme(theme, isCompactMode, isHighContrast) {
            const body = document.body;
            body.classList.remove("light-theme", "dark-theme", "system-theme", "compact-mode", "high-contrast");

            // Apply base theme
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

            // Apply display options
            if (isCompactMode) {
                body.classList.add("compact-mode");
            }
            if (isHighContrast) {
                body.classList.add("high-contrast");
            }

            // Update the 'selected' class on theme options
            document.querySelectorAll('.theme-option').forEach(opt => opt.classList.remove('selected'));
            const selectedThemeOption = document.querySelector(`.theme-option input[value="${theme}"]`);
            if (selectedThemeOption) {
                selectedThemeOption.closest('.theme-option').classList.add('selected');
            }
        }

        /**
         * Toggles the active state of a settings section.
         */
        function toggleSection() {
            const content = this.nextElementSibling;
            const isActive = this.classList.toggle('active');
            content.classList.toggle('active');
            
            const toggleIcon = this.querySelector('.section-arrow');
            if (toggleIcon) {
                toggleIcon.style.transform = isActive ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }

        /**
         * Handles client-side validation for the change password modal.
         * @param {Event} e - The form submission event.
         */
        function handlePasswordChangeClient(e) {
            const currentPassword = document.getElementById("currentPassword").value;
            const newPassword = document.getElementById("newPassword").value;
            const confirmNewPassword = document.getElementById("confirmNewPassword").value;

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
                showPasswordError("newPassword", "Password must be at least 6 characters long.");
                hasErrors = true;
            }
            if (!confirmNewPassword) {
                showPasswordError("confirmNewPassword", "Please confirm your new password.");
                hasErrors = true;
            } else if (newPassword !== confirmNewPassword) {
                showPasswordError("confirmNewPassword", "New passwords do not match.");
                hasErrors = true;
            }

            if (hasErrors) {
                e.preventDefault(); // Stop form submission if client-side errors exist
                showNotification("Please fix the password errors.", "error");
            }
        }

        /**
         * Displays an error message for a password field.
         * @param {string} fieldId - The ID of the input field.
         * @param {string} message - The error message.
         */
        function showPasswordError(fieldId, message) {
            const errorElement = document.getElementById(`${fieldId}Error`);
            if (errorElement) {
                errorElement.textContent = message;
                const inputElement = document.getElementById(fieldId);
                if (inputElement) {
                    inputElement.classList.add('error');
                }
            }
        }

        /**
         * Clears all password error messages and styling.
         */
        function clearPasswordErrors() {
            document.querySelectorAll("#changePasswordModal .error-message").forEach(element => element.textContent = "");
            document.querySelectorAll("#changePasswordModal input[type='password']").forEach(input => input.classList.remove('error'));
        }

        /**
         * Opens the change password modal.
         */
        function openChangePasswordModal() {
            const modal = document.getElementById("changePasswordModal");
            if (modal) {
                modal.classList.add("active");
                document.body.style.overflow = "hidden";
                clearPasswordErrors();
            }
        }

        /**
         * Closes the change password modal.
         */
        function closeChangePasswordModal() {
            const modal = document.getElementById("changePasswordModal");
            if (modal) {
                modal.classList.remove("active");
                document.body.style.overflow = "";
                const passwordForm = document.getElementById("changePasswordForm");
                if (passwordForm) {
                    passwordForm.reset();
                }
                clearPasswordErrors();
            }
        }

        // --- Main Initialization Logic ---

        // Initialize theme and display settings based on PHP variables
        applyTheme(initialAdminUserData.theme, initialAdminUserData.compactMode, initialAdminUserData.highContrast);

        // System theme change listener (for real-time updates if 'system' theme is active)
        if (window.matchMedia) {
            window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
                const themeRadio = document.getElementById('theme-system');
                if (themeRadio && themeRadio.checked) {
                    applyTheme("system", initialAdminUserData.compactMode, initialAdminUserData.highContrast);
                }
            });
        }

        // Section toggle functionality
        document.querySelectorAll(".settings-section .section-header").forEach(header => {
            header.addEventListener("click", toggleSection);
            // Ensure initial arrow direction for sections active by default in HTML
            if (header.classList.contains('active')) {
                const toggleIcon = header.querySelector('.section-arrow');
                if (toggleIcon) {
                    toggleIcon.style.transform = 'rotate(180deg)';
                }
            }
        });

        // Theme radio button change listener (for immediate visual update)
        document.querySelectorAll('input[name="theme"]').forEach(radio => {
            radio.addEventListener('change', function() {
                applyTheme(this.value, document.getElementById('compactMode').checked, document.getElementById('highContrast').checked);
            });
        });

        // Compact Mode and High Contrast toggle listeners (for immediate visual update)
        document.getElementById('compactMode').addEventListener('change', function() {
            applyTheme(document.querySelector('input[name="theme"]:checked').value, this.checked, document.getElementById('highContrast').checked);
        });
        document.getElementById('highContrast').addEventListener('change', function() {
            applyTheme(document.querySelector('input[name="theme"]:checked').value, document.getElementById('compactMode').checked, this.checked);
        });

        // Change Password Modal event listeners
        const changePasswordModal = document.getElementById("changePasswordModal");
        const changePasswordModalCloseBtn = document.getElementById("changePasswordModalClose");
        const changePasswordForm = document.getElementById("changePasswordForm");

        // Open modal only if PHP indicated it should be open (due to validation errors on submit)
        if (changePasswordModal.classList.contains('active')) {
             document.body.style.overflow = "hidden";
        }

        if (changePasswordModalCloseBtn) {
            changePasswordModalCloseBtn.addEventListener('click', closeChangePasswordModal);
        }
        if (changePasswordModal) {
            // Close modal when clicking outside content
            changePasswordModal.addEventListener('click', function(e) {
                if (e.target === changePasswordModal) {
                    closeChangePasswordModal();
                }
            });
        }
        if (changePasswordForm) {
            changePasswordForm.addEventListener('submit', handlePasswordChangeClient);
        }

        // Handle initial toast message display (from PHP on page load)
        const initialToast = document.getElementById("notificationToast");
        if (initialToast.classList.contains('show')) {
            // Re-attach close listener if it's rendered by PHP
            const closeBtn = initialToast.querySelector(".toast-close");
            if (closeBtn && !closeBtn.dataset.listenerAttached) {
                closeBtn.addEventListener("click", () => {
                    initialToast.classList.remove("show");
                });
                closeBtn.dataset.listenerAttached = 'true';
            }
            // Set timeout for the initial toast too
            setTimeout(() => {
                initialToast.classList.remove("show");
            }, 5000);
        }

        // Security section - Session Timeout (PHP handling on form submit)
        // No client-side JS needed beyond initial value setting from PHP
        const sessionTimeoutSelect = document.getElementById('sessionTimeout');
        if (sessionTimeoutSelect) {
            sessionTimeoutSelect.value = initialAdminUserData.sessionTimeout;
        }

        // Security section - Login Alerts toggle
        const loginAlertsToggle = document.getElementById('loginAlerts');
        if (loginAlertsToggle) {
             // This toggle will be submitted with the form; no real-time JS effect needed besides initial state
        }

    });
    </script>
</body>
</html>
