<?php
ob_start();
// dashboard.php
// PHP-only version: tab switching and post filtering handled by PHP, UI/CSS unchanged
include("user_session.php");
requireLogin();
include("post-modal-shared.php");

// Map currentUser to user variable for consistency in the template
if ($currentUser) {
    $user = [
        'firstName' => $currentUser['first_name'] ?? 'N/A',
        'lastName' => $currentUser['last_name'] ?? 'N/A',
        'studentNumber' => $currentUser['student_number'] ?? 'N/A',
        'profile_picture' => $currentUser['profile_picture'] ?? null,
        'username' => $currentUser['username'] ?? 'N/A'
    ];
} else {
    // Fallback if currentUser is not set
    $user = [
        'firstName' => 'Guest',
        'lastName' => '',
        'studentNumber' => '',
        'profile_picture' => null,
        'username' => 'guest'
    ];
}

// Connect to the database
include("connections.php");

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

// PHP: Filter posts by search term
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "";
if ($active_tab !== 'all') {
    $dept_map = ['dit' => 1, 'dom' => 2, 'das' => 3, 'ted' => 4];
    $dept_id = $dept_map[strtolower($active_tab)] ?? null;
    if ($dept_id) {
        $where = "WHERE (p.target_department_id = " . intval($dept_id) . " OR p.target_department_id IS NULL)";
    }
}
if ($search !== '') {
    $search_sql = "(p.title LIKE ? OR p.content LIKE ?)";
    if ($where) {
        $where .= " AND $search_sql";
    } else {
        $where = "WHERE $search_sql";
    }
}
$sql = "SELECT p.*, u.first_name, u.last_name, u.profile_picture 
        FROM posts p 
        LEFT JOIN signuptbl u ON p.user_id = u.user_id 
        $where 
        ORDER BY p.created_at DESC";
if ($search !== '') {
    $search_param = "%$search%";
    if ($active_tab !== 'all' && isset($dept_id)) {
        $stmt = $con->prepare($sql);
        $stmt->bind_param("iss", $dept_id, $search_param, $search_param);
    } else {
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ss", $search_param, $search_param);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
$result = $con->query($sql);
}
if (!$result) {
    // Show database error for debugging
    echo "Database Error: " . $con->error;
    $posts = [];
} else {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

// Post submission is now handled by post-modal-shared.php

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
        // Notify post owner
        $owner_q = $con->prepare("SELECT user_id FROM posts WHERE post_id=?");
        $owner_q->bind_param("i", $post_id);
        $owner_q->execute();
        $owner_q->bind_result($owner_id);
        $owner_q->fetch();
        $owner_q->close();
        if ($owner_id != $user_id) {
            $msg = "Someone liked your post!";
            $type = "like";
            $stmt_n = $con->prepare("INSERT INTO notifications (user_id, notification_type, message, related_post_id, is_read) VALUES (?, ?, ?, ?, 0)");
            $stmt_n->bind_param("issi", $owner_id, $type, $msg, $post_id);
            $stmt_n->execute();
            $stmt_n->close();
        }
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
        // Notify post owner
        $owner_q = $con->prepare("SELECT user_id FROM posts WHERE post_id=?");
        $owner_q->bind_param("i", $post_id);
        $owner_q->execute();
        $owner_q->bind_result($owner_id);
        $owner_q->fetch();
        $owner_q->close();
        if ($owner_id != $user_id) {
            $msg = "Someone commented on your post!";
            $type = "comment";
            $stmt_n = $con->prepare("INSERT INTO notifications (user_id, notification_type, message, related_post_id, is_read) VALUES (?, ?, ?, ?, 0)");
            $stmt_n->bind_param("issi", $owner_id, $type, $msg, $post_id);
            $stmt_n->execute();
            $stmt_n->close();
        }
        // Notify mentioned users
        preg_match_all('/@([a-zA-Z0-9_]+)/', $comment, $mentions);
        if (!empty($mentions[1])) {
            foreach ($mentions[1] as $mentioned_username) {
                $mention_q = $con->prepare("SELECT user_id FROM signuptbl WHERE username = ?");
                $mention_q->bind_param("s", $mentioned_username);
                $mention_q->execute();
                $mention_q->bind_result($mentioned_user_id);
                $found = $mention_q->fetch();
                $mention_q->close();
                if ($found && $mentioned_user_id != $user_id) {
                    $msg = "You were mentioned in a comment!";
                    $type = "mention";
                    $stmt_n = $con->prepare("INSERT INTO notifications (user_id, notification_type, message, related_post_id, is_read) VALUES (?, ?, ?, ?, 0)");
                    $stmt_n->bind_param("issi", $mentioned_user_id, $type, $msg, $post_id);
                    $stmt_n->execute();
                    $stmt_n->close();
                }
            }
        }
    }
    header("Location: dashboard.php?tab=" . urlencode($active_tab));
    exit();
}

