// DOM Elements
const loginTab = document.getElementById("loginTab")
const signupTab = document.getElementById("signupTab")
const loginForm = document.getElementById("loginForm")
const signupForm = document.getElementById("signupForm")
const loginFormElement = document.getElementById("loginFormElement")
const signupFormElement = document.getElementById("signupFormElement")
const notification = document.getElementById("notification")
const notificationMessage = document.getElementById("notificationMessage")
const notificationClose = document.getElementById("notificationClose")

// Tab switching functionality
loginTab.addEventListener("click", () => switchTab("login"))
signupTab.addEventListener("click", () => switchTab("signup"))

// // Check if user is logged in
// document.addEventListener('DOMContentLoaded', function() {
//     const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true' ||
//                        sessionStorage.getItem('isLoggedIn') === 'true';

//     if (!isLoggedIn) {
//         // Redirect to login page if not logged in
//         window.location.href = 'index.html';
//     } else {
//         // Get username from storage
//         const userName = localStorage.getItem('userName') ||
//                          sessionStorage.getItem('userName') ||
//                          'User';

//         // Update user profile in sidebar if it exists
//         const userNameElement = document.querySelector('.user-info h4');
//         const userEmailElement = document.querySelector('.user-info p');

//         if (userNameElement) {
//             userNameElement.textContent = userName;
//         }

//         if (userEmailElement) {
//             userEmailElement.textContent = `@${userName.toLowerCase()}`;
//         }
//     }
// });

// Handle logout
document.addEventListener("DOMContentLoaded", () => {
  const logoutLink = document.querySelector('a[href="index.html"]')

  if (logoutLink) {
    logoutLink.addEventListener("click", (e) => {
      e.preventDefault()

      // Clear login state
      localStorage.removeItem("isLoggedIn")
      sessionStorage.removeItem("isLoggedIn")

      // Redirect to login page
      window.location.href = "index.html"
    })
  }
})

function switchTab(tab) {
  if (tab === "login") {
    loginTab.classList.add("active")
    loginTab.classList.remove("inactive")
    signupTab.classList.add("inactive")
    signupTab.classList.remove("active")

    loginForm.classList.remove("hidden")
    signupForm.classList.add("hidden")
  } else {
    signupTab.classList.add("active")
    signupTab.classList.remove("inactive")
    loginTab.classList.add("inactive")
    loginTab.classList.remove("active")

    signupForm.classList.remove("hidden")
    loginForm.classList.add("hidden")
  }
}

// Populate Date of Birth dropdown
function populateDateOfBirth() {
  const dateOfBirthSelect = document.getElementById("dateOfBirth")
  if (!dateOfBirthSelect) return

  // Clear existing options
  dateOfBirthSelect.innerHTML = '<option value="" disabled selected>Select DoB</option>'

  // Get current year
  const currentYear = new Date().getFullYear()

  // Add years (from current year - 60 to current year - 15)
  for (let year = currentYear - 15; year >= currentYear - 60; year--) {
    const option = document.createElement("option")
    option.value = year
    option.textContent = year
    dateOfBirthSelect.appendChild(option)
  }
}

// Call the function to populate the dropdown
document.addEventListener("DOMContentLoaded", populateDateOfBirth)

// Form validation functions
function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function validatePassword(password) {
  return password.length >= 6
}

function validateName(name) {
  return name.trim().length >= 2
}

function validateStudentNumber(studentNumber) {
  // Assuming student number is at least 5 characters
  return studentNumber.trim().length >= 5
}

function showError(fieldId, message) {
  const field = document.getElementById(fieldId)
  const errorElement = document.getElementById(fieldId + "Error")

  field.classList.add("error")
  errorElement.textContent = message
}

function clearError(fieldId) {
  const field = document.getElementById(fieldId)
  const errorElement = document.getElementById(fieldId + "Error")

  field.classList.remove("error")
  errorElement.textContent = ""
}

function clearAllErrors(formType) {
  if (formType === "login") {
    clearError("email")
    clearError("password")
  } else {
    clearError("firstName")
    clearError("lastName")
    clearError("signupEmail")
    clearError("dateOfBirth")
    clearError("studentNumber")
    clearError("department")
    clearError("signupPassword")
    clearError("confirmPassword")
  }
}

