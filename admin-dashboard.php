<?php
session_start();
// Apply user settings from session (theme, appearance, etc.)
$bodyClass = 'admin-body';
if (isset($_SESSION['admin_theme'])) {
    if ($_SESSION['admin_theme'] === 'dark') {
        $bodyClass .= ' dark-theme';
    } elseif ($_SESSION['admin_theme'] === 'light') {
        $bodyClass .= ' light-theme';
    } elseif ($_SESSION['admin_theme'] === 'system') {
        $bodyClass .= ' system-theme';
    }
}
if (isset($_SESSION['admin_compactMode']) && $_SESSION['admin_compactMode']) {
    $bodyClass .= ' compact-mode';
}
if (isset($_SESSION['admin_highContrast']) && $_SESSION['admin_highContrast']) {
    $bodyClass .= ' high-contrast';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CVSU Bulletin System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
/* Admin Portal Styles */
* {
    margin: 0;
    padding: 0;
    /* box-sizing: border-box; */
  }
  
  body.admin-body {
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f5f5;
    color: #333;
  }
  
  body.dark-theme {
    background-color: #1a1a1a;
    color: #fff;
  }
  
  body.light-theme {
    background-color: #f5f5f5;
    color: #333;
  }
  
  body.system-theme {
    /* Default to light, JS can override for system preference */
    background-color: #f5f5f5;
    color: #333;
  }
  
  body.compact-mode .admin-header {
    padding: 0.5rem 1rem;
  }
  
  body.high-contrast {
    background-color: #000;
    color: #fff;
  }
  
  .admin-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }
  
  /* Header Styles */
  .admin-header {
    background: linear-gradient(135deg, #1b4332, #2d5a3d);
    color: white;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }
  
  .header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .logo {
    width: 50px;
    height: 50px;
    border-radius: 8px;
  }
  
  .header-left h1 {
    font-size: 1.8rem;
    font-weight: 600;
  }
  
  .header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .search-container {
    position: relative;
  }
  
  .search-input {
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: none;
    border-radius: 25px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    width: 300px;
    font-size: 0.9rem;
  }
  
  .search-input::placeholder {
    color: rgba(255, 255, 255, 0.7);
  }
  
  .search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.7);
  }
  
  .admin-profile {
    position: relative;
    cursor: pointer;
  }
  
  .admin-avatar {
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
  }
  
  .dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    min-width: 180px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
  }
  
  .admin-profile:hover .dropdown-menu,
  .admin-profile.active .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }
  
  .dropdown-menu a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #333;
    text-decoration: none;
    transition: background-color 0.2s;
  }
  
  .dropdown-menu a:hover {
    background-color: #f8f9fa;
  }
  
  .dropdown-menu a:first-child {
    border-radius: 8px 8px 0 0;
  }
  
  .dropdown-menu a:last-child {
    border-radius: 0 0 8px 8px;
  }
  
  /* Main Content */
  .admin-main {
    flex: 1;
    padding: 2rem;
  }
  
  /* Dashboard Grid - Updated for 3 columns */
  .dashboard-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
  }
  
  .dashboard-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    min-height: 120px;
  }
  
  body.dark-theme .dashboard-card {
    background: #23272f;
    color: #fff;
  }
  body.dark-theme .dashboard-card .card-content h2,
  body.dark-theme .dashboard-card .card-content p,
  body.dark-theme .dashboard-card .card-icon {
    color: #fff;
  }
  body.dark-theme .dashboard-card.chart-card .card-content h3 {
    color: #fff !important;
  }
  
  .dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  }
  
  .card-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #1b4332, #2d5a3d);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
  }
  
  .card-content h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1b4332;
    margin-bottom: 0.5rem;
  }
  
  .card-content p {
    color: #666;
    font-size: 1.1rem;
  }
  
  .chart-card {
    flex-direction: column;
    align-items: stretch;
    gap: 0;
  }
  
  .chart-card .card-content {
    width: 100%;
    text-align: center;
  }
  
  .chart-card h3 {
    margin-bottom: 1rem;
    color: #1b4332;
    font-size: 1.3rem;
  }
  
  /* Table Styles */
  .table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
  }
  
  .admin-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .admin-table th {
    background: linear-gradient(135deg, #1b4332, #2d5a3d);
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
  }
  
  .admin-table td {
    padding: 1rem;
    border-bottom: 1px solid #eee;
  }
  
  .admin-table tr:hover {
    background-color: #f8f9fa;
  }
  
  /* Buttons */
  .btn-primary,
  .btn-secondary,
  .btn-success,
  .btn-danger,
  .btn-warning {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
  }
  
  .btn-primary {
    background: #1b4332;
    color: white;
  }
  
  .btn-primary:hover {
    background: #2d5a3d;
  }
  
  .btn-secondary {
    background: #6c757d;
    color: white;
  }
  
  .btn-secondary:hover {
    background: #5a6268;
  }
  
  .btn-success {
    background: #28a745;
    color: white;
  }
  
  .btn-success:hover {
    background: #218838;
  }
  
  .btn-danger {
    background: #dc3545;
    color: white;
  }
  
  .btn-danger:hover {
    background: #c82333;
  }
  
  .btn-warning {
    background: #ffc107;
    color: #212529;
  }
  
  .btn-warning:hover {
    background: #e0a800;
  }
  
  .btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #1b4332;
    text-decoration: none;
    font-weight: 500;
    padding: 0.75rem 0;
    transition: color 0.2s ease;
  }
  
  .btn-back:hover {
    color: #2d5a3d;
  }
  
  /* Modal Styles */
  .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  
  .modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 1;
  }
  
  .modal-content {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.9);
    transition: transform 0.3s ease;
  }
  
  .modal.active .modal-content {
    transform: scale(1);
  }
  
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
  }
  
  .modal-header h3 {
    color: #1b4332;
    font-size: 1.3rem;
  }
  
  .modal-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: #999;
    transition: color 0.2s ease;
  }
  
  .modal-close:hover {
    color: #333;
  }
  
  /* Form Styles */
  .form-group {
    margin-bottom: 1.5rem;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #333;
  }
  
  .form-group input,
  .form-group select,
  .form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: border-color 0.2s ease;
  }
  
  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: #1b4332;
  }
  
  .form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
  }
  
  .modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
  }
  
  /* Detail Groups */
  .detail-group {
    display: flex;
    margin-bottom: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
  }
  
  .detail-group label {
    font-weight: 600;
    color: #1b4332;
    min-width: 150px;
    margin-bottom: 0;
  }
  
  .detail-group span,
  .detail-group p {
    color: #666;
    flex: 1;
  }
  
  /* Reported Posts Styles */
  .reported-posts-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }
  
  .reported-post-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .reported-post-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  }
  
  .post-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
  }
  
  .post-avatar {
    width: 40px;
    height: 40px;
    background: #1b4332;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
  }
  
  .post-info h4 {
    color: #1b4332;
    margin-bottom: 0.25rem;
  }
  
  .post-info p {
    color: #666;
    font-size: 0.9rem;
  }
  
  .post-content {
    margin-bottom: 1rem;
  }
  
  .post-content h5 {
    color: #333;
    margin-bottom: 0.5rem;
  }
  
  .post-content p {
    color: #666;
    line-height: 1.6;
  }
  
  .report-info {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 0.75rem;
    margin-top: 1rem;
  }
  
  .report-info strong {
    color: #856404;
  }
  
  /* Help Tickets Styles */
  .help-tickets-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }
  
  .help-ticket-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .help-ticket-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  }
  
  .ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
  }
  
  .ticket-info h4 {
    color: #1b4332;
    margin-bottom: 0.5rem;
  }
  
  .ticket-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #666;
  }
  
  .priority-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
  }
  
  .priority-low {
    background: #d4edda;
    color: #155724;
  }
  
  .priority-medium {
    background: #fff3cd;
    color: #856404;
  }
  
  .priority-high {
    background: #f8d7da;
    color: #721c24;
  }
  
  .priority-urgent {
    background: #f5c6cb;
    color: #721c24;
  }
  
  /* Muted Words Styles */
  .muted-words-container {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }
  
  .words-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .word-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #1b4332;
  }
  
  .word-content h5 {
    color: #1b4332;
    margin-bottom: 0.25rem;
  }
  
  .word-content p {
    color: #666;
    font-size: 0.9rem;
  }
  
  .word-actions {
    display: flex;
    gap: 0.5rem;
  }
  
  /* Radio Options */
  .radio-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    cursor: pointer;
  }
  
  .radio-option input[type="radio"] {
    width: auto;
  }
  
  /* Suspension Options */
  .suspension-options {
    margin: 1.5rem 0;
  }
  
  /* Password Info */
  .password-info {
    background: #e7f3ff;
    border: 1px solid #b3d9ff;
    border-radius: 6px;
    padding: 1rem;
    margin-top: 1rem;
  }
  
  .password-info code {
    background: #f1f3f4;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: "Courier New", monospace;
  }
  
  /* Notification Toast */
  .notification-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 8px;
    padding: 1rem 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 1rem;
    z-index: 1001;
    transform: translateX(100%);
    transition: transform 0.3s ease;
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
  
  .notification-toast.info {
    border-left: 4px solid #17a2b8;
  }
  
  .toast-icon {
    font-size: 1.2rem;
  }
  
  .toast-icon.success {
    color: #28a745;
  }
  
  .toast-icon.error {
    color: #dc3545;
  }
  
  .toast-icon.info {
    color: #17a2b8;
  }
  
  .toast-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: #999;
    padding: 0;
    margin-left: auto;
  }
  
  .toast-close:hover {
    color: #333;
  }
  
  /* Floating Help Button */
  .floating-help-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #1b4332, #2d5a3d);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(27, 67, 50, 0.3);
    transition: all 0.3s ease;
    z-index: 1000;
  }
  
  .floating-help-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(27, 67, 50, 0.4);
  }
  
  .help-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    border: 2px solid white;
  }
  
  /* Responsive Design Updates */
  @media (max-width: 1024px) {
    .dashboard-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 1.5rem;
    }
  }
  
  /* Responsive Design */
  @media (max-width: 768px) {
    .admin-header {
      padding: 1rem;
      flex-direction: column;
      gap: 1rem;
    }
  
    .header-right {
      width: 100%;
      justify-content: space-between;
    }
  
    .search-input {
      width: 200px;
    }
  
    .dashboard-grid {
      grid-template-columns: 1fr;
      gap: 1rem;
    }
  
    .chart-card {
      grid-column: span 1;
    }
  
    .admin-main {
      padding: 1rem;
    }
  
    .modal-content {
      width: 95%;
      padding: 1.5rem;
    }
  
    .form-actions,
    .modal-actions {
      flex-direction: column;
    }
  
    .admin-table {
      font-size: 0.9rem;
    }
  
    .admin-table th,
    .admin-table td {
      padding: 0.75rem 0.5rem;
    }
  
    .floating-help-btn {
      width: 50px;
      height: 50px;
      font-size: 1.2rem;
      bottom: 20px;
      right: 20px;
    }
  
    .help-badge {
      width: 20px;
      height: 20px;
      font-size: 0.7rem;
    }
  }
  
  @media (max-width: 480px) {
    .header-left h1 {
      font-size: 1.4rem;
    }
  
    .search-input {
      width: 150px;
    }
  
    .card-content h2 {
      font-size: 2rem;
    }
  
    .admin-table {
      font-size: 0.8rem;
    }
  
    .admin-table th,
    .admin-table td {
      padding: 0.5rem 0.25rem;
    }
  
    .dashboard-card {
      padding: 1.5rem;
      flex-direction: column;
      text-align: center;
      gap: 1rem;
    }
  
    .card-content h2 {
      font-size: 2rem;
    }
  }
    </style>
