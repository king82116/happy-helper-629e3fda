<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'josh296689_max');
define('DB_PASSWORD', 'josh296689_max');
define('DB_NAME', 'josh296689_max');

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    error_log('Database connection failed');
    echo 'Something Issue While Processing';
    exit();
}

// Debugging enabled
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function handleError($message)
{
    error_log("ERROR: " . $message);
    echo 'Something Issue While Processing';
    exit();
}

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
// -------- AUTH endpoint (token-based) --------
if ($method === 'POST' && strpos($request, '/auth') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);

    error_log("==== /auth endpoint hit ====");
    error_log("Raw input: " . json_encode($data));

    if (!isset($data['token'])) {
        error_log("Missing token in request.");
        handleError('Missing token');
    }

    $token = $data['token'];
    error_log("Token received: " . $token);

    // Step 1: Prepare the query to fetch 'id' from 'shonu_subjects' using 'token'
    $stmt1 = $conn->prepare("SELECT id FROM shonu_subjects WHERE token = ?");
    if (!$stmt1) {
        error_log("Prepare failed: " . $conn->error);
        handleError("DB error");
    }

    // Bind the 'token' parameter to the statement
    $stmt1->bind_param("s", $token); // Assuming $token contains the value you're searching for

    // Execute the first query to get the 'id'
    $stmt1->execute();
    $stmt1->bind_result($id);

    // Check if token exists and fetch the 'id'
    if (!$stmt1->fetch()) {
        error_log("Token not found: " . $token);
        handleError("Token not found");
    }

    // Close the first statement as it's no longer needed
    $stmt1->close();

    // Step 2: Prepare the query to fetch 'motta' from 'shonu_kaichila' using 'balakedara'
    $stmt = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        handleError("DB error");
    }

    // Bind the 'id' (balakedara) to the second query
    $stmt->bind_param("i", $id); // Assuming 'id' is an integer

    // Execute the second query
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $rowCount = $result->num_rows;
        error_log("Rows returned: " . $rowCount);

        // If results are found, return the response
        if ($rowCount > 0) {
            $row = $result->fetch_assoc();
            header('Content-Type: application/json');

            // Prepare the response in the format expected by Jili API
            $response = json_encode([
                "errorCode" => 0,            // Success code
                "message" => "success",     // Success message
                "username" => $token,        // Token as username
                "currency" => "INR",         // Hardcoded currency
                "balance" => floatval($row['motta']),  // Balance fetched from DB
                "token" => $token            // Token sent back in response
            ]);

            error_log("Auth success response: " . $response);
            echo $response;
        } else {
            // If no results, handle error
            error_log("User not found for token: " . $token);
            handleError('User not found');
        }
    } else {
        error_log("Execute failed: " . $stmt->error);
        handleError("DB error");
    }

    // Close the second statement
    $stmt->close();
    // Close the database connection
    $conn->close();
    exit();
}

// -------- fallback for other routes --------

// -------- GET endpoint to fetch user balance using token-based ID --------
elseif ($method === 'GET' && strpos($request, '/getUserBalance') !== false) {
    // Check if the userId (token) is provided in the request
    if (!isset($_GET['userId'])) {
        handleError("Missing userId");
    }

    $userId = $_GET['userId'];  // The token passed in as userId

    error_log("==== /getUserBalance endpoint hit ====");
    error_log("UserID (token) received: " . $userId);

    // Step 1: Fetch the 'id' from 'shonu_subjects' table using the provided token (userId)
    $stmt1 = $conn->prepare("SELECT id FROM shonu_subjects WHERE token = ?");
    if (!$stmt1) {
        error_log("Prepare failed: " . $conn->error);
        handleError("DB error");
    }

    // Bind the 'userId' (token) parameter to the first query
    $stmt1->bind_param("s", $userId);  // Assuming userId is a string (token)

    // Execute the query to get the 'id'
    $stmt1->execute();
    $stmt1->bind_result($id);

    // Check if the token exists and fetch the 'id'
    if (!$stmt1->fetch()) {
        error_log("Token not found: " . $userId);
        handleError("Token not found");
    }

    // Close the first statement
    $stmt1->close();

    // Step 2: Fetch 'motta' from 'shonu_kaichila' table using the fetched 'id' (balakedara)
    $stmt2 = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    if (!$stmt2) {
        error_log("Prepare failed: " . $conn->error);
        handleError("DB error");
    }

    // Bind the 'id' (balakedara) to the second query
    $stmt2->bind_param("i", $id);  // Assuming 'id' is an integer

    // Execute the second query to fetch the balance
    if ($stmt2->execute()) {
        $result = $stmt2->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            header('Content-Type: application/json');
            // Return the balance in the response
            echo json_encode(["balance" => floatval($row['motta'])]);
        } else {
            handleError("User not found");
        }
    } else {
        error_log("Execute failed: " . $stmt2->error);
        handleError("Query error");
    }

    // Close the second statement and database connection
    $stmt2->close();
    $conn->close();
    exit();
}
//-------------------XXXXXXXXXX---------------------------------------------//

