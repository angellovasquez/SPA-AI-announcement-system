<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="icon" type="image/png" sizes="712x712" href="images/SPA AI.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('images/acts.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.9);
            /* Slight transparency */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }

        .form-title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 13px;
            top: 75%;
            transform: translateY(-50%);
            color: #888;
        }

        .input-icon input {
            padding-left: 30px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .register-btn {
            width: 100%;
        }

        .back-link {
            font-size: 14px;
            text-decoration: none;
            color: #007bff;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <?php
        ob_start(); // Start output buffering
        include 'config.php';

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            require_once "database.php"; // Database connection

            // Retrieve and sanitize user inputs
            $firstName = mysqli_real_escape_string($conn, $_POST["firstName"]);
            $lastName = mysqli_real_escape_string($conn, $_POST["lastName"]);
            $contactNumber = mysqli_real_escape_string($conn, $_POST["contactNumber"]);
            $email = mysqli_real_escape_string($conn, $_POST["email"]);
            $role = mysqli_real_escape_string($conn, $_POST["role"]);
            $userName = mysqli_real_escape_string($conn, $_POST["userName"]);
            $password = mysqli_real_escape_string($conn, $_POST["password"]);

            $profilePicture = null;  // Ensure it starts as null
            if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === 0) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxFileSize = 2 * 1024 * 1024; // 2MB limit

                if (in_array($_FILES['profilePicture']['type'], $allowedTypes) && $_FILES['profilePicture']['size'] <= $maxFileSize) {
                    $imageData = file_get_contents($_FILES['profilePicture']['tmp_name']);
                    $profilePicture = base64_encode($imageData); // Encode as Base64
                } else {
                    echo "<div class='alert alert-danger'>Invalid profile picture. Only JPG, PNG, or GIF files under 2MB are allowed.</div>";
                    exit();
                }
            } else {
                echo "<div class='alert alert-warning'>No profile picture uploaded or error during upload.</div>";
                exit();
            }

            // Debugging Output
            if ($profilePicture === null) {
                echo "<div class='alert alert-warning'>Profile picture conversion failed.</div>";
            }

            // Validation
            $errors = [];
            if (empty($firstName) || empty($lastName) || empty($contactNumber) || empty($email) || empty($role) || empty($userName) || empty($password)) {
                $errors[] = "All fields are required.";
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format.";
            }
            if (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters long.";
            }
            if (strlen($contactNumber) != 11) {
                $errors[] = "Contact number must be exactly 11 characters long.";
            }

            // Check if email already exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors[] = "Email already exists.";
            }

            // Display errors if any
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo "<div class='alert alert-danger'>$error</div>";
                }
            } else {
                // Hash the password and save to database
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("
            INSERT INTO users (firstName, lastName, contact_number, email, role, userName, password, profile_picture) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
                $stmt->bind_param(
                    "ssssssss",
                    $firstName,
                    $lastName,
                    $contactNumber,
                    $email,
                    $role,
                    $userName,
                    $passwordHash,
                    $profilePicture
                );

                if ($stmt->execute()) {
                    header("Location:login.php?message=Registered+Successfully.");
                    exit();
                } else {
                    echo "<div class='alert alert-danger'>Something went wrong. Please try again.</div>";
                }
            }
        }

        ob_end_flush(); // End output buffering
        ?>


        <h2 class="form-title">Sign up</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="row g-3 mb-3">
                <div class="col-md-6 position-relative">
                    <label for="firstName" class="form-label">First Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="firstName" name="firstName" required>
                    </div>
                </div>
                <div class="col-md-6 position-relative">
                    <label for="lastName" class="form-label">Last Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="lastName" name="lastName" required>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6 position-relative">
                    <label for="contactNumber" class="form-label">Contact Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" class="form-control" id="contactNumber" name="contactNumber" required>
                    </div>
                </div>
                <div class="col-md-6 position-relative">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" name="role" id="role" required>
                    <option value="" selected disabled>Select Role</option>
                    <option value="Admin">Admin</option>
                    <option value="Teacher">Teacher</option>
                </select>
            </div>
            <div class="col-mb-3 position-relative">
                <label for="profilePicture" class="form-label">Profile Picture</label>
                <input type="file" class="form-control" id="profilePicture" name="profilePicture" accept="image/*" required>
            </div>


            <div class="col-mb-3 position-relative">
                <label for="userName" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                    <input type="text" class="form-control" id="userName" name="userName" required>
                </div>
            </div>
            <div class="col-mb-3 position-relative">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;">
                        <i class="fas fa-eye-slash" id="toggleEye"></i>
                    </span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <p>Already have an account? <a href="login.php">Login here</a>.</p>
            </div>
            <button type="submit" class="btn btn-success register-btn">Register</button>
        </form>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>
</body>

</html>