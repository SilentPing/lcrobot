<?php
/**
 * Generate Formatted Excel File for Timely Birth Registration
 * Creates a properly formatted Excel file from database data
 */

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Suppress errors for clean output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

try {
    $submissionId = $_GET['id'] ?? null;
    
    if (!$submissionId) {
        throw new Exception('Submission ID is required');
    }
    
    // Get submission details
    $submissionQuery = "SELECT * FROM timely_birth_submissions WHERE id = ?";
    $stmt = mysqli_prepare($conn, $submissionQuery);
    mysqli_stmt_bind_param($stmt, "i", $submissionId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $submission = mysqli_fetch_assoc($result);
    
    if (!$submission) {
        throw new Exception('Submission not found');
    }
    
    // Get submission data
    $dataQuery = "SELECT * FROM timely_birth_data WHERE submission_id = ? ORDER BY id";
    $stmt = mysqli_prepare($conn, $dataQuery);
    mysqli_stmt_bind_param($stmt, "i", $submissionId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    // Load the original template
    $templatePath = __DIR__ . '/../templates/birth_cert_template.xlsx';
    
    if (!file_exists($templatePath)) {
        throw new Exception('Birth certificate template not found');
    }
    
    // Load the template spreadsheet
    $spreadsheet = IOFactory::load($templatePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    // Create data mapping from the database
    $dataMap = [];
    foreach ($data as $row) {
        $dataMap[$row['field_name']] = $row['field_value'];
    }
    
    // Map database fields to Excel cells based on your template structure
    // This mapping should match the cell positions in your birth_cert_template.xlsx
    $cellMapping = [
        'B3' => 'child_first_name',
        'B4' => 'child_middle_name', 
        'B5' => 'child_last_name',
        'B6' => 'child_sex',
        'B8' => 'birth_day',
        'B9' => 'birth_month',
        'B10' => 'birth_year',
        'B12' => 'birth_place_barangay',
        'B13' => 'birth_place_municipality',
        'B14' => 'birth_place_province',
        'B15' => 'birth_type',
        'B17' => 'birth_order',
        'B18' => 'birth_weight',
        'B20' => 'mother_first_name',
        'B21' => 'mother_middle_name',
        'B22' => 'mother_last_name',
        'B23' => 'mother_citizenship',
        'B24' => 'mother_religion',
        'B25' => 'mother_age',
        'B26' => 'mother_residence',
        'B28' => 'father_first_name',
        'B29' => 'father_middle_name',
        'B30' => 'father_last_name',
        'B31' => 'father_citizenship',
        'B32' => 'father_religion',
        'B33' => 'father_age',
        'B34' => 'father_residence',
        'B36' => 'marriage_date',
        'B37' => 'marriage_place',
        'B38' => 'attendant_name',
        'B39' => 'attendant_title',
        'B40' => 'attendant_address',
        'B41' => 'attendant_date'
    ];
    
    // Populate the template with data
    foreach ($cellMapping as $cell => $fieldName) {
        $value = $dataMap[$fieldName] ?? '';
        
        // Special handling for certain fields
        if ($fieldName === 'marriage_date' && $value === 'NOT MARRIED') {
            $value = 'NOT MARRIED';
        } elseif ($fieldName === 'marriage_place' && $dataMap['marriage_date'] === 'NOT MARRIED') {
            $value = 'N/A';
        }
        
        $sheet->setCellValue($cell, $value);
    }
    
    // Add submission metadata (you can place this in a specific area of your template)
    $sheet->setCellValue('A45', 'Submission Number: ' . $submission['submission_number']);
    $sheet->setCellValue('A46', 'Requestor: ' . $submission['requestor_name']);
    $sheet->setCellValue('A47', 'Submitted: ' . date('Y-m-d H:i:s', strtotime($submission['created_at'])));
    
    // Generate filename
    $filename = 'Birth_Certificate_' . $submission['submission_number'] . '_' . date('Y-m-d') . '.xlsx';
    
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Write file
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
} catch (Exception $e) {
    error_log("Generate Excel error: " . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
