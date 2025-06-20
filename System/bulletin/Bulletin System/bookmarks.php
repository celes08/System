<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookmarks - CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="bookmarks.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        <!-- Main Content Area - Bookmarks -->
        <main class="main-content bookmarks-content">
            <div class="bookmarks-container">
                <div class="bookmarks-header">
                    <h1>Bookmarks</h1>
                    <button class="clear-all-bookmarks" id="clearAllBookmarks">
                        <i class="fas fa-trash"></i> Clear all bookmarks
                    </button>
                </div>
                
                <div class="bookmarks-tabs">
                    <button class="bookmark-tab active" data-filter="all">All</button>
                    <button class="bookmark-tab" data-filter="mentions">Mentions</button>
                </div>
                
                <div class="bookmarks-list" id="bookmarksList">
                    <!-- Bookmark items will be populated by JavaScript -->
                    <div class="bookmark-item" data-type="announcement">
                        <div class="bookmark-avatar">
                            <img src="img/avatar-placeholder.png" alt="User Avatar">
                        </div>
                        <div class="bookmark-content">
                            <div class="bookmark-header">
                                <span class="bookmark-author">Person</span>
                                <span class="bookmark-username">@person</span>
                                <span class="bookmark-date">May 7, 2025</span>
                            </div>
                            <div class="bookmark-title">CSHARP General Assembly</div>
                            <div class="bookmark-text">
                                General Assembly will be held on May 8 in the school gallery. All students, parents, and staff are invited to the opening reception from 9:00 AM to 12:00 PM. Refreshments will be served.
                            </div>
                            <div class="bookmark-tag">
                                <span class="tag dit">DIT</span>
                            </div>
                            <div class="bookmark-stats">
                                <span class="stat">
                                    <i class="fas fa-comment"></i> 2
                                </span>
                                <span class="stat">
                                    <i class="fas fa-heart"></i> 7
                                </span>
                                <span class="stat">
                                    <i class="fas fa-eye"></i> 15
                                </span>
                                <span class="stat">
                                    <i class="fas fa-share"></i> 6
                                </span>
                            </div>
                        </div>
                        <button class="remove-bookmark" data-id="1">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>

                    <div class="bookmark-item" data-type="announcement">
                        <div class="bookmark-avatar">
                            <img src="img/avatar-placeholder.png" alt="User Avatar">
                        </div>
                        <div class="bookmark-content">
                            <div class="bookmark-header">
                                <span class="bookmark-author">Person</span>
                                <span class="bookmark-username">@person</span>
                                <span class="bookmark-date">May 2, 2025</span>
                            </div>
                            <div class="bookmark-title">Capstone Project Defense Schedule Released!</div>
                            <div class="bookmark-text">
                                All graduating students must check the updated schedule posted on the IT Department website. Final defense starts on May 2, 2025.
                            </div>
                            <div class="bookmark-tag">
                                <span class="tag dit">DIT</span>
                            </div>
                            <div class="bookmark-stats">
                                <span class="stat">
                                    <i class="fas fa-comment"></i> 2
                                </span>
                                <span class="stat">
                                    <i class="fas fa-heart"></i> 15
                                </span>
                                <span class="stat">
                                    <i class="fas fa-eye"></i> 18
                                </span>
                                <span class="stat">
                                    <i class="fas fa-share"></i> 4
                                </span>
                            </div>
                        </div>
                        <button class="remove-bookmark" data-id="2">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>

                    <div class="bookmark-item" data-type="mention">
                        <div class="bookmark-avatar">
                            <img src="img/avatar-placeholder.png" alt="User Avatar">
                        </div>
                        <div class="bookmark-content">
                            <div class="bookmark-header">
                                <span class="bookmark-author">Person</span>
                                <span class="bookmark-username">@person</span>
                                <span class="bookmark-date">May 28, 2025</span>
                            </div>
                            <div class="bookmark-title">Science & Arts Journal</div>
                            <div class="bookmark-text">
                                Deadline: May 20, 2025. Open to all students and faculty.
                            </div>
                            <div class="bookmark-tag">
                                <span class="tag dit">DIT</span>
                            </div>
                            <div class="bookmark-stats">
                                <span class="stat">
                                    <i class="fas fa-comment"></i> 2
                                </span>
                                <span class="stat">
                                    <i class="fas fa-heart"></i> 15
                                </span>
                                <span class="stat">
                                    <i class="fas fa-eye"></i> 18
                                </span>
                                <span class="stat">
                                    <i class="fas fa-share"></i> 9
                                </span>
                            </div>
                        </div>
                        <button class="remove-bookmark" data-id="3">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Empty state (hidden by default) -->
                <div class="empty-bookmarks" id="emptyBookmarks" style="display: none;">
                    <i class="fas fa-bookmark empty-icon"></i>
                    <h2>No Bookmarks Yet</h2>
                    <p>You haven't bookmarked any posts yet. Start bookmarking posts to see them here.</p>
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