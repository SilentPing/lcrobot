<?php
/**
 * Download Excel Template for Timely Birth Registration
 * Accessible from civ dashboard without login requirement
 */

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Include PhpSpreadsheet
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    // Template file path
    $templatePath = __DIR__ . '/../templates/birth_cert_template.xlsx';
    
    // Check if template exists
    if (!file_exists($templatePath)) {
        // Create a simple Excel template if it doesn't exist
        
        // Create new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Birth Certificate Form');
        
        // Add headers and labels
        $sheet->setCellValue('B1', 'BIRTH CERTIFICATE FORM');
        $sheet->setCellValue('A2', 'CHILD\'S NAME');
        $sheet->setCellValue('A3', 'FIRST NAME:');
        $sheet->setCellValue('A4', 'MIDDLE NAME:');
        $sheet->setCellValue('A5', 'LAST NAME:');
        $sheet->setCellValue('A6', 'GENDER:');
        $sheet->setCellValue('A7', 'BIRTH DAY');
        $sheet->setCellValue('A8', 'DAY:');
        $sheet->setCellValue('A9', 'MONTH:');
        $sheet->setCellValue('A10', 'YEAR:');
        $sheet->setCellValue('A11', 'PLACE OF BIRTH');
        $sheet->setCellValue('A12', 'BARANGAY:');
        $sheet->setCellValue('A13', 'MUNICIPALITY:');
        $sheet->setCellValue('A14', 'PROVINCE:');
        $sheet->setCellValue('A15', 'TYPE OF BIRTH:');
        $sheet->setCellValue('A16', 'IF MULTIPLE:');
        $sheet->setCellValue('A17', 'BIRTH ORDER:');
        $sheet->setCellValue('A18', 'GRAMS:');
        $sheet->setCellValue('A19', 'MOTHER');
        $sheet->setCellValue('A20', 'FIRST NAME:');
        $sheet->setCellValue('A21', 'MIDDLE NAME:');
        $sheet->setCellValue('A22', 'LAST NAME:');
        $sheet->setCellValue('A23', 'CITIZENSHIP:');
        $sheet->setCellValue('A24', 'RELIGION:');
        $sheet->setCellValue('A25', 'AGE AT THE TIME OF THIS BIRTH:');
        $sheet->setCellValue('A26', 'RESIDENCE:');
        $sheet->setCellValue('A27', 'FATHER');
        $sheet->setCellValue('A28', 'FIRST NAME:');
        $sheet->setCellValue('A29', 'MIDDLE NAME:');
        $sheet->setCellValue('A30', 'LAST NAME:');
        $sheet->setCellValue('A31', 'CITIZENSHIP:');
        $sheet->setCellValue('A32', 'RELIGION:');
        $sheet->setCellValue('A33', 'AGE AT THE TIME OF THIS BIRTH:');
        $sheet->setCellValue('A34', 'RESIDENCE:');
        $sheet->setCellValue('A35', 'MARRIAGE OF PARENTS');
        $sheet->setCellValue('A36', 'DATE OF MARRIAGE:');
        $sheet->setCellValue('A37', 'PLACE OF MARRIAGE:');
        $sheet->setCellValue('A38', 'ATTENDANT AT BIRTH:');
        $sheet->setCellValue('A39', 'TITLE OR POSITION:');
        $sheet->setCellValue('A40', 'ADDRESS:');
        $sheet->setCellValue('A41', 'DATE:');
        
        // Style the header
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A19')->getFont()->setBold(true);
        $sheet->getStyle('A27')->getFont()->setBold(true);
        $sheet->getStyle('A35')->getFont()->setBold(true);
        $sheet->getStyle('A38')->getFont()->setBold(true);
        
        // Auto-size columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        
        // Save template
        $writer = new Xlsx($spreadsheet);
        $writer->save($templatePath);
    }
    
    // Generate filename with timestamp
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "birth_cert_template{$timestamp}.xlsx";
    
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($templatePath));
    header('Cache-Control: max-age=0');
    
    // Output file
    readfile($templatePath);
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error: ' . $e->getMessage();
}
?>
