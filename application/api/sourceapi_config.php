<?php
/**
 * SOURCEAPI (sourceapi.pro / sourceapi.com) credentials and defaults.
 * Copy api_key, api_secret, and base URL from your SOURCEAPI API Details page.
 */
return [
    'base_url' => 'https://sourceapi.pro',

    'api_key' => 'b5c26e22a00821b76ecc145435e41b6230035a21decac71f',
    'api_secret' => '92b629215a1de557f11f53db1e2696ca2cafb923cf82c8aac98af177f4fa6699',

    // Required on every SOURCEAPI request (lowercase).
    'vendor_code' => 'sourceapi',

    'default_currency' => 'INR',

    'language_map' => [
        1 => 'en',
        2 => 'zh',
        3 => 'th',
        4 => 'vi',
        5 => 'id',
    ],
    'default_language' => 'en',
];
