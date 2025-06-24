document.addEventListener("DOMContentLoaded", () => {
    loadSchoolAdmins()
    setupEventListeners()
  })
  
  function setupEventListeners() {
    // Search functionality
    const searchInput = document.getElementById("adminSearch")
    searchInput.addEventListener("input", function () {
      filterAdmins(this.value)
    })
  
    // Modal close events
    const createModalClose = document.getElementById("createModalClose")
    const adminModalClose = document.getElementById("adminModalClose")
  
    createModalClose.addEventListener("click", closeCreateAdminModal)
    adminModalClose.addEventListener("click", closeAdminDetailsModal)
  
    // Create admin form submission
    const createForm = document.getElementById("createAdminForm")
    createForm.addEventListener("submit", handleCreateAdmin)
  
    // Close modals when clicking outside
    const createModal = document.getElementById("createAdminModal")
    const detailsModal = document.getElementById("adminDetailsModal")
  
    createModal.addEventListener("click", (e) => {
      if (e.target === createModal) closeCreateAdminModal()
    })
  
    detailsModal.addEventListener("click", (e) => {
      if (e.target === detailsModal) closeAdminDetailsModal()
    })
  }
  
  function loadSchoolAdmins() {
    const admins = getSchoolAdmins()
    const tbody = document.getElementById("adminsTableBody")
  
    tbody.innerHTML = ""
  
    admins.forEach((admin) => {
      const row = createAdminRow(admin)
      tbody.appendChild(row)
    })
  }
  
  function getSchoolAdmins() {
    // In a real app, this would come from an API
    return [
      {
        id: 1,
        firstName: "Dr. Roberto",
        lastName: "Silva",
        email: "roberto.silva@cvsu.edu.ph",
        department: "DIT",
        dateOfBirth: "1975",
        createdDate: "2023-08-15",
      },
      {
        id: 2,
        firstName: "Prof. Elena",
        lastName: "Morales",
        email: "elena.morales@cvsu.edu.ph",
        department: "DOM",
        dateOfBirth: "1980",
        createdDate: "2023-09-01",
      },
      {
        id: 3,
        firstName: "Dr. Antonio",
        lastName: "Fernandez",
        email: "antonio.fernandez@cvsu.edu.ph",
        department: "DAS",
        dateOfBirth: "1978",
        createdDate: "2023-09-15",
      },
    ]
  }
  
  function createAdminRow(admin) {
    const row = document.createElement("tr")
    row.innerHTML = `
          <td>${admin.firstName} ${admin.lastName}</td>
          <td>${admin.email}</td>
          <td>${getDepartmentFullName(admin.department)}</td>
          <td>${new Date(admin.createdDate).toLocaleDateString()}</td>
          <td>
              <button class="btn-primary" onclick="viewAdmin(${admin.id})">View</button>
          </td>
      `
    return row
  }
  
  function filterAdmins(searchTerm) {
    const admins = getSchoolAdmins()
    const filteredAdmins = admins.filter(
      (admin) =>
        admin.firstName.toLowerCase().includes(searchTerm.toLowerCase()) ||
        admin.lastName.toLowerCase().includes(searchTerm.toLowerCase()) ||
        admin.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
        admin.department.toLowerCase().includes(searchTerm.toLowerCase()),
    )
  
    const tbody = document.getElementById("adminsTableBody")
    tbody.innerHTML = ""
  
    filteredAdmins.forEach((admin) => {
      const row = createAdminRow(admin)
      tbody.appendChild(row)
    })
  }
  
  function openCreateAdminModal() {
    const modal = document.getElementById("createAdminModal")
    modal.classList.add("active")
    document.body.style.overflow = "hidden"
  }
  
  function closeCreateAdminModal() {
    const modal = document.getElementById("createAdminModal")
    modal.classList.remove("active")
    document.body.style.overflow = ""
  
    // Reset form
    document.getElementById("createAdminForm").reset()
  }
  
  function viewAdmin(adminId) {
    const admins = getSchoolAdmins()
    const admin = admins.find((a) => a.id === adminId)
  
    if (!admin) return
  
    // Populate modal with admin data
    document.getElementById("adminDetailFirstName").textContent = admin.firstName
    document.getElementById("adminDetailLastName").textContent = admin.lastName
    document.getElementById("adminDetailEmail").textContent = admin.email
    document.getElementById("adminDetailDepartment").textContent = getDepartmentFullName(admin.department)
    document.getElementById("adminDetailDateOfBirth").textContent = admin.dateOfBirth || "Not provided"
    document.getElementById("adminDetailCreatedDate").textContent = new Date(admin.createdDate).toLocaleDateString()
  
    // Show modal
    const modal = document.getElementById("adminDetailsModal")
    modal.classList.add("active")
    document.body.style.overflow = "hidden"
  }
  
  function closeAdminDetailsModal() {
    const modal = document.getElementById("adminDetailsModal")
    modal.classList.remove("active")
    document.body.style.overflow = ""
  }
  
  function handleCreateAdmin(e) {
    e.preventDefault()
  
    const firstName = document.getElementById("adminFirstName").value.trim()
    const lastName = document.getElementById("adminLastName").value.trim()
    const email = document.getElementById("adminEmail").value.trim()
  
    // Validate inputs
    if (!firstName || !lastName || !email) {
      showNotification("Please fill in all fields", "error")
      return
    }
  
    // Validate email format
    if (!isValidEmail(email)) {
      showNotification("Please enter a valid email address", "error")
      return
    }
  
    // Check if email already exists
    const admins = getSchoolAdmins()
    if (admins.some((admin) => admin.email === email)) {
      showNotification("Email already exists", "error")
      return
    }
  
    // Create new admin
    const newAdmin = {
      id: Date.now(),
      firstName: firstName,
      lastName: lastName,
      email: email,
      department: "Not assigned",
      dateOfBirth: null,
      createdDate: new Date().toISOString(),
      defaultPassword: "SAdmin123",
    }
  
    // In a real app, this would be an API call
    console.log("Creating admin:", newAdmin)
  
    // Log the action
    logAdminAction("Created school admin", `${firstName} ${lastName} (${email})`)
  
    // Show success message
    showNotification(`Admin created successfully! Default password: SAdmin123`, "success")
  
    // Close modal and refresh
    closeCreateAdminModal()
    setTimeout(() => {
      loadSchoolAdmins()
    }, 1000)
  }
  
  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
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
    localStorage.setItem("adminLogs", JSON.stringify(logs))
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
  