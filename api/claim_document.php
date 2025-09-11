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
    $input = json_decode(file_get_contents('php://input'), true);
    $reference = $input['reference'] ?? null;
    $notes = $input['notes'] ?? '';
    
    if (!$reference) {
        throw new Exception('Reference number is required');
    }
    
    $conn->begin_transaction();
    
    // Check if QR code exists and is active
    $stmt = $conn->prepare("
        SELECT qr.*, ar.request_id, u.u_fn, u.u_ln
        FROM qr_codes qr
        LEFT JOIN approved_requests ar ON qr.reference_number = ar.qr_reference
        LEFT JOIN users u ON qr.user_id = u.id_user
        WHERE qr.reference_number = ? AND qr.status = 'active'
    ");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('QR code not found or already claimed/expired');
    }
    
    $qr_data = $result->fetch_assoc();
    
    // Check if QR code is expired
    $now = new DateTime();
    $expires_at = new DateTime($qr_data['expires_at']);
    
    if ($now > $expires_at) {
        throw new Exception('QR code has expired');
    }
    
    // Update QR code status to claimed
    $stmt = $conn->prepare("
        UPDATE qr_codes 
        SET status = 'claimed', claimed_at = NOW() 
        WHERE reference_number = ?
    ");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    
    // Update approved_requests status
    $stmt = $conn->prepare("
        UPDATE approved_requests 
        SET qr_status = 'claimed', qr_claimed_at = NOW() 
        WHERE qr_reference = ?
    ");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    
    // Insert claim record
    $stmt = $conn->prepare("
        INSERT INTO qr_claims (qr_reference, claimed_by, admin_id, notes)
        VALUES (?, ?, ?, ?)
    ");
    $claimed_by = $qr_data['u_fn'] . ' ' . $qr_data['u_ln'];
    $admin_id = 1; // Default admin ID since we don't have user ID in session
    $stmt->bind_param("ssis", $reference, $claimed_by, $admin_id, $notes);
    $stmt->execute();
    
    // Update request status to released
    $stmt = $conn->prepare("
        UPDATE reqtracking_tbl 
        SET status = 'RELEASED' 
        WHERE user_id = ? AND type_request = ?
    ");
    $stmt->bind_param("is", $qr_data['user_id'], $qr_data['document_type']);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Document claimed successfully',
        'data' => [
            'reference_number' => $reference,
            'claimed_by' => $claimed_by,
            'claimed_at' => date('Y-m-d H:i:s'),
            'admin_id' => $admin_id
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
