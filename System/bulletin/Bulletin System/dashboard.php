<?php
include("user_session.php");
requireLogin();
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
        <main class="main-content">
            <header class="content-header">
                <div class="header-title">
                    <h1>Home</h1>
                    <div class="tabs">
                        <button class="tab active">All</button>
                        <button class="tab">DIT</button>
                        <button class="tab">DOM</button>
                        <button class="tab">DAS</button>
                        <button class="tab">TED</button>
                    </div>
                </div>
                <div class="search-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search Announcements">
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <div class="posts-feed" id="postsFeed">
                    <!-- Sample posts with different departments -->
                    <article class="post-card" data-post-id="1" data-department="dit">
                        <div class="post-header">
                            <div class="post-avatar">
                                <img src="img/avatar-placeholder.png" alt="Person">
                            </div>
                            <div class="post-user-info">
                                <h4 class="post-author">Person</h4>
                                <p class="post-username">@person</p>
                                <span class="post-timestamp">May 7, 2025</span>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <h3 class="post-title">CSHARP General Assembly</h3>
                            <p class="post-text">General Assembly will be held on May 8 in the school gallery. All students, parents, and staff are invited to the opening reception from 9:00 AM to 12:00 PM. Refreshments will be served.</p>
                            <span class="post-department dit">DIT</span>
                        </div>
                        
                        <div class="post-actions">
                            <button class="action-btn comment-btn" data-post-id="1">
                                <i class="fas fa-comment"></i>
                                <span class="action-count">2</span>
                            </button>
                            <button class="action-btn like-btn" data-post-id="1">
                                <i class="fas fa-heart"></i>
                                <span class="action-count">7</span>
                            </button>
                            <button class="action-btn view-btn" data-post-id="1">
                                <i class="fas fa-eye"></i>
                                <span class="action-count">15</span>
                            </button>
                            <button class="action-btn bookmark-btn" data-post-id="1">
                                <i class="fas fa-bookmark"></i>
                                <span class="action-count">6</span>
                            </button>
                        </div>
                        
                        <div class="post-comments" id="comments-1" style="display: none;">
                            <div class="comments-list">
                                <div class="comment-item">
                                    <div class="comment-avatar">
                                        <img src="img/avatar-placeholder.png" alt="User">
                                    </div>
                                    <div class="comment-content">
                                        <div class="comment-header">
                                            <span class="comment-author">John Doe</span>
                                            <span class="comment-time">2 hours ago</span>
                                        </div>
                                        <p class="comment-text">Looking forward to this event!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="post-card" data-post-id="2" data-department="dom">
                        <div class="post-header">
                            <div class="post-avatar">
                                <img src="img/avatar-placeholder.png" alt="Person">
                            </div>
                            <div class="post-user-info">
                                <h4 class="post-author">Person</h4>
                                <p class="post-username">@person</p>
                                <span class="post-timestamp">May 5, 2025</span>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <h3 class="post-title">Management Workshop Series</h3>
                            <p class="post-text">Join us for a comprehensive management workshop series designed for business students and professionals. Topics include leadership, strategic planning, and team management.</p>
                            <span class="post-department dom">DOM</span>
                        </div>
                        
                        <div class="post-actions">
                            <button class="action-btn comment-btn" data-post-id="2">
                                <i class="fas fa-comment"></i>
                                <span class="action-count">8</span>
                            </button>
                            <button class="action-btn like-btn" data-post-id="2">
                                <i class="fas fa-heart"></i>
                                <span class="action-count">12</span>
                            </button>
                            <button class="action-btn view-btn" data-post-id="2">
                                <i class="fas fa-eye"></i>
                                <span class="action-count">25</span>
                            </button>
                            <button class="action-btn bookmark-btn" data-post-id="2">
                                <i class="fas fa-bookmark"></i>
                                <span class="action-count">3</span>
                            </button>
                        </div>
                        
                        <div class="post-comments" id="comments-2" style="display: none;">
                            <div class="comments-list"></div>
                        </div>
                    </article>

                    <article class="post-card" data-post-id="3" data-department="das">
                        <div class="post-header">
                            <div class="post-avatar">
                                <img src="img/avatar-placeholder.png" alt="Person">
                            </div>
                            <div class="post-user-info">
                                <h4 class="post-author">Person</h4>
                                <p class="post-username">@person</p>
                                <span class="post-timestamp">May 4, 2025</span>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <h3 class="post-title">Arts & Sciences Research Symposium</h3>
                            <p class="post-text">Annual research symposium showcasing student and faculty research projects. Presentations will cover various fields including natural sciences, social sciences, and humanities.</p>
                            <span class="post-department das">DAS</span>
                        </div>
                        
                        <div class="post-actions">
                            <button class="action-btn comment-btn" data-post-id="3">
                                <i class="fas fa-comment"></i>
                                <span class="action-count">5</span>
                            </button>
                            <button class="action-btn like-btn" data-post-id="3">
                                <i class="fas fa-heart"></i>
                                <span class="action-count">18</span>
                            </button>
                            <button class="action-btn view-btn" data-post-id="3">
                                <i class="fas fa-eye"></i>
                                <span class="action-count">32</span>
                            </button>
                            <button class="action-btn bookmark-btn" data-post-id="3">
                                <i class="fas fa-bookmark"></i>
                                <span class="action-count">7</span>
                            </button>
                        </div>
                        
                        <div class="post-comments" id="comments-3" style="display: none;">
                            <div class="comments-list"></div>
                        </div>
                    </article>

                    <article class="post-card" data-post-id="4" data-department="ted">
                        <div class="post-header">
                            <div class="post-avatar">
                                <img src="img/avatar-placeholder.png" alt="Person">
                            </div>
                            <div class="post-user-info">
                                <h4 class="post-author">Person</h4>
                                <p class="post-username">@person</p>
                                <span class="post-timestamp">May 3, 2025</span>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <h3 class="post-title">Teacher Education Practicum Guidelines</h3>
                            <p class="post-text">Updated guidelines for student teaching practicum. All education majors must review the new requirements and deadlines for the upcoming semester.</p>
                            <span class="post-department ted">TED</span>
                        </div>
                        
                        <div class="post-actions">
                            <button class="action-btn comment-btn" data-post-id="4">
                                <i class="fas fa-comment"></i>
                                <span class="action-count">12</span>
                            </button>
                            <button class="action-btn like-btn" data-post-id="4">
                                <i class="fas fa-heart"></i>
                                <span class="action-count">9</span>
                            </button>
                            <button class="action-btn view-btn" data-post-id="4">
                                <i class="fas fa-eye"></i>
                                <span class="action-count">28</span>
                            </button>
                            <button class="action-btn bookmark-btn" data-post-id="4">
                                <i class="fas fa-bookmark"></i>
                                <span class="action-count">4</span>
                            </button>
                        </div>
                        
                        <div class="post-comments" id="comments-4" style="display: none;">
                            <div class="comments-list"></div>
                        </div>
                    </article>

                    <article class="post-card" data-post-id="5" data-department="all">
                        <div class="post-header">
                            <div class="post-avatar">
                                <img src="img/avatar-placeholder.png" alt="Person">
                            </div>
                            <div class="post-user-info">
                                <h4 class="post-author">Person</h4>
                                <p class="post-username">@person</p>
                                <span class="post-timestamp">May 2, 2025</span>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <h3 class="post-title">University-wide Announcement</h3>
                            <p class="post-text">Important announcement for all students and faculty across all departments. Please read the updated university policies and procedures.</p>
                            <span class="post-department all">ALL DEPARTMENTS</span>
                        </div>
                        
                        <div class="post-actions">
                            <button class="action-btn comment-btn" data-post-id="5">
                                <i class="fas fa-comment"></i>
                                <span class="action-count">25</span>
                            </button>
                            <button class="action-btn like-btn" data-post-id="5">
                                <i class="fas fa-heart"></i>
                                <span class="action-count">45</span>
                            </button>
                            <button class="action-btn view-btn" data-post-id="5">
                                <i class="fas fa-eye"></i>
                                <span class="action-count">120</span>
                            </button>
                            <button class="action-btn bookmark-btn" data-post-id="5">
                                <i class="fas fa-bookmark"></i>
                                <span class="action-count">15</span>
                            </button>
                        </div>
                        
                        <div class="post-comments" id="comments-5" style="display: none;">
                            <div class="comments-list"></div>
                        </div>
                    </article>
                </div>
            </div>
        </main>
        
        <!-- Right Sidebar - Calendar -->
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

    <!-- Post Modal -->
    <div class="modal-overlay" id="postModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>New Announcement</h2>
                <button class="modal-close" id="modalClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <form id="postForm">
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
                                <option value="ALL">All Departments</option>
                                <option value="DIT">DIT Only</option>
                                <option value="DOM">DOM Only</option>
                                <option value="DAS">DAS Only</option>
                                <option value="TED">TED Only</option>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <div class="action-buttons">
                            <button type="button" class="action-btn" title="Add Image">
                                <i class="fas fa-image"></i>
                            </button>
                            <button type="button" class="action-btn" title="Add Link">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                        
                        <div class="form-options">
                            <label class="checkbox-container">
                                <input type="checkbox" id="markImportant" name="important">
                                <span class="checkmark"></span>
                                Mark as important
                            </label>
                            
                            <button type="submit" class="post-submit-btn">
                                Post
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Comment Modal -->
    <div class="modal-overlay" id="commentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Comment</h2>
                <button class="modal-close" id="commentModalClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <form id="commentForm">
                    <div class="form-group">
                        <label for="commentText">Your Comment</label>
                        <textarea id="commentText" name="comment" placeholder="Write your comment..." rows="4" required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <div class="form-options">
                            <button type="submit" class="post-submit-btn">
                                Post Comment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script src="tab-filtering.js"></script>
    <script src="post-modal.js"></script>
    <script src="comments.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.bookmark-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const postCard = btn.closest('.post-card');
                const postId = postCard.getAttribute('data-post-id');
                const department = postCard.getAttribute('data-department');
                const title = postCard.querySelector('.post-title').textContent;
                const text = postCard.querySelector('.post-text').textContent;
                const author = postCard.querySelector('.post-author').textContent;
                const username = postCard.querySelector('.post-username').textContent;
                const date = postCard.querySelector('.post-timestamp').textContent;

                // Build bookmark object
                const bookmark = {
                    id: postId,
                    department,
                    title,
                    text,
                    author,
                    username,
                    date
                };

                // Get current bookmarks
                let bookmarks = JSON.parse(localStorage.getItem('bookmarks') || '[]');
                // Prevent duplicates
                if (!bookmarks.some(b => b.id === postId)) {
                    bookmarks.push(bookmark);
                    localStorage.setItem('bookmarks', JSON.stringify(bookmarks));
                    alert('Bookmarked!');
                } else {
                    alert('Already bookmarked!');
                }
            });
        });
    });
    </script>
</body>
</html>