</head>
<body class="<?php echo $bodyClass; ?>"></body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-left">
                <img src="img/logo.png" alt="CVSU Logo" class="logo">
                <h1>Admin Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="admin-profile" id="adminProfile">
                    <div class="admin-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="dropdown-menu" id="adminDropdown">
                        <a href="admin-settings.php"><i class="fas fa-cog"></i> Settings</a>
                        <a href="muted-words.php"><i class="fas fa-ban"></i> Muted Words</a>
                        <a href="#" id="adminLogout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <div class="dashboard-grid">
                <div class="dashboard-card" onclick="navigateTo('registered-users.php')">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-content">
                        <h2 id="registeredUsersCount">187</h2>
                        <p>Registered Users</p>
                    </div>
                </div>

                <div class="dashboard-card" onclick="openPendingRegistration()">
                    <div class="card-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="card-content">
                        <h2 id="pendingRegistrationCount">1,071</h2>
                        <p>Pending Registration</p>
                    </div>
                </div>

                <div class="dashboard-card" onclick="openSchoolAdmin()">
                    <div class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="card-content">
                        <h2 id="schoolAdminsCount">80</h2>
                        <p>School Admins</p>
                    </div>
                </div>

                <div class="dashboard-card" onclick="openAdminLogs()">
                    <div class="card-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="card-content">
                        <h2>Logs</h2>
                        <p>System Activity</p>
                    </div>
                </div>

                <div class="dashboard-card" onclick="openDepartmentModal()">
                    <div class="card-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="card-content">
                        <h2>Users</h2>
                        <p>Users per Department</p>
                    </div>
                </div>

                <div class="dashboard-card" onclick="navigateTo('reported-posts.php')">
                    <div class="card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="card-content">
                        <h2 id="reportedPostsCount">08</h2>
                        <p>Reported Posts</p>
                    </div>
                </div>
            </div>
        </main>

    <div class="floating-help-btn" onclick="navigateTo('admin-help.php')" title="Help Tickets">
        <i class="fas fa-life-ring"></i>
        <span class="help-badge" id="helpTicketsBadge">15</span>
    </div>

    <div class="modal" id="departmentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Department</h3>
                <span class="modal-close" id="departmentModalClose">&times;</span>
            </div>
            <form id="addDepartmentForm">
                <div class="form-group">
                    <label for="departmentName">Department Name</label>
                    <input type="text" id="departmentName" required placeholder="e.g., Department of Computer Science">
                </div>
                <div class="form-group">
                    <label for="departmentAcronym">Department Acronym</label>
                    <input type="text" id="departmentAcronym" required placeholder="e.g., DCS" maxlength="5">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeDepartmentModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add Department</button>
                </div>
            </form>
        </div>
    </div>

    <div class="notification-toast" id="notificationToast">
        <i class="toast-icon"></i>
        <span class="toast-message"></span>
        <button class="toast-close">&times;</button>
    </div>

    <script>
        function navigateTo(url) {
            window.location.href = url;
        }
        function openPendingRegistration() {
            window.location.href = 'pending-registration.php';
        }
        function openSchoolAdmin() {
            window.location.href = 'school-admins.php';
        }
        function openAdminLogs() {
            window.location.href = 'admin-logs.php';
        }

    </script>
</body>
</html>