<?php
// Simple test to verify last_login functionality
session_start();
require_once __DIR__ . '/db.php';

// Check if user is logged in
if (!isset($_SESSION['name'])) {
    echo "Please log in first to test last_login functionality.";
    exit;
}

$user_id = $_SESSION['id_user'];

// Get current last_login value
$query = "SELECT last_login FROM users WHERE id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

echo "<h3>Last Login Test</h3>";
echo "<p><strong>User ID:</strong> " . $user_id . "</p>";
echo "<p><strong>Username:</strong> " . $_SESSION['name'] . "</p>";
echo "<p><strong>Current Last Login:</strong> " . ($user['last_login'] ? $user['last_login'] : 'NULL') . "</p>";

if ($user['last_login']) {
    echo "<p style='color: green;'><strong>✅ SUCCESS:</strong> Last login is being updated!</p>";
} else {
    echo "<p style='color: red;'><strong>❌ ISSUE:</strong> Last login is still NULL. Please log out and log back in.</p>";
}

echo "<p><a href='logout.php'>Logout and Login Again</a> to test the fix.</p>";
?>
