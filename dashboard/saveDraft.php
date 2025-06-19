<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

require '../database.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die(json_encode(["success" => false, "message" => "Invalid request method."]));
}

$title = isset($_POST['title']) ? trim($_POST['title']) : "Untitled Draft";
$content = isset($_POST['announcement']) ? trim($_POST['announcement']) : '';

if (empty($content)) {
    die(json_encode(["success" => false, "message" => "Draft content cannot be empty."]));
}

$stmt = $conn->prepare("INSERT INTO announcements (title, content, status, created_at) VALUES (?, ?, 'draft', NOW())");
$stmt->bind_param("ss", $title, $content);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Draft saved successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Error saving draft: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
