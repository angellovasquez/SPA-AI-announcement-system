<?php
include 'database.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the uploads folder exists
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // File details
    $imageName = basename($_FILES["profilePicture"]["name"]);
    $targetFilePath = $targetDir . $imageName;
    $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Allowed file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    // Check if the uploaded file is an image
    if (in_array($imageFileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $targetFilePath)) {
            // Update user's image path in the database
            $userId = 1; // Replace with dynamic user ID if available
            $sql = "UPDATE users SET image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $targetFilePath, $userId);
            $stmt->execute();

            echo "Profile picture uploaded successfully!";
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Invalid file type. Please upload a JPG, JPEG, PNG, or GIF.";
    }
}
?>
