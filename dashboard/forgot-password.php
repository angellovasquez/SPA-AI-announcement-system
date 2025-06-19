<?php
session_start();
include '../database.php'; // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower(trim($_POST["email"])); // Convert email to lowercase

    // ✅ Check if email exists
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ✅ Email found, generate a reset token
        $token = bin2hex(random_bytes(50)); // Generate a secure token
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Expires in 1 hour

        // ✅ Store token in database
        $updateQuery = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sss", $token, $expiry, $email);
        $updateStmt->execute();

        // ✅ Prepare reset link
        $resetLink = "http://localhost/thesis%20system/dashboard/reset-password.php?token=" . urlencode($token) . "&email=" . urlencode($email);

        // ✅ Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'spaisystem69@gmail.com'; // ⚠ Move this to a config file for security
            $mail->Password = 'fvxd kegs fxeu rzzx'; // Use App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('your-email@gmail.com', 'SPA-AI');
            $mail->addAddress($email);
            $mail->Subject = "Password Reset Request";
            $mail->isHTML(true);
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; text-align: center;'>
                    <h2 style='color: #333;'>Password Reset Request</h2>
                    <p>Click the link below to reset your password. This link will expire in 1 hour.</p>
                    <a href='$resetLink' style='display: inline-block; padding: 10px 20px; color: #fff; background: #28a745; text-decoration: none; border-radius: 5px;'>Reset Password</a>
                    <p style='color: red;'>If you did not request this, please ignore this email.</p>
                </div>
            ";

            if ($mail->send()) {
                $_SESSION['message'] = "✅ A password reset link has been sent to your email.";
            } else {
                $_SESSION['error'] = "❌ Email sending failed: " . $mail->ErrorInfo;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "❌ Email error: " . $mail->ErrorInfo;
        }
    } else {
        $_SESSION['error'] = "❌ Email not found.";
    }

    header("Location: forgot-password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Forgot Password</title>
    <link rel="icon" type="image/png" sizes="712x712" href="../images/SPA AI.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* General Body Styling */
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 94vh;
            margin: 0;
            background: linear-gradient(to bottom, #ece9e6, #ffffff);
            padding: 20px;
        }

        /* Container Styling */
        .container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid #2a9d8f;
            border-radius: 12px;
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        /* Header */
        .container h2 {
            font-size: 22px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        /* Input Field */
        input {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
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

        /* Button */
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

        /* Success & Error Messages */
        .success,
        .error {
            margin: 10px 0;
            font-weight: bold;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
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
                padding: 25px;
            }

            .container h2 {
                font-size: 20px;
            }

            input,
            button {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2><i class="fas fa-key"></i> Forgot Password</h2>

        <?php if (isset($_SESSION['message'])) { ?>
            <p class="success"><?= $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php } ?>

        <?php if (isset($_SESSION['error'])) { ?>
            <p class="error"><?= $_SESSION['error']; ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php } ?>

        <form method="POST" action="">
            <h3 style="text-align: left;  margin-left: 12px; margin-bottom: 5px;">Email:</h3>
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
        </form>

        <a href="../login.php" class="back-link">← Back to Login</a>
    </div>
</body>

</html>