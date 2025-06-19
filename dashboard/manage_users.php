<?php
session_start();
include "../database.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="icon" type="image/png" sizes="712x712" href="../images/SPA AI.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css">
    <!-- FontAwesome (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        .user-management-container {
            background: linear-gradient(to bottom, #ece9e6, #ffffff);
            border: 2px solid #2a9d8f;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .container {
            border: 2px solid #2a9d8f;
            /* Blue border color */
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
            background: linear-gradient(to bottom, #ece9e6, #ffffff);
            /* Slight transparency */
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

        .upload-form-container {
            background: linear-gradient(to bottom, #ece9e6, #ffffff);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            margin: 0 auto;
            display: none;
            border: 2px solid #2a9d8f;
            /* Hidden initially */
            transition: opacity 0.3s ease-in-out;
        }

        /* Style for the input field (file upload) */
        .upload-form-container input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        /* Style for the submit button */
        .upload-form-container .btn-submit {
            background-color: #28a745;
            color: black;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        /* Hover effect for the submit button */
        .upload-form-container .btn-submit:hover {
            background-color: #218838;
        }

        /* Styling for the icon inside the submit button */
        .upload-form-container .btn-submit i {
            margin-right: 10px;
        }

        /* Optional transition for fade-in/out effect */
        .upload-form-container.show {
            display: block;
            opacity: 1;
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
            /* zoom-in effect */
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
                    <i class="fas fa-user me-2"></i>Manage Users
                </h2>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="d-flex justify-content-end mb-4">
                <div class="dropdown" style="margin-right: 37px;">
                    <button class="btn btn-primary dropdown-toggle fw-bold" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cogs"></i> Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li>
                            <a class="dropdown-item" href="Template/Template Data.xlsx" download>
                                <i class="fas fa-file-download"></i> <b>Download Template</b>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" id="showFormButton">
                                <i class="fas fa-plus"></i><b> Add New Admin</b>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" id="showExcelUpload">
                                <i class="fas fa-file-upload"></i><b> Import File</b>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="export_student.php">
                                <i class="fas fa-file-excel"></i> <b>Export Student Data</b>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="backup.php">
                                <i class="fas fa-database"></i> <b>Back Up Database</b>
                            </a>
                        </li>
                    </ul>
                    </ul>
                </div>
            </div>


            <div id="excelUploadForm" class="upload-form-container" style="display: none;">
                <form action="upload_excel.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="excelFile" class="form-label"><b>Select Excel File:</b></label>
                        <input type="file" class="form-control" name="excelFile" id="excelFile" accept=".xlsx, .xls" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Import File
                    </button>
                    <button type="button" class="btn btn-danger btn-md" id="cancelExcelButton">Close</button>
                </form>
            </div>


            <div class="user-management-container" id="userForm" style="display: none;">
                <h2 class="text-center">Add New Admin</h2>
                <div id="message-container"></div> <!-- Message will appear here -->
                <form id="addUserForm" method="POST" enctype="multipart/form-data" action="add_user.php">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 position-relative">
                            <label for="firstName" class="form-label"><b>First Name</b></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="lastName" class="form-label"><b>Last Name</b></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 position-relative">
                            <label for="contactNumber" class="form-label"><b>Contact Number</b></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control" id="contactNumber" name="contactNumber" required
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="11">
                            </div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="email" class="form-label"><b>Email</b></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label"><b>Role</b></label>
                        <select class="form-select" name="role" id="role" required>
                            <option value="" selected disabled>Select Role</option>
                            <option value="Admin">Admin</option>
                            <option value="Teacher">Teacher</option>
                        </select>
                    </div>
                    <div class="col-mb-3 position-relative">
                        <label for="profilePicture" class="form-label"><b>Profile Picture</b></label>
                        <input type="file" class="form-control" id="profilePicture" name="profilePicture" accept="image/*" required>
                    </div>


                    <div class="col-mb-3 position-relative">
                        <label for="userName" class="form-label"><b>Username</b></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                            <input type="text" class="form-control" id="userName" name="userName" required>
                        </div>
                    </div>
                    <div class="col-mb-3 position-relative">
                        <label for="password" class="form-label"><b>Password</b></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;">
                                <i class="fas fa-eye-slash" id="toggleEye"></i>
                            </span>
                        </div>
                    </div>
                    <br>
                    <div class="text-left">
                        <button type="submit" class="btn btn-success btn-md">Add User</button>
                        <button type="button" class="btn btn-danger btn-md" id="cancelFormButton">Close</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="container">
            <?php
            if (isset($_SESSION['message'])):
            ?>
                <div class="alert alert-<?php echo isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : 'info'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php
                unset($_SESSION['message']); // Remove message after displaying
                unset($_SESSION['msg_type']); // Remove type to avoid repeated alerts
            endif;
            ?>
            <h3 class="fs-4 mb-3">Registered Users</h3>
            <div class="table-responsive">
                <table id="example" class="table table-striped nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>#id</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Year</th>
                            <th>Course</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include "../database.php"; // Include the database connection

                        $sql = "SELECT * FROM members"; // Ensure your table name is correct
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><strong>" . htmlspecialchars($row['id']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['year']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['course']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                                echo "<td>";
                                if (strtolower($row['status']) === 'active') {
                                    echo "<span class='badge bg-success'>Active</span>";
                                } else {
                                    echo "<span class='badge bg-danger'>Inactive</span>";
                                }
                                echo "</td>";
                                echo "<td>
                                
                                <button class='btn btn-primary btn-sm edit-btn' data-bs-toggle='modal' data-bs-target='#editUserModal' 
                                    data-id='" . $row['id'] . "' 
                                    data-firstname='" . $row['firstName'] . "'
                                    data-lastname='" . $row['lastName'] . "'
                                    data-year='" . $row['year'] . "'
                                    data-course='" . $row['course'] . "'
                                    data-email='" . $row['email'] . "'
                                    data-contact='" . $row['contact_number'] . "'
                                    data-role='" . $row['role'] . "'>
                                    <i class='fas fa-edit'></i>
                                </button>
                                <form method='POST' action='update_user.php' style='display:inline-block;'>
                <input type='hidden' name='user_id' value='" . $row['id'] . "'>
                <input type='hidden' name='new_status' value='" . (strtolower($row['status']) === 'active' ? 'Inactive' : 'Active') . "'>

                
                <button type='submit' class='btn btn-sm " .
                                    ($row['status'] === 'Active' ? 'btn-danger' : 'btn-success') . "'>
                    <i class='fas " . ($row['status'] === 'Active' ? 'fa-ban' : 'fa-check-circle') . "'></i>
                </button>
            </form>
                                
                                <button 
    class='btn btn-dark btn-sm open-delete-modal' 
    data-id='" . $row['id'] . "' 
    data-firstname='" . htmlspecialchars($row['firstName']) . "' 
    data-lastname='" . htmlspecialchars($row['lastName']) . "' 
    data-bs-toggle='modal' 
    data-bs-target='#deleteUserModal'>
    <i class='fas fa-trash'></i>
</button>
                              </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>No users found</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editUserForm" method="POST" action="edit_users.php">
                            <input type="hidden" id="editUserId" name="id">

                            <div class="mb-3">
                                <label for="editFirstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="editFirstName" name="firstName" required>
                            </div>
                            <div class="mb-3">
                                <label for="editLastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="editLastName" name="lastName" required>
                            </div>
                            <div class="mb-3">
                                <label for="editYear" class="form-label">Year</label>
                                <select class="form-select" id="editYear" name="year" required>
                                    <option value="First year">First Year</option>
                                    <option value="Second year">Second Year</option>
                                    <option value="Third year">Third Year</option>
                                    <option value="Fourth year">Fourth Year</option>
                                    <option value="Teacher">Teacher</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editCourse" class="form-label">Course</label>
                                <select class="form-select" id="editCourse" name="course" required>
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
                            <div class="mb-3">
                                <label for="editEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="editEmail" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="editContact" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="editContact" name="contactNumber" required>
                            </div>
                            <div class="mb-3">
                                <label for="editRole" class="form-label">Role</label>
                                <select class="form-select" id="editRole" name="role" required>
                                    <option value="Teacher">Teacher</option>
                                    <option value="Student">Student</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success">Update User</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteUserModalLabel">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="deleteUserMessage">Are you sure you want to delete this user?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                        <a href="" id="confirmDeleteBtn" class="btn btn-success">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        </div>



        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <!-- Bootstrap Bundle JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>

        <script>
            $(document).ready(function() {
                // Initialize DataTables
                $('#example').DataTable();

                // Show/hide add user form
                $("#showFormButton").click(function() {
                    $("#userForm").fadeIn();
                });

                $("#cancelFormButton").click(function() {
                    $("#userForm").fadeOut();
                });
            });

            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll(".edit-btn").forEach(button => {
                    button.addEventListener("click", function() {
                        document.getElementById("editUserId").value = this.getAttribute("data-id");
                        document.getElementById("editFirstName").value = this.getAttribute("data-firstname");
                        document.getElementById("editLastName").value = this.getAttribute("data-lastname");
                        document.getElementById("editYear").value = this.getAttribute("data-year");
                        document.getElementById("editCourse").value = this.getAttribute("data-course");
                        document.getElementById("editEmail").value = this.getAttribute("data-email");
                        document.getElementById("editContact").value = this.getAttribute("data-contact");
                        document.getElementById("editRole").value = this.getAttribute("data-role");
                    });
                });

                document.querySelectorAll(".delete-btn").forEach(button => {
                    button.addEventListener("click", function() {
                        // Get the user ID from the button's data attribute
                        let userId = this.getAttribute("data-id");
                        // Store the userId in the confirm button's data attribute
                        document.getElementById("confirmDeleteBtn").setAttribute("data-id", userId);
                        // Show the modal using Bootstrap's modal plugin
                        let deleteModal = new bootstrap.Modal(document.getElementById("deleteConfirmationModal"));
                        deleteModal.show();
                    });
                });

                // When confirm button is clicked, redirect to the delete URL
                document.querySelectorAll(".delete-btn").forEach(button => {
                    button.addEventListener("click", function() {
                        let userId = this.getAttribute("data-id");
                        let deleteBtn = document.getElementById("confirmDeleteBtn");

                        if (deleteBtn) {
                            deleteBtn.setAttribute("href", "delete_users.php?id=" + userId);
                        }

                        // Show modal with the correct ID
                        let deleteModal = new bootstrap.Modal(document.getElementById("deleteUserModal"));
                        deleteModal.show();
                    });
                });
                document.getElementById("toggleEye").addEventListener("click", function() {
                    let passwordInput = document.getElementById("password");
                    let eyeIcon = document.getElementById("toggleEye");

                    if (passwordInput.type === "password") {
                        passwordInput.type = "text";
                        eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
                    } else {
                        passwordInput.type = "password";
                        eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
                    }
                });
                $("#addUserForm").submit(function(event) {
                    event.preventDefault(); // Prevent form from reloading the page

                    $.ajax({
                        url: "add_user.php", // Path to PHP script
                        type: "POST",
                        data: $(this).serialize(), // Serialize form data
                        dataType: "json",
                        success: function(response) {
                            if (response.status === "success") {
                                $("#message-container").html(
                                    '<div class="alert alert-success text-center">' + response.message + "</div>"
                                );
                                setTimeout(function() {
                                    location.reload(); // Reload the page to update the database table
                                }, 1500); // Wait 1.5 seconds before reloading
                            } else {
                                $("#message-container").html(
                                    '<div class="alert alert-danger text-center">' + response.message + "</div>"
                                );
                            }
                        },
                        error: function() {
                            $("#message-container").html(
                                '<div class="alert alert-danger text-center">An error occurred. Please try again.</div>'
                            );
                        },
                    });
                });

                let table = $('#example').DataTable({
                    "responsive": true, // Keeps table responsive
                    "autoWidth": false, // Prevents unwanted resizing
                    "destroy": false, // Prevents re-initialization issues
                    "ordering": true, // Enables sorting
                    "paging": true, // Enables pagination
                    "info": true, // Shows table info
                });

            });
            document.addEventListener("DOMContentLoaded", function() {
                const showBtn = document.getElementById("showExcelUpload");
                const cancelBtn = document.getElementById("cancelExcelButton");
                const formContainer = document.getElementById("excelUploadForm");

                // Show the upload form
                showBtn.addEventListener("click", function() {
                    formContainer.style.display = "block";
                });

                // Close the upload form
                cancelBtn.addEventListener("click", function() {
                    formContainer.style.display = "none";
                });
            });
            document.querySelectorAll('.open-delete-modal').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-id');
                    const firstName = this.getAttribute('data-firstname');
                    const lastName = this.getAttribute('data-lastname');
                    const fullName = `${firstName} ${lastName}`;

                    // Bold the name with <strong>
                    document.getElementById('deleteUserMessage').innerHTML =
                        `Are you sure you want to delete <strong>${fullName}</strong>?`;

                    // Set delete link
                    document.getElementById('confirmDeleteBtn').href =
                        `delete_users.php?id=${userId}&firstName=${encodeURIComponent(firstName)}&lastName=${encodeURIComponent(lastName)}`;
                });
            });
        </script>
</body>

</html>