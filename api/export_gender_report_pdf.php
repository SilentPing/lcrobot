<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../TCPDF-main/tcpdf.php'; // TCPDF library

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    die('Unauthorized access.');
}

try {
    // Get gender statistics
    $query = "SELECT 
                gender,
                COUNT(*) as count
              FROM reqtracking_tbl 
              WHERE status != 'Approved'
              GROUP BY gender";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Failed to fetch gender statistics: ' . $conn->error);
    }
    
    $statistics = [
        'total' => 0,
        'male' => 0,
        'female' => 0,
        'other' => 0
    ];
    
    while ($row = $result->fetch_assoc()) {
        $gender = strtolower($row['gender']);
        $count = (int)$row['count'];
        
        switch ($gender) {
            case 'male':
                $statistics['male'] = $count;
                break;
            case 'female':
                $statistics['female'] = $count;
                break;
            case 'other':
                $statistics['other'] = $count;
                break;
        }
        
        $statistics['total'] += $count;
    }
    
    // Get statistics by request type
    $typeQuery = "SELECT 
                    type_request,
                    gender,
                    COUNT(*) as count
                  FROM reqtracking_tbl 
                  WHERE status != 'Approved'
                  GROUP BY type_request, gender
                  ORDER BY type_request, gender";
    
    $typeResult = $conn->query($typeQuery);
    $typeStatistics = [];
    
    if ($typeResult) {
        while ($row = $typeResult->fetch_assoc()) {
            $type = $row['type_request'];
            $gender = strtolower($row['gender']);
            $count = (int)$row['count'];
            
            if (!isset($typeStatistics[$type])) {
                $typeStatistics[$type] = [
                    'total' => 0,
                    'male' => 0,
                    'female' => 0,
                    'other' => 0
                ];
            }
            
            $typeStatistics[$type]['total'] += $count;
            $typeStatistics[$type][$gender] = $count;
        }
    }
    
    // Create PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Civil Registry System');
    $pdf->SetAuthor('LCRO');
    $pdf->SetTitle('Gender Statistics Report - ' . date('Y-m-d'));
    $pdf->SetSubject('Gender Statistics Report');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 18);
    
    // Title
    $pdf->Cell(0, 15, 'REPUBLIC OF THE PHILIPPINES', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'LOCAL CIVIL REGISTRY OFFICE', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'GENDER STATISTICS REPORT', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 5, 'Report Period: ' . date('F j, Y'), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Overall Statistics
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'OVERALL GENDER STATISTICS', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    
    $malePercentage = $statistics['total'] > 0 ? (($statistics['male'] / $statistics['total']) * 100) : 0;
    $femalePercentage = $statistics['total'] > 0 ? (($statistics['female'] / $statistics['total']) * 100) : 0;
    $otherPercentage = $statistics['total'] > 0 ? (($statistics['other'] / $statistics['total']) * 100) : 0;
    
    $pdf->Cell(0, 6, 'Total Requests: ' . $statistics['total'], 0, 1, 'L');
    $pdf->Ln(3);
    
    $pdf->Cell(0, 6, 'Male Requestors: ' . $statistics['male'] . ' (' . number_format($malePercentage, 1) . '%)', 0, 1, 'L');
    $pdf->Cell(0, 6, 'Female Requestors: ' . $statistics['female'] . ' (' . number_format($femalePercentage, 1) . '%)', 0, 1, 'L');
    $pdf->Cell(0, 6, 'Other: ' . $statistics['other'] . ' (' . number_format($otherPercentage, 1) . '%)', 0, 1, 'L');
    
    $pdf->Ln(10);
    
    // Statistics by Request Type
    if (!empty($typeStatistics)) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'GENDER STATISTICS BY REQUEST TYPE', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        
        foreach ($typeStatistics as $type => $stats) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, $type . ':', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);
            
            $typeMalePercentage = $stats['total'] > 0 ? (($stats['male'] / $stats['total']) * 100) : 0;
            $typeFemalePercentage = $stats['total'] > 0 ? (($stats['female'] / $stats['total']) * 100) : 0;
            $typeOtherPercentage = $stats['total'] > 0 ? (($stats['other'] / $stats['total']) * 100) : 0;
            
            $pdf->Cell(20, 5, '', 0, 0, 'L');
            $pdf->Cell(0, 5, 'Total: ' . $stats['total'], 0, 1, 'L');
            $pdf->Cell(20, 5, '', 0, 0, 'L');
            $pdf->Cell(0, 5, 'Male: ' . $stats['male'] . ' (' . number_format($typeMalePercentage, 1) . '%)', 0, 1, 'L');
            $pdf->Cell(20, 5, '', 0, 0, 'L');
            $pdf->Cell(0, 5, 'Female: ' . $stats['female'] . ' (' . number_format($typeFemalePercentage, 1) . '%)', 0, 1, 'L');
            $pdf->Cell(20, 5, '', 0, 0, 'L');
            $pdf->Cell(0, 5, 'Other: ' . $stats['other'] . ' (' . number_format($typeOtherPercentage, 1) . '%)', 0, 1, 'L');
            $pdf->Ln(5);
        }
    }
    
    $pdf->Ln(10);
    
    // Summary
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'SUMMARY', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    
    if ($statistics['total'] > 0) {
        if ($statistics['male'] > $statistics['female']) {
            $pdf->Cell(0, 6, '• Male requestors represent the majority of civil document requests.', 0, 1, 'L');
        } elseif ($statistics['female'] > $statistics['male']) {
            $pdf->Cell(0, 6, '• Female requestors represent the majority of civil document requests.', 0, 1, 'L');
        } else {
            $pdf->Cell(0, 6, '• Male and female requestors are equally represented.', 0, 1, 'L');
        }
        
        $pdf->Cell(0, 6, '• Total of ' . $statistics['total'] . ' pending civil document requests analyzed.', 0, 1, 'L');
        $pdf->Cell(0, 6, '• Gender distribution provides insights for service planning and resource allocation.', 0, 1, 'L');
    } else {
        $pdf->Cell(0, 6, '• No pending civil document requests found for analysis.', 0, 1, 'L');
    }
    
    $pdf->Ln(15);
    
    // Footer
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'This report was generated on ' . date('F j, Y \a\t g:i A') . ' by the Civil Registry System.', 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->Cell(0, 5, '_________________________', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Civil Registrar', 0, 1, 'C');
    
    // Output PDF
    $filename = 'Gender_Statistics_Report_' . date('Y-m-d') . '.pdf';
    $pdf->Output($filename, 'D');
    
} catch (Exception $e) {
    die('Error generating PDF: ' . $e->getMessage());
}
?>
