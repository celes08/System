<?php
include("connections.php");
session_start();
$user_id = $_SESSION['user_id'] ?? 1;

// Handle "Clear All Bookmarks"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_all_bookmarks'])) {
    $stmt = $con->prepare("DELETE FROM post_bookmarks WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: bookmarks.php");
    exit();
}

// Handle Unbookmark (remove single)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookmark_post_id'])) {
    $post_id = intval($_POST['bookmark_post_id']);
    $stmt = $con->prepare("DELETE FROM post_bookmarks WHERE post_id=? AND user_id=?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: bookmarks.php");
    exit();
}

$posts = [];
$sql = "SELECT p.* FROM posts p
        INNER JOIN post_bookmarks b ON p.post_id = b.post_id
        WHERE b.user_id = ?
        ORDER BY b.bookmark_id DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookmarks - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="bookmarks.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bookmark-btn.bookmarked i {
            color: #FFD600;
        }
    </style>
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
                <button class="post-button">
                    <i class="fas fa-plus"></i> Post
                </button>
            </div>
            
            <div class="sidebar-footer">
                <ul>
                    <li><a href="settings.html"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="help.html"><i class="fas fa-question-circle"></i> Help</a></li>
                    <li><a href="index.html"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
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
        
        <!-- Main Content Area - Bookmarks -->
        <main class="main-content bookmarks-content">
            <div class="bookmarks-container">
                <div class="bookmarks-header">
                    <h1>Bookmarks</h1>
                    <form method="post" style="display:inline;">
                        <button class="clear-all-bookmarks" type="submit" name="clear_all_bookmarks" onclick="return confirm('Are you sure you want to remove all bookmarks?');">
                            <i class="fas fa-trash"></i> Clear all bookmarks
                        </button>
                    </form>
                </div>
                
                <div class="bookmarks-tabs">
                    <button class="bookmark-tab active" data-filter="all">All</button>
                    <button class="bookmark-tab" data-filter="mentions">Mentions</button>
                </div>
                
                <div class="bookmarks-list" id="bookmarksList">
                    <?php if (empty($posts)): ?>
                        <div class="empty-notifications">
                            <i class="fas fa-bookmark empty-icon"></i>
                            <h2>No Bookmarks Yet</h2>
                            <p>You haven't bookmarked any announcements.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="bookmark-item" data-type="announcement">
                                <div class="bookmark-avatar">
                                    <img src="img/avatar-placeholder.png" alt="User Avatar">
                                </div>
                                <div class="bookmark-content">
                                    <div class="bookmark-header">
                                        <span class="bookmark-author">Person</span>
                                        <span class="bookmark-username">@person</span>
                                        <span class="bookmark-date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                    </div>
                                    <div class="bookmark-title"><?php echo htmlspecialchars($post['title']); ?></div>
                                    <div class="bookmark-text"><?php echo htmlspecialchars($post['content']); ?></div>
                                    <div class="bookmark-tag">
                                        <span class="tag <?php echo strtolower($post['target_department_id'] == 1 ? 'dit' : ($post['target_department_id'] == 2 ? 'dom' : ($post['target_department_id'] == 3 ? 'das' : ($post['target_department_id'] == 4 ? 'ted' : 'all')))); ?>">
                                            <?php
                                            echo ($post['target_department_id'] == 1) ? 'DIT'
                                                : (($post['target_department_id'] == 2) ? 'DOM'
                                                : (($post['target_department_id'] == 3) ? 'DAS'
                                                : (($post['target_department_id'] == 4) ? 'TED'
                                                : 'ALL')));
                                            ?>
                                        </span>
                                    </div>
                                    <div class="bookmark-stats">
                                        <span class="stat">
                                            <i class="fas fa-comment"></i>
                                            <?php
                                            $comment_count = 0;
                                            $comment_q = $con->prepare("SELECT COUNT(*) FROM post_comments WHERE post_id=?");
                                            $comment_q->bind_param("i", $post['post_id']);
                                            $comment_q->execute();
                                            $comment_q->bind_result($comment_count);
                                            $comment_q->fetch();
                                            $comment_q->close();
                                            echo $comment_count;
                                            ?>
                                        </span>
                                        <span class="stat">
                                            <i class="fas fa-heart"></i>
                                            <?php
                                            $like_count = 0;
                                            $like_q = $con->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id=?");
                                            $like_q->bind_param("i", $post['post_id']);
                                            $like_q->execute();
                                            $like_q->bind_result($like_count);
                                            $like_q->fetch();
                                            $like_q->close();
                                            echo $like_count;
                                            ?>
                                        </span>
                                        <span class="stat">
                                            <i class="fas fa-eye"></i>
                                            <?php
                                            $view_count = 0;
                                            $view_q = $con->prepare("SELECT COUNT(*) FROM post_views WHERE post_id=?");
                                            $view_q->bind_param("i", $post['post_id']);
                                            $view_q->execute();
                                            $view_q->bind_result($view_count);
                                            $view_q->fetch();
                                            $view_q->close();
                                            echo $view_count;
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="bookmark_post_id" value="<?php echo $post['post_id']; ?>">
                                    <button class="remove-bookmark bookmark-btn bookmarked" type="submit" title="Remove Bookmark">
                                        <i class="fas fa-bookmark"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
        </main>

                <!-- Right Sidebar - Calendar -->
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

    <script src="script.js"></script>
    <script src="bookmarks.js"></script>
</body>
</html>