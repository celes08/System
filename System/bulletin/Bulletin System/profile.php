<?php
include("user_session.php");
requireLogin();
include("connections.php");
include("post-modal-shared.php");

// Fetch user's posts
$user_id = $currentUser['user_id'];
$departments = [
    'all' => 'All Departments',
    1 => 'DIT',
    2 => 'DOM',
    3 => 'DAS',
    4 => 'TED'
];

// User's own posts
$user_posts = [];
$sql = "SELECT p.*, u.first_name, u.last_name, u.profile_picture FROM posts p LEFT JOIN signuptbl u ON p.user_id = u.user_id WHERE p.user_id = ? ORDER BY p.created_at DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_posts[] = $row;
}
$stmt->close();

// Liked posts
$liked_posts = [];
$sql = "SELECT p.*, u.first_name, u.last_name, u.profile_picture FROM posts p LEFT JOIN signuptbl u ON p.user_id = u.user_id JOIN post_likes l ON p.post_id = l.post_id WHERE l.user_id = ? ORDER BY p.created_at DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $liked_posts[] = $row;
}
$stmt->close();

// Commented posts (posts where user has commented)
$commented_posts = [];
$sql = "SELECT DISTINCT p.*, u.first_name, u.last_name, u.profile_picture FROM posts p LEFT JOIN signuptbl u ON p.user_id = u.user_id JOIN post_comments c ON p.post_id = c.post_id WHERE c.user_id = ? ORDER BY p.created_at DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $commented_posts[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="profile.css">
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
                        <h4><?php echo htmlspecialchars($currentUser['fullName']); ?></h4>
                        <p><?php echo htmlspecialchars($currentUser['username']); ?></p>
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
                    <form id="profilePictureForm" action="upload_handler.php" method="POST" enctype="multipart/form-data">
                        <div class="profile-avatar-container">
                            <label for="profilePictureInput" class="profile-avatar-label">
                                <div class="profile-avatar">
                                    <img src="<?php echo !empty($currentUser['profile_picture']) ? htmlspecialchars($currentUser['profile_picture']) : 'img/avatar-placeholder.png'; ?>" alt="Profile Picture" id="profileAvatar">
                                    <div class="profile-avatar-overlay">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                </div>
                            </label>
                            <input type="file" name="profile_picture" id="profilePictureInput" style="display: none;">
                        </div>
                    </form>
                    <button class="edit-profile-btn" id="editProfileBtn">
                        Edit Profile
                    </button>
                </div>
                
                <div class="profile-info">
                    <h1 class="profile-name" id="profileName"><?php echo htmlspecialchars($currentUser['first_name']); ?></h1>
                    <p class="profile-username" id="profileUsername"><?php echo htmlspecialchars($currentUser['username']); ?></p>
                    <p class="profile-joined" id="profileJoined">Email: <?php echo htmlspecialchars($currentUser['email']); ?></p>
                    <p class="profile-joined" id="profileDepartment">Department: <?php echo htmlspecialchars($currentUser['department']); ?></p>
                    <p class="profile-joined" id="profileStudentNumber">Student Number: <?php echo htmlspecialchars($currentUser['student_number']); ?></p>
                </div>
            </div>
            
            <!-- Profile Tabs -->
            <div class="profile-tabs">
                <button class="profile-tab active" data-tab="posts">
                    <i class="fas fa-clipboard-list"></i>
                    Posts
                </button>
                <button class="profile-tab" data-tab="liked">
                    <i class="fas fa-heart"></i>
                    Liked
                </button>
                <button class="profile-tab" data-tab="comments">
                    <i class="fas fa-comment"></i>
                    Comments
                </button>
            </div>
            
            <!-- Profile Content -->
            <div class="profile-content">
                <!-- Posts Tab Content -->
                <div class="tab-content active" id="posts-content">
                    <div class="posts-list" id="userPostsList">
                        <?php if (empty($user_posts)): ?>
                            <div class="empty-state" id="emptyPosts">
                        <i class="fas fa-clipboard-list empty-icon"></i>
                        <h3>No Posts Yet</h3>
                        <p>You haven't created any posts yet. Start sharing your thoughts!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($user_posts as $post): ?>
                                <?php include 'post_card_profile.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Liked Tab Content -->
                <div class="tab-content" id="liked-content">
                    <div class="posts-list" id="likedPostsList">
                        <?php if (empty($liked_posts)): ?>
                            <div class="empty-state" id="emptyLiked">
                        <i class="fas fa-heart empty-icon"></i>
                        <h3>No Liked Posts</h3>
                        <p>You haven't liked any posts yet. Like posts to see them here!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($liked_posts as $post): ?>
                                <?php include 'post_card_profile.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Comments Tab Content -->
                <div class="tab-content" id="comments-content">
                    <div class="posts-list" id="commentedPostsList">
                        <?php if (empty($commented_posts)): ?>
                            <div class="empty-state" id="emptyComments">
                        <i class="fas fa-comment empty-icon"></i>
                        <h3>No Comments Yet</h3>
                        <p>You haven't commented on any posts yet. Join the conversation!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($commented_posts as $post): ?>
                                <?php include 'post_card_profile.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
    <script src="profile.js"></script>
</body>
</html>