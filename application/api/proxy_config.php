<?php

/**
 * Proxy API Configuration
 * Configure your proxy server details here
 */

return [
    // Proxy API Server URL (your TM-API server)
    'proxy_api_url' => 'https://api.techmazet.in', // Your proxy server domain

    // Force direct IP if hosting DNS is broken (api.techmazet.in → 65.108.9.253)
    'proxy_api_resolve_ip' => '65.108.9.253',

    // Outbound request timeouts (seconds) — increase if provider is slow
    'connect_timeout' => 20,
    'request_timeout' => 60,

    // Client API Key (get this from your proxy server admin panel)
    'client_api_key' => '93addf8313eae43bedbb04b8bc82b338a8f42e2b1bd1486c6b30c1e06351d3b2', // CHANGE THIS to your client API key

    // --- Sports API (game website / frontend config) ---
    // Endpoint URL (for the API call): proxy_api_url + '/api/sports/launch'. Backend calls GET that URL with X-API-KEY. Do NOT put that API URL into sports_launch_url.
    // sports_launch_url = optional *page* URL to open when user goes to Sports (browser URL), not the API endpoint. Leave empty or set to a real page (e.g. https://yoursite.com/sports). Do NOT set to .../api/sports/launch.
    // | Config / concept   | Use for                      | Example / value                          |
    // | Endpoint URL       | API call to get sports list  | proxy_api_url + '/api/sports/launch'    |
    // | sports_api_key     | Header X-API-KEY for that call | SPT_xxxx...                            |
    // | sports_launch_url  | Optional page URL to open    | '' or https://yoursite.com/sports       |

    'sports_api_key' => 'SPT_5f6bf8604738a22f0d68caf8bd292fc6', // CHANGE THIS to your Sports API key from your administrator

    // Sports vendor codes: these vendors use Sports API (test/launch/events), not POST /api/game/launch. Add "betfair", "UNITED GAMING", and/or numeric id (e.g. 8, 25).
    'sports_vendor_codes' => ['betfair', 'UNITED GAMING'],

    'sports_launch_url' => 'https://superparibet.com/sports.html', // Optional page to open for Sports. Leave empty or set to a real page URL; do NOT set to the API endpoint.




    // Client Domain (must match the domain in proxy server's allowed_domain)
    // This is used for Origin header validation
    'client_domain' => 'joshclub.fun', // Site domain for Origin header (must match proxy allowlist)

    // Set false only if the proxy uses a self-signed certificate
    'ssl_verify' => true,

    // Vendor codes to route through proxy (empty array = route all vendors)
    // If you want to route only specific vendors, add them here: ['18', 'JILI', '23', 'JDB']
    // If empty, all vendors will be routed through proxy
    'proxy_vendor_codes' => [], // Empty = route all vendors through proxy

    // Currency code (default: USD)
    'default_currency' => 'INR',

    // Language mapping (1=en, 2=zh, etc.)
    'language_map' => [
        1 => 'en',
        2 => 'zh',
        3 => 'th',
        4 => 'vi',
        5 => 'id',
    ],

    // Default language if not found in map
    'default_language' => 'en',

    // Platform mapping (phonetype: 1=web, 2=H5)
    'platform_map' => [
        1 => 1, // web
        2 => 2, // H5
    ],

    // Default platform
    'default_platform' => 1, // web
];
