<?php
/**
 * Get Timely Birth Submission Details for Admin
 */

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../db.php';

// Suppress errors for clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

try {
    $submissionId = $_GET['id'] ?? null;
    
    if (!$submissionId) {
        throw new Exception('Submission ID is required');
    }
    
    // Get submission details
    $submissionQuery = "SELECT * FROM timely_birth_submissions WHERE id = ?";
    $stmt = mysqli_prepare($conn, $submissionQuery);
    mysqli_stmt_bind_param($stmt, "i", $submissionId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $details = mysqli_fetch_assoc($result);
    
    if (!$details) {
        throw new Exception('Submission not found');
    }
    
    // Get submission data
    $dataQuery = "SELECT * FROM timely_birth_data WHERE submission_id = ? ORDER BY id";
    $stmt = mysqli_prepare($conn, $dataQuery);
    mysqli_stmt_bind_param($stmt, "i", $submissionId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'field_name' => $row['field_name'],
            'field_value' => $row['field_value'],
            'field_type' => $row['field_type']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'details' => $details,
        'data' => $data,
        'data_count' => count($data)
    ]);
    
} catch (Exception $e) {
    error_log("Get timely birth details error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
