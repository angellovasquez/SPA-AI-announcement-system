<?php
// Database connection
require_once '../database.php';

// Get the draft ID and content from the POST request
$id = isset($_POST['id']) ? $_POST['id'] : '';
$content = isset($_POST['content']) ? $_POST['content'] : '';

// Check if the ID and content are provided
if ($id && $content) {
    // Prepare the SQL query to update the draft in the database
    $query = "UPDATE announcements SET content = ? WHERE id = ? AND status = 'draft'";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        // Bind the parameters and execute the query
        $stmt->bind_param("si", $content, $id);
        if ($stmt->execute()) {
            // Return success response if the update was successful
            echo json_encode(["success" => true, "message" => "Draft updated successfully"]);
        } else {
            // Return error message if execution failed
            echo json_encode(["success" => false, "message" => "Error updating draft"]);
        }
        $stmt->close();
    } else {
        // Return error message if the prepared statement failed
        echo json_encode(["success" => false, "message" => "Error preparing query"]);
    }
} else {
    // Return error if the ID or content is missing
    echo json_encode(["success" => false, "message" => "Invalid data"]);
}

// Close the database connection
$conn->close();
?>