// Show notification
function showNotification(message, isError = false) {
  notificationMessage.textContent = message
  notification.classList.toggle("error", isError)
  notification.classList.add("show")

  // Auto hide after 5 seconds
  setTimeout(() => {
    hideNotification()
  }, 5000)
}

function hideNotification() {
  notification.classList.remove("show")
}

// Close notification when X is clicked
notificationClose.addEventListener("click", hideNotification)

// Show loading state
function showLoading(buttonId, spinnerId) {
  const button = document.getElementById(buttonId)
  const spinner = document.getElementById(spinnerId)
  const buttonText = button.querySelector(".button-text")

  button.disabled = true
  buttonText.style.opacity = "0.7"
  spinner.classList.add("show")
}

function hideLoading(buttonId, spinnerId) {
  const button = document.getElementById(buttonId)
  const spinner = document.getElementById(spinnerId)
  const buttonText = button.querySelector(".button-text")

  button.disabled = false
  buttonText.style.opacity = "1"
  spinner.classList.remove("show")
}

// Login form submission - Update this function in your script.js file
loginFormElement.addEventListener("submit", async (e) => {
  e.preventDefault()

  clearAllErrors("login")

  const email = document.getElementById("email").value
  const password = document.getElementById("password").value
  const remember = document.getElementById("remember").checked

  let hasErrors = false

  // Validate email
  if (!email) {
    showError("email", "Email is required")
    hasErrors = true
  } else if (!validateEmail(email)) {
    showError("email", "Please enter a valid email address")
    hasErrors = true
  }

  // Validate password
  if (!password) {
    showError("password", "Password is required")
    hasErrors = true
  } else if (!validatePassword(password)) {
    showError("password", "Password must be at least 6 characters")
    hasErrors = true
  }

  if (hasErrors) return

  // Show loading state
  showLoading("loginButton", "loadingSpinner")

  // Simulate API call
  try {
    await new Promise((resolve) => setTimeout(resolve, 1500))

    // For demo purposes, accept any valid email/password combination
    // In a real application, you would verify credentials with a server
    if (validateEmail(email) && validatePassword(password)) {
      showNotification("Login successful! Redirecting to dashboard...")

      // Store login state if remember me is checked
      if (remember) {
        localStorage.setItem("rememberedEmail", email)
        localStorage.setItem("isLoggedIn", "true")
        localStorage.setItem("userName", email.split("@")[0]) // Store username for dashboard
      } else {
        sessionStorage.setItem("isLoggedIn", "true")
        sessionStorage.setItem("userName", email.split("@")[0]) // Store username for dashboard
      }

      // Redirect to dashboard after a short delay
      setTimeout(() => {
        window.location.href = "dashboard.html"
      }, 1000)
    } else {
      showNotification("Invalid email or password. Please try again.", true)
      hideLoading("loginButton", "loadingSpinner")
    }
  } catch (error) {
    showNotification("Login failed. Please try again later.", true)
    hideLoading("loginButton", "loadingSpinner")
  }
})

// Sign up form submission
signupFormElement.addEventListener("submit", async (e) => {
  e.preventDefault()

  clearAllErrors("signup")

  const firstName = document.getElementById("firstName").value
  const lastName = document.getElementById("lastName").value
  const email = document.getElementById("signupEmail").value
  const dateOfBirth = document.getElementById("dateOfBirth").value
  const studentNumber = document.getElementById("studentNumber").value
  const department = document.getElementById("department").value
  const password = document.getElementById("signupPassword").value
  const confirmPassword = document.getElementById("confirmPassword").value

  let hasErrors = false

  // Validate first name
  if (!firstName) {
    showError("firstName", "First name is required")
    hasErrors = true
  } else if (!validateName(firstName)) {
    showError("firstName", "Please enter a valid first name")
    hasErrors = true
  }

  // Validate last name
  if (!lastName) {
    showError("lastName", "Last name is required")
    hasErrors = true
  } else if (!validateName(lastName)) {
    showError("lastName", "Please enter a valid last name")
    hasErrors = true
  }

  // Validate email
  if (!email) {
    showError("signupEmail", "Email is required")
    hasErrors = true
  } else if (!validateEmail(email)) {
    showError("signupEmail", "Please enter a valid email address")
    hasErrors = true
  }

  // Validate date of birth
  if (!dateOfBirth) {
    showError("dateOfBirth", "Date of birth is required")
    hasErrors = true
  }

  // Validate student number
  if (!studentNumber) {
    showError("studentNumber", "Student number is required")
    hasErrors = true
  } else if (!validateStudentNumber(studentNumber)) {
    showError("studentNumber", "Please enter a valid student number")
    hasErrors = true
  }

  // Validate department
  if (!department) {
    showError("department", "Department is required")
    hasErrors = true
  }

  // Validate password
  if (!password) {
    showError("signupPassword", "Password is required")
    hasErrors = true
  } else if (!validatePassword(password)) {
    showError("signupPassword", "Password must be at least 6 characters")
    hasErrors = true
  }

  // Validate confirm password
  if (!confirmPassword) {
    showError("confirmPassword", "Please confirm your password")
    hasErrors = true
  } else if (password !== confirmPassword) {
    showError("confirmPassword", "Passwords do not match")
    hasErrors = true
  }

  if (hasErrors) return

  // Show loading state
  showLoading("signupButton", "signupLoadingSpinner")

  // Simulate API call
  try {
    await new Promise((resolve) => setTimeout(resolve, 2500))

    // Simulate successful signup
    showNotification("Account created successfully! Please check your email for verification.")

    // Clear form and switch to login
    signupFormElement.reset()
    setTimeout(() => {
      switchTab("login")
    }, 2000)
  } catch (error) {
    showNotification("Registration failed. Please try again later.", true)
  } finally {
    hideLoading("signupButton", "signupLoadingSpinner")
  }
})

