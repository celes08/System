document.addEventListener("DOMContentLoaded", () => {
  // Initialize settings page
  initializeSettings()
  setupEventListeners()
  loadUserData()
  loadUserPreferences()
})

function initializeSettings() {
  // Populate date of birth dropdown
  populateDateOfBirthDropdown()

  // Set default section states (all collapsed initially)
  const sectionContents = document.querySelectorAll(".section-content")
  sectionContents.forEach((content) => {
    content.classList.remove("active")
  })
}

function setupEventListeners() {
  // Section toggle functionality
  const sectionHeaders = document.querySelectorAll(".section-header")
  sectionHeaders.forEach((header) => {
    header.addEventListener("click", function () {
      const section = this.getAttribute("data-section")
      const content = document.getElementById(`${section}-content`)
      const arrow = this.querySelector(".section-arrow")

      // Toggle active state
      const isActive = content.classList.contains("active")

      if (isActive) {
        content.classList.remove("active")
        this.classList.remove("active")
      } else {
        content.classList.add("active")
        this.classList.add("active")
      }
    })
  })

  // Edit button functionality
  const editButtons = document.querySelectorAll(".edit-btn")
  editButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.stopPropagation()
      const field = this.getAttribute("data-field")
      toggleEditMode(field)
    })
  })

  // Save changes button
  const saveButton = document.getElementById("saveAccountChanges")
  saveButton.addEventListener("click", saveAccountChanges)

  // Change password button
  const changePasswordBtn = document.getElementById("changePasswordBtn")
  changePasswordBtn.addEventListener("click", openChangePasswordModal)

  // Change password modal
  setupChangePasswordModal()

  // Theme selection
  const themeOptions = document.querySelectorAll('input[name="theme"]')
  themeOptions.forEach((option) => {
    option.addEventListener("change", function () {
      if (this.checked) {
        applyTheme(this.value)
        savePreference("theme", this.value)
        showNotification("Theme updated successfully!", "success")
      }
    })
  })

  // Toggle switches
  const toggleSwitches = document.querySelectorAll(".toggle-switch input")
  toggleSwitches.forEach((toggle) => {
    toggle.addEventListener("change", function () {
      const setting = this.id
      const value = this.checked
      savePreference(setting, value)
      showNotification(`${setting} ${value ? "enabled" : "disabled"}`, "success")

      // Handle specific toggle logic
      handleToggleChange(setting, value)
    })
  })

  // Notification frequency
  const frequencyOptions = document.querySelectorAll('input[name="frequency"]')
  frequencyOptions.forEach((option) => {
    option.addEventListener("change", function () {
      if (this.checked) {
        savePreference("notificationFrequency", this.value)
        showNotification(`Notification frequency set to ${this.value}`, "success")
      }
    })
  })

  // Input change detection for save button
  const editInputs = document.querySelectorAll(".edit-input")
  editInputs.forEach((input) => {
    input.addEventListener("input", checkForChanges)
  })
}

function populateDateOfBirthDropdown() {
  const dobSelect = document.getElementById("edit-dateOfBirth")
  if (!dobSelect) return

  dobSelect.innerHTML = ""

  const currentYear = new Date().getFullYear()
  for (let year = currentYear - 15; year >= currentYear - 60; year--) {
    const option = document.createElement("option")
    option.value = year
    option.textContent = year
    dobSelect.appendChild(option)
  }
}

function loadUserData() {
  // Load user data from localStorage or use defaults
  const userData = getUserData()

  // Populate account information
  document.getElementById("display-firstName").textContent = userData.firstName
  document.getElementById("display-lastName").textContent = userData.lastName
  document.getElementById("display-email").textContent = userData.email
  document.getElementById("display-studentNumber").textContent = userData.studentNumber
  document.getElementById("display-department").textContent = getDepartmentFullName(userData.department)
  document.getElementById("display-dateOfBirth").textContent = userData.dateOfBirth

  // Set edit input values
  document.getElementById("edit-firstName").value = userData.firstName
  document.getElementById("edit-lastName").value = userData.lastName
  document.getElementById("edit-email").value = userData.email
  document.getElementById("edit-department").value = userData.department
  document.getElementById("edit-dateOfBirth").value = userData.dateOfBirth
}

function loadUserPreferences() {
  // Load saved preferences
  const preferences = getUserPreferences()

  // Apply theme
  const theme = preferences.theme || "light"
  document.querySelector(`input[value="${theme}"]`).checked = true
  applyTheme(theme)

  // Set toggle switches
  document.getElementById("compactMode").checked = preferences.compactMode || false
  document.getElementById("highContrast").checked = preferences.highContrast || false
  document.getElementById("emailAllAnnouncements").checked = preferences.emailAllAnnouncements !== false
  document.getElementById("emailDepartmentOnly").checked = preferences.emailDepartmentOnly || false
  document.getElementById("emailMentions").checked = preferences.emailMentions !== false
  document.getElementById("emailWeeklyDigest").checked = preferences.emailWeeklyDigest !== false
  document.getElementById("browserNotifications").checked = preferences.browserNotifications !== false
  document.getElementById("soundNotifications").checked = preferences.soundNotifications || false

  // Set notification frequency
  const frequency = preferences.notificationFrequency || "instant"
  document.querySelector(`input[value="${frequency}"]`).checked = true
}

