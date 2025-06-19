<?php
// Database connection parameters
$hostName = "localhost";
$dbUser = "root";
$dbPassword = "";
$dbName = "login-register";

// Create a database connection
$conn = mysqli_connect($hostName, $dbUser, $dbPassword, $dbName);

// Check if the connection was successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set the character set to UTF-8 for proper handling of special characters
if (!mysqli_set_charset($conn, "utf8")) {
    die("Failed to set character set: " . mysqli_error($conn));
}

// Optional: Enable detailed error reporting for debugging during development
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Uncomment for debugging purposes (disable in production):
// echo "Connected successfully!";
?>
