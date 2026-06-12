<?php
header("Content-Type: application/json");

// Database connection
$conn = new mysqli("localhost", "josh296689_max", "josh296689_max", "josh296689_max");

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Input sanitization
$account_id = filter_input(INPUT_GET, 'account_id', FILTER_SANITIZE_STRING);
$issue_type = filter_input(INPUT_GET, 'issue_type', FILTER_SANITIZE_STRING);

if (empty($account_id)) {
    echo json_encode(["error" => "Account ID is required."]);
    exit;
}

// Prepare SQL query
$sql = "SELECT id, issue_type, withdrawal_amount, amount_deposit, status, created_at FROM issues WHERE account = ?";
if (!empty($issue_type)) {
    $sql .= " AND issue_type = ?";
}

$stmt = $conn->prepare($sql);

if (!empty($issue_type)) {
    $stmt->bind_param("ss", $account_id, $issue_type);
} else {
    $stmt->bind_param("s", $account_id);
}

$stmt->execute();
$result = $stmt->get_result();

$issues = [];
while ($row = $result->fetch_assoc()) {
    $issues[] = $row;
}

$stmt->close();
$conn->close();

// Return results as JSON
echo json_encode($issues);
?>