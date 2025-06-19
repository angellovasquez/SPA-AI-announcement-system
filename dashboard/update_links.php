<?php
include '../database.php'; // Ensure this correctly connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $links = [
        'facebook' => $_POST['facebook'],
        'messenger' => $_POST['messenger'],
        'website' => $_POST['website']
    ];

    foreach ($links as $platform => $url) {
        $stmt = $conn->prepare("UPDATE social_links SET url = ? WHERE platform = ?");
        $stmt->bind_param("ss", $url, $platform);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: settings.php?success=1");
    exit;
}
?>
