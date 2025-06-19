<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">



    <?php
    ob_start(); // Start output buffering
    include_once "main.php";

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    include '../database.php'; // Database connection file

    // Fetch user details
    if (isset($_SESSION['currentUser'])) {
        $userId = $_SESSION['currentUser'];

        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $firstName = htmlspecialchars($_POST['firstname']);
        $lastName = htmlspecialchars($_POST['lastname']);
        $contactNumber = htmlspecialchars($_POST['contact_number']);
        $email = htmlspecialchars($_POST['email']);
        $userName = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Handle Profile Picture Upload
        $profilePicture = $user['profile_picture'];

        if (!empty($_FILES['profilePicture']['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 2 * 1024 * 1024;
            $fileType = $_FILES['profilePicture']['type'];
            $fileSize = $_FILES['profilePicture']['size'];
            $tmpName = $_FILES['profilePicture']['tmp_name'];

            if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
                $imageData = file_get_contents($tmpName);
                $profilePicture = base64_encode($imageData);
            } else {
                $_SESSION['error_message'] = "Invalid file. Only JPG, PNG, or GIF files under 2MB are allowed.";
                header("Location: profile.php");
                exit();
            }
        }

        // Update user details in the database including profile picture
        $updateQuery = "UPDATE users 
                    SET firstname = ?, lastname = ?, contact_number = ?, email = ?, username = ?, password = ?, profile_picture = ?
                    WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssssssi", $firstName, $lastName, $contactNumber, $email, $userName, $hashedPassword, $profilePicture, $userId);

        if ($stmt->execute()) {
            // Update session data to reflect changes instantly
            $_SESSION['firstName'] = $firstName;
            $_SESSION['lastName'] = $lastName;

            if (!empty($_FILES['profilePicture']['name'])) {
                $_SESSION['profilePicture'] = 'data:image/jpeg;base64,' . $profilePicture;
            }

            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Failed to update profile. Please try again.";
            header("Location: profile.php");
            exit();
        }
    }

    ob_end_flush(); // End output buffering
    ?>

    <!-- Bootstrap CSS -->


    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0" id="header-title">
                    <i class="fas fa-user me-2"></i>Edit Profile
                </h2>
            </div>
        </nav>
        <style>
            .container-box {
                background: linear-gradient(to bottom, #ece9e6, #ffffff);
                border: 2px solid #2a9d8f;
                border-radius: 15px;
                padding: 30px;
                background-color: #f8f9fa;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .position-relative {
                display: inline-block;
            }

            .profile-circle {
                display: block;
                border: 3px solid #ddd;
            }

            .camera-icon {
                position: absolute;
                bottom: 5px;
                right: 5px;
                width: 40px;
                height: 40px;
                background-color: white;
                border-radius: 50%;
                display: flex;
                justify-content: center;
                align-items: center;
                cursor: pointer;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }

            .camera-icon i {
                font-size: 18px;
                color: #333;
            }

            .btn-success {
                background-color: #2a9d8f;
                /* Teal */
                border: none;
                color: white;
                transition: background 0.3s ease-in-out;
            }

            .btn-success:hover {
                background-color: #21867a;
                /* Darker Teal */
            }

            /* Danger Button */
            .btn-danger {
                background-color: #e76f51;
                /* Soft Coral */
                border: none;
                color: white;
                transition: background 0.3s ease-in-out;
            }

            .btn-danger:hover {
                background-color: #d65c3b;
                /* Darker Coral */
            }
        </style>
</head>

<body>
    <div class="container">
        <div class="container-box">
            <!-- Display success or error messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success fade show" id="alertMessage">
                    <?= $_SESSION['success_message']; ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger fade show" id="alertMessage">
                    <?= $_SESSION['error_message']; ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data">
                <div class="row g-3 mb-3">

                    <!-- Profile Picture -->
                    <!-- Profile Picture -->
                    <div class="col-md-12 d-flex justify-content-center align-items-center flex-column text-center position-relative">
                        <label for="profilePicture" class="position-relative">
                            <!-- Profile Image -->
                            <img id="profileImage"
                                src="<?= !empty($user['profile_picture']) ? 'data:image/jpeg;base64,' . $user['profile_picture'] : 'default-profile.jpg' ?>"
                                alt="Profile Picture"
                                class="profile-circle rounded-circle img-fluid shadow"
                                style="width: 150px; height: 150px; object-fit: cover;">

                            <!-- Camera Icon -->
                            <div class="camera-icon" onclick="document.getElementById('profilePicture').click();">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                        </label>
                        <input type="file" id="profilePicture" name="profilePicture" accept="image/*" style="display: none;">
                    </div>
                    <!-- First Name -->
                    <div class="col-md-6 position-relative">
                        <label for="firstName" class="form-label fw-bold">
                            <i class="fas fa-user"></i>&nbsp;First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstname"
                            value="<?= htmlspecialchars($user['firstname'] ?? '') ?>" required>
                    </div>

                    <!-- Last Name -->
                    <div class="col-md-6 position-relative">
                        <label for="lastName" class="form-label fw-bold">
                            <i class="fas fa-user"></i>&nbsp;Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastname"
                            value="<?= htmlspecialchars($user['lastname'] ?? '') ?>" required>
                    </div>

                    <!-- Contact Number -->
                    <div class="col-md-6 position-relative">
                        <label for="contactNumber" class="form-label fw-bold">
                            <i class="fas fa-phone"></i>&nbsp;Contact Number</label>
                        <input type="tel" class="form-control" id="contactNumber" name="contact_number"
                            value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>" required
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="11">
                    </div>

                    <!-- Email -->
                    <div class="col-md-6 position-relative">
                        <label for="email" class="form-label fw-bold">
                            <i class="fas fa-envelope"></i>&nbsp;Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>

                    <!-- Username -->
                    <div class="col-md-12 position-relative">
                        <label for="userName" class="form-label fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                            <input type="text" class="form-control" id="userName" name="username"
                                value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="col-md-12 position-relative">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;">
                                <i class="fas fa-eye-slash" id="toggleEye"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Submit & Cancel -->
                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" class="btn btn-success">Save Profile</button>
                        <a href="dashboard.php" class="btn btn-danger">Cancel</a>
                    </div>
            </form>
        </div>
    </div>
    <script>
        // Toggle password visibility function
        function togglePassword() {
            var passwordInput = document.getElementById("password");
            var eyeIcon = document.getElementById("toggleEye");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            }
        }
        setTimeout(function() {
            const alertBox = document.getElementById('alertMessage');
            if (alertBox) {
                alertBox.classList.remove('show'); // Bootstrap class to animate fade out
                alertBox.classList.add('fade'); // Add fade effect
                setTimeout(() => alertBox.remove(), 500); // Remove element after fade
            }
        }, 3000); // 5000ms = 5 seconds

        document.getElementById("profilePicture").addEventListener("change", function(event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("profileImage").src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        document.querySelectorAll('input[type="text"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\b\w/g, char => char.toUpperCase());
            });
        });
    </script>