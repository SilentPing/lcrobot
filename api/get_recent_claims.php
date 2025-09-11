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
    // Get recent claims (last 10)
    $stmt = $conn->prepare("
        SELECT 
            qc.qr_reference as reference,
            qc.claimed_at,
            qc.claimed_by,
            qc.notes,
            qr.document_type,
            u.u_fn,
            u.u_ln
        FROM qr_claims qc
        LEFT JOIN qr_codes qr ON qc.qr_reference = qr.reference_number
        LEFT JOIN users u ON qr.user_id = u.id_user
        ORDER BY qc.claimed_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $claims = [];
    while ($row = $result->fetch_assoc()) {
        $claims[] = [
            'reference' => $row['reference'],
            'document_type' => $row['document_type'],
            'claimed_by' => $row['claimed_by'],
            'claimed_at' => $row['claimed_at'],
            'notes' => $row['notes'],
            'requestor_name' => $row['u_fn'] . ' ' . $row['u_ln']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $claims
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