// -------- POST endpoint for placing a bet --------
elseif ($method === 'POST' && strpos($request, '/bet') !== false) {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    //error_log("==== /bet endpoint hit ====");
    //error_log("📥 Raw input: " . $rawInput);

    // Required params from JiLi doc
    if (!isset($data['token'], $data['betAmount'], $data['winloseAmount'], $data['game'], $data['round'], $data['wagersTime'])) {
        handleError('Missing params in /bet');
    }


    $userId = $data['token'];
    $betAmount = round(floatval($data['betAmount']), 2);
    $winloseAmount = round(floatval($data['winloseAmount']), 2);
    $token = $data['token'];

    error_log("==== /bet endpoint hit ====");
    error_log("Payload: " . json_encode($data));

    // Step 1: Get internal user ID from token
    $stmt1 = $conn->prepare("SELECT id FROM shonu_subjects WHERE token = ?");
    $stmt1->bind_param("s", $userId);
    $stmt1->execute();
    $stmt1->bind_result($id);
    if (!$stmt1->fetch()) {
        $stmt1->close();
        handleError("Token not found");
    }
    $stmt1->close();
    error_log("User internal ID: $id");

    // Step 2: Get current balance
    $stmt2 = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    if ($result->num_rows === 0) {
        $stmt2->close();
        handleError("User not found in balance table");
    }
    $row = $result->fetch_assoc();
    $currentBalance = round(floatval($row['motta']), 2);
    $stmt2->close();

    error_log("Before Update - Balance: $currentBalance, Bet: $betAmount, Win/Loss: $winloseAmount");

    // Step 3: Fix win/lose if needed (as per doc)
    if ($winloseAmount > 0 && $winloseAmount < 1) {
        $winloseAmount = round($winloseAmount * 100, 2);
    }

    // Step 4: Balance logic (no preserve in /bet)
    $finalBalance = round(($currentBalance - $betAmount + $winloseAmount), 2);
    error_log("Updated Balance: $finalBalance");

    // Step 5: Update balance
    $stmt3 = $conn->prepare("UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?");
    if (!$stmt3) {
        error_log("Prepare failed (update): " . $conn->error);
        handleError("DB error");
    }

    $stmt3->bind_param("di", $finalBalance, $id);
    if (!$stmt3->execute()) {
        error_log("Update failed: " . $stmt3->error);
        handleError("Failed to update bet balance");
    }

    error_log("✅ Balance updated. Affected rows: " . $stmt3->affected_rows);
    $stmt3->close();

    // Final response (as per JiLi doc)
    header('Content-Type: application/json');
    echo json_encode([
        "errorCode" => 0,
        "message" => "success",
        "username" => $token,
        "balance" => $finalBalance,
        "currency" => "INR",
        "token" => $token
    ]);

    $conn->close();
    exit();
}


//-------------------XXXXXXXXXX---------------------------------------------//

