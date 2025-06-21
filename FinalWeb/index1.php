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

    // Check for duplicate email
    if (empty($errors)) {
        $stmt = $con->prepare("SELECT email FROM signuptbl WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "Email already registered.";
        $stmt->close();
    }

    // Insert into database if no errors
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $con->prepare("INSERT INTO signuptbl (firstName, middleName, lastName, email, dateOfBirth, studentNumber, department, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $firstName, $middleName, $lastName, $email, $dateOfBirth, $studentNumber, $department, $hashedPassword);
        if ($stmt->execute()) {
            $success_message = "Signup successful! You may now log in.";
            $firstName = $middleName = $lastName = $email = $dateOfBirth = $studentNumber = $department = '';
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
        $stmt = $con->prepare("SELECT id, password FROM signuptbl WHERE email = ?");
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
        .name-row {
    display: flex;
    gap: 16px;
}
.name-row .form-group {
    flex: 1 1 0;
    min-width: 0;
}
@media (max-width: 700px) {
    .name-row {
        flex-direction: column;
        gap: 0;
    }
}
    </style>
</head>
<body>
    <img src="img/Silang-Campus-scaled.jpg" alt="Campus aerial view" class="background-image">
    <div class="main-container">
        <div>
            <span id="loginTab" class="tab <?php echo $active_tab === 'login' ? 'active' : ''; ?>">Login</span>
            <span id="signupTab" class="tab <?php echo $active_tab === 'signup' ? 'active' : ''; ?>">Sign Up</span>
        </div>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form id="loginForm" method="POST" action="" style="display: <?php echo $active_tab === 'login' ? 'block' : 'none'; ?>;">
            <div class="form-group">
                <label for="loginEmail">Email</label>
                <input type="email" id="loginEmail" name="loginEmail" value="<?php echo htmlspecialchars($loginEmail); ?>" required>
            </div>
            <div class="form-group">
                <label for="loginPassword">Password</label>
                <input type="password" id="loginPassword" name="loginPassword" required>
            </div>
            <button type="submit" name="loginButton">Login</button>
        </form>

        <!-- Signup Form -->
        <form id="signupForm" method="POST" action="" style="display: <?php echo $active_tab === 'signup' ? 'block' : 'none'; ?>;">
            <div class="name-row">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" required>
                </div>
                <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName" value="<?php echo htmlspecialchars($middleName); ?>">
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="dateOfBirth">Date of Birth</label>
                <input type="date" id="dateOfBirth" name="dateOfBirth" value="<?php echo htmlspecialchars($dateOfBirth); ?>" required>
            </div>
            <div class="form-group">
                <label for="studentNumber">Student Number</label>
                <input type="text" id="studentNumber" name="studentNumber" value="<?php echo htmlspecialchars($studentNumber); ?>" required>
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($department); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password (min 6 chars)</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>
            <button type="submit" name="signupButton">Sign Up</button>
        </form>
    </div>
    <script src="script.js"></script>
</body>
</html>