// Help Page Functionality

document.addEventListener('DOMContentLoaded', function() {
    const helpTabs = document.querySelectorAll('.help-tab');
    const submitTicketBtn = document.getElementById('submitTicketBtn');
    const ticketModal = document.getElementById('ticketModal');
    const ticketModalClose = document.getElementById('ticketModalClose');
    const ticketForm = document.getElementById('ticketForm');
    const helpTickets = document.querySelectorAll('.help-ticket');

    // Initialize tab functionality
    initializeTabs();
    initializeModal();

    function initializeTabs() {
        helpTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                helpTabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Get filter value
                const filterValue = this.getAttribute('data-status');
                
                // Filter tickets
                filterTickets(filterValue);
            });
        });
    }

    function filterTickets(status) {
        helpTickets.forEach(ticket => {
            const ticketStatus = ticket.getAttribute('data-status');
            
            if (status === 'all') {
                // Show all tickets
                ticket.style.display = 'flex';
                ticket.classList.remove('hidden');
            } else {
                // Show only tickets matching the status filter
                if (ticketStatus === status) {
                    ticket.style.display = 'flex';
                    ticket.classList.remove('hidden');
                } else {
                    ticket.style.display = 'none';
                    ticket.classList.add('hidden');
                }
            }
        });

        // Check if any tickets are visible
        const visibleTickets = document.querySelectorAll('.help-ticket:not(.hidden)');
        toggleEmptyState(visibleTickets.length === 0, status);
    }

    function toggleEmptyState(show, status) {
        const helpTicketsContainer = document.getElementById('helpTickets');
        let emptyState = helpTicketsContainer.querySelector('.empty-state');
        
        if (show && !emptyState) {
            // Create empty state
            emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            
            let message = 'No tickets found';
            let icon = 'fas fa-ticket-alt';
            
            switch(status) {
                case 'pending':
                    message = 'No pending tickets';
                    icon = 'fas fa-clock';
                    break;
                case 'resolved':
                    message = 'No resolved tickets';
                    icon = 'fas fa-check-circle';
                    break;
                default:
                    message = 'No tickets found';
                    icon = 'fas fa-ticket-alt';
            }
            
            emptyState.innerHTML = `
                <i class="${icon}"></i>
                <h3>${message}</h3>
                <p>There are no help tickets to display for this filter.</p>
            `;
            
            helpTicketsContainer.appendChild(emptyState);
        } else if (!show && emptyState) {
            // Remove empty state
            emptyState.remove();
        }
    }

    function initializeModal() {
        // Open modal when submit ticket button is clicked
        submitTicketBtn.addEventListener('click', function() {
            openTicketModal();
        });

        // Close modal when close button is clicked
        ticketModalClose.addEventListener('click', function() {
            closeTicketModal();
        });

        // Close modal when clicking outside the modal content
        ticketModal.addEventListener('click', function(e) {
            if (e.target === ticketModal) {
                closeTicketModal();
            }
        });

        // Close modal when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && ticketModal.classList.contains('active')) {
                closeTicketModal();
            }
        });

        // Handle form submission
        ticketForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleTicketSubmission();
        });
    }

    function openTicketModal() {
        ticketModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus on the subject input
        setTimeout(() => {
            document.getElementById('ticketSubject').focus();
        }, 300);
    }

    function closeTicketModal() {
        ticketModal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Reset form
        ticketForm.reset();
    }

    function handleTicketSubmission() {
        // Get form data
        const formData = new FormData(ticketForm);
        const ticketData = {
            subject: formData.get('subject'),
            category: formData.get('category'),
            description: formData.get('description'),
            priority: formData.get('priority'),
            timestamp: new Date().toISOString(),
            id: 'ticket-' + Date.now(),
            status: 'pending'
        };

        // Validate required fields
        if (!ticketData.subject.trim()) {
            showNotification('Subject is required', 'error');
            return;
        }

        if (!ticketData.category) {
            showNotification('Please select a category', 'error');
            return;
        }

        if (!ticketData.description.trim()) {
            showNotification('Description is required', 'error');
            return;
        }

        if (!ticketData.priority) {
            showNotification('Please select a priority', 'error');
            return;
        }

        // Show loading state
        const submitBtn = document.querySelector('.submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;

        // Simulate API call
        setTimeout(() => {
            // Reset button
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;

            // Show success message
            showNotification('Help ticket submitted successfully!', 'success');

            // Close modal
            closeTicketModal();

            // Add the ticket to the help page
            addTicketToPage(ticketData);
            
            // Switch to pending tab to show the new ticket
            switchToPendingTab();
        }, 1500);
    }

    function addTicketToPage(ticketData) {
        const helpTicketsContainer = document.getElementById('helpTickets');
        
        // Create new ticket element
        const newTicket = createTicketElement(ticketData);
        
        // Add the new ticket at the beginning
        helpTicketsContainer.insertBefore(newTicket, helpTicketsContainer.firstChild);
        
        // Add animation
        newTicket.style.opacity = '0';
        newTicket.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            newTicket.style.transition = 'all 0.3s ease';
            newTicket.style.opacity = '1';
            newTicket.style.transform = 'translateY(0)';
        }, 100);
    }

    function createTicketElement(ticketData) {
        const ticketElement = document.createElement('div');
        ticketElement.className = 'help-ticket';
        ticketElement.setAttribute('data-status', ticketData.status);
        
        // Format timestamp
        const timeString = 'Just now';
        
        // Get priority color
        const priorityColors = {
            low: '#10b981',
            medium: '#f59e0b',
            high: '#ef4444',
            urgent: '#dc2626'
        };
        
        ticketElement.innerHTML = `
            <div class="ticket-avatar">
                <img src="img/avatar-placeholder.png" alt="Person">
            </div>
            <div class="ticket-content">
                <div class="ticket-header">
                    <div class="ticket-user-info">
                        <span class="ticket-author">Person</span>
                        <span class="ticket-username">@person</span>
                        <span class="ticket-date">${timeString}</span>
                    </div>
                </div>
                <div class="ticket-body">
                    <h3 class="ticket-title">${ticketData.subject}</h3>
                    <p class="ticket-description">${ticketData.description}</p>
                    <span class="ticket-status ${ticketData.status}">${ticketData.status.charAt(0).toUpperCase() + ticketData.status.slice(1)}</span>
                </div>
            </div>
        `;

        return ticketElement;
    }

    function switchToPendingTab() {
        const pendingTab = document.querySelector('.help-tab[data-status="pending"]');
        if (pendingTab) {
            pendingTab.click();
        }
    }

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        // Add styles
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
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Auto-resize textarea
    const textarea = document.getElementById('ticketDescription');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
});