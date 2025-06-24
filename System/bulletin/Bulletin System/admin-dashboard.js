document.addEventListener("DOMContentLoaded", () => {
  initializeDashboard()
  setupEventListeners()
  loadDashboardData()
  createDepartmentChart()
})

function initializeDashboard() {
  // Check if user is admin
  const userRole = localStorage.getItem("userRole")
  if (userRole !== "admin") {
    window.location.href = "index.html"
    return
  }
}

function setupEventListeners() {
  console.log("Setting up event listeners...")
  
  // Admin profile dropdown
  const adminProfile = document.getElementById("adminProfile")
  if (adminProfile) {
    adminProfile.addEventListener("click", function () {
      this.classList.toggle("active")
    })
  }

  // Close dropdown when clicking outside
  document.addEventListener("click", (e) => {
    if (adminProfile && !adminProfile.contains(e.target)) {
      adminProfile.classList.remove("active")
    }
  })

  // Logout functionality
  const logoutBtn = document.getElementById("adminLogout")
  if (logoutBtn) {
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault()
      logout()
    })
  }

  // Department modal
  const departmentModal = document.getElementById("departmentModal")
  const departmentModalClose = document.getElementById("departmentModalClose")
  const addDepartmentForm = document.getElementById("addDepartmentForm")

  console.log("Modal elements found:", {
    modal: departmentModal,
    closeBtn: departmentModalClose,
    form: addDepartmentForm
  })

  if (departmentModalClose) {
    departmentModalClose.addEventListener("click", closeDepartmentModal)
  }
  
  if (addDepartmentForm) {
    addDepartmentForm.addEventListener("submit", handleAddDepartment)
  }

  // Close modal when clicking outside
  if (departmentModal) {
    departmentModal.addEventListener("click", (e) => {
      if (e.target === departmentModal) {
        closeDepartmentModal()
      }
    })
  }
  
  console.log("Event listeners setup complete")
}

function loadDashboardData() {
  // Load dashboard statistics
  const stats = getDashboardStats()

  document.getElementById("registeredUsersCount").textContent = stats.registeredUsers
  document.getElementById("pendingRegistrationCount").textContent = stats.pendingRegistrations
  document.getElementById("schoolAdminsCount").textContent = stats.schoolAdmins
  document.getElementById("reportedPostsCount").textContent = stats.reportedPosts.toString().padStart(2, "0")
  document.getElementById("helpTicketsBadge").textContent = stats.helpTickets
}

function getDashboardStats() {
  // In a real app, this would come from an API
  return {
    registeredUsers: 187,
    pendingRegistrations: 1071,
    schoolAdmins: 80,
    reportedPosts: 8,
    helpTickets: 15,
  }
}

function createDepartmentChart() {
  try {
    const ctx = document.getElementById("departmentChart")
    if (!ctx) {
      console.error("Department chart canvas not found")
      return
    }
    
    const context = ctx.getContext("2d")
    const departmentData = getDepartmentData()

    new Chart(context, {
      type: "bar",
      data: {
        labels: departmentData.labels,
        datasets: [
          {
            label: "Users",
            data: departmentData.data,
            backgroundColor: ["#1b4332", "#2d5a3d", "#40916c", "#52b788"],
            borderColor: ["#1b4332", "#2d5a3d", "#40916c", "#52b788"],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 10,
            },
          },
        },
        plugins: {
          legend: {
            display: false,
          },
        },
      },
    })
  } catch (error) {
    console.error("Error creating department chart:", error)
  }
}

function getDepartmentData() {
  // In a real app, this would come from an API
  const departments = getDepartments()
  return {
    labels: departments.map((dept) => dept.acronym),
    data: [45, 38, 52, 32], // Sample data
  }
}

function getDepartments() {
  const saved = localStorage.getItem("departments")
  return saved
    ? JSON.parse(saved)
    : [
        { name: "Department of Information Technology", acronym: "DIT" },
        { name: "Department of Arts and Sciences", acronym: "DAS" },
        { name: "Teacher Education Department", acronym: "TED" },
        { name: "Department of Management", acronym: "DOM" },
      ]
}

function navigateTo(page) {
  window.location.href = page
}

function openDepartmentModal() {
  console.log("openDepartmentModal called")
  const modal = document.getElementById("departmentModal")
  console.log("Modal element:", modal)
  if (modal) {
    modal.classList.add("active")
    document.body.style.overflow = "hidden"
    console.log("Modal should now be active")
  } else {
    console.error("Modal element not found!")
  }
}

function closeDepartmentModal() {
  const modal = document.getElementById("departmentModal")
  modal.classList.remove("active")
  document.body.style.overflow = ""

  // Reset form
  document.getElementById("addDepartmentForm").reset()
}

function handleAddDepartment(e) {
  e.preventDefault()

  const departmentName = document.getElementById("departmentName").value.trim()
  const departmentAcronym = document.getElementById("departmentAcronym").value.trim().toUpperCase()

  // Validate inputs
  if (!departmentName || !departmentAcronym) {
    showNotification("Please fill in all fields", "error")
    return
  }

  // Check if acronym already exists
  const departments = getDepartments()
  if (departments.some((dept) => dept.acronym === departmentAcronym)) {
    showNotification("Department acronym already exists", "error")
    return
  }

  // Add new department
  const newDepartment = {
    name: departmentName,
    acronym: departmentAcronym,
  }

  departments.push(newDepartment)
  localStorage.setItem("departments", JSON.stringify(departments))

  // Log the action
  logAdminAction("Added new department", `${departmentName} (${departmentAcronym})`)

  // Show success message
  showNotification("Department added successfully!", "success")

  // Close modal
  closeDepartmentModal()

  // Refresh chart
  setTimeout(() => {
    location.reload()
  }, 1500)
}

function logout() {
  // Clear admin session
  localStorage.removeItem("userRole")
  localStorage.removeItem("adminUser")
  sessionStorage.clear()

  // Redirect to login
  window.location.href = "index.html"
}

function logAdminAction(action, details = "") {
  const logs = JSON.parse(localStorage.getItem("adminLogs") || "[]")
  const adminUser = localStorage.getItem("adminUser") || "Admin"

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

function showNotification(message, type = "info") {
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
