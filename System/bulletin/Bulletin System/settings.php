<?php
include("user_session.php");
requireLogin();

// Use the current user data from user_session.php
$user = [
    'firstName' => $currentUser['firstName'],
    'lastName' => $currentUser['lastName'],
    'email' => $currentUser['email'],
    'studentNumber' => $currentUser['studentNumber'],
    'department' => $currentUser['department'],
    'dateOfBirth' => $currentUser['dateOfBirth'],
    'theme' => $_SESSION['theme'] ?? 'system',
    'compactMode' => $_SESSION['compactMode'] ?? false,
    'highContrast' => $_SESSION['highContrast'] ?? false,
];

$successMsg = '';
$errorMsg = '';
$showPasswordModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    if (isset($_POST['showChangePassword'])) {
        $showPasswordModal = true;
    }
    if (isset($_POST['changePassword'])) {
        // Password change logic here (validate and update password)
        // In a real application, you would add server-side validation here
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
            // Simulate successful password change
            $successMsg = 'Password changed successfully!';
            $showPasswordModal = false; // Close modal on success
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
}
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
/* Universal Box-Sizing and Font */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', sans-serif; /* Changed to Inter as per instructions */
}

/* Ensure html and body take full height for proper scrolling */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

/* Body and Dashboard Layout */
body {
    position: relative; /* Keep if needed for modals, toasts etc. */
    /* Default theme properties */
    background-color: var(--bg-color);
    color: var(--text-color);
    /* Removed min-height, display, align-items, justify-content, overflow from here */
}

/* Base theme variables */
:root {
    --bg-color: #f5f5f5;
    --text-color: #333333;
    --content-bg: #f8f9fa;
    --card-bg: #ffffff;
    --border-color: #e1e5e9;
    --accent-color: #007bff; /* Not directly used for green primary, but good to have */
}

/* Dark Theme */
.dark-theme {
    --bg-color: #1a1a1a;
    --text-color: #ffffff;
    --content-bg: #2d2d2d;
    --card-bg: #333333;
    --border-color: #444444;
    --accent-color: #4dabf7;
}

/* High Contrast Mode */
.high-contrast {
    --bg-color: #000000;
    --text-color: #ffffff;
    --content-bg: #000000;
    --card-bg: #000000;
    --border-color: #ffffff;
    --accent-color: #ffff00;
}

/* Dashboard Specific Body - This will now control the main layout and overall scrolling */
body.dashboard-body {
    display: flex; /* Make the body itself a flex container for sidebar and main content */
    height: 100%; /* Make it fill the viewport */
    overflow-y: auto; /* Allow the entire page to scroll if content overflows */
    background-color: var(--bg-color); /* Use theme variable */
}

.dashboard-container {
    /* No longer needs min-height: 100vh if body.dashboard-body is flex and 100% height */
    /* Flex properties moved to body.dashboard-body */
    display: flex; /* Kept for internal layout of sidebar and main content if body is not direct flex parent */
    width: 100%;
    /* min-height: 100vh; is often not needed if parent is flex and grows */
}

/* Left Sidebar Styles */
.sidebar {
    width: 280px; /* Adjusted width */
    background-color: #1b4332 !important; /* Force green background */
    color: white !important; /* Force white text */
    display: flex;
    flex-direction: column;
    height: 100%;
    position: fixed; /* Fixed sidebar */
    left: 0;
    top: 0;
    bottom: 0;
    /* Removed overflow-y: hidden; so internal scrolling can be managed */
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
}

/* Ensure theme changes don't affect sidebar in any mode */
.dark-theme .sidebar,
.light-theme .sidebar,
.system-theme .sidebar,
.high-contrast .sidebar {
    background-color: #1b4332 !important;
    color: white !important;
}

.sidebar-header {
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background-color: #1b4332 !important; /* Force green background */
    flex-shrink: 0; /* Prevent header from shrinking */
}

.sidebar-logo {
    width: 80px;
    height: 80px;
    object-fit: contain; /* Ensure logo scales properly */
}

