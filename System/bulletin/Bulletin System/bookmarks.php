<?php
include("user_session.php");
requireLogin();

// Connect to the database
include("connections.php");
include("post-modal-shared.php");

// Fetch bookmarked posts for the current user
$user_id = $_SESSION['user_id'];
$bookmarked_posts = [];

$sql = "SELECT p.*, u.first_name, u.last_name, u.profile_picture 
        FROM post_bookmarks pb
        JOIN posts p ON pb.post_id = p.post_id
        JOIN signuptbl u ON p.user_id = u.user_id
        WHERE pb.user_id = ?
        ORDER BY pb.created_at DESC";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bookmarked_posts[] = $row;
    }
}
$stmt->close();

// Handle Clear All Bookmarks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_all_bookmarks'])) {
    $user_id_to_clear = $_SESSION['user_id'];
    
    $delete_sql = "DELETE FROM post_bookmarks WHERE user_id = ?";
    $delete_stmt = $con->prepare($delete_sql);
    $delete_stmt->bind_param("i", $user_id_to_clear);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    // Redirect to the same page to reflect the changes
    header("Location: bookmarks.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookmarks - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="bookmarks.css">
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
                    <li>
                        <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                    </li>
                    <li class="active">
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
                            $initials = htmlspecialchars(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1));
                            $imgSrc = 'https://placehold.co/36x36/cccccc/000000?text=' . $initials;
                        }
                        ?>
                        <img src="<?php echo $imgSrc; ?>" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h4>
                        <p><?php echo htmlspecialchars($currentUser['username']); ?></p>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Area - Bookmarks -->
        <main class="main-content bookmarks-content">
            <div class="bookmarks-container">
                <div class="bookmarks-header">
                    <h1>Bookmarks</h1>
                    <form method="post" onsubmit="return confirm('Are you sure you want to clear all bookmarks? This action cannot be undone.');">
                        <button type="submit" name="clear_all_bookmarks" class="clear-all-bookmarks" id="clearAllBookmarks">
                            <i class="fas fa-trash"></i> Clear all bookmarks
                        </button>
                    </form>
                </div>
                
                <div class="bookmarks-tabs">
                    <button class="bookmark-tab active" data-filter="all">All</button>
                    <button class="bookmark-tab" data-filter="mentions">Mentions</button>
                </div>
                
                <div class="bookmarks-list" id="bookmarksList">
                    <?php if (empty($bookmarked_posts)): ?>
                        <div class="empty-bookmarks" id="emptyBookmarks">
                            <i class="fas fa-bookmark empty-icon"></i>
                            <h2>No Bookmarks Yet</h2>
                            <p>You haven't bookmarked any posts yet. Start bookmarking posts to see them here.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($bookmarked_posts as $post): ?>
                            <div class="bookmark-item" data-type="announcement">
                                <div class="bookmark-avatar">
                                    <?php
                                    $profilePic = $post['profile_picture'] ?? '';
                                    if ($profilePic) {
                                        $profilePic = preg_replace('#^uploads/#', '', $profilePic);
                                        $imgSrc = 'uploads/' . htmlspecialchars($profilePic);
                                    } else {
                                        $imgSrc = 'img/avatar-placeholder.png';
                                    }
                                    ?>
                                    <img src="<?php echo $imgSrc; ?>" alt="User Avatar">
                                </div>
                                <div class="bookmark-content">
                                    <div class="bookmark-header">
                                        <span class="bookmark-author"><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></span>
                                        <span class="bookmark-username">@<?php echo htmlspecialchars(strtolower($post['first_name'] . $post['last_name'])); ?></span>
                                        <span class="bookmark-date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                    </div>
                                    <div class="bookmark-title"><?php echo htmlspecialchars($post['title']); ?></div>
                                    <div class="bookmark-text">
                                        <?php echo htmlspecialchars($post['content']); ?>
                                    </div>
                                    <div class="bookmark-tag">
                                        <!-- You might need a way to map department ID back to code, e.g., DIT, DOM -->
                                        <span class="tag">DEPARTMENT</span>
                                    </div>
                                    <div class="bookmark-stats">
                                        <!-- Stats would need additional queries, keeping it simple for now -->
                                        <span class="stat"><i class="fas fa-comment"></i> 0</span>
                                        <span class="stat"><i class="fas fa-heart"></i> 0</span>
                                        <span class="stat"><i class="fas fa-eye"></i> 0</span>
                                    </div>
                                </div>
                                <button class="remove-bookmark" data-id="<?php echo $post['post_id']; ?>">
                                    <i class="fas fa-bookmark"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Right Sidebar - Calendar (same as dashboard) -->
        <aside class="calendar-sidebar">
            <div class="calendar-header">
                <h2>Calendar of Events</h2>
            </div>
            
            <div class="calendar-body">
                <div class="calendar-day">
                    <div class="day-number">1</div>
                    <div class="day-info">
                        <div class="day-label">MAY, THU</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">2</div>
                    <div class="day-info">
                        <div class="day-label">MAY, THU</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event has-event">Capstone Project Defense Schedule</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">3</div>
                    <div class="day-info">
                        <div class="day-label">MAY, THU</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">4</div>
                    <div class="day-info">
                        <div class="day-label">MAY, THU</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">5</div>
                    <div class="day-info">
                        <div class="day-label">MAY, THU</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">6</div>
                    <div class="day-info">
                        <div class="day-label">MAY, THU</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">7</div>
                    <div class="day-info">
                        <div class="day-label">MAY, THU</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">8</div>
                    <div class="day-info">
                        <div class="day-label">MAY, THU</div>
                        <div class="day-event time-event">9-12 PM</div>
                        <div class="day-event has-event">CSHARP General Assembly</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">9</div>
                    <div class="day-info">
                        <div class="day-label">MAY, THU</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">10</div>
                    <div class="day-info">
                        <div class="day-label">MAY, FRI</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">11</div>
                    <div class="day-info">
                        <div class="day-label">MAY, SAT</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">12</div>
                    <div class="day-info">
                        <div class="day-label">MAY, SUN</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">13</div>
                    <div class="day-info">
                        <div class="day-label">MAY, MON</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">14</div>
                    <div class="day-info">
                        <div class="day-label">MAY, TUE</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
                
                <div class="calendar-day">
                    <div class="day-number">15</div>
                    <div class="day-info">
                        <div class="day-label">MAY, WED</div>
                        <div class="day-event all-day">All day</div>
                        <div class="day-event no-events">No events</div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <script src="script.js"></script>
    <script src="bookmarks.js"></script>
</body>
</html>