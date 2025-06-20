<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CVSU Bulletin System</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <img src="Bulletin System/img/logo.png" alt="CVSU Logo" class="logo">
                <h1>Admin Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="admin-profile" id="adminProfile">
                    <div class="admin-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="dropdown-menu" id="adminDropdown">
                        <a href="admin-settings.php"><i class="fas fa-cog"></i> Settings</a>
                        <a href="muted-words.pp"><i class="fas fa-ban"></i> Muted Words</a>
                        <a href="#" id="adminLogout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="dashboard-grid">
                <!-- First Row - User Management -->
                <div class="dashboard-card" onclick="navigateTo('registered-users.html')">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-content">
                        <h2 id="registeredUsersCount">187</h2>
                        <p>Registered Users</p>
                    </div>
                </div>

                <div class="dashboard-card" onclick="navigateTo('pending-registration.html')">
                    <div class="card-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="card-content">
                        <h2 id="pendingRegistrationCount">1,071</h2>
                        <p>Pending Registration</p>
                    </div>
                </div>

                <div class="dashboard-card" onclick="navigateTo('school-admins.html')">
                    <div class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="card-content">
                        <h2 id="schoolAdminsCount">80</h2>
                        <p>School Admins</p>
                    </div>
                </div>

                <!-- Second Row - Analytics & Management -->
                <div class="dashboard-card" onclick="navigateTo('admin-logs.html')">
                    <div class="card-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="card-content">
                        <h2>Logs</h2>
                        <p>System Activity</p>
                    </div>
                </div>

                <div class="dashboard-card chart-card" onclick="openDepartmentModal()">
                    <div class="card-content">
                        <h3>Users per Department</h3>
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>

                <div class="dashboard-card" onclick="navigateTo('reported-posts.html')">
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

    <!-- Floating Help Button -->
    <div class="floating-help-btn" onclick="navigateTo('admin-help.html')" title="Help Tickets">
        <i class="fas fa-life-ring"></i>
        <span class="help-badge" id="helpTicketsBadge">15</span>
    </div>

    <!-- Add Department Modal -->
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

    <!-- Notification Toast -->
    <div class="notification-toast" id="notificationToast">
        <i class="toast-icon"></i>
        <span class="toast-message"></span>
        <button class="toast-close">&times;</button>
    </div>

    <script src="admin-dashboard.js"></script>
</body>
</html>
