<?php
session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || $_SESSION['usertype'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include TCPDF
require_once __DIR__ . '/../TCPDF-main/tcpdf.php';

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
    
    // Create PDF (use landscape for better table display)
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Civil Registry System');
    $pdf->SetAuthor('Botolan Civil Registry');
    $pdf->SetTitle('Claimed Documents Report');
    $pdf->SetSubject('Claimed Documents Export');
    
    // Set header data
    $pdf->SetHeaderData('', 0, 'LOCAL CIVIL REGISTRY OFFICE | BOTOLAN, ZAMBALES', 'Claimed Documents Report');
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins (reduced for better table fit)
    $pdf->SetMargins(10, PDF_MARGIN_TOP, 10);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 10);
    
    // Report title and filters
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 12, 'CLAIMED DOCUMENTS REPORT', 0, 1, 'C');
    $pdf->Ln(8);
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, 'Generated on: ' . date('F d, Y H:i:s'), 0, 1);
    $pdf->Cell(0, 6, 'Total Records: ' . count($claims), 0, 1);
    
    if (!empty($filters)) {
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'Applied Filters:', 0, 1);
        $pdf->SetFont('helvetica', '', 9);
        
        if (!empty($searchName)) {
            $pdf->Cell(0, 5, '• Search Name: ' . $searchName, 0, 1);
        }
        if (!empty($documentType)) {
            $pdf->Cell(0, 5, '• Document Type: ' . $documentType, 0, 1);
        }
        if (!empty($dateFrom)) {
            $pdf->Cell(0, 5, '• Date From: ' . $dateFrom, 0, 1);
        }
        if (!empty($dateTo)) {
            $pdf->Cell(0, 5, '• Date To: ' . $dateTo, 0, 1);
        }
    }
    
    $pdf->Ln(10);
    
    // Create table
    if (!empty($claims)) {
        // Table header
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(240, 240, 240);
        
        // Adjusted column widths for landscape mode
        $pdf->Cell(50, 8, 'QR Reference', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Requestor', 1, 0, 'C', true);
        $pdf->Cell(35, 8, 'Document Type', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Claimed By', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'Admin ID', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Claim Date', 1, 0, 'C', true);
        $pdf->Cell(35, 8, 'Notes', 1, 1, 'C', true);
        
        // Table data
        $pdf->SetFont('helvetica', '', 7);
        foreach ($claims as $claim) {
            // Truncate long QR references to fit in column
            $qrRef = strlen($claim['qr_reference']) > 22 ? 
                substr($claim['qr_reference'], 0, 19) . '...' : 
                $claim['qr_reference'];
            
            // Truncate long names to fit in column
            $requestorName = strlen($claim['requestor_name']) > 25 ? 
                substr($claim['requestor_name'], 0, 22) . '...' : 
                $claim['requestor_name'];
            
            $claimedBy = strlen($claim['claimed_by']) > 25 ? 
                substr($claim['claimed_by'], 0, 22) . '...' : 
                $claim['claimed_by'];
            
            $pdf->Cell(50, 6, $qrRef, 1, 0, 'C');
            $pdf->Cell(40, 6, $requestorName, 1, 0, 'L');
            $pdf->Cell(35, 6, $claim['document_type'], 1, 0, 'C');
            $pdf->Cell(40, 6, $claimedBy, 1, 0, 'L');
            $pdf->Cell(20, 6, $claim['admin_id'], 1, 0, 'C');
            $pdf->Cell(30, 6, date('M d, Y', strtotime($claim['claimed_at'])), 1, 0, 'C');
            $pdf->Cell(35, 6, substr($claim['notes'] ?: 'N/A', 0, 20), 1, 1, 'L');
        }
    } else {
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'No claimed documents found with the specified criteria.', 0, 1, 'C');
    }
    
    // Output PDF
    $filename = 'claimed_documents_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D'); // 'D' for download
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
