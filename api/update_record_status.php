<?php
session_start();
require_once __DIR__ . '/../db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is LCRO Staff
if (!isset($_SESSION['usertype']) || ($_SESSION['usertype'] != 'admin' && $_SESSION['usertype'] != 'staff')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Only LCRO staff can update record status.']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $recordId = $input['id'] ?? '';
    $recordType = $input['type'] ?? '';
    $status = $input['status'] ?? '';
    
    if (empty($recordId) || empty($recordType) || empty($status)) {
        throw new Exception('Record ID, type, and status are required.');
    }
    
    if (!in_array($status, ['APPROVED', 'REJECTED', 'PENDING'])) {
        throw new Exception('Invalid status value.');
    }
    
    switch($recordType) {
        case 'birth':
            $result = updateBirthRecordStatus($recordId, $status);
            break;
        case 'marriage':
            $result = updateMarriageRecordStatus($recordId, $status);
            break;
        case 'death':
            $result = updateDeathRecordStatus($recordId, $status);
            break;
        default:
            throw new Exception('Invalid record type.');
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function updateBirthRecordStatus($id, $status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE birthceno_tbl SET status_request = ? WHERE id_birth_ceno = ?");
    $stmt->bind_param("si", $status, $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update birth record status: ' . $stmt->error);
    }
    
    $stmt->close();
    return ['success' => true, 'message' => 'Birth record status updated successfully!'];
}

function updateMarriageRecordStatus($id, $status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE marriage_tbl SET status_request = ? WHERE id_marriage = ?");
    $stmt->bind_param("si", $status, $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update marriage record status: ' . $stmt->error);
    }
    
    $stmt->close();
    return ['success' => true, 'message' => 'Marriage record status updated successfully!'];
}

function updateDeathRecordStatus($id, $status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE death_tbl SET status_request = ? WHERE id_death = ?");
    $stmt->bind_param("si", $status, $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update death record status: ' . $stmt->error);
    }
    
    $stmt->close();
    return ['success' => true, 'message' => 'Death record status updated successfully!'];
}
?>
