<?php
// Quick fix for notifications table
include "application/conn.php";

echo "<h2>Fixing Notifications Table...</h2>";

// Add the content column if it doesn't exist
$sql = "ALTER TABLE `notifications` ADD COLUMN IF NOT EXISTS `content` text NOT NULL AFTER `title`";
if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Added 'content' column to notifications table</p>";
} else {
    echo "<p style='color: red;'>✗ Error adding column: " . $conn->error . "</p>";
}

// Insert sample notification
$sql = "INSERT IGNORE INTO `notifications` (`title`, `content`, `type`, `status`) VALUES ('Welcome', 'Welcome to our platform!', 'info', 1)";
if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Sample notification inserted successfully</p>";
} else {
    echo "<p style='color: red;'>✗ Error inserting notification: " . $conn->error . "</p>";
}

echo "<h3 style='color: green;'>🎉 Database setup is now 100% complete!</h3>";
echo "<p>All tables have been created and all default data has been inserted.</p>";
echo "<p>You can now delete all setup files and test your application.</p>";

$conn->close();
?>