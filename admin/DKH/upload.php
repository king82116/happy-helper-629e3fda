<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

$uploadDir = '../uploads/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
    die(json_encode(['error' => 'Upload failed']));
}

$fileType = mime_content_type($_FILES['upload']['tmp_name']);
if (!in_array($fileType, $allowedTypes)) {
    die(json_encode(['error' => 'Invalid file type']));
}

$fileName = uniqid() . "_" . basename($_FILES['upload']['name']);
$filePath = $uploadDir . $fileName;

if (move_uploaded_file($_FILES['upload']['tmp_name'], $filePath)) {
    echo json_encode(['url' => "/uploads/$fileName"]);
} else {
    echo json_encode(['error' => 'Could not save file']);
}
?>
