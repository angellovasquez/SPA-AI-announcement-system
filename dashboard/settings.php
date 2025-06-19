<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once "main.php";
include '../database.php'; // Adjusted path

// Fetch settings from the database
$result = mysqli_query($conn, "SELECT * FROM settings WHERE id = 1");
$settings = mysqli_fetch_assoc($result) ?? [];

// Define folder for uploads
$uploadDir = "../uploads/";

// Get current image filenames or fallback to defaults
$home_bg = isset($settings['home_bg']) && !empty($settings['home_bg']) ? $uploadDir . $settings['home_bg'] : '../images/default_home.png';
$login_bg = isset($settings['login_bg']) && !empty($settings['login_bg']) ? $uploadDir . $settings['login_bg'] : '../images/default_login.png';
$user_bg = isset($settings['user_bg']) && !empty($settings['user_bg']) ? $uploadDir . $settings['user_bg'] : '../images/default_user.png';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pages = ['home' => 'home_bg', 'login' => 'login_bg', 'user' => 'user_bg'];
    $updated = false;

    foreach ($pages as $page => $column) {
        if (!empty($_FILES[$page]["name"])) {
            $fileName = time() . "_" . basename($_FILES[$page]["name"]); // Unique filename
            $targetFile = $uploadDir . $fileName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Validate image type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($imageFileType, $allowedTypes)) {
                echo "<script>alert('Only JPG, JPEG, PNG, and GIF files are allowed.');</script>";
                continue;
            }

            // Ensure the uploads directory exists
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Move the uploaded file
            if (move_uploaded_file($_FILES[$page]["tmp_name"], $targetFile)) {
                $sql = "UPDATE settings SET $column = ? WHERE id = 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $fileName);
                if ($stmt->execute()) {
                    $updated = true;
                }
            }
        }
    }

    if ($updated) {
        header("Location: settings.php?success=1");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Customize Background</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0" id="header-title">
                    <i class="fas fa-cog me-2"></i>Settings
                </h2>
            </div>
        </nav>
        <style>
            body {
                background-color: #f8f9fa;
            }

            .container-custom {
                max-width: 900px;
                width: 100%;
                margin: auto;
            }

            .card {
                background: linear-gradient(to bottom, #ece9e6, #ffffff);
                border: 2px solid #2a9d8f;
                border-radius: 10px;
                box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            }

            .preview-img {
                width: 50%;
                max-width: 250px;
                height: auto;
                display: block;
                margin: 10px auto;
                border-radius: 10px;
                border: 2px solid #2a9d8f;
                box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            }
        </style>
</head>

<body>

    <div class="container container-custom">
        <div class="card p-4 shadow">
            <h3 class="text-center mb-4">
                <i class="fas fa-cog"></i> System Customization
            </h3>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success text-center">✅ Successfully customized!</div>
            <?php endif; ?>

            <!-- Background Customization Form -->
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-home"></i> <b>Home Page Background:</b>
                    </label>
                    <input type="file" class="form-control" name="home" accept="image/*" onchange="previewImage(event, 'homePreview')">
                    <img src="<?= !empty($home_bg) ? $home_bg : 'default-home.jpg'; ?>" class="preview-img" id="homePreview">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-sign-in-alt"></i> <b>Login Page Background:</b>
                    </label>
                    <input type="file" class="form-control" name="login" accept="image/*" onchange="previewImage(event, 'loginPreview')">
                    <img src="<?= !empty($login_bg) ? $login_bg : 'default-login.jpg'; ?>" class="preview-img" id="loginPreview">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user"></i> <b>User Page Background:</b>
                    </label>
                    <input type="file" class="form-control" name="user" accept="image/*" onchange="previewImage(event, 'userPreview')">
                    <img src="<?= !empty($user_bg) ? $user_bg : 'default-user.jpg'; ?>" class="preview-img" id="userPreview">
                </div>

                <button type="submit" class="btn btn-primary mt-3 w-100">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>

            <!-- Social Links Form -->
            <form action="update_links.php" method="POST" class="mt-4">
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fab fa-facebook-f"></i> <b>Facebook URL:</b>
                    </label>
                    <input type="text" class="form-control" name="facebook" value="<?= htmlspecialchars($socialLinks['facebook'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fab fa-facebook-messenger"></i> <b>Messenger URL:</b>
                    </label>
                    <input type="text" class="form-control" name="messenger" value="<?= htmlspecialchars($socialLinks['messenger'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-globe"></i> <b>Website URL:</b>
                    </label>
                    <input type="text" class="form-control" name="website" value="<?= htmlspecialchars($socialLinks['website'] ?? '') ?>">
                </div>

                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-save"></i> Save Links
                </button>
            </form>
        </div>
    </div>


    <script>
        function previewImage(event, previewId) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById(previewId);
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>

</body>

</html>