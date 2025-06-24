// Make openModal function globally available
window.openModal = () => {
    const postModal = document.getElementById("postModal")
    const dashboardContainer = document.getElementById("dashboardContainer")
  
    if (postModal) {
      postModal.classList.add("active")
      if (dashboardContainer) {
        dashboardContainer.classList.add("modal-open")
      }
      document.body.style.overflow = "hidden"
  
      // Focus on the title input
      setTimeout(() => {
        const titleInput = document.getElementById("postTitle")
        if (titleInput) titleInput.focus()
      }, 300)
    }
  }
  
  // Enhanced Post Modal Functionality - OPTIMIZED VERSION
  
  document.addEventListener("DOMContentLoaded", () => {
    // Check if already initialized to prevent duplicates
    if (window.postModalMainInitialized) {
      return;
    }
    window.postModalMainInitialized = true;
    
    const postButton = document.getElementById("postButton")
    const postModal = document.getElementById("postModal")
    const modalClose = document.getElementById("modalClose")
    const dashboardContainer = document.getElementById("dashboardContainer")
    const postForm = document.getElementById("postForm")
  
    // Open modal when post button is clicked
    if (postButton && !postButton.hasAttribute("data-main-listener")) {
      postButton.setAttribute("data-main-listener", "true")
      postButton.addEventListener("click", (e) => {
        e.preventDefault()
        e.stopPropagation()
        openModal()
      })
    }
  
    // Close modal when close button is clicked
    if (modalClose && !modalClose.hasAttribute("data-main-close-listener")) {
      modalClose.setAttribute("data-main-close-listener", "true")
      modalClose.addEventListener("click", () => {
        closeModal()
      })
    }
  
    // Close modal when clicking outside the modal content
    if (postModal && !postModal.hasAttribute("data-main-overlay-listener")) {
      postModal.setAttribute("data-main-overlay-listener", "true")
      postModal.addEventListener("click", (e) => {
        if (e.target === postModal) {
          closeModal()
        }
      })
    }
  
    // Close modal when pressing Escape key
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && postModal && postModal.classList.contains("active")) {
        closeModal()
      }
    })
  
    // Handle form submission
    if (postForm && !postForm.hasAttribute("data-main-form-listener")) {
      postForm.setAttribute("data-main-form-listener", "true")
      postForm.addEventListener("submit", (e) => {
        e.preventDefault()
        handlePostSubmission()
      })
    }
  
    function openModal() {
      console.log("Opening post modal...")
      window.openModal()
    }
  
    function closeModal() {
      console.log("Closing post modal...")
      postModal.classList.remove("active")
      dashboardContainer.classList.remove("modal-open")
      document.body.style.overflow = "" // Restore scrolling
  
      // Reset form
      postForm.reset()
    }
  
    function handlePostSubmission() {
      // Get form data
      const formData = new FormData(postForm)
      const postData = {
        title: formData.get("title"),
        content: formData.get("content"),
        department: formData.get("department"),
        important: formData.get("important") === "on",
        timestamp: new Date().toISOString(),
        id: "post-" + Date.now(), // Generate unique ID
      }
  
      // Validate required fields
      if (!postData.title.trim()) {
        showError("Title is required")
        return
      }
  
      if (!postData.department) {
        showError("Please select target audience")
        return
      }
  
      // Show loading state
      const submitBtn = document.querySelector(".post-submit-btn")
      const originalText = submitBtn.textContent
      submitBtn.textContent = "Posting..."
      submitBtn.disabled = true
  
      // Simulate API call
      setTimeout(() => {
        // Reset button
        submitBtn.textContent = originalText
        submitBtn.disabled = false
  
        // Show success message
        showSuccess("Announcement posted successfully!")
  
        // Close modal
        closeModal()
  
        // Add the post to the dashboard
        addPostToDashboard(postData)
  
        // Switch to appropriate tab based on post target
        switchToAppropriateTab(postData.department)
  
        // Scroll to top to show the new post
        scrollToTop()
      }, 1500)
    }
  
    function addPostToDashboard(postData) {
      const postsFeed = document.getElementById("postsFeed")
  
      // Create new post element
      const newPost = createPostElement(postData)
  
      // Add the new post at the beginning of the feed
      postsFeed.insertBefore(newPost, postsFeed.firstChild)
  
      // Add animation class
      newPost.classList.add("new-post")
  
      // Remove animation class after animation completes
      setTimeout(() => {
        newPost.classList.remove("new-post")
      }, 500)
    }
  
    function createPostElement(postData) {
      const postElement = document.createElement("article")
      postElement.className = "post-card"
      postElement.setAttribute("data-post-id", postData.id)
  
      // Set department data attribute based on selection
      const departmentData = postData.department === "ALL" ? "all" : postData.department.toLowerCase()
      postElement.setAttribute("data-department", departmentData)
  
      // Format timestamp
      const timeString = "Just now"
  
      // Determine department class and display text
      const departmentClass = departmentData
      const departmentDisplay = postData.department === "ALL" ? "ALL DEPARTMENTS" : postData.department
  
      postElement.innerHTML = `
              <div class="post-header">
                  <div class="post-avatar">
                      <img src="img/avatar-placeholder.png" alt="Person">
                  </div>
                  <div class="post-user-info">
                      <h4 class="post-author">Person</h4>
                      <p class="post-username">@person</p>
                      <span class="post-timestamp">${timeString}</span>
                  </div>
                  ${postData.important ? '<span class="important-badge">Important</span>' : ""}
              </div>
              
              <div class="post-content">
                  <h3 class="post-title">${postData.title}</h3>
                  ${postData.content ? `<p class="post-text">${postData.content}</p>` : ""}
                  <span class="post-department ${departmentClass}">${departmentDisplay}</span>
              </div>
              
              <div class="post-actions">
                  <button class="action-btn comment-btn" data-post-id="${postData.id}">
                      <i class="fas fa-comment"></i>
                      <span class="action-count">0</span>
                  </button>
                  <button class="action-btn like-btn" data-post-id="${postData.id}">
                      <i class="fas fa-heart"></i>
                      <span class="action-count">0</span>
                  </button>
                  <button class="action-btn view-btn" data-post-id="${postData.id}">
                      <i class="fas fa-eye"></i>
                      <span class="action-count">1</span>
                  </button>
                  <button class="action-btn bookmark-btn" data-post-id="${postData.id}">
                      <i class="fas fa-bookmark"></i>
                      <span class="action-count">0</span>
                  </button>
              </div>
              
              <div class="post-comments" id="comments-${postData.id}" style="display: none;">
                  <div class="comments-list">
                      <!-- Comments will be loaded here -->
                  </div>
              </div>
          `
  
      return postElement
    }
  
    function switchToAppropriateTab(department) {
      // If posting to all departments, switch to "All" tab
      // If posting to specific department, switch to that department's tab
      const targetTab = department === "ALL" ? "all" : department.toLowerCase()
  
      if (window.tabFiltering) {
        window.tabFiltering.switchToTab(targetTab)
      }
    }
  
    function scrollToTop() {
      const contentBody = document.querySelector(".content-body")
      if (contentBody) {
        contentBody.scrollTo({
          top: 0,
          behavior: "smooth",
        })
      }
    }
  
    function showError(message) {
      showNotification(message, "error")
    }
  
    function showSuccess(message) {
      showNotification(message, "success")
    }
  
    function showNotification(message, type = "info") {
      // Create notification element
      const notification = document.createElement("div")
      notification.className = `notification notification-${type}`
      notification.innerHTML = `
              <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-circle" : "info-circle"}"></i>
              <span>${message}</span>
          `
  
      // Add styles
      notification.style.cssText = `
              position: fixed;
              top: 20px;
              right: 20px;
              background: ${type === "success" ? "#10b981" : type === "error" ? "#ef4444" : "#3b82f6"};
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
          `
  
      document.body.appendChild(notification)
  
      // Animate in
      setTimeout(() => {
        notification.style.transform = "translateX(0)"
      }, 10)
  
      // Remove after 3 seconds
      setTimeout(() => {
        notification.style.transform = "translateX(100%)"
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification)
          }
        }, 300)
      }, 3000)
    }
  
    // Handle action buttons (image, link)
    const actionButtons = document.querySelectorAll(".action-btn")
    actionButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const icon = this.querySelector("i")
        if (icon.classList.contains("fa-image")) {
          // Handle image upload
          console.log("Image upload clicked")
          // You could open a file picker here
        } else if (icon.classList.contains("fa-link")) {
          // Handle link addition
          console.log("Link addition clicked")
          // You could open a link input dialog here
        }
      })
    })
  
    // Auto-resize textarea
    const textarea = document.getElementById("postContent")
    textarea.addEventListener("input", function () {
      this.style.height = "auto"
      this.style.height = this.scrollHeight + "px"
    })
  })
  