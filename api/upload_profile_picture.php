<?php
/**
 * API Endpoint: Upload Profile Picture
 * Handles profile picture uploads with validation and resizing
 */

session_start();
require_once __DIR__ . '/../db.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in as admin
if (!isset($_SESSION['id_user']) || $_SESSION['usertype'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $user_id = intval($_POST['user_id']);
    
    if (!$user_id) {
        throw new Exception('Invalid user ID');
    }
    
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }
    
    $file = $_FILES['profile_picture'];
    
    // Validate file
    validateProfilePicture($file);
    
    // Create upload directory if it doesn't exist
    $upload_dir = __DIR__ . '/../uploads/profile_pictures/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        throw new Exception('Failed to save uploaded file');
    }
    
    // Resize image if needed
    resizeProfilePicture($file_path, 200, 200);
    
    // Update database
    $relative_path = 'uploads/profile_pictures/' . $filename;
    $sql = "UPDATE users SET profile_picture = ? WHERE id_user = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $relative_path, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Profile picture uploaded successfully',
            'file_path' => $relative_path
        ]);
    } else {
        // Delete uploaded file if database update fails
        unlink($file_path);
        throw new Exception('Failed to update database');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function validateProfilePicture($file) {
    // Check file size (20MB limit)
    $max_size = 20 * 1024 * 1024; // 20MB in bytes
    if ($file['size'] > $max_size) {
        throw new Exception('File size exceeds 20MB limit');
    }
    
    // Check file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, and GIF are allowed');
    }
    
    // Check if it's actually an image
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        throw new Exception('File is not a valid image');
    }
}

function resizeProfilePicture($file_path, $max_width, $max_height) {
    $image_info = getimagesize($file_path);
    $width = $image_info[0];
    $height = $image_info[1];
    $mime_type = $image_info['mime'];
    
    // If image is already smaller than max dimensions, no need to resize
    if ($width <= $max_width && $height <= $max_height) {
        return;
    }
    
    // Calculate new dimensions maintaining aspect ratio
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = intval($width * $ratio);
    $new_height = intval($height * $ratio);
    
    // Create image resource based on type
    switch ($mime_type) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($file_path);
            break;
        case 'image/png':
            $source = imagecreatefrompng($file_path);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($file_path);
            break;
        default:
            return; // Unsupported type
    }
    
    // Create new image with new dimensions
    $resized = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG and GIF
    if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefilledrectangle($resized, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Resize image
    imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Save resized image
    switch ($mime_type) {
        case 'image/jpeg':
            imagejpeg($resized, $file_path, 90);
            break;
        case 'image/png':
            imagepng($resized, $file_path, 9);
            break;
        case 'image/gif':
            imagegif($resized, $file_path);
            break;
    }
    
    // Clean up memory
    imagedestroy($source);
    imagedestroy($resized);
}
?>
