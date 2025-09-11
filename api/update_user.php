<?php
/**
 * API Endpoint: Update User Profile
 * Handles user profile updates including basic info and password changes
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
    
    if (!$user_id) {
        throw new Exception('Invalid user ID');
    }
    
    switch ($action) {
        case 'update_profile':
            updateUserProfile($conn, $user_id, $input);
            break;
            
        case 'change_password':
            changeUserPassword($conn, $user_id, $input);
            break;
            
        case 'update_status':
            updateUserStatus($conn, $user_id, $input);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function updateUserProfile($conn, $user_id, $data) {
    $firstname = mysqli_real_escape_string($conn, trim($data['firstname']));
    $lastname = mysqli_real_escape_string($conn, trim($data['lastname']));
    $middlename = mysqli_real_escape_string($conn, trim($data['middlename']));
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
        echo json_encode(['success' => true, 'message' => 'User profile updated successfully']);
    } else {
        throw new Exception('Failed to update user profile: ' . mysqli_error($conn));
    }
}

function changeUserPassword($conn, $user_id, $data) {
    $new_password = trim($data['new_password']);
    $confirm_password = trim($data['confirm_password']);
    
    if (empty($new_password) || empty($confirm_password)) {
        throw new Exception('Password fields cannot be empty');
    }
    
    if ($new_password !== $confirm_password) {
        throw new Exception('Passwords do not match');
    }
    
    if (strlen($new_password) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $sql = "UPDATE users SET password = ? WHERE id_user = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $hashed_password, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } else {
        throw new Exception('Failed to update password');
    }
}

function updateUserStatus($conn, $user_id, $data) {
    $status = $data['status'];
    
    if (!in_array($status, ['active', 'inactive'])) {
        throw new Exception('Invalid status value');
    }
    
    $sql = "UPDATE users SET status = ? WHERE id_user = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $status, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $status_text = $status === 'active' ? 'activated' : 'deactivated';
        echo json_encode(['success' => true, 'message' => "User account {$status_text} successfully"]);
    } else {
        throw new Exception('Failed to update user status');
    }
}
?>
