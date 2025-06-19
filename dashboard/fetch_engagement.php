<?php
header('Content-Type: application/json');
include '../database.php';

// Month mapping
$monthMap = [
    "01" => "January", "02" => "February", "03" => "March", "04" => "April",
    "05" => "May", "06" => "June", "07" => "July", "08" => "August",
    "09" => "September", "10" => "October", "11" => "November", "12" => "December"
];

// Get the current year and month
$currentYear = date("Y");
$currentMonth = date("m");

// Fetch engagement count for the current month only
$query = "
    SELECT 
        engagement_type, 
        COUNT(*) AS count 
    FROM engagement 
    WHERE YEAR(engagement_date) = ? AND MONTH(engagement_date) = ?
    GROUP BY engagement_type";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $currentYear, $currentMonth);
$stmt->execute();
$result = $stmt->get_result();

$data = [
    "$currentYear " . $monthMap[$currentMonth] => ['email' => 0, 'sms' => 0]
];

while ($row = $result->fetch_assoc()) {
    $data["$currentYear " . $monthMap[$currentMonth]][$row['engagement_type']] = (int) $row['count'];
}

$stmt->close();
$conn->close();

echo json_encode($data);
?>
