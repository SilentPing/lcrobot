<?php
/**
 * Export Pricing API
 * This API endpoint exports pricing data as CSV or PDF
 */

session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    http_response_code(401);
    echo "Unauthorized access - Admin required";
    exit;
}

// Get export format (default to CSV)
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'csv';

try {
    // Fetch pricing data
    $query = "SELECT * FROM document_pricing ORDER BY document_type, form_type";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Failed to fetch pricing data: ' . mysqli_error($conn));
    }
    
    // Prepare data array
    $pricingData = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pricingData[] = $row;
    }
    
    if ($format === 'pdf') {
        exportAsPDF($pricingData);
    } else {
        exportAsCSV($pricingData);
    }
    
} catch (Exception $e) {
    // If there's an error, output it as plain text
    header('Content-Type: text/plain');
    echo "Error exporting pricing data: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}

function exportAsCSV($data) {
    // Set headers for CSV download
    $filename = 'document_pricing_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create file pointer
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'ID',
        'Document Type',
        'Form Type',
        'Form Number',
        'Price (₱)',
        'Description',
        'Status',
        'Created At',
        'Updated At'
    ]);
    
    // Add data rows
    foreach ($data as $row) {
        fputcsv($output, [
            $row['id'],
            $row['document_type'],
            $row['form_type'],
            $row['form_number'],
            $row['price'],
            $row['description'],
            $row['is_active'] ? 'Active' : 'Inactive',
            $row['created_at'],
            $row['updated_at']
        ]);
    }
    
    fclose($output);
}

function exportAsPDF($data) {
    // Include TCPDF library
    require_once(__DIR__ . '/../TCPDF-main/tcpdf.php');
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Botolan Civil Registry Online Portal');
    $pdf->SetAuthor('Civil Registry Office');
    $pdf->SetTitle('Document Pricing Report');
    $pdf->SetSubject('Civil Registry Document Pricing');
    $pdf->SetKeywords('Civil Registry, Document Pricing, Botolan');
    
    // Set default header data with logos
    $pdf->SetHeaderData('', 0, 'Botolan Civil Registry Online Portal', 'Document Pricing Report');
    
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
    
    // Add logos at the top
    $pdf->SetY(20);
    
    // LGU Botolan Logo (left side)
    $lgu_logo_path = __DIR__ . '/../assets/images/lgu2.png';
    if (file_exists($lgu_logo_path)) {
        $pdf->Image($lgu_logo_path, 15, 20, 25, 25, 'PNG');
    } else {
        // Fallback text if logo not found
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(15, 25);
        $pdf->Cell(25, 10, 'LGU BOTOLAN', 0, 0, 'C');
    }
    
    // LCRO Logo (right side)
    $lcro_logo_path = __DIR__ . '/../assets/images/lcrobot.png';
    if (file_exists($lcro_logo_path)) {
        $pdf->Image($lcro_logo_path, 170, 20, 25, 25, 'PNG');
    } else {
        // Fallback text if logo not found
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(170, 25);
        $pdf->Cell(25, 10, 'LCRO', 0, 0, 'C');
    }
    
    // Set font for title
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title (centered)
    $pdf->SetY(50);
    $pdf->Cell(0, 15, 'Document Pricing Report', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Generation date
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'Generated on: ' . date('F j, Y \a\t g:i A'), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Summary statistics
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Summary Statistics', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    $totalItems = count($data);
    $activeItems = count(array_filter($data, function($item) { return $item['is_active']; }));
    $originalDocs = count(array_filter($data, function($item) { return $item['form_type'] === 'original'; }));
    $transcriptions = count(array_filter($data, function($item) { return $item['form_type'] === 'transcription'; }));
    
    $summary = "Total Items: $totalItems | Active Items: $activeItems | Original Documents: $originalDocs | Transcriptions: $transcriptions";
    $pdf->Cell(0, 10, $summary, 0, 1, 'L');
    $pdf->Ln(10);
    
    // Create centered table
    $pdf->SetFont('helvetica', 'B', 10);
    
    // Table headers
    $headers = array('Document Type', 'Form Type', 'Form Number', 'Price', 'Status');
    $widths = array(50, 30, 25, 25, 20);
    
    // Calculate total table width
    $total_width = array_sum($widths);
    
    // Calculate starting X position to center the table
    $page_width = $pdf->getPageWidth();
    $margin_left = $pdf->getMargins()['left'];
    $available_width = $page_width - ($margin_left * 2);
    $start_x = $margin_left + (($available_width - $total_width) / 2);
    
    // Set starting position for centered table
    $pdf->SetX($start_x);
    
    // Header row
    for ($i = 0; $i < count($headers); $i++) {
        $pdf->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C', true);
    }
    $pdf->Ln();
    
    // Table data
    $pdf->SetFont('helvetica', '', 9);
    foreach ($data as $row) {
        $status = $row['is_active'] ? 'Active' : 'Inactive';
        $formType = ucfirst($row['form_type']);
        
        // Set X position for each row to maintain centering
        $pdf->SetX($start_x);
        
        $pdf->Cell($widths[0], 8, $row['document_type'], 1, 0, 'L');
        $pdf->Cell($widths[1], 8, $formType, 1, 0, 'C');
        $pdf->Cell($widths[2], 8, 'Form ' . $row['form_number'], 1, 0, 'C');
        $pdf->Cell($widths[3], 8, '₱' . number_format($row['price'], 2), 1, 0, 'R');
        $pdf->Cell($widths[4], 8, $status, 1, 0, 'C');
        $pdf->Ln();
    }
    
    // Add footer note
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 10, 'This report was generated by the Botolan Civil Registry Online Portal', 0, 1, 'C');
    $pdf->Cell(0, 10, 'For questions or concerns, please contact the Civil Registry Office', 0, 1, 'C');
    
    // Set headers for PDF download
    $filename = 'document_pricing_' . date('Y-m-d_H-i-s') . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output PDF
    $pdf->Output($filename, 'D');
}

?>
