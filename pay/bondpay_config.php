<?php
// bondpay_config.php - keep this outside webroot if possible
return [
    'BOND_BASE_URL'    => 'https://api.bond-pays.com',
    'BOND_CREATE_PATH' => '/v1/create',
    'BOND_MERCHANT_ID' => '100888049', // replace with your actual merchant id
    'BOND_API_KEY'     => 'a08df5e4c8c9d5317e90542863421123', // keep secret
    'MY_HOST'          => 'https://ninjaclub.online', // your site
    'RETURN_URL'       => 'https://ninjaclub.online/#/main',
    // callback must be reachable publicly and use https
    'NOTIFY_URL'       => 'https://ninjaclub.online/pay/bondpay_callback.php',
    // optional: path to log file for debug
    'LOG_FILE'         => '/tmp/bondpay_integration.log'
];
