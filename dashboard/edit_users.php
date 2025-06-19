<?php
session_start();
include "../database.php"; // Ensure correct path to database.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST data with correct variable names
    $id = $_POST['id'] ?? '';
    $firstname = $_POST['firstName'] ?? ''; // FIXED: Matches form input name
    $lastname = $_POST['lastName'] ?? ''; // FIXED: Matches form input name
    $email = $_POST['email'] ?? '';
    $year = $_POST['year'] ?? '';
    $course = $_POST['course'] ?? '';
    $contact_number = $_POST['contactNumber'] ?? ''; // FIXED: Matches form input name
    $role = $_POST['role'] ?? '';

    // Validate: Ensure ALL required fields are filled
    if (empty($id) || empty($firstname) || empty($lastname) || empty($email) || empty($year) || empty($course) || empty($contact_number) || empty($role)) {
        $_SESSION['message'] = "All fields are required!";
        $_SESSION['msg_type'] = "danger";
        header("Location: manage_users.php");
        exit();
    }

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("UPDATE members SET firstname=?, lastname=?, email=?, year=?, course=?, contact_number=?, role=? WHERE id=?");
    $stmt->bind_param("sssssssi", $firstname, $lastname, $email, $year, $course, $contact_number, $role, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User updated successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating user!";
        $_SESSION['msg_type'] = "danger";
    }

    $stmt->close();
    $conn->close();

    header("Location: manage_users.php"); // Redirect back to user page
    exit();
} else {
    $_SESSION['message'] = "Invalid request!";
    $_SESSION['msg_type'] = "warning";
    header("Location: manage_users.php");
    exit();
}
?>
