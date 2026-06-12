<?php
// watchpayverify.php - CORRECTED VERSION

include("../serive/samparka.php");

$merchant_key = "VKRW7BLBSWUJUWYYY9A3U6IPINUPYSXP";

function wp_log($msg) {
    file_put_contents(__DIR__ . "/watchpay_callback.log",
        date("Y-m-d H:i:s") . " -- " . $msg . "\n",
        FILE_APPEND
    );
}

// Read callback data
$data = $_POST;
if(empty($data)) $data = $_GET;

wp_log("RAW CALLBACK: " . json_encode($data, JSON_UNESCAPED_UNICODE));

if (empty($data)) {
    wp_log("❌ NO CALLBACK DATA");
    echo "fail";
    exit;
}

// Verify signature
$signFromGateway = $data['sign'] ?? '';
$dataForSign = $data;
unset($dataForSign['sign'], $dataForSign['signType']);
$dataForSign = array_filter($dataForSign, fn($v) => $v !== "" && $v !== null);

ksort($dataForSign, SORT_STRING);

$signSrc = "";
foreach($dataForSign as $k => $v){
    $signSrc .= $k."=".$v."&";
}
$signSrc .= "key=".$merchant_key;

$mySign = strtolower(md5($signSrc));

wp_log("MY SIGN: $mySign | GATEWAY SIGN: $signFromGateway");

if($mySign !== strtolower($signFromGateway)){
    wp_log("❌ SIGNATURE MISMATCH");
    echo "fail";
    exit;
}

// Payment details
$tradeResult = $data['tradeResult'] ?? 0;
$mchOrderNo  = $data['mchOrderNo'] ?? '';
$amount      = $data['tradeAmount'] ?? 0;

wp_log("Trade Result: $tradeResult | Order: $mchOrderNo | Amount: $amount");

// Process only if payment successful
if($tradeResult == 1){
    
    // Find pending order (LG Pay जैसा logic)
    $checkamt = mysqli_query($conn, 
        "SELECT motta, balakedara FROM thevani 
         WHERE dharavahi = '".$mchOrderNo."' AND sthiti = '0'"
    );

    if (!$checkamt) {
        wp_log("❌ Database Query Error: " . mysqli_error($conn));
        echo "fail";
        exit;
    }

    $checkamtrow = mysqli_num_rows($checkamt);

    if ($checkamtrow >= 1) {
        $checkamtar = mysqli_fetch_array($checkamt);
        $motta = $checkamtar['motta'];
        $shonuid = $checkamtar['balakedara'];
        
        wp_log("Found Order - UID: $shonuid | Amount: $motta");

        // ====== MAIN FIX: LG Pay जैसा exact update ======
        $nabikarana = "UPDATE shonu_kaichila
                       SET motta = ROUND(motta + '".$motta."', 2)
                       WHERE balakedara = '".$shonuid."'";
        
        if (!$conn->query($nabikarana)) {
            wp_log("❌ Wallet Update Error: " . mysqli_error($conn));
            echo "fail";
            exit;
        }

        wp_log("✅ Wallet Updated Successfully");

        // Mark order as completed
        $sql2 = mysqli_query($conn, 
            "UPDATE thevani SET sthiti = '1' WHERE dharavahi = '".$mchOrderNo."'"
        );

        if (!$sql2) {
            wp_log("❌ Order Status Update Error: " . mysqli_error($conn));
            echo "fail";
            exit;
        }

        wp_log("✅ Order Marked Complete");

    } else {
        wp_log("⚠️ Order Not Found or Already Processed");
    }
    
} else {
    wp_log("❌ Payment Failed - TradeResult: $tradeResult");
}

echo "success";
exit;
?>