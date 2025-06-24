<?php
// Theme Manager - Global theme system for the entire application
// session_start();

// // Initialize default theme settings if not set
// if (!isset($_SESSION['theme'])) {
//     $_SESSION['theme'] = 'system';
// }
// if (!isset($_SESSION['compactMode'])) {
//     $_SESSION['compactMode'] = false;
// }
// if (!isset($_SESSION['highContrast'])) {
//     $_SESSION['highContrast'] = false;
// }

// Get current theme settings
function getThemeSettings() {
    return [
        'theme' => $_SESSION['theme'] ?? 'system',
        'compactMode' => $_SESSION['compactMode'] ?? false,
        'highContrast' => $_SESSION['highContrast'] ?? false
    ];
}

// Generate CSS classes for the body element
function getThemeClasses() {
    $settings = getThemeSettings();
    $classes = ['dashboard-body'];
    
    if ($settings['theme'] === 'dark') {
        $classes[] = 'dark-theme';
    } elseif ($settings['theme'] === 'light') {
        $classes[] = 'light-theme';
    } elseif ($settings['theme'] === 'system') {
        $classes[] = 'system-theme';
    }
    
    if ($settings['compactMode']) {
        $classes[] = 'compact-mode';
    }
    
    if ($settings['highContrast']) {
        $classes[] = 'high-contrast';
    }
    
    return implode(' ', $classes);
}

// Handle theme updates via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'updateTheme':
            if (isset($_POST['theme'])) {
                $_SESSION['theme'] = $_POST['theme'];
            }
            if (isset($_POST['compactMode'])) {
                $_SESSION['compactMode'] = $_POST['compactMode'] === 'true';
            }
            if (isset($_POST['highContrast'])) {
                $_SESSION['highContrast'] = $_POST['highContrast'] === 'true';
            }
            echo json_encode(['success' => true, 'message' => 'Theme updated successfully']);
            break;
            
        case 'getTheme':
            echo json_encode(getThemeSettings());
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?> 