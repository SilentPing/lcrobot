<?php
/**
 * Export All Timely Birth Records to Excel
 */

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header('HTTP/1.1 401 Unauthorized');
    echo 'Unauthorized access';
    exit;
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    // Get all submissions
    $query = "SELECT * FROM timely_birth_submissions ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }
    
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Timely Birth Records');
    
    // Set headers
    $headers = [
        'A1' => 'Submission Number',
        'B1' => 'Requestor Name',
        'C1' => 'Status',
        'D1' => 'Submitted Date',
        'E1' => 'Updated Date',
        'F1' => 'Excel File Path'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Style headers
    $headerRange = 'A1:F1';
    $sheet->getStyle($headerRange)->getFont()->setBold(true);
    $sheet->getStyle($headerRange)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE0E0E0');
    
    // Add data
    $row = 2;
    while ($submission = mysqli_fetch_assoc($result)) {
        $sheet->setCellValue('A' . $row, $submission['submission_number']);
        $sheet->setCellValue('B' . $row, $submission['requestor_name']);
        $sheet->setCellValue('C' . $row, $submission['status']);
        $sheet->setCellValue('D' . $row, $submission['created_at']);
        $sheet->setCellValue('E' . $row, $submission['updated_at']);
        $sheet->setCellValue('F' . $row, $submission['excel_file_path']);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'F') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Set headers for download
    $filename = 'Timely_Birth_Records_' . date('Y-m-d_H-i-s') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Write file
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
    
} catch (Exception $e) {
    error_log("Export timely birth excel error: " . $e->getMessage());
    echo 'Error: ' . $e->getMessage();
}
?>
