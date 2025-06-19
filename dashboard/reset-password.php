<?php
session_start();
include '../database.php'; // Database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if token and email are in the URL
if (!isset($_GET['token']) || !isset($_GET['email'])) {
    $_SESSION['error'] = "❌ Invalid password reset request.";
    header("Location: forgot-password.php");
    exit();
}

$token = $_GET['token'];
$email = $_GET['email'];

// ✅ Verify the reset token
$query = "SELECT id, reset_expires FROM users WHERE email = ? AND reset_token = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $expires = strtotime($row['reset_expires']);

    // ✅ Check if the token is expired
    if ($expires < time()) {
        $_SESSION['error'] = "❌ This reset link has expired. Please request a new one.";
        header("Location: forgot-password.php");
        exit();
    }
} else {
    $_SESSION['error'] = "❌ Invalid or expired reset link.";
    header("Location: forgot-password.php");
    exit();
}

// ✅ Handle password reset form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // ✅ Validate password match
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "❌ Passwords do not match.";
        header("Location: reset-password.php?token=$token&email=$email");
        exit();
    }

    // ✅ Hash new password securely
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // ✅ Update password in database
    $updateQuery = "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ss", $hashed_password, $email);

    if ($updateStmt->execute()) {
        header("Location: ../login.php?message=Password+reset+successful!");
        exit();
    } else {
        header("Location: ../login.php?message=Something+went+wrong.+Please+try+again.");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Reset Password</title>
    <link rel="icon" type="image/png" sizes="712x712" href="../images/SPA AI.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 95vh;
            margin: 0;
            background: linear-gradient(to bottom, #ece9e6, #ffffff);
            padding: 20px;
        }

        /* Container Box */
        .container {
            max-width: 400px;
            width: 100%;
            padding: 25px;
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid #2a9d8f;
            border-radius: 12px;
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        /* Title */
        .container h2 {
            color: black;
            font-size: 22px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        /* Input Fields */
        .input-container {
            position: relative;
            width: 100%;
            margin-bottom: 15px;
        }

        input {
            width: 90%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: #2a9d8f;
            box-shadow: 0px 0px 5px rgba(42, 157, 143, 0.5);
        }

        /* Eye Icon */
        .toggle-password {
            position: absolute;
            right: 20px;
            top: 67%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            transition: 0.3s;
        }

        .toggle-password:hover {
            color: #2a9d8f;
        }

        /* Submit Button */
        button {
            width: 90%;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            background: #2a9d8f;
            color: black;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #21867b;
            transform: scale(1.05);
        }

        /* Messages */
        .success,
        .error {
            font-weight: bold;
            padding: 8px;
            border-radius: 6px;
            display: inline-block;
            width: 100%;
        }

        .success {
            color: green;
            background: rgba(0, 255, 0, 0.1);
        }

        .error {
            color: red;
            background: rgba(255, 0, 0, 0.1);
        }

        /* Back to Login Link */
        .back-link {
            display: block;
            margin-top: 10px;
            color: black;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Mobile Optimization */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .container {
                max-width: 90%;
                padding: 15px;
            }

            .container h2 {
                font-size: 20px;
            }

            input,
            button {
                font-size: 14px;
                padding: 10px;
            }

            .toggle-password {
                right: 10px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h2><i class="fas fa-sync-alt"></i>&nbsp;Reset Password</h2>
        <?php if (isset($_SESSION['message'])) {
            echo "<p class='success'>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']);
        } ?>
        <?php if (isset($_SESSION['error'])) {
            echo "<p class='error'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']);
        } ?>

        <form method="POST" action="">
            <div class="input-container">
                <h3 style="text-align: left;  margin-left: 12px; margin-bottom: 5px;">New Password:</h3>
                <input type="password" id="password" name="password" placeholder="Enter new password" required>
                <i class="fas fa-eye-slash toggle-password" onclick="togglePassword('password')"></i>
            </div>

            <div class="input-container">
                <h3 style="text-align: left;  margin-left: 12px; margin-bottom: 5px;">Confirm Password:</h3>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                <i class="fas fa-eye-slash toggle-password" onclick="togglePassword('confirm_password')"></i>
            </div>

            <button type="submit">Reset Password</button>
        </form>

        <a href="../login.php" class="back-link">← Back to Login</a>
    </div>

    <script>
        function togglePassword(fieldId) {
            let field = document.getElementById(fieldId);
            let icon = field.nextElementSibling;
            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                field.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }

        function checkPasswordStrength() {
            let password = document.getElementById("password").value;
            let validationMessage = document.getElementById("passwordValidation");

            if (password.length < 8) {
                validationMessage.style.display = "block";
            } else {
                validationMessage.style.display = "none";
            }
            validateForm();
        }

        function checkPasswordMatch() {
            let password = document.getElementById("password").value;
            let confirmPassword = document.getElementById("confirm_password").value;
            let matchMessage = document.getElementById("matchValidation");

            if (password !== confirmPassword) {
                matchMessage.style.display = "block";
            } else {
                matchMessage.style.display = "none";
            }
            validateForm();
        }

        function validateForm() {
            let password = document.getElementById("password").value;
            let confirmPassword = document.getElementById("confirm_password").value;
            let submitBtn = document.getElementById("submitBtn");

            if (password.length >= 8 && password === confirmPassword) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }
    </script>

</body>

</html>