function toggleEditMode(field) {
  const displayElement = document.getElementById(`display-${field}`)
  const editElement = document.getElementById(`edit-${field}`)
  const editBtn = document.querySelector(`[data-field="${field}"]`)

  if (editElement.classList.contains("hidden")) {
    // Enter edit mode
    displayElement.style.display = "none"
    editElement.classList.remove("hidden")
    editElement.focus()
    editBtn.innerHTML = '<i class="fas fa-check"></i>'
    editBtn.style.color = "#28a745"
  } else {
    // Exit edit mode
    displayElement.style.display = "block"
    editElement.classList.add("hidden")
    editBtn.innerHTML = '<i class="fas fa-edit"></i>'
    editBtn.style.color = ""

    // Update display value
    if (field === "department") {
      displayElement.textContent = getDepartmentFullName(editElement.value)
    } else {
      displayElement.textContent = editElement.value
    }

    checkForChanges()
  }
}

function checkForChanges() {
  const userData = getUserData()
  const saveButton = document.getElementById("saveAccountChanges")

  const currentValues = {
    firstName: document.getElementById("edit-firstName").value,
    lastName: document.getElementById("edit-lastName").value,
    email: document.getElementById("edit-email").value,
    department: document.getElementById("edit-department").value,
    dateOfBirth: document.getElementById("edit-dateOfBirth").value,
  }

  const hasChanges = Object.keys(currentValues).some((key) => currentValues[key] !== userData[key])

  saveButton.disabled = !hasChanges
}

function saveAccountChanges() {
  const updatedData = {
    firstName: document.getElementById("edit-firstName").value,
    lastName: document.getElementById("edit-lastName").value,
    email: document.getElementById("edit-email").value,
    department: document.getElementById("edit-department").value,
    dateOfBirth: document.getElementById("edit-dateOfBirth").value,
  }

  // Validate data
  if (!validateAccountData(updatedData)) {
    return
  }

  // Show pending approval message for editable fields
  const currentData = getUserData()
  const hasChanges = Object.keys(updatedData).some((key) => updatedData[key] !== currentData[key])

  if (hasChanges) {
    // Store pending changes
    localStorage.setItem("pendingAccountChanges", JSON.stringify(updatedData))

    // Show pending approval notification
    showNotification("Changes submitted for admin approval. You will be notified once approved.", "info")

    // Disable save button
    document.getElementById("saveAccountChanges").disabled = true
  }
}

function validateAccountData(data) {
  // Basic validation
  if (!data.firstName.trim()) {
    showNotification("First name is required", "error")
    return false
  }
  if (!data.lastName.trim()) {
    showNotification("Last name is required", "error")
    return false
  }
  if (!data.email.trim() || !isValidEmail(data.email)) {
    showNotification("Valid email is required", "error")
    return false
  }
  return true
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function setupChangePasswordModal() {
  const modal = document.getElementById("changePasswordModal")
  const closeBtn = document.getElementById("changePasswordModalClose")
  const cancelBtn = document.getElementById("cancelPasswordChange")
  const form = document.getElementById("changePasswordForm")

  closeBtn.addEventListener("click", closeChangePasswordModal)
  cancelBtn.addEventListener("click", closeChangePasswordModal)

  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      closeChangePasswordModal()
    }
  })

  form.addEventListener("submit", handlePasswordChange)
}

function openChangePasswordModal() {
  const modal = document.getElementById("changePasswordModal")
  modal.classList.add("active")
  document.body.style.overflow = "hidden"
}

function closeChangePasswordModal() {
  const modal = document.getElementById("changePasswordModal")
  modal.classList.remove("active")
  document.body.style.overflow = ""

  // Clear form
  document.getElementById("changePasswordForm").reset()
  clearPasswordErrors()
}

function handlePasswordChange(e) {
  e.preventDefault()

  const currentPassword = document.getElementById("currentPassword").value
  const newPassword = document.getElementById("newPassword").value
  const confirmPassword = document.getElementById("confirmNewPassword").value

  // Clear previous errors
  clearPasswordErrors()

  // Validate
  let hasErrors = false

  if (!currentPassword) {
    showPasswordError("currentPassword", "Current password is required")
    hasErrors = true
  }

  if (!newPassword) {
    showPasswordError("newPassword", "New password is required")
    hasErrors = true
  } else if (newPassword.length < 6) {
    showPasswordError("newPassword", "Password must be at least 6 characters")
    hasErrors = true
  }

  if (!confirmPassword) {
    showPasswordError("confirmNewPassword", "Please confirm your new password")
    hasErrors = true
  } else if (newPassword !== confirmPassword) {
    showPasswordError("confirmNewPassword", "Passwords do not match")
    hasErrors = true
  }

  if (hasErrors) return

  // Simulate password change (in real app, this would be an API call)
  setTimeout(() => {
    closeChangePasswordModal()
    showNotification("Password changed successfully!", "success")
  }, 1000)
}

