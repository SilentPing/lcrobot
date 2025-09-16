<?php
session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || $_SESSION['usertype'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Get filters from URL parameters
    $filters = isset($_GET['filters']) ? json_decode($_GET['filters'], true) : [];
    $searchName = $filters['searchName'] ?? '';
    $documentType = $filters['documentType'] ?? '';
    $dateFrom = $filters['dateFrom'] ?? '';
    $dateTo = $filters['dateTo'] ?? '';
    
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
    
    // Get claims data
    $query = "
        SELECT 
            qc.qr_reference,
            qc.claimed_by,
            qc.admin_id,
            qc.claimed_at,
            qc.notes,
            qr.document_type,
            u.u_fn,
            u.u_ln,
            u.contact_no,
            CONCAT(u.u_fn, ' ', u.u_ln) as requestor_name
        FROM qr_claims qc
        LEFT JOIN qr_codes qr ON qc.qr_reference = qr.reference_number
        LEFT JOIN users u ON qr.user_id = u.id_user
        $whereClause
        ORDER BY qc.claimed_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $claims = [];
    while ($row = $result->fetch_assoc()) {
        $claims[] = $row;
    }
    
    // Set headers for Excel download
    $filename = 'claimed_documents_report_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write header information
    fputcsv($output, ['LOCAL CIVIL REGISTRY OFFICE | BOTOLAN, ZAMBALES']);
    fputcsv($output, ['CLAIMED DOCUMENTS REPORT']);
    fputcsv($output, ['Generated on: ' . date('F d, Y H:i:s')]);
    fputcsv($output, ['Total Records: ' . count($claims)]);
    fputcsv($output, []); // Empty row
    
    // Write filters if any
    if (!empty($filters)) {
        fputcsv($output, ['Applied Filters:']);
        if (!empty($searchName)) {
            fputcsv($output, ['Search Name: ' . $searchName]);
        }
        if (!empty($documentType)) {
            fputcsv($output, ['Document Type: ' . $documentType]);
        }
        if (!empty($dateFrom)) {
            fputcsv($output, ['Date From: ' . $dateFrom]);
        }
        if (!empty($dateTo)) {
            fputcsv($output, ['Date To: ' . $dateTo]);
        }
        fputcsv($output, []); // Empty row
    }
    
    // Write column headers
    fputcsv($output, [
        'QR Reference',
        'Requestor Name',
        'Document Type',
        'Claimed By',
        'Admin ID',
        'Claim Date',
        'Claim Time',
        'Contact Number',
        'Notes'
    ]);
    
    // Write data rows
    foreach ($claims as $claim) {
        fputcsv($output, [
            $claim['qr_reference'],
            $claim['requestor_name'],
            $claim['document_type'],
            $claim['claimed_by'],
            $claim['admin_id'],
            date('Y-m-d', strtotime($claim['claimed_at'])),
            date('H:i:s', strtotime($claim['claimed_at'])),
            $claim['contact_no'] ?: 'N/A',
            $claim['notes'] ?: 'N/A'
        ]);
    }
    
    // Close output stream
    fclose($output);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
