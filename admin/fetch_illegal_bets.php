<?php 
include("conn.php");

// Function to check for illegal bets
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

// Get illegal bets data
$illegal_bets = checkIllegalBets($conn);

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
                    <form method='POST' action='index.php'>
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
