<?php
include("connections.php");
session_start();
$user_id = $_SESSION['user_id'] ?? 1;

// Fetch user's name 
$user_name = 'User';
$user_stmt = $con->prepare("SELECT first_name, last_name FROM users WHERE user_id=? LIMIT 1");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_stmt->bind_result($first_name, $last_name);
if ($user_stmt->fetch()) {
    $user_name = trim($first_name . ' ' . $last_name);
}
$user_stmt->close();

// Fetch posts by this user
$user_posts = [];
$stmt = $con->prepare("SELECT * FROM posts WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_posts[] = $row;
}
$stmt->close();

// Fetch liked posts
$liked_posts = [];
$like_stmt = $con->prepare(
    "SELECT p.* FROM posts p
     JOIN post_likes l ON p.post_id = l.post_id
     WHERE l.user_id=? ORDER BY l.created_at DESC"
);
$like_stmt->bind_param("i", $user_id);
$like_stmt->execute();
$like_result = $like_stmt->get_result();
while ($row = $like_result->fetch_assoc()) {
    $liked_posts[] = $row;
}
$like_stmt->close();

// Fetch posts where user commented
$commented_posts = [];
$comment_stmt = $con->prepare(
    "SELECT DISTINCT p.* FROM posts p
     JOIN post_comments c ON p.post_id = c.post_id
     WHERE c.user_id=? ORDER BY c.created_at DESC"
);
$comment_stmt->bind_param("i", $user_id);
$comment_stmt->execute();
$comment_result = $comment_stmt->get_result();
while ($row = $comment_result->fetch_assoc()) {
    $commented_posts[] = $row;
}
$comment_stmt->close();

