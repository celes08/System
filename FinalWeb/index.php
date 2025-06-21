<?php
// index.php
// This file handles displaying both login and signup forms,
// and processes the signup submission within the same file.

// Include your database connection file.
// Ensure 'connections.php' correctly defines a variable like $con (most likely).
include("connections.php");

// Set up robust error reporting for debugging (REMOVE OR COMMENT OUT IN PRODUCTION)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables for displaying messages
$errors = [];          // To store validation or database errors
$success_message = ''; // To store success messages

// --- Process Signup Form Submission ---
// Check if the signup form was submitted (by checking for 'firstName' which is unique to signup)
// and if the request method is POST.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signupButton'])) {

    // Sanitize and validate inputs
    $first_name = trim($_POST['firstName'] ?? '');
    $middle_name = trim($_POST['middleName'] ?? '');
    $last_name = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? ''); // 'email' for signup form
    $date_of_birth = trim($_POST['dateOfBirth'] ?? '');
    $student_number = trim($_POST['studentNumber'] ?? '');
    $department_id = trim($_POST['department'] ?? '');
    $password = $_POST['password'] ?? ''; // 'password' for signup form
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Basic Server-Side Validation
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($date_of_birth)) $errors[] = "Date of Birth is required.";
    if (empty($student_number)) $errors[] = "Student number is required.";
    if (empty($department_id)) $errors[] = "Department is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";

    // Duplicate check
    if (empty($errors)) {
        $check = $con->prepare("SELECT user_id FROM users WHERE email = ? OR student_number = ?");
        $check->bind_param("ss", $email, $student_number);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $errors[] = "An account with this email or student number already exists.";
        }
        $check->close();
    }

    // If no validation errors, proceed with database insertion
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL statement for inserting a new user into 'users'.
        $sql = "INSERT INTO users (first_name, middle_name, last_name, email, date_of_birth, student_number, department_id, password_hash, account_status, role_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 1)";
        $stmt = $con->prepare($sql);

        if ($stmt === false) {
            error_log("Failed to prepare signup statement: " . $con->error);
            $errors[] = "An internal server error occurred during registration. Please try again later. (Error P1)";
        } else {
            $stmt->bind_param("ssssssis", $first_name, $middle_name, $last_name, $email, $date_of_birth, $student_number, $department_id, $hashed_password);

            if ($stmt->execute()) {
                $success_message = "Registration successful! You can now log in.";
                // Clear form fields after successful registration
                $first_name = $middle_name = $last_name = $email = $date_of_birth = $student_number = $department_id = $password = $confirmPassword = '';
                $active_tab = 'login';
            } else {
                error_log("Error during registration execution: " . $stmt->error);
                if ($stmt->errno == 1062) { // MySQL error code for duplicate entry on a unique key
                    $errors[] = "An account with this email or student number already exists.";
                } else {
                    $errors[] = "Registration failed due to a database error. Please try again. (Error E1)";
                }
                $active_tab = 'signup';
            }
            $stmt->close();
        }
    } else {
        $active_tab = 'signup';
    }
}

