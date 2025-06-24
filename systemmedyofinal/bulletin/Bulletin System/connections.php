<?php
// connections.php

// Database connection parameters
$db_host = "localhost"; // The database host (usually 'localhost' for XAMPP)
$db_user = "root";      // Your MySQL username (default for XAMPP is 'root')
$db_pass = "";          // Your MySQL password (default for 'root' in XAMPP is an empty string '')
$db_name = "cvsu_bulletin_system_db"; // *** IMPORTANT: Replace with the exact name of YOUR DATABASE ***

// Attempt to establish a connection to the MySQL database
// The mysqli_connect function takes four main arguments:
// 1. Hostname
// 2. Username
// 3. Password
// 4. Database Name
$con = mysqli_connect("localhost", "root", "", "cvsu_bulletin_system_db");

// Check if the connection failed
if (!$con) {
    // If connection fails, stop the script and display a detailed error message
    // mysqli_connect_error() provides the specific reason for the connection failure
    die("Failed to connect to database: " . mysqli_connect_error());
}

// Set the character set for the connection to UTF-8 (highly recommended)
// This helps prevent issues with special characters and international text.
$con->set_charset("utf8mb4");

// At this point, if the script hasn't died, the connection is successful.
// The database connection object is now stored in the variable $con.
// Other PHP files (like index.php, signup.php, login.php) can now include this file
// and use the $con variable to interact with the database.
?>