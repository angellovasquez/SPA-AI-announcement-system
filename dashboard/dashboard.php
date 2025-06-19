<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$shouldSpeak = false;
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : "User";

if (isset($_SESSION['shouldSpeak']) && $_SESSION['shouldSpeak'] === true) {
    $shouldSpeak = true;
    unset($_SESSION['shouldSpeak']); // Reset session variable
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="css/fontawesome.min.css">
    <?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // If the user is not logged in, redirect to login page


    // Prevent browser from caching the dashboard
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");


    include_once "main.php";
    ?>
    <style>
        /* Apply gradient background only to cards */
        .stat-card {
            position: relative;
            background-image: linear-gradient(-225deg, #FFFEFF 0%, #D7FFFE 100%);
            border-radius: 10px;
            /* Rounded corners */
            padding: 20px;
            border: 2px solid #2a9d8f;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* Hover effect */
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }


        .badge {
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }

        .bg-success {
            background-color: #28a745 !important;
            /* Green */
        }

        .bg-danger {
            background-color: #dc3545 !important;
            /* Red */
        }

        /* Floating Toast Styling */
        .floating-toast {
            position: fixed;
            top: 88%;
            /* Vertically center the toast */
            left: 68%;
            /* Horizontally center the toast */
            transform: translate(-50%, -50%);
            /* Adjust position to be exactly at the center */
            z-index: 1050;
            /* Ensure the toast is on top */
            max-width: 520px;
            width: 100%;

            background: rgba(0, 128, 0, 0.9);
            /* Green with transparency */
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        /* Show Toast */
        .floating-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .table-container {
            max-width: 97%;
            width: 100%;
            margin: auto;
        }

        .table-responsive {
            overflow-x: auto;
            background: linear-gradient(to bottom, #ece9e6, #ffffff);
            /* Light gray background */
            border: 2px solid #2a9d8f;
            /* Teal border */
            border-radius: 10px;
            padding: 15px;
        }

        .pagination .page-item.active .page-link {
            background-color: #f4a261;
            /* Muted Orange */
            border-color: #f4a261;
        }

        .pagination .page-item .page-link {
            color: #264653;
            /* Deep Navy */
        }

        .pagination .page-item .page-link:hover {
            background-color: #e76f51;
            /* Soft Coral */
            color: white;
        }

        canvas {
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        #engagementChart {
            padding: 20px;
            background: linear-gradient(to bottom, #ece9e6, #ffffff);
            /* Light gray background */
            border: 2px solid #2a9d8f;
            max-width: 97%;
            width: 100%;
            margin: auto;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004080;
        }

        canvas {
            max-width: 100%;
            height: auto !important;
            /* Makes sure it resizes properly */
            min-height: 300px;
            /* Prevents graph from shrinking too much */
        }
    </style>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0" id="header-title">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </h2>

            </div>
        </nav>

        <?php
        // Include database connection
        include '../database.php';

        // Fetch published announcement count
        $announcementQuery = "SELECT COUNT(*) AS count FROM announcements WHERE status = 'published'";
        $announcementResult = mysqli_query($conn, $announcementQuery);
        $announcementCount = mysqli_fetch_assoc($announcementResult)['count'];

        // Fetch draft announcement count
        $draftQuery = "SELECT COUNT(*) AS count FROM announcements WHERE status = 'draft'";
        $draftResult = mysqli_query($conn, $draftQuery);
        $draftCount = mysqli_fetch_assoc($draftResult)['count'];

        // Fetch active audience count
        $activeAudienceQuery = "SELECT COUNT(*) AS count FROM members WHERE status = 'active'";
        $activeAudienceResult = mysqli_query($conn, $activeAudienceQuery);
        $activeAudienceCount = mysqli_fetch_assoc($activeAudienceResult)['count'];

        // Fetch total audience count
        $totalAudienceQuery = "SELECT COUNT(*) AS count FROM members";
        $totalAudienceResult = mysqli_query($conn, $totalAudienceQuery);
        $totalAudienceCount = mysqli_fetch_assoc($totalAudienceResult)['count'];

        function getCount($query, $conn)
        {
            $result = $conn->query($query);
            $row = $result->fetch_assoc();
            return $row['count'] ?? 0;
        }

        // General statistics
        $totalUsers = getCount("SELECT COUNT(*) AS count FROM members", $conn);
        $activeUsers = getCount("SELECT COUNT(*) AS count FROM members WHERE status='active'", $conn);
        $newSignups = getCount("SELECT COUNT(*) AS count FROM members WHERE registration_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)", $conn);
        $totalAnnouncements = getCount("SELECT COUNT(*) AS count FROM announcements", $conn);

        // Fetch engagement data per month
        $engagementData = [];
        $query = "
            SELECT 
                MONTH(engagement_date) AS month, 
                COUNT(*) AS count 
            FROM engagement 
            GROUP BY month 
            ORDER BY month ASC";

        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $monthNum = (int)$row['month'];
            $engagementData[$monthNum] = (int)$row['count'];
        }

        // Format data for JavaScript (fill missing months with 0)
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July'];
        $chartData = [];
        foreach ($months as $index => $month) {
            $monthNum = $index + 1;
            $chartData[] = $engagementData[$monthNum] ?? 0; // Default to 0 if no data
        }
        $conn->close();
        ?>
        <?php
        // Connect to DB
        include '../database.php'; // or use your own connection logic

        // Get current year and month
        $currentYear = date('Y');
        $currentMonth = date('m');

        // Query to count signups this month
        $sql = "SELECT COUNT(*) as count FROM members 
        WHERE YEAR(registration_date) = '$currentYear' 
        AND MONTH(registration_date) = '$currentMonth'";

        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);

        $newSignups = $row['count'];
        ?>

        <div id="welcomeToast" class="floating-toast">
            <p id="welcomeMessage"></p>
        </div>
        <div class="container-fluid px-4">
            <div class="row g-3 my-2">
                <div class="col-12 col-md-6 col-lg-3 mb-4">
                    <a href="announcement.php" class="text-decoration-none text-dark">
                        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded stat-card">
                            <div>
                                <h3 class="fs-2"><?php echo $announcementCount; ?></h3>
                                <p class="fs-5"><b>Posted Announcements</b></p>
                            </div>
                            <i class="fas fa-bullhorn fs-1 primary-text border rounded-full secondary-bg p-3"></i>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-4">
                    <a href="manage_users.php" class="text-decoration-none text-dark">
                        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded stat-card">
                            <div>
                                <h3 class="fs-2"><?php echo $activeAudienceCount; ?></h3>
                                <p class="fs-5"><b>Active Audience</b></p>
                            </div>
                            <i class="fas fa-user fs-1 primary-text border rounded-full secondary-bg p-3"></i>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-4">
                    <a href="draft.php" class="text-decoration-none text-dark">
                        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded stat-card">
                            <div>
                                <h3 class="fs-2"><?php echo $draftCount; ?></h3>
                                <p class="fs-5"><b>Drafted Announcements</b></p>
                            </div>
                            <i class="fas fa-file-alt fs-1 primary-text border rounded-full secondary-bg p-3"></i>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-4">
                    <a href="user.php" class="text-decoration-none text-dark">
                        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded stat-card">
                            <div>
                                <h3 class="fs-2"><?php echo $totalAudienceCount; ?></h3>
                                <p class="fs-5"><b>Total Audience</b></p>
                            </div>
                            <i class="fas fa-user fs-1 primary-text border rounded-full secondary-bg p-3"></i>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-6 col-lg-3 mb-4">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-between align-items-center rounded stat-card">
                        <div class="text-start">
                            <h3 class="fs-2"><?php echo max($totalUsers - $activeUsers, 0); ?></h3>
                            <p class="fs-5"><b>Inactive Users</b></p>
                        </div>
                        <i class="fas fa-user-slash fs-1 primary-text border rounded-full secondary-bg p-3"></i>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-4">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-between align-items-center rounded stat-card">
                        <div class="text-start">
                            <h3 class="fs-2"><?php echo round(($activeUsers / max($totalUsers, 1)) * 100, 2); ?>%</h3>
                            <p class="fs-5"><b>Active Users</b></p>
                        </div>
                        <i class="fas fa-user-check fs-1 primary-text border rounded-full secondary-bg p-3"></i>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-4">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-between align-items-center rounded stat-card">
                        <div class="text-start">
                            <h3 class="fs-2"><?php echo $newSignups; ?></h3>
                            <p class="fs-5"><b>New Signups</b></p>
                        </div>
                        <i class="fas fa-user-plus fs-1 primary-text border rounded-full secondary-bg p-3"></i>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-4">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-between align-items-center rounded stat-card">
                        <div class="text-start">
                            <h3 class="fs-2"><?php echo $totalAnnouncements; ?></h3>
                            <p class="fs-5"><b>Total Announcements</b></p>
                        </div>
                        <i class="fas fa-bullhorn fs-1 primary-text border rounded-full secondary-bg p-3"></i>
                    </div>
                </div>
            </div>
        </div>


        <?php
        include "../database.php"; // Include the database connection

        // Pagination variables
        $limit = 10; // Number of users per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max($page, 1);
        $offset = ($page - 1) * $limit;

        // ✅ Get total records from the correct table: members
        $totalQuery = "SELECT COUNT(*) as total FROM members";
        $totalResult = $conn->query($totalQuery);
        $totalRow = $totalResult->fetch_assoc();
        $totalUsers = $totalRow['total'];
        $totalPages = ceil($totalUsers / $limit);

        // Fetch users in descending order
        $sql = "SELECT * FROM members ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        ?>

        <div class="row my-3">
            <h3 class="fs-4 mb-3">&nbsp;&nbsp;&nbsp;Recent Users</h3>
            <div class="col-lg-10 col-md-12 table-container">
                <div class="table-responsive bg-white rounded shadow-sm p-3">
                    <table class="table table-hover">
                        <thead class="table-success">
                            <tr>
                                <th scope="col" width="50">#</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Last Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone Number</th>
                                <th scope="col">Year</th>
                                <th scope="col">Course</th>
                                <th scope="col">Role</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td><b>" . htmlspecialchars($row['id']) . "</b></td>";
                                    echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['year']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['course']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                                    echo "<td>";
                                    echo (strtolower($row['status']) === 'active')
                                        ? "<span class='badge bg-success'>Active</span>"
                                        : "<span class='badge bg-danger'>Inactive</span>";
                                    echo "</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>No users found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                    <nav>
                        <ul class="pagination justify-content-end flex-wrap mb-0">
                            <!-- Previous -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <?php if ($page > 1): ?>
                                    <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                                <?php else: ?>
                                    <span class="page-link">Previous</span>
                                <?php endif; ?>
                            </li>

                            <!-- Page numbers -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <?php if ($page < $totalPages): ?>
                                    <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                <?php else: ?>
                                    <span class="page-link">Next</span>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </nav>

                </div>
            </div>
        </div>

        <div class="row my-5">
            <h3 class="fs-4 mb-3">&nbsp;&nbsp;&nbsp;User Engagement</h3>
            <div class="col-12">
                <canvas id="engagementChart" style="max-height: 450px;"></canvas>
            </div>
        </div>
    </div>



    <?php $conn->close(); ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let engagementChart; // ✅ Declare globally

        document.addEventListener('DOMContentLoaded', function() {
            function fetchAndUpdateChart() {
                fetch('fetch_engagement.php?t=' + new Date().getTime()) // Prevents caching
                    .then(response => response.json())
                    .then(data => {
                        const labels = Object.keys(data);
                        const emailData = labels.map(month => data[month]?.email || 0);
                        const smsData = labels.map(month => data[month]?.sms || 0);

                        if (engagementChart) {
                            engagementChart.destroy(); // 🔥 Reset chart before updating
                        }

                        const ctx = document.getElementById('engagementChart').getContext('2d');
                        engagementChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                        label: 'Email Engagements',
                                        data: emailData,
                                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        borderWidth: 2
                                    },
                                    {
                                        label: 'SMS Engagements',
                                        data: smsData,
                                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                        borderColor: 'rgba(255, 99, 132, 1)',
                                        borderWidth: 2
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: '📊 Engagement Statistics', // Chart title
                                        font: {
                                            size: 18,
                                            weight: 'bold' // ✅ Bold title
                                        },
                                        color: '#333'
                                    },
                                    legend: {
                                        labels: {
                                            font: {
                                                weight: 'bold', // ✅ Bold legend labels
                                                size: 14
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Month',
                                            font: {
                                                size: 14,
                                                weight: 'bold' // ✅ Bold X-axis title
                                            }
                                        },
                                        ticks: {
                                            font: {
                                                weight: 'bold' // ✅ Bold month names (labels)
                                            }
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Engagement Count',
                                            font: {
                                                size: 14,
                                                weight: 'bold' // ✅ Bold Y-axis title
                                            }
                                        },
                                        ticks: {
                                            font: {
                                                weight: 'bold' // ✅ Bold Y-axis numbers
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }

            fetchAndUpdateChart(); // Initial load
            setInterval(fetchAndUpdateChart, 30000); // Refresh every 30s
        });


        history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.pushState(null, null, location.href);
        };

        document.addEventListener("DOMContentLoaded", function() {
            // Hide the loading overlay after the page loads
            document.getElementById("loading-overlay").style.display = "none";

            checkInternet(); // Run the internet check on load
        });

        function checkInternet() {
            if (navigator.onLine) {
                // If online, hide the no-internet message
                document.getElementById("no-internet-overlay").style.display = "none";
            } else {
                // If offline, show the message
                document.getElementById("no-internet-overlay").style.display = "flex";
            }
        }

        // Listen for internet status changes
        window.addEventListener("online", checkInternet);
        window.addEventListener("offline", checkInternet);




        document.addEventListener("DOMContentLoaded", function() {
            let shouldSpeak = <?= json_encode($shouldSpeak); ?>;
            let firstName = <?= json_encode($firstName); ?>;

            if (shouldSpeak) {
                let welcomeMessage = `Hi ${firstName}, Welcome to the dashboard of SPA AI system.`;

                // Show floating message
                showWelcomeToast(welcomeMessage);

                // Load voices and trigger speech (Ensure it runs only once)
                function handleVoicesLoaded() {
                    if (!window.speechSpoken) { // Prevent multiple executions
                        speak(welcomeMessage);
                        window.speechSpoken = true;
                    }
                }

                if (speechSynthesis.getVoices().length > 0) {
                    handleVoicesLoaded(); // If voices are already loaded
                } else {
                    speechSynthesis.onvoiceschanged = function() {
                        handleVoicesLoaded();
                        speechSynthesis.onvoiceschanged = null; // Unbind event to prevent re-trigger
                    };
                }
            }
        });

        // Function to show floating welcome message
        function showWelcomeToast(message) {
            let toast = document.getElementById("welcomeToast");
            let toastMessage = document.getElementById("welcomeMessage");

            toastMessage.textContent = message;
            toast.classList.add("show");

            setTimeout(() => {
                toast.classList.remove("show");
            }, 5000);
        }

        // Function to trigger AI voice (Force Female Voice)
        function speak(text) {
            if ('speechSynthesis' in window) {
                let speech = new SpeechSynthesisUtterance(text);
                speech.lang = "en-US";
                speech.rate = 0.95;
                speech.pitch = 1.3;
                speech.volume = 1;

                let voices = speechSynthesis.getVoices();
                let femaleVoice = voices.find(voice =>
                    voice.name.includes("Samantha") ||
                    voice.name.includes("Google UK English Female") ||
                    voice.name.includes("Victoria") ||
                    voice.name.includes("Tessa") ||
                    voice.name.includes("Serena") ||
                    voice.name.includes("Zira")
                );

                if (femaleVoice) {
                    speech.voice = femaleVoice;
                }

                speechSynthesis.speak(speech);
            } else {
                console.error("Speech Synthesis not supported in this browser.");
            }
        }
    </script>


    </body>

</html>