<?php
/**
 * Get Timely Birth Records for Admin
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
    // Get all timely birth submissions
    $query = "SELECT * FROM timely_birth_submissions ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }
    
    $records = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = [
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'submission_number' => $row['submission_number'],
            'excel_file_path' => $row['excel_file_path'],
            'requestor_name' => $row['requestor_name'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'records' => $records
    ]);
    
} catch (Exception $e) {
    error_log("Get timely birth records error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
