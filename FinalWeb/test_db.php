<?php
// test_db.php
$servername = "localhost";
$username = "root";       // YOUR DB USERNAME
$password = "";           // YOUR DB PASSWORD
$dbname = "bulletin_db"; // YOUR ACTUAL DB NAME

$con = mysqli_connect($servername, $username, $password, $dbname);

if (!$con) {
    die("Database connection failed (from test_db.php): " . mysqli_connect_error());
}
echo "Database connected successfully from test_db.php!<br>";

// Try a simple query to ensure table access
$test_query = "SELECT 1 FROM signuptbl LIMIT 1";
if (mysqli_query($con, $test_query)) {
    echo "Access to 'signuptbl' confirmed.<br>";
} else {
    echo "Could NOT access 'signuptbl'. Error: " . mysqli_error($con) . "<br>";
    echo "Is the 'signuptbl' table created in '" . $dbname . "'?<br>";
    echo "Does your database user ('" . $username . "') have SELECT privileges on it?<br>";
}

mysqli_close($con);
?>