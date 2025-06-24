<?php
// PHP SCRIPT START
session_start();
include_once "theme-manager.php";

// Simulated user data (replace with DB logic in production for persistent storage)
$user = [
    'firstName' => $_SESSION['firstName'] ?? 'John',
    'lastName' => $_SESSION['lastName'] ?? 'Doe',
    'email' => 'john.doe@cvsu.edu.ph',
    'studentNumber' => '202312345',
    'department' => $_SESSION['department'] ?? 'DIT',
    'dateOfBirth' => $_SESSION['dateOfBirth'] ?? '1995',
    'theme' => $_SESSION['theme'] ?? 'system',
    'compactMode' => $_SESSION['compactMode'] ?? false,
    'highContrast' => $_SESSION['highContrast'] ?? false,
];
// PHP SCRIPT END
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Users - Admin Portal</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Removed custom JS theme logic, appearance is now handled by getThemeClasses() -->
</head>
<body class="<?php echo getThemeClasses(); ?>">
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <img src="img/logo.png" alt="CVSU Logo" class="logo">
                <h1>Registered Users</h1>
            </div>
            <div class="header-right">
                <div class="search-container">
                    <input type="text" id="userSearch" placeholder="Search users..." class="search-input">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Student Number</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Date of Birth</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- Users will be populated here -->
                    </tbody>
                </table>
            </div>

            <div class="back-button">
                <a href="admin-dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to admin page
                </a>
            </div>
        </main>
    </div>

    <!-- User Details Modal -->
    <div class="modal" id="userDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>User Details</h3>
                <span class="modal-close" id="userModalClose">&times;</span>
            </div>
            <div class="user-details">
                <div class="detail-group">
                    <label>First Name:</label>
                    <span id="userFirstName"></span>
                </div>
                <div class="detail-group">
                    <label>Last Name:</label>
                    <span id="userLastName"></span>
                </div>
                <div class="detail-group">
                    <label>Email:</label>
                    <span id="userEmail"></span>
                </div>
                <div class="detail-group">
                    <label>Student Number:</label>
                    <span id="userStudentNumber"></span>
                </div>
                <div class="detail-group">
                    <label>Department:</label>
                    <span id="userDepartment"></span>
                </div>
                <div class="detail-group">
                    <label>Date of Birth:</label>
                    <span id="userDateOfBirth"></span>
                </div>
                <div class="detail-group">
                    <label>Registration Date:</label>
                    <span id="userRegistrationDate"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Update User Modal -->
    <div class="modal" id="updateUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update User</h3>
                <span class="modal-close" id="updateModalClose">&times;</span>
            </div>
            <div class="modal-body">
                <form id="updateUserForm">
                    <div class="form-group">
                        <label for="updateFirstName">First Name</label>
                        <input type="text" id="updateFirstName" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label for="updateLastName">Last Name</label>
                        <input type="text" id="updateLastName" name="lastName" required>
                    </div>
                    <div class="form-group">
                        <label for="updateEmail">Email</label>
                        <input type="email" id="updateEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="updateDepartment">Department</label>
                        <select id="updateDepartment" name="department" required>
                            <option value="DIT">Department of Information Technology (DIT)</option>
                            <option value="DOM">Department of Management (DOM)</option>
                            <option value="DAS">Department of Arts and Sciences (DAS)</option>
                            <option value="TED">Teacher Education Department (TED)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="updateDateOfBirth">Date of Birth</label>
                        <input type="date" id="updateDateOfBirth" name="dateOfBirth" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Save Changes</button>
                        <button type="button" class="btn-secondary" id="updateModalClose">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
// Inlined from registered-users.js

// Store users globally
window.registeredUsers = getRegisteredUsers();

document.addEventListener("DOMContentLoaded", () => {
    loadRegisteredUsers()
    setupEventListeners()
  })
  
