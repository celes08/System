<?php
// PHP SCRIPT START
session_start();

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var theme = '<?php echo $user['theme']; ?>';
            var compactMode = <?php echo $user['compactMode'] ? 'true' : 'false'; ?>;
            var highContrast = <?php echo $user['highContrast'] ? 'true' : 'false'; ?>;
            function applyTheme(theme, compact, contrast) {
                var body = document.body;
                body.classList.remove('light-theme', 'dark-theme', 'high-contrast', 'compact-mode');
                if (contrast) {
                    body.classList.add('high-contrast');
                } else if (theme === 'dark') {
                    body.classList.add('dark-theme');
                } else if (theme === 'system') {
                    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        body.classList.add('dark-theme');
                    } else {
                        body.classList.add('light-theme');
                    }
                } else {
                    body.classList.add('light-theme');
                }
                if (compact) {
                    body.classList.add('compact-mode');
                }
            }
            applyTheme(theme, compactMode, highContrast);
        });
    </script>
</head>
<body class="admin-body<?php echo ($user['theme'] === 'dark' || ($user['theme'] === 'system' && (isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM']) && strpos(strtolower($_SERVER['HTTP_SEC_CH_UA_PLATFORM']), 'dark') !== false))) ? ' dark-theme' : ''; ?><?php echo $user['highContrast'] ? ' high-contrast' : ''; ?><?php echo $user['compactMode'] ? ' compact-mode' : ''; ?>">
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <img src="Bulletin System/img/logo.png" alt="CVSU Logo" class="logo">
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
            <!-- ...rest of update user modal... -->
        </div>
    </div>

    <script src="registered-users.js"></script>
</body>
</html>
