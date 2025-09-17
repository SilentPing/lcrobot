<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../TCPDF-main/tcpdf.php'; // TCPDF library

// Check if user is logged in
if (!isset($_SESSION['name'])) {
    die('Unauthorized access.');
}

$recordId = $_POST['record_id'] ?? '';
$recordType = $_POST['record_type'] ?? '';

if (empty($recordId) || empty($recordType)) {
    die('Invalid record ID or type.');
}

try {
    switch($recordType) {
        case 'birth':
            generateBirthCertificatePDF($recordId);
            break;
        case 'marriage':
            generateMarriageCertificatePDF($recordId);
            break;
        case 'death':
            generateDeathCertificatePDF($recordId);
            break;
        default:
            die('Invalid record type.');
    }
} catch (Exception $e) {
    die('Error generating PDF: ' . $e->getMessage());
}

function generateBirthCertificatePDF($id) {
    global $conn;
    
    // Fetch birth record data
    $stmt = $conn->prepare("SELECT * FROM birthceno_tbl WHERE id_birth_ceno = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$row = $result->fetch_assoc()) {
        throw new Exception('Birth record not found.');
    }
    
    // Create PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Civil Registry System');
    $pdf->SetAuthor('LCRO');
    $pdf->SetTitle('Birth Certificate - ' . $row['lastname'] . ', ' . $row['firstname']);
    $pdf->SetSubject('Birth Certificate');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 15, 'REPUBLIC OF THE PHILIPPINES', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'CERTIFICATE OF LIVE BIRTH', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 5, 'Civil Registry No. ' . $id, 0, 1, 'C');
    $pdf->Ln(10);
    
    // Personal Information
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'PERSONAL INFORMATION', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    
    $pdf->Cell(40, 6, 'Last Name:', 0, 0, 'L');
    $pdf->Cell(60, 6, $row['lastname'], 0, 0, 'L');
    $pdf->Cell(40, 6, 'First Name:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['firstname'], 0, 1, 'L');
    
    $pdf->Cell(40, 6, 'Middle Name:', 0, 0, 'L');
    $pdf->Cell(60, 6, $row['middlename'] ?: 'N/A', 0, 0, 'L');
    $pdf->Cell(40, 6, 'Sex:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['sex'], 0, 1, 'L');
    
    $pdf->Cell(40, 6, 'Date of Birth:', 0, 0, 'L');
    $pdf->Cell(60, 6, date('F j, Y', strtotime($row['dob'])), 0, 0, 'L');
    $pdf->Cell(40, 6, 'Place of Birth:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['pob_municipality'] . ', ' . $row['pob_province'], 0, 1, 'L');
    
    $pdf->Ln(5);
    
    // Parents Information
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'PARENTS INFORMATION', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    
    $pdf->Cell(40, 6, 'Father:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['fath_ln'] . ', ' . $row['fath_fn'] . ' ' . ($row['fath_mn'] ?: ''), 0, 1, 'L');
    
    $pdf->Cell(40, 6, 'Mother:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['moth_maiden_ln'] . ', ' . $row['moth_maiden_fn'] . ' ' . ($row['moth_maiden_mn'] ?: ''), 0, 1, 'L');
    
    $pdf->Ln(10);
    
    // Footer
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'This is to certify that the above information is true and correct based on the records of this office.', 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->Cell(0, 5, 'Issued this ' . date('jS') . ' day of ' . date('F Y') . ' at the Local Civil Registry Office.', 0, 1, 'C');
    $pdf->Ln(15);
    
    $pdf->Cell(0, 5, '_________________________', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Civil Registrar', 0, 1, 'C');
    
    // Output PDF
    $filename = 'Birth_Certificate_' . $row['lastname'] . '_' . $row['firstname'] . '_' . date('Y-m-d') . '.pdf';
    $pdf->Output($filename, 'D');
}

function generateMarriageCertificatePDF($id) {
    global $conn;
    
    // Fetch marriage record data
    $stmt = $conn->prepare("SELECT * FROM marriage_tbl WHERE id_marriage = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$row = $result->fetch_assoc()) {
        throw new Exception('Marriage record not found.');
    }
    
    // Create PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Civil Registry System');
    $pdf->SetAuthor('LCRO');
    $pdf->SetTitle('Marriage Certificate - ' . $row['husband_ln'] . ' & ' . $row['maiden_wife_ln']);
    $pdf->SetSubject('Marriage Certificate');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 15, 'REPUBLIC OF THE PHILIPPINES', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'CERTIFICATE OF MARRIAGE', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 5, 'Civil Registry No. ' . $id, 0, 1, 'C');
    $pdf->Ln(10);
    
    // Marriage Information
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'MARRIAGE INFORMATION', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    
    $pdf->Cell(40, 6, 'Date of Marriage:', 0, 0, 'L');
    $pdf->Cell(0, 6, date('F j, Y', strtotime($row['marriage_date'])), 0, 1, 'L');
    
    $pdf->Cell(40, 6, 'Place of Marriage:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['place_of_marriage'], 0, 1, 'L');
    
    $pdf->Ln(5);
    
    // Husband Information
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'HUSBAND INFORMATION', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    
    $pdf->Cell(40, 6, 'Last Name:', 0, 0, 'L');
    $pdf->Cell(60, 6, $row['husband_ln'], 0, 0, 'L');
    $pdf->Cell(40, 6, 'First Name:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['husband_fn'], 0, 1, 'L');
    
    $pdf->Cell(40, 6, 'Middle Name:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['husband_mn'] ?: 'N/A', 0, 1, 'L');
    
    $pdf->Ln(5);
    
    // Wife Information
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'WIFE INFORMATION', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    
    $pdf->Cell(40, 6, 'Maiden Last Name:', 0, 0, 'L');
    $pdf->Cell(60, 6, $row['maiden_wife_ln'], 0, 0, 'L');
    $pdf->Cell(40, 6, 'Maiden First Name:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['maiden_wife_fn'], 0, 1, 'L');
    
    $pdf->Cell(40, 6, 'Maiden Middle Name:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['maiden_wife_mn'] ?: 'N/A', 0, 1, 'L');
    
    $pdf->Ln(10);
    
    // Footer
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'This is to certify that the above information is true and correct based on the records of this office.', 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->Cell(0, 5, 'Issued this ' . date('jS') . ' day of ' . date('F Y') . ' at the Local Civil Registry Office.', 0, 1, 'C');
    $pdf->Ln(15);
    
    $pdf->Cell(0, 5, '_________________________', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Civil Registrar', 0, 1, 'C');
    
    // Output PDF
    $filename = 'Marriage_Certificate_' . $row['husband_ln'] . '_' . $row['maiden_wife_ln'] . '_' . date('Y-m-d') . '.pdf';
    $pdf->Output($filename, 'D');
}

function generateDeathCertificatePDF($id) {
    global $conn;
    
    // Fetch death record data
    $stmt = $conn->prepare("SELECT * FROM death_tbl WHERE id_death = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$row = $result->fetch_assoc()) {
        throw new Exception('Death record not found.');
    }
    
    // Create PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Civil Registry System');
    $pdf->SetAuthor('LCRO');
    $pdf->SetTitle('Death Certificate - ' . $row['deceased_ln'] . ', ' . $row['deceased_fn']);
    $pdf->SetSubject('Death Certificate');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 15, 'REPUBLIC OF THE PHILIPPINES', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'CERTIFICATE OF DEATH', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 5, 'Civil Registry No. ' . $id, 0, 1, 'C');
    $pdf->Ln(10);
    
    // Deceased Information
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'DECEASED INFORMATION', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    
    $pdf->Cell(40, 6, 'Last Name:', 0, 0, 'L');
    $pdf->Cell(60, 6, $row['deceased_ln'], 0, 0, 'L');
    $pdf->Cell(40, 6, 'First Name:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['deceased_fn'], 0, 1, 'L');
    
    $pdf->Cell(40, 6, 'Middle Name:', 0, 0, 'L');
    $pdf->Cell(60, 6, $row['deceased_mn'] ?: 'N/A', 0, 0, 'L');
    $pdf->Cell(40, 6, 'Date of Birth:', 0, 0, 'L');
    $pdf->Cell(0, 6, date('F j, Y', strtotime($row['dob'])), 0, 1, 'L');
    
    $pdf->Cell(40, 6, 'Date of Death:', 0, 0, 'L');
    $pdf->Cell(60, 6, date('F j, Y', strtotime($row['dod'])), 0, 0, 'L');
    $pdf->Cell(40, 6, 'Place of Death:', 0, 0, 'L');
    $pdf->Cell(0, 6, $row['place_of_death'], 0, 1, 'L');
    
    $pdf->Ln(10);
    
    // Footer
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'This is to certify that the above information is true and correct based on the records of this office.', 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->Cell(0, 5, 'Issued this ' . date('jS') . ' day of ' . date('F Y') . ' at the Local Civil Registry Office.', 0, 1, 'C');
    $pdf->Ln(15);
    
    $pdf->Cell(0, 5, '_________________________', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Civil Registrar', 0, 1, 'C');
    
    // Output PDF
    $filename = 'Death_Certificate_' . $row['deceased_ln'] . '_' . $row['deceased_fn'] . '_' . date('Y-m-d') . '.pdf';
    $pdf->Output($filename, 'D');
}
?>
