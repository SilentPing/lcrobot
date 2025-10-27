<?php
/**
 * Download Timely Birth Excel File
 */

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header('HTTP/1.1 401 Unauthorized');
    echo 'Unauthorized access';
    exit;
}

require_once __DIR__ . '/../db.php';

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
    $submission = mysqli_fetch_assoc($result);
    
    if (!$submission) {
        throw new Exception('Submission not found');
    }
    
    $filePath = $submission['excel_file_path'];
    
    if (!file_exists($filePath)) {
        throw new Exception('Excel file not found');
    }
    
    // Set headers for file download
    $filename = basename($filePath);
    $cleanFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $cleanFilename . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output the file
    readfile($filePath);
    exit;
    
} catch (Exception $e) {
    error_log("Download timely birth excel error: " . $e->getMessage());
    echo 'Error: ' . $e->getMessage();
}
?>