/* New scrollable area for main sidebar content */
.sidebar-main-scrollable-area {
    flex-grow: 1; /* Allows this area to take up remaining vertical space */
    overflow-y: auto; /* Enables scrolling for this section */
    padding: 20px 0; /* Apply padding here instead of sidebar-nav */
    background-color: #1b4332 !important; /* Force green background */
    scrollbar-width: thin;
    scrollbar-color: #4a7c64 #1b4332; /* Custom scrollbar for better visibility */
}

.sidebar-main-scrollable-area::-webkit-scrollbar {
    width: 8px;
}

.sidebar-main-scrollable-area::-webkit-scrollbar-track {
    background: #1b4332;
    border-radius: 4px;
}

.sidebar-main-scrollable-area::-webkit-scrollbar-thumb {
    background: #4a7c64;
    border-radius: 4px;
    transition: background 0.3s ease;
}

.sidebar-main-scrollable-area::-webkit-scrollbar-thumb:hover {
    background: #6a9c84;
}


.sidebar-nav {
    /* flex-grow: 1; - Moved to sidebar-main-scrollable-area */
    padding: 0; /* Removed padding from here, moved to parent scrollable area */
    background-color: #1b4332 !important; /* Force green background */
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
    color: white !important; /* Force white text */
    text-decoration: none;
    transition: background-color 0.3s;
}

.sidebar-nav a i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.sidebar-nav li.active a {
    background-color: rgba(255, 255, 255, 0.1) !important;
    font-weight: bold;
}

.sidebar-nav a:hover {
    background-color: rgba(255, 255, 255, 0.05) !important;
}

.dark-theme .sidebar-nav a,
.light-theme .sidebar-nav a,
.system-theme .sidebar-nav a,
.high-contrast .sidebar-nav a {
    color: white !important;
}

.post-button-container {
    padding: 0 20px 20px;
    flex-shrink: 0; /* Prevent button container from shrinking */
}

.post-button {
    width: 100%;
    padding: 12px;
    background-color: white !important; /* Force white background */
    color: #1b4332 !important; /* Force green text */
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
    background-color: #f0f0f0 !important;
}

.dark-theme .post-button,
.light-theme .post-button,
.system-theme .post-button,
.high-contrast .post-button {
    background-color: white !important;
    color: #1b4332 !important;
}

.sidebar-footer {
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background-color: #1b4332 !important; /* Force green background */
    flex-shrink: 0; /* Prevent footer from shrinking */
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
    color: rgba(255, 255, 255, 0.8) !important; /* Force white text */
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
    color: white !important;
}

.user-profile {
    display: flex;
    align-items: center;
    padding: 10px;
    background-color: rgba(255, 255, 255, 0.05) !important; /* Force semi-transparent white */
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
    color: white !important; /* Force white text */
}

.user-info p {
    margin: 0;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6) !important; /* Force light white text */
}

.user-profile i {
    font-size: 12px;
}

/* Main Content Area - applied to settings-main-content */
.settings-main-content {
    flex-grow: 1;
    margin-left: 280px; /* Adjusted margin based on new sidebar width */
    overflow-y: auto; /* Changed from hidden to auto to enable scrolling */
    display: flex;
    flex-direction: column;
    background-color: var(--bg-color); /* Use theme variable */
    color: var(--text-color); /* Use theme variable */
}

/* Settings Header */
.settings-header {
    background-color: var(--card-bg); /* Use theme variable */
    border-bottom: 1px solid var(--border-color); /* Use theme variable */
    padding: 30px 50px;
    flex-shrink: 0;
}
  
.settings-header h1 {
    margin: 0 0 8px 0;
    font-size: 32px;
    font-weight: 700;
    color: var(--text-color); /* Use theme variable */
}
  
.settings-header p {
    margin: 0;
    font-size: 16px;
    color: #6c757d; /* Consistent grey text for description */
}
  
