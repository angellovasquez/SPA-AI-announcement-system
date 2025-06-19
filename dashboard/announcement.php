<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['currentUser'])) {
    header("Location: ../login.php?error=Unauthorized+Access");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement Generator</title>
    <link rel="icon" type="image/png" sizes="712x712" href="../images/SPA AI.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .announcement-container {
            background: linear-gradient(to bottom, #ece9e6, #ffffff);
            border: 2px solid #2a9d8f;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 15px;
        }

        .announcement-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 25px;
            padding: 15px;
        }

        .announcement-title {
            font-weight: bold;
            font-size: 1.25rem;
        }

        .announcement-description {
            margin-top: 10px;
        }

        .announcement-date {
            font-size: 0.85rem;
            color: #555;
        }

        input[type="checkbox"]:checked {
            accent-color: green;
        }

        .output-container {
            background: #eef1f7;
            padding: 15px;
            border-radius: 8px;
            min-height: 100px;
        }

        #audienceModal .modal-dialog {
            max-width: 30%;
            height: 70vh;
            position: fixed;
            top: 0;
            right: 0;
            width: 450px;
            height: 70vh;
            margin: 0;
            border-radius: 0;
        }

        #audienceModal .modal-content {
            height: 100%;
        }

        #audienceModal .modal-body {
            height: calc(100% - 120px);
            overflow-y: auto;
        }

        .modal-content {
            background: linear-gradient(to bottom, #ece9e6, #ffffff);
            border: 2px solid #2a9d8f;
        }



        #loader {
            display: none;
            text-align: center;
            margin-top: 20px;
        }

        .loader-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(0, 0, 0, 0.2);
            border-top-color: #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loader-text {
            margin-top: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            color: black;
        }

        .loader-text {
            animation: wave 1s ease-in-out infinite;
        }

        .loader-text span {
            display: inline-block;
            animation: wave 1.2s ease-in-out infinite;
            animation-delay: calc(0.1s * var(--i));
        }

        .loader-text span:nth-child(odd) {
            animation-delay: calc(0.2s * var(--i));
        }

        #editableAnnouncement {
            font-size: 18px;
            /* Adjust as needed */

            line-height: 1.5;
        }

        @keyframes wave {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0);
            }
        }


        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <?php include_once "main.php"; ?>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0" id="header-title">
                    <i class="fas fa-bullhorn me-2 "></i>Announcement
                </h2>
            </div>
        </nav>

        <div class="container mt-5">
            <div class="announcement-container p-4 bg-white rounded shadow">
                <h2 class="text-center">Generate Announcement</h2>
                <form id="announcementForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="title">Title:</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Write your title here..." required>
                    </div>
                    <div class="mb-3">
                        <label for="max_tokens" class="form-label fw-bold">Max Tokens:</label> <small class="form-text text-muted" data-toggle="tooltip" title="150 tokens can already generate a short announcement, while a higher value allows for more details. Adjust the max tokens for AI-generated content (50-600 🎟️).">
                            🎟️
                        </small>
                        <input type="number" class="form-control" id="max_tokens" name="max_tokens" placeholder="Enter max tokens (e.g., 150)" min="50" max="600" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Create Announcement:</label>
                        <small class="form-text text-muted" data-toggle="tooltip" title="For email prompting, provide a detailed and professional message that clearly conveys the information. For SMS, keep it concise, always put this 160 CHARACTERS in making a prompt, for quick and effective short communication.">
                            <i class="fas fa-question-circle"></i>
                        </small>

                        <div class="input-group">
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Write your announcement details here..." required></textarea>
                            <button type="button" class="btn btn-outline-success" id="start-voice">
                                <i class="fas fa-microphone-lines"></i>
                            </button>
                        </div>
                        <small id="voice-feedback" class="form-text text-muted"></small>
                    </div>
                    <br>
                    <div class="text-left">
                        <button type="submit" class="btn btn-success" id="generate-ai">Generate Announcement</button>
                        <button type="button" class="btn btn-danger" id="cancel-button">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="loader" class="text-center" style="display: none;">
            <div class="spinner-grow" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="loader-text">Please wait, The Announcement is Generating...</p>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="announcementModalLabel">Generated Announcement:</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <textarea class="form-control" id="editableAnnouncement" rows="20"></textarea>
                    </div>
                    <div id="toastContainer"></div>
                    <div class="modal-footer">
                        <div class="mb-3" id="smsGatewayInput" style="display: none;">
                            <label for="smsGatewayIP" class="form-label fw-bold">SMS Gateway IP Address:</label>
                            <input type="text" class="form-control" id="smsGatewayIP" placeholder="Enter SMS Gateway IP">
                        </div>
                        <label>
                            <input type="checkbox" id="sendSMS" class="send-option"> <small class="form-text text-muted"
                                data-toggle="tooltip"
                                title="SMS messages are limited to 160 characters. If your message is longer, please use the email option instead.">
                                📩
                            </small> Send via SMS
                        </label>
                        <label><input type="checkbox" id="sendEmail" class="send-option"> Send via Email</label>
                        <button type="button" class="btn btn-success" onclick="postAnnouncement()">Post</button>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#audienceModal">Recipient</button>
                        <button type="button" class="btn btn-primary" onclick="saveDraft()">Draft</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" onclick="closeAnnouncementModal()">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="audienceModal" tabindex="-1" aria-labelledby="audienceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="audienceModalLabel">Select Audience</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Year:</label>
                            <div>
                                <input type="checkbox" id="year1" name="year" value="1st Year"> <label for="year1"><b>1st Year</b></label><br>
                                <input type="checkbox" id="year2" name="year" value="2nd Year"> <label for="year2"><b>2nd Year</b></label><br>
                                <input type="checkbox" id="year3" name="year" value="3rd Year"> <label for="year3"><b>3rd Year</b></label><br>
                                <input type="checkbox" id="year4" name="year" value="4th Year"> <label for="year4"><b>4th Year</b></label><br>
                                <input type="checkbox" id="Teacher" name="year" value="Teacher"> <label for="Teacher"><b>Teachers</b></label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Courses:</label>
                            <div>
                                <input type="checkbox" id="course1" name="course" value="Computer Science"> <label for="course1">(BSCS)<b>Computer Science</b></label><br>
                                <input type="checkbox" id="course2" name="course" value="Information Technology"> <label for="course2">(BSIT)<b>Information Technology</b></label><br>
                                <input type="checkbox" id="course3" name="course" value="Business Administration"> <label for="course3">(BSBA)<b>Business Administration</b></label><br>
                                <input type="checkbox" id="course4" name="course" value="Entrepreneurship"> <label for="course4">(BS-ENTREP)<b>Entrepreneurship</b></label><br>
                                <input type="checkbox" id="course5" name="course" value="Accounting Information Systems"><label for="course5">(BS-AIS)<b>Accounting Information Systems</b></label><br>
                                <input type="checkbox" id="course6" name="course" value="Office Administration"> <label for="course6">(BSOA)<b>Office Administration</b></label><br>
                                <input type="checkbox" id="course7" name="course" value="Technical Teacher Education"><label for="course7">(BTVTEd)<b>Technical-Vocational Teacher Education</b></label><br>
                                <input type="checkbox" id="TeacherCourse" name="course" value="Teacher"> <label for="TeacherCourse"><b>Teachers</b></label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="selectAllBtn">Select All</button>
                        <button type="button" class="btn btn-success" id="Confirm">Confirm</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });

        document.getElementById("announcementForm").addEventListener("submit", function(event) {
            event.preventDefault();

            document.getElementById("loader").style.display = "block";

            let announcementModal = new bootstrap.Modal(document.getElementById('announcementModal'));
            announcementModal.hide();

            let title = document.getElementById("title").value;
            let description = document.getElementById("description").value;
            let maxTokens = document.getElementById("max_tokens").value || 150; // Default to 150

            let requestData = {
                title,
                description,
                max_tokens: parseInt(maxTokens) // Ensure it's an integer
            };

            fetch("generate_announcement.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("loader").style.display = "none";

                    if (data.generated_text) {
                        announcementModal.show();
                        typeEffect(document.getElementById("editableAnnouncement"), data.generated_text, 5);
                    } else {
                        document.getElementById("editableAnnouncement").value = `Error: ${data.error || "Unexpected response"}`;
                    }
                })
                .catch(error => {
                    document.getElementById("loader").style.display = "none";
                    document.getElementById("editableAnnouncement").value = "Failed to generate announcement.";
                });
        });


        document.addEventListener("DOMContentLoaded", function() {
            const titleInput = document.getElementById("title");
            const descriptionInput = document.getElementById("description");

            function capitalizeFirstLetter(inputField) {
                let value = inputField.value;
                if (value.length > 0) {
                    inputField.value = value.charAt(0).toUpperCase() + value.slice(1);
                }
            }

            // Apply on input (while typing)
            titleInput.addEventListener("input", function() {
                capitalizeFirstLetter(titleInput);
            });

            descriptionInput.addEventListener("input", function() {
                capitalizeFirstLetter(descriptionInput);
            });

            // Apply on blur (when losing focus)
            titleInput.addEventListener("blur", function() {
                capitalizeFirstLetter(titleInput);
            });

            descriptionInput.addEventListener("blur", function() {
                capitalizeFirstLetter(descriptionInput);
            });
        });

        function showToast(message, type = "success") {
            let toastContainer = document.getElementById("toastContainer");
            if (!toastContainer) {
                console.error("Toast container not found!");
                return;
            }

            let toast = document.createElement("div");
            toast.className = `alert alert-${type} fade show`;
            toast.style.minWidth = "300px";
            toast.style.maxWidth = "500px";
            toast.style.textAlign = "center";
            toast.style.padding = "15px";
            toast.style.borderRadius = "5px";
            toast.style.marginTop = "10px";
            toast.style.opacity = "0";
            toast.style.transition = "opacity 0.5s ease";

            toast.innerHTML = message;
            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = "1";
            }, 50);

            setTimeout(() => {
                toast.style.opacity = "0";
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        function typeEffect(element, text, speed) {
            element.value = "";
            let i = 0;

            function typing() {
                if (i < text.length) {
                    element.value += text.charAt(i);
                    i++;
                    setTimeout(typing, speed);
                }
            }
            typing();
        }

        // Speech-to-Text (Voice Input)
        // Speech-to-Text (Voice Input)
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (SpeechRecognition) {
            const recognition = new SpeechRecognition();
            recognition.continuous = false; // Stop after one input
            recognition.interimResults = false; // Don't show interim results
            recognition.lang = 'en-US'; // Set the language to English (US)

            // Start listening for voice input when the button is clicked
            document.getElementById('start-voice').addEventListener('click', () => {
                recognition.start();
                document.getElementById('voice-feedback').textContent = 'Listening...';
            });

            // When speech is recognized
            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript; // Get the recognized text
                document.getElementById('description').value = transcript; // Display it in the text area
                document.getElementById('voice-feedback').textContent = 'Voice input captured. Generating announcement...';

                // Automatically trigger the "Generate Announcement" button
                setTimeout(() => {
                    document.getElementById('generate-ai').click(); // Simulate a click on the "Generate Announcement" button
                }, 1000); // Wait 1 second before triggering (adjust if needed)
            };

            // Error handling
            recognition.onerror = (event) => {
                document.getElementById('voice-feedback').textContent = `Error: ${event.error}`;
            };

            recognition.onend = () => {
                document.getElementById('voice-feedback').textContent = 'Speech recognition stopped.';
            };
        } else {
            document.getElementById('start-voice').disabled = true;
            document.getElementById('voice-feedback').textContent = 'Voice recognition not supported.';
        }

        document.getElementById('cancel-button').addEventListener('click', function() {
            // Reset the form fields
            document.getElementById('title').value = ''; // Clear the title field
            document.getElementById('description').value = ''; // Clear the description field
            document.getElementById('max_tokens').value = '';
        });

        function postAnnouncement(event) {
            if (event) event.preventDefault(); // Prevent page reload

            let title = document.getElementById("title").value.trim();
            let announcementText = document.getElementById("editableAnnouncement").value.trim();
            let sendSMS = document.getElementById("sendSMS").checked ? 1 : 0;
            let sendEmail = document.getElementById("sendEmail").checked ? 1 : 0;
            let smsGatewayIP = document.getElementById("smsGatewayIP").value.trim();

            let selectedYears = Array.from(document.querySelectorAll("input[name='year']:checked")).map(cb => cb.value);
            let selectedCourses = Array.from(document.querySelectorAll("input[name='course']:checked")).map(cb => cb.value);

            // ✅ Ensure at least one year and one course is selected
            if (selectedYears.length === 0 || selectedCourses.length === 0) {
                showToast("⚠️ Please select at least one Year and one Course before posting.", "warning");
                return;
            }

            if (!title) {
                showToast("⚠️ Please enter a title before posting.", "warning");
                return;
            }

            if (!announcementText) {
                showToast("⚠️ Please enter an announcement before posting.", "warning");
                return;
            }

            if (!sendSMS && !sendEmail) {
                showToast("⚠️ Please select at least one option (SMS or Email) before posting.", "warning");
                return;
            }

            if (sendSMS && !smsGatewayIP) {
                showToast("⚠️ Please enter the SMS Gateway IP Address.", "warning");
                return;
            }

            // 🔹 Fetch recipients before posting
            fetch("process_recipient.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        years: selectedYears,
                        courses: selectedCourses
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Fetched Recipients:", data);

                    if (!data || data.length === 0) {
                        showToast("⚠️ No recipients found! Announcement will not be posted.", "danger");
                        return;
                    }

                    let recipientCount = data.length; // ✅ Count recipients

                    // ✅ Proceed with posting if recipients exist
                    let formData = new FormData();
                    formData.append("title", title);
                    formData.append("announcement", announcementText);
                    formData.append("sendSMS", sendSMS);
                    formData.append("sendEmail", sendEmail);
                    formData.append("smsGatewayIP", smsGatewayIP);
                    formData.append("status", "published");
                    formData.append("years", JSON.stringify(selectedYears));
                    formData.append("courses", JSON.stringify(selectedCourses));

                    showToast("📢 Posting announcement...", "info");

                    fetch("post_announcement.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(postData => {
                            console.log("Response:", postData);

                            if (postData.success) {
                                showToast("✅ Announcement successfully posted!", "success");

                                // ✅ Call saveRecipients() to store recipient details in the database

                                // 🔹 Send SMS if selected
                                if (sendSMS) {
                                    sendSMSFunction(announcementText);
                                }

                                // 🔹 Send Email if selected
                                if (sendEmail) {
                                    sendEmailFunction(announcementText);
                                }

                            } else {
                                showToast("❌ Failed to post announcement: " + postData.message, "danger");
                            }
                        })
                        .catch(error => {
                            console.error("Error posting announcement:", error);
                            showToast("❌ Failed to post announcement. Please try again.", "danger");
                        });

                })
                .catch(error => {
                    console.error("Error fetching recipients:", error);
                    showToast("❌ Error checking recipients. Please try again.", "danger");
                });
        }

        // ✅ Save recipients function (Moved outside so it's always available)
        // ✅ SMS Sending Function (Improved)
        function sendSMSFunction(message) {
            let ip = document.getElementById("smsGatewayIP").value.trim();

            if (!ip) {
                showToast("⚠️ Please enter the SMS Gateway IP Address.", "warning");
                return;
            }

            // Validate IP Address Format
            const ipRegex = /^(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}$/;
            if (!ipRegex.test(ip)) {
                showToast("❌ Invalid IP address format.", "danger");
                return;
            }

            let formData = new FormData();
            formData.append("MSG_SMS", message);
            formData.append("IPadd", ip);

            // Get selected years
            let selectedYears = Array.from(document.querySelectorAll("input[name='year']:checked"))
                .map(input => input.value);
            formData.append("years", JSON.stringify(selectedYears)); // ✅ Send as JSON string

            // Get selected courses
            let selectedCourses = Array.from(document.querySelectorAll("input[name='course']:checked"))
                .map(input => input.value);
            formData.append("courses", JSON.stringify(selectedCourses)); // ✅ Send as JSON string

            console.log("📤 Sending SMS Data:", {
                message: message,
                ip: ip,
                years: selectedYears,
                courses: selectedCourses
            }); // 🔍 Debugging

            showToast("📲 Sending SMS...", "info");

            fetch("send_sms.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text()) // Read response as text first
                .then(text => {
                    console.log("📨 SMS Response Raw:", text); // 🔍 Debugging

                    let data;
                    try {
                        data = JSON.parse(text); // ✅ Try parsing JSON
                    } catch (e) {
                        throw new Error("Invalid JSON response: " + text);
                    }

                    console.log("✅ Parsed SMS Response:", data); // 🔍 Log parsed response

                    if (data.success) {
                        showToast("✅ SMS Sent Successfully!", "success");
                    } else {
                        showToast("❌ Failed to send SMS: " + data.message, "danger");
                    }
                })
                .catch(error => {
                    console.error("❌ Error sending SMS:", error);
                    showToast("❌ Failed to send SMS. Check the SMS Gateway IP.", "danger");
                });
        }


        function sendEmailFunction(message) {
            let formData = new FormData();
            formData.append("emailMessage", message);

            // Get selected years
            let selectedYears = Array.from(document.querySelectorAll("input[name='year']:checked"))
                .map(input => input.value);
            formData.append("years", JSON.stringify(selectedYears)); // ✅ Send as JSON string

            // Get selected courses
            let selectedCourses = Array.from(document.querySelectorAll("input[name='course']:checked"))
                .map(input => input.value);
            formData.append("courses", JSON.stringify(selectedCourses)); // ✅ Send as JSON string

            console.log("📤 Sending Data:", {
                message: message,
                years: selectedYears,
                courses: selectedCourses
            }); // 🔍 Debugging

            showToast("📧 Sending Email...", "info");

            fetch("send_email.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text()) // Read response as text first
                .then(text => {
                    console.log("📨 Email Response Raw:", text); // 🔍 Debugging

                    let data;
                    try {
                        data = JSON.parse(text); // ✅ Try parsing JSON
                    } catch (e) {
                        throw new Error("Invalid JSON response: " + text);
                    }

                    console.log("✅ Parsed Email Response:", data); // 🔍 Log parsed response

                    if (data.success) {
                        showToast("✅ Email Sent Successfully!", "success");
                    } else {
                        showToast("❌ Failed to send Email: " + data.message, "danger");
                    }
                })
                .catch(error => {
                    console.error("❌ Error sending Email:", error);
                    showToast("❌ Failed to send Email.", "danger");
                });
        }


        function saveDraft() {
            let title = document.getElementById("title").value.trim();
            let content = document.getElementById("editableAnnouncement").value.trim();


            if (!content) {
                showToast("⚠️ Draft content cannot be empty.", "warning");
                return;
            }

            let formData = new FormData();
            formData.append("title", title);
            formData.append("announcement", content);
            fetch("saveDraft.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json()) // Expecting JSON response
                .then(data => {
                    if (data.success) {
                        showToast("✅ Draft saved successfully!", "success");
                    } else {
                        showToast("❌ Error: " + data.message, "danger");
                    }
                })
                .catch(error => {
                    console.error("Error saving draft:", error);
                    showToast("❌ An error occurred while saving.", "danger");
                });
        }

        document.getElementById("sendSMS").addEventListener("change", function() {
            let smsGatewayInput = document.getElementById("smsGatewayInput");
            if (this.checked) {
                smsGatewayInput.style.display = "block";
            } else {
                smsGatewayInput.style.display = "none";
            }
        });

        document.getElementById('selectAllBtn').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]:not(.send-option)'); // Exclude SMS & Email
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);

            checkboxes.forEach(checkbox => checkbox.checked = !allChecked);

            this.textContent = allChecked ? 'Select All' : 'Deselect All';
        });

        // Function to handle recipient selection
        function handleRecipientSelection(selectedRecipients) {
            console.log("Recipients Selected:", selectedRecipients.length);

            // Do NOT auto-check SMS or Email checkboxes
            document.querySelectorAll(".send-option").forEach(option => {
                option.checked = false;
            });
        }


        document.getElementById("Confirm").addEventListener("click", function() {
            let selectedYears = [];
            let selectedCourses = [];

            // Collect selected years
            document.querySelectorAll("input[name='year']:checked").forEach((checkbox) => {
                selectedYears.push(checkbox.value);
            });

            // Collect selected courses
            document.querySelectorAll("input[name='course']:checked").forEach((checkbox) => {
                selectedCourses.push(checkbox.value);
            });

            // Ensure at least one year and one course are selected
            if (selectedYears.length === 0 || selectedCourses.length === 0) {
                showToast("⚠️ Please select your recipient, at least one Year and one Course!", "info");
                return;
            }

            // Send selected data to the server
            fetch("process_recipient.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        years: selectedYears,
                        courses: selectedCourses,
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    console.log("✅ Response from server:", data);

                    if (data.error) {
                        showToast(`❌ Error: ${data.error}`, "danger");
                    } else {
                        // Filter only active users
                        let activeRecipients = data.filter(user => user.status && user.status.toLowerCase() === "active");

                        showToast(`📢 Announcement will be sent to <b>${activeRecipients.length || 0}</b> active recipients.`, "success");
                    }

                    // ✅ Close the modal after showing the toast
                    closeAudienceModal();
                })
                .catch(error => {
                    console.error("❌ Fetch error:", error);
                    showToast("❌ Something went wrong. Please try again.", "danger");

                    // ✅ Ensure modal closes even if there's an error
                    closeAudienceModal();
                });
        });

        // ✅ Function to Properly Close the Modal
        // ✅ General function to close any modal properly
        function closeModal(modalId) {
            let modalElement = document.getElementById(modalId);

            if (!modalElement) return; // Exit if modal doesn't exist

            let modalInstance = bootstrap.Modal.getInstance(modalElement);

            if (modalInstance) {
                modalInstance.hide(); // ✅ Close modal using Bootstrap
            }

            // ✅ Wait for modal to fully close before cleaning up
            modalElement.addEventListener("hidden.bs.modal", function() {
                document.body.classList.remove("modal-open"); // Remove body lock

                let backdrops = document.querySelectorAll(".modal-backdrop");
                backdrops.forEach((backdrop) => backdrop.remove()); // ✅ Remove stuck backdrops
            }, {
                once: true
            }); // Ensure event runs only once per close action
        }

        // ✅ Function to close the audience modal
        function closeAudienceModal() {
            closeModal("audienceModal");
        }

        // ✅ Function to close the announcement modal
        function closeAnnouncementModal() {
            closeModal("announcementModal");
        }

        // ✅ Ensure modals close properly when clicking outside (optional)
        document.addEventListener("click", function(event) {
            let modals = document.querySelectorAll(".modal.show"); // Get open modals
            modals.forEach(modal => {
                if (!modal.contains(event.target) && event.target.classList.contains("modal")) {
                    let modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                }
            });
        });
    </script>
</body>

</html>