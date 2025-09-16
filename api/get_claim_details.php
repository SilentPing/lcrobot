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
    $qrReference = isset($_GET['qr_reference']) ? trim($_GET['qr_reference']) : '';
    
    
    if (empty($qrReference)) {
        throw new Exception('QR reference is required');
    }
    
    // Get detailed claim information
    $query = "
        SELECT 
            qc.qr_reference,
            qc.claimed_by,
            qc.admin_id,
            qc.claimed_at,
            qc.notes,
            qr.document_type,
            qr.generated_at,
            qr.expires_at,
            qr.status as qr_status,
            qr.user_id,
            u.u_fn,
            u.u_ln,
            u.contact_no,
            u.email,
            CONCAT(u.u_fn, ' ', u.u_ln) as requestor_name
        FROM qr_claims qc
        LEFT JOIN qr_codes qr ON qc.qr_reference = qr.reference_number
        LEFT JOIN users u ON qr.user_id = u.id_user
        WHERE qc.qr_reference = ?
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $qrReference);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Failed to get result: ' . $stmt->error);
    }
    
    if ($result->num_rows === 0) {
        throw new Exception('Claim not found for QR reference: ' . $qrReference);
    }
    
    $claimDetails = $result->fetch_assoc();
    if (!$claimDetails) {
        throw new Exception('Failed to fetch claim details');
    }
    
    // Get additional request information from reqtracking_tbl if available
    if (isset($claimDetails['user_id']) && isset($claimDetails['document_type'])) {
        $requestQuery = "
            SELECT 
                rt.status,
                rt.registrar_name,
                rt.registration_date
            FROM reqtracking_tbl rt
            WHERE rt.user_id = ? AND rt.type_request = ?
        ";
        
        $stmt = $conn->prepare($requestQuery);
        if ($stmt) {
            $stmt->bind_param("is", $claimDetails['user_id'], $claimDetails['document_type']);
            if ($stmt->execute()) {
                $requestResult = $stmt->get_result();
                if ($requestResult && $requestResult->num_rows > 0) {
                    $requestData = $requestResult->fetch_assoc();
                    $claimDetails = array_merge($claimDetails, $requestData);
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $claimDetails
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'qr_reference' => $qrReference ?? 'not set',
            'error_line' => $e->getLine(),
            'error_file' => basename($e->getFile())
        ]
    ]);
}
?>
