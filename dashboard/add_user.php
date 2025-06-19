<?php

require_once "../database.php"; // Include database connection

header('Content-Type: application/json'); // Ensure JSON response

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = mysqli_real_escape_string($conn, $_POST["firstName"]);
    $lastName = mysqli_real_escape_string($conn, $_POST["lastName"]);
    $contactNumber = mysqli_real_escape_string($conn, $_POST["contactNumber"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $role = mysqli_real_escape_string($conn, $_POST["role"]);
    $userName = mysqli_real_escape_string($conn, $_POST["userName"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);

    // Profile picture handling
    $profilePicture = null; // Default null value

    if (!empty($_FILES['profilePicture']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB limit

        if (in_array($_FILES['profilePicture']['type'], $allowedTypes) && $_FILES['profilePicture']['size'] <= $maxFileSize) {
            // Securely read the uploaded file
            $imageData = file_get_contents($_FILES['profilePicture']['tmp_name']);
            $profilePicture = base64_encode($imageData);
        } else {
            $errors[] = "Invalid profile picture. Only JPG, PNG, or GIF files under 2MB are allowed.";
        }
    } else {
        // Assign default profile picture if none uploaded
        $defaultImagePath = "images/default picture.jpg"; // Ensure path is correct
        if (file_exists($defaultImagePath)) {
            $imageData = file_get_contents($defaultImagePath);
            $profilePicture = base64_encode($imageData);
        } else {
            $errors[] = "Default profile picture is missing. Please contact support.";
        }
    }

    // Validation
    if (empty($firstName) || empty($lastName) || empty($contactNumber) || empty($email) || empty($role) || empty($userName) || empty($password)) {
        $errors[] = "All fields are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if (!preg_match('/^[0-9]{11}$/', $contactNumber)) {
        $errors[] = "Contact number must be exactly 11 digits.";
    }
    if (!in_array($role, ["Admin", "Teacher"])) {
        $errors[] = "Invalid role selected.";
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

    // Display errors if any
    if (!empty($errors)) {
        echo json_encode(["status" => "error", "message" => implode("<br>", $errors)]);
        exit;
    }

    // Hash password and save to database
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users 
        (firstName, lastName, contact_number, email, role, userName, password, profile_picture) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssssssss",
        $firstName,
        $lastName,
        $contactNumber,
        $email,
        $role,
        $userName,
        $passwordHash,
        $profilePicture
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User added successfully!"]);
    } else {
        $errors[] = "Something went wrong while inserting the user. Please try again.";
        echo json_encode(["status" => "error", "message" => implode("<br>", $errors)]);
    }
}
?>
