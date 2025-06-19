<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json"); // Ensure JSON output

include "../database.php";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = mysqli_real_escape_string($conn, $_POST["firstName"]);
    $lastName = mysqli_real_escape_string($conn, $_POST["lastName"]);
    $contactNumber = mysqli_real_escape_string($conn, $_POST["contactNumber"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $year = mysqli_real_escape_string($conn, $_POST["year"]);
    $course = mysqli_real_escape_string($conn, $_POST["course"]);
    $role = mysqli_real_escape_string($conn, $_POST["role"]);
    $userName = mysqli_real_escape_string($conn, $_POST["userName"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);

    $errors = [];

    if (empty($firstName) || empty($lastName) || empty($contactNumber) || empty($email) || empty($role) || empty($userName) || empty($password)) {
        $errors[] = "All fields are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if (strlen($contactNumber) != 11) {
        $errors[] = "Contact number must be exactly 11 digits long.";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Email already exists.";
    }

    // Check if contact number already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE contact_number = ?");
    $stmt->bind_param("s", $contactNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Contact number already exists.";
    }

    if (!empty($errors)) {
        echo json_encode(["status" => "error", "message" => implode("<br>", $errors)]);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, contact_number, email, role, year, course, userName, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $firstName, $lastName, $contactNumber, $email, $role, $year, $course, $userName, $passwordHash);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User registered successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Something went wrong. Try again."]);
    }
}
?>
