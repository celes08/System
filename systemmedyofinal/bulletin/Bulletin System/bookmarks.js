// Bookmarks Page Functionality

document.addEventListener('DOMContentLoaded', function() {
    const bookmarksList = document.getElementById('bookmarksList');
    const emptyBookmarks = document.getElementById('emptyBookmarks');
    const clearAllButton = document.getElementById('clearAllBookmarks');
    const bookmarkTabs = document.querySelectorAll('.bookmark-tab');
    const bookmarksTab = document.querySelector('.sidebar-nav li.active a'); // Bookmarks link in sidebar
    
    let currentFilter = 'all';
    let bookmarkedPosts = JSON.parse(localStorage.getItem('bookmarkedPosts')) || [];
    let bookmarkData = JSON.parse(localStorage.getItem('bookmarkData')) || {};

    // Initialize bookmarks page
    loadBookmarks();
    updateClearAllButton();
    updateBookmarkCount();

    // Tab filtering
    bookmarkTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            bookmarkTabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            this.classList.add('active');
            
            currentFilter = this.getAttribute('data-filter');
            filterBookmarks();
        });
    });

    // Clear all bookmarks
    clearAllButton.addEventListener('click', function() {
        if (bookmarkedPosts.length === 0) return;
        
        if (confirm('Are you sure you want to clear all bookmarks? This action cannot be undone.')) {
            clearAllBookmarks();
        }
    });

    // Remove individual bookmark
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-bookmark')) {
            const button = e.target.closest('.remove-bookmark');
            const postId = button.getAttribute('data-id');
            removeBookmark(postId);
        }
    });

    function loadBookmarks() {
        if (bookmarkedPosts.length === 0) {
            showEmptyState();
            return;
        }

        hideEmptyState();
        renderBookmarks();
    }

    function renderBookmarks() {
        // Clear existing bookmarks (except sample ones, replace them)
        bookmarksList.innerHTML = '';

        bookmarkedPosts.forEach(postId => {
            const postData = bookmarkData[postId];
            if (postData) {
                const bookmarkItem = createBookmarkElement(postId, postData);
                bookmarksList.appendChild(bookmarkItem);
            }
        });

        // If no bookmarks from dashboard, show sample bookmarks
        if (bookmarkedPosts.length === 0) {
            loadSampleBookmarks();
        }

        updateBookmarkCount();
    }

    function createBookmarkElement(postId, data) {
        const bookmarkItem = document.createElement('div');
        bookmarkItem.className = 'bookmark-item';
        bookmarkItem.setAttribute('data-type', 'announcement');
        bookmarkItem.setAttribute('data-id', postId);
        
        bookmarkItem.innerHTML = `
            <div class="bookmark-avatar">
                <img src="img/avatar-placeholder.png" alt="User Avatar">
            </div>
            <div class="bookmark-content">
                <div class="bookmark-header">
                    <span class="bookmark-author">${data.author}</span>
                    <span class="bookmark-username">${data.username}</span>
                    <span class="bookmark-date">${data.timestamp}</span>
                </div>
                <div class="bookmark-title">${data.title}</div>
                <div class="bookmark-text">${data.text}</div>
                <div class="bookmark-tag">
                    <span class="tag ${data.departmentClass}">${data.department}</span>
                </div>
                <div class="bookmark-stats">
                    <span class="stat">
                        <i class="fas fa-comment"></i> ${data.stats.comments}
                    </span>
                    <span class="stat">
                        <i class="fas fa-heart"></i> ${data.stats.likes}
                    </span>
                    <span class="stat">
                        <i class="fas fa-eye"></i> ${data.stats.views}
                    </span>
                    <span class="stat">
                        <i class="fas fa-share"></i> ${data.stats.bookmarks}
                    </span>
                </div>
            </div>
            <button class="remove-bookmark" data-id="${postId}">
                <i class="fas fa-bookmark"></i>
            </button>
        `;

        // Add fade-in animation
        bookmarkItem.style.opacity = '0';
        bookmarkItem.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            bookmarkItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            bookmarkItem.style.opacity = '1';
            bookmarkItem.style.transform = 'translateY(0)';
        }, 10);

        return bookmarkItem;
    }

    function loadSampleBookmarks() {
        // Keep the existing sample bookmarks if no real bookmarks exist
        const sampleBookmarks = [
            {
                id: 'sample-1',
                author: 'Person',
                username: '@person',
                timestamp: 'May 7, 2025',
                title: 'CSHARP General Assembly',
                text: 'General Assembly will be held on May 8 in the school gallery. All students, parents, and staff are invited to the opening reception from 9:00 AM to 12:00 PM. Refreshments will be served.',
                department: 'DIT',
                departmentClass: 'dit',
                stats: { comments: '2', likes: '7', views: '15', bookmarks: '6' }
            },
            {
                id: 'sample-2',
                author: 'Person',
                username: '@person',
                timestamp: 'May 2, 2025',
                title: 'Capstone Project Defense Schedule Released!',
                text: 'All graduating students must check the updated schedule posted on the IT Department website. Final defense starts on May 2, 2025.',
                department: 'DIT',
                departmentClass: 'dit',
                stats: { comments: '2', likes: '15', views: '18', bookmarks: '4' }
            },
            {
                id: 'sample-3',
                author: 'Person',
                username: '@person',
                timestamp: 'May 28, 2025',
                title: 'Science & Arts Journal',
                text: 'Deadline: May 20, 2025. Open to all students and faculty.',
                department: 'DIT',
                departmentClass: 'dit',
                stats: { comments: '2', likes: '15', views: '18', bookmarks: '9' }
            }
        ];

        sampleBookmarks.forEach(bookmark => {
            const bookmarkItem = createBookmarkElement(bookmark.id, bookmark);
            bookmarksList.appendChild(bookmarkItem);
        });
    }

    function removeBookmark(postId) {
        // Add fade-out animation
        const bookmarkItem = document.querySelector(`[data-id="${postId}"]`);
        if (bookmarkItem) {
            bookmarkItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            bookmarkItem.style.opacity = '0';
            bookmarkItem.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                bookmarkItem.remove();
                
                // Check if bookmarks list is empty
                if (bookmarksList.children.length === 0) {
                    showEmptyState();
                }
                
                updateClearAllButton();
            }, 300);
        }

        // Remove from localStorage
        bookmarkedPosts = bookmarkedPosts.filter(id => id !== postId);
        localStorage.setItem('bookmarkedPosts', JSON.stringify(bookmarkedPosts));
        
        delete bookmarkData[postId];
        localStorage.setItem('bookmarkData', JSON.stringify(bookmarkData));

        // Update bookmark button on dashboard if page is open
        const dashboardBookmarkBtn = document.querySelector(`.bookmark-btn[data-post-id="${postId}"]`);
        if (dashboardBookmarkBtn) {
            dashboardBookmarkBtn.classList.remove('bookmarked');
            const countSpan = dashboardBookmarkBtn.querySelector('.action-count');
            if (countSpan) {
                const currentCount = parseInt(countSpan.textContent);
                countSpan.textContent = Math.max(0, currentCount - 1);
            }
        }

        showNotification('Bookmark removed successfully', 'info');

        renderBookmarks();
    }

    function clearAllBookmarks() {
        // Add fade-out animation to all items
        const bookmarkItems = bookmarksList.querySelectorAll('.bookmark-item');
        bookmarkItems.forEach((item, index) => {
            setTimeout(() => {
                item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                item.style.opacity = '0';
                item.style.transform = 'translateY(-10px)';
            }, index * 50);
        });

        setTimeout(() => {
            bookmarksList.innerHTML = '';
            showEmptyState();
            updateClearAllButton();
        }, bookmarkItems.length * 50 + 300);

        // Clear localStorage
        localStorage.removeItem('bookmarkedPosts');
        localStorage.removeItem('bookmarkData');
        bookmarkedPosts = [];
        bookmarkData = {};

        showNotification('All bookmarks cleared', 'info');

        renderBookmarks();
    }

    function filterBookmarks() {
        const bookmarkItems = bookmarksList.querySelectorAll('.bookmark-item');
        
        bookmarkItems.forEach(item => {
            const itemType = item.getAttribute('data-type');
            
            if (currentFilter === 'all' || itemType === currentFilter) {
                item.style.display = 'flex';
                item.classList.remove('hidden');
            } else {
                item.style.display = 'none';
                item.classList.add('hidden');
            }
        });

        // Check if any items are visible
        const visibleItems = bookmarksList.querySelectorAll('.bookmark-item:not(.hidden)');
        if (visibleItems.length === 0) {
            showEmptyState();
        } else {
            hideEmptyState();
        }
    }

    function showEmptyState() {
        emptyBookmarks.style.display = 'flex';
        bookmarksList.style.display = 'none';
    }

    function hideEmptyState() {
        emptyBookmarks.style.display = 'none';
        bookmarksList.style.display = 'block';
    }

    function updateClearAllButton() {
        if (bookmarkedPosts.length === 0) {
            clearAllButton.disabled = true;
            clearAllButton.style.opacity = '0.5';
        } else {
            clearAllButton.disabled = false;
            clearAllButton.style.opacity = '1';
        }
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    function updateBookmarkCount() {
        let bookmarks = JSON.parse(localStorage.getItem('bookmarks') || '[]');
        let badge = document.getElementById('bookmarkCountBadge');
        if (!badge) {
            badge = document.createElement('span');
            badge.id = 'bookmarkCountBadge';
            badge.style.background = '#dc3545';
            badge.style.color = '#fff';
            badge.style.borderRadius = '50%';
            badge.style.padding = '2px 8px';
            badge.style.fontSize = '0.8rem';
            badge.style.marginLeft = '8px';
            badge.style.verticalAlign = 'middle';
            bookmarksTab.appendChild(badge);
        }
        badge.textContent = bookmarks.length;
        badge.style.display = bookmarks.length > 0 ? 'inline-block' : 'none';
    }

    // Listen for storage changes (when bookmarks are added from dashboard)
    window.addEventListener('storage', function(e) {
        if (e.key === 'bookmarkedPosts' || e.key === 'bookmarkData') {
            bookmarkedPosts = JSON.parse(localStorage.getItem('bookmarkedPosts')) || [];
            bookmarkData = JSON.parse(localStorage.getItem('bookmarkData')) || {};
            loadBookmarks();
            updateClearAllButton();
        }
    });
});