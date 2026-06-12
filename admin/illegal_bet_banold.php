<?php 
include("conn.php");

// Function to check for illegal bets across all tables
function checkIllegalBets($conn) {
    $query = "
        SELECT byabaharkarta, kalaparichaya, 'bajikattuttate_zehn' AS table_name FROM bajikattuttate_zehn
        UNION ALL
        SELECT byabaharkarta, kalaparichaya, 'bajikattuttate' FROM bajikattuttate
        UNION ALL
        SELECT byabaharkarta, kalaparichaya, 'bajikattuttate_drei' FROM bajikattuttate_drei
        UNION ALL
        SELECT byabaharkarta, kalaparichaya, 'bajikattuttate_funf' FROM bajikattuttate_funf
        UNION ALL
        SELECT byabaharkarta, kalaparichaya, 'bajikattuttate_aidudi' FROM bajikattuttate_aidudi
        UNION ALL
        SELECT byabaharkarta, kalaparichaya, 'bajikattuttate_aidudi_drei' FROM bajikattuttate_aidudi_drei
        UNION ALL
        SELECT byabaharkarta, kalaparichaya, 'bajikattuttate_aidudi_funf' FROM bajikattuttate_aidudi_funf
        UNION ALL
        SELECT byabaharkarta, kalaparichaya, 'bajikattuttate_aidudi_zehn' FROM bajikattuttate_aidudi_zehn
    ";
    return $conn->query($query);
}

// Function to map table names to game names
function getGameName($table_name) {
    $game_names = [
        "bajikattuttate_zehn" => "Wingo 30 sec",
        "bajikattuttate" => "Wingo 1 min",
        "bajikattuttate_drei" => "Wingo 3 min",
        "bajikattuttate_funf" => "Wingo 5 min",
        "bajikattuttate_aidudi" => "D5 1 min",
        "bajikattuttate_aidudi_drei" => "D5 3 min",
        "bajikattuttate_aidudi_funf" => "D5 5 min",
        "bajikattuttate_aidudi_zehn" => "D5 10 min"
    ];
    return $game_names[$table_name] ?? "Unknown Game";
}

// Ban user if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ban_user'])) {
    $user_id = $conn->real_escape_string($_POST['user_id']);
    $period = $conn->real_escape_string($_POST['period']);
    $game = $conn->real_escape_string($_POST['game']);

    // Insert into illegal_bet_banned table
    $insert_query = "INSERT INTO illegal_bet_banned (user_id, period, game) VALUES ('$user_id', '$period', '$game')";
    
    // Update user status in shonu_subjects table
    $update_query = "UPDATE shonu_subjects SET status = 0 WHERE id = '$user_id'";

    if ($conn->query($insert_query) === TRUE && $conn->query($update_query) === TRUE) {
        $message = "User $user_id has been banned successfully!";
    } else {
        $message = "Error banning user: " . $conn->error;
    }
}

// Get illegal bets data
$illegal_bets = checkIllegalBets($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="stylesheet" href="css/mobile-responsive.css">
    <title>Illegal Bet Bans</title>
</head>
<body>

<div class="container">
    <h1>Illegal Bet Bans</h1>

    <?php if (isset($message)): ?>
        <p style="color: red; font-weight: bold;"><?php echo $message; ?></p>
    <?php endif; ?>

    <table border="1">
        <thead>
            <tr>
                <th>Period</th>
                <th>User ID</th>
                <th>Game</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($illegal_bets->num_rows > 0) {
                while ($row = $illegal_bets->fetch_assoc()) {
                    $user_id = $row['byabaharkarta'];
                    $period = $row['kalaparichaya'];
                    $game = getGameName($row['table_name']);  
                    echo "<tr>
                            <td>{$period}</td>
                            <td>{$user_id}</td>
                            <td>{$game}</td>
                            <td>
                                <form method='POST'>
                                    <input type='hidden' name='user_id' value='{$user_id}'>
                                    <input type='hidden' name='period' value='{$period}'>
                                    <input type='hidden' name='game' value='{$game}'>
                                    <button type='submit' name='ban_user'>Ban</button>
                                </form>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No illegal bets detected.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
