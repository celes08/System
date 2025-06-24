<?php
include("user_session.php");
requireLogin();
include("connections.php");
include("post-modal-shared.php");
$user_id = $_SESSION['user_id'];
$user_department = $_SESSION['user_department'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$con->query("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = $user_id AND is_read = 0");
echo '<!-- Logged in as user_id: ' . $_SESSION['user_id'] . ' -->';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="notifications.css">
    <link rel="stylesheet" href="post-modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body<?php
if (isset($_SESSION['theme'])) {
    echo ' ' . htmlspecialchars($_SESSION['theme']) . '-theme';
} else {
    echo ' system-theme';
}
?>">
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
                <form method="post" style="margin:0;">
                    <button class="post-button" name="showPostModal" type="submit">
                        <i class="fas fa-plus"></i> Post
                    </button>
                </form>
            </div>
            
            <div class="sidebar-footer">
                <ul>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="help.php"><i class="fas fa-question-circle"></i> Help</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
                </ul>
                
                <div class="user-profile">
                    <div class="user-avatar">
                        <?php
                        $profilePic = $currentUser['profile_picture'] ?? '';
                        if ($profilePic) {
                            $profilePic = preg_replace('#^uploads/#', '', $profilePic);
                            $imgSrc = 'uploads/' . htmlspecialchars($profilePic);
                        } else {
                            $initials = htmlspecialchars(substr($currentUser['fullName'], 0, 1));
                            $imgSrc = 'https://placehold.co/36x36/cccccc/000000?text=' . $initials;
                        }
                        ?>
                        <img src="<?php echo $imgSrc; ?>" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($currentUser['fullName']); ?></h4>
                        <p><?php echo htmlspecialchars($currentUser['username']); ?></p>
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
                
                <div class="notifications-filter" style="margin-bottom: 16px;">
                    <form method="get" style="display:inline;">
                        <select name="filter" onchange="this.form.submit()">
                            <option value="all" <?php if($filter==='all') echo 'selected'; ?>>All Notifications</option>
                            <option value="department" <?php if($filter==='department') echo 'selected'; ?>>My Department Only</option>
                        </select>
                    </form>
                </div>
                
                <div class="notifications-list">
                    <?php
                    $where = "user_id = $user_id";
                    if ($filter === 'department' && !empty($user_department)) {
                        $where .= " AND (SELECT department FROM signuptbl WHERE user_id = $user_id) = '$user_department'";
                    }
                    $result = $con->query("SELECT * FROM notifications WHERE $where ORDER BY created_at DESC");
                    echo '<!-- Found ' . $result->num_rows . ' notifications for user_id ' . $user_id . ' -->';
                    ?>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<div class='notification'>";
                            echo htmlspecialchars($row['message']);
                            if ($row['related_post_id']) {
                                echo " <a href='dashboard.php?post_id=" . $row['related_post_id'] . "'>View</a>";
                            }
                            if (!$row['is_read']) {
                                echo " <span class='unread-badge'>New</span>";
                            }
                            echo "</div>";
                        }
                    } else {
                        echo "<div class='empty-notifications'><i class='fas fa-bell empty-icon'></i><h2>No Notifications Yet</h2><p>You don't have any notifications at this time.</p></div>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
    <script src="notifications.js"></script>
</body>
</html>