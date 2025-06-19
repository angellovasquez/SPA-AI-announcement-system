<?php
// Include the database connection file
include '../database.php'; // adjust if database.php is in another path

// Map the variables from your database config
$host = $hostName;
$user = $dbUser;
$pass = $dbPassword;
$dbname = $dbName;

// Generate the backup filename with timestamp
$backupFile = 'backup_' . $dbname . '_' . date("Y-m-d_H-i-s") . '.sql';

// Full path to mysqldump in XAMPP
$mysqldump = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\"";

// Build the command
$command = "$mysqldump --user=$user --password=$pass $dbname > $backupFile";

// Run the command
system($command);

// Debug: show the command that was run (optional for troubleshooting)
// echo "<pre>$command</pre>";

// Check if backup file was created and download it
if (file_exists($backupFile)) {
    // Force download headers
    header('Content-Description: File Transfer');
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename=' . basename($backupFile));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backupFile));
    readfile($backupFile);

    // Delete the file after download
    unlink($backupFile);
    exit;
} else {
    echo "⚠️ Backup failed. Make sure mysqldump is working and the database has data.";
}
?>
