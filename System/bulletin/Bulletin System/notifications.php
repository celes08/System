<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <!-- Left Sidebar Navigation - Keep unchanged as requested -->
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
                    <li class="active">
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
                <button class="post-button">
                    <i class="fas fa-plus"></i> Post
                </button>
            </div>
            
            <div class="sidebar-footer">
                <ul>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="help.php"><i class="fas fa-question-circle"></i> Help</a></li>
                    <li><a href="index.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
                </ul>
                
                <div class="user-profile">
                    <div class="user-avatar">
                        <img src="img/avatar-placeholder.png" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <h4>Person</h4>
                        <p>@person</p>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Area - Empty Notifications -->
        <main class="main-content notifications-content">
            <div class="notifications-container">
                <div class="notifications-header">
                    <h1>Notification</h1>
                    <button class="mark-all-read" disabled>
                        <i class="fas fa-check-circle"></i> Mark all as read
                    </button>
                </div>
                
                <div class="notifications-tabs">
                    <button class="notification-tab active">All</button>
                    <button class="notification-tab">Unread</button>
                    <button class="notification-tab">Mentions</button>
                </div>
                
                <div class="notifications-list empty">
                    <!-- Empty state - no notifications -->
                    <div class="empty-notifications">
                        <i class="fas fa-bell empty-icon"></i>
                        <h2>No Notifications Yet</h2>
                        <p>You don't have any notifications at this time.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
    <script src="notifications.js"></script>
</body>
</html>