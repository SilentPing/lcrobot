<?php
/**
 * API Endpoint: Update My Profile (for regular users)
 * Allows users to update their own profile information
 */

session_start();
require_once __DIR__ . '/../db.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Handle both JSON and form data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If JSON parsing failed, try form data
    if (!$input) {
        $input = $_POST;
    }
    
    if (!$input || empty($input)) {
        throw new Exception('No input data received');
    }
    
    $user_id = intval($input['user_id']);
    $action = $input['action'];
    
    // Security check: Users can only update their own profile
    if ($user_id !== $_SESSION['id_user']) {
        throw new Exception('Unauthorized: You can only update your own profile');
    }
    
    if (!$user_id) {
        throw new Exception('Invalid user ID');
    }
    
    switch ($action) {
        case 'update_profile':
            updateMyProfile($conn, $user_id, $input);
            break;
            
        case 'change_password':
            changeMyPassword($conn, $user_id, $input);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function updateMyProfile($conn, $user_id, $data) {
    $firstname = mysqli_real_escape_string($conn, trim($data['firstname']));
    $lastname = mysqli_real_escape_string($conn, trim($data['lastname']));
    $middlename = !empty(trim($data['middlename'])) ? mysqli_real_escape_string($conn, trim($data['middlename'])) : null;
    $username = mysqli_real_escape_string($conn, trim($data['username']));
    $email = mysqli_real_escape_string($conn, trim($data['email']));
    $contact_no = mysqli_real_escape_string($conn, trim($data['contact_no']));
    $house_no = mysqli_real_escape_string($conn, trim($data['house_no']));
    $street_brgy = mysqli_real_escape_string($conn, trim($data['street_brgy']));
    $city_municipality = mysqli_real_escape_string($conn, trim($data['city_municipality']));
    $province = mysqli_real_escape_string($conn, trim($data['province']));
    
    // Validate required fields
    if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($contact_no)) {
        throw new Exception('All required fields must be filled');
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Check if username already exists (excluding current user)
    $checkUsername = "SELECT id_user FROM users WHERE username = ? AND id_user != ?";
    $stmt = mysqli_prepare($conn, $checkUsername);
    mysqli_stmt_bind_param($stmt, 'si', $username, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        throw new Exception('Username already exists');
    }
    
    // Check if email already exists (excluding current user)
    $checkEmail = "SELECT id_user FROM users WHERE email = ? AND id_user != ?";
    $stmt = mysqli_prepare($conn, $checkEmail);
    mysqli_stmt_bind_param($stmt, 'si', $email, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        throw new Exception('Email already exists');
    }
    
    // Update user profile
    $sql = "UPDATE users SET 
            u_fn = ?, u_ln = ?, u_mn = ?, username = ?, email = ?, 
            contact_no = ?, house_no = ?, street_brgy = ?, 
            city_municipality = ?, province = ?
            WHERE id_user = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssssssssi', 
        $firstname, $lastname, $middlename, $username, $email,
        $contact_no, $house_no, $street_brgy, $city_municipality, $province, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Update session data
        $_SESSION['name'] = $username;
        $_SESSION['fname'] = $firstname;
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        throw new Exception('Failed to update profile: ' . mysqli_error($conn));
    }
}

function changeMyPassword($conn, $user_id, $data) {
    $current_password = trim($data['current_password']);
    $new_password = trim($data['new_password']);
    $confirm_password = trim($data['confirm_password']);
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        throw new Exception('All password fields must be filled');
    }
    
    if ($new_password !== $confirm_password) {
        throw new Exception('New passwords do not match');
    }
    
    if (strlen($new_password) < 6) {
        throw new Exception('New password must be at least 6 characters long');
    }
    
    // Verify current password (support both MD5 and password_hash)
    $checkPassword = "SELECT password FROM users WHERE id_user = ?";
    $stmt = mysqli_prepare($conn, $checkPassword);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    $stored_password = $user['password'];
    $current_password_valid = false;
    
    // Check if stored password is MD5 (32 chars) or password_hash (60+ chars)
    if (strlen($stored_password) === 32) {
        // MD5 password
        $current_password_valid = (md5($current_password) === $stored_password);
    } else {
        // password_hash
        $current_password_valid = password_verify($current_password, $stored_password);
    }
    
    if (!$current_password_valid) {
        throw new Exception('Current password is incorrect');
    }
    
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    $sql = "UPDATE users SET password = ? WHERE id_user = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $new_password_hash, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
    } else {
        throw new Exception('Failed to change password');
    }
}
?>
