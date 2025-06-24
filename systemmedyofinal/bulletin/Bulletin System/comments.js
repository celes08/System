// Comments and Bookmark functionality

document.addEventListener("DOMContentLoaded", () => {
    const commentModal = document.getElementById("commentModal")
    const commentModalClose = document.getElementById("commentModalClose")
    const commentForm = document.getElementById("commentForm")
    const dashboardContainer = document.getElementById("dashboardContainer")
  
    let currentPostId = null
    let bookmarkedPosts = JSON.parse(localStorage.getItem("bookmarkedPosts")) || []
  
    // Initialize bookmarked posts on page load
    initializeBookmarks()
  
    // Handle comment button clicks - IMPROVED VERSION
    document.addEventListener("click", (e) => {
      // Comment button click
      if (e.target.closest(".comment-btn")) {
        e.stopPropagation()
        e.preventDefault()
        const btn = e.target.closest(".comment-btn")
        currentPostId = btn.getAttribute("data-post-id")
        openCommentModal()
        return
      }
  
      // Post card click (show/hide comments) - ONLY if not clicking on buttons
      else if (e.target.closest(".post-card") && 
               !e.target.closest(".action-btn") && 
               !e.target.closest(".post-button") &&
               !e.target.closest(".modal-overlay")) {
        const postCard = e.target.closest(".post-card")
        const postId = postCard.getAttribute("data-post-id")
        toggleComments(postId)
        return
      }
  
      // Like button click
      else if (e.target.closest(".like-btn")) {
        e.stopPropagation()
        e.preventDefault()
        const btn = e.target.closest(".like-btn")
        toggleLike(btn)
        return
      }
  
      // Bookmark button click
      else if (e.target.closest(".bookmark-btn")) {
        e.stopPropagation()
        e.preventDefault()
        const btn = e.target.closest(".bookmark-btn")
        toggleBookmark(btn)
        return
      }
    })
  
    // Close comment modal
    commentModalClose.addEventListener("click", () => {
      closeCommentModal()
    })
  
    // Close modal when clicking outside
    commentModal.addEventListener("click", (e) => {
      if (e.target === commentModal) {
        closeCommentModal()
      }
    })
  
    // Handle comment form submission
    commentForm.addEventListener("submit", (e) => {
      e.preventDefault()
      submitComment()
    })
  
    function initializeBookmarks() {
      bookmarkedPosts.forEach((postId) => {
        const bookmarkBtn = document.querySelector(`.bookmark-btn[data-post-id="${postId}"]`)
        if (bookmarkBtn) {
          bookmarkBtn.classList.add("bookmarked")
        }
      })
    }
  
    function openCommentModal() {
      commentModal.classList.add("active")
      dashboardContainer.classList.add("modal-open")
      document.body.style.overflow = "hidden"
  
      // Focus on comment textarea
      setTimeout(() => {
        document.getElementById("commentText").focus()
      }, 300)
    }
  
    function closeCommentModal() {
      commentModal.classList.remove("active")
      dashboardContainer.classList.remove("modal-open")
      document.body.style.overflow = ""
      commentForm.reset()
      currentPostId = null
    }
  
    function submitComment() {
      const commentText = document.getElementById("commentText").value.trim()
  
      if (!commentText) {
        alert("Please enter a comment")
        return
      }
  
      // Show loading state
      const submitBtn = commentForm.querySelector(".post-submit-btn")
      const originalText = submitBtn.textContent
      submitBtn.textContent = "Posting..."
      submitBtn.disabled = true
  
      // Simulate API call
      setTimeout(() => {
        // Reset button
        submitBtn.textContent = originalText
        submitBtn.disabled = false
  
        // Add comment to the post
        addCommentToPost(currentPostId, commentText)
  
        // Update comment count
        updateCommentCount(currentPostId, 1)
  
        // Close modal
        closeCommentModal()
  
        // Show success message
        showNotification("Comment posted successfully!", "success")
      }, 1000)
    }
  
    function addCommentToPost(postId, commentText) {
      const commentsSection = document.getElementById(`comments-${postId}`)
      const commentsList = commentsSection.querySelector(".comments-list")
  
      // Create new comment element
      const commentItem = document.createElement("div")
      commentItem.className = "comment-item"
      commentItem.innerHTML = `
              <div class="comment-avatar">
                  <img src="img/avatar-placeholder.png" alt="User">
              </div>
              <div class="comment-content">
                  <div class="comment-header">
                      <span class="comment-author">Person</span>
                      <span class="comment-time">Just now</span>
                  </div>
                  <p class="comment-text">${commentText}</p>
              </div>
          `
  
      // Add animation class
      commentItem.style.opacity = "0"
      commentItem.style.transform = "translateY(-10px)"
  
      // Insert at the beginning of comments list
      commentsList.insertBefore(commentItem, commentsList.firstChild)
  
      // Animate in
      setTimeout(() => {
        commentItem.style.transition = "opacity 0.3s ease, transform 0.3s ease"
        commentItem.style.opacity = "1"
        commentItem.style.transform = "translateY(0)"
      }, 10)
  
      // Show comments section if hidden
      if (commentsSection.style.display === "none") {
        commentsSection.style.display = "block"
      }
    }
  
    function toggleComments(postId) {
      const commentsSection = document.getElementById(`comments-${postId}`)
  
      if (commentsSection.style.display === "none" || !commentsSection.style.display) {
        commentsSection.style.display = "block"
        loadCommentsForPost(postId)
      } else {
        commentsSection.style.display = "none"
      }
    }
  
    function loadCommentsForPost(postId) {
      const commentsList = document.querySelector(`#comments-${postId} .comments-list`)
  
      // If comments are already loaded, don't reload
      if (commentsList.children.length > 0) {
        return
      }
  
      // Simulate loading comments from API
      const sampleComments = getSampleComments(postId)
  
      sampleComments.forEach((comment, index) => {
        setTimeout(() => {
          const commentItem = document.createElement("div")
          commentItem.className = "comment-item"
          commentItem.innerHTML = `
                      <div class="comment-avatar">
                          <img src="img/avatar-placeholder.png" alt="${comment.author}">
                      </div>
                      <div class="comment-content">
                          <div class="comment-header">
                              <span class="comment-author">${comment.author}</span>
                              <span class="comment-time">${comment.time}</span>
                          </div>
                          <p class="comment-text">${comment.text}</p>
                      </div>
                  `
  
          commentItem.style.opacity = "0"
          commentItem.style.transform = "translateY(-10px)"
          commentsList.appendChild(commentItem)
  
          setTimeout(() => {
            commentItem.style.transition = "opacity 0.3s ease, transform 0.3s ease"
            commentItem.style.opacity = "1"
            commentItem.style.transform = "translateY(0)"
          }, 10)
        }, index * 100)
      })
    }
  
    function getSampleComments(postId) {
      const comments = {
        1: [
          { author: "John Doe", time: "2 hours ago", text: "Looking forward to this event!" },
          { author: "Jane Smith", time: "1 hour ago", text: "Will there be parking available?" },
        ],
        2: [
          { author: "Mike Johnson", time: "3 hours ago", text: "Finally! I've been waiting for this schedule." },
          { author: "Sarah Wilson", time: "2 hours ago", text: "Good luck to all the graduating students!" },
          { author: "Alex Brown", time: "1 hour ago", text: "Is there a backup date in case of emergencies?" },
        ],
        3: [
          { author: "Emily Davis", time: "4 hours ago", text: "This sounds like a great opportunity for students." },
          { author: "Chris Lee", time: "3 hours ago", text: "What are the submission guidelines?" },
        ],
      }
  
      return comments[postId] || []
    }
  
    function updateCommentCount(postId, increment) {
      const commentBtn = document.querySelector(`.comment-btn[data-post-id="${postId}"]`)
      const countSpan = commentBtn.querySelector(".action-count")
      const currentCount = Number.parseInt(countSpan.textContent)
      countSpan.textContent = currentCount + increment
    }
  
    function toggleLike(btn) {
      const postId = btn.getAttribute("data-post-id")
      const countSpan = btn.querySelector(".action-count")
      const currentCount = Number.parseInt(countSpan.textContent)
  
      if (btn.classList.contains("liked")) {
        btn.classList.remove("liked")
        btn.style.color = ""
        countSpan.textContent = currentCount - 1
  
        // Remove from liked posts
        removeLikedPost(postId)
        showNotification("Removed from liked posts", "info")
      } else {
        btn.classList.add("liked")
        btn.style.color = "#e74c3c"
        countSpan.textContent = currentCount + 1
  
        // Add to liked posts
        addLikedPost(postId)
        showNotification("Post liked!", "success")
      }
    }
  
    function toggleBookmark(btn) {
      const postId = btn.getAttribute("data-post-id")
      const countSpan = btn.querySelector(".action-count")
      const currentCount = Number.parseInt(countSpan.textContent)
  
      if (btn.classList.contains("bookmarked")) {
        // Remove bookmark
        btn.classList.remove("bookmarked")
        btn.style.color = ""
        countSpan.textContent = currentCount - 1
  
        // Remove from bookmarked posts array
        bookmarkedPosts = bookmarkedPosts.filter((id) => id !== postId)
        localStorage.setItem("bookmarkedPosts", JSON.stringify(bookmarkedPosts))
  
        // Remove from bookmarks page
        removeFromBookmarksPage(postId)
  
        showBookmarkNotification("Removed from bookmarks", "removed")
      } else {
        // Add bookmark
        btn.classList.add("bookmarked")
        btn.style.color = "#f39c12"
        countSpan.textContent = currentCount + 1
  
        // Add success animation
        btn.classList.add("bookmark-success")
        setTimeout(() => {
          btn.classList.remove("bookmark-success")
        }, 1000)
  
        // Add to bookmarked posts array
        if (!bookmarkedPosts.includes(postId)) {
          bookmarkedPosts.push(postId)
          localStorage.setItem("bookmarkedPosts", JSON.stringify(bookmarkedPosts))
  
          // Add to bookmarks page
          addToBookmarksPage(postId)
        }
  
        showBookmarkNotification("Added to bookmarks", "added")
      }
    }
  
    function addLikedPost(postId) {
      const postCard = document.querySelector(`.post-card[data-post-id="${postId}"]`)
      if (!postCard) return
  
      const postData = extractPostData(postCard)
  
      // Store in localStorage for profile page
      const likedPosts = JSON.parse(localStorage.getItem("likedPosts")) || []
      if (!likedPosts.find((post) => post.id === postId)) {
        likedPosts.push({
          id: postId,
          ...postData,
          likedAt: new Date().toISOString(),
        })
        localStorage.setItem("likedPosts", JSON.stringify(likedPosts))
      }
    }
  
    function removeLikedPost(postId) {
      let likedPosts = JSON.parse(localStorage.getItem("likedPosts")) || []
      likedPosts = likedPosts.filter((post) => post.id !== postId)
      localStorage.setItem("likedPosts", JSON.stringify(likedPosts))
    }
  
    function addToBookmarksPage(postId) {
      // Get post data
      const postCard = document.querySelector(`.post-card[data-post-id="${postId}"]`)
      if (!postCard) return
  
      const postData = extractPostData(postCard)
  
      // Store in localStorage for bookmarks page
      const bookmarkData = JSON.parse(localStorage.getItem("bookmarkData")) || {}
      bookmarkData[postId] = postData
      localStorage.setItem("bookmarkData", JSON.stringify(bookmarkData))
    }
  
    function removeFromBookmarksPage(postId) {
      // Remove from localStorage
      const bookmarkData = JSON.parse(localStorage.getItem("bookmarkData")) || {}
      delete bookmarkData[postId]
      localStorage.setItem("bookmarkData", JSON.stringify(bookmarkData))
    }
  
    function extractPostData(postCard) {
      const author = postCard.querySelector(".post-author").textContent
      const username = postCard.querySelector(".post-username").textContent
      const timestamp = postCard.querySelector(".post-timestamp").textContent
      const title = postCard.querySelector(".post-title").textContent
      const text = postCard.querySelector(".post-text").textContent
      const department = postCard.querySelector(".post-department").textContent
      const departmentClass = postCard.querySelector(".post-department").className.split(" ")[1]
  
      // Get current stats
      const commentCount = postCard.querySelector(".comment-btn .action-count").textContent
      const likeCount = postCard.querySelector(".like-btn .action-count").textContent
      const viewCount = postCard.querySelector(".view-btn .action-count").textContent
      const bookmarkCount = postCard.querySelector(".bookmark-btn .action-count").textContent
  
      return {
        author,
        username,
        timestamp,
        title,
        text,
        department,
        departmentClass,
        stats: {
          comments: commentCount,
          likes: likeCount,
          views: viewCount,
          bookmarks: bookmarkCount,
        },
      }
    }
  
    function showBookmarkNotification(message, type) {
      // Remove any existing bookmark notifications
      const existingNotification = document.querySelector(".bookmark-notification")
      if (existingNotification) {
        existingNotification.remove()
      }
  
      // Create notification element
      const notification = document.createElement("div")
      notification.className = "bookmark-notification"
      notification.innerHTML = `
              <i class="fas fa-${type === "added" ? "bookmark" : "bookmark-o"}"></i>
              <span>${message}</span>
          `
  
      document.body.appendChild(notification)
  
      // Animate in
      setTimeout(() => {
        notification.classList.add("show")
      }, 10)
  
      // Remove after 3 seconds
      setTimeout(() => {
        notification.classList.remove("show")
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification)
          }
        }, 300)
      }, 3000)
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
  
    // Close modal with Escape key
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && commentModal.classList.contains("active")) {
        closeCommentModal()
      }
    })
  
    // Export functions for use in other scripts
    window.bookmarkFunctions = {
      getBookmarkedPosts: () => bookmarkedPosts,
      getBookmarkData: () => JSON.parse(localStorage.getItem("bookmarkData")) || {},
    }
  })
  
  // Add bookmark success animation styles
  const style = document.createElement("style")
  style.textContent = `
      .bookmark-success {
          animation: bookmarkPulse 0.6s ease-in-out;
      }
      
      @keyframes bookmarkPulse {
          0% { transform: scale(1); }
          50% { transform: scale(1.2); }
          100% { transform: scale(1); }
      }
      
      .action-btn.liked {
          color: #e74c3c !important;
      }
      
      .action-btn.bookmarked {
          color: #f39c12 !important;
      }
  `
  document.head.appendChild(style)
  