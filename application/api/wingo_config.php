<?php
/**
 * Wingo API Configuration
 * Configure your Wingo API server details here
 */

return [
    // Wingo API Server URL (your TM-API server)
    'wingo_api_url' => 'https://api.techmazet.in', // Your proxy server domain

    // Wingo Client API Key (get this from your proxy server admin panel)
    // Use /admin/create_wingo_client to create a Wingo client and get the API key
    'wingo_api_key' => 'bd3553ba4e032ed7fc76a1d24d5ef1c878198d718dcab8571e218f87620f20e8', // CHANGE THIS to your Wingo client API key

    // Client Domain (must match the domain in proxy server's allowed_domain for Wingo client)
    // This is used for Origin header validation
    'wingo_client_domain' => 'joshclub.fun', // CHANGE THIS to your actual domain (without https://)
];

