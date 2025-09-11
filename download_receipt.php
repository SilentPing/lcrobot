<?php
require_once __DIR__ . '/db.php';

$reference = $_GET['ref'] ?? '';

if (empty($reference)) {
    die('Reference number is required');
}

try {
    // Get document details
    $stmt = $conn->prepare("
        SELECT 
            qr.reference_number,
            qr.document_type,
            qr.generated_at,
            qr.expires_at,
            qr.claimed_at,
            qr.status,
            ar.request_id,
            u.u_fn,
            u.u_ln,
            u.contact_no,
            u.email,
            u.house_no,
            u.street_brgy,
            u.city_municipality,
            u.province
        FROM qr_codes qr
        LEFT JOIN approved_requests ar ON qr.reference_number = ar.qr_reference
        LEFT JOIN users u ON qr.user_id = u.id_user
        WHERE qr.reference_number = ?
    ");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die('Document not found');
    }
    
    $document = $result->fetch_assoc();
    $stmt->close();
    
    // Generate PDF receipt using TCPDF
    require_once __DIR__ . '/TCPDF-main/tcpdf.php';
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('MCRO Botolan');
    $pdf->SetAuthor('Local Civil Registry Office');
    $pdf->SetTitle('Document Receipt - ' . $reference);
    $pdf->SetSubject('Civil Registry Document Receipt');
    
    // Set default header data
    $pdf->SetHeaderData('', 0, 'LOCAL CIVIL REGISTRY OFFICE | BOTOLAN, ZAMBALES', 'Document Receipt');
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 15, 'LOCAL CIVIL REGISTRY OFFICE', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Document Receipt', 0, 1, 'C');
    $pdf->Ln(10);
    
    // QR Code section
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'QR Code for Verification:', 0, 1, 'C');
    
    // Generate QR code image
    $qr_image_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($reference);
    
    // Add QR code image
    $pdf->Image($qr_image_url, 80, $pdf->GetY(), 50, 50, 'PNG');
    $pdf->Ln(55);
    
    // Reference number
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, $reference, 0, 1, 'C');
    $pdf->Ln(10);
    
    // Document details
    $pdf->SetFont('helvetica', '', 10);
    
    $details = [
        'Document Type' => $document['document_type'],
        'Requestor' => $document['u_fn'] . ' ' . $document['u_ln'],
        'Contact' => $document['contact_no'],
        'Generated' => date('M d, Y H:i', strtotime($document['generated_at'])),
        'Expires' => date('M d, Y H:i', strtotime($document['expires_at']))
    ];
    
    foreach ($details as $label => $value) {
        $pdf->Cell(40, 6, $label . ':', 0, 0, 'L');
        $pdf->Cell(0, 6, $value, 0, 1, 'L');
    }
    
    $pdf->Ln(10);
    
    // Status
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Status: ' . strtoupper($document['status']), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Instructions
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 8, 'Claiming Instructions:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    $instructions = [
        '1. Present this receipt at the LCRO counter',
        '2. Show valid ID for verification',
        '3. QR code will be scanned for validation',
        '4. Document will be released upon verification'
    ];
    
    foreach ($instructions as $instruction) {
        $pdf->Cell(0, 6, $instruction, 0, 1, 'L');
    }
    
    $pdf->Ln(15);
    
    // Footer
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 6, 'Local Civil Registry Office', 0, 1, 'C');
    $pdf->Cell(0, 6, 'Municipality of Botolan, Zambales', 0, 1, 'C');
    $pdf->Cell(0, 6, 'Generated on ' . date('F d, Y \a\t H:i A'), 0, 1, 'C');
    $pdf->Cell(0, 6, 'For inquiries, contact: 090-5280-3518', 0, 1, 'C');
    
    // Output PDF
    $pdf->Output('receipt_' . $reference . '.pdf', 'D');
    
} catch (Exception $e) {
    error_log("PDF Download Error: " . $e->getMessage());
    die('Error generating PDF: ' . $e->getMessage());
}
?>
