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
    // Get parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $searchName = isset($_GET['searchName']) ? trim($_GET['searchName']) : '';
    $documentType = isset($_GET['documentType']) ? trim($_GET['documentType']) : '';
    $dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : '';
    $dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : '';
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $whereConditions = [];
    $params = [];
    $paramTypes = '';
    
    if (!empty($searchName)) {
        $whereConditions[] = "(u.u_fn LIKE ? OR u.u_ln LIKE ? OR CONCAT(u.u_fn, ' ', u.u_ln) LIKE ?)";
        $searchTerm = "%$searchName%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $paramTypes .= 'sss';
    }
    
    if (!empty($documentType)) {
        $whereConditions[] = "qc.document_type = ?";
        $params[] = $documentType;
        $paramTypes .= 's';
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "DATE(qc.claimed_at) >= ?";
        $params[] = $dateFrom;
        $paramTypes .= 's';
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "DATE(qc.claimed_at) <= ?";
        $params[] = $dateTo;
        $paramTypes .= 's';
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count for pagination
    $countQuery = "
        SELECT COUNT(*) as total
        FROM qr_claims qc
        LEFT JOIN qr_codes qr ON qc.qr_reference = qr.reference_number
        LEFT JOIN users u ON qr.user_id = u.id_user
        $whereClause
    ";
    
    $stmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $totalRecords = $stmt->get_result()->fetch_assoc()['total'];
    
    // Get claims data with pagination
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
            u.u_fn,
            u.u_ln,
            u.contact_no,
            CONCAT(u.u_fn, ' ', u.u_ln) as requestor_name
        FROM qr_claims qc
        LEFT JOIN qr_codes qr ON qc.qr_reference = qr.reference_number
        LEFT JOIN users u ON qr.user_id = u.id_user
        $whereClause
        ORDER BY qc.claimed_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    $paramTypes .= 'ii';
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $claims = [];
    while ($row = $result->fetch_assoc()) {
        $claims[] = $row;
    }
    
    // Get statistics
    $stats = [];
    
    // Total claims
    $result = $conn->query("SELECT COUNT(*) as total FROM qr_claims");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    // Today's claims
    $result = $conn->query("SELECT COUNT(*) as today FROM qr_claims WHERE DATE(claimed_at) = CURDATE()");
    $stats['today'] = $result->fetch_assoc()['today'];
    
    // This week's claims
    $result = $conn->query("SELECT COUNT(*) as week FROM qr_claims WHERE claimed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['week'] = $result->fetch_assoc()['week'];
    
    // This month's claims
    $result = $conn->query("SELECT COUNT(*) as month FROM qr_claims WHERE claimed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['month'] = $result->fetch_assoc()['month'];
    
    // Pagination info
    $totalPages = ceil($totalRecords / $limit);
    $pagination = [
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalRecords' => $totalRecords,
        'recordsPerPage' => $limit
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $claims,
        'statistics' => $stats,
        'pagination' => $pagination
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
