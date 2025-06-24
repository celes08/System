<?php
include("connections.php");

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = [];
$success_message = '';

$firstName = $_POST['firstName'] ?? '';
$middleName = $_POST['middleName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$email = $_POST['email'] ?? '';
$dateOfBirth = $_POST['dateOfBirth'] ?? '';
$studentNumber = $_POST['studentNumber'] ?? '';
$department = $_POST['department'] ?? '';
$password = '';
$confirmPassword = '';
$loginEmail = $_POST['loginEmail'] ?? '';
$username = trim($_POST['username'] ?? '');

// --- Process Signup Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signupButton'])) {
    $firstName = trim($_POST['firstName'] ?? '');
    $middleName = trim($_POST['middleName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dateOfBirth = trim($_POST['dateOfBirth'] ?? '');
    $studentNumber = trim($_POST['studentNumber'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $username = trim($_POST['username'] ?? '');

    // Server-side validation
    if (empty($firstName)) $errors[] = "First name is required.";
    if (empty($middleName)) $errors[] = "Middle name is required.";
    if (empty($lastName)) $errors[] = "Last name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($dateOfBirth)) $errors[] = "Date of birth is required.";
    if (empty($studentNumber)) $errors[] = "Student number is required.";
    if (empty($department)) $errors[] = "Department is required.";
    if (empty($password) || strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";
    if (empty($username)) $errors[] = "Username is required.";
    if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) $errors[] = "Username must be 3-20 characters, letters, numbers, or underscores only.";

    // Check for duplicate email
    if (empty($errors)) {
        $stmt = $con->prepare("SELECT email FROM signuptbl WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "Email already registered.";
        $stmt->close();
    }

    // Check for duplicate username
    if (empty($errors)) {
        $stmt = $con->prepare("SELECT username FROM signuptbl WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "Username already taken.";
        $stmt->close();
    }

    // Insert into database if no errors
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $con->prepare("INSERT INTO signuptbl (first_name, middle_name, last_name, username, email, date_of_birth, student_number, department, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $firstName, $middleName, $lastName, $username, $email, $dateOfBirth, $studentNumber, $department, $hashedPassword);
        if ($stmt->execute()) {
            $success_message = "Signup successful! You may now log in.";
            $firstName = $middleName = $lastName = $email = $dateOfBirth = $studentNumber = $department = $username = '';
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// --- Process Login Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['loginButton'])) {
    $loginEmail = trim($_POST['loginEmail'] ?? '');
    $loginPassword = $_POST['loginPassword'] ?? '';

    if (empty($loginEmail) || empty($loginPassword)) {
        $errors[] = "Email and password are required for login.";
    } else {
        $stmt = $con->prepare("SELECT user_id, password FROM signuptbl WHERE email = ?");
        $stmt->bind_param("s", $loginEmail);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($userId, $hashedPassword);
            $stmt->fetch();
            if (password_verify($loginPassword, $hashedPassword)) {
                // Successful login
                session_start();
                $_SESSION['user_id'] = $userId;
                $_SESSION['email'] = $loginEmail;
                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No account found with that email.";
        }
        $stmt->close();
    }
}

// Determine which tab should be active
$active_tab = 'login';
if (!empty($errors) && isset($_POST['signupButton'])) {
    $active_tab = 'signup';
}
if (!empty($errors) && isset($_POST['loginButton'])) {
    $active_tab = 'login';
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
            <div class="tabs-container">
                <button class="tab <?php echo $active_tab === 'login' ? 'active' : 'inactive'; ?>" id="loginTab">Login</button>
                <button class="tab <?php echo $active_tab === 'signup' ? 'active' : 'inactive'; ?>" id="signupTab">Sign Up</button>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="notification error">
                    <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="notification success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <div class="form-container <?php echo $active_tab === 'login' ? '' : 'hidden'; ?>" id="loginForm">
                <h2>Login to your account</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="loginEmail">Email</label>
                        <input type="email" id="loginEmail" name="loginEmail" value="<?php echo htmlspecialchars($loginEmail); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input type="password" id="loginPassword" name="loginPassword" required>
                    </div>
                    <button type="submit" name="loginButton" class="login-button">Login</button>
                </form>
            </div>

            <!-- Signup Form -->
            <div class="form-container <?php echo $active_tab === 'signup' ? '' : 'hidden'; ?>" id="signupForm">
                <h2>Create an account</h2>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" required>
                        </div>
                        <div class="form-group half">
                            <label for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="middleName">Middle Name</label>
                        <input type="text" id="middleName" name="middleName" value="<?php echo htmlspecialchars($middleName); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="dateOfBirth">Date of Birth</label>
                            <input type="date" id="dateOfBirth" name="dateOfBirth" value="<?php echo htmlspecialchars($dateOfBirth); ?>" required>
                        </div>
                        <div class="form-group half">
                            <label for="studentNumber">Student Number</label>
                            <input type="text" id="studentNumber" name="studentNumber" value="<?php echo htmlspecialchars($studentNumber); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="DIT" <?php echo ($department === 'DIT') ? 'selected' : ''; ?>>DIT</option>
                            <option value="DOM" <?php echo ($department === 'DOM') ? 'selected' : ''; ?>>DOM</option>
                            <option value="DAS" <?php echo ($department === 'DAS') ? 'selected' : ''; ?>>DAS</option>
                            <option value="TED" <?php echo ($department === 'TED') ? 'selected' : ''; ?>>TED</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="password">Password (min 6 chars)</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required pattern="[A-Za-z0-9_]{3,20}" title="3-20 characters, letters, numbers, or underscores only">
                    </div>
                    <button type="submit" name="signupButton" class="login-button">Sign Up</button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>© 2025 School Bulletin Board System. All rights reserved.</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginTab = document.getElementById('loginTab');
            const signupTab = document.getElementById('signupTab');
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');

            loginTab.addEventListener('click', function() {
                loginTab.classList.add('active');
                loginTab.classList.remove('inactive');
                signupTab.classList.add('inactive');
                signupTab.classList.remove('active');
                loginForm.classList.remove('hidden');
                signupForm.classList.add('hidden');
            });

            signupTab.addEventListener('click', function() {
                signupTab.classList.add('active');
                signupTab.classList.remove('inactive');
                loginTab.classList.add('inactive');
                loginTab.classList.remove('active');
                signupForm.classList.remove('hidden');
                loginForm.classList.add('hidden');
            });
        });
    </script>
</body>
</html>