// -------- POST endpoint for placing a session bet --------
elseif ($method === 'POST' && strpos($request, '/sessionBet') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['userId'], $data['betAmount'], $data['winloseAmount'], $data['sessionId'], $data['type'])) {
        handleError('Missing params in /sessionBet');
    }

    $userId = $data['userId'];
    $betAmount = round(floatval($data['betAmount']), 2);
    $winloseAmount = round(floatval($data['winloseAmount']), 2);
    $preserve = isset($data['preserve']) ? round(floatval($data['preserve']), 2) : 0.00;
    $type = intval($data['type']);

    error_log("==== /sessionBet endpoint hit ====");
    error_log("Payload: " . json_encode($data));
    $token = $data['token'];
    // Step 1: Get internal user ID from token
    $stmt1 = $conn->prepare("SELECT id FROM shonu_subjects WHERE token = ?");
    $stmt1->bind_param("s", $userId);
    $stmt1->execute();
    $stmt1->bind_result($id);
    if (!$stmt1->fetch()) {
        $stmt1->close();
        handleError("Token not found");
    }
    $stmt1->close();
    error_log("User internal ID: $id");

    // Step 2: Fetch current balance
    $stmt2 = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    if ($result->num_rows === 0) {
        $stmt2->close();
        handleError("User not found in balance table");
    }
    $row = $result->fetch_assoc();
    $currentBalance = round(floatval($row['motta']), 2);
    $stmt2->close();

    error_log("Before Update - Balance: $currentBalance, Bet: $betAmount, Win/Loss: $winloseAmount, Preserve: $preserve");

    // Step 3: Fix win/lose if needed
    if ($winloseAmount > 0 && $winloseAmount < 1) {
        $winloseAmount = round($winloseAmount * 100, 2);
    }

    // Step 4: Calculate final balance
    $finalBalance = $currentBalance;

    if ($preserve == 0 && $betAmount > 0 && $winloseAmount == 0) {
        $finalBalance -= $betAmount;
    } elseif ($preserve == 0 && $betAmount == 0 && $winloseAmount > 0) {
        $finalBalance += $winloseAmount;
    } elseif ($preserve > 0 && $betAmount == 0 && $winloseAmount == 0) {
        $finalBalance -= $preserve;
    } elseif ($preserve > 0 && $betAmount >= 0 && $winloseAmount >= 0) {
        $finalBalance += ($preserve - $betAmount + $winloseAmount);
    }

    $finalBalance = round($finalBalance, 2);
    error_log("Updated Balance: $finalBalance");

    // Step 5: Update balance
    $stmt3 = $conn->prepare("UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?");
    if (!$stmt3) {
        error_log("Prepare failed (update): " . $conn->error);
        handleError("DB error");
    }

    $stmt3->bind_param("di", $finalBalance, $id);
    if (!$stmt3->execute()) {
        error_log("Update failed: " . $stmt3->error);
        handleError("Failed to update session bet balance");
    }

    // Log if update did nothing
    if ($stmt3->affected_rows === 0) {
        error_log("⚠️ UPDATE executed but no rows were changed. Possibly same balance or silent fail.");
    } else {
        error_log("✅ Balance updated. Affected rows: " . $stmt3->affected_rows);
    }

    $stmt3->close();

    // Final response
    header('Content-Type: application/json');
    echo json_encode([
        "errorCode" => 0,
        "message" => "success",
        "username" => $token,
        "balance" => $finalBalance,
        "currency" => "INR",
        "token" => $token
    ]);

    $conn->close();
    exit();
}


// -------- POST endpoint for canceling a bet --------
elseif ($method === 'POST' && strpos($request, '/cancelBet') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if required parameters are provided
    if (!isset($data['userId'], $data['betAmount'], $data['round'])) {
        handleError('Missing params in /cancelBet');
    }

    $userId = $data['userId'];
    $betAmount = $data['betAmount'];

    error_log("==== /cancelBet endpoint hit ====");
    error_log("UserId (token) received: " . $userId);

    // Step 1: Fetch the 'id' from 'shonu_subjects' table using the provided userId (token)
    $stmt1 = $conn->prepare("SELECT id FROM shonu_subjects WHERE token = ?");
    if (!$stmt1) {
        error_log("Prepare failed: " . $conn->error);
        handleError("DB error");
    }

    // Bind the 'userId' (token) parameter to the first query
    $stmt1->bind_param("s", $userId);  // Assuming userId is a string (token)

    // Execute the query to get the 'id'
    $stmt1->execute();
    $stmt1->bind_result($id);

    // Check if the token exists and fetch the 'id'
    if (!$stmt1->fetch()) {
        error_log("Token not found: " . $userId);
        handleError("Token not found");
    }

    // Close the first statement
    $stmt1->close();

    // Step 2: Fetch the current balance ('motta') from 'shonu_kaichila' table using the fetched 'id' (balakedara)
    $stmt2 = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    if (!$stmt2) {
        error_log("Prepare failed: " . $conn->error);
        handleError("DB error");
    }

    // Bind the 'id' (balakedara) to the second query
    $stmt2->bind_param("i", $id);  // Assuming 'id' is an integer

    // Execute the query to fetch the current balance
    if ($stmt2->execute()) {
        $result = $stmt2->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currentBalance = $row['motta'];

            // Calculate the new balance after canceling the bet (revert the betAmount)
            $newBalance = $currentBalance + $betAmount;

            // Step 3: Update the balance in the 'shonu_kaichila' table
            $update = $conn->prepare("UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?");
            if (!$update) {
                error_log("Prepare failed: " . $conn->error);
                handleError("DB error");
            }

            // Bind parameters for the update query
            $update->bind_param("ds", $newBalance, $id);  // 'd' for double (new balance), 'i' for integer (id)
            if ($update->execute()) {
                // Return the new balance and message as a JSON response
                header('Content-Type: application/json');
                echo json_encode([
                    "username" => $token,
                    "balance" => $finalBalance,
                    "currency" => "INR",
                    "token" => $token
                ]);
            } else {
                handleError('Failed to update balance');
            }

            // Close the update statement
            $update->close();
        } else {
            handleError('User not found');
        }
    } else {
        handleError('Failed to fetch balance');
    }

    // Close the second statement
    $stmt2->close();

    // Close the database connection
    $conn->close();
    exit();
}
//-------------------XXXXXXXXXX---------------------------------------------//