/* Settings Content */
.settings-content {
    flex: 1;
    overflow-y: auto;
    padding: 0;
    background-color: var(--content-bg); /* Use theme variable */
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 var(--content-bg);
}
  
.settings-content::-webkit-scrollbar {
    width: 8px;
}
.settings-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}
.settings-content::-webkit-scrollbar-track {
    background: var(--content-bg);
    border-radius: 4px;
}
  
/* Settings Sections */
.settings-section {
    background-color: var(--card-bg); /* Use theme variable */
    margin: 20px 50px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: box-shadow 0.3s ease;
}
  
.settings-section:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}
  
/* Section Header */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    cursor: pointer;
    border-bottom: 1px solid var(--border-color); /* Use theme variable */
    transition: background-color 0.3s ease;
}
  
.section-header:hover {
    background-color: var(--content-bg); /* Use theme variable */
}
  
.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
}
  
.section-title i {
    font-size: 20px;
    color: #1b4332; /* Green icon for titles */
}
  
.section-title h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--text-color); /* Use theme variable */
}
  
.section-arrow {
    font-size: 16px;
    color: #6c757d;
    transition: transform 0.3s ease;
}
  
.section-header.active .section-arrow {
    transform: rotate(180deg);
}
  
/* Section Content */
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
  
/* Account Information Styles */
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
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
  
.info-value {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background-color: var(--content-bg); /* Use theme variable */
    border-radius: 8px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}
  
.info-value:hover {
    background-color: #e9ecef; /* Fixed light hover for readability */
}
  
.info-value span {
    font-size: 16px;
    color: var(--text-color); /* Use theme variable */
    font-weight: 500;
}
  
.readonly-badge {
    background-color: #6c757d; /* Consistent grey for badge */
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
    border: 2px solid var(--border-color); /* Use theme variable */
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease;
    background-color: var(--card-bg); /* Use theme variable */
    color: var(--text-color); /* Use theme variable */
    -webkit-appearance: none; /* Remove default for selects */
    -moz-appearance: none;
    appearance: none;
}
  
.edit-input:focus {
    outline: none;
    border-color: #1b4332; /* Green focus border */
    box-shadow: 0 0 0 3px rgba(27, 67, 50, 0.1);
}

.info-item select {
    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%20viewBox%3D%220%200%20292.4%20292.4%22%3E%3Cpath%20fill%3D%22%236c757d%22%20d%3D%22M287%2C118.8L146.2%2C259.6L5.4%2C118.8c-2.8-2.8-4.3-6.6-4.3-10.8s1.5-8%2C4.3-10.8l8.5-8.5c2.8-2.8%2C6.6-4.3%2C10.8-4.3s8%2C1.5%2C10.8%2C4.3l111.9%2C111.9L257.4%2C88.7c2.8-2.8%2C6.6-4.3%2C10.8-4.3s8%2C1.5%2C10.8%2C4.3l8.5%2C8.5c2.8%2C2.8%2C4.3%2C6.6%2C4.3%2C10.8S289.8%2C116%2C287%2C118.8z%22%2F%3E%3C%2Fsvg%3E');
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 12px;
    padding-right: 40px; /* Adjust for arrow */
}
/* For dark theme select arrow */
.dark-theme .info-item select {
    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%20viewBox%3D%220%200%20292.4%20292.4%22%3E%3Cpath%20fill%3D%22%23ffffff%22%20d%3D%22M287%2C118.8L146.2%2C259.6L5.4%2C118.8c-2.8-2.8-4.3-6.6-4.3-10.8s1.5-8%2C4.3-10.8l8.5-8.5c2.8-2.8%2C6.6-4.3%2C10.8-4.3s8%2C1.5%2C10.8%2C4.3l111.9%2C111.9L257.4%2C88.7c2.8-2.8%2C6.6-4.3%2C10.8-4.3s8%2C1.5%2C10.8%2C4.3l8.5%2C8.5c2.8%2C2.8%2C4.3%2C6.6%2C4.3%2C10.8S289.8%2C116%2C287%2C118.8z%22%2F%3E%3C%2Fsvg%3E');
}

  
/* Section Actions */
.section-actions {
    display: flex;
    gap: 12px;
    padding-top: 24px;
    border-top: 1px solid var(--border-color); /* Use theme variable */
    justify-content: flex-end; /* Align buttons to the right */
}
  
