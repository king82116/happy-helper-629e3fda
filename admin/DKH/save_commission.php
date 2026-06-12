<?php
header('Content-Type: application/json');
require '../conn.php'; // Include database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the necessary data is provided
    if (!isset($_POST['commission']) || !isset($_POST['userID'])) {
        echo json_encode(["success" => false, "message" => "Missing required parameters."]);
        exit;
    }

    $commission_amount = floatval($_POST['commission']); // Ensure it's a number
    $user_id = intval($_POST['userID']); // Ensure it's an integer

    if ($commission_amount <= 0 || $user_id <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid commission amount or user ID."]);
        exit;
    }

    try {
      

        // Step 1: Update motta in `shonu_kaichila`
        $update_query = $conn->prepare("UPDATE shonu_kaichila SET motta = motta + ? WHERE balakedara = ?");
        $update_query->bind_param("di", $commission_amount, $user_id);
        if (!$update_query->execute()) {
            throw new Exception("Failed to update shonu_kaichila: " . $update_query->error);
        }

        // Step 2: Insert record into `daily_awards_table`
        $timestamp = date('Y-m-d H:i:s');
        $remark = "daily salary";
        $processed = 1;

        $insert_query = $conn->prepare("
            INSERT INTO daily_awards_table (userkani, price, serial, shonu, remark, processed) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insert_query->bind_param("idissi", $user_id, $commission_amount, $user_id, $timestamp, $remark, $processed);
        if (!$insert_query->execute()) {
            throw new Exception("Failed to insert into daily_awards_table: " . $insert_query->error);
        }

        // Close statements and connection
        $update_query->close();
        $insert_query->close();
        $conn->close();

        echo json_encode(["success" => true, "message" => "Commission saved successfully."]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
