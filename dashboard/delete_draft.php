<?php
require_once '../database.php';

// Get the draft ID from the POST request
$id = isset($_POST['id']) ? $_POST['id'] : '';

if ($id) {
    // Delete the draft from the database
    $query = "DELETE FROM announcements WHERE id = ? AND status = 'draft'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Draft deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error deleting draft"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
}

$conn->close();
?>