.save-changes-btn,
.change-password-btn {
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
    background-color: #1b4332; /* Specific green for save button */
    color: white;
}
  
.save-changes-btn:hover:not(:disabled) {
    background-color: #0f2419;
    transform: translateY(-1px);
}
  
.save-changes-btn:disabled {
    background-color: #adb5bd;
    cursor: not-allowed;
    transform: none;
}
  
.change-password-btn {
    background-color: #6c757d; /* Grey for change password */
    color: white;
}
  
.change-password-btn:hover {
    background-color: #5a6268;
    transform: translateY(-1px);
}
  
/* Appearance Styles */
.appearance-options {
    display: flex;
    flex-direction: column;
    gap: 32px;
}
  
.theme-selection h4,
.other-appearance-settings h4,
.notification-category h4 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--text-color); /* Use theme variable */
}
  
.theme-selection p,
.notification-category p {
    margin: 0 0 20px 0;
    color: #6c757d; /* Consistent grey for description */
}
  
.theme-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}
  
.theme-option {
    position: relative;
    border: 2px solid var(--border-color); /* Use theme variable */
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: var(--card-bg); /* Use theme variable */
}
  
.theme-option:hover {
    border-color: #1b4332; /* Green hover border */
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(27, 67, 50, 0.1);
}

/* Selected theme option */
.theme-option.selected {
    border-color: #1b4332; /* Green border when selected */
    background-color: rgba(27, 67, 50, 0.05); /* Light green tint when selected */
    box-shadow: 0 4px 12px rgba(27, 67, 50, 0.1);
}
  
.theme-option input[type="radio"] {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 20px;
    height: 20px;
    accent-color: #1b4332; /* Green radio button */
}
  
.theme-preview {
    width: 100%;
    height: 80px;
    border-radius: 8px;
    margin-bottom: 12px;
    overflow: hidden;
    border: 1px solid #e5e7eb; /* Fixed light border for preview */
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
  
/* Dark theme preview */
.dark-preview .preview-header {
    background-color: #2d3748;
}
  
.dark-preview .preview-sidebar {
    background-color: #1a202c;
}
  
.dark-preview .preview-main {
    background-color: #2d3748;
}
  
/* System theme preview */
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
    color: var(--text-color); /* Use theme variable */
}
  
.theme-info p {
    margin: 0;
    font-size: 14px;
    color: #6c757d; /* Consistent grey text for description */
}
  
/* Setting Items */
.setting-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    border-bottom: 1px solid var(--border-color); /* Use theme variable */
}
  
.setting-item:last-child {
    border-bottom: none;
}
  
.setting-info label {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-color); /* Use theme variable */
    margin-bottom: 4px;
    display: block;
}
  
.setting-info p {
    margin: 0;
    font-size: 14px;
    color: #6c757d; /* Consistent grey text for description */
}
  
/* Toggle Switch */
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
    background-color: #1b4332; /* Green when checked */
}
  
input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

/* Disabled toggle switch */
.toggle-switch input[disabled] + .toggle-slider {
    background-color: #e0e0e0;
    cursor: not-allowed;
}

.toggle-switch input[disabled] + .toggle-slider:before {
    background-color: #bdbdbd;
}
  
/* Notifications Settings */
.notifications-settings {
    display: flex;
    flex-direction: column;
    gap: 32px;
}
  
.notification-category {
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border-color); /* Use theme variable */
}
  
.notification-category:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
  
/* Frequency Options */
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
    border: 2px solid var(--border-color); /* Use theme variable */
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: var(--card-bg); /* Use theme variable */
    color: var(--text-color); /* Use theme variable */
}
  
