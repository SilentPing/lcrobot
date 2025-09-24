<?php
/**
 * API Endpoint: Get Current Profile Picture
 * Returns the current profile picture path for the logged-in user
 */

session_start();
require_once __DIR__ . '/../db.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['name'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $email = $_SESSION['name'];
    $query = "SELECT profile_picture FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $profilePicture = $row['profile_picture'];
        $userProfilePicture = 'images/lcro.jpg'; // Default profile picture
        
        if (!empty($profilePicture)) {
            // Try different path variations to find the profile picture
            $path_variations = [
                $profilePicture, // Original path from database
                __DIR__ . '/../' . $profilePicture, // With full directory path
                'uploads/profile_pictures/' . basename($profilePicture), // Just filename in uploads dir
            ];
            
            foreach ($path_variations as $path) {
                if (file_exists($path)) {
                    // Ensure the path is web-accessible (relative to web root)
                    if (strpos($path, 'uploads/profile_pictures/') !== false) {
                        $userProfilePicture = $path;
                    } else {
                        // Convert absolute path to relative web path
                        $userProfilePicture = 'uploads/profile_pictures/' . basename($path);
                    }
                    break;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'profile_picture' => $userProfilePicture,
            'timestamp' => time()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
