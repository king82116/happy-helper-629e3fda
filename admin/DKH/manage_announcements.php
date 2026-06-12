<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:../index.php?msg=unauthorized");
    exit();
}

include '../conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $title = $conn->real_escape_string($_POST["title"]);
    $siteMessage = $conn->real_escape_string($_POST["siteMessage"]);
    $sort = (int) $_POST["sort"];
    $addtime = date("Y-m-d H:i:s");

    $sql = "INSERT INTO fazi_ann (title, siteMessage, sort, addtime) VALUES ('$title', '$siteMessage', '$sort', '$addtime')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $id = (int) $_POST["id"];
    $sql = "DELETE FROM fazi_ann WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    exit();
}

$announcements = $conn->query("SELECT * FROM fazi_ann ORDER BY sort ASC");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="stylesheet" href="../css/mobile-responsive.css">
    <title>Manage Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
</head>

<body class="container py-4">

    <h2 class="text-center mb-4">Manage Announcements</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Add Announcement</button>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Title</th>
                <th>Site Message</th>
                <th>Sort</th>
                <th>Added Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="announcementTable">
            <?php while ($row = $announcements->fetch_assoc()): ?>
                <tr id="row-<?= $row['id'] ?>">
                    <td><?= htmlspecialchars($row["title"]) ?></td>
                    <td><?= htmlspecialchars($row["siteMessage"]) ?></td>
                    <td><?= $row["sort"] ?></td>
                    <td><?= $row["addtime"] ?></td>
                    <td>
                        <button class="btn btn-danger btn-sm"
                            onclick="deleteAnnouncement(<?= $row['id'] ?>)">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Add Announcement Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        <input type="hidden" name="add" value="1">
                        <label>Title:</label>
                        <input type="text" name="title" class="form-control" required>

                        <label>Site Message:</label>
                        <textarea name="siteMessage" id="editor"></textarea> <!-- Removed required from textarea -->

                        <label>Sort Order:</label>
                        <input type="number" name="sort" class="form-control" required>

                        <button type="submit" class="btn btn-primary mt-3">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let siteMessageEditor;

        ClassicEditor.create(document.querySelector('#editor'), {
            ckfinder: { uploadUrl: 'https://josh296689_max.com/anu/upload.php' }
        }).then(editor => {
            siteMessageEditor = editor;
        }).catch(error => console.error(error));


        document.getElementById("addForm").addEventListener("submit", function (event) {
            event.preventDefault();

            document.querySelector('textarea[name="siteMessage"]').value = siteMessageEditor.getData();

            let formData = new FormData(this);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        alert("Announcement Added Successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("AJAX Error:", error);
                    alert("Something went wrong!");
                });
        });

        // Handle Delete Announcement (AJAX)
        function deleteAnnouncement(id) {
            if (confirm("Are you sure you want to delete this announcement?")) {
                fetch(window.location.href, {
                    method: 'POST',
                    body: new URLSearchParams({ delete: 1, id: id }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            document.getElementById("row-" + id).remove();
                        } else {
                            alert("Error: " + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Delete AJAX Error:", error);
                    });
            }
        }
    </script>

</body>

</html>