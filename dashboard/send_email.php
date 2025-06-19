<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


header('Content-Type: application/json');
include '../database.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request. Must be POST."]);
    exit;
}

// Get announcement details
$emailMessage = trim($_POST["emailMessage"] ?? '');
$years = json_decode($_POST["years"] ?? '[]', true);
$courses = json_decode($_POST["courses"] ?? '[]', true);

if (empty($emailMessage)) {
    echo json_encode(["success" => false, "message" => "No announcement message provided."]);
    exit;
}

if (empty($years) || empty($courses)) {
    echo json_encode(["success" => false, "message" => "No recipients selected."]);
    exit;
}

// Year & Course Mapping
$yearMapping = [
    "1st Year" => "First Year",
    "2nd Year" => "Second Year",
    "3rd Year" => "Third Year",
    "4th Year" => "Fourth Year",
    "Teacher" => "Teacher"
];

$courseMapping = [
    "Computer Science" => "BSCS",
    "Information Technology" => "BSIT",
    "Business Administration" => "BSBA",
    "Entrepreneurship" => "BSENTREP",
    "Accounting Information Systems" => "BSAIS",
    "Office Administration" => "BSOA",
    "Technical Teacher Education" => "BSVTTE",
    "Teacher" => "Teacher"
];

$selectedYears = array_map(fn($y) => $yearMapping[$y] ?? '', $years);
$selectedCourses = array_map(fn($c) => $courseMapping[$c] ?? '', $courses);

$selectedYears = array_filter($selectedYears);
$selectedCourses = array_filter($selectedCourses);

if (empty($selectedYears) || empty($selectedCourses)) {
    echo json_encode(["success" => false, "message" => "Invalid year or course selection."]);
    exit;
}

$yearPlaceholders = implode(',', array_fill(0, count($selectedYears), '?'));
$coursePlaceholders = implode(',', array_fill(0, count($selectedCourses), '?'));

$sql = "SELECT id, email FROM members 
        WHERE status = 'Active' 
        AND year IN ($yearPlaceholders) 
        AND course IN ($coursePlaceholders)";

$stmt = $conn->prepare($sql);
$params = array_merge($selectedYears, $selectedCourses);
$types = str_repeat("s", count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$userIds = [];
$emails = [];

while ($row = $result->fetch_assoc()) {
    $userIds[] = $row['id'];
    $emails[] = $row['email'];
}

$stmt->close();

// Check if recipients exist
if (empty($emails)) {
    echo json_encode(["success" => false, "message" => "No matching recipients found."]);
    exit;
}

// Initialize PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'spaisystem69@gmail.com'; // ⚠ Move to a config file for security
    $mail->Password = 'fvxd kegs fxeu rzzx'; // ⚠ NEVER expose passwords in public code
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->CharSet = 'UTF-8';
    $mail->setFrom('spaisystem69@gmail.com', 'ACTSCC • powered by SPA-AI');

    // Add recipients
    foreach ($emails as $email) {
        $mail->addAddress($email);
    }

    // Email Content
    $mail->isHTML(true);
    $mail->Subject = 'New Announcement From ACTSCC • powered by SPA-AI';
    $mail->Body = '<p style="color: black; font-size: 14px;">' . nl2br(htmlspecialchars($emailMessage)) . '</p>';

    // Send email
    if ($mail->send()) {
        // ✅ Log engagement for each user
        $stmt = $conn->prepare("INSERT INTO engagement (user_id, engagement_type) VALUES (?, 'email') 
                                ON DUPLICATE KEY UPDATE engagement_date = CURRENT_TIMESTAMP");

        foreach ($userIds as $userId) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }

        echo json_encode(["success" => true, "message" => "Announcement email sent successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to send email."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Email sending error: " . $mail->ErrorInfo]);
}

// Close DB connection
$conn->close();
?>