// Handle edit post
$edit_success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_post_id'])) {
    $edit_post_id = intval($_POST['edit_post_id']);
    $edit_title = trim($_POST['edit_title']);
    $edit_content = trim($_POST['edit_content']);
    $edit_department = $_POST['edit_department'] ?? 'all';
    $edit_target_department_id = ($edit_department === 'all') ? null : intval($edit_department);
    $edit_publish_date = $_POST['edit_publish_date'] ?? '';
    $edit_publish_time = $_POST['edit_publish_time'] ?? '';
    if ($edit_publish_date && $edit_publish_time) {
        $edit_scheduled_publish_at = $edit_publish_date . ' ' . $edit_publish_time . ':00';
        $edit_is_scheduled = 1;
    } else {
        $edit_scheduled_publish_at = null;
        $edit_is_scheduled = 0;
    }
    $last_edited_at = date('Y-m-d H:i:s');
    $last_edited_by_user_id = $_SESSION['user_id'];

    // Only allow if the user owns the post
    $check = $con->prepare("SELECT user_id FROM posts WHERE post_id=?");
    $check->bind_param("i", $edit_post_id);
    $check->execute();
    $check->bind_result($owner_id);
    $check->fetch();
    $check->close();

    if ($owner_id == $_SESSION['user_id']) {
        $stmt = $con->prepare("UPDATE posts SET title=?, content=?, target_department_id=?, is_scheduled=?, scheduled_publish_at=?, last_edited_at=?, last_edited_by_user_id=? WHERE post_id=?");
        $stmt->bind_param("ssisssii", $edit_title, $edit_content, $edit_target_department_id, $edit_is_scheduled, $edit_scheduled_publish_at, $last_edited_at, $last_edited_by_user_id, $edit_post_id);
        $stmt->execute();
        $stmt->close();
        $edit_success_msg = 'Post updated successfully!';
        // Do not redirect, just show the message
    }
}
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
        /* Only keep edit post button styles here */
.post-card {
    position: relative;
}
.edit-post-btn-upper {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 2;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.07);
    transition: background 0.2s, border 0.2s;
}
.edit-post-btn-upper:hover {
    background: #f0f0f0;
    border-color: #888;
}
.edit-post-btn-upper i {
    font-size: 16px;
    color: #333;
}
    </style>