// --- Process Login Form Submission ---
// Check if the login form was submitted (by checking for 'email' and 'password' which are common,
// but the 'loginButton' is unique to login form to differentiate from signup form's submit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['loginButton'])) { // Assumes loginButton has a name attribute or similar unique identifier
    // Note: The original HTML for loginForm did not have a 'name' attribute on the submit button.
    // I'm adding `name="loginButton"` to the login submit button in the HTML below for this check.
    // If not, you'd need a different way to distinguish, like an hidden input.

    // Sanitize inputs
    $loginEmail = trim($_POST['email'] ?? ''); // 'email' for login form
    $loginPassword = $_POST['password'] ?? ''; // 'password' for login form

    // Initialize login-specific errors
    $loginErrors = [];

    // Basic validation
    if (empty($loginEmail) || !filter_var($loginEmail, FILTER_VALIDATE_EMAIL)) $loginErrors[] = "Valid email is required for login.";
    if (empty($loginPassword)) $loginErrors[] = "Password is required for login.";

    if (empty($loginErrors)) {
        $sql = "SELECT user_id, email, password_hash, first_name, last_name, department_id FROM users WHERE email = ?";
        $stmt = $con->prepare($sql);

        if ($stmt === false) {
            error_log("Failed to prepare login statement: " . $con->error);
            $loginErrors[] = "An internal error occurred during login. Please try again. (Error L1)";
        } else {
            $stmt->bind_param("s", $loginEmail);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($loginPassword, $user['password_hash'])) {
                    // Login successful! Start session and redirect.
                    session_start();
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_first_name'] = $user['first_name'];
                    $_SESSION['user_last_name'] = $user['last_name'];
                    $_SESSION['user_department_id'] = $user['department_id'];

                    header("Location: dashboard.php"); // Redirect to dashboard
                    exit();
                } else {
                    $loginErrors[] = "Invalid password.";
                }
            } else {
                $loginErrors[] = "No account found with that email address.";
            }
            $stmt->close();
        }
    }
    // If login failed, add loginErrors to the main errors array for display
    $errors = array_merge($errors, $loginErrors);
    $active_tab = 'login';
}

// --- Handle URL-based notifications (from previous redirects like logout) ---
// This ensures messages from other pages (like dashboard.php after logout) are still displayed.
if (isset($_GET['status']) && isset($_GET['message'])) {
    if ($_GET['status'] === 'success') {
        $success_message = htmlspecialchars(urldecode($_GET['message']));
    } elseif ($_GET['status'] === 'error') {
        $errors[] = htmlspecialchars(urldecode($_GET['message']));
    }
}

// --- Handle tab switching ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['showLogin'])) {
    $active_tab = 'login';
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['showSignup'])) {
    $active_tab = 'signup';
}

