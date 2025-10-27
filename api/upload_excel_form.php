<?php
/**
 * Upload Excel Form for Timely Birth Registration
 * Handles both logged-in and non-logged-in users
 */

session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../db.php';

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Suppress errors for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    // Check if user is logged in
    $isLoggedIn = isset($_SESSION['name']);
    $userId = null;
    $requestorName = '';
    
    if ($isLoggedIn) {
        // Get user information if logged in
        $email = $_SESSION['name'];
        $query = "SELECT id_user, u_fn, u_ln FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if ($user) {
            $userId = $user['id_user'];
            $requestorName = $user['u_fn'] . ' ' . $user['u_ln'];
        }
    } else {
        // Get requestor information from form
        $requestorName = $_POST['requestor_name'] ?? '';
        $requestorEmail = $_POST['requestor_email'] ?? '';
        $requestorPhone = $_POST['requestor_phone'] ?? '';
        $requestorAddress = $_POST['requestor_address'] ?? '';
        
        if (empty($requestorName) || empty($requestorPhone) || empty($requestorAddress)) {
            throw new Exception('Missing required requestor information. Please provide your name, phone number, and address.');
        }
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred.');
    }
    
    $file = $_FILES['excel_file'];
    
    // Validate file type
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileExtension !== 'xlsx') {
        throw new Exception('Invalid file type. Only .xlsx files are allowed.');
    }
    
    // Validate file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception('File size too large. Maximum size is 10MB.');
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/timely_birth/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $timestamp = date('Y-m-d_H-i-s');
    $randomString = bin2hex(random_bytes(8));
    $filename = "timely_birth_{$timestamp}_{$randomString}.xlsx";
    $filePath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Failed to save uploaded file.');
    }
    
    // Read Excel file
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $data = $worksheet->toArray();
    
    // Generate submission number
    $submissionNumber = 'TBR-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Start database transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert submission record - handle NULL user_id for non-logged-in users
        if ($userId !== null) {
            // Logged-in user
            $insertSubmission = "INSERT INTO timely_birth_submissions (user_id, submission_number, excel_file_path, requestor_name, status) VALUES (?, ?, ?, ?, 'pending')";
            $stmt = mysqli_prepare($conn, $insertSubmission);
            mysqli_stmt_bind_param($stmt, "isss", $userId, $submissionNumber, $filePath, $requestorName);
        } else {
            // Non-logged-in user - user_id will be NULL
            $insertSubmission = "INSERT INTO timely_birth_submissions (user_id, submission_number, excel_file_path, requestor_name, status) VALUES (NULL, ?, ?, ?, 'pending')";
            $stmt = mysqli_prepare($conn, $insertSubmission);
            mysqli_stmt_bind_param($stmt, "sss", $submissionNumber, $filePath, $requestorName);
        }
        mysqli_stmt_execute($stmt);
        $submissionId = mysqli_insert_id($conn);
        
        // Process Excel data and save to database
        foreach ($data as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip header row
            
            foreach ($row as $colIndex => $cellValue) {
                if (!empty($cellValue)) {
                    $fieldName = "row_" . ($rowIndex + 1) . "_col_" . ($colIndex + 1);
                    $fieldType = is_numeric($cellValue) ? 'number' : 'text';
                    
                    $insertData = "INSERT INTO timely_birth_data (submission_id, field_name, field_value, field_type) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $insertData);
                    mysqli_stmt_bind_param($stmt, "isss", $submissionId, $fieldName, $cellValue, $fieldType);
                    mysqli_stmt_execute($stmt);
                }
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Excel form submitted successfully.',
            'submission_number' => $submissionNumber,
            'submission_id' => $submissionId
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        
        // Delete uploaded file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        throw $e;
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Excel upload error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
