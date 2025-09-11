<?php
session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || $_SESSION['usertype'] !== 'admin') {
    die('Unauthorized access');
}

// Get date parameters
$startDate = $_GET['start'] ?? date('Y-m-d');
$endDate = $_GET['end'] ?? date('Y-m-d');

// Generate report data
$reportData = generateReportData($startDate, $endDate);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="civil_registry_report_' . $startDate . '_to_' . $endDate . '.xls"');

// Generate Excel content
generateExcelContent($reportData, $startDate, $endDate);

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
    
    // Daily breakdown
    $query = "SELECT DATE(registration_date) as date, COUNT(*) as count FROM reqtracking_tbl WHERE DATE(registration_date) BETWEEN '$startDate' AND '$endDate' GROUP BY DATE(registration_date) ORDER BY date";
    $result = mysqli_query($conn, $query);
    $data['daily_breakdown'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data['daily_breakdown'][$row['date']] = $row['count'];
    }
    
    return $data;
}

function generateExcelContent($data, $startDate, $endDate) {
    $title = "Civil Registry Report - " . date('M j', strtotime($startDate)) . " to " . date('M j, Y', strtotime($endDate));
    
    echo "<table border='1'>";
    
    // Header
    echo "<tr><td colspan='3' style='text-align:center; font-weight:bold; font-size:16px;'>BOTOLAN CIVIL REGISTRY</td></tr>";
    echo "<tr><td colspan='3' style='text-align:center;'>Municipality of Botolan, Zambales</td></tr>";
    echo "<tr><td colspan='3' style='text-align:center; font-weight:bold; font-size:14px;'>" . $title . "</td></tr>";
    echo "<tr><td colspan='3' style='text-align:center;'>Generated on: " . date('F j, Y \a\t g:i A') . "</td></tr>";
    echo "<tr><td colspan='3'></td></tr>";
    
    // Summary
    echo "<tr><td colspan='3' style='font-weight:bold; background-color:#f0f0f0;'>SUMMARY</td></tr>";
    echo "<tr><td>Total Requests</td><td>" . $data['total_requests'] . "</td><td></td></tr>";
    echo "<tr><td>Pending</td><td>" . ($data['by_status']['Pending'] ?? 0) . "</td><td></td></tr>";
    echo "<tr><td>Approved</td><td>" . ($data['by_status']['Approved'] ?? 0) . "</td><td></td></tr>";
    echo "<tr><td>Rejected</td><td>" . ($data['by_status']['Rejected'] ?? 0) . "</td><td></td></tr>";
    echo "<tr><td colspan='3'></td></tr>";
    
    // Requests by Type
    echo "<tr><td colspan='3' style='font-weight:bold; background-color:#f0f0f0;'>REQUESTS BY TYPE</td></tr>";
    echo "<tr><td style='font-weight:bold;'>Request Type</td><td style='font-weight:bold;'>Count</td><td style='font-weight:bold;'>Percentage</td></tr>";
    
    $total = array_sum($data['by_type']);
    foreach ($data['by_type'] as $type => $count) {
        $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        echo "<tr><td>" . $type . "</td><td>" . $count . "</td><td>" . $percentage . "%</td></tr>";
    }
    echo "<tr><td colspan='3'></td></tr>";
    
    // Status Breakdown
    echo "<tr><td colspan='3' style='font-weight:bold; background-color:#f0f0f0;'>STATUS BREAKDOWN</td></tr>";
    echo "<tr><td style='font-weight:bold;'>Status</td><td style='font-weight:bold;'>Count</td><td></td></tr>";
    
    foreach ($data['by_status'] as $status => $count) {
        echo "<tr><td>" . $status . "</td><td>" . $count . "</td><td></td></tr>";
    }
    echo "<tr><td colspan='3'></td></tr>";
    
    // Daily Breakdown (if available)
    if (!empty($data['daily_breakdown'])) {
        echo "<tr><td colspan='3' style='font-weight:bold; background-color:#f0f0f0;'>DAILY BREAKDOWN</td></tr>";
        echo "<tr><td style='font-weight:bold;'>Date</td><td style='font-weight:bold;'>Requests</td><td></td></tr>";
        
        foreach ($data['daily_breakdown'] as $date => $count) {
            echo "<tr><td>" . date('M j, Y', strtotime($date)) . "</td><td>" . $count . "</td><td></td></tr>";
        }
    }
    
    echo "</table>";
}
?>
