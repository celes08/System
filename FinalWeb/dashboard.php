<?php
// dashboard.php
// PHP-only version: tab switching and post filtering handled by PHP, UI/CSS unchanged

// Connect to the database
include("connections.php");
session_start();

// Set the default timezone
date_default_timezone_set('Asia/Manila'); // or your local timezone

// Department mapping (for dropdown and filtering)
$departments = [
    'all' => 'All Departments',
    1 => 'DIT',
    2 => 'DOM',
    3 => 'DAS',
    4 => 'TED'
];

// Tab logic for department filter
$active_tab = $_GET['tab'] ?? 'all';

// Fetch posts from DB (replace with your actual posts table/fields)
$posts = [];
$where = "";

if ($active_tab !== 'all') {
    // Map tab string to department id
    $dept_map = ['dit' => 1, 'dom' => 2, 'das' => 3, 'ted' => 4];
    $dept_id = $dept_map[strtolower($active_tab)] ?? null;
    if ($dept_id) {
        $where = "WHERE (target_department_id = " . intval($dept_id) . " OR target_department_id IS NULL)";
    }
    // If $dept_id is not found, $where stays empty (shows all)
}

$sql = "SELECT * FROM posts $where ORDER BY created_at DESC";
$result = $con->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

// Handle post submission (modal)
$post_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitPost'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $department_raw = $_POST['department'] ?? '';
    $target_department_id = ($department_raw === 'all') ? null : intval($department_raw);
    $important = isset($_POST['important']) ? 1 : 0;
    $created_at = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? 1; // Replace with real user session

    // Check if user exists
    $user_check = $con->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $user_check->bind_param("i", $user_id);
    $user_check->execute();
    $user_check->store_result();
    if ($user_check->num_rows === 0) {
        $post_success = "User does not exist. Please log in again.";
        $user_check->close();
    } else {
        $user_check->close();

        if ($title && isset($_POST['department'])) { // Accept 0 as valid
            $post_type = 'Department';
            $is_scheduled = 0;
            $scheduled_publish_at = null; // or date string if scheduled
            $status = 'Published';
            $view_count = 0;
            $published_at = $created_at;
            $updated_at = $created_at;
            $last_edited_at = $created_at;
            $last_edited_by_user_id = $user_id;

            $stmt = $con->prepare(
                "INSERT INTO posts 
                (user_id, title, content, post_type, target_department_id, is_super_important, is_scheduled, scheduled_publish_at, status, view_count, created_at, published_at, updated_at, last_edited_at, last_edited_by_user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            // If $target_department_id is null, use 'i' and pass null, MySQLi will handle it
            $stmt->bind_param(
                "isssiiississssi",
                $user_id,
                $title,
                $content,
                $post_type,
                $target_department_id,
                $important,
                $is_scheduled,
                $scheduled_publish_at,
                $status,
                $view_count,
                $created_at,
                $published_at,
                $updated_at,
                $last_edited_at,
                $last_edited_by_user_id
            );
            if ($stmt->execute()) {
                $post_success = "Announcement posted!";
                header("Location: dashboard.php?tab=" . urlencode($active_tab));
                exit();
            } else {
                $post_success = "Failed to post announcement: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $post_success = "Title and department are required.";
        }
    }
}

// Handle Like/Unlike
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_post_id'])) {
    $post_id = intval($_POST['like_post_id']);
    $user_id = $_SESSION['user_id'] ?? 1;
    // Check if already liked
    $check = $con->prepare("SELECT like_id FROM post_likes WHERE post_id=? AND user_id=?");
    $check->bind_param("ii", $post_id, $user_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows == 0) {
        // Not liked yet, insert like
        $stmt = $con->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Already liked, remove like (unlike)
        $stmt = $con->prepare("DELETE FROM post_likes WHERE post_id=? AND user_id=?");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    $check->close();
    header("Location: dashboard.php?tab=" . urlencode($active_tab));
    exit();
}

// Handle Bookmark
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookmark_post_id'])) {
    $post_id = intval($_POST['bookmark_post_id']);
    $user_id = $_SESSION['user_id'] ?? 1;
    // Check if already bookmarked
    $check = $con->prepare("SELECT bookmark_id FROM post_bookmarks WHERE post_id=? AND user_id=?");
    $check->bind_param("ii", $post_id, $user_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows == 0) {
        // Not bookmarked yet, insert bookmark
        $stmt = $con->prepare("INSERT INTO post_bookmarks (post_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Already bookmarked, remove bookmark (unbookmark)
        $stmt = $con->prepare("DELETE FROM post_bookmarks WHERE post_id=? AND user_id=?");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    $check->close();
    header("Location: dashboard.php?tab=" . urlencode($active_tab));
    exit();
}

// Handle Comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_post_id'], $_POST['comment_text'])) {
    $post_id = intval($_POST['comment_post_id']);
    $user_id = $_SESSION['user_id'] ?? 1;
    $comment = trim($_POST['comment_text']);
    if ($comment !== '') {
        $stmt = $con->prepare("INSERT INTO post_comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user_id, $comment);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: dashboard.php?tab=" . urlencode($active_tab));
    exit();
}

// Modal logic for post modal
$show_post_modal = isset($_POST['showPostModal']) || isset($_POST['submitPost']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="post-modal.css">
    <link rel="stylesheet" href="posts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Add to styles.css or inside <style> in <head> */
.modal-overlay {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.4);
    align-items: center;
    justify-content: center;
}
.modal-overlay.active,
.modal-overlay[style*="display: flex"] {
    display: flex !important;
}
.modal-content {
    background: #fff;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.15);
    width: 100%;
    max-width: 400px;
}
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container" id="dashboardContainer">
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="img/logo.png" alt="CVSU Logo" class="sidebar-logo">
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li>
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
        
        <!-- Main Content Area -->
        <main class="main-content">
            <header class="content-header">
                <div class="header-title">
                    <h1>Home</h1>
                    <div class="tabs">
                        <form method="get" style="display:inline;">
                            <button type="submit" name="tab" value="all" class="tab<?php echo ($active_tab === 'all' ? ' active' : ''); ?>">All</button>
                        </form>
                        <form method="get" style="display:inline;">
                            <button type="submit" name="tab" value="dit" class="tab<?php echo ($active_tab === 'dit' ? ' active' : ''); ?>">DIT</button>
                        </form>
                        <form method="get" style="display:inline;">
                            <button type="submit" name="tab" value="dom" class="tab<?php echo ($active_tab === 'dom' ? ' active' : ''); ?>">DOM</button>
                        </form>
                        <form method="get" style="display:inline;">
                            <button type="submit" name="tab" value="das" class="tab<?php echo ($active_tab === 'das' ? ' active' : ''); ?>">DAS</button>
                        </form>
                        <form method="get" style="display:inline;">
                            <button type="submit" name="tab" value="ted" class="tab<?php echo ($active_tab === 'ted' ? ' active' : ''); ?>">TED</button>
                        </form>
                    </div>
                </div>
                <div class="search-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search Announcements" disabled>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <div class="posts-feed" id="postsFeed">
                    <?php if (empty($posts)): ?>
                        <div class="empty-notifications">
                            <i class="fas fa-bell empty-icon"></i>
                            <h2>No Announcements Yet</h2>
                            <p>There are no announcements for this department.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                        <?php
                        $post_id = $post['post_id'];
                        $user_id = $_SESSION['user_id'] ?? 1;
                        // Check if this user has already viewed this post in this session
                        if (empty($_SESSION['viewed_posts'][$post_id])) {
                            $stmt = $con->prepare("INSERT INTO post_views (post_id, user_id) VALUES (?, ?)");
                            $stmt->bind_param("ii", $post_id, $user_id);
                            $stmt->execute();
                            $stmt->close();
                            $_SESSION['viewed_posts'][$post_id] = true;
                        }
                        ?>
                        <article class="post-card" data-post-id="<?php echo $post['post_id']; ?>" data-department="<?php echo strtolower($departments[$post['target_department_id']] ?? 'all'); ?>">
                            <div class="post-header">
                                <div class="post-avatar">
                                    <img src="img/avatar-placeholder.png" alt="Person">
                                </div>
                                <div class="post-user-info">
                                    <h4 class="post-author">Person</h4>
                                    <p class="post-username">@person</p>
                                    <span class="post-timestamp"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                <p class="post-text"><?php echo htmlspecialchars($post['content']); ?></p>
                                <span class="post-department <?php echo strtolower($departments[$post['target_department_id']] ?? 'all'); ?>">
                                    <?php
                                    echo ($post['target_department_id'] == 1) ? 'DIT'
                                        : (($post['target_department_id'] == 2) ? 'DOM'
                                        : (($post['target_department_id'] == 3) ? 'DAS'
                                        : (($post['target_department_id'] == 4) ? 'TED'
                                        : 'ALL DEPARTMENTS')));
                                    ?>
                                </span>
                            </div>
                            
                            <div class="post-actions">
                                <!-- Comments -->
                                <?php
                                    // Fetch comment count for this post
                                    $comment_count = 0;
                                    $comment_q = $con->prepare("SELECT COUNT(*) FROM post_comments WHERE post_id=?");
                                    $comment_q->bind_param("i", $post['post_id']);
                                    $comment_q->execute();
                                    $comment_q->bind_result($comment_count);
                                    $comment_q->fetch();
                                    $comment_q->close();
                                    ?>
                                    <button class="action-btn comment-btn" type="button" onclick="openCommentModal(<?php echo $post['post_id']; ?>);">
                                        <i class="fas fa-comment"></i>
                                        <span class="action-count"><?php echo $comment_count; ?></span>
                                    </button>

                                <!-- Likes -->
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="like_post_id" value="<?php echo $post['post_id']; ?>">
                                    <?php
                                    // Check if the user already liked this post
                                    $liked = false;
                                    $like_check = $con->prepare("SELECT like_id FROM post_likes WHERE post_id=? AND user_id=?");
                                    $like_check->bind_param("ii", $post['post_id'], $user_id);
                                    $like_check->execute();
                                    $like_check->store_result();
                                    if ($like_check->num_rows > 0) $liked = true;
                                    $like_check->close();
                                    ?>
                                    <button class="action-btn like-btn<?php echo $liked ? ' liked' : ''; ?>" type="submit">
                                        <i class="fas fa-heart"></i>
                                        <span class="action-count">
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
                                    </button>
                                </form>

                                <!-- Views -->
                                <button class="action-btn view-btn" disabled>
                                    <i class="fas fa-eye"></i>
                                    <span class="action-count">
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
                                </button>

                                <!-- Bookmarks -->
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="bookmark_post_id" value="<?php echo $post['post_id']; ?>">
                                    <?php
                                    // Check if the user already bookmarked this post
                                    $bookmarked = false;
                                    $bookmark_check = $con->prepare("SELECT bookmark_id FROM post_bookmarks WHERE post_id=? AND user_id=?");
                                    $bookmark_check->bind_param("ii", $post['post_id'], $user_id);
                                    $bookmark_check->execute();
                                    $bookmark_check->store_result();
                                    if ($bookmark_check->num_rows > 0) $bookmarked = true;
                                    $bookmark_check->close();
                                    ?>
                                    <button class="action-btn bookmark-btn<?php echo $bookmarked ? ' bookmarked' : ''; ?>" type="submit">
                                        <i class="fas fa-bookmark"></i>
                                        <span class="action-count">
                                            <?php
                                            $bookmark_count = 0;
                                            $bookmark_q = $con->prepare("SELECT COUNT(*) FROM post_bookmarks WHERE post_id=?");
                                            $bookmark_q->bind_param("i", $post['post_id']);
                                            $bookmark_q->execute();
                                            $bookmark_q->bind_result($bookmark_count);
                                            $bookmark_q->fetch();
                                            $bookmark_q->close();
                                            echo $bookmark_count;
                                            ?>
                                        </span>
                                    </button>
                                </form>
                            </div>

                            <!-- Show Comments -->
                            <div class="post-comments-scroll" style="max-height:120px; overflow-y:auto; margin-top:10px;">
                        <?php
                        // Fetch all comments for this post, including user info
                        $comments = [];
                        $comment_q = $con->prepare(
                            "SELECT c.comment, c.created_at, u.first_name, u.profile_picture_url
                             FROM post_comments c 
                             JOIN users u ON c.user_id = u.user_id 
                             WHERE c.post_id=? 
                             ORDER BY c.created_at ASC"
                        );
                        $comment_q->bind_param("i", $post['post_id']);
                        $comment_q->execute();
                        $comment_q->bind_result($comment_text, $comment_created, $comment_user, $comment_avatar);
                        while ($comment_q->fetch()) {
                            $comments[] = [
                                'text' => $comment_text,
                                'created' => $comment_created,
                                'user' => $comment_user,
                                'avatar' => $comment_avatar
                            ];
                        }
                        $comment_q->close();

                        // Display all comments 
                        foreach ($comments as $c) {
                            $avatar = $c['avatar'] ? htmlspecialchars($c['avatar']) : 'img/avatar-placeholder.png';
                            $user = htmlspecialchars($c['user']);
                            $text = htmlspecialchars($c['text']);
                            $date = date('M j, Y H:i', strtotime($c['created']));
                            echo '<div class="post-comment" style="display:flex;align-items:flex-start;gap:10px;margin-bottom:8px;">';
                            echo '  <img src="' . $avatar . '" alt="User" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">';
                            echo '  <div>';
                            echo '    <strong>' . $user . '</strong><br>';
                            echo '    <span>' . $text . '</span><br>';
                            echo '    <small>' . $date . '</small>';
                            echo '  </div>';
                            echo '</div>';
                        }
                        ?>
                        </div>
                        </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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

    <!-- Post Modal -->
    <div class="modal-overlay<?php echo $show_post_modal ? ' active' : ''; ?>" id="postModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>New Announcement</h2>
                <form method="post" style="display:inline; float:right;">
                    <button class="modal-close" name="closePostModal" type="submit" style="background:none;border:none;">
                        <i class="fas fa-times"></i>
                    </button>
                </form>
            </div>
            
            <div class="modal-body">
                <form method="post">
                    <div class="form-group">
                        <label for="postTitle">Title</label>
                        <input type="text" id="postTitle" name="title" placeholder="What's happening?" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="postContent">Content</label>
                        <textarea id="postContent" name="content" placeholder="Share your announcement..." rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
    <label for="postDepartment">Target Audience</label>
    <div class="select-wrapper">
        <select id="postDepartment" name="department" required>
            <option value="" disabled selected>Select Target Audience</option>
            <option value="all">All Departments</option>
            <option value="1">DIT Only</option>
            <option value="2">DOM Only</option>
            <option value="3">DAS Only</option>
            <option value="4">TED Only</option>
        </select>
        <i class="fas fa-chevron-down select-arrow"></i>
    </div>
</div>            
                    <div class="form-actions">
                        <div class="action-buttons">
                            <button type="button" class="action-btn" title="Add Image" disabled>
                                <i class="fas fa-image"></i>
                            </button>
                            <button type="button" class="action-btn" title="Add Link" disabled>
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                        
                        <div class="form-options">
                            <label class="checkbox-container">
                                <input type="checkbox" id="markImportant" name="important">
                                <span class="checkmark"></span>
                                Mark as important
                            </label>
                            
                            <button type="submit" class="post-submit-btn" name="submitPost">
                                Post
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Comment Modal -->
    <div class="modal-overlay" id="commentModal" style="display:none;">
        <div class="modal-content" style="max-width:400px;">
            <div class="modal-header">
                <h2>Add Comment</h2>
                <button class="modal-close" type="button" onclick="closeCommentModal()" style="background:none;border:none;float:right;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="post" id="commentModalForm">
                <input type="hidden" name="comment_post_id" id="commentModalPostId">
                <div class="form-group">
                    <label for="commentModalText">Your Comment</label>
                    <textarea id="commentModalText" name="comment_text" placeholder="Write your comment..." rows="4" required style="width:100%;"></textarea>
                </div>
                <button type="submit" class="post-submit-btn" style="margin-top:10px;">Post Comment</button>
            </form>
        </div>
    </div>

    <script>
function openCommentModal(postId) {
    var modal = document.getElementById('commentModal');
    modal.style.display = 'flex';
    modal.classList.add('active');
    document.getElementById('commentModalPostId').value = postId;
    document.getElementById('commentModalText').value = '';
    setTimeout(function() {
        document.getElementById('commentModalText').focus();
    }, 100);
}
function closeCommentModal() {
    var modal = document.getElementById('commentModal');
    modal.style.display = 'none';
    modal.classList.remove('active');
}
document.getElementById('commentModal').addEventListener('click', function(e) {
    if (e.target === this) closeCommentModal();
});
</script>
</body>
</html>