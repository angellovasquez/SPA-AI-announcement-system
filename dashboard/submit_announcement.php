<?php
include '../database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $send_sms = isset($_POST['send_sms']) ? 1 : 0;
    $send_email = isset($_POST['send_email']) ? 1 : 0;

    $sql = "INSERT INTO announcements (title, content, send_sms, send_email) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $title, $content, $send_sms, $send_email);

    if ($stmt->execute()) {
        echo "Announcement saved successfully!";
    } else {
        echo "Error saving announcement.";
    }

    $stmt->close();
    $conn->close();
}
?>