</head>
<body class="dashboard-body<?php
if (isset($_SESSION['theme'])) {
    echo ' ' . htmlspecialchars($_SESSION['theme']) . '-theme';
} else {
    echo ' system-theme';
}
?>">
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
                        <?php
                        $profilePic = $user['profile_picture'] ?? '';
                        if ($profilePic) {
                            // Remove any leading 'uploads/' if present
                            $profilePic = preg_replace('#^uploads/#', '', $profilePic);
                            $imgSrc = 'uploads/' . htmlspecialchars($profilePic);
                        } else {
                            $initials = htmlspecialchars(substr($user['firstName'], 0, 1) . substr($user['lastName'], 0, 1));
                            $imgSrc = 'https://placehold.co/36x36/cccccc/000000?text=' . $initials;
                        }
                        ?>
                        <img src="<?php echo $imgSrc; ?>" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h4>
                        <p><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
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
                        <form method="get" style="display:inline;">
                            <input type="text" name="search" placeholder="Search Announcements" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </form>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <?php if (!empty($edit_success_msg)): ?>
                    <div class="notification success" style="margin-bottom:16px;"> <?php echo $edit_success_msg; ?> </div>
                <?php endif; ?>
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
                        if (empty($_SESSION['viewed_posts'][$post_id])) {
                            // Check if this view already exists in the database
                            $view_check = $con->prepare("SELECT 1 FROM post_views WHERE post_id=? AND user_id=?");
                            $view_check->bind_param("ii", $post_id, $user_id);
                            $view_check->execute();
                            $view_check->store_result();
                            if ($view_check->num_rows == 0) {
                            $stmt = $con->prepare("INSERT INTO post_views (post_id, user_id) VALUES (?, ?)");
                            $stmt->bind_param("ii", $post_id, $user_id);
                            $stmt->execute();
                            $stmt->close();
                            }
                            $view_check->close();
                            $_SESSION['viewed_posts'][$post_id] = true;
                        }
                        ?>
                        <article class="post-card" data-post-id="<?php echo $post['post_id']; ?>" data-department="<?php echo strtolower($departments[$post['target_department_id']] ?? 'all'); ?>">
                            <div class="post-header">
                                <div class="post-avatar">
                                    <?php
                                    $profilePic = $post['profile_picture'] ?? '';
                                    if ($profilePic) {
                                        // Remove any leading 'uploads/' if present
                                        $profilePic = preg_replace('#^uploads/#', '', $profilePic);
                                        $imgSrc = 'uploads/' . htmlspecialchars($profilePic);
                                    } else {
                                        $imgSrc = 'img/avatar-placeholder.png';
                                    }
                                    ?>
                                    <img src="<?php echo $imgSrc; ?>" alt="User Avatar">
                                </div>
                                <div class="post-user-info">
                                    <h4 class="post-author"><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></h4>
                                    <p class="post-username">@<?php echo htmlspecialchars(strtolower($post['first_name'] . $post['last_name'])); ?></p>
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

                                <?php if ($post['user_id'] == $user_id): ?>
                                    <button class="action-btn edit-post-btn edit-post-btn-upper" data-post-id="<?php echo $post['post_id']; ?>" style="position:absolute;top:10px;right:10px;z-index:2;">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <!-- Show Comments -->
                            <div class="post-comments-scroll" style="max-height:120px; overflow-y:auto; margin-top:10px;">
                        <?php
                        // Fetch all comments for this post, including user info
                        $comments = [];
                        $comment_q = $con->prepare(
                            "SELECT c.comment, c.created_at, u.first_name, u.profile_picture
                             FROM post_comments c 
                             JOIN signuptbl u ON c.user_id = u.user_id 
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
                            $avatar = $c['avatar'] ?? '';
                            if ($avatar) {
                                $avatar = preg_replace('#^uploads/#', '', $avatar);
                                $avatarSrc = 'uploads/' . htmlspecialchars($avatar);
                            } else {
                                $avatarSrc = 'img/avatar-placeholder.png';
                            }
                            echo '<div class="post-comment" style="display:flex;align-items:flex-start;gap:10px;margin-bottom:8px;">';
                            echo '  <img src="' . $avatarSrc . '" alt="User" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">';
                            echo '  <div>';
                            echo '    <strong>' . htmlspecialchars($c['user']) . '</strong><br>';
                            echo '    <span>' . htmlspecialchars($c['text']) . '</span><br>';
                            echo '    <small>' . date('M j, Y H:i', strtotime($c['created'])) . '</small>';
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

            // Fetch scheduled posts for the current month
            $scheduled_posts = [];
            $month_num = date('n');
            $year_num = date('Y');
            $calendar_q = $con->prepare("SELECT post_id, title, scheduled_publish_at FROM posts WHERE scheduled_publish_at IS NOT NULL AND MONTH(scheduled_publish_at) = ? AND YEAR(scheduled_publish_at) = ?");
            $calendar_q->bind_param("ii", $month_num, $year_num);
            $calendar_q->execute();
            $calendar_q->bind_result($cal_post_id, $cal_title, $cal_scheduled);
            while ($calendar_q->fetch()) {
                $date_key = date('Y-m-d', strtotime($cal_scheduled));
                if (!isset($scheduled_posts[$date_key])) $scheduled_posts[$date_key] = [];
                $scheduled_posts[$date_key][] = [
                    'id' => $cal_post_id,
                    'title' => $cal_title,
                    'datetime' => $cal_scheduled
                ];
            }
            $calendar_q->close();

            echo '<div class="calendar-body">';
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $timestamp = mktime(0, 0, 0, date('n'), $day, $year);
                $weekdayIndex = date('w', $timestamp);
                $weekday = $weekdays[$weekdayIndex];
                $isToday = (date('j') == $day && date('n') == date('n') && date('Y') == $year) ? ' style="background:#e0ffe0;border-radius:8px;"' : '';
                $date_key = date('Y-m-d', $timestamp);
                ?>
                <div class="calendar-day"<?php echo $isToday; ?>>
                    <div class="day-number"><?php echo $day; ?></div>
                    <div class="day-info">
                        <div class="day-label"><?php echo $monthShort . ', ' . $weekday; ?></div>
                        <?php if (!empty($scheduled_posts[$date_key])): ?>
                            <?php foreach ($scheduled_posts[$date_key] as $event): ?>
                                <div class="day-event">
                                    <span class="event-title"><?php echo htmlspecialchars($event['title']); ?></span>
                                    <span class="event-time"><?php echo date('H:i', strtotime($event['datetime'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div class="day-event no-events">No events</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
            ?>
        </aside>
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

    <!-- Edit Post Modal -->
    <div class="modal-overlay" id="editPostModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Post</h2>
                <button class="modal-close" type="button" onclick="closeEditPostModal()" style="background:none;border:none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" id="editPostForm">
                    <input type="hidden" name="edit_post_id" id="edit_post_id">
                    <div class="form-group">
                        <label for="edit_post_title">Title</label>
                        <input type="text" id="edit_post_title" name="edit_title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_post_content">Content</label>
                        <textarea id="edit_post_content" name="edit_content" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_post_department">Target Audience</label>
                        <div class="select-wrapper">
                            <select id="edit_post_department" name="edit_department" required>
                                <option value="all">All Departments</option>
                                <option value="1">DIT Only</option>
                                <option value="2">DOM Only</option>
                                <option value="3">DAS Only</option>
                                <option value="4">TED Only</option>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_publish_date">Publication Date</label>
                        <input type="date" id="edit_publish_date" name="edit_publish_date">
                    </div>
                    <div class="form-group">
                        <label for="edit_publish_time">Publication Time</label>
                        <input type="time" id="edit_publish_time" name="edit_publish_time">
                    </div>
                    <button type="submit" class="post-submit-btn">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Shared Post Modal -->
    <?php
    // Modal logic for post modal
    $show_post_modal = isset($_POST['showPostModal']) || isset($_POST['submitPost']);
    ?>
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
                    
                    <div class="form-group">
                        <label for="publish_date">Publication Date</label>
                        <input type="date" id="publish_date" name="publish_date">
                    </div>
                    <div class="form-group">
                        <label for="publish_time">Publication Time</label>
                        <input type="time" id="publish_time" name="publish_time">
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

    <script>
    document.querySelectorAll('.edit-post-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const postCard = this.closest('.post-card');
            const postId = this.getAttribute('data-post-id');
            const title = postCard.querySelector('.post-title').textContent.trim();
            const content = postCard.querySelector('.post-text') ? postCard.querySelector('.post-text').textContent.trim() : '';
            const department = postCard.getAttribute('data-department') || 'all';
            const scheduled = postCard.getAttribute('data-scheduled') || '';
            let scheduled_date = '', scheduled_time = '';
            if (scheduled) {
                const dt = scheduled.split(' ');
                scheduled_date = dt[0];
                scheduled_time = dt[1] ? dt[1].slice(0,5) : '';
            }

            // Fill the modal fields
            document.getElementById('edit_post_id').value = postId;
            document.getElementById('edit_post_title').value = title;
            document.getElementById('edit_post_content').value = content;
            document.getElementById('edit_post_department').value = department;
            document.getElementById('edit_publish_date').value = scheduled_date;
            document.getElementById('edit_publish_time').value = scheduled_time;

            // Show the modal
            document.getElementById('editPostModal').classList.add('active');
        });
    });

    // Close modal function (if not already present)
    function closeEditPostModal() {
        document.getElementById('editPostModal').classList.remove('active');
    }
    </script>
</body>
</html>
<?php ob_end_flush(); ?>