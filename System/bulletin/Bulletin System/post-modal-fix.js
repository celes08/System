// Post Modal Fix - Consolidates all post modal functionality and prevents conflicts
document.addEventListener('DOMContentLoaded', function() {
    // Remove any existing event listeners to prevent duplicates
    const postButtons = document.querySelectorAll('.post-button, #postButton');
    
    postButtons.forEach(button => {
        // Remove existing listeners by cloning the element
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        // Add single event listener with proper event handling
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Only open modal if we're on the dashboard page
            if (window.location.pathname.includes('dashboard') || 
                document.body.classList.contains('dashboard-body')) {
                
                const modal = document.getElementById('postModal');
                if (modal && window.openModal) {
                    window.openModal();
                } else if (modal) {
                    modal.classList.add('active');
                    modal.style.display = 'flex';
                }
            } else {
                // Redirect to dashboard for posting
                window.location.href = 'dashboard.html';
            }
        });
    });
    
    // Prevent modal from opening when clicking on other elements
    document.addEventListener('click', function(e) {
        // If clicking on a post button, let it handle its own event
        if (e.target.closest('.post-button') || e.target.closest('#postButton')) {
            return;
        }
        
        // If clicking on modal elements, don't interfere
        if (e.target.closest('.modal-overlay') || e.target.closest('.modal-content')) {
            return;
        }
        
        // If clicking on action buttons, don't interfere
        if (e.target.closest('.action-btn') || e.target.closest('.comment-btn') || 
            e.target.closest('.like-btn') || e.target.closest('.bookmark-btn')) {
            return;
        }
        
        // For all other clicks, ensure post modal is closed
        const postModal = document.getElementById('postModal');
        if (postModal && postModal.classList.contains('active')) {
            // Don't close if clicking inside the modal
            if (!e.target.closest('.modal-content')) {
                postModal.classList.remove('active');
                postModal.style.display = 'none';
            }
        }
    });
    
    // Optimize modal close functionality
    const modalOverlays = document.querySelectorAll('.modal-overlay');
    modalOverlays.forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                this.style.display = 'none';
            }
        });
    });
    
    const closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const modal = this.closest('.modal-overlay');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
        });
    });
    
    // Add escape key functionality
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-overlay.active');
            if (activeModal) {
                activeModal.classList.remove('active');
                activeModal.style.display = 'none';
            }
        }
    });
}); 