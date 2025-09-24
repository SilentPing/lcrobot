<?php
/**
 * Serve Profile Picture
 * This endpoint directly serves the profile picture with aggressive cache-busting
 */

session_start();
require_once __DIR__ . '/../db.php';

header('Content-Type: image/jpeg');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['id_user'])) {
    // Serve default image if not logged in
    $default_path = __DIR__ . '/../images/lcro.jpg';
    if (file_exists($default_path)) {
        readfile($default_path);
    }
    exit;
}

$user_id = intval($_SESSION['id_user']);

// Get user's profile picture path
$query = "SELECT profile_picture FROM users WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$profile_picture_path = null;

if ($row = mysqli_fetch_assoc($result)) {
    $db_profile_picture = $row['profile_picture'];
    
    if (!empty($db_profile_picture)) {
        // Try different path variations to find the profile picture
        $path_variations = [
            $db_profile_picture, // Original path from database
            __DIR__ . '/../' . $db_profile_picture, // With full directory path
            'uploads/profile_pictures/' . basename($db_profile_picture), // Just filename in uploads dir
        ];
        
        foreach ($path_variations as $path) {
            if (file_exists($path)) {
                $profile_picture_path = $path;
                break;
            }
        }
    }
}

// If no profile picture found, use default
if (!$profile_picture_path) {
    $profile_picture_path = __DIR__ . '/../images/lcro.jpg';
}

// Serve the image
if (file_exists($profile_picture_path)) {
    readfile($profile_picture_path);
} else {
    // Fallback to default
    $default_path = __DIR__ . '/../images/lcro.jpg';
    if (file_exists($default_path)) {
        readfile($default_path);
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
