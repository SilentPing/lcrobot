<?php
session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || $_SESSION['usertype'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');

try {
    // Get statistics
    $stats = [];
    
    // Total QR codes
    $result = $conn->query("SELECT COUNT(*) as total FROM qr_codes");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    // Active QR codes
    $result = $conn->query("SELECT COUNT(*) as active FROM qr_codes WHERE status = 'active'");
    $stats['active'] = $result->fetch_assoc()['active'];
    
    // Claimed documents
    $result = $conn->query("SELECT COUNT(*) as claimed FROM qr_codes WHERE status = 'claimed'");
    $stats['claimed'] = $result->fetch_assoc()['claimed'];
    
    // Expired QR codes
    $result = $conn->query("SELECT COUNT(*) as expired FROM qr_codes WHERE status = 'expired'");
    $stats['expired'] = $result->fetch_assoc()['expired'];
    
    // Chart data
    $chartData = [];
    
    // Status distribution
    $result = $conn->query("
        SELECT status, COUNT(*) as count 
        FROM qr_codes 
        GROUP BY status
    ");
    $chartData['status'] = ['active' => 0, 'claimed' => 0, 'expired' => 0];
    while ($row = $result->fetch_assoc()) {
        $chartData['status'][$row['status']] = (int)$row['count'];
    }
    
    // Document types
    $result = $conn->query("
        SELECT document_type, COUNT(*) as count 
        FROM qr_codes 
        GROUP BY document_type
    ");
    $chartData['documentTypes'] = [];
    while ($row = $result->fetch_assoc()) {
        $chartData['documentTypes'][$row['document_type']] = (int)$row['count'];
    }
    
    // Daily claims (last 30 days)
    $result = $conn->query("
        SELECT DATE(claimed_at) as claim_date, COUNT(*) as count
        FROM qr_claims 
        WHERE claimed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(claimed_at)
        ORDER BY claim_date
    ");
    
    $dailyClaims = ['labels' => [], 'data' => []];
    $last30Days = [];
    
    // Generate last 30 days array
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $last30Days[$date] = 0;
    }
    
    // Fill in actual data
    while ($row = $result->fetch_assoc()) {
        $last30Days[$row['claim_date']] = (int)$row['count'];
    }
    
    foreach ($last30Days as $date => $count) {
        $dailyClaims['labels'][] = date('M d', strtotime($date));
        $dailyClaims['data'][] = $count;
    }
    
    $chartData['dailyClaims'] = $dailyClaims;
    
    // Recent activity
    $result = $conn->query("
        SELECT 
            'generated' as type,
            CONCAT('QR code generated for ', document_type) as description,
            generated_at as created_at
        FROM qr_codes 
        WHERE generated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        
        UNION ALL
        
        SELECT 
            'claimed' as type,
            CONCAT('Document claimed: ', qr_reference) as description,
            claimed_at as created_at
        FROM qr_claims 
        WHERE claimed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        
        ORDER BY created_at DESC
        LIMIT 10
    ");
    
    $recentActivity = [];
    while ($row = $result->fetch_assoc()) {
        $recentActivity[] = $row;
    }
    
    // QR codes for management table
    $result = $conn->query("
        SELECT 
            qr.reference_number,
            qr.document_type,
            qr.generated_at,
            qr.expires_at,
            qr.claimed_at,
            qr.status,
            u.u_fn,
            u.u_ln
        FROM qr_codes qr
        LEFT JOIN users u ON qr.user_id = u.id_user
        ORDER BY qr.generated_at DESC
        LIMIT 50
    ");
    
    $qrCodes = [];
    while ($row = $result->fetch_assoc()) {
        $qrCodes[] = [
            'reference_number' => $row['reference_number'],
            'document_type' => $row['document_type'],
            'generated_at' => $row['generated_at'],
            'expires_at' => $row['expires_at'],
            'claimed_at' => $row['claimed_at'],
            'status' => $row['status'],
            'requestor_name' => $row['u_fn'] . ' ' . $row['u_ln']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'statistics' => $stats,
        'chartData' => $chartData,
        'recentActivity' => $recentActivity,
        'qrCodes' => $qrCodes
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
