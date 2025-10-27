<?php
/**
 * Get Timely Birth Statistics for Admin
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
    // Get total count
    $totalQuery = "SELECT COUNT(*) as total FROM timely_birth_submissions";
    $totalResult = mysqli_query($conn, $totalQuery);
    $total = mysqli_fetch_assoc($totalResult)['total'];
    
    // Get counts by status
    $statusQuery = "SELECT status, COUNT(*) as count FROM timely_birth_submissions GROUP BY status";
    $statusResult = mysqli_query($conn, $statusQuery);
    
    $stats = [
        'total' => $total,
        'pending' => 0,
        'processing' => 0,
        'completed' => 0,
        'rejected' => 0
    ];
    
    while ($row = mysqli_fetch_assoc($statusResult)) {
        $stats[$row['status']] = $row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Get timely birth stats error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
