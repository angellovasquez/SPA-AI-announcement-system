<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_unset();
session_destroy();

// Clear session cookie (if exists)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/', '', true, true);
}

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login page with a success message
header("Location: ../login.php?message=Successfully+logged+out.");
exit();
?>