// Default tab logic
if (!isset($active_tab)) {
    $active_tab = (!empty($errors) && isset($_POST['signupButton'])) ? 'signup' : 'login';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CVSU Department Bulletin Board System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic styling for notification, ensure this is included in your styles.css */
        .notification {
            display: none; /* Hidden by default, shown by JS */
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            min-width: 300px;
            text-align: center;
        }
        .notification.success {
            background-color: #4CAF50; /* Green */
        }
        .notification.error {
            background-color: #f44336; /* Red */
        }
        .notification-close {
            position: absolute;
            top: 5px;
            right: 10px;
            color: white;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
        }
        .hidden { display: none !important; }
        .tab.active { background-color: #0056b3; color: white; }
        .tab.inactive { background-color: #e0e0e0; color: #555; }
        .loading-spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: none;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .button-text {
            display: inline-block;
        }
    </style>
</head>
<body>
    <img src="img/Silang-Campus-scaled.jpg" alt="Campus aerial view" class="background-image">

    <div class="main-container">
        <div class="left-panel">
            <div class="logo-container">
                <img src="img/logo.png" alt="CvSU Logo" class="logo">
            </div>
            <h1>Welcome to CVSU's Department Bulletin Board System</h1>
            <p>Stay updated with the latest announcements from all departments</p>
        </div>

        <div class="right-panel">
            <div class="tabs">
                <div class="tabs-container">
                    <form method="post" style="display:inline;">
                        <button type="submit" name="showLogin" class="tab <?php echo ($active_tab === 'login' ? 'active' : 'inactive'); ?>">Login</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <button type="submit" name="showSignup" class="tab <?php echo ($active_tab === 'signup' ? 'active' : 'inactive'); ?>">Sign Up</button>
                    </form>
                </div>
            </div>

            <div class="form-container<?php echo ($active_tab === 'signup' ? ' hidden' : ''); ?>" id="loginForm">
                <h2>Login to your account</h2>

                <form id="loginFormElement" action="" method="POST">
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" placeholder="your@email.com" value="<?php echo htmlspecialchars($loginEmail ?? ''); ?>" required>
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <div class="form-group">
                        <div class="form-header">
                            <label for="password">Password</label>
                            <a href="#" id="forgotPassword">Forget password?</a>
                        </div>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                        <span class="error-message" id="passwordError"></span>
                    </div>

                    <div class="checkbox-container">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>

                    <button type="submit" class="login-button" id="loginButton" name="loginButton" value="1">
                        <span class="button-text">Login</span>
                        <span class="loading-spinner" id="loadingSpinner"></span>
                    </button>
                </form>
            </div>

            <div class="form-container<?php echo ($active_tab === 'login' ? ' hidden' : ''); ?>" id="signupForm">
                <h2>Create an account</h2>

                <form id="signupFormElement" action="" method="POST">
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" name="firstName" placeholder="Enter first name" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                            <span class="error-message" id="firstNameError"></span>
                        </div>
                        <div class="form-group half">
                            <label for="middleName">Middle Name</label>
                            <input type="text" id="middleName" name="middleName" placeholder="Enter middle name" value="<?php echo htmlspecialchars($middle_name ?? ''); ?>">
                            <span class="error-message" id="middleNameError"></span>
                        </div>
                        <div class="form-group half">
                            <label for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="lastName" placeholder="Enter last name" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                            <span class="error-message" id="lastNameError"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="signupEmail">Email address</label>
                        <input type="email" id="signupEmail" name="email" placeholder="your@email.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        <span class="error-message" id="signupEmailError"></span>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label for="dateOfBirth">Date of Birth</label>
                            <input type="date" id="dateOfBirth" name="dateOfBirth" value="<?php echo htmlspecialchars($date_of_birth ?? ''); ?>" required>
                            <span class="error-message" id="dateOfBirthError"></span>
                        </div>
                        <div class="form-group half">
                            <label for="studentNumber">Student Number</label>
                            <input type="text" id="studentNumber" name="studentNumber" placeholder="Enter Student Number" value="<?php echo htmlspecialchars($student_number ?? ''); ?>" required>
                            <span class="error-message" id="studentNumberError"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="" disabled selected>Select department</option>
                            <option value="1" <?php echo (isset($department_id) && $department_id == '1' ? 'selected' : ''); ?>>DIT</option>
                            <option value="2" <?php echo (isset($department_id) && $department_id == '2' ? 'selected' : ''); ?>>DOM</option>
                            <option value="3" <?php echo (isset($department_id) && $department_id == '3' ? 'selected' : ''); ?>>DAS</option>
                            <option value="4" <?php echo (isset($department_id) && $department_id == '4' ? 'selected' : ''); ?>>TED</option>
                        </select>
                        <span class="select-arrow">▼</span>
                        <span class="error-message" id="departmentError"></span>
                    </div>

                    <div class="form-group">
                        <label for="signupPassword">Password</label>
                        <input type="password" id="signupPassword" name="password" placeholder="Enter password" required>
                        <span class="error-message" id="signupPasswordError"></span>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Re-enter Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password" required>
                        <span class="error-message" id="confirmPasswordError"></span>
                    </div>

                    <button type="submit" class="login-button" id="signupButton" name="signupButton" value="1">
                        <span class="button-text">Sign Up</span>
                        <span class="loading-spinner" id="signupLoadingSpinner"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>© 2025 School Bulletin Board System. All rights reserved.</p>
    </div>

    <div class="notification" id="notification" style="<?php echo (!empty($success_message) || !empty($errors)) ? 'display:block;' : ''; ?> <?php echo (!empty($errors)) ? 'background-color:#f44336;' : 'background-color:#4CAF50;'; ?>">
        <span id="notificationMessage">
            <?php
            if (!empty($success_message)) echo htmlspecialchars($success_message);
            if (!empty($errors)) foreach ($errors as $e) echo htmlspecialchars($e) . "<br>";
            ?>
        </span>
        <button class="notification-close" id="notificationClose" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
</body>
</html>
