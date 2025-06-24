<?php
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