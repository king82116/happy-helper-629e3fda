<?php
// Simple Database Fix Script
// This script will create all missing tables with better error handling

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Setup Script</h1>";
echo "<p>Starting database setup...</p>";

// Test database connection first
try {
    include "application/conn.php";

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    echo "<p style='color: green;'>✓ Database connection successful</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials in application/conn.php</p>";
    exit;
}

// Array to store results
$results = [];

// Function to execute SQL safely
function executeSQL($conn, $sql, $description)
{
    global $results;

    try {
        if ($conn->query($sql)) {
            $results[] = "✓ " . $description;
            return true;
        } else {
            $results[] = "✗ " . $description . " - Error: " . $conn->error;
            return false;
        }
    } catch (Exception $e) {
        $results[] = "✗ " . $description . " - Exception: " . $e->getMessage();
        return false;
    }
}

echo "<h2>Creating Database Tables...</h2>";

// 1. homepage_banners
$sql = "CREATE TABLE IF NOT EXISTS `homepage_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_url` varchar(500) NOT NULL,
  `redirect_url` varchar(500) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeSQL($conn, $sql, "homepage_banners table");

// 2. notifications
$sql = "CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeSQL($conn, $sql, "notifications table");

// 3. notification (singular)
$sql = "CREATE TABLE IF NOT EXISTS `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeSQL($conn, $sql, "notification table");

// 4. withdrawal_settings
$sql = "CREATE TABLE IF NOT EXISTS `withdrawal_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fee` decimal(10,2) DEFAULT 0.00,
  `min_price` decimal(10,2) DEFAULT 110.00,
  `max_price` decimal(10,2) DEFAULT 50000.00,
  `u_rate` decimal(5,2) DEFAULT 93.00,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeSQL($conn, $sql, "withdrawal_settings table");

// 5. shonu_subjects
$sql = "CREATE TABLE IF NOT EXISTS `shonu_subjects` (
  `shonu` int(11) NOT NULL AUTO_INCREMENT,
  `nam` varchar(100) NOT NULL,
  `duravani` varchar(20) NOT NULL,
  `aksinak` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`shonu`),
  UNIQUE KEY `duravani` (`duravani`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeSQL($conn, $sql, "shonu_subjects table");

// 6. shonu_kaichila
$sql = "CREATE TABLE IF NOT EXISTS `shonu_kaichila` (
  `shonu` int(11) NOT NULL AUTO_INCREMENT,
  `balakedara` int(11) NOT NULL,
  `motta` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`shonu`),
  UNIQUE KEY `balakedara` (`balakedara`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeSQL($conn, $sql, "shonu_kaichila table");

// 7. hintegedukolli
$sql = "CREATE TABLE IF NOT EXISTS `hintegedukolli` (
  `shonu` int(11) NOT NULL AUTO_INCREMENT,
  `balakedara` int(11) NOT NULL,
  `motta` decimal(15,2) NOT NULL,
  `madari` tinyint(1) DEFAULT 1,
  `dharavahi` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `sthiti` tinyint(1) DEFAULT 0,
  `dinankavannuracisi` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`shonu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeSQL($conn, $sql, "hintegedukolli table");

// 8. khate
$sql = "CREATE TABLE IF NOT EXISTS `khate` (
  `shonu` int(11) NOT NULL AUTO_INCREMENT,
  `byabaharkarta` int(11) NOT NULL,
  `khatehesaru` varchar(100) NOT NULL,
  `khatesankhye` varchar(50) NOT NULL,
  `kod` varchar(20) DEFAULT NULL,
  `duravani` varchar(20) DEFAULT NULL,
  `phalanubhavi` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`shonu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeSQL($conn, $sql, "khate table");

// 9. thevani
$sql = "CREATE TABLE IF NOT EXISTS `thevani` (
  `shonu` int(11) NOT NULL AUTO_INCREMENT,
  `balakedara` int(11) NOT NULL,
  `motta` decimal(15,2) NOT NULL,
  `sthiti` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`shonu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeSQL($conn, $sql, "thevani table");

// 10. hodike_balakedara
$sql = "CREATE TABLE IF NOT EXISTS `hodike_balakedara` (
  `shonu` int(11) NOT NULL AUTO_INCREMENT,
  `userkani` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`shonu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeSQL($conn, $sql, "hodike_balakedara table");

// 11. captcha_data
$sql = "CREATE TABLE IF NOT EXISTS `captcha_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `captcha_text` varchar(10) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeSQL($conn, $sql, "captcha_data table");

echo "<h2>Inserting Default Data...</h2>";

// Insert default withdrawal settings
$sql = "INSERT IGNORE INTO `withdrawal_settings` (`id`, `fee`, `min_price`, `max_price`, `u_rate`) VALUES (1, 0.00, 110.00, 50000.00, 93.00)";
executeSQL($conn, $sql, "Default withdrawal settings");

// Insert sample banner
$sql = "INSERT IGNORE INTO `homepage_banners` (`banner_url`, `redirect_url`, `status`, `sort_order`) VALUES ('https://example.com/banner1.jpg', 'https://example.com', 1, 1)";
executeSQL($conn, $sql, "Sample banner");

// Insert sample notification
$sql = "INSERT IGNORE INTO `notifications` (`title`, `content`, `type`, `status`) VALUES ('Welcome', 'Welcome to our platform!', 'info', 1)";
executeSQL($conn, $sql, "Sample notification");

echo "<h2>Setup Results:</h2>";
echo "<ul>";
foreach ($results as $result) {
    $color = (strpos($result, '✓') !== false) ? 'green' : 'red';
    echo "<li style='color: $color;'>$result</li>";
}
echo "</ul>";

// Count successes and failures
$success_count = count(array_filter($results, function ($result) {
    return strpos($result, '✓') !== false;
}));

$total_count = count($results);

echo "<h2>Summary:</h2>";
echo "<p>Successfully completed: $success_count out of $total_count operations</p>";

if ($success_count == $total_count) {
    echo "<h3 style='color: green;'>🎉 Database setup completed successfully!</h3>";
    echo "<p>All required tables have been created. You can now:</p>";
    echo "<ul>";
    echo "<li>Delete this file for security</li>";
    echo "<li>Test your application</li>";
    echo "<li>The database errors should be resolved</li>";
    echo "</ul>";
} else {
    echo "<h3 style='color: orange;'>⚠️ Setup completed with some issues</h3>";
    echo "<p>Some operations failed. Please check the error messages above.</p>";
}

$conn->close();
?>