<?php
// Signature Generator Tool
// This tool helps you understand how to create the correct signature for API calls

echo "<h2>API Signature Generator</h2>";

// Get the current timestamp
$timestamp = time();
$current_time = date("Y-m-d H:i:s");

echo "<h3>Current Time:</h3>";
echo "<p>Timestamp: $timestamp</p>";
echo "<p>Formatted: $current_time</p>";

// Example parameters for ARBWalletActivateNet
$language = "en"; // or your language code
$random = "test123"; // your random string

echo "<h3>Parameters:</h3>";
echo "<p>Language: $language</p>";
echo "<p>Random: $random</p>";

// Create the signature string (same as in the API)
$signature_string = '{"language":' . $language . ',"random":"' . $random . '"}';
$expected_signature = strtoupper(md5($signature_string));

echo "<h3>Signature Generation:</h3>";
echo "<p><strong>Signature String:</strong> $signature_string</p>";
echo "<p><strong>Expected Signature:</strong> $expected_signature</p>";

echo "<h3>API Request Example:</h3>";
echo "<pre>";
echo "POST https://joshgame.online/application/api/webapi/ARBWalletActivateNet\n";
echo "Content-Type: application/json\n\n";
echo json_encode([
    'language' => $language,
    'random' => $random,
    'signature' => $expected_signature,
    'timestamp' => $timestamp
], JSON_PRETTY_PRINT);
echo "</pre>";

echo "<h3>How to Use:</h3>";
echo "<ol>";
echo "<li>Use the parameters above in your API request</li>";
echo "<li>Make sure the signature matches exactly</li>";
echo "<li>The signature is case-sensitive (uppercase MD5)</li>";
echo "<li>Include all required parameters: language, random, signature, timestamp</li>";
echo "</ol>";

echo "<h3>Common Issues:</h3>";
echo "<ul>";
echo "<li><strong>Wrong signature format:</strong> Make sure it's uppercase MD5</li>";
echo "<li><strong>Missing parameters:</strong> All 4 parameters are required</li>";
echo "<li><strong>Wrong JSON format:</strong> The signature string must match exactly</li>";
echo "<li><strong>Case sensitivity:</strong> Signature must be uppercase</li>";
echo "</ul>";

echo "<h3>Test Your Request:</h3>";
echo "<p>Try this exact request:</p>";
echo "<pre>";
echo "curl -X POST https://joshgame.online/application/api/webapi/ARBWalletActivateNet \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\n";
echo "    \"language\": \"$language\",\n";
echo "    \"random\": \"$random\",\n";
echo "    \"signature\": \"$expected_signature\",\n";
echo "    \"timestamp\": $timestamp\n";
echo "  }'";
echo "</pre>";
?>