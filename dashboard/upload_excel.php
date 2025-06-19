<?php
session_start();
require_once '../database.php';
require_once 'excel_upload/vendor/autoload.php';

if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] === 0) {
    $fileTmpPath = $_FILES['excelFile']['tmp_name'];
    $fileExtension = pathinfo($_FILES['excelFile']['name'], PATHINFO_EXTENSION);

    if (in_array($fileExtension, ['xlsx', 'xls'])) {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $inserted = 0;
            $skipped = 0;

            foreach ($rows as $index => $row) {
                if ($index === 0 || empty(array_filter($row))) continue;

                $firstName = isset($row[0]) ? trim($row[0]) : '';
                $lastName = isset($row[1]) ? trim($row[1]) : '';
                $contactNumber = isset($row[2]) && !empty($row[2]) ? trim($row[2]) : 'Unknown';
                $email = isset($row[3]) ? trim($row[3]) : '';
                $year = isset($row[4]) ? trim($row[4]) : '';
                $course = isset($row[5]) ? trim($row[5]) : '';
                $role = isset($row[6]) ? trim($row[6]) : '';

                // Check if the email or contact number already exists
                $checkSql = "SELECT * FROM members WHERE email = ? OR contact_number = ?";
                $checkStmt = mysqli_prepare($conn, $checkSql);
                mysqli_stmt_bind_param($checkStmt, 'ss', $email, $contactNumber);
                mysqli_stmt_execute($checkStmt);
                $result = mysqli_stmt_get_result($checkStmt);

                if (mysqli_num_rows($result) > 0) {
                    $skipped++;
                    mysqli_stmt_close($checkStmt);
                    continue; // Skip if already exists
                }
                mysqli_stmt_close($checkStmt);

                // Insert new data
                $insertSql = "INSERT INTO members (firstName, lastName, contact_number, email, year, course, role)
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
                $insertStmt = mysqli_prepare($conn, $insertSql);
                mysqli_stmt_bind_param($insertStmt, 'sssssss', $firstName, $lastName, $contactNumber, $email, $year, $course, $role);
                mysqli_stmt_execute($insertStmt);
                mysqli_stmt_close($insertStmt);

                $inserted++;
            }

            $_SESSION['message'] = "Imported: $inserted | Skipped (existing): $skipped";
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = "Error reading Excel file: " . $e->getMessage();
            $_SESSION['msg_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = "Invalid file type. Please upload an Excel file (.xlsx or .xls).";
        $_SESSION['msg_type'] = 'danger';
    }
} else {
    $_SESSION['message'] = "No file uploaded or an error occurred.";
    $_SESSION['msg_type'] = 'danger';
}

header('Location: manage_users.php');
exit();
?>
