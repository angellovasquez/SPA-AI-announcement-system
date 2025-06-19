<?php
// Database connection settings
$host = "localhost"; // Usually "localhost" if your database is on the same server
$userName = "root";  // Replace with your MySQL username
$password = "";      // Replace with your MySQL password
$database = "login-register"; // Replace with your database name

// Create a connection
$conn = new mysqli($host, $userName, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}