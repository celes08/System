<?php
include("user_session.php");
requireLogin();

// Debug output
echo "<!-- Debug: Profile page loaded -->";
if ($currentUser) {
    echo "<!-- Debug: User data available in profile.php -->";
    echo "<!-- Debug: Full name: " . htmlspecialchars($currentUser['fullName']) . " -->";
    echo "<!-- Debug: Username: " . htmlspecialchars($currentUser['username']) . " -->";
} else {
    echo "<!-- Debug: No user data available in profile.php -->";
}
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
        <main class="profile-main-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-cover">
                    <div class="profile-avatar-container">
                        <div class="profile-avatar">
                            <img src="img/avatar-placeholder.png" alt="Profile Picture" id="profileAvatar">
                        </div>
                    </div>
                    <button class="edit-profile-btn" id="editProfileBtn">
                        Edit Profile
                    </button>
                </div>
                
                <div class="profile-info">
                    <h1 class="profile-name" id="profileName"><?php echo htmlspecialchars($currentUser['fullName']); ?></h1>
                    <p class="profile-username" id="profileUsername"><?php echo htmlspecialchars($currentUser['username']); ?></p>
                    <p class="profile-joined" id="profileJoined">Email: <?php echo htmlspecialchars($currentUser['email']); ?></p>
                    <p class="profile-joined" id="profileDepartment">Department: <?php echo htmlspecialchars($currentUser['department']); ?></p>
                    <p class="profile-joined" id="profileStudentNumber">Student Number: <?php echo htmlspecialchars($currentUser['studentNumber']); ?></p>
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
                        <!-- User's posts will be loaded here -->
                    </div>
                    
                    <!-- Empty state for posts -->
                    <div class="empty-state" id="emptyPosts" style="display: none;">
                        <i class="fas fa-clipboard-list empty-icon"></i>
                        <h3>No Posts Yet</h3>
                        <p>You haven't created any posts yet. Start sharing your thoughts!</p>
                    </div>
                </div>
                
                <!-- Liked Tab Content -->
                <div class="tab-content" id="liked-content">
                    <div class="posts-list" id="likedPostsList">
                        <!-- Liked posts will be loaded here -->
                    </div>
                    
                    <!-- Empty state for liked posts -->
                    <div class="empty-state" id="emptyLiked" style="display: none;">
                        <i class="fas fa-heart empty-icon"></i>
                        <h3>No Liked Posts</h3>
                        <p>You haven't liked any posts yet. Like posts to see them here!</p>
                    </div>
                </div>
                
                <!-- Comments Tab Content -->
                <div class="tab-content" id="comments-content">
                    <div class="posts-list" id="commentedPostsList">
                        <!-- Posts with user's comments will be loaded here -->
                    </div>
                    
                    <!-- Empty state for comments -->
                    <div class="empty-state" id="emptyComments" style="display: none;">
                        <i class="fas fa-comment empty-icon"></i>
                        <h3>No Comments Yet</h3>
                        <p>You haven't commented on any posts yet. Join the conversation!</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
    <script src="profile.js"></script>
</body>
</html>