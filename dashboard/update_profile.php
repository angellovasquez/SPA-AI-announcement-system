<?php
session_start();
require '../database.php'; // Database connection

$response = ["success" => false, "message" => "Failed to update profile."];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = 1; // Replace with session user ID
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];

    $stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ? WHERE id = ?");
    $stmt->bind_param("ssi", $firstname, $lastname, $user_id);

    if ($stmt->execute()) {
        $response = ["success" => true, "message" => "Profile updated successfully."];
    }
}

echo json_encode($response);
?>