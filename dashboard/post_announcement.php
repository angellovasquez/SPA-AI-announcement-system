<?php
header("Content-Type: application/json"); 
require_once "../database.php"; // Include database connection

// ✅ Use $_POST instead of json_decode(file_get_contents("php://input"), true)
$title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
$announcementText = isset($_POST["announcement"]) ? trim($_POST["announcement"]) : "";
$sendSMS = isset($_POST["sendSMS"]) && $_POST["sendSMS"] == 1;
$sendEmail = isset($_POST["sendEmail"]) && $_POST["sendEmail"] == 1;
$smsGatewayIP = isset($_POST["smsGatewayIP"]) ? trim($_POST["smsGatewayIP"]) : "";
$status = isset($_POST["status"]) ? $_POST["status"] : "draft"; // Default to draft

// ✅ Validate input
if (empty($title) || empty($announcementText)) {
    echo json_encode(["success" => false, "message" => "Title and announcement are required."]);
    exit;
}

if (!$sendSMS && !$sendEmail) {
    echo json_encode(["success" => false, "message" => "Select at least one option (SMS or Email)."]);
    exit;
}

if ($sendSMS && empty($smsGatewayIP)) {
    echo json_encode(["success" => false, "message" => "SMS Gateway IP Address is required."]);
    exit;
}

$status = "published"; // ✅ Ensure it's marked as published
// ✅ Insert into database
$stmt = $conn->prepare("INSERT INTO announcements (title, content, send_sms, send_email, status) VALUES (?, ?, ?, ?, 'published')");
$stmt->bind_param("ssii", $title, $announcementText, $sendSMS, $sendEmail);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
    exit;
}

$response = ["success" => true, "message" => "Announcement successfully posted!"];

// ✅ Send SMS if selected
if ($sendSMS) {
    $response["smsResponse"] = sendSMSFunction($announcementText, $smsGatewayIP);
}

// ✅ Send Email if selected
if ($sendEmail) {
    $response["emailResponse"] = sendEmailFunction($announcementText);
}

echo json_encode($response);

// ✅ Function to send SMS
function sendSMSFunction($message, $ip) {
    $data = ["MSG_SMS" => $message, "IPadd" => $ip];
    return sendRequest("send_sms.php", $data);
}

// ✅ Function to send Email
function sendEmailFunction($message) {
    $data = ["emailMessage" => $message];
    return sendRequest("send_email.php", $data);
}

// ✅ Function to send HTTP POST request using cURL
function sendRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    return $error ? "Error: " . $error : $response;
}
?>
