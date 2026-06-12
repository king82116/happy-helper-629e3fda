<?php
/*
Improved Database Connection with proper connection management
This file contains database configuration with better error handling
*/

date_default_timezone_set('Asia/Kolkata');

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'josh296689_max');
define('DB_PASSWORD', 'josh296689_max');
define('DB_NAME', 'josh296689_max');

// Global connection variable
$conn = null;

// Function to get database connection
function getDBConnection()
{
    global $conn;

    // If connection already exists and is valid, return it
    if ($conn && $conn->ping()) {
        return $conn;
    }

    // Close existing connection if it exists
    if ($conn) {
        $conn->close();
    }

    // Create new connection
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check the connection
    if ($conn === false) {
        error_log("Database connection failed: " . mysqli_connect_error());
        return false;
    }

    // Set charset to prevent encoding issues
    $conn->set_charset("utf8mb4");

    return $conn;
}

// Function to close database connection
function closeDBConnection()
{
    global $conn;

    // Close connection if it exists and is valid
    if ($conn && $conn instanceof mysqli) {
        $conn->close();
        $conn = null;
    }
}

// Get the connection
$conn = getDBConnection();

// Check if connection failed
if (!$conn) {
    error_log("Failed to establish database connection");
    // Don't die here, let the calling script handle the error
}
?>