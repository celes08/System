document.addEventListener("DOMContentLoaded", () => {
    loadPendingRegistrations()
    setupEventListeners()
  })
  
  function setupEventListeners() {
    // Modal close events
    const modalClose = document.getElementById("registrationModalClose")
    modalClose.addEventListener("click", closeRegistrationModal)
  
    // Close modal when clicking outside
    const modal = document.getElementById("registrationModal")
    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeRegistrationModal()
    })
  }
  
  function loadPendingRegistrations() {
    const pendingUsers = getPendingRegistrations()
    const tbody = document.getElementById("pendingTableBody")
  
    tbody.innerHTML = ""
  
    pendingUsers.forEach((user) => {
      const row = createPendingRow(user)
      tbody.appendChild(row)
    })
  }
  
  function getPendingRegistrations() {
    // In a real app, this would come from an API
    return [
      {
        id: 1,
        firstName: "Carlos",
        lastName: "Mendoza",
        email: "carlos.mendoza@cvsu.edu.ph",
        studentNumber: "202312001",
        department: "DIT",
        dateOfBirth: "1998",
        registrationDate: "2024-01-20",
      },
      {
        id: 2,
        firstName: "Sofia",
        lastName: "Reyes",
        email: "sofia.reyes@cvsu.edu.ph",
        studentNumber: "202312002",
        department: "DOM",
        dateOfBirth: "1999",
        registrationDate: "2024-01-21",
      },
      {
        id: 3,
        firstName: "Miguel",
        lastName: "Torres",
        email: "miguel.torres@cvsu.edu.ph",
        studentNumber: "202312003",
        department: "DAS",
        dateOfBirth: "1997",
        registrationDate: "2024-01-22",
      },
    ]
  }
  
  function createPendingRow(user) {
    const row = document.createElement("tr")
    row.innerHTML = `
          <td>${user.firstName}</td>
          <td>${user.lastName}</td>
          <td>${user.email}</td>
          <td>${new Date(user.registrationDate).toLocaleDateString()}</td>
          <td>
              <button class="btn-primary" onclick="viewRegistration(${user.id})">View</button>
          </td>
      `
    return row
  }
  
  function viewRegistration(userId) {
    const pendingUsers = getPendingRegistrations()
    const user = pendingUsers.find((u) => u.id === userId)
  
    if (!user) return
  
    // Populate modal with user data
    document.getElementById("regFirstName").textContent = user.firstName
    document.getElementById("regLastName").textContent = user.lastName
    document.getElementById("regEmail").textContent = user.email
    document.getElementById("regStudentNumber").textContent = user.studentNumber
    document.getElementById("regDepartment").textContent = getDepartmentFullName(user.department)
    document.getElementById("regDateOfBirth").textContent = user.dateOfBirth
    document.getElementById("regRegistrationDate").textContent = new Date(user.registrationDate).toLocaleDateString()
  
    // Store user ID for approval/rejection
    document.getElementById("registrationModal").dataset.userId = userId
  
    // Show modal
    const modal = document.getElementById("registrationModal")
    modal.classList.add("active")
    document.body.style.overflow = "hidden"
  }
  
  function approveRegistration() {
    const modal = document.getElementById("registrationModal")
    const userId = Number.parseInt(modal.dataset.userId)
    const pendingUsers = getPendingRegistrations()
    const user = pendingUsers.find((u) => u.id === userId)
  
    if (!user) return
  
    // In a real app, this would be an API call to approve the registration
    console.log("Approving registration for:", user)
  
    // Log the action
    logAdminAction("Approved registration", `${user.firstName} ${user.lastName} (${user.email})`)
  
    // Show success message
    showNotification("Registration approved successfully!", "success")
  
    // Close modal and refresh
    closeRegistrationModal()
    setTimeout(() => {
      loadPendingRegistrations()
    }, 1000)
  }
  
  function rejectRegistration() {
    const modal = document.getElementById("registrationModal")
    const userId = Number.parseInt(modal.dataset.userId)
    const pendingUsers = getPendingRegistrations()
    const user = pendingUsers.find((u) => u.id === userId)
  
    if (!user) return
  
    // In a real app, this would be an API call to reject the registration
    console.log("Rejecting registration for:", user)
  
    // Log the action
    logAdminAction("Rejected registration", `${user.firstName} ${user.lastName} (${user.email})`)
  
    // Show success message
    showNotification("Registration rejected", "info")
  
    // Close modal and refresh
    closeRegistrationModal()
    setTimeout(() => {
      loadPendingRegistrations()
    }, 1000)
  }
  
  function closeRegistrationModal() {
    const modal = document.getElementById("registrationModal")
    modal.classList.remove("active")
    document.body.style.overflow = ""
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
  