// -------- POST endpoint for canceling a session bet --------
elseif ($method === 'POST' && strpos($request, '/cancelSessionBet') !== false) {
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if required parameters are provided
    if (!isset($data['userId'], $data['betAmount'], $data['sessionId'], $data['type'])) {
        handleError('Missing params in /cancelSessionBet');
    }

    $userId = $data['userId'];
    $betAmount = $data['betAmount'];

    error_log("==== /cancelSessionBet endpoint hit ====");
    error_log("UserId (token) received: " . $userId);

    // Step 1: Fetch the 'id' from 'shonu_subjects' table using the provided userId (token)
    $stmt1 = $conn->prepare("SELECT id FROM shonu_subjects WHERE token = ?");
    if (!$stmt1) {
        error_log("Prepare failed: " . $conn->error);
        handleError("DB error");
    }

    // Bind the 'userId' (token) parameter to the first query
    $stmt1->bind_param("s", $userId);  // Assuming userId is a string (token)

    // Execute the query to get the 'id'
    $stmt1->execute();
    $stmt1->bind_result($id);

    // Check if the token exists and fetch the 'id'
    if (!$stmt1->fetch()) {
        error_log("Token not found: " . $userId);
        handleError("Token not found");
    }

    // Close the first statement
    $stmt1->close();

    // Step 2: Fetch the current balance ('motta') from 'shonu_kaichila' table using the fetched 'id' (balakedara)
    $stmt2 = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    if (!$stmt2) {
        error_log("Prepare failed: " . $conn->error);
        handleError("DB error");
    }

    // Bind the 'id' (balakedara) to the second query
    $stmt2->bind_param("i", $id);  // Assuming 'id' is an integer

    // Execute the query to fetch the current balance
    if ($stmt2->execute()) {
        $result = $stmt2->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currentBalance = $row['motta'];

            // Calculate the new balance after canceling the session bet (revert the betAmount)
            $newBalance = $currentBalance + $betAmount;

            // Step 3: Update the balance in the 'shonu_kaichila' table
            $update = $conn->prepare("UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?");
            if (!$update) {
                error_log("Prepare failed: " . $conn->error);
                handleError("DB error");
            }

            // Bind parameters for the update query
            $update->bind_param("ds", $newBalance, $id);  // 'd' for double (new balance), 'i' for integer (id)
            if ($update->execute()) {
                // Return the new balance and message as a JSON response
                header('Content-Type: application/json');
                echo json_encode([
                    "username" => $token,
                    "balance" => $finalBalance,
                    "currency" => "INR",
                    "token" => $token
                ]);
            } else {
                handleError('Failed to update balance');
            }

            // Close the update statement
            $update->close();
        } else {
            handleError('User not found');
        }
    } else {
        handleError('Failed to fetch balance');
    }

    // Close the second statement
    $stmt2->close();

    // Close the database connection
    $conn->close();
    exit();
}
//-------------------XXXXXXXXXX---------------------------------------------//
else {
    handleError('Invalid route');
}

$conn->close();
