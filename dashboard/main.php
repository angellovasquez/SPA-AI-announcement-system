<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['currentUser'])) {
    header("Location: ../login.php?error=Unauthorized+Access");
    exit();
}

require_once "../database.php";

// Check if user data is missing; if so, refresh data from database
if (!isset($_SESSION['firstName']) || !isset($_SESSION['lastName'])) {
    $userID = $_SESSION['currentUser'] ?? null;

    if ($userID) {
        $stmt = $conn->prepare("SELECT firstName, lastName, profile_picture FROM users WHERE id = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $_SESSION['firstName'] = htmlspecialchars($row['firstName']);
            $_SESSION['lastName'] = htmlspecialchars($row['lastName']);

            $_SESSION['profilePicture'] = !empty($row['profile_picture'])
                ? 'data:image/jpeg;base64,' . $row['profile_picture']
                : "../uploads/default.jpg";
        }
    }
}

// Display data for UI
$firstName = $_SESSION['firstName'] ?? "Admin";
$lastName = $_SESSION['lastName'] ?? "User";
$profilePicture = $_SESSION['profilePicture'] ?? "../uploads/default.jpg";
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" sizes="712x712" href="../images/SPA AI.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link rel="stylesheet" href="main.css" />
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<style>
    body {
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
    }

    /* Fade-in effect */
    .fade-in {
        opacity: 0;
        transform: translateY(10px);
        animation: fadeInAnimation 0.5s ease-in-out forwards;
    }

    @keyframes fadeInAnimation {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .list-group-item.active {
        background-image: linear-gradient(to right,
                #a8f0cb, #baf3d7, #c2f5de, #cbf7e4, #d4f8ea, #ddfaef);
        color: #1b4332 !important;
        /* Darker green for better contrast */
        font-weight: bold;
        /* Makes text stand out */
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        /* Soft shadow for better readability */
        transition: all 0.3s ease-in-out;
        /* Smooth hover effect */
        border-radius: 8px;
        /* Slight rounded edges */
        box-shadow: 2px 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Add hover effect for better interaction */
    .list-group-item.active:hover {
        background-image: linear-gradient(to right,
                #94ebbf, #a8f0cb, #baf3d7, #c2f5de, #cbf7e4);
        border-color: #21867a !important;
        /* Slightly darker border on hover */
        transform: scale(1.03);
        /* Slight zoom effect on hover */
        box-shadow: 4px 6px 12px rgba(0, 0, 0, 0.3);
    }



    /* Modal Custom Styles */
    #manualModal .modal-content {
        background: linear-gradient(to bottom, #ece9e6, #ffffff);
        /* Gradient background */
        border: 2px solid #2a9d8f;
        /* Border color */
        border-radius: 10px;
        /* Slightly rounded corners */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        /* Subtle shadow for depth */
    }

    #manualModal .modal-header {
        background: linear-gradient(to bottom, #ece9e6, #ffffff);
        /* Teal header background */
        color: black;
        border: 2px solid #2a9d8f;
        /* White border below the header */
    }

    #manualModal .modal-footer {
        border-top: 2px solid #2a9d8f;
        background: linear-gradient(to bottom, #ece9e6, #ffffff);
        /* Border on top of footer */
    }

    #manualModal .modal-body {
        padding: 20px;
        /* Adequate padding for content */
    }

    #manualModalLabel {
        font-weight: bold;
        /* Bold title */
    }

    /* Adjustments for modal buttons */
    #manualModal .btn-danger {
        background-color: #e76f51;
        /* Soft Coral */
        border: none;
        color: white;
    }

    #manualModal .btn-danger:hover {
        background-color: #d65c3b;
        /* Darker Coral on hover */
    }

    /* Styling for icons */
    #manualModal i.fas {
        margin-right: 8px;
        /* Spacing for icons */
    }


    /* No Internet Overlay */
    #no-internet-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        z-index: 10000;
    }

    .offline-container {
        text-align: center;
    }

    .offline-container i {
        font-size: 50px;
        color: red;
    }

    #loader-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    }

    /* Loader Animation */
    .loader {
        border: 6px solid #f3f3f3;
        border-top: 6px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }

    .text-wave {
        display: inline-flex;
        /* Align letters in a row */
        gap: 2px;
        /* Small spacing between letters for better readability */
    }

    .logo-animate,
    .text-wave span {
        display: inline-block;
        animation: synchronizedWave 2.5s infinite ease-in-out;
    }

    /* Keyframes for synchronized wave */
    @keyframes synchronizedWave {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }

        /* Wave peak */
    }

    /* Add delay to text for smooth staggered wave */
    .text-wave span {
        animation-delay: calc(0.15s * var(--i));
    }

    /* Individual delays for each letter */
    .text-wave span:nth-child(1) {
        --i: 1;
    }

    .text-wave span:nth-child(2) {
        --i: 2;
    }

    .text-wave span:nth-child(3) {
        --i: 3;
    }

    .text-wave span:nth-child(4) {
        --i: 4;
    }

    .text-wave span:nth-child(5) {
        --i: 5;
    }

    .text-wave span:nth-child(6) {
        --i: 6;
    }

    .dropdown-menu .dropdown-item {
        background: linear-gradient(to bottom, #ece9e6, #ffffff);
        transition: transform 0.2s ease, background-color 0.3s ease;
        /* smooth transition */
    }

    /* Custom hover effect with zoom */
    .dropdown-menu .dropdown-item:hover {
        background-image: linear-gradient(to right,
                #a8f0cb, #baf3d7, #c2f5de, #cbf7e4, #d4f8ea, #ddfaef);
        color: black;
        transform: scale(1.05);
    }
</style>

<body>
    <div id="no-internet-overlay">
        <div class="offline-container">
            <i class="fas fa-exclamation-triangle"></i>
            <h2>No Internet Connection</h2>
            <p>Please check your network and try again.</p>
        </div>
    </div>
    <div id="loader-overlay">
        <div class="loader"></div>
    </div>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-white" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 primary-text fs-4 fw-bold text-uppercase border-bottom">
                <a href="dashboard.php" class="d-block text-decoration-none" style="color: inherit;">
                    <img src="images/SPA AI.png" alt="Logo" class="logo-animate" style="height: 50px; width: auto;"><span class="text-wave">&nbsp;&nbsp;
                        <span>S</span>
                        <span>P</span>
                        <span>A</span>
                        <span>.</span>
                        <span>A</span>
                        <span>I</span>
                    </span>
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="dashboard.php" class="list-group-item list-group-item-action bg-transparent second-text fw-bold" data-title="Dashboard">
                    <i class="fas fa-tachometer-alt me-2"></i>&nbsp;Dashboard
                </a>
                <a href="announcement.php" class="list-group-item list-group-item-action bg-transparent second-text fw-bold" data-title="Announcement">
                    <i class="fas fa-bullhorn me-2"></i>&nbsp;Announcement
                </a>
                <a href="draft.php" class="list-group-item list-group-item-action bg-transparent second-text fw-bold" data-title="Draft">
                    <i class="fas fa-file-alt me-2"></i>&nbsp;Draft
                </a>
                <a href="manage_users.php" class="list-group-item list-group-item-action bg-transparent second-text fw-bold" data-title="User Account">
                    <i class="fas fa-users me-2"></i>&nbsp;Manage User
                </a>
            </div>
            <div class="profile-section dropdown">
                <a href="#" class="nav-link dropdown-toggle second-text fw-bold"
                    id="profileDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $profilePicture; ?>"
                        alt="Profile Picture"
                        class="rounded-circle me-2"
                        style="width: 40px; height: 40px; object-fit: cover;">
                    <?php echo ucwords(strtolower($firstName . ' ' . $lastName)); ?>
                </a>

                <ul id="admin" class="dropdown-menu" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="profile.php" style="color: black;"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="settings.php" style="color: black;"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><a class="dropdown-item" href="#" style="color: black;" data-bs-toggle="modal" data-bs-target="#manualModal"><i class="fas fa-book me-2"></i>Manual</a></li>
                    <li><a class="dropdown-item" href="logout.php" style="color: red;"><i class="fas fa-power-off me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>

        <div class="modal fade" id="manualModal" tabindex="-1" aria-labelledby="manualModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title w-100" id="manualModalLabel">
                            <i class="fas fa-book me-2"></i> SPA AI - Admin User Manual
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4>Introduction</h4>
                        <p>Welcome to the <strong>Smart Personal Assistant (SPA AI)</strong> system. This manual provides step-by-step instructions for admins to effectively manage the system.</p>

                        <h5>📌 Getting Started</h5>
                        <ul>
                            <li>Log in using your <strong>Username</strong> and <strong>Password</strong> to access the admin panel.</li>
                            <li>If you encounter login issues, contact the system administrator for support.</li>
                            <li>Explore the dashboard displaying key metrics such as:
                                <ul>
                                    <li>Posted Announcements</li>
                                    <li>Active Audience</li>
                                    <li>Drafted Announcements</li>
                                    <li>Total Users</li>
                                </ul>
                            </li>
                            <li>Use the sidebar to navigate between features.</li>
                        </ul>

                        <h5>📢 Creating Announcements</h5>
                        <ul>
                            <li>Click the <strong>"Announcements"</strong> tab in the sidebar.</li>
                            <li>Fill in the required fields:
                                <ul>
                                    <li><strong>Title</strong></li>
                                    <li><strong>Description</strong> (details about the event or update)</li>
                                </ul>
                            </li>
                            <li>Click <strong>"Generate"</strong> to allow SPA AI to assist in generating professional content.</li>
                            <li>Select the <strong>Target Audience</strong> dynamically (e.g., students, faculty, or specific groups).</li>
                            <li>Click <strong>"Post"</strong> to share the announcement within the system.</li>
                            <li>Enable options like <strong>"Send via SMS"</strong> or <strong>"Send via Email"</strong> for wider reach.</li>
                            <li><strong>Tips for Writing SMS and Email Prompts:</strong>
                                <ul>
                                    <li><strong>📱 SMS Prompt Guidelines:</strong>
                                        <ul>
                                            <li>Keep the message concise — SMS is limited to <strong>160 characters</strong>.</li>
                                            <li>Ensure your AI prompt stays within <strong>100 tokens</strong> for optimal sms length.</li>
                                            <li>Avoid unnecessary punctuation and long words.</li>
                                            <li><em>Example:</em><br>
                                                <code>"Create a 160-character announcement for 4th year BSCS and BSIT students regarding their final defense scheduled on April 21–26, 2025."</code>
                                            </li>
                                        </ul>
                                    </li>
                                    <li><strong>📧 Email Prompt Guidelines:</strong>
                                        <ul>
                                            <li>Email messages allow more content, but keep them readable and informative.</li>
                                            <li>Ensure your AI prompt stays within <strong>150 tokens</strong> for optimal email length.</li>
                                            <li><em>Example:</em><br>
                                                <code>
                                                    "Create a announcement for 4th year BSCS and BSIT Students to thier final defense on April 21-26, 2025"
                                                </code>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>


                        <h5>📊 Tracking Engagement</h5>
                        <ul>
                            <li>Go to the <strong>Dashboard</strong> page.</li>
                            <li>View insights such as:
                                <ul>
                                    <li>SMS and Email Engagement</li>
                                    <li>Number of posted announcements</li>
                                </ul>
                            </li>
                        </ul>

                        <h5>👤 Managing User Accounts</h5>
                        <ul>
                            <li>Click the <strong>"User Accounts"</strong> tab in the sidebar.</li>
                            <li>View the table of all registered users from the database.</li>
                            <li>Edit or delete user accounts as required.</li>
                            <li>Click <strong>"Actions"</strong> to access additional options:
                                <ul>
                                    <li><strong>Download Template</strong> – Get the Excel format required for importing users.</li>
                                    <li><strong>Add Admin</strong> – Register a new administrator account.</li>
                                    <li><strong>Import Excel Data</strong> – Upload bulk student data using an Excel file.</li>
                                    <li><strong>Export Student Data</strong> – Save current user data to an Excel file.</li>
                                    <li><strong>Backup Database</strong> – Secure a full backup of the user database.</li>
                                </ul>
                            </li>
                            <li>Manage user interaction status:
                                <ul>
                                    <li>Set users as <strong>Active</strong> or <strong>Inactive</strong> to control system access.</li>
                                    <li>Inactive users will not receive announcements or notifications.</li>
                                </ul>
                            </li>
                        </ul>


                        <h5>⚙️ Profile & System Customization</h5>
                        <ul>
                            <li>Admins can update their profile details, including <strong>name</strong> and <strong>email</strong>.</li>
                            <li>Customize the system appearance by selecting background templates for:
                                <ul>
                                    <li><strong>Home Page</strong></li>
                                    <li><strong>Login Page</strong></li>
                                    <li><strong>User Dashboard</strong></li>
                                </ul>
                            </li>
                            <li>Admins can also <strong>update social media links</strong> displayed on the home page, allowing users to connect with official system pages.</li>
                        </ul>


                        <h5>✅ Best Practices</h5>
                        <ul>
                            <li>Regularly generate and update announcements using the integrated AI to ensure timely and relevant communication.</li>
                            <li>Optimize SMS announcements by enabling AI-powered auto-summarization to fit the 160-character limit without losing key information.</li>
                            <li>Maintain accurate and up-to-date user account records to ensure effective role-based access and personalized interaction.</li>
                            <li>Periodically review and deactivate inactive users to enhance system performance and safeguard data security.</li>
                            <li>Utilize built-in tools like Excel import/export and database backup to support data integrity and continuity.</li>
                        </ul>
                        <h5 class="text-center">🎖️ System Developers</h5>
                        <br>
                        <div class="row text-center">
                            <div class="col-6 col-md-4 col-lg-2 mb-3">
                                <img src="images/angelo.jpg" alt="Angelo Vasquez" class="rounded-circle img-fluid" width="80">
                                <p><strong>Angello Vasquez</strong></p>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 mb-3">
                                <img src="images/randy.jpg" alt="Randy Vicencio" class="rounded-circle img-fluid" width="80">
                                <p><strong>Randy Vicencio</strong></p>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 mb-3">
                                <img src="images/karl.jpg" alt="Karl Regalado" class="rounded-circle img-fluid" width="80">
                                <p><strong>Karl Regalado</strong></p>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 mb-3">
                                <img src="images/eugene.jpg" alt="Eugene Dela Cruz" class="rounded-circle img-fluid" width="80">
                                <p><strong>Eugene Dela Cruz</strong></p>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 mb-3">
                                <img src="images/james.jpg" alt="James Ibañez" class="rounded-circle img-fluid" width="80">
                                <p><strong>James Ibañez</strong></p>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2 mb-3">
                                <img src="images/jerome.jpg" alt="Jerome Ong" class="rounded-circle img-fluid" width="80">
                                <p><strong>Jerome Ong</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>



        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Toggle sidebar
            document.addEventListener("DOMContentLoaded", function() {
                const wrapper = document.getElementById("wrapper");
                const toggleButton = document.getElementById("menu-toggle");

                if (toggleButton) {
                    toggleButton.addEventListener("click", function(e) {
                        e.preventDefault();
                        wrapper.classList.toggle("toggled");
                    });
                }
            });
            document.addEventListener("DOMContentLoaded", function() {
                const loaderOverlay = document.getElementById("loader-overlay");
                const noInternetOverlay = document.getElementById("no-internet-overlay");
                const menuLinks = document.querySelectorAll(".list-group-item");

                // Function to check internet connection and speed
                function checkInternetSpeed(callback) {
                    const startTime = new Date().getTime();
                    const img = new Image();
                    img.src = "https://www.google.com/images/phd/px.gif?t=" + startTime;
                    img.onload = function() {
                        const endTime = new Date().getTime();
                        const duration = endTime - startTime;
                        const speed = (100 / duration) * 1000; // Estimated speed in KB/s
                        callback(speed);
                    };
                    img.onerror = function() {
                        callback(0); // No internet or slow network
                    };
                }

                function checkInternetConnection() {
                    if (!navigator.onLine) {
                        noInternetOverlay.style.display = "flex"; // Show no-internet overlay
                    } else {
                        noInternetOverlay.style.display = "none"; // Hide no-internet overlay
                    }
                }

                // Hide overlays initially
                noInternetOverlay.style.display = "none";
                loaderOverlay.style.display = "none";

                // Check internet status when page loads
                checkInternetConnection();

                // Listen for online/offline changes
                window.addEventListener("online", checkInternetConnection);
                window.addEventListener("offline", checkInternetConnection);

                // Handle menu navigation with loader effect
                menuLinks.forEach(link => {
                    link.addEventListener("click", function(event) {
                        event.preventDefault(); // Prevents immediate navigation

                        checkInternetSpeed(function(speed) {
                            if (speed < 50 || !navigator.onLine) { // If slow or no internet
                                loaderOverlay.style.display = "flex"; // Show loader
                            } else {
                                loaderOverlay.style.display = "none"; // Hide loader
                            }

                            setTimeout(() => {
                                window.location.href = link.href; // Redirect after short delay
                            }, 300); // Shorter delay (800ms) for smoother experience
                        });
                    });
                });
            });
            document.addEventListener("DOMContentLoaded", function() {
                const currentUrl = window.location.href;
                const menuLinks = document.querySelectorAll(".list-group-item");

                menuLinks.forEach(link => {
                    if (link.href === currentUrl) {
                        link.classList.add("active");
                    }
                });
            });
            document.addEventListener("DOMContentLoaded", function() {
                // Fade in the page when loaded
                document.body.style.opacity = "1";

                // Add click event to all menu links for smooth navigation
                document.querySelectorAll(".list-group-item").forEach(link => {
                    link.addEventListener("click", function(e) {
                        e.preventDefault();
                        let targetUrl = this.getAttribute("href");

                        // Fade out effect
                        document.body.style.opacity = "0";
                        setTimeout(() => {
                            window.location.href = targetUrl;
                        }, 10); // Delay before redirecting
                    });
                });
            });
        </script>
</body>

</html>