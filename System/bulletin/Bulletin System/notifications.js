// Notifications page functionality

document.addEventListener('DOMContentLoaded', function() {
    // Only run this code on the notifications page
    if (document.querySelector('.notifications-container')) {
        setupNotificationTabs();
    }
});

function setupNotificationTabs() {
    const tabs = document.querySelectorAll('.notification-tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Get the tab text for filtering
            const tabText = this.textContent.trim();
            
            // You can add filtering logic here when notifications are implemented
            console.log(`Switched to ${tabText} tab`);
        });
    });
    
    // Handle "Mark all as read" button
    const markAllReadBtn = document.querySelector('.mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            console.log('Marked all notifications as read');
            // Add logic to mark all notifications as read
        });
    }
}

// This function would be used to populate notifications when you have data
function populateNotifications(notifications) {
    const notificationsList = document.querySelector('.notifications-list');
    const emptyState = document.querySelector('.empty-notifications');
    
    // If there are notifications, hide empty state
    if (notifications && notifications.length > 0) {
        if (emptyState) emptyState.classList.add('hidden');
        notificationsList.classList.remove('empty');
        
        // Clear existing notifications except empty state
        const existingItems = notificationsList.querySelectorAll('.notification-item');
        existingItems.forEach(item => item.remove());
        
        // Add each notification
        notifications.forEach(notification => {
            const notificationItem = createNotificationItem(notification);
            notificationsList.appendChild(notificationItem);
        });
    } else {
        // Show empty state
        if (emptyState) emptyState.classList.remove('hidden');
        notificationsList.classList.add('empty');
    }
}

// Helper function to create notification item
function createNotificationItem(notification) {
    const item = document.createElement('div');
    item.className = 'notification-item';
    if (notification.unread) item.classList.add('unread');
    
    // Create icon element
    const iconElement = document.createElement('div');
    iconElement.className = 'notification-icon';
    
    // Set icon based on notification type
    let iconClass = 'fas fa-bell';
    if (notification.type === 'like') iconClass = 'fas fa-heart';
    else if (notification.type === 'mention') iconClass = 'fas fa-comment';
    
    iconElement.innerHTML = `<i class="${iconClass}"></i>`;
    
    // Create avatar element
    const avatarElement = document.createElement('div');
    avatarElement.className = 'notification-avatar';
    avatarElement.innerHTML = `<img src="${notification.avatar || 'img/avatar-placeholder.png'}" alt="User Avatar">`;
    
    // Create content element
    const contentElement = document.createElement('div');
    contentElement.className = 'notification-content';
    contentElement.innerHTML = `
        <p>${notification.content}</p>
        ${notification.time ? `<div class="time">${notification.time}</div>` : ''}
    `;
    
    // Append all elements to item
    item.appendChild(iconElement);
    item.appendChild(avatarElement);
    item.appendChild(contentElement);
    
    return item;
}