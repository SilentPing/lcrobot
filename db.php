<?php
/**
 * Database Connection File
 * 
 * This file establishes the database connection using configuration
 * from config.php and includes encryption helper functions.
 */

// Load configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/crypto.php';

// Create database connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Enable error reporting for development
if (APP_DEBUG) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

// Function to get database connection
function getDBConnection() {
    global $conn;
    return $conn;
}

// Function to close database connection
function closeDBConnection() {
    global $conn;
    if ($conn && !$conn->connect_error) {
        try {
            $conn->close();
        } catch (Exception $e) {
            // Ignore errors when closing connection
        }
        $conn = null; // Set to null to prevent double closing
    }
}

// Don't register shutdown function to prevent conflicts
// Connection will be closed automatically when script ends
?>