// Tab logic
$active_tab = $_GET['tab'] ?? 'posts';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container" id="dashboardContainer">
        <!-- Left Sidebar Navigation (Same as other pages) -->
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
                    <li>
                        <a href="bookmarks.php"><i class="fas fa-bookmark"></i> Bookmarks</a>
                    </li>
                    <li class="active">
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    </li>
                </ul>
            </nav>
            <div class="post-button-container">
                <button class="post-button" id="postButton" disabled>
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
                        <h4><?php echo htmlspecialchars($user_name); ?></h4>
                        <!-- Username removed since not available -->
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </aside>
        <!-- Main Content Area -->
        <main class="profile-main-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-cover">
                    <div class="profile-avatar-container">
                        <div class="profile-avatar">
                            <img src="img/avatar-placeholder.png" alt="Person" id="profileAvatar">
                        </div>
                    </div>
                    <button class="edit-profile-btn" id="editProfileBtn" disabled>
                        Edit Profile
                    </button>
                </div>
                <div class="profile-info">
                    <h1 class="profile-name" id="profileName"><?php echo htmlspecialchars($user_name); ?></h1>
                    <!-- Username removed since not available -->
                    <p class="profile-joined" id="profileJoined">Joined May 7, 2025</p>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-number" id="postsCount"><?php echo count($user_posts); ?></span>
                            <span class="stat-label">Posts</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number" id="likesCount"><?php echo count($liked_posts); ?></span>
                            <span class="stat-label">Likes</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number" id="commentsCount"><?php echo count($commented_posts); ?></span>
                            <span class="stat-label">Comments</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Profile Tabs -->
            <div class="profile-tabs">
                <a href="profile.php?tab=posts" class="profile-tab<?php echo ($active_tab == 'posts') ? ' active' : ''; ?>">
                    <i class="fas fa-clipboard-list"></i>
                    Posts
                </a>
                <a href="profile.php?tab=liked" class="profile-tab<?php echo ($active_tab == 'liked') ? ' active' : ''; ?>">
                    <i class="fas fa-heart"></i>
                    Liked
                </a>
                <a href="profile.php?tab=comments" class="profile-tab<?php echo ($active_tab == 'comments') ? ' active' : ''; ?>">
                    <i class="fas fa-comment"></i>
                    Comments
                </a>
            </div>
            <!-- Profile Content -->
            <div class="profile-content">
                <!-- Posts Tab Content -->
                <div class="tab-content<?php echo ($active_tab == 'posts') ? ' active' : ''; ?>" id="posts-content" <?php if ($active_tab != 'posts') echo 'style="display:none"'; ?>>
                    <h2>Your Posts</h2>
                    <div class="posts-list" id="userPostsList">
                        <?php if (empty($user_posts)): ?>
                            <div class="empty-state" id="emptyPosts">
                                <i class="fas fa-clipboard-list empty-icon"></i>
                                <h3>No Posts Yet</h3>
                                <p>You haven't created any posts yet. Start sharing your thoughts!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($user_posts as $post): ?>
                                <article class="post-card">
                                    <div class="post-header" style="display:flex;align-items:center;gap:12px;">
                                        <div class="post-avatar">
                                            <img src="img/avatar-placeholder.png" alt="Person" style="width:40px;height:40px;border-radius:50%;">
                                        </div>
                                        <div class="post-user-info" style="display:flex;flex-direction:column;">
                                            <span class="post-author" style="font-weight:bold;"><?php echo htmlspecialchars($user_name); ?></span>
                                            <span class="post-username" style="color:#888;">
                                                <?php
                                                    $username = strtolower(str_replace(' ', '', explode(' ', trim($user_name))[0]));
                                                    echo '@' . htmlspecialchars($username);
                                                ?>
                                            </span>
                                        </div>
                                        <span class="post-timestamp" style="margin-left:auto;color:#888;">
                                            <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="post-content" style="margin-top:10px;">
                                        <h3 class="post-title" style="margin-bottom:6px;"><?php echo htmlspecialchars($post['title']); ?></h3>
                                        <p class="post-text"><?php echo htmlspecialchars($post['content']); ?></p>
                                        <span class="post-department <?php
                                            echo strtolower(
                                                $post['target_department_id'] == 1 ? 'dit' :
                                                ($post['target_department_id'] == 2 ? 'dom' :
                                                ($post['target_department_id'] == 3 ? 'das' :
                                                ($post['target_department_id'] == 4 ? 'ted' : 'all'))));
                                            ?>" style="display:inline-block;margin-top:12px;">
                                            <?php
                                            echo ($post['target_department_id'] == 1) ? 'DIT'
                                                : (($post['target_department_id'] == 2) ? 'DOM'
                                                : (($post['target_department_id'] == 3) ? 'DAS'
                                                : (($post['target_department_id'] == 4) ? 'TED'
                                                : 'ALL DEPARTMENTS')));
                                            ?>
                                        </span>
                                    </div>
                                    <div class="post-stats" style="display:flex;align-items:center;gap:24px;margin-top:18px;color:#444;">
                                        <span class="stat"><i class="fas fa-comment"></i>
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
                                        <span class="stat"><i class="fas fa-heart"></i>
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
                                        <span class="stat"><i class="fas fa-eye"></i>
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
                                        <span class="stat"><i class="fas fa-bookmark"></i>
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
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Liked Tab Content -->
                <div class="tab-content<?php echo ($active_tab == 'liked') ? ' active' : ''; ?>" id="liked-content" <?php if ($active_tab != 'liked') echo 'style="display:none"'; ?>>
                    <div class="posts-list" id="likedPostsList">
                        <?php if (empty($liked_posts)): ?>
                            <div class="empty-state" id="emptyLiked">
                                <i class="fas fa-heart empty-icon"></i>
                                <h3>No Liked Posts</h3>
                                <p>You haven't liked any posts yet. Like posts to see them here!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($liked_posts as $post): ?>
                                <article class="post-card">
                                    <div class="post-header">
                                        <div class="post-avatar">
                                            <img src="img/avatar-placeholder.png" alt="Person">
                                        </div>
                                        <div class="post-user-info">
                                            <h4 class="post-author"><?php echo htmlspecialchars($post['user_id'] == $user_id ? $user_name : 'User'); ?></h4>
                                            <span class="post-timestamp"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="post-content">
                                        <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                        <p class="post-text"><?php echo htmlspecialchars($post['content']); ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Comments Tab Content -->
                <div class="tab-content<?php echo ($active_tab == 'comments') ? ' active' : ''; ?>" id="comments-content" <?php if ($active_tab != 'comments') echo 'style="display:none"'; ?>>
                    <div class="posts-list" id="commentedPostsList">
                        <?php if (empty($commented_posts)): ?>
                            <div class="empty-state" id="emptyComments">
                                <i class="fas fa-comment empty-icon"></i>
                                <h3>No Comments Yet</h3>
                                <p>You haven't commented on any posts yet. Join the conversation!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($commented_posts as $post): ?>
                                <article class="post-card">
                                    <div class="post-header">
                                        <div class="post-avatar">
                                            <img src="img/avatar-placeholder.png" alt="Person">
                                        </div>
                                        <div class="post-user-info">
                                            <h4 class="post-author"><?php echo htmlspecialchars($post['user_id'] == $user_id ? $user_name : 'User'); ?></h4>
                                            <span class="post-timestamp"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="post-content">
                                        <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                        <p class="post-text"><?php echo htmlspecialchars($post['content']); ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>