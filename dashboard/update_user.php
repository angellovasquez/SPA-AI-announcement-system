<?php
// Database connection
require '../database.php';

// Get user ID and new status
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['user_id']) && isset($_POST['new_status'])) {
    $userId = intval($_POST['user_id']);
    $newStatus = strtolower($_POST['new_status']) === 'active' ? 'Active' : 'Inactive';

    // Ensure user_id is valid
    if ($userId <= 0) {
        echo "Invalid user ID.";
        exit();
    }

    // Update status in the database
    $sql = "UPDATE members SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newStatus, $userId);

    if ($stmt->execute()) {
        header("Location: manage_users.php"); // Redirect back to dashboard
        exit(); // Important to stop further code execution
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