function setupEventListeners() {
    // Search functionality
    const searchInput = document.getElementById("userSearch")
    searchInput.addEventListener("input", function () {
      filterUsers(this.value)
    })
  
    // Modal close events
    const userModalClose = document.getElementById("userModalClose")
    const updateModalClose = document.getElementById("updateModalClose")
  
    userModalClose.addEventListener("click", closeUserModal)
    updateModalClose.addEventListener("click", closeUpdateModal)
  
    // Update form submission
    const updateForm = document.getElementById("updateUserForm")
    updateForm.addEventListener("submit", handleUpdateUser)
  
    // Close modals when clicking outside
    const userModal = document.getElementById("userDetailsModal")
    const updateModal = document.getElementById("updateUserModal")
  
    userModal.addEventListener("click", (e) => {
      if (e.target === userModal) closeUserModal()
    })
  
    updateModal.addEventListener("click", (e) => {
      if (e.target === updateModal) closeUpdateModal()
    })
  }
  
  function loadRegisteredUsers() {
    const users = window.registeredUsers;
    const tbody = document.getElementById("usersTableBody")
    tbody.innerHTML = ""
    users.forEach((user) => {
      const row = createUserRow(user)
      tbody.appendChild(row)
    })
  }

  function getRegisteredUsers() {
    // In a real app, this would come from an API
    return [
      {
        id: 1,
        firstName: "Jose",
        lastName: "Santos",
        email: "jose.santos@cvsu.edu.ph",
        studentNumber: "202210987",
        department: "DIT",
        dateOfBirth: "1995",
        registrationDate: "2024-01-15",
      },
      {
        id: 2,
        firstName: "Maria",
        lastName: "Cruz",
        email: "maria.cruz@cvsu.edu.ph",
        studentNumber: "202210988",
        department: "DOM",
        dateOfBirth: "1996",
        registrationDate: "2024-01-16",
      },
      {
        id: 3,
        firstName: "Juan",
        lastName: "Dela Cruz",
        email: "juan.delacruz@cvsu.edu.ph",
        studentNumber: "202210989",
        department: "DAS",
        dateOfBirth: "1994",
        registrationDate: "2024-01-17",
      },
      {
        id: 4,
        firstName: "Ana",
        lastName: "Garcia",
        email: "ana.garcia@cvsu.edu.ph",
        studentNumber: "202210990",
        department: "TED",
        dateOfBirth: "1997",
        registrationDate: "2024-01-18",
      },
    ]
  }
  
  function createUserRow(user) {
    const row = document.createElement("tr")
    row.innerHTML = `
          <td>${user.studentNumber}</td>
          <td>${user.firstName} ${user.lastName}</td>
          <td>${user.email}</td>
          <td>${getDepartmentFullName(user.department)}</td>
          <td>${user.dateOfBirth}</td>
          <td>
              <button class="btn-primary" onclick="viewUser(${user.id})">View</button>
              <button class="btn-warning" onclick="editUser(${user.id})">Update</button>
          </td>
      `
    return row
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
  
  function filterUsers(searchTerm) {
    const users = window.registeredUsers
    const filteredUsers = users.filter(
      (user) =>
        user.firstName.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.lastName.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.studentNumber.includes(searchTerm) ||
        user.department.toLowerCase().includes(searchTerm.toLowerCase()),
    )
  
    const tbody = document.getElementById("usersTableBody")
    tbody.innerHTML = ""
  
    filteredUsers.forEach((user) => {
      const row = createUserRow(user)
      tbody.appendChild(row)
    })
  }
  
  function viewUser(userId) {
    const users = window.registeredUsers
    const user = users.find((u) => u.id === userId)
  
    if (!user) return
  
    // Populate modal with user data
    document.getElementById("userFirstName").textContent = user.firstName
    document.getElementById("userLastName").textContent = user.lastName
    document.getElementById("userEmail").textContent = user.email
    document.getElementById("userStudentNumber").textContent = user.studentNumber
    document.getElementById("userDepartment").textContent = getDepartmentFullName(user.department)
    document.getElementById("userDateOfBirth").textContent = user.dateOfBirth
    document.getElementById("userRegistrationDate").textContent = new Date(user.registrationDate).toLocaleDateString()
  
    // Show modal
    const modal = document.getElementById("userDetailsModal")
    modal.classList.add("active")
    document.body.style.overflow = "hidden"
  }
  
  function editUser(userId) {
    const users = window.registeredUsers
    const user = users.find((u) => u.id === userId)
  
    if (!user) return
  
    // Populate form with user data
    document.getElementById("updateFirstName").value = user.firstName
    document.getElementById("updateLastName").value = user.lastName
    document.getElementById("updateEmail").value = user.email
    document.getElementById("updateDepartment").value = user.department
    document.getElementById("updateDateOfBirth").value = user.dateOfBirth
  
    // Store user ID for update
    document.getElementById("updateUserForm").dataset.userId = userId
  
    // Show modal
    const modal = document.getElementById("updateUserModal")
    modal.classList.add("active")
    document.body.style.overflow = "hidden"
  }
  
  function closeUserModal() {
    const modal = document.getElementById("userDetailsModal")
    modal.classList.remove("active")
    document.body.style.overflow = ""
  }
  
  function closeUpdateModal() {
    const modal = document.getElementById("updateUserModal")
    modal.classList.remove("active")
    document.body.style.overflow = ""
  }
  
  function handleUpdateUser(e) {
    e.preventDefault()
    const userId = Number.parseInt(e.target.dataset.userId)
    const updatedData = {
      firstName: document.getElementById("updateFirstName").value,
      lastName: document.getElementById("updateLastName").value,
      email: document.getElementById("updateEmail").value,
      department: document.getElementById("updateDepartment").value,
      dateOfBirth: document.getElementById("updateDateOfBirth").value,
    }
    // Update user in global array
    const userIndex = window.registeredUsers.findIndex(u => u.id === userId)
    if (userIndex !== -1) {
      window.registeredUsers[userIndex] = {
        ...window.registeredUsers[userIndex],
        ...updatedData
      }
    }
    // Log the action
    logAdminAction("Updated user", `${updatedData.firstName} ${updatedData.lastName}`)
    // Show success message
    showNotification("User updated successfully!", "success")
    // Close modal and refresh
    closeUpdateModal()
    setTimeout(() => {
      loadRegisteredUsers()
      // If user-details modal is open for this user, update its content
      const userDetailsModal = document.getElementById("userDetailsModal")
      if (userDetailsModal.classList.contains("active")) {
        const currentId = document.getElementById("userStudentNumber").textContent
        if (currentId === window.registeredUsers[userIndex].studentNumber) {
          // Repopulate modal
          viewUser(userId)
        }
      }
    }, 100)
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
    </script>
</body>
</html>
