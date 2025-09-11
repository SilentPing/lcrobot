<?php
// Start output buffering to prevent any output before headers
ob_start();

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || $_SESSION['usertype'] !== 'admin') {
    ob_end_clean();
    die('Unauthorized access');
}

// Clear output buffer
ob_end_clean();

// Get date parameters
$startDate = $_GET['start'] ?? date('Y-m-d');
$endDate = $_GET['end'] ?? date('Y-m-d');

// Generate report data
$reportData = generateReportData($startDate, $endDate);

// Create PDF using TCPDF
$title = "Civil Registry Report - " . date('M j', strtotime($startDate)) . " to " . date('M j, Y', strtotime($endDate));

// Include TCPDF
require_once(__DIR__ . '/../TCPDF-main/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Botolan Civil Registry');
$pdf->SetAuthor('LCRO Admin');
$pdf->SetTitle($title);
$pdf->SetSubject('Civil Registry Report');

// Set default header data
$pdf->SetHeaderData('', 0, 'BOTOLAN CIVIL REGISTRY', 'Municipality of Botolan, Zambales');

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

// Generate PDF content
generatePDFContent($pdf, $title, $reportData, $startDate, $endDate);

// Close and output PDF document
$pdf->Output('civil_registry_report_' . $startDate . '_to_' . $endDate . '.pdf', 'D');

function generateReportData($startDate, $endDate) {
    global $conn;
    
    $data = [];
    
    // Total requests in period
    $query = "SELECT COUNT(*) as total FROM reqtracking_tbl WHERE DATE(registration_date) BETWEEN '$startDate' AND '$endDate'";
    $result = mysqli_query($conn, $query);
    $data['total_requests'] = mysqli_fetch_assoc($result)['total'];
    
    // Requests by type
    $query = "SELECT type_request, COUNT(*) as count FROM reqtracking_tbl WHERE DATE(registration_date) BETWEEN '$startDate' AND '$endDate' GROUP BY type_request";
    $result = mysqli_query($conn, $query);
    $data['by_type'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data['by_type'][$row['type_request']] = $row['count'];
    }
    
    // Status breakdown
    $query = "SELECT status, COUNT(*) as count FROM reqtracking_tbl WHERE DATE(registration_date) BETWEEN '$startDate' AND '$endDate' GROUP BY status";
    $result = mysqli_query($conn, $query);
    $data['by_status'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data['by_status'][$row['status']] = $row['count'];
    }
    
    return $data;
}

function generatePDFContent($pdf, $title, $data, $startDate, $endDate) {
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 15, $title, 0, 1, 'C');
    $pdf->Ln(5);
    
    // Generated date
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'Generated on: ' . date('F j, Y \a\t g:i A'), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Summary section
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'SUMMARY', 0, 1, 'L');
    $pdf->Ln(5);
    
    // Summary data
    $pdf->SetFont('helvetica', '', 10);
    $summaryData = [
        'Total Requests' => $data['total_requests'],
        'Pending' => $data['by_status']['Pending'] ?? 0,
        'Approved' => $data['by_status']['Approved'] ?? 0,
        'Rejected' => $data['by_status']['Rejected'] ?? 0
    ];
    
    foreach ($summaryData as $label => $value) {
        $pdf->Cell(60, 8, $label . ':', 0, 0, 'L');
        $pdf->Cell(30, 8, $value, 0, 1, 'R');
    }
    
    $pdf->Ln(10);
    
    // Requests by Type
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'REQUESTS BY TYPE', 0, 1, 'L');
    $pdf->Ln(5);
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(80, 8, 'Request Type', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Count', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Percentage', 1, 1, 'C');
    
    // Table data
    $pdf->SetFont('helvetica', '', 9);
    $total = array_sum($data['by_type']);
    foreach ($data['by_type'] as $type => $count) {
        $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        $pdf->Cell(80, 8, $type, 1, 0, 'L');
        $pdf->Cell(30, 8, $count, 1, 0, 'C');
        $pdf->Cell(30, 8, $percentage . '%', 1, 1, 'C');
    }
    
    $pdf->Ln(10);
    
    // Status Breakdown
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'STATUS BREAKDOWN', 0, 1, 'L');
    $pdf->Ln(5);
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(80, 8, 'Status', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Count', 1, 1, 'C');
    
    // Table data
    $pdf->SetFont('helvetica', '', 9);
    foreach ($data['by_status'] as $status => $count) {
        $pdf->Cell(80, 8, $status, 1, 0, 'L');
        $pdf->Cell(30, 8, $count, 1, 1, 'C');
    }
    
    $pdf->Ln(15);
    
    // Footer
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'This report was generated by the Botolan Civil Registry Online Portal', 0, 1, 'C');
    $pdf->Cell(0, 5, 'For questions, contact the Civil Registry Office', 0, 1, 'C');
}
?>
