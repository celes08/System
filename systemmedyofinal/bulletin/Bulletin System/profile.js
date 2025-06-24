// Profile Page Functionality

document.addEventListener("DOMContentLoaded", () => {
    // Initialize profile page
    initializeProfile()
    setupEventListeners()
    // loadProfileData() // This is no longer needed as data is rendered by PHP
  })
  
  function initializeProfile() {
    // Set default active tab
    const defaultTab = document.querySelector('.profile-tab[data-tab="posts"]')
    if (defaultTab) {
      defaultTab.classList.add("active")
    }
  
    const defaultContent = document.getElementById("posts-content")
    if (defaultContent) {
      defaultContent.classList.add("active")
    }
  }
  
  function setupEventListeners() {
    // Tab switching
    const profileTabs = document.querySelectorAll(".profile-tab")
    profileTabs.forEach((tab) => {
      tab.addEventListener("click", function () {
        const tabName = this.getAttribute("data-tab")
        switchTab(tabName)
      })
    })
  
    // Edit profile button
    const editProfileBtn = document.getElementById("editProfileBtn")
    if (editProfileBtn) {
      editProfileBtn.addEventListener("click", () => {
        // Redirect to settings page
        window.location.href = "settings.php"
      })
    }
  }
  
  function switchTab(tabName) {
    // Remove active class from all tabs and content
    document.querySelectorAll(".profile-tab").forEach((tab) => {
      tab.classList.remove("active")
    })
  
    document.querySelectorAll(".tab-content").forEach((content) => {
      content.classList.remove("active")
    })
  
    // Add active class to selected tab and content
    const selectedTab = document.querySelector(`[data-tab="${tabName}"]`)
    const selectedContent = document.getElementById(`${tabName}-content`)
  
    if (selectedTab) selectedTab.classList.add("active")
    if (selectedContent) selectedContent.classList.add("active")
  
    // Removed JS content loading for tabs
  }
  
  function loadUserPosts() {
    const postsContainer = document.getElementById("userPostsList")
    const emptyState = document.getElementById("emptyPosts")
  
    // Show loading state
    showLoadingState(postsContainer)
  
    // Simulate API call
    setTimeout(() => {
      const userPosts = getUserPosts()
  
      if (userPosts.length === 0) {
        postsContainer.innerHTML = ""
        emptyState.style.display = "flex"
      } else {
        emptyState.style.display = "none"
        renderPosts(postsContainer, userPosts, "user-post")
      }
    }, 500)
  }
  
  function loadLikedPosts() {
    const postsContainer = document.getElementById("likedPostsList")
    const emptyState = document.getElementById("emptyLiked")
  
    // Show loading state
    showLoadingState(postsContainer)
  
    // Simulate API call
    setTimeout(() => {
      const likedPosts = getLikedPosts()
  
      if (likedPosts.length === 0) {
        postsContainer.innerHTML = ""
        emptyState.style.display = "flex"
      } else {
        emptyState.style.display = "none"
        renderPosts(postsContainer, likedPosts, "liked-post")
      }
    }, 500)
  }
  
  function loadCommentedPosts() {
    const postsContainer = document.getElementById("commentedPostsList")
    const emptyState = document.getElementById("emptyComments")
  
    // Show loading state
    showLoadingState(postsContainer)
  
    // Simulate API call
    setTimeout(() => {
      const commentedPosts = getCommentedPosts()
  
      if (commentedPosts.length === 0) {
        postsContainer.innerHTML = ""
        emptyState.style.display = "flex"
      } else {
        emptyState.style.display = "none"
        renderPostsWithComments(postsContainer, commentedPosts)
      }
    }, 500)
  }
  
  function showLoadingState(container) {
    container.innerHTML = `
            <div class="loading-state">
                <div class="loading-spinner"></div>
                <div class="loading-text">Loading...</div>
            </div>
        `
  }
  
  function renderPosts(container, posts, postType) {
    container.innerHTML = ""
  
    posts.forEach((post) => {
      const postElement = createPostElement(post, postType)
      container.appendChild(postElement)
    })
  }
  
  function renderPostsWithComments(container, posts) {
    container.innerHTML = ""
  
    posts.forEach((post) => {
      const postElement = createPostElement(post, "commented-post")
  
      // Add user comments section
      if (post.userComments && post.userComments.length > 0) {
        const commentsSection = createUserCommentsSection(post.userComments)
        postElement.appendChild(commentsSection)
      }
  
      container.appendChild(postElement)
    })
  }
  
  function createPostElement(post, postType) {
    const postElement = document.createElement("div")
    postElement.className = `profile-post-card ${postType}`
  
    postElement.innerHTML = `
            <div class="profile-post-header">
                <div class="profile-post-avatar">
                    <img src="img/avatar-placeholder.png" alt="${post.author}">
                </div>
                <div class="profile-post-user-info">
                    <h4 class="profile-post-author">${post.author}</h4>
                    <div class="profile-post-meta">
                        <span class="profile-post-username">${post.username}</span>
                        <span class="profile-post-timestamp">${post.timestamp}</span>
                    </div>
                </div>
            </div>
            
            <div class="profile-post-content">
                <h3 class="profile-post-title">${post.title}</h3>
                ${post.text ? `<p class="profile-post-text">${post.text}</p>` : ""}
                <span class="profile-post-department ${post.departmentClass}">${post.department}</span>
            </div>
            
            <div class="profile-post-actions">
                <div class="profile-post-stat">
                    <i class="fas fa-comment"></i>
                    <span>${post.stats.comments}</span>
                </div>
                <div class="profile-post-stat">
                    <i class="fas fa-heart"></i>
                    <span>${post.stats.likes}</span>
                </div>
                <div class="profile-post-stat">
                    <i class="fas fa-eye"></i>
                    <span>${post.stats.views}</span>
                </div>
                <div class="profile-post-stat">
                    <i class="fas fa-bookmark"></i>
                    <span>${post.stats.bookmarks}</span>
                </div>
            </div>
        `
  
    return postElement
  }
  
  function createUserCommentsSection(comments) {
    const section = document.createElement("div")
    section.className = "user-comments-section"
  
    const header = document.createElement("div")
    header.className = "user-comments-header"
    header.innerHTML = `
            <i class="fas fa-comment"></i>
            <span>Your Comments</span>
        `
  
    section.appendChild(header)
  
    comments.forEach((comment) => {
      const commentElement = document.createElement("div")
      commentElement.className = "user-comment"
  
      commentElement.innerHTML = `
                <div class="user-comment-header">
                    <span class="user-comment-author">You</span>
                    <span class="user-comment-time">${comment.timestamp}</span>
                </div>
                <p class="user-comment-text">${comment.text}</p>
            `
  
      section.appendChild(commentElement)
    })
  
    return section
  }
  
  function updateProfileStats() {
    const userPosts = getUserPosts()
    const likedPosts = getLikedPosts()
    const commentedPosts = getCommentedPosts()
  
    // Count total user comments
    let totalComments = 0
    commentedPosts.forEach((post) => {
      if (post.userComments) {
        totalComments += post.userComments.length
      }
    })
  
    // Update stats display
    document.getElementById("postsCount").textContent = userPosts.length
    document.getElementById("likesCount").textContent = likedPosts.length
    document.getElementById("commentsCount").textContent = totalComments
  }
  
  // Data functions (in a real app, these would fetch from an API)
  function getUserData() {
    return {
      name: "Person",
      username: "@person",
      joinDate: "May 7, 2025",
      avatar: "img/avatar-placeholder.png",
    }
  }
  
  function getUserPosts() {
    // Sample user posts
    return [
      {
        id: "user-1",
        author: "Person",
        username: "@person",
        timestamp: "May 7, 2025",
        title: "Welcome to the new semester!",
        text: "Excited to start this new academic year. Looking forward to all the learning opportunities ahead.",
        department: "DIT",
        departmentClass: "dit",
        stats: {
          comments: "5",
          likes: "12",
          views: "25",
          bookmarks: "3",
        },
      },
      {
        id: "user-2",
        author: "Person",
        username: "@person",
        timestamp: "May 5, 2025",
        title: "Study Group Formation",
        text: "Looking to form a study group for Advanced Programming. Anyone interested?",
        department: "DIT",
        departmentClass: "dit",
        stats: {
          comments: "8",
          likes: "15",
          views: "32",
          bookmarks: "7",
        },
      },
    ]
  }
  
  function getLikedPosts() {
    // Load liked posts from localStorage
    const likedPosts = JSON.parse(localStorage.getItem("likedPosts")) || []
  
    // If no liked posts in storage, return sample data
    if (likedPosts.length === 0) {
      return [
        {
          id: "liked-1",
          author: "John Doe",
          username: "@johndoe",
          timestamp: "May 6, 2025",
          title: "Programming Best Practices",
          text: "Here are some essential programming best practices every developer should know...",
          department: "DIT",
          departmentClass: "dit",
          stats: {
            comments: "12",
            likes: "45",
            views: "89",
            bookmarks: "23",
          },
        },
        {
          id: "liked-2",
          author: "Jane Smith",
          username: "@janesmith",
          timestamp: "May 4, 2025",
          title: "Career Opportunities in Tech",
          text: "The tech industry offers numerous career paths. Let me share some insights...",
          department: "DIT",
          departmentClass: "dit",
          stats: {
            comments: "18",
            likes: "67",
            views: "134",
            bookmarks: "34",
          },
        },
      ]
    }
  
    return likedPosts
  }
  
  function getCommentedPosts() {
    // Sample posts where user has commented
    return [
      {
        id: "commented-1",
        author: "Mike Johnson",
        username: "@mikejohnson",
        timestamp: "May 6, 2025",
        title: "Database Design Principles",
        text: "Understanding proper database design is crucial for any application...",
        department: "DIT",
        departmentClass: "dit",
        stats: {
          comments: "15",
          likes: "38",
          views: "72",
          bookmarks: "19",
        },
        userComments: [
          {
            id: "comment-1",
            text: "Great explanation! This really helped me understand normalization better.",
            timestamp: "2 hours ago",
          },
          {
            id: "comment-2",
            text: "Could you elaborate more on the third normal form?",
            timestamp: "1 hour ago",
          },
        ],
      },
      {
        id: "commented-2",
        author: "Sarah Wilson",
        username: "@sarahwilson",
        timestamp: "May 3, 2025",
        title: "Web Development Trends 2025",
        text: "Here are the top web development trends to watch out for this year...",
        department: "DIT",
        departmentClass: "dit",
        stats: {
          comments: "22",
          likes: "56",
          views: "98",
          bookmarks: "28",
        },
        userComments: [
          {
            id: "comment-3",
            text: "Really excited about the new JavaScript frameworks mentioned here!",
            timestamp: "3 days ago",
          },
        ],
      },
    ]
  }
  
  // Export functions for use in other scripts if needed
  window.profileFunctions = {
    switchTab: switchTab,
    loadUserPosts: loadUserPosts,
    loadLikedPosts: loadLikedPosts,
    loadCommentedPosts: loadCommentedPosts,
  }
  
  document.addEventListener('DOMContentLoaded', function() {
    const profilePictureInput = document.getElementById('profilePictureInput');
    const profilePictureForm = document.getElementById('profilePictureForm');

    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function() {
            if (profilePictureForm) {
                profilePictureForm.submit();
            }
        });
    }
  });
  