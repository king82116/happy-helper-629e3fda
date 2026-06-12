<?php

return [
    // WePayPlus Merchant ID and Key (provided by WePayPlus)
    'merchantId' => '3r295032',  // WePayPlus Merchant ID
    'merchantKey' => 'f675d1b1c85f4e48a8690346135988e3',  // WePayPlus Merchant Key
    'passageId' => '32301',  // Your passage ID (can be used for specific payment gateways or channels)
    'notifyUrl' => 'https://joshgame.online/pay/verify_wepay.php',  // WePayPlus will send payment status notifications here
    'callBackUrl' => 'https://joshgame.online/#/wallet/RechargeHistory',  // After payment is complete, redirect back to this URL
];

?>