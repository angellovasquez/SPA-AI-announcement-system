<?php
session_start();
include 'database.php';

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Clear old session data when logging in
if (isset($_SESSION['currentUser'])) {
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);  // Regenerate session ID for security
}

$error = ""; // Initialize error message

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "database.php";

    $userName = trim($_POST["userName"]);
    $password = trim($_POST["password"]);

    if (!empty($userName) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE userName = ?");
        $stmt->bind_param("s", $userName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $status = strtolower(trim($row['status']));

            // Check if user is inactive
            if ($status === 'inactive') {
                $error = "Your account is inactive. Please contact admin.";
            }
            // Verify password only if user is active
            elseif ($status === 'active' && password_verify($password, $row['password'])) {
                // Refresh session with full profile data
                $_SESSION['currentUser'] = $row['id'];
                $_SESSION['firstName'] = $row['firstName'];
                $_SESSION['lastName'] = $row['lastName'];
                $_SESSION['role'] = $row['role'];

                // Profile Picture Handling
                $_SESSION['profilePicture'] = !empty($row['profile_picture'])
                    ? 'data:image/jpeg;base64,' . $row['profile_picture']
                    : "uploads/default.jpg";

                $_SESSION['shouldSpeak'] = true;
                // Redirect based on role
                header('Location: dashboard/dashboard.php');
                exit();
            } else {
                $error = "Incorrect username or password!";
            }
        } else {
            $error = "Incorrect username or password!";
        }

        $stmt->close();
    } else {
        $error = "Please fill in both fields!";
    }
}
// Fetch the login page background image from the database

$result = mysqli_query($conn, "SELECT login_bg FROM settings WHERE id = 1");
$settings = mysqli_fetch_assoc($result);
$login_bg = 'uploads/' . ($settings['login_bg'] ?? 'default_login.png'); // Corrected path

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="icon" type="image/png" sizes="712x712" href="images/SPA AI.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('<?php echo $login_bg; ?>');
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
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .form-title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .input-group-text {
            background: #f8f9fa;
            border-right: none;
        }

        .input-group input {
            border-left: none;
        }

        .login-btn {
            width: 100%;
        }

        .register-link {
            font-size: 14px;
            text-decoration: none;
            color: #007bff;
        }

        .register-link:hover {
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
        <form action="" method="post">
            <h2 class="form-title">Sign in</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php
            if (isset($_GET['message'])) {
                echo '<div class="alert alert-success text-center">' . htmlspecialchars($_GET['message']) . '</div>';
            }
            ?>

            <div class="mb-3">
                <label for="userName" class="form-label"><b>Username</b></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                    <input type="text" class="form-control" id="userName" name="userName" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label"><b>Password</b></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;">
                        <i class="fas fa-eye-slash" id="toggleEye"></i>
                    </span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="registration.php" class="register-link">Create an account?</a>
                <a href="dashboard/forgot-password.php" class="register-link">
                    <i class="fas fa-key"></i> Forgot Password?
                </a>
            </div>

            <button type="submit" class="btn btn-success login-btn">Login</button>
        </form>
    </div>

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
        if (window.history.replaceState) {
            window.history.replaceState(null, "", window.location.href);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const registerLink = document.querySelector('.register-link');
            if (registerLink) {
                registerLink.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevents clicking
                    alert('Account registration is disabled at this time.');
                });
                registerLink.style.color = 'black';
                registerLink.style.pointerEvents = 'none';
                registerLink.style.opacity = '0.5';
            }
        });
    </script>

    </script>
</body>

</html>