<?php
include_once "theme-manager.php";
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Registration - Admin Portal</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?php echo getThemeClasses(); ?>">
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <img src="img/logo.png" alt="CVSU Logo" class="logo">
                <h1>Pending Registration</h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pendingTableBody">
                        <!-- Pending registrations will be populated here -->
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

    <!-- Registration Details Modal -->
    <div class="modal" id="registrationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Registration Details</h3>
                <span class="modal-close" id="registrationModalClose">&times;</span>
            </div>
            <div class="registration-details">
                <div class="detail-group">
                    <label>First Name:</label>
                    <span id="regFirstName"></span>
                </div>
                <div class="detail-group">
                    <label>Last Name:</label>
                    <span id="regLastName"></span>
                </div>
                <div class="detail-group">
                    <label>Email:</label>
                    <span id="regEmail"></span>
                </div>
                <div class="detail-group">
                    <label>Student Number:</label>
                    <span id="regStudentNumber"></span>
                </div>
                <div class="detail-group">
                    <label>Department:</label>
                    <span id="regDepartment"></span>
                </div>
                <div class="detail-group">
                    <label>Date of Birth:</label>
                    <span id="regDateOfBirth"></span>
                </div>
                <div class="detail-group">
                    <label>Registration Date:</label>
                    <span id="regRegistrationDate"></span>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn-success" onclick="approveRegistration()">
                    <i class="fas fa-check"></i> Accept
                </button>
                <button class="btn-danger" onclick="rejectRegistration()">
                    <i class="fas fa-times"></i> Reject
                </button>
            </div>
        </div>
    </div>

    <script src="pending-registration.js"></script>
</body>
</html>
