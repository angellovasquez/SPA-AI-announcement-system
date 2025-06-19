<?php
header("Content-Type: application/json");
require "../database.php"; // Ensure database connection

// Read JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Ensure JSON is received correctly
if ($data === null) {
    echo json_encode(["error" => "Invalid JSON input"]);
    exit;
}

$years = $data['years'] ?? [];
$courses = $data['courses'] ?? [];

if (empty($years) || empty($courses)) {
    echo json_encode(["error" => "No recipients selected"]);
    exit;
}

// Year & Course Mappings (DB Format)
$yearMapping = [
    "1st Year" => "First year",
    "2nd Year" => "Second year",
    "3rd Year" => "Third year",
    "4th Year" => "Fourth year",
    "Teacher" => "Teacher"
];

$courseMapping = [
    "Computer Science" => "BSCS",
    "Information Technology" => "BSIT",
    "Business Administration" => "BSBA",
    "Entrepreneurship" => "BS-ENTREP",
    "Accounting Information Systems" => "BS-AIS", // FIXED
    "Office Administration" => "BSOA",
    "Technical Teacher Education" => "BTVTEd", // FIXED
    "Teacher" => "Teacher"
];


// Convert selected values to database format
$selectedYears = array_map(fn($y) => $yearMapping[$y] ?? '', $years);
$selectedCourses = array_map(fn($c) => $courseMapping[$c] ?? '', $courses);

// Remove empty values
$selectedYears = array_filter($selectedYears);
$selectedCourses = array_filter($selectedCourses);

// Ensure valid selections exist
if (empty($selectedYears) || empty($selectedCourses)) {
    echo json_encode(["error" => "Invalid year or course selection"]);
    exit;
}

// Ensure database connection
if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Debug: Log selections before running SQL
error_log("Selected Years: " . implode(", ", $selectedYears));
error_log("Selected Courses: " . implode(", ", $selectedCourses));

// Construct SQL Query
$sql = "SELECT email, contact_number, status, year, course FROM members WHERE status = 'Active'";

// Apply year and course filters
$conditions = [];
$params = [];
$types = "";

if (!empty($selectedYears)) {
    $conditions[] = "year IN (" . implode(",", array_fill(0, count($selectedYears), "?")) . ")";
    foreach ($selectedYears as $year) {
        $params[] = $year;
        $types .= "s";
    }
}

if (!empty($selectedCourses)) {
    $conditions[] = "course IN (" . implode(",", array_fill(0, count($selectedCourses), "?")) . ")";
    foreach ($selectedCourses as $course) {
        $params[] = $course;
        $types .= "s";
    }
}

// Add conditions to query
if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

// Debug: Log the SQL query
error_log("SQL Query: " . $sql);

$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $recipients = [];
    while ($row = $result->fetch_assoc()) {
        $recipients[] = [
            "email" => $row['email'],
            "contact_number" => $row['contact_number'],
            "status" => $row['status'],
            "year" => $row['year'],  // Debugging: Ensure correct year
            "course" => $row['course'] // Debugging: Ensure correct course
        ];
    }

    $stmt->close();
    
    if (!empty($recipients)) {
        echo json_encode($recipients);
    } else {
        echo json_encode(["error" => "No recipients found"]);
    }
} else {
    echo json_encode(["error" => "Query preparation failed: " . $conn->error]);
}
?>
