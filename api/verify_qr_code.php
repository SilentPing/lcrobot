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
    
    if (!$reference) {
        throw new Exception('Reference number is required');
    }
    
    // Get QR code details
    $stmt = $conn->prepare("
        SELECT 
            qr.reference_number,
            qr.document_type,
            qr.generated_at,
            qr.expires_at,
            qr.claimed_at,
            qr.status,
            ar.request_id,
            ar.user_id,
            u.u_fn,
            u.u_ln,
            u.contact_no,
            u.email
        FROM qr_codes qr
        LEFT JOIN approved_requests ar ON qr.reference_number = ar.qr_reference
        LEFT JOIN users u ON qr.user_id = u.id_user
        WHERE qr.reference_number = ?
    ");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('QR code not found or invalid');
    }
    
    $qr_data = $result->fetch_assoc();
    
    // Check if QR code is expired
    $now = new DateTime();
    $expires_at = new DateTime($qr_data['expires_at']);
    
    if ($now > $expires_at && $qr_data['status'] === 'active') {
        // Mark as expired
        $stmt = $conn->prepare("UPDATE qr_codes SET status = 'expired' WHERE reference_number = ?");
        $stmt->bind_param("s", $reference);
        $stmt->execute();
        
        $stmt = $conn->prepare("UPDATE approved_requests SET qr_status = 'expired' WHERE qr_reference = ?");
        $stmt->bind_param("s", $reference);
        $stmt->execute();
        
        $qr_data['status'] = 'expired';
    }
    
    // Get document-specific details
    $document_details = getDocumentDetails($qr_data['document_type'], $qr_data['user_id'], $conn);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'reference_number' => $qr_data['reference_number'],
            'document_type' => $qr_data['document_type'],
            'status' => $qr_data['status'],
            'generated_at' => $qr_data['generated_at'],
            'expires_at' => $qr_data['expires_at'],
            'claimed_at' => $qr_data['claimed_at'],
            'requestor_name' => $qr_data['u_fn'] . ' ' . $qr_data['u_ln'],
            'contact_no' => $qr_data['contact_no'],
            'email' => $qr_data['email'],
            'document_details' => $document_details
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getDocumentDetails($document_type, $user_id, $conn) {
    $details = [];
    
    switch ($document_type) {
        case 'Birth Certificate':
            $stmt = $conn->prepare("
                SELECT firstname, lastname, middlename, dob, pob_municipality, 
                       fath_ln, fath_fn, fath_mn, moth_maiden_ln, moth_maiden_fn, moth_maiden_mn, purpose_of_request
                FROM birthceno_tbl 
                WHERE id_user = ? 
                ORDER BY id_birth_ceno DESC 
                LIMIT 1
            ");
            break;
            
        case 'Marriage Certificate':
            $stmt = $conn->prepare("
                SELECT husband_fn, husband_ln, husband_mn, maiden_wife_fn, maiden_wife_ln, 
                       maiden_wife_mn, marriage_date, place_of_marriage, purpose_of_request
                FROM marriage_tbl 
                WHERE id_user = ? 
                ORDER BY id_marriage DESC 
                LIMIT 1
            ");
            break;
            
        case 'Death Certificate':
            $stmt = $conn->prepare("
                SELECT deceased_fn, deceased_ln, deceased_mn, death_date, place_of_death, 
                       cause_of_death, purpose_of_request
                FROM death_tbl 
                WHERE id_user = ? 
                ORDER BY id_death DESC 
                LIMIT 1
            ");
            break;
            
        case 'CENOMAR':
            $stmt = $conn->prepare("
                SELECT firstname, lastname, middlename, dob, pob_municipality, 
                       fath_ln, fath_fn, fath_mn, moth_maiden_ln, moth_maiden_fn, moth_maiden_mn, purpose_of_request
                FROM birthceno_tbl 
                WHERE id_user = ? 
                ORDER BY id_birth_ceno DESC 
                LIMIT 1
            ");
            break;
            
        default:
            return $details;
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $details = $result->fetch_assoc();
    }
    
    return $details;
}
?>
