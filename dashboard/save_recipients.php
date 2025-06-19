<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require '../database.php'; // Make sure this file exists and connects properly

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "Invalid JSON received"]);
    exit;
}

// ✅ Extract and validate data
$title = isset($data['title']) ? $data['title'] : null;
$announcement = isset($data['announcement']) ? $data['announcement'] : null;
$years = isset($data['years']) ? json_encode($data['years']) : null;
$courses = isset($data['courses']) ? json_encode($data['courses']) : null;
$recipientCount = isset($data['recipientCount']) ? (int) $data['recipientCount'] : 0;

if (!$title || !$announcement || !$years || !$courses) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

// ✅ Insert into database (Make sure `announcements` table exists and has these columns)
$query = "INSERT INTO announcements (title, announcement, years, courses, recipient_count) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssi", $title, $announcement, $years, $courses, $recipientCount);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
