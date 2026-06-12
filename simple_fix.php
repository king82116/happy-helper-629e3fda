<?php
// Simple fix for notifications table
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Simple Database Fix</h2>";

try {
    include "application/conn.php";

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    echo "<p style='color: green;'>✓ Database connected</p>";

    // Check if content column exists
    $result = $conn->query("SHOW COLUMNS FROM notifications LIKE 'content'");
    $column_exists = $result->num_rows > 0;

    if (!$column_exists) {
        // Add content column
        $sql = "ALTER TABLE notifications ADD COLUMN content text NOT NULL AFTER title";
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✓ Added content column</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Content column already exists</p>";
    }

    // Insert sample notification
    $sql = "INSERT IGNORE INTO notifications (title, content, type, status) VALUES ('Welcome', 'Welcome to our platform!', 'info', 1)";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Sample notification inserted</p>";
    } else {
        echo "<p style='color: red;'>✗ Error inserting notification: " . $conn->error . "</p>";
    }

    echo "<h3 style='color: green;'>🎉 Database setup complete!</h3>";
    echo "<p>All tables are ready. You can now delete this file and test your application.</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

if (isset($conn)) {
    $conn->close();
}
?>