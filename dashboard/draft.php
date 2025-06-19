<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Draft Announcements</title>

<?php include_once "main.php";

// Database connection
require_once '../database.php';
// Fetch drafts
$query = "SELECT id, title, content, created_at FROM announcements WHERE status = 'draft' ORDER BY created_at DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    // Fetch the first announcement if there are any drafts
    $row = $result->fetch_assoc();

    // Check if created_at is not empty
    if (!empty($row['created_at'])) {
        $formattedDate = date("F j, Y - g:i A", strtotime($row['created_at']));
    } else {
        $formattedDate = 'Date not available';  // Default if created_at is empty or null
    }

    $title = $row['title'];
    $content = $row['content'];
} else {
    // Handle the case when no drafts exist
    $formattedDate = (!empty($row['created_at'])) ? date("F j, Y - g:i A", strtotime($row['created_at'])) : 'N/A';  // Default message
    $title = '';  // Default empty values
    $content = '';
}

$limit = 4; // 2 per row, 2 rows per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Fetch drafts with pagination
$query = "SELECT id, title, content, created_at FROM announcements WHERE status = 'draft' ORDER BY created_at DESC LIMIT $start, $limit";
$result = $conn->query($query);

// Count total drafts
$totalQuery = "SELECT COUNT(id) AS total FROM announcements WHERE status = 'draft'";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $limit);
?>

