<?php
include("user_session.php");
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizational Chart - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="organizational-chart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container" id="dashboardContainer">
        <!-- Left Sidebar Navigation (Same as dashboard) -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="img/logo.png" alt="CVSU Logo" class="sidebar-logo">
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="active">
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
                <button class="post-button" id="postButton">
                    <i class="fas fa-plus"></i> Post
                </button>
            </div>
            
            <div class="sidebar-footer">
                <ul>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="help.php"><i class="fas fa-question-circle"></i> Help</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
                </ul>
                
                <div class="user-profile">
                    <div class="user-avatar">
                        <img src="img/avatar-placeholder.png" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($currentUser['fullName']); ?></h4>
                        <p><?php echo htmlspecialchars($currentUser['username']); ?></p>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <main class="org-chart-main-content">
            <header class="org-chart-header">
                <div class="org-chart-header-content">
                    <h1>Organizational Chart Viewer</h1>
                    <p>View organizational structure across all departments</p>
                </div>
            </header>
            
            <!-- Department Navigation Tabs -->
            <div class="department-tabs-container">
                <div class="department-tabs">
                    <button class="dept-tab active" data-department="dit">DIT</button>
                    <button class="dept-tab" data-department="dom">DOM</button>
                    <button class="dept-tab" data-department="das">DAS</button>
                    <button class="dept-tab" data-department="ted">TED</button>
                </div>
            </div>
            
            <!-- Scrollable Content -->
            <div class="org-chart-content">
                <!-- DIT Department -->
                <section class="department-section" id="dit-section" data-department="dit">
                    <div class="department-header">
                        <h2>Department of Information Technology (DIT)</h2>
                        <p>Organizational structure and hierarchy</p>
                    </div>
                    <div class="org-chart-container">
                        <img src="img/dit.png" alt="DIT Organizational Chart" class="org-chart-image">
                    </div>
                </section>

                <!-- DOM Department -->
                <section class="department-section" id="dom-section" data-department="dom">
                    <div class="department-header">
                        <h2>Department of Management (DOM)</h2>
                        <p>Organizational structure and hierarchy</p>
                    </div>
                    <div class="org-chart-container">
                        <img src="img/DOM.png" alt="DOM Organizational Chart" class="org-chart-image">
                    </div>
                </section>

                <!-- DAS Department -->
                <section class="department-section" id="das-section" data-department="das">
                    <div class="department-header">
                        <h2>Department of Arts and Sciences (DAS)</h2>
                        <p>Organizational structure and hierarchy</p>
                    </div>
                    <div class="org-chart-container">
                        <img src="img/das.png" alt="DAS Organizational Chart" class="org-chart-image">
                    </div>
                </section>

                <!-- TED Department -->
                <section class="department-section" id="ted-section" data-department="ted">
                    <div class="department-header">
                        <h2>Teacher Education Department (TED)</h2>
                        <p>Organizational structure and hierarchy</p>
                    </div>
                    <div class="org-chart-container">
                        <img src="img/TED.png" alt="TED Organizational Chart" class="org-chart-image">
                    </div>
                    <div class="org-chart-container scrollable-ted">
                        <img src="img/TED2.png" alt="TED Organizational Chart 2" class="org-chart-image">
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
    <script src="organizational-chart.js"></script>
</body>
</html>
