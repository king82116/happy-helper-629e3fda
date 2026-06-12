<?php
// Always start the session at the very top.
session_start();
include("conn.php"); // Include the database connection file.

// --- SINGLE-PAGE AJAX HANDLER ---
// Check if the request is a POST request (from our modal form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Set header to return JSON response
  header('Content-Type: application/json');

  // Security: Re-check session for AJAX requests
  if (!isset($_SESSION['unohs'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in again.']);
    exit;
  }

  // Get the data from the form
  $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
  $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

  if ($id > 0) {
    // --- SECURITY: USE PREPARED STATEMENTS TO PREVENT SQL INJECTION ---
    // The query updates the remarks and sets the status to 1 (e.g., "Responded")
    $stmt = $conn->prepare("UPDATE your_table SET remarks = ?, status = 1 WHERE id = ?");

    // Check if the statement was prepared successfully
    if ($stmt === false) {
      echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement.']);
      exit;
    }

    // Bind the parameters (s = string, i = integer)
    $stmt->bind_param("si", $remarks, $id);

    // Execute the query and check for success
    if ($stmt->execute()) {
      echo json_encode(['success' => true, 'message' => 'Remarks updated successfully!']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to update remarks.']);
    }
    $stmt->close();
  } else {
    echo json_encode(['success' => false, 'message' => 'Invalid ID provided.']);
  }

  // Stop script execution after handling the AJAX request
  exit;
}
// --- END OF AJAX HANDLER ---


// --- PAGE LOAD LOGIC (GET request) ---
// Security: More robust session check. Redirect if not logged in.
if (!isset($_SESSION['unohs'])) {
  header("location:index.php?msg=unauthorized");
  exit; // Always exit after a header redirect
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Game Problems - Dashboard</title>

  <!-- Vendor CSS -->
  <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="vendors/feather/feather.css">
  <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
  <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="css/style.css">
  <link rel="shortcut icon" href="images/favicon.png" />

  <style>
    /* --- CUSTOM YELLOW THEME --- */
    :root {
      --brand-yellow: #ffc107;
      /* A vibrant, pleasant yellow */
      --brand-dark: #2c3034;
      /* A complementary dark color */
      --brand-dark-hover: #1e2124;
    }

    /* Navbar and Sidebar */
    .navbar,
    .sidebar {
      background: var(--brand-dark);
    }

    .navbar .navbar-brand-wrapper {
      background: var(--brand-dark);
    }

    .sidebar .nav .nav-item.active>.nav-link {
      background: var(--brand-yellow);
      color: var(--brand-dark-hover);
    }

    .sidebar .nav .nav-item:hover>.nav-link {
      background: var(--brand-dark-hover);
    }

    .sidebar .user-profile .user-name,
    .sidebar .user-profile .user-designation {
      color: #fff;
    }

    /* Primary Button */
    .btn-primary {
      background-color: var(--brand-yellow);
      border-color: var(--brand-yellow);
      color: #000;
    }

    .btn-primary:hover {
      background-color: #e0a800;
      /* Darker yellow on hover */
      border-color: #e0a800;
      color: #000;
    }

    /* Page Title and Table Header */
    .font-weight-bold.text-dark {
      color: var(--brand-dark) !important;
    }

    #yourTable thead {
      background-color: var(--brand-dark);
      color: var(--brand-yellow);
    }

    /* Modal Header */
    .modal-header {
      background-color: var(--brand-dark);
      color: #fff;
    }

    .modal-header .btn-close {
      filter: invert(1) grayscale(100%) brightness(200%);
    }

    /* Fixing z-index for DataTables search box */
    .dataTables_filter {
      padding-bottom: 20px;
    }
  </style>
</head>

<body>
  <div class="container-scroller">
    <!-- Navbar -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="dashboard.php"><img src="images/logo.png" alt="logo" /></a>
        <a class="navbar-brand brand-logo-mini" href="dashboard.php"><img src="images/logo-mini.png" alt="logo" /></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>
        <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item dropdown d-flex mr-4">
            <a class="nav-link count-indicator dropdown-toggle d-flex align-items-center justify-content-center"
              id="notificationDropdown" href="#" data-toggle="dropdown">
              <i class="icon-cog"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list"
              aria-labelledby="notificationDropdown">
              <p class="mb-0 font-weight-normal float-left dropdown-header">Settings</p>
              <a class="dropdown-item preview-item" href="logout.php">
                <i class="icon-inbox"></i> Logout
              </a>
            </div>
          </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
          data-toggle="offcanvas">
          <span class="icon-menu"></span>
        </button>
      </div>
    </nav>

    <div class="container-fluid page-body-wrapper">
      <!-- Sidebar -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <div class="user-profile">
          <div class="user-image">
            <img src="images/faces/face28.png">
          </div>
          <div class="user-name">Prime Tech</div>
          <div class="user-designation">Admin</div>
        </div>
        <?php include 'compass.php'; ?>
      </nav>

      <!-- Main Content -->
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-sm-12 mb-4 mb-xl-0">
              <h4 class="font-weight-bold text-dark">Game Problems</h4>
              <p>List of unresolved game problems from users.</p>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <div class="card">
                <div class="card-body">
                  <div class="table-responsive">
                    <table id="yourTable" class="table table-striped table-hover">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>User ID</th>
                          <th>Order No</th>
                          <th>User Problem</th>
                          <th>Image</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        // BUG FIX: Added `file_upload` to the SELECT statement.
                        // SECURITY: Switched to prepared statements.
                        $sql = "SELECT id, userid, deposit_order_no, text_content, remarks, file_upload 
                                FROM your_table 
                                WHERE prob = 'Game Problems' AND status = 2";

                        $stmt = $conn->prepare($sql);

                        if ($stmt) {
                          $stmt->execute();
                          $result = $stmt->get_result();

                          if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                              // SECURITY: Sanitize all output with htmlspecialchars() to prevent XSS attacks.
                              $id = htmlspecialchars($row["id"]);
                              $userid = htmlspecialchars($row["userid"]);
                              $deposit_order_no = htmlspecialchars($row["deposit_order_no"]);
                              $text_content = htmlspecialchars($row["text_content"]);
                              $remarks = htmlspecialchars($row["remarks"]); // Sanitize remarks for data attribute
                              $file_upload = htmlspecialchars($row["file_upload"]);

                              echo "<tr>";
                              echo "<td>{$id}</td>";
                              echo "<td>{$userid}</td>";
                              echo "<td>{$deposit_order_no}</td>";
                              echo "<td>{$text_content}</td>";
                              // Make the link clickable and open in a new tab. Check if file exists.
                              if (!empty($file_upload)) {
                                echo "<td><a href='https://joshgame.online/uploads/{$file_upload}' target='_blank'>View Image</a></td>";
                              } else {
                                echo "<td>No Image</td>";
                              }
                              echo "<td><button class='btn btn-primary edit-btn' data-id='{$id}' data-remarks='{$remarks}' data-bs-toggle='modal' data-bs-target='#editModal'>Respond</button></td>";
                              echo "</tr>";
                            }
                          } else {
                            echo "<tr><td colspan='6' class='text-center'>No pending game problems found.</td></tr>";
                          }
                          $stmt->close();
                        } else {
                          // Error handling if statement preparation fails
                          echo "<tr><td colspan='6' class='text-center text-danger'>Error preparing database query.</td></tr>";
                        }
                        $conn->close();
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal for editing remarks -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Send Response to User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="editForm">
                  <input type="hidden" id="editId" name="id">
                  <div class="mb-3">
                    <label for="editRemarks" class="form-label">Your Response / Remarks:</label>
                    <textarea class="form-control" id="editRemarks" name="remarks" rows="4"
                      placeholder="Enter your response here. This will be visible to the user."></textarea>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save and Mark as Responded</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © Prime Tech
              2025</span>
          </div>
        </footer>
      </div>
    </div>
  </div>

  <!-- Core JS -->
  <script src="vendors/base/vendor.bundle.base.js"></script>
  <!-- Bootstrap 5 for modern modal functionality -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Page-specific JS -->
  <script src="js/off-canvas.js"></script>
  <script src="js/hoverable-collapse.js"></script>
  <script src="js/template.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

  <script>
    $(document).ready(function () {
      // Initialize DataTables for better sorting, searching, and pagination.
      $('#yourTable').DataTable();

      // Use event delegation for the edit button click.
      // This is more efficient and works for dynamically added rows.
      $('#yourTable').on('click', '.edit-btn', function () {
        const id = $(this).data('id');
        const remarks = $(this).data('remarks');

        // Populate the modal form fields
        $('#editId').val(id);
        $('#editRemarks').val(remarks);

        // The modal is now opened via data-bs-toggle attributes in the HTML button
      });

      // Handle the form submission via AJAX
      $('#editForm').submit(function (e) {
        e.preventDefault(); // Prevent the default form submission
        const formData = $(this).serialize(); // Serialize form data for POST

        $.ajax({
          type: 'POST',
          url: '', // Post to the same page
          data: formData,
          dataType: 'json', // Expect a JSON response from the server
          success: function (response) {
            // Check the 'success' flag from our PHP script
            if (response.success) {
              alert(response.message); // Show success message
              location.reload(); // Reload the page to see the changes
            } else {
              alert('Error: ' + response.message); // Show error message
            }
          },
          error: function (xhr, status, error) {
            // Handle AJAX errors (e.g., server down, 404)
            console.error("AJAX Error:", status, error);
            alert('An unexpected error occurred. Please try again.');
          }
        });
      });
    });
  </script>
</body>

</html>