// Forgot password functionality
document.getElementById("forgotPassword").addEventListener("click", (e) => {
  e.preventDefault()

  const email = document.getElementById("email").value

  if (!email) {
    showNotification("Please enter your email address first", true)
    return
  }

  if (!validateEmail(email)) {
    showNotification("Please enter a valid email address", true)
    return
  }

  // Store the email in sessionStorage to use it on the forgot password page
  sessionStorage.setItem("resetEmail", email)

  // Navigate to forgot password page
  window.location.href = "forgot-password.html"
})

// Load remembered email on page load
window.addEventListener("load", () => {
  const rememberedEmail = localStorage.getItem("rememberedEmail")
  if (rememberedEmail) {
    document.getElementById("email").value = rememberedEmail
    document.getElementById("remember").checked = true
  }
})

// Real-time validation
document.getElementById("email").addEventListener("input", (e) => {
  if (e.target.value && validateEmail(e.target.value)) {
    clearError("email")
  }
})

document.getElementById("password").addEventListener("input", (e) => {
  if (e.target.value && validatePassword(e.target.value)) {
    clearError("password")
  }
})

// Add event listeners for sign-up form fields if they exist
if (document.getElementById("firstName")) {
  document.getElementById("firstName").addEventListener("input", (e) => {
    if (e.target.value && validateName(e.target.value)) {
      clearError("firstName")
    }
  })
}

if (document.getElementById("lastName")) {
  document.getElementById("lastName").addEventListener("input", (e) => {
    if (e.target.value && validateName(e.target.value)) {
      clearError("lastName")
    }
  })
}

if (document.getElementById("signupEmail")) {
  document.getElementById("signupEmail").addEventListener("input", (e) => {
    if (e.target.value && validateEmail(e.target.value)) {
      clearError("signupEmail")
    }
  })
}

if (document.getElementById("studentNumber")) {
  document.getElementById("studentNumber").addEventListener("input", (e) => {
    if (e.target.value && validateStudentNumber(e.target.value)) {
      clearError("studentNumber")
    }
  })
}

if (document.getElementById("signupPassword")) {
  document.getElementById("signupPassword").addEventListener("input", (e) => {
    if (e.target.value && validatePassword(e.target.value)) {
      clearError("signupPassword")
    }
  })
}

if (document.getElementById("confirmPassword")) {
  document.getElementById("confirmPassword").addEventListener("input", (e) => {
    const password = document.getElementById("signupPassword").value
    if (e.target.value && e.target.value === password) {
      clearError("confirmPassword")
    }
  })
}

// Add smooth animations and interactions
document.addEventListener("DOMContentLoaded", () => {
  // Add fade-in animation to main container
  const mainContainer = document.querySelector(".main-container")
  mainContainer.style.opacity = "0"
  mainContainer.style.transform = "translateY(20px)"

  setTimeout(() => {
    mainContainer.style.transition = "all 0.6s ease"
    mainContainer.style.opacity = "1"
    mainContainer.style.transform = "translateY(0)"
  }, 100)
})