.frequency-option:hover {
    border-color: #1b4332; /* Green hover border */
    background-color: rgba(27, 67, 50, 0.05);
}
  
.frequency-option input[type="radio"] {
    width: 20px;
    height: 20px;
    accent-color: #1b4332; /* Green radio button */
}
  
.frequency-info h5 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--text-color); /* Use theme variable */
}
  
.frequency-info p {
    margin: 0;
    font-size: 14px;
    color: #6c757d; /* Consistent grey text for description */
}
  
/* Modal Styles */
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
    background: var(--card-bg); /* Use theme variable */
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
    border-bottom: 1px solid var(--border-color); /* Use theme variable */
    margin-bottom: 24px;
}
  
.modal-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--text-color); /* Use theme variable */
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
    background-color: var(--content-bg); /* Use theme variable */
    color: var(--text-color); /* Use theme variable */
}
  
.modal-body {
    padding: 0 24px 24px 24px;
}
  
/* Form Styles within Modal */
.form-group {
    margin-bottom: 20px;
}
  
.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: var(--text-color); /* Use theme variable */
    font-size: 14px;
}
  
.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border-color); /* Use theme variable */
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
    background-color: var(--card-bg); /* Use theme variable */
    color: var(--text-color); /* Use theme variable */
}
  
.form-group input:focus {
    outline: none;
    border-color: #1b4332; /* Green focus border */
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
  
.cancel-btn,
.save-btn {
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
  
/* Notification Toast */
.notification-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: var(--card-bg); /* Use theme variable */
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
    color: var(--text-color); /* Use theme variable */
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
    background-color: var(--content-bg); /* Use theme variable */
    color: var(--text-color); /* Use theme variable */
}
  
/* Custom Scrollbar */
.settings-content::-webkit-scrollbar {
    width: 8px;
}
  
.settings-content::-webkit-scrollbar-track {
    background: var(--content-bg); /* Use theme variable */
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

/* Compact Mode */
.compact-mode .settings-header {
    padding: 20px 30px;
}

.compact-mode .settings-header h1 {
    font-size: 28px;
}

.compact-mode .settings-header p {
    font-size: 14px;
}
 
.compact-mode .settings-section {
    margin: 15px 30px;
}

.compact-mode .section-header {
    padding: 15px 20px;
}

.compact-mode .section-title h3 {
    font-size: 16px;
}

.compact-mode .section-content.active {
    padding: 15px 20px;
}

.compact-mode .account-info-grid {
    gap: 16px;
    margin-bottom: 24px;
}

.compact-mode .info-item label {
    font-size: 12px;
}

.compact-mode .info-value,
.compact-mode .edit-input {
    padding: 10px 14px;
    font-size: 14px;
}

.compact-mode .readonly-badge {
    font-size: 10px;
    padding: 1px 6px;
}

.compact-mode .section-actions {
    padding-top: 16px;
}

.compact-mode .save-changes-btn,
.compact-mode .change-password-btn,
.compact-mode .cancel-btn,
.compact-mode .save-btn {
    padding: 10px 16px;
    font-size: 13px;
}

.compact-mode .theme-selection h4,
.compact-mode .other-appearance-settings h4,
.compact-mode .notification-category h4 {
    font-size: 16px;
}

.compact-mode .theme-selection p,
.compact-mode .notification-category p {
    font-size: 13px;
    margin-bottom: 15px;
}

.compact-mode .theme-options {
    gap: 12px;
}

.compact-mode .theme-option {
    padding: 12px;
}

.compact-mode .theme-preview {
    height: 60px;
    margin-bottom: 8px;
}

.compact-mode .theme-info h5 {
    font-size: 14px;
}

.compact-mode .theme-info p {
    font-size: 12px;
}

.compact-mode .setting-item {
    padding: 12px 0;
}

.compact-mode .setting-info label {
    font-size: 14px;
}

.compact-mode .setting-info p {
    font-size: 12px;
}

.compact-mode .toggle-switch {
    width: 40px;
    height: 20px;
}

.compact-mode .toggle-slider:before {
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
}

.compact-mode input:checked + .toggle-slider:before {
    transform: translateX(20px);
}

.compact-mode .notification-category {
    padding-bottom: 16px;
}

.compact-mode .frequency-option {
    padding: 12px;
    font-size: 13px;
}


/* Responsive Design */
@media (max-width: 992px) {
    .sidebar {
        width: 240px;
    }
    
    .settings-main-content { /* Adjusted for settings-main-content */
        margin-left: 240px;
        width: calc(100% - 240px);
    }
    
    .settings-header {
        padding: 20px 40px;
    }

    .settings-section {
        margin: 15px 40px;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        flex-direction: column;
    }
    
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        overflow-y: visible; /* Allow scrolling for sidebar on small screens if content overflows */
    }
    
    .settings-main-content { /* Adjusted for settings-main-content */
        margin-left: 0;
        width: 100%;
        height: auto; /* Allow height to adjust to content */
        overflow-y: visible; /* Allow scrolling for main content on small screens */
    }
    
    .calendar-sidebar {
        width: 100%;
        height: 300px;
    }

    .settings-header {
        padding: 20px 30px;
    }

    .settings-header h1 {
        font-size: 28px;
    }

    .settings-section {
        margin: 15px 30px;
    }
    
    .account-info-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .theme-options {
        grid-template-columns: 1fr;
    }
    
    .section-actions {
        flex-direction: column;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .frequency-options {
        gap: 8px;
    }
}

@media (max-width: 480px) {
    .settings-main-content {
        /* margin-left: 0; already set above for 768px */
        width: 100%;
    }
  
    .settings-header {
        padding: 16px 20px;
    }
  
    .settings-header h1 {
        font-size: 24px;
    }
  
    .settings-section {
        margin: 12px 20px;
    }
  
    .section-header {
        padding: 16px 20px;
    }
  
    .section-content.active {
        padding: 20px;
    }
  
    .modal-content {
        width: 95%;
        margin: 20px;
    }
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
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <!-- Updated logo source for visibility and proper centering -->
                <img src="img/logo.png" alt="Cavite State University Logo" class="sidebar-logo">
            </div>
            <!-- New scrollable area for sidebar content -->
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
                <!-- Notifications Section (static, always expanded) -->
                <div class="settings-section">
                    <div class="section-header active" data-section="notifications">
                        <div class="section-title">
                            <i class="fas fa-bell"></i>
                            <h3>Notifications</h3>
                        </div>
                        <i class="fas fa-chevron-down section-arrow"></i>
                    </div>
                    <div class="section-content active" id="notifications-content">
                        <div class="notifications-settings">
                            <div class="notification-category">
                                <h4>Email Notifications</h4>
                                <p>Choose what you want to be notified about via email</p>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label for="emailAllAnnouncements">All Announcements</label>
                                        <p>Receive emails for all new announcements</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="emailAllAnnouncements" disabled checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label for="emailDepartmentOnly">Department Announcements Only</label>
                                        <p>Only receive emails from your department</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="emailDepartmentOnly" disabled>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label for="emailMentions">Mentions and Replies</label>
                                        <p>Get notified when someone mentions you or replies to your posts</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="emailMentions" disabled checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="notification-category">
                                <h4>Push Notifications</h4>
                                <p>Manage browser and mobile notifications</p>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label for="browserNotifications">Browser Notifications</label>
                                        <p>Show notifications in your browser</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="browserNotifications" disabled checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label for="soundNotifications">Sound Notifications</label>
                                        <p>Play sound when receiving notifications</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="soundNotifications" disabled>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="notification-category">
                                <h4>Notification Frequency</h4>
                                <p>Control how often you receive notifications</p>
                                <div class="frequency-options">
                                    <label class="frequency-option">
                                        <input type="radio" name="frequency" value="instant" disabled> Instantly
                                    </label>
                                    <label class="frequency-option">
                                        <input type="radio" name="frequency" value="hourly" disabled> Hourly
                                    </label>
                                    <label class="frequency-option">
                                        <input type="radio" name="frequency" value="daily" disabled> Daily
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
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
                highContrast: <?php echo $user['highContrast'] ? 'true' : 'false'; ?>
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

        function initializeSettings() {
            // This function is mostly a placeholder now as PHP handles initial DOM population
            // and section states. The original JS `initializeSettings` had:
            // - `populateDateOfBirthDropdown()`: Replaced by PHP loop.
            // - Collapsing all sections: Replaced by initial `active` class in PHP for open sections.
            // No specific JS initialization needed for these parts anymore.
        }

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
                // Your current HTML has some toggles disabled (like email notifications, frequency)
                if (!toggle.disabled) {
                    toggle.addEventListener("change", function () {
                        const setting = this.id;
                        const value = this.checked;
                        // Handle specific toggle logic for immediate visual effect
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

            // Initial application of theme and toggles based on PHP state
            applyTheme(initialUserData.theme, initialUserData.compactMode, initialUserData.highContrast);
        }

        // Removed populateDateOfBirthDropdown - PHP generates these options.
        // Removed loadUserData and loadUserPreferences - PHP directly populates the HTML with session data.
        // Removed toggleEditMode, checkForChanges, saveAccountChanges - PHP forms handle these directly.
        // Removed isValidEmail - Can be kept for client-side input validation if desired, but not strictly necessary with PHP validation.
        // Removed getUserData, saveUserData, getUserPreferences, savePreference - These were localStorage functions, replaced by PHP session.

        function setupChangePasswordModal() {
            const modal = document.getElementById("changePasswordModal");
            if (!modal) return; // Exit if modal not present (i.e., PHP didn't render it initially)

            const closeBtn = document.getElementById("changePasswordModalClose");
            const form = document.getElementById("changePasswordForm");

            if (closeBtn) {
                closeBtn.addEventListener("click", closeChangePasswordModal);
            }
            
            // For the "Cancel" button within the modal, it's currently a submit button that PHP handles.
            // If you want it to close the modal client-side without submitting to PHP,
            // change type="submit" to type="button" and add an event listener here.
            // const cancelBtn = document.getElementById("cancelPasswordChange");
            // if (cancelBtn) { cancelBtn.addEventListener("click", closeChangePasswordModal); }

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
            // This client-side validation runs BEFORE PHP submission.
            // PHP will perform final validation for security.
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
            // If no client-side errors, the form will submit normally to PHP for server-side validation.
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

        function applyTheme(theme, isCompactMode, isHighContrast) {
            const body = document.body;
            const root = document.documentElement;

            // Remove existing theme classes (except dashboard-body, compact-mode, high-contrast which are controlled by toggles/PHP)
            body.classList.remove("light-theme", "dark-theme", "system-theme");

            // Apply new theme classes and CSS variables
            if (theme === "dark") {
                body.classList.add("dark-theme");
                root.style.setProperty("--bg-color", "#1a1a1a");
                root.style.setProperty("--text-color", "#ffffff");
                root.style.setProperty("--content-bg", "#2d2d2d");
                root.style.setProperty("--card-bg", "#333333");
                root.style.setProperty("--border-color", "#444444");
            } else if (theme === "system") {
                body.classList.add("system-theme");
                // Check system preference for 'system' theme
                if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
                    body.classList.add("dark-theme"); // Add dark-theme for system dark mode
                    root.style.setProperty("--bg-color", "#1a1a1a");
                    root.style.setProperty("--text-color", "#ffffff");
                    root.style.setProperty("--content-bg", "#2d2d2d");
                    root.style.setProperty("--card-bg", "#333333");
                    root.style.setProperty("--border-color", "#444444");
                } else {
                    body.classList.add("light-theme"); // Add light-theme for system light mode
                    root.style.setProperty("--bg-color", "#f5f5f5");
                    root.style.setProperty("--text-color", "#333333");
                    root.style.setProperty("--content-bg", "#f8f9fa");
                    root.style.setProperty("--card-bg", "#ffffff");
                    root.style.setProperty("--border-color", "#e1e5e9");
                }
            } else { // 'light' theme
                body.classList.add("light-theme");
                root.style.setProperty("--bg-color", "#f5f5f5");
                root.style.setProperty("--text-color", "#333333");
                root.style.setProperty("--content-bg", "#f8f9fa");
                root.style.setProperty("--card-bg", "#ffffff");
                root.style.setProperty("--border-color", "#e1e5e9");
            }

            // Apply compact mode based on parameter (from initial PHP state or toggle change)
            if (isCompactMode) {
                body.classList.add("compact-mode");
            } else {
                body.classList.remove("compact-mode");
            }

            // Apply high contrast based on parameter (from initial PHP state or toggle change)
            if (isHighContrast) {
                body.classList.add("high-contrast");
            } else {
                body.classList.remove("high-contrast");
            }
        }

        function handleToggleChange(setting, value) {
            // This function is for immediate client-side visual updates or browser API calls.
            // The actual state persistence is handled by the PHP form submission.
            switch (setting) {
                case "browserNotifications":
                    if (value && "Notification" in window) {
                        Notification.requestPermission();
                    }
                    break;
                case "compactMode":
                    // Re-apply theme to ensure CSS variables and class are correctly set
                    // This is important because compact-mode classes might affect other styles.
                    const currentThemeRadio = document.querySelector('input[name="theme"]:checked');
                    const currentThemeValue = currentThemeRadio ? currentThemeRadio.value : 'system';
                    const currentHighContrast = document.getElementById("highContrast")?.checked;
                    applyTheme(currentThemeValue, value, currentHighContrast);
                    break;
                case "highContrast":
                    // Re-apply theme for high contrast changes as well
                    const currentThemeRadioForContrast = document.querySelector('input[name="theme"]:checked');
                    const currentThemeValueForContrast = currentThemeRadioForContrast ? currentThemeRadioForContrast.value : 'system';
                    const currentCompactMode = document.getElementById("compactMode")?.checked;
                    applyTheme(currentThemeValueForContrast, currentCompactMode, value);
                    break;
                // emailAllAnnouncements, emailDepartmentOnly, emailMentions, soundNotifications, frequency
                // These are disabled in your current HTML, so no active JS logic needed for them.
            }
        }

        function showNotification(message, type = "success") {
            const toast = document.getElementById("notificationToast");
            if (!toast) return; // Ensure toast element exists

            const icon = toast.querySelector(".toast-icon");
            const messageElement = toast.querySelector(".toast-message");
            const closeBtn = toast.querySelector(".toast-close");

            // Set message
            messageElement.textContent = message;

            // Set type and icon
            toast.classList.remove("success", "error");
            toast.classList.add(type);

            if (type === "success") {
                icon.className = "toast-icon fas fa-check-circle";
            } else {
                icon.className = "toast-icon fas fa-times-circle";
            }

            // Show toast
            toast.classList.add("show");

            // Auto hide after 5 seconds
            setTimeout(() => {
                toast.classList.remove("show");
            }, 5000);

            // Attach close button listener once
            if (closeBtn && !closeBtn.dataset.listenerAttached) {
                closeBtn.addEventListener("click", () => {
                    toast.classList.remove("show");
                });
                closeBtn.dataset.listenerAttached = 'true';
            }
        }

        // Utility functions (getDepartmentFullName is kept as it might be useful client-side)
        function getDepartmentFullName(code) {
            const departments = {
                DIT: "Department of Information Technology (DIT)",
                DOM: "Department of Management (DOM)",
                DAS: "Department of Arts and Sciences (DAS)",
                TED: "Teacher Education Department (TED)",
            };
            return departments[code] || code;
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
    </script>
</body>
</html>