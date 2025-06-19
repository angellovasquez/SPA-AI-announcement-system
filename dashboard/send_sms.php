<?php
header('Content-Type: application/json');
include '../database.php';

// Ensure request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request. Must be POST."]);
    exit;
}

// Validate required parameters
$message = trim($_POST['MSG_SMS'] ?? '');
$ip = trim($_POST['IPadd'] ?? '');
$years = json_decode($_POST["years"] ?? '[]', true);
$courses = json_decode($_POST["courses"] ?? '[]', true);

// Check if message is empty
if (empty($message)) {
    echo json_encode(["success" => false, "message" => "Message cannot be empty."]);
    exit;
}

// ✅ Sanitize message (Remove unwanted characters)
$message = str_replace(["\r", "\n", "\\"], ["", "", ""], $message);

// Validate IP address format
if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    echo json_encode(["success" => false, "message" => "Invalid IP address format."]);
    exit;
}

// Validate year and course selection
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

// Prepare SQL Query
$yearPlaceholders = implode(',', array_fill(0, count($selectedYears), '?'));
$coursePlaceholders = implode(',', array_fill(0, count($selectedCourses), '?'));

$sql = "SELECT id, contact_number FROM members 
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
$contacts = [];

while ($row = $result->fetch_assoc()) {
    if (!empty($row['contact_number'])) {
        $contacts[] = $row['contact_number'];
        $userIds[] = $row['id'];
    }
}

$stmt->close();

// Check if recipients exist
if (empty($contacts)) {
    echo json_encode(["success" => false, "message" => "No matching recipients found."]);
    exit;
}

// Function to send SMS with retries
function sendSMS($phone, $message, $ip, $maxRetries = 3)
{
    $url = "http://$ip:8080/v1/sms/send/?phone=" . urlencode($phone) . "&message=" . urlencode($message);
    $attempt = 0;

    while ($attempt < $maxRetries) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 && $response !== false) {
            return "success";
        }
        $attempt++;
        sleep(1); // Wait before retrying
    }

    return "failed";
}

// Process SMS Sending & Log Engagement
$smsResults = [];
$stmt = $conn->prepare("INSERT INTO engagement (user_id, engagement_type) VALUES (?, 'sms') 
                        ON DUPLICATE KEY UPDATE engagement_date = CURRENT_TIMESTAMP");

foreach ($contacts as $index => $phone) {
    $status = sendSMS($phone, $message, $ip);
    $smsResults[] = ["phone" => $phone, "status" => $status];

    // Log successful SMS in engagement table
    if ($status === "success") {
        $stmt->bind_param("i", $userIds[$index]);
        $stmt->execute();
    }

    // ✅ Limit sending rate to avoid overload (pause after every 10 messages)
    if (($index + 1) % 10 == 0) {
        sleep(1);
    }
}

$stmt->close();
$conn->close();

echo json_encode(["success" => true, "message" => "SMS processing completed.", "results" => $smsResults]);
exit;
?>

