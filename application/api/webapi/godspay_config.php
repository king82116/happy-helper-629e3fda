<?php
/**
 * Godspay API Configuration
 * 
 * This file contains configuration for integrating with Godspay Master API
 * Clients only need ONE API key from godspay - all HUIDU details are hidden
 */

// Godspay Master API Base URL
define('GODSPAY_API_URL', 'https://godspay.site');

// Godspay Client API Key
// Get this by creating a client via: POST https://godspay.site/admin/createClient
define('GODSPAY_API_KEY', 'godspayjoshkfewfnlwemfwklwev');

/**
 * Get Godspay API Key
 * You can override this function to fetch from database
 * 
 * @return string API Key
 */
function getGodspayApiKey()
{
    // Option 1: Use constant (current)
    if (defined('GODSPAY_API_KEY')) {
        $key = GODSPAY_API_KEY;
        // Return key if it's not empty and not the placeholder
        if (!empty($key) && $key !== 'YOUR_GODSPAY_API_KEY_HERE') {
            return $key;
        }
    }

    // Option 2: Get from database (uncomment and customize)
    /*
    global $conn;
    if (isset($conn)) {
        $query = "SELECT api_key FROM godspay_config LIMIT 1";
        $result = $conn->query($query);
        if ($result && $row = $result->fetch_assoc() && !empty($row['api_key'])) {
            return $row['api_key'];
        }
    }
    */

    // Option 3: Get from environment/config file
    // $envKey = getenv('GODSPAY_API_KEY');
    // if ($envKey && !empty($envKey)) {
    //     return $envKey;
    // }

    // Default fallback
    return '';
}

/**
 * Check if vendor code is Godspay game
 * 
 * @param mixed $vendorCode Vendor code to check
 * @return bool True if Godspay game
 */
function isGodspayVendor($vendorCode)
{
    return (
        $vendorCode === 'GODSPAY' ||
        $vendorCode === 'godspay' ||
        $vendorCode == 99 ||
        (is_numeric($vendorCode) && intval($vendorCode) >= 100)
    );
}

/**
 * Map language code to API format
 * 
 * @param int $languageCode Your language code
 * @return string API language code
 */
function mapLanguageToApi($languageCode)
{
    $languageMap = [
        1 => 'en',  // English
        2 => 'zh',  // Chinese
        3 => 'hi',  // Hindi
        4 => 'th',  // Thai
        5 => 'vi',  // Vietnamese
    ];
    return $languageMap[$languageCode] ?? 'en';
}

/**
 * Map platform type to API format
 * 
 * @param mixed $phonetype Phone type (1=web, 2=H5)
 * @return int API platform (1=web, 2=H5)
 */
function mapPlatformToApi($phonetype)
{
    return ($phonetype == 2 || $phonetype === 'H5') ? 2 : 1;
}

