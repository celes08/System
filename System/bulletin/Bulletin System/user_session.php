<?php
// user_session.php - Shared user session management
include("connections.php");
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to get user data
function getUserData() {
    global $con;
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Fetch user info
    $stmt = $con->prepare("SELECT firstName, middleName, lastName, email, dateOfBirth, studentNumber, department FROM signuptbl WHERE user_id = ?");
    if (!$stmt) {
        echo "<!-- Debug: Prepare failed: " . $con->error . " -->";
        return null;
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    
    echo "<!-- Debug: Number of rows found: " . $stmt->num_rows . " -->";
    
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($firstName, $middleName, $lastName, $email, $dateOfBirth, $studentNumber, $department);
        $stmt->fetch();
        $stmt->close();
        
        $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
        $username = '@' . strtolower($firstName . $lastName);
        
        echo "<!-- Debug: Fetched name: " . $fullName . " -->";
        echo "<!-- Debug: Fetched username: " . $username . " -->";
        
        return [
            'user_id' => $user_id,
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'email' => $email,
            'dateOfBirth' => $dateOfBirth,
            'studentNumber' => $studentNumber,
            'department' => $department,
            'fullName' => $fullName,
            'username' => $username
        ];
    }
    
    $stmt->close();
    echo "<!-- Debug: User not found in database -->";
    return null;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

// Get current user data
$currentUser = getUserData();

// Debug output
if ($currentUser) {
    echo "<!-- Debug: Current user data loaded successfully -->";
} else {
    echo "<!-- Debug: Failed to load current user data -->";
}
?> 