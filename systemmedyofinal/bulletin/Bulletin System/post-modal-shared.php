<?php
ob_start();
// Shared post modal functionality
// Include this file in any page that needs the post modal

// Handle post submission (modal)
$post_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitPost'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $department_raw = $_POST['department'] ?? '';
    $target_department_id = ($department_raw === 'all') ? null : intval($department_raw);
    $important = isset($_POST['important']) ? 1 : 0;
    $created_at = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? 1;

    // Get scheduled date/time
    $publish_date = $_POST['publish_date'] ?? '';
    $publish_time = $_POST['publish_time'] ?? '';
    if ($publish_date && $publish_time) {
        $scheduled_publish_at = $publish_date . ' ' . $publish_time . ':00';
        $is_scheduled = 1;
    } else {
        $scheduled_publish_at = null;
        $is_scheduled = 0;
    }

    // Check if user exists
    $user_check = $con->prepare("SELECT user_id FROM signuptbl WHERE user_id = ?");
    if (!$user_check) {
        $post_success = "Database error: " . $con->error;
    } else {
        $user_check->bind_param("i", $user_id);
        $user_check->execute();
        $user_check->store_result();
        if ($user_check->num_rows === 0) {
            $post_success = "User does not exist. Please log in again.";
        } else {
            $user_check->close();

            if ($title && isset($_POST['department'])) {
                $post_type = 'Department';
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

                if (!$stmt) {
                    $post_success = "Failed to prepare post: " . $con->error;
                } else {
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
                        $new_post_id = $con->insert_id;
                        $poster_id = $user_id;
                        $post_department = strtoupper($department_raw);
                        $notify_users = [];
                        if ($post_department === 'ALL') {
                            $user_q = $con->query("SELECT user_id FROM signuptbl WHERE user_id != $poster_id");
                            while ($row = $user_q->fetch_assoc()) {
                                $notify_users[] = $row['user_id'];
                            }
                            $user_q->close();
                        } else {
                            $user_q = $con->prepare("SELECT user_id FROM signuptbl WHERE UPPER(department) = ? AND user_id != ?");
                            $user_q->bind_param("si", $post_department, $poster_id);
                            $user_q->execute();
                            $user_q->bind_result($notify_user_id);
                            while ($user_q->fetch()) {
                                $notify_users[] = $notify_user_id;
                            }
                            $user_q->close();
                        }
                        foreach ($notify_users as $notify_user_id) {
                            $msg = "A new announcement has been posted!";
                            $type = "new_post";
                            $stmt_n = $con->prepare("INSERT INTO notifications (user_id, notification_type, message, related_post_id, is_read) VALUES (?, ?, ?, ?, 0)");
                            $stmt_n->bind_param("issi", $notify_user_id, $type, $msg, $new_post_id);
                            if (!$stmt_n->execute()) {
                                error_log("Notification insert error: " . $stmt_n->error);
                            }
                            $stmt_n->close();
                        }
                        error_log("Inserted post $new_post_id for department $post_department");
                        error_log("Notifying users: " . implode(',', $notify_users));
                        
                        // Redirect to dashboard after successful post
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $post_success = "Failed to post announcement: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                $post_success = "Title and department are required.";
            }
        }
    }
}

// Modal logic for post modal
$show_post_modal = isset($_POST['showPostModal']) || isset($_POST['submitPost']);
?>

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
// Post modal functionality - OPTIMIZED VERSION
document.addEventListener('DOMContentLoaded', function() {
    // Only add event listeners if they haven't been added already
    if (window.postModalInitialized) {
        return;
    }
    window.postModalInitialized = true;
    
    // Show post modal when post button is clicked
    const postButtons = document.querySelectorAll('.post-button');
    postButtons.forEach(button => {
        // Check if listener already exists
        if (!button.hasAttribute('data-modal-listener')) {
            button.setAttribute('data-modal-listener', 'true');
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const modal = document.getElementById('postModal');
                if (modal) {
                    modal.classList.add('active');
                    modal.style.display = 'flex';
                }
            });
        }
    });

    // Close modal when clicking outside
    const modalOverlays = document.querySelectorAll('.modal-overlay');
    modalOverlays.forEach(overlay => {
        if (!overlay.hasAttribute('data-close-listener')) {
            overlay.setAttribute('data-close-listener', 'true');
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                    this.style.display = 'none';
                }
            });
        }
    });

    // Close modal when clicking close button
    const closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
        if (!button.hasAttribute('data-close-listener')) {
            button.setAttribute('data-close-listener', 'true');
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const modal = this.closest('.modal-overlay');
                if (modal) {
                    modal.classList.remove('active');
                    modal.style.display = 'none';
                }
            });
        }
    });
});
</script>
<?php ob_end_flush(); ?> 