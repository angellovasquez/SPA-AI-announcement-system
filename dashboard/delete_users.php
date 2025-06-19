<?php
session_start();
require_once '../database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $firstName = $_GET['firstName'] ?? '';
    $lastName = $_GET['lastName'] ?? '';
    $fullName = trim($firstName . ' ' . $lastName);

    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User '$fullName' has been deleted successfully.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting user '$fullName'.";
        $_SESSION['msg_type'] = "danger";
    }

    $stmt->close();
} else {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['msg_type'] = "warning";
}

header("Location: manage_users.php");
exit();
?>
