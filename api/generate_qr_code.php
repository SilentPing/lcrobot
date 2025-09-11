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
    $request_id = $input['request_id'] ?? null;
    $action = $input['action'] ?? 'generate';
    
    if (!$request_id) {
        throw new Exception('Request ID is required');
    }
    
    // Get request details
    $stmt = $conn->prepare("
        SELECT ar.*, u.u_fn, u.u_ln, u.contact_no, u.email 
        FROM approved_requests ar 
        LEFT JOIN users u ON ar.user_id = u.id_user 
        WHERE ar.request_id = ?
    ");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Request not found');
    }
    
    $request = $result->fetch_assoc();
    
    if ($action === 'generate') {
        // Generate new QR code
        $reference_number = generateReferenceNumber($request['type_request']);
        $qr_data = json_encode([
            'reference' => $reference_number,
            'type' => $request['type_request'],
            'date' => date('Y-m-d'),
            'expires' => date('Y-m-d', strtotime('+30 days'))
        ]);
        
        // Update approved_requests table
        $stmt = $conn->prepare("
            UPDATE approved_requests 
            SET qr_reference = ?, 
                qr_code_data = ?, 
                qr_generated_at = NOW(), 
                qr_expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY),
                qr_status = 'active'
            WHERE request_id = ?
        ");
        $stmt->bind_param("ssi", $reference_number, $qr_data, $request_id);
        $stmt->execute();
        
        // Insert into qr_codes table
        $stmt = $conn->prepare("
            INSERT INTO qr_codes (reference_number, document_type, user_id, qr_data, expires_at, created_by)
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), ?)
        ");
        $stmt->bind_param("ssisi", $reference_number, $request['type_request'], $request['user_id'], $qr_data, $_SESSION['id_user']);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'QR Code generated successfully',
            'data' => [
                'reference_number' => $reference_number,
                'qr_data' => $qr_data,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
                'qr_image' => generateQRImage($reference_number)
            ]
        ]);
        
    } elseif ($action === 'regenerate') {
        // Regenerate QR code (invalidate old one)
        $old_reference = $request['qr_reference'];
        
        // Mark old QR as expired
        $stmt = $conn->prepare("UPDATE qr_codes SET status = 'expired' WHERE reference_number = ?");
        $stmt->bind_param("s", $old_reference);
        $stmt->execute();
        
        // Generate new QR code
        $reference_number = generateReferenceNumber($request['type_request']);
        $qr_data = json_encode([
            'reference' => $reference_number,
            'type' => $request['type_request'],
            'date' => date('Y-m-d'),
            'expires' => date('Y-m-d', strtotime('+30 days'))
        ]);
        
        // Update approved_requests table
        $stmt = $conn->prepare("
            UPDATE approved_requests 
            SET qr_reference = ?, 
                qr_code_data = ?, 
                qr_generated_at = NOW(), 
                qr_expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY),
                qr_status = 'active'
            WHERE request_id = ?
        ");
        $stmt->bind_param("ssi", $reference_number, $qr_data, $request_id);
        $stmt->execute();
        
        // Insert new QR code
        $stmt = $conn->prepare("
            INSERT INTO qr_codes (reference_number, document_type, user_id, qr_data, expires_at, created_by)
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), ?)
        ");
        $stmt->bind_param("ssisi", $reference_number, $request['type_request'], $request['user_id'], $qr_data, $_SESSION['id_user']);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'QR Code regenerated successfully',
            'data' => [
                'reference_number' => $reference_number,
                'qr_data' => $qr_data,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
                'qr_image' => generateQRImage($reference_number)
            ]
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function generateReferenceNumber($document_type) {
    $prefix = 'LCRO-';
    $type_codes = [
        'Birth Certificate' => 'BIR',
        'Marriage Certificate' => 'MAR',
        'Death Certificate' => 'DEA',
        'CENOMAR' => 'CEN'
    ];
    
    $type_code = $type_codes[$document_type] ?? 'DOC';
    $year = date('Y');
    $random = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
    
    return $prefix . $type_code . '-' . $year . '-' . $random;
}

function generateQRImage($reference_number) {
    // Simple QR code generation using Google Charts API
    // In production, you might want to use a proper QR library
    $size = 200;
    $url = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($reference_number);
    
    return $url;
}
?>
