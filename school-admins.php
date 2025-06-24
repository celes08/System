<?php
// PHP SCRIPT START
session_start();
include_once "theme-manager.php";

// Use the same session variable names as admin-settings.php
$theme = $_SESSION['admin_theme'] ?? 'system';
$compactMode = $_SESSION['admin_compactMode'] ?? false;
$highContrast = $_SESSION['admin_highContrast'] ?? false;

// Simulated user data (replace with DB logic in production for persistent storage)
$user = [
    'firstName' => $_SESSION['firstName'] ?? 'John',
    'lastName' => $_SESSION['lastName'] ?? 'Doe',
    'email' => 'john.doe@cvsu.edu.ph',
    'studentNumber' => '202312345',
    'department' => $_SESSION['department'] ?? 'DIT',
    'dateOfBirth' => $_SESSION['dateOfBirth'] ?? '1995',
];
// PHP SCRIPT END
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Admins - Admin Portal</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?php echo getThemeClasses(); ?>">
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <img src="img/logo.png" alt="CVSU Logo" class="logo">
                <h1>School Admins</h1>
            </div>
            <div class="header-right">
                <div class="search-container">
                    <input type="text" id="adminSearch" placeholder="Search admins..." class="search-input">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <button class="btn-primary" onclick="openCreateAdminModal()">
                    <i class="fas fa-plus"></i> Create Admin
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminsTableBody">
                        <!-- Admins will be populated here -->
                    </tbody>
                </table>
            </div>

            <div class="back-button">
                <a href="admin-dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to admin page
                </a>
            </div>
        </main>
    </div>

    <!-- Create Admin Modal -->
    <div class="modal" id="createAdminModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create School Admin</h3>
                <span class="modal-close" id="createModalClose">&times;</span>
            </div>
            <form id="createAdminForm">
                <div class="form-group">
                    <label for="adminFirstName">First Name</label>
                    <input type="text" id="adminFirstName" required>
                </div>
                <div class="form-group">
                    <label for="adminLastName">Last Name</label>
                    <input type="text" id="adminLastName" required>
                </div>
                <div class="form-group">
                    <label for="adminEmail">Email</label>
                    <input type="email" id="adminEmail" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeCreateAdminModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Create Admin</button>
                </div>
            </form>
            <div class="password-info">
                <p><strong>Note:</strong> Default password will be <code>SAdmin123</code></p>
            </div>
        </div>
    </div>

    <!-- Admin Details Modal -->
    <div class="modal" id="adminDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Admin Details</h3>
                <span class="modal-close" id="adminModalClose">&times;</span>
            </div>
            <div class="admin-details">
                <div class="detail-group">
                    <label>First Name:</label>
                    <span id="adminDetailFirstName"></span>
                </div>
                <div class="detail-group">
                    <label>Last Name:</label>
                    <span id="adminDetailLastName"></span>
                </div>
                <div class="detail-group">
                    <label>Email:</label>
                    <span id="adminDetailEmail"></span>
                </div>
                <div class="detail-group">
                    <label>Department:</label>
                    <span id="adminDetailDepartment"></span>
                </div>
                <div class="detail-group">
                    <label>Created Date:</label>
                    <span id="adminDetailCreatedDate"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- <script src="school-admins.js"></script> -->
</body>
</html>