// FORGOT PASSWORD

// Check if we're on the forgot password page
if (document.getElementById("resetPasswordForm")) {
  const resetPasswordForm = document.getElementById("resetPasswordForm")

  // Reset password form submission
  resetPasswordForm.addEventListener("submit", async (e) => {
    e.preventDefault()

    clearError("newPassword")
    clearError("confirmPassword")

    const newPassword = document.getElementById("newPassword").value
    const confirmPassword = document.getElementById("confirmPassword").value

    let hasErrors = false

    // Validate new password
    if (!newPassword) {
      showError("newPassword", "New password is required")
      hasErrors = true
    } else if (!validatePassword(newPassword)) {
      showError("newPassword", "Password must be at least 6 characters")
      hasErrors = true
    }

    // Validate confirm password
    if (!confirmPassword) {
      showError("confirmPassword", "Please confirm your password")
      hasErrors = true
    } else if (newPassword !== confirmPassword) {
      showError("confirmPassword", "Passwords do not match")
      hasErrors = true
    }

    if (hasErrors) return

    // Show loading state
    showLoading("submitButton", "loadingSpinner")

    // Simulate API call
    try {
      await new Promise((resolve) => setTimeout(resolve, 2000))

      // Simulate successful password reset
      showNotification("Password has been changed successfully!")

      // Clear form
      resetPasswordForm.reset()

      // Redirect to login page after 2 seconds
      setTimeout(() => {
        window.location.href = "index.html"
      }, 2000)
    } catch (error) {
      showNotification("Password change failed. Please try again later.", true)
    } finally {
      hideLoading("submitButton", "loadingSpinner")
    }
  })

  // Real-time validation for password fields
  document.getElementById("newPassword").addEventListener("input", (e) => {
    if (e.target.value && validatePassword(e.target.value)) {
      clearError("newPassword")
    }

    // Optional: Update password strength indicator
    updatePasswordStrength(e.target.value)
  })

  document.getElementById("confirmPassword").addEventListener("input", (e) => {
    const newPassword = document.getElementById("newPassword").value
    if (e.target.value && e.target.value === newPassword) {
      clearError("confirmPassword")
    }
  })
}

// Optional: Password strength indicator
function updatePasswordStrength(password) {
  // If you want to add a password strength meter, uncomment this code
  // and add the HTML element with id 'passwordStrength' to your form
  /*
    const strengthIndicator = document.getElementById('passwordStrength');
    if (!strengthIndicator) return;
    
    // Remove all classes
    strengthIndicator.classList.remove('weak', 'medium', 'strong');
    
    if (!password) {
        return;
    }
    
    // Calculate password strength
    let strength = 0;
    
    // Length check
    if (password.length >= 8) strength += 1;
    
    // Character variety checks
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    // Update UI based on strength
    if (strength <= 1) {
        strengthIndicator.classList.add('weak');
    } else if (strength <= 3) {
        strengthIndicator.classList.add('medium');
    } else {
        strengthIndicator.classList.add('strong');
    }
    */
}

// Update the forgot password link in the login page to navigate to forgot-password.html
if (document.getElementById("forgotPassword")) {
  document.getElementById("forgotPassword").addEventListener("click", (e) => {
    e.preventDefault()

    const email = document.getElementById("email").value

    if (!email) {
      showNotification("Please enter your email address first", true)
      return
    }

    if (!validateEmail(email)) {
      showNotification("Please enter a valid email address", true)
      return
    }

    // Store the email in sessionStorage to use it on the forgot password page
    sessionStorage.setItem("resetEmail", email)

    // Navigate to forgot password page
    window.location.href = "forgot-password.html"
  })
}

// Dashboard

