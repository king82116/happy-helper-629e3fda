<?php
// Token Expiry Diagnosis and Fix Tool
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Token Expiry Diagnosis Tool</h2>";

// Include the JWT functions
include "application/functions2.php";

// Test JWT token validation
function testJWTToken($token)
{
    $result = is_jwt_valid($token);
    $data = json_decode($result, true);

    echo "<h3>JWT Token Analysis:</h3>";
    echo "<p><strong>Token:</strong> " . substr($token, 0, 50) . "...</p>";
    echo "<p><strong>Status:</strong> " . $data['status'] . "</p>";

    if ($data['status'] === 'Success') {
        $payload = $data['payload'];
        echo "<p><strong>User ID:</strong> " . $payload['id'] . "</p>";
        echo "<p><strong>Mobile:</strong> " . $payload['mobile'] . "</p>";
        echo "<p><strong>Expire Time:</strong> " . date('Y-m-d H:i:s', $payload['expire']) . "</p>";
        echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s', time()) . "</p>";

        $timeLeft = $payload['expire'] - time();
        echo "<p><strong>Time Left:</strong> " . $timeLeft . " seconds (" . round($timeLeft / 3600, 2) . " hours)</p>";

        if ($timeLeft <= 0) {
            echo "<p style='color: red;'>❌ Token has expired!</p>";
            return false;
        } else {
            echo "<p style='color: green;'>✅ Token is valid</p>";
            return true;
        }
    } else {
        echo "<p style='color: red;'>❌ Token is invalid</p>";
        return false;
    }
}

// Check if token is provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    testJWTToken($token);
} else {
    echo "<h3>How to Use:</h3>";
    echo "<p>Add your JWT token as a URL parameter:</p>";
    echo "<p><code>https://joshgame.online/token_expiry_fix.php?token=YOUR_JWT_TOKEN</code></p>";

    echo "<h3>Common Token Issues:</h3>";
    echo "<ul>";
    echo "<li><strong>Token Expired:</strong> JWT token has passed its expiration time</li>";
    echo "<li><strong>Invalid Token:</strong> Token format is incorrect or corrupted</li>";
    echo "<li><strong>Missing Token:</strong> No token provided in request</li>";
    echo "<li><strong>Wrong Signature:</strong> Token signature validation failed</li>";
    echo "</ul>";

    echo "<h3>Solutions:</h3>";
    echo "<ol>";
    echo "<li><strong>Re-login:</strong> Get a fresh token by logging in again</li>";
    echo "<li><strong>Check Token:</strong> Verify the token is being sent correctly</li>";
    echo "<li><strong>Clear Cache:</strong> Clear browser cache and localStorage</li>";
    echo "<li><strong>Check Time:</strong> Ensure server and client times are synchronized</li>";
    echo "</ol>";

    echo "<h3>Frontend Token Management:</h3>";
    echo "<pre>";
    echo "// Check if token exists and is valid\n";
    echo "const token = localStorage.getItem('token');\n";
    echo "if (!token) {\n";
    echo "    // Redirect to login\n";
    echo "    window.location.href = '/#/login';\n";
    echo "}\n\n";
    echo "// Check token expiration\n";
    echo "const tokenData = JSON.parse(atob(token.split('.')[1]));\n";
    echo "if (tokenData.exp * 1000 < Date.now()) {\n";
    echo "    // Token expired, redirect to login\n";
    echo "    localStorage.removeItem('token');\n";
    echo "    window.location.href = '/#/login';\n";
    echo "}\n";
    echo "</pre>";
}

echo "<h3>API Response Codes:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Code</th><th>Message</th><th>Action</th></tr>";
echo "<tr><td>4</td><td>Login has expired</td><td>Re-login required</td></tr>";
echo "<tr><td>5</td><td>Wrong signature</td><td>Check signature format</td></tr>";
echo "<tr><td>7</td><td>Param is Invalid</td><td>Check request parameters</td></tr>";
echo "<tr><td>12</td><td>Account logged in elsewhere</td><td>Logout from other sessions</td></tr>";
echo "</table>";

echo "<h3>Quick Fix Steps:</h3>";
echo "<ol>";
echo "<li>Clear browser localStorage: <code>localStorage.clear()</code></li>";
echo "<li>Clear browser cache and cookies</li>";
echo "<li>Re-login to get a fresh token</li>";
echo "<li>Check if the token is being sent in Authorization header</li>";
echo "<li>Verify the token format is correct</li>";
echo "</ol>";
?>