// Enhanced Tab Filtering Functionality

document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab');
    let currentFilter = 'all';

    // Initialize tab functionality
    initializeTabs();

    function initializeTabs() {
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Get filter value
                const filterValue = this.textContent.trim().toLowerCase();
                currentFilter = filterValue;
                
                // Filter posts
                filterPosts(filterValue);
            });
        });
    }

    function filterPosts(filter) {
        const postCards = document.querySelectorAll('.post-card');
        
        postCards.forEach(post => {
            const postDepartment = post.getAttribute('data-department');
            
            if (filter === 'all') {
                // Show all posts
                post.style.display = 'block';
                post.classList.remove('hidden');
            } else {
                // Show posts matching the department filter OR posts marked for all departments
                if (postDepartment === filter || postDepartment === 'all') {
                    post.style.display = 'block';
                    post.classList.remove('hidden');
                } else {
                    post.style.display = 'none';
                    post.classList.add('hidden');
                }
            }
        });

        // Check if any posts are visible
        const visiblePosts = document.querySelectorAll('.post-card:not(.hidden)');
        toggleEmptyState(visiblePosts.length === 0);
    }

    function toggleEmptyState(show) {
        const postsFeed = document.getElementById('postsFeed');
        let emptyState = postsFeed.querySelector('.empty-dashboard');
        
        if (show && !emptyState) {
            // Create empty state
            emptyState = document.createElement('div');
            emptyState.className = 'empty-dashboard';
            emptyState.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-clipboard-list empty-icon"></i>
                    <h2>No Announcements Yet</h2>
                    <p>There are no announcements to display for this filter.</p>
                </div>
            `;
            
            // Style the empty state
            emptyState.style.cssText = `
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 100px 20px;
                text-align: center;
                min-height: 400px;
            `;
            
            const emptyIcon = emptyState.querySelector('.empty-icon');
            if (emptyIcon) {
                emptyIcon.style.cssText = `
                    font-size: 48px;
                    color: #adb5bd;
                    margin-bottom: 16px;
                `;
            }
            
            const emptyTitle = emptyState.querySelector('h2');
            if (emptyTitle) {
                emptyTitle.style.cssText = `
                    margin: 0 0 8px 0;
                    font-size: 20px;
                    color: #495057;
                `;
            }
            
            const emptyText = emptyState.querySelector('p');
            if (emptyText) {
                emptyText.style.cssText = `
                    margin: 0;
                    font-size: 14px;
                    color: #6c757d;
                    max-width: 400px;
                `;
            }
            
            postsFeed.appendChild(emptyState);
        } else if (!show && emptyState) {
            // Remove empty state
            emptyState.remove();
        }
    }

    // Export function for use in other scripts
    window.tabFiltering = {
        getCurrentFilter: () => currentFilter,
        filterPosts: filterPosts,
        switchToAll: () => {
            const allTab = Array.from(tabs).find(tab => 
                tab.textContent.trim().toLowerCase() === 'all'
            );
            if (allTab) {
                allTab.click();
            }
        },
        switchToTab: (tabName) => {
            const targetTab = Array.from(tabs).find(tab => 
                tab.textContent.trim().toLowerCase() === tabName.toLowerCase()
            );
            if (targetTab) {
                targetTab.click();
            }
        }
    };
});

//recently added stuff here
// Tab filtering functionality for dashboard

document.addEventListener("DOMContentLoaded", () => {
    // Only run on dashboard page
    if (!document.body.classList.contains("dashboard-body")) return
  
    const tabs = document.querySelectorAll(".content-header .tab")
    const posts = document.querySelectorAll(".post-card")
  
    // Set default active tab to "All"
    let currentFilter = "all"
  
    // Initialize tab filtering
    tabs.forEach((tab) => {
      tab.addEventListener("click", function () {
        // Remove active class from all tabs
        tabs.forEach((t) => t.classList.remove("active"))
  
        // Add active class to clicked tab
        this.classList.add("active")
  
        // Get filter value
        const tabText = this.textContent.trim().toLowerCase()
        currentFilter = tabText === "all" ? "all" : tabText
  
        // Filter posts
        filterPosts(currentFilter)
      })
    })
  
    // Initial filter - show all posts
    filterPosts("all")
  
    function filterPosts(filter) {
      posts.forEach((post) => {
        const postDepartment = post.getAttribute("data-department")
  
        if (filter === "all" || postDepartment === filter || postDepartment === "all") {
          post.style.display = "block"
          post.classList.remove("filtered-out")
        } else {
          post.style.display = "none"
          post.classList.add("filtered-out")
        }
      })
    }
  
    // Export function for use in other scripts
    window.tabFiltering = {
      switchToTab: (tabName) => {
        const targetTab = document.querySelector(`.tab:nth-child(${getTabIndex(tabName)})`)
        if (targetTab) {
          targetTab.click()
        }
      },
    }
  
    function getTabIndex(tabName) {
      const tabMap = {
        all: 1,
        dit: 2,
        dom: 3,
        das: 4,
        ted: 5,
      }
      return tabMap[tabName.toLowerCase()] || 1
    }
  })
  