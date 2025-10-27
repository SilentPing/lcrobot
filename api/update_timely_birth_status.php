<?php
/**
 * Update Timely Birth Submission Status
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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    $submissionId = $input['submission_id'] ?? null;
    $status = $input['status'] ?? null;
    $notes = $input['notes'] ?? '';
    
    if (!$submissionId || !$status) {
        throw new Exception('Submission ID and status are required');
    }
    
    // Validate status
    $validStatuses = ['pending', 'processing', 'completed', 'rejected'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status');
    }
    
    // Update status
    $updateQuery = "UPDATE timely_birth_submissions SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "si", $status, $submissionId);
    mysqli_stmt_execute($stmt);
    
    if (mysqli_affected_rows($conn) === 0) {
        throw new Exception('No records updated');
    }
    
    // Log the status change (optional - you can create a status_logs table if needed)
    // For now, we'll just return success
    
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Update timely birth status error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
