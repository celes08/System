document.addEventListener("DOMContentLoaded", () => {
    initializeAdminSettings()
    setupEventListeners()
    loadAdminData()
    loadAdminPreferences()
  })
  
  function initializeAdminSettings() {
    // Check if user is admin
    const userRole = localStorage.getItem("userRole")
    if (userRole !== "admin") {
      window.location.href = "index.html"
      return
    }
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
    saveButton.addEventListener("click", saveAdminChanges)
  
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
          saveAdminPreference("theme", this.value)
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
        saveAdminPreference(setting, value)
        showNotification(`${setting} ${value ? "enabled" : "disabled"}`, "success")
  
        // Handle specific toggle logic
        handleToggleChange(setting, value)
      })
    })
  
    // Notification method
    const methodOptions = document.querySelectorAll('input[name="method"]')
    methodOptions.forEach((option) => {
      option.addEventListener("change", function () {
        if (this.checked) {
          saveAdminPreference("notificationMethod", this.value)
          showNotification(`Notification method set to ${this.value}`, "success")
        }
      })
    })
  
    // Session timeout
    const sessionTimeout = document.getElementById("sessionTimeout")
    sessionTimeout.addEventListener("change", function () {
      saveAdminPreference("sessionTimeout", this.value)
      showNotification("Session timeout updated", "success")
    })
  
    // 2FA Setup
    const setup2FA = document.getElementById("setup2FA")
    setup2FA.addEventListener("click", () => {
      showNotification("2FA setup will be implemented in future version", "info")
    })
  
    // Input change detection for save button
    const editInputs = document.querySelectorAll(".edit-input")
    editInputs.forEach((input) => {
      input.addEventListener("input", checkForChanges)
    })
  }
  
  function loadAdminData() {
    // Load admin data from localStorage or use defaults
    const adminData = getAdminData()
  
    // Populate account information
    document.getElementById("display-firstName").textContent = adminData.firstName
    document.getElementById("display-lastName").textContent = adminData.lastName
    document.getElementById("display-email").textContent = adminData.email
    document.getElementById("display-adminId").textContent = adminData.adminId
    document.getElementById("display-role").textContent = adminData.role
    document.getElementById("display-department").textContent = adminData.department
  
    // Set edit input values
    document.getElementById("edit-firstName").value = adminData.firstName
    document.getElementById("edit-lastName").value = adminData.lastName
    document.getElementById("edit-email").value = adminData.email
    document.getElementById("edit-department").value = adminData.department
  }
  
  function loadAdminPreferences() {
    // Load saved preferences
    const preferences = getAdminPreferences()
  
    // Apply theme
    const theme = preferences.theme || "light"
    document.querySelector(`input[value="${theme}"]`).checked = true
    applyTheme(theme)
  
    // Set toggle switches
    document.getElementById("compactMode").checked = preferences.compactMode || false
    document.getElementById("highContrast").checked = preferences.highContrast || false
    document.getElementById("newRegistrations").checked = preferences.newRegistrations !== false
    document.getElementById("reportedContent").checked = preferences.reportedContent !== false
    document.getElementById("helpTickets").checked = preferences.helpTickets !== false
    document.getElementById("systemErrors").checked = preferences.systemErrors !== false
    document.getElementById("dailyActivity").checked = preferences.dailyActivity || false
    document.getElementById("moderationReport").checked = preferences.moderationReport || false
    document.getElementById("loginAlerts").checked = preferences.loginAlerts !== false
  
    // Set notification method
    const method = preferences.notificationMethod || "email"
    document.querySelector(`input[value="${method}"]`).checked = true
  
    // Set session timeout
    const timeout = preferences.sessionTimeout || "60"
    document.getElementById("sessionTimeout").value = timeout
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
      displayElement.textContent = editElement.value
  
      checkForChanges()
    }
  }
  
  function checkForChanges() {
    const adminData = getAdminData()
    const saveButton = document.getElementById("saveAccountChanges")
  
    const currentValues = {
      firstName: document.getElementById("edit-firstName").value,
      lastName: document.getElementById("edit-lastName").value,
      email: document.getElementById("edit-email").value,
      department: document.getElementById("edit-department").value,
    }
  
    const hasChanges = Object.keys(currentValues).some((key) => currentValues[key] !== adminData[key])
  
    saveButton.disabled = !hasChanges
  }
  
  function saveAdminChanges() {
    const updatedData = {
      firstName: document.getElementById("edit-firstName").value,
      lastName: document.getElementById("edit-lastName").value,
      email: document.getElementById("edit-email").value,
      department: document.getElementById("edit-department").value,
      adminId: getAdminData().adminId,
      role: getAdminData().role,
    }
  
    // Validate data
    if (!validateAdminData(updatedData)) {
      return
    }
  
    // Save updated data
    localStorage.setItem("adminData", JSON.stringify(updatedData))
  
    // Log the action
    logAdminAction("Updated admin profile", "Personal information updated")
  
    // Show success message
    showNotification("Profile updated successfully!", "success")
  
    // Disable save button
    document.getElementById("saveAccountChanges").disabled = true
  }
  
  function validateAdminData(data) {
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
    const form = document.getElementById("changePasswordForm")
  
    closeBtn.addEventListener("click", closeChangePasswordModal)
  
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
  }
  
  function handlePasswordChange(e) {
    e.preventDefault()
  
    const currentPassword = document.getElementById("currentPassword").value
    const newPassword = document.getElementById("newPassword").value
    const confirmPassword = document.getElementById("confirmPassword").value
  
    // Validate
    if (!currentPassword || !newPassword || !confirmPassword) {
      showNotification("All fields are required", "error")
      return
    }
  
    if (newPassword.length < 6) {
      showNotification("Password must be at least 6 characters", "error")
      return
    }
  
    if (newPassword !== confirmPassword) {
      showNotification("Passwords do not match", "error")
      return
    }
  
    // Simulate password change
    setTimeout(() => {
      logAdminAction("Changed password", "Admin password updated")
      closeChangePasswordModal()
      showNotification("Password changed successfully!", "success")
    }, 1000)
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
      root.style.setProperty("--card-bg", "#333333")
    } else if (theme === "system") {
      body.classList.add("system-theme")
      // Check system preference
      if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
        body.classList.add("dark-theme")
        root.style.setProperty("--bg-color", "#1a1a1a")
        root.style.setProperty("--text-color", "#ffffff")
        root.style.setProperty("--card-bg", "#333333")
      } else {
        root.style.setProperty("--bg-color", "#ffffff")
        root.style.setProperty("--text-color", "#333333")
        root.style.setProperty("--card-bg", "#ffffff")
      }
    } else {
      body.classList.add("light-theme")
      root.style.setProperty("--bg-color", "#ffffff")
      root.style.setProperty("--text-color", "#333333")
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
    toast.classList.remove("success", "error", "info")
    toast.classList.add(type)
  
    if (type === "success") {
      icon.className = "toast-icon fas fa-check-circle success"
    } else if (type === "error") {
      icon.className = "toast-icon fas fa-exclamation-circle error"
    } else {
      icon.className = "toast-icon fas fa-info-circle info"
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
  function getAdminData() {
    const saved = localStorage.getItem("adminData")
    return saved
      ? JSON.parse(saved)
      : {
          firstName: "Admin",
          lastName: "User",
          email: "admin@cvsu.edu.ph",
          adminId: "ADM001",
          role: "System Administrator",
          department: "Information Technology Services",
        }
  }
  
  function getAdminPreferences() {
    const saved = localStorage.getItem("adminPreferences")
    return saved ? JSON.parse(saved) : {}
  }
  
  function saveAdminPreference(key, value) {
    const preferences = getAdminPreferences()
    preferences[key] = value
    localStorage.setItem("adminPreferences", JSON.stringify(preferences))
  }
  
  function logAdminAction(action, details = "") {
    const logs = JSON.parse(localStorage.getItem("adminLogs") || "[]")
    const adminUser = getAdminData().firstName + " " + getAdminData().lastName
  
    const logEntry = {
      id: Date.now(),
      admin: adminUser,
      action: action,
      details: details,
      timestamp: new Date().toISOString(),
      date: new Date().toLocaleDateString(),
      time: new Date().toLocaleTimeString(),
    }
  
    logs.unshift(logEntry)
  
    // Keep only last 1000 logs
    if (logs.length > 1000) {
      logs.splice(1000)
    }
  
    localStorage.setItem("adminLogs", JSON.stringify(logs))
  }
  
  // Listen for system theme changes
  if (window.matchMedia) {
    window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
      const currentTheme = getAdminPreferences().theme
      if (currentTheme === "system") {
        applyTheme("system")
      }
    })
  }
  