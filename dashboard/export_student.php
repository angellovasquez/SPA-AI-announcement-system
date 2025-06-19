<?php
// Include the database connection file
include '../database.php'; // Ensure the path is correct

// Include PhpSpreadsheet library
require 'excel_upload/vendor/autoload.php'; // Adjust the path if needed

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create a new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the title of the sheet
$sheet->setTitle("Students Data");

// Fetch all member data from the database
$sql = "SELECT * FROM members";  // Adjust the table name as needed
$result = mysqli_query($conn, $sql);

// Set the headers for the Excel file
$headers = ["ID", "First Name", "Last Name", "Contact Number", "Email", "Role", "Year", "Course", "Registration Date", "Status"];
$sheet->fromArray($headers, NULL, 'A1');

// Bold the header row
$sheet->getStyle('A1:J1')->getFont()->setBold(true);

// Auto-size each column
foreach (range('A', 'J') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Add the data to the sheet
$rowNum = 2;  // Start from the second row
while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowNum, $row['id']);
    $sheet->setCellValue('B' . $rowNum, $row['firstName']);
    $sheet->setCellValue('C' . $rowNum, $row['lastName']);
    $sheet->setCellValue('D' . $rowNum, $row['contact_number']);
    $sheet->setCellValue('E' . $rowNum, $row['email']);
    $sheet->setCellValue('F' . $rowNum, $row['role']);
    $sheet->setCellValue('G' . $rowNum, $row['year']);
    $sheet->setCellValue('H' . $rowNum, $row['course']);
    $sheet->setCellValue('I' . $rowNum, $row['registration_date']);
    $sheet->setCellValue('J' . $rowNum, $row['status']);
    $rowNum++;
}

// Apply text wrapping to all cells (headers + data rows)
$sheet->getStyle('A1:J' . ($rowNum - 1))->getAlignment()->setWrapText(true);

// Write the file to the browser
$writer = new Xlsx($spreadsheet);
$filename = "students_data_" . date("Y-m-d_H-i-s") . ".xlsx";  // Unique filename with timestamp

// Send headers to force download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Write the Excel file to output
$writer->save('php://output');
exit;
?>