// Wait for the DOM to be fully loaded
document.addEventListener("DOMContentLoaded", () => {
  // Tab switching functionality
  const tabs = document.querySelectorAll(".tab")
  if (tabs) {
    tabs.forEach((tab) => {
      tab.addEventListener("click", function () {
        // Remove active class from all tabs
        tabs.forEach((t) => t.classList.remove("active"))
        // Add active class to clicked tab
        this.classList.add("active")
      })
    })
  }

  // User profile dropdown toggle
  const userProfile = document.querySelector(".user-profile")
  if (userProfile) {
    userProfile.addEventListener("click", function () {
      // Toggle a class or show/hide a dropdown menu
      this.classList.toggle("active")
      // You can add more code here to show/hide a dropdown menu
    })
  }

  // Post button click handler - REMOVED TO PREVENT CONFLICTS
  // const postButton = document.querySelector(".post-button")
  // if (postButton) {
  //   postButton.addEventListener("click", () => {
  //     // Show a modal or navigate to post creation page
  //     alert("Create a new post functionality will be implemented here.")
  //   })
  // }

  // Make post button work on all pages - IMPROVED VERSION
  document.addEventListener("DOMContentLoaded", () => {
    // Post button functionality for all pages
    const postButtons = document.querySelectorAll(".post-button, #postButton")
    postButtons.forEach((button) => {
      if (button && !button.hasAttribute("data-listener-added")) {
        button.setAttribute("data-listener-added", "true")
        button.addEventListener("click", (e) => {
          e.preventDefault()
          e.stopPropagation() // Prevent event bubbling
          
          // Check if we're on dashboard page
          if (
            window.location.pathname.includes("dashboard.html") ||
            document.body.classList.contains("dashboard-body")
          ) {
            // Use existing modal functionality
            if (window.openModal) {
              window.openModal()
            } else {
              // Fallback - redirect to dashboard
              window.location.href = "dashboard.html"
            }
          } else {
            // Redirect to dashboard for posting
            window.location.href = "dashboard.html"
          }
        })
      }
    })
  })

  // Search functionality
  const searchInput = document.querySelector(".search-box input")
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      // Implement search functionality
      console.log("Searching for:", this.value)
      // You can add code here to filter posts based on search input
    })
  }

  // Search functionality for dashboard
  document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.querySelector(".search-box input")
    if (searchInput) {
      searchInput.addEventListener("input", function () {
        const searchTerm = this.value.toLowerCase().trim()
        const posts = document.querySelectorAll(".post-card")

        posts.forEach((post) => {
          const title = post.querySelector(".post-title")?.textContent.toLowerCase() || ""
          const content = post.querySelector(".post-text")?.textContent.toLowerCase() || ""
          const author = post.querySelector(".post-author")?.textContent.toLowerCase() || ""

          const matches = title.includes(searchTerm) || content.includes(searchTerm) || author.includes(searchTerm)

          if (searchTerm === "" || matches) {
            post.style.display = "block"
          } else {
            post.style.display = "none"
          }
        })
      })
    }
  })

  // Make sure the calendar sidebar is scrollable
  const calendarSidebar = document.querySelector(".calendar-sidebar")
  if (calendarSidebar) {
    // Ensure the calendar is scrollable
    calendarSidebar.style.overflowY = "auto"
  }

  // Make sure the main content area is not scrollable
  const contentBody = document.querySelector(".content-body")
  if (contentBody) {
    // Ensure the main content is not scrollable
    contentBody.style.overflowY = "hidden"
  }

  // Make sure the sidebar is not scrollable
  const sidebar = document.querySelector(".sidebar")
  if (sidebar) {
    // Ensure the sidebar is not scrollable
    sidebar.style.overflowY = "hidden"
  }
})

// Add this to your script.js file for tab switching functionality

document.addEventListener("DOMContentLoaded", () => {
  // Only run this code on the dashboard page
  if (document.body.classList.contains("dashboard-body")) {
    // Get all tabs in the content header
    const tabs = document.querySelectorAll(".content-header .tab")

    // Add click event listeners to each tab
    tabs.forEach((tab) => {
      tab.addEventListener("click", function () {
        // Remove active class from all tabs
        tabs.forEach((t) => t.classList.remove("active"))

        // Add active class to clicked tab
        this.classList.add("active")

        // Optional: You can add logic here to filter content based on selected tab
        const tabText = this.textContent.trim()
        console.log(`Switched to ${tabText} tab`)

        // Example: Filter announcements based on selected department
        if (tabText === "All") {
          // Show all announcements
          showAllAnnouncements()
        } else if (tabText === "DIT") {
          // Show only DIT announcements
          showDITAnnouncements()
        }
      })
    })
  }
})

// Example functions for filtering content (you can customize these)
function showAllAnnouncements() {
  // Logic to show all announcements
  console.log("Showing all announcements")
}

function showDITAnnouncements() {
  // Logic to show only DIT announcements
  console.log("Showing DIT announcements only")
}
