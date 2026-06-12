<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "josh296689_max", "josh296689_max", "josh296689_max");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$mobile = isset($_POST['mobile']) ? $conn->real_escape_string($_POST['mobile']) : null;

if ($mobile) {
    $query = "SELECT id FROM shonu_subjects WHERE mobile = '$mobile'";
    $result = $conn->query($query);

    if ($result) {
        $numRows = $result->num_rows;
        if ($numRows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "Record ID: " . $row['id'] . "<br>";
            }
        } else {
            echo "No records found.";
        }
    }
}

$issueType = $_POST['issue'] ?? null;
$account = $_POST['account'] ?? null;
$amountDeposit = $_POST['amountDeposit'] ?? null;
$utrNumber = $_POST['utrNumber'] ?? null;
$upiid = $_POST['upiid'] ?? null;
$withdrawAmount = $_POST['withdrawalAmount'] ?? null;

if (!$issueType || !$account) {
    die("Error: Required fields are missing.");
}

if ($withdrawAmount && (!is_numeric($withdrawAmount) || $withdrawAmount <= 0)) {
    die("Error: Invalid withdrawal amount.");
}
if ($amountDeposit && (!is_numeric($amountDeposit) || $amountDeposit <= 0)) {
    die("Error: Invalid deposit amount.");
}

$targetDir = "uploads/";
$depositProofPath = $screenshotPath = null;

function uploadFile($fileKey, $targetDir)
{
    if (!empty($_FILES[$fileKey]["name"])) {
        $fileName = basename($_FILES[$fileKey]["name"]);
        $fileName = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $fileName);
        $filePath = $targetDir . $fileName;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        if (move_uploaded_file($_FILES[$fileKey]["tmp_name"], $filePath)) {
            return $filePath;
        }
    }
    return null;
}

$depositProofPath = uploadFile("depositProof", $targetDir);
$screenshotPath = uploadFile("screenshot", $targetDir);

$query = "INSERT INTO issues (
            issue_type,
            account,
            amount_deposit,
            utr_number,
            upiid,
            withdrawal_amount,
            deposit_proof_path,
            screenshot_path
        ) VALUES (
            '$issueType',
            '$account',
            '$amountDeposit',
            '$utrNumber',
            '$upiid',
            '$withdrawAmount',
            '$depositProofPath',
            '$screenshotPath'
        )";

if ($conn->query($query)) {
    echo "Issue submitted successfully.";
} else {
    echo "Error: Unable to submit the issue. " . $conn->error;
}

$conn->close();
?>