<head>
    <style>
        /* General Body Styling */
        body {
            background-color: #f8f9fa;
        }

        .pagination-container {
            background: linear-gradient(to bottom, #ece9e6, #ffffff);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: fit-content;
            margin: 0 auto;
            border: 2px solid #2a9d8f;
            /* Centering the pagination box */
        }

        /* Announcement Container */
        .announcement-container {
            margin-top: 10%;
            margin-bottom: 10%;
        }

        /* Announcement Card */
        .announcement-card {
            background: #fff;
            padding: 20px;
            /* Increased padding for larger cards */
            border-radius: 12px;
            border: 2px solid #2a9d8f;
            /* Added border */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            font-size: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            min-height: 250px;
            /* Ensuring consistent card size */
        }

        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            background-image: linear-gradient(to top, #c1dfc4 0%, #deecdd 100%);
        }

        /* Announcement Title */
        .announcement-title {
            font-weight: bold;
            font-size: 1.2rem;
            color: #333;
        }

        /* Announcement Description */
        .announcement-description {
            margin-top: 8px;
            font-size: 0.95rem;
            color: #555;
            flex-grow: 1;
            /* Ensures content expands while buttons stay aligned */
        }

        /* Announcement Date */
        .announcement-date {
            font-size: 0.85rem;
            color: #777;
        }

        /* Bold Text */
        .bold-text {
            font-weight: bold;
        }

        /* Button Container */
        .btn-container {
            margin-top: auto;
            /* Pushes buttons to the bottom */
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        /* Row Spacing */
        .row {
            gap: 10px;
            /* Increased spacing for better layout */
        }

        .pagination-nav {
            position: relative;
            top: -80px;
            /* Adjust the value to move it up */
        }

        /* Checkbox Styling */
        input[type="checkbox"]:checked {
            accent-color: green;
        }

        h3,
        .announcement-title {
            color: #264653;
            /* Deep Navy */
        }

        /* 📜 Description */
        .announcement-description {
            color: #1b4332;
            /* Dark Green */
        }

        /* 📅 Date Styling */
        .announcement-date {
            font-size: 0.9rem;
            color: #0077b6;
            /* Azure Blue */
        }

        /* 🔘 Buttons */
        .btn-primary {
            background-color: #2a9d8f;
            /* Teal */
            border: none;
        }

        .btn-primary:hover {
            background-color: #116530;
            /* Dark Green */
        }

        .btn-danger {
            background-color: #d67f7f;
            /* Soft Coral */
            border: none;
        }

        .btn-danger:hover {
            background-color: #e76f51;
            /* Warm Rose */
        }

        .btn-info {
            background-color: #0077b6;
            /* Azure Blue */
            color: white !important;
            border: none;
        }

        .btn-info:hover {
            background-color: #264653;
            /* Deep Navy */
        }

        /* 🛠 Pagination */
        .pagination-nav {
            margin-top: 10px;
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

        /* RESPONSIVE TABLET VIEW */
        @media (max-width: 1024px) {
            .announcement-item {
                flex: 0 0 50%;
                /* 2 cards per row */
            }
        }

        /* MOBILE VIEW */
        @media (max-width: 768px) {
            .announcement-item {
                flex: 0 0 100%;
                /* 1 card per row */
            }
        }
    </style>
</head>

<body>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle"></i>
                <h2 class="fs-2 m-0">
                    <i class="fas fa-file-alt me-2"></i>Drafts
                </h2>
            </div>
        </nav>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="deleteToast" class="toast align-items-center text-bg-light border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-bold text-success" id="toastMessage">
                        ✅ Draft deleted successfully!
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="editToast" class="toast align-items-center text-bg-light border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-bold text-success" id="toastMessage">
                        ✅ Draft updated successfully!
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="pagination-container  p-3 mt-3 shadow-sm rounded">
                <div class="announcement-container mt-2">
                    <h3 class="text-center">Saved Drafts</h3>
                    <br>
                    <div class="row justify-content-center" id="announcementContainer">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="col-lg-4 col-md-6 col-sm-12 mb-4 d-flex">
                                    <div class="announcement-card p-3 shadow-sm rounded flex-fill d-flex flex-column">
                                        <h5 class="announcement-title"><?= htmlspecialchars($row['title']) ?></h5>
                                        <p class="announcement-description flex-grow-1">
                                            <?= nl2br(htmlspecialchars(substr($row['content'], 0, 100))) ?>...
                                        </p>
                                        <p class="announcement-date">
                                            <span class="fw-bold">Drafted on:</span> <?= date("F j, Y - g:i A", strtotime($row['created_at'])) ?>
                                        </p>
                                        <div class="btn-container d-flex flex-wrap gap-2">
                                            <button class="btn btn-primary flex-grow-1" onclick="editAnnouncement(<?= $row['id'] ?>, `<?= htmlspecialchars($row['content']) ?>`)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>

                                            <button class="btn btn-danger flex-grow-1" onclick="deleteDraft(<?= $row['id'] ?>, '<?= htmlspecialchars($row['title']) ?>')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>

                                            <button class="btn btn-info flex-grow-1" onclick="showFullAnnouncement(`<?= htmlspecialchars(addslashes($row['title'])) ?>`, `<?= htmlspecialchars(addslashes($row['content'])) ?>`)">
                                                <i class="fas fa-eye"></i> Read More
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center">No draft announcements found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <nav class="pagination-nav" style="transform: translateX(-10%);"> <!-- Adjust the position by 30% to the left -->
                <ul class="pagination justify-content-end flex-wrap mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= max($page - 1, 1) ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= min($page + 1, $totalPages) ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Edit Draft Modal -->
        <div id="editModal" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-xl"> <!-- Added 'modal-lg' for larger size -->
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Draft</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <input type="hidden" id="editId">
                            <div class="mb-3">
                                <textarea id="editContent" class="form-control" style="height: 500px; font-size: 1.1rem;"></textarea>
                                <!-- Increased height and font size -->
                            </div>
                            <button type="button" class="btn btn-success" onclick="saveEditedDraft()">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Read More Modal -->
        <div id="readMoreModal" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle"><?= htmlspecialchars($title) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="modalContent"><?= nl2br(htmlspecialchars($content)) ?></p>
                    </div>
                    <div id="toastContainer"></div>
                    <div class="modal-footer">
                        <div class="mb-3" id="smsGatewayInput" style="display: none;">
                            <label for="smsGatewayIP" class="form-label fw-bold">SMS Gateway IP Address:</label>
                            <input type="text" class="form-control" id="smsGatewayIP" placeholder="Enter SMS Gateway IP">
                        </div>
                        <label><input type="checkbox" id="sendSMS" class="send-option"> Send via SMS</label>
                        <label><input type="checkbox" id="sendEmail" class="send-option"> Send via Email</label>
                        <button type="button" class="btn btn-success" onclick="postAnnouncement()">Post</button>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#audienceModal">Recipient</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
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
                                <input type="checkbox" id="course5" name="course" value="Accounting Information Systems"> <label for="course5">(BSAIS)<b>Accounting Information Systems</b></label><br>
                                <input type="checkbox" id="course6" name="course" value="Office Administration"> <label for="course6">(BSOA)<b>Office Administration</b></label><br>
                                <input type="checkbox" id="course7" name="course" value="Technical Teacher Education"> <label for="course7">(BTVTEd)<b>Technical-Vocational Teacher Education</b></label><br>
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
        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this draft?</p>
                    </div>
                    <div class="modal-footer">
                        <a href="#" id="confirmDeleteBtn" class="btn btn-success">
                            <i class="fas fa-trash"></i>
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>


        <script>
            function showFullAnnouncement(title, content) {
                document.getElementById("modalTitle").innerText = title;
                document.getElementById("modalContent").innerText = content;

                var myModal = new bootstrap.Modal(document.getElementById("readMoreModal"));
                myModal.show();
            }

            function editAnnouncement(id, content) {
                document.getElementById('editId').value = id;
                document.getElementById('editContent').value = content;
                var myModal = new bootstrap.Modal(document.getElementById('editModal'));
                myModal.show();
            }


            function deleteDraft(id, title) {
                // Show the confirmation modal and display the title
                const deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));
                deleteModal.show();

                // Update the confirmation message with the title of the draft
                document.querySelector('#deleteModal .modal-body p').innerHTML = `Are you sure you want to delete the draft <b>Titled: "${title}"</b>?`;

                // Handle the confirmation of the deletion
                const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");

                confirmDeleteBtn.onclick = function() {
                    // Proceed with deleting the draft after confirmation
                    let formData = new FormData();
                    formData.append("id", id);

                    fetch("delete_draft.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Reload the page after successful deletion
                                location.reload(); // This will reload the page and the deleted draft will be gone

                                // Alternatively, you can also remove the draft card directly from the DOM before reloading
                                // const card = document.querySelector(`[data-id="${id}"]`);
                                // if (card) {
                                //     card.remove();
                                // }

                                // Show toast message (optional)
                                const toastEl = document.getElementById("deleteToast");
                                const toastMessage = document.getElementById("toastMessage");
                                toastMessage.textContent = data.message; // Set message
                                const toast = new bootstrap.Toast(toastEl);
                                toast.show();
                            } else {
                                console.error("Error:", data.message);
                            }
                        })
                        .catch(error => {
                            console.error("Error deleting draft:", error);
                        });

                    // Close the modal
                    deleteModal.hide();
                };
            }



            function saveEditedDraft() {
                const draftId = document.getElementById('editId').value;
                const editedContent = document.getElementById('editContent').value;

                if (!editedContent.trim()) {
                    alert('Content cannot be empty!');
                    return;
                }

                let formData = new FormData();
                formData.append("id", draftId);
                formData.append("content", editedContent);

                fetch("edit_draft.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update toast message dynamically
                            document.getElementById("toastMessage").innerText = "Draft updated successfully!";

                            // Show toast
                            const toastEl = document.getElementById("editToast");
                            const toast = new bootstrap.Toast(toastEl);
                            toast.show();

                            // Close modal
                            const editModalEl = document.getElementById('editModal');
                            const editModal = bootstrap.Modal.getInstance(editModalEl);
                            editModal.hide();

                            // Reload after toast is shown (1.5s delay)
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            console.error("Error:", data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Error editing draft:", error);
                    });
            }
            document.getElementById('selectAllBtn').addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('input[type="checkbox"]');
                const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
                checkboxes.forEach(checkbox => checkbox.checked = !allChecked);
                this.textContent = allChecked ? 'Select All' : 'Deselect All';
            });

            document.getElementById("sendSMS").addEventListener("change", function() {
                let smsGatewayInput = document.getElementById("smsGatewayInput");
                if (this.checked) {
                    smsGatewayInput.style.display = "block";
                } else {
                    smsGatewayInput.style.display = "none";
                }
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
                toast.style.maxWidth = "600px";
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

            function showFullAnnouncement(title, content) {
                document.getElementById("modalTitle").innerText = title;
                document.getElementById("modalContent").innerHTML = content.replace(/\n/g, "<br>");
                var myModal = new bootstrap.Modal(document.getElementById("readMoreModal"));
                myModal.show();
            }


            function postAnnouncement(event) {
                if (event) event.preventDefault(); // Prevent page reload

                let title = document.getElementById("modalTitle").innerText.trim();
                let announcementText = document.getElementById("modalContent").innerText.trim();
                let sendSMS = document.getElementById("sendSMS").checked ? 1 : 0;
                let sendEmail = document.getElementById("sendEmail").checked ? 1 : 0;
                let smsGatewayIP = document.getElementById("smsGatewayIP").value.trim();

                let selectedYears = Array.from(document.querySelectorAll("input[name='year']:checked")).map(cb => cb.value);
                let selectedCourses = Array.from(document.querySelectorAll("input[name='course']:checked")).map(cb => cb.value);


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

                                    // ✅ Save recipients in the database AFTER announcement is posted
                                    fetch("save_recipients.php", {
                                            method: "POST",
                                            headers: {
                                                "Content-Type": "application/json"
                                            },
                                            body: JSON.stringify({
                                                title: title,
                                                announcement: announcementText,
                                                years: selectedYears,
                                                courses: selectedCourses,
                                                recipientCount: recipientCount
                                            })
                                        })
                                        .then(recipientResponse => recipientResponse.json())
                                        .then(recipientData => {
                                            if (recipientData.success) {
                                                showToast("✅ Recipients saved successfully!", "success");
                                            } else {
                                                showToast("❌ Failed to save recipients: " + recipientData.message, "danger");
                                            }
                                        })
                                        .catch(error => {
                                            console.error("Error saving recipients:", error);
                                            showToast("❌ Failed to save recipients. Please try again.", "danger");
                                        });

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
                    alert("⚠️ Please select at least one Year and one Course!");
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

                        // ✅ Close Modal After Processing
                        closeAudienceModal();
                    })
                    .catch(error => {
                        console.error("❌ Fetch error:", error);
                        showToast("❌ Something went wrong. Please try again.", "danger");
                        closeAudienceModal();
                    });
            });

            // ✅ Function to Properly Close the Modal
            function closeAudienceModal() {
                let modalElement = document.getElementById("audienceModal");

                if (modalElement) {
                    let modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (!modalInstance) {
                        modalInstance = new bootstrap.Modal(modalElement); // Ensure instance exists
                    }
                    modalInstance.hide();

                    // ✅ Remove any stuck Bootstrap modal backdrops
                    setTimeout(() => {
                        modalElement.classList.remove("show");
                        modalElement.style.display = "none";
                        modalElement.setAttribute("aria-hidden", "true");

                        document.body.classList.remove("modal-open"); // Remove Bootstrap modal open class

                        let backdrop = document.querySelector(".modal-backdrop");
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }, 300);
                }
            }
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $conn->close(); ?>