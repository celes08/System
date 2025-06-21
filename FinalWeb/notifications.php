<?php
// notifications.php
?>
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
        <!-- Left Sidebar Navigation -->
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
                <button class="post-button" disabled>
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
                    <form method="get" style="display:inline;">
                        <button type="submit" name="tab" value="all" class="notification-tab<?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'all') ? ' active' : ''; ?>">All</button>
                    </form>
                    <form method="get" style="display:inline;">
                        <button type="submit" name="tab" value="unread" class="notification-tab<?php echo (isset($_GET['tab']) && $_GET['tab'] === 'unread') ? ' active' : ''; ?>">Unread</button>
                    </form>
                    <form method="get" style="display:inline;">
                        <button type="submit" name="tab" value="mentions" class="notification-tab<?php echo (isset($_GET['tab']) && $_GET['tab'] === 'mentions') ? ' active' : ''; ?>">Mentions</button>
                    </form>
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
        
        <!-- Calendar Sidebar - Integrated -->
        <aside class="calendar-sidebar">
            <div class="calendar-header">
                <h2>Calendar of Events</h2>
            </div>
            <?php
            $month = date('F');
            $year = date('Y');
            $daysInMonth = date('t');
            $monthShort = strtoupper(date('M'));
            $weekdays = ['SUN','MON','TUE','WED','THU','FRI','SAT'];

            echo '<div class="calendar-body">';
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $timestamp = mktime(0, 0, 0, date('n'), $day, $year); // Use mktime for reliability
                $weekdayIndex = date('w', $timestamp); // 0=Sun, 6=Sat
                $weekday = $weekdays[$weekdayIndex];
                $isToday = (date('j') == $day && date('n') == date('n') && date('Y') == $year) ? ' style="background:#e0ffe0;border-radius:8px;"' : '';
                ?>
                <div class="calendar-day"<?php echo $isToday; ?>>
                    <div class="day-number"><?php echo $day; ?></div>
                    <div class="day-info">
                        <div class="day-label"><?php echo $monthShort . ', ' . $weekday; ?></div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
            ?>
        </aside>
    </div>
</body>
</html>