function showPasswordError(fieldId, message) {
  const errorElement = document.getElementById(`${fieldId}Error`)
  if (errorElement) {
    errorElement.textContent = message
  }
}

function clearPasswordErrors() {
  const errorElements = document.querySelectorAll("#changePasswordModal .error-message")
  errorElements.forEach((element) => {
    element.textContent = ""
  })
}

function applyTheme(theme) {
  const body = document.body
  const root = document.documentElement

  // Remove existing theme classes
  body.classList.remove("light-theme", "dark-theme", "system-theme")

  // Apply new theme
  if (theme === "dark") {
    body.classList.add("dark-theme")
    root.style.setProperty("--bg-color", "#1a1a1a")
    root.style.setProperty("--text-color", "#ffffff")
    root.style.setProperty("--sidebar-bg", "#2d2d2d")
    root.style.setProperty("--card-bg", "#333333")
  } else if (theme === "system") {
    body.classList.add("system-theme")
    // Check system preference
    if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
      body.classList.add("dark-theme")
      root.style.setProperty("--bg-color", "#1a1a1a")
      root.style.setProperty("--text-color", "#ffffff")
      root.style.setProperty("--sidebar-bg", "#2d2d2d")
      root.style.setProperty("--card-bg", "#333333")
    } else {
      root.style.setProperty("--bg-color", "#ffffff")
      root.style.setProperty("--text-color", "#333333")
      root.style.setProperty("--sidebar-bg", "#f8f9fa")
      root.style.setProperty("--card-bg", "#ffffff")
    }
  } else {
    body.classList.add("light-theme")
    root.style.setProperty("--bg-color", "#ffffff")
    root.style.setProperty("--text-color", "#333333")
    root.style.setProperty("--sidebar-bg", "#f8f9fa")
    root.style.setProperty("--card-bg", "#ffffff")
  }

  // Apply compact mode if enabled
  const compactMode = document.getElementById("compactMode")?.checked
  if (compactMode) {
    body.classList.add("compact-mode")
  } else {
    body.classList.remove("compact-mode")
  }

  // Apply high contrast if enabled
  const highContrast = document.getElementById("highContrast")?.checked
  if (highContrast) {
    body.classList.add("high-contrast")
  } else {
    body.classList.remove("high-contrast")
  }
}

function handleToggleChange(setting, value) {
  switch (setting) {
    case "browserNotifications":
      if (value && "Notification" in window) {
        Notification.requestPermission()
      }
      break
    case "emailDepartmentOnly":
      if (value) {
        document.getElementById("emailAllAnnouncements").checked = false
        savePreference("emailAllAnnouncements", false)
      }
      break
    case "emailAllAnnouncements":
      if (value) {
        document.getElementById("emailDepartmentOnly").checked = false
        savePreference("emailDepartmentOnly", false)
      }
      break
    case "compactMode":
      if (value) {
        document.body.classList.add("compact-mode")
      } else {
        document.body.classList.remove("compact-mode")
      }
      break
    case "highContrast":
      if (value) {
        document.body.classList.add("high-contrast")
      } else {
        document.body.classList.remove("high-contrast")
      }
      break
  }
}

function showNotification(message, type = "success") {
  const toast = document.getElementById("notificationToast")
  const icon = toast.querySelector(".toast-icon")
  const messageElement = toast.querySelector(".toast-message")
  const closeBtn = toast.querySelector(".toast-close")

  // Set message
  messageElement.textContent = message

  // Set type and icon
  toast.classList.remove("success", "error")
  toast.classList.add(type)

  if (type === "success") {
    icon.className = "toast-icon fas fa-check-circle"
  } else {
    icon.className = "toast-icon fas fa-exclamation-circle"
  }

  // Show toast
  toast.classList.add("show")

  // Auto hide after 5 seconds
  setTimeout(() => {
    toast.classList.remove("show")
  }, 5000)

  // Close button
  closeBtn.onclick = () => {
    toast.classList.remove("show")
  }
}

// Utility functions
function getUserData() {
  // In a real app, this would come from an API or localStorage
  return {
    firstName: "John",
    lastName: "Doe",
    email: "john.doe@cvsu.edu.ph",
    studentNumber: "202312345",
    department: "DIT",
    dateOfBirth: "1995",
  }
}

function saveUserData(data) {
  // In a real app, this would save to an API
  localStorage.setItem("userData", JSON.stringify(data))
}

function getUserPreferences() {
  const saved = localStorage.getItem("userPreferences")
  return saved ? JSON.parse(saved) : {}
}

function savePreference(key, value) {
  const preferences = getUserPreferences()
  preferences[key] = value
  localStorage.setItem("userPreferences", JSON.stringify(preferences))
}

function getDepartmentFullName(code) {
  const departments = {
    DIT: "Department of Information Technology (DIT)",
    DOM: "Department of Management (DOM)",
    DAS: "Department of Arts and Sciences (DAS)",
    TED: "Teacher Education Department (TED)",
  }
  return departments[code] || code
}

// Listen for system theme changes
if (window.matchMedia) {
  window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
    const currentTheme = getUserPreferences().theme
    if (currentTheme === "system") {
      applyTheme("system")
    }
  })
}
