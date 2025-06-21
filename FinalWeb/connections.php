<?php
// connections.php

// Database connection parameters
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "cvsu_bulletin_system_db";

// Procedural MySQLi connection
$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check if the connection failed
if (!$con) {
    die("Failed to connect to database: " . mysqli_connect_error());
}

// Set the character set for the connection to UTF-8
mysqli_set_charset($con, "utf8mb4");
?>