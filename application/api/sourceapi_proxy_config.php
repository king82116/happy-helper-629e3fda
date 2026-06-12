<?php

/**
 * SoftAPI Proxy Configuration (TM-API 2ND GEN)
 * Separate config for SoftAPI - does not affect old proxy_config.php
 */

return [
    // Vendor codes that use SoftAPI (TM-API 2ND GEN) - add codes like 'SOFTAPI', 'JILI', etc.
    'vendor_codes' => ['SOFTAPI'],

    // SoftAPI Proxy API Server URL
    'proxy_api_url' => 'https://api.bdt.techmazet.in',

    // Client API Key (must match client in proxy admin - Clients page)
    'client_api_key' => 'ce1a2d1254536378253c18ceb8e433a3cd58ec53b056c7dbd9c736f26fbe8691',

    // Client Domain (must be in proxy allowed_domains)
    'client_domain' => 'superparibet.com',

    // Callback URL - MUST be publicly reachable (proxy forwards here).
    // NOTE: This URL returns 404 on superparibet.com - fix deployment or path:
    // 1. Ensure UpdateBalanceSoftAPI.php is uploaded to this exact path
    // 2. If game is in /game/ subfolder, use: .../game/application/api/webapi/UpdateBalanceSoftAPI.php
    // 3. Test: curl -X POST "https://superparibet.com/application/api/webapi/UpdateBalanceSoftAPI.php" -d '{}' -H "Content-Type: application/json"
    'callback_url_override' => 'https://superparibet.com/application/api/webapi/UpdateBalanceSoftAPI',

    'default_currency' => 'BDT',
    'default_language' => 'en',

    'language_map' => [
        1 => 'en',
        2 => 'zh',
        3 => 'th',
        4 => 'vi',
        5 => 'id',
    ],
];
