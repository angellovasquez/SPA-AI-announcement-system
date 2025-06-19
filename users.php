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
    <?php
    include 'database.php';
    $result = mysqli_query($conn, "SELECT user_bg FROM settings WHERE id = 1");
    $settings = mysqli_fetch_assoc($result);
    $user_bg = 'uploads/' . ($settings['user_bg'] ?? 'default_user.png'); // Corrected path
    ?>
    <style>
        body {
            background-image: url('<?php echo $user_bg; ?>');
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

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            require_once "database.php"; // Ensure correct path to database connection file

            // Retrieve and sanitize user inputs
            $firstName = mysqli_real_escape_string($conn, $_POST["firstName"]);
            $lastName = mysqli_real_escape_string($conn, $_POST["lastName"]);
            $contactNumber = mysqli_real_escape_string($conn, $_POST["contactNumber"]);
            $email = mysqli_real_escape_string($conn, $_POST["email"]);
            $year = mysqli_real_escape_string($conn, $_POST["year"]);
            $course = mysqli_real_escape_string($conn, $_POST["course"]);
            $role = mysqli_real_escape_string($conn, $_POST["role"]);

            $errors = [];

            // Validate inputs
            if (empty($firstName) || empty($lastName) || empty($contactNumber) || empty($email) || empty($role)) {
                $errors[] = "All fields are required.";
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format.";
            }
            if (strlen($contactNumber) != 11) {
                $errors[] = "Contact number must be exactly 11 characters long.";
            }
            if (!in_array($year, ["First year", "Second year", "Third year", "Fourth year", "Teacher"])) {
                $errors[] = "Invalid year selected.";
            }
            if (!in_array($course, ["BSCS", "BSIT", "BSBA", "BS-ENTREP", "BS-AIS", "BSOA", "BTVTEd", "Teacher"])) {
                $errors[] = "Invalid course selected.";
            }
            if (!in_array($role, ["Teacher", "Student"])) {
                $errors[] = "Invalid role selected.";
            }

            // Check if email already exists
            $stmt = $conn->prepare("SELECT * FROM members WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors[] = "Email already exists.";
            }

            // Check if contact number already exists
            $stmt = $conn->prepare("SELECT * FROM members WHERE contact_number = ?");
            $stmt->bind_param("s", $contactNumber);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors[] = "Contact number already exists.";
            }

            // Display errors if any
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo "<div class='alert alert-danger'>$error</div>";
                }
            } else {
                // Hash the password and save to database
                $fullName = $firstName . " " . $lastName;

                $stmt = $conn->prepare("INSERT INTO members (firstName, lastName, contact_number, email, role, year, course) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss", $firstName, $lastName, $contactNumber, $email, $role, $year, $course);


                if ($stmt->execute()) {
                    echo "<div class='alert alert-success text-center'>Hi Users, you are now registered!<br>Please wait for further announcements by the Admin.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Something went wrong. Please try again.</div>";
                }
            }
        }
        ob_end_flush(); // End output buffering
        ?>

        <h2 class="form-title">Sign up</h2>
        <form action="" method="post">
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
                        <input type="text" class="form-control" id="contactNumber" name="contactNumber" required
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="11">
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
            <div class="row g-3 mb-3">
                <div class="col-md-6 input-icon">
                    <label for="year" class="form-label">Year:</label>
                    <select class="form-select" name="year" id="year" required>
                        <option value="" selected disabled>Select Your Year</option>
                        <option value="First year">First year</option>
                        <option value="Second year">Second year</option>
                        <option value="Third year">Third year</option>
                        <option value="Fourth year">Fourth year</option>
                        <option value="Teacher">Teacher </option>
                    </select>
                </div>
                <div class="col-md-6 input-icon">
                    <label for="course" class="form-label">Course:</label>
                    <select class="form-select" name="course" id="course" required>
                        <option value="" selected disabled>Select Your Course</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSBA">BSBA</option>
                        <option value="BS-ENTREP">BS-ENTREP</option>
                        <option value="BS-AIS">BS-AIS</option>
                        <option value="BSOA">BSOA</option>
                        <option value="BTVTEd">BTVTEd</option>
                        <option value="Teacher">Teacher</option>

                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" name="role" id="role" required>
                    <option value="" selected disabled>Select Role</option>
                    <option value="Teacher">Teacher</option>
                    <option value="Student">Student</option>
                </select>
            </div>
            <a href="index.php" class="register-link">
                <i class="fas fa-arrow-left me-1"></i> Go Back
            </a>
            <br>
            <br>
            <button type="submit" class="btn btn-success register-btn">Register</button>
        </form>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("year").addEventListener("change", function() {
            const yearValue = this.value;
            const courseSelect = document.getElementById("course");
            const roleSelect = document.getElementById("role");

            if (yearValue === "Teacher") {
                courseSelect.value = "Teacher";
                roleSelect.value = "Teacher";
            } else {
                roleSelect.value = "Student";
                if (courseSelect.value === "Teacher") {
                    courseSelect.value = ""; // reset course if previously selected "Teacher"
                }
            }
        });
    </script>
</body>

</html>