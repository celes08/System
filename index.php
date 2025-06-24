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
                if (!isset($_SESSION['accepted_terms']) || !$_SESSION['accepted_terms']) {
                    header("Location: terms.php");
                } else {
                    header("Location: dashboard.php");
                }
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
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Inter', Arial, sans-serif;
            background: #e9ecef;
        }
        .background-image {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            object-fit: cover;
            z-index: -2;
            filter: brightness(0.50);
            background: linear-gradient(rgba(4, 9, 30, 0.85), rgba(4, 9, 30, 0.85)), url('img/Silang-Campus-scaled.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 1.2s cubic-bezier(.4,0,.2,1);
            gap: 0;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .left-panel, .right-panel {
            min-height: 480px;
            height: 70vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin: 0;
        }
        .left-panel {
            background: rgba(22, 53, 32, 0.82);
            color: #fff;
            border-radius: 22px 0 0 22px;
            padding: 54px 38px 54px 38px;
            max-width: 370px;
            min-width: 300px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.22);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .logo-container {
            margin-bottom: 18px;
        }
        .logo {
            width: 90px;
            height: 90px;
            display: block;
            margin: 0 auto 10px auto;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.12));
        }
        .left-panel h1 {
            font-size: 1.45rem;
            font-weight: 800;
            margin: 0 0 10px 0;
            text-align: center;
            line-height: 1.3;
            letter-spacing: 0.5px;
            color: #fff !important;
            text-shadow: 0 2px 8px rgba(0,0,0,0.18), 0 1px 0 #222;
        }
        .left-panel p {
            font-size: 1.08rem;
            font-weight: 400;
            margin: 0;
            text-align: center;
            color: #e0e0e0;
            text-shadow: 0 2px 8px rgba(0,0,0,0.18);
        }
        .right-panel {
            background: rgba(255,255,255,0.82);
            border-radius: 0 22px 22px 0;
            box-shadow: 0 8px 32px rgba(0,0,0,0.13);
            padding: 22px 36px 36px 36px;
            min-width: 350px;
            max-width: 400px;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            animation: fadeIn 1.2s cubic-bezier(.4,0,.2,1);
            position: relative;
        }
        .tabs-container {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
            margin-top: 0;
            position: relative;
            top: 0;
            z-index: 2;
        }
        .tab {
            border: none;
            outline: none;
            padding: 12px 38px;
            border-radius: 32px;
            font-size: 1.08rem;
            font-weight: 700;
            cursor: pointer;
            background: transparent;
            color: #1b4332;
            transition: background 0.25s, color 0.25s, box-shadow 0.2s;
            z-index: 1;
            position: relative;
        }
        .tab.active {
            background: #1b4332;
            color: #fff;
            box-shadow: 0 2px 8px rgba(27,67,50,0.10);
        }
        .tab.inactive {
            background: transparent;
            color: #1b4332;
        }
        .form-container {
            margin: 0 auto;
            width: 100%;
            max-width: 340px;
            padding: 0;
        }
        .form-container h2 {
            font-size: 1.18rem;
            font-weight: 800;
            margin-bottom: 16px;
            color: #1b4332;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .form-group {
            margin-bottom: 13px;
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-size: 1.01rem;
            font-weight: 600;
            margin-bottom: 7px;
            color: #1b4332;
            letter-spacing: 0.2px;
        }
        .form-group input,
        .form-group select {
            padding: 13px 15px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            font-size: 1.01rem;
            background: #f8f9fa;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #1b4332;
            outline: none;
            box-shadow: 0 0 0 2px rgba(27,67,50,0.10);
        }
        .form-row {
            display: flex;
            gap: 8px;
        }
        .form-row .form-group.half {
            flex: 1 1 0;
        }
        .login-button {
            width: 100%;
            padding: 11px 0;
            font-size: 1.05rem;
            font-weight: 700;
            border-radius: 9px;
            background: #1b4332;
            color: #fff;
            border: none;
            margin-top: 6px;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(27,67,50,0.08);
        }
        .login-button:hover, .login-button:focus {
            background: #14532d;
            transform: translateY(-1px) scale(1.01);
            box-shadow: 0 4px 16px rgba(27,67,50,0.13);
        }
        .notification {
            display: block;
            position: static;
            margin-bottom: 18px;
            border-radius: 8px;
            font-size: 1rem;
            padding: 12px 18px;
        }
        .notification.success {
            background: #d1fae5;
            color: #065f46;
            border: 1.5px solid #10b981;
        }
        .notification.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1.5px solid #ef4444;
        }
        .footer {
            text-align: center;
            color: #fff;
            font-size: 0.98rem;
            position: fixed;
            bottom: 0; left: 0; width: 100vw;
            background: rgba(27,67,50,0.85);
            padding: 10px 0;
            z-index: 10;
        }
        @media (max-width: 900px) {
            .main-container { flex-direction: column; gap: 0; }
            .left-panel, .right-panel {
                border-radius: 22px 22px 0 0;
                min-width: unset;
                max-width: 100vw;
                height: auto;
                margin: 0;
        }
            .right-panel { border-radius: 0 0 22px 22px; }
        }
        @media (max-width: 600px) {
            .main-container { flex-direction: column; gap: 0; }
            .left-panel, .right-panel {
                border-radius: 0;
                min-width: unset;
                max-width: 100vw;
                padding: 24px 8px;
                height: auto;
                margin: 0;
            }
            .right-panel { border-radius: 0 0 22px 22px; }
            .footer { font-size: 0.9rem; }
            .form-container {
                max-width: 98vw;
                padding: 0 2vw;
            }
        }
        /* Only the form fields scroll, not the tabs or heading */
        #signupForm.form-container {
            max-height: 52vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #b5b5b5 #f8f9fa;
            padding-bottom: 8px;
        }
        #signupForm.form-container::-webkit-scrollbar {
            width: 8px;
        }
        #signupForm.form-container::-webkit-scrollbar-thumb {
            background: #b5b5b5;
            border-radius: 4px;
        }
        #signupForm.form-container::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 4px;
        }
        @media (max-width: 600px) {
            #signupForm.form-container {
                max-height: 40vh;
            }
        }
        .signup-scroll-area {
            max-height: 52vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #b5b5b5 #f8f9fa;
            padding-bottom: 8px;
        }
        @media (max-width: 600px) {
            .signup-scroll-area {
                max-height: 40vh;
            }
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
                <div class="signup-scroll-area">
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