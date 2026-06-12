<?php
include('conn.php');

if (isset($_POST['type'])) {
    $id = intval($_POST['id']);
    $remark = isset($_POST['remark']) ? mysqli_real_escape_string($conn, $_POST['remark']) : '';
    $today = date('Y-m-d H:i:s');

    // Fetch withdrawal details
    $finduid = mysqli_query($conn, "SELECT * FROM `hintegedukolli` WHERE `shonu` = '$id'");
    $finduidArray = mysqli_fetch_assoc($finduid);
    $userid = $finduidArray['balakedara'];
    $amount = $finduidArray['motta'];

    if ($_POST['type'] === 'processing') {
        $sqlA = mysqli_query($conn, "UPDATE `hintegedukolli` 
                                   SET sthiti = '3', 
                                       tike = 'Processing', 
                                       remarks = '$remark', 
                                       dinankavannuracisi = '$today' 
                                   WHERE `shonu` = '$id' AND `sthiti` = '0'");

        if ($sqlA && mysqli_affected_rows($conn) > 0) {
            echo 3;
        } else {
            echo 0;
        }

    } elseif ($_POST['type'] === 'accept') {
        // Mark withdrawal completed (from pending or processing)
        $sqlA = mysqli_query($conn, "UPDATE `hintegedukolli` 
                                   SET sthiti = '1', 
                                       tike = 'Completed', 
                                       remarks = '$remark', 
                                       dinankavannuracisi = '$today' 
                                   WHERE `shonu` = '$id' AND `sthiti` IN ('0','3')");

        if ($sqlA && mysqli_affected_rows($conn) > 0) {
            echo 1;
        } else {
            echo 0;
        }
        
    } elseif ($_POST['type'] === 'reject') {
        // REJECT LOGIC (SHOULD REFUND BALANCE)
        $sqlA = mysqli_query($conn, "UPDATE `hintegedukolli` 
                                   SET sthiti = '2', 
                                       tike = 'Rejected', 
                                       remarks = '$remark', 
                                       dinankavannuracisi = '$today' 
                                   WHERE `shonu` = '$id' AND `sthiti` IN ('0','3')");

        // Keep wallet update for rejection
        $sqlwallet = mysqli_query($conn, "UPDATE `shonu_kaichila` 
                                        SET `motta` = ROUND((motta + $amount), 2) 
                                        WHERE `balakedara`= '$userid'");

        if ($sqlA && $sqlwallet) {
            echo 2;  // Success
        } else {
            echo 0;  // Error
        }
    } else {
        echo 0;  // Invalid action
    }
}
?>