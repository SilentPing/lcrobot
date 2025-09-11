<?php
// Set error reporting to prevent HTML errors in JSON response
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || $_SESSION['usertype'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check database connection
if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get report type from POST data
$input = json_decode(file_get_contents('php://input'), true);
$reportType = $input['type'] ?? 'daily';

// Set date ranges
$today = date('Y-m-d');
$weekStart = date('Y-m-d', strtotime('monday this week'));
$monthStart = date('Y-m-01');

switch ($reportType) {
    case 'daily':
        $startDate = $today;
        $endDate = $today;
        $title = "Daily Report - " . date('F j, Y');
        break;
    case 'weekly':
        $startDate = $weekStart;
        $endDate = date('Y-m-d', strtotime('sunday this week'));
        $title = "Weekly Report - " . date('M j', strtotime($weekStart)) . " to " . date('M j, Y', strtotime($endDate));
        break;
    case 'monthly':
        $startDate = $monthStart;
        $endDate = date('Y-m-t');
        $title = "Monthly Report - " . date('F Y');
        break;
    case 'custom':
        $startDate = $input['startDate'] ?? $today;
        $endDate = $input['endDate'] ?? $today;
        $title = "Custom Report - " . date('M j', strtotime($startDate)) . " to " . date('M j, Y', strtotime($endDate));
        break;
    default:
        $startDate = $today;
        $endDate = $today;
        $title = "Daily Report - " . date('F j, Y');
}

// Generate report data
$reportData = generateReportData($startDate, $endDate);

// Generate HTML
$html = generateReportHTML($title, $reportData, $startDate, $endDate);

// Prepare chart data
$chartData = [
    'byType' => $reportData['by_type'],
    'dailyBreakdown' => $reportData['daily_breakdown'] ?? null
];

echo json_encode(['success' => true, 'html' => $html, 'chartData' => $chartData]);

function generateReportData($startDate, $endDate) {
    global $conn;
    
    $data = [];
    
    // Single optimized query to get all data at once
    $query = "SELECT 
        COUNT(*) as total_requests,
        type_request,
        status,
        DATE(registration_date) as date
        FROM reqtracking_tbl 
        WHERE DATE(registration_date) BETWEEN '$startDate' AND '$endDate'
        GROUP BY type_request, status, DATE(registration_date)";
    
    $result = mysqli_query($conn, $query);
    
    // Initialize data arrays
    $data['total_requests'] = 0;
    $data['by_type'] = [];
    $data['by_status'] = [];
    $data['daily_breakdown'] = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Count total requests
            $data['total_requests'] += $row['total_requests'];
            
            // Count by type
            if (!isset($data['by_type'][$row['type_request']])) {
                $data['by_type'][$row['type_request']] = 0;
            }
            $data['by_type'][$row['type_request']] += $row['total_requests'];
            
            // Count by status
            if (!isset($data['by_status'][$row['status']])) {
                $data['by_status'][$row['status']] = 0;
            }
            $data['by_status'][$row['status']] += $row['total_requests'];
            
            // Count by date (for daily breakdown)
            if ($startDate != $endDate) {
                if (!isset($data['daily_breakdown'][$row['date']])) {
                    $data['daily_breakdown'][$row['date']] = 0;
                }
                $data['daily_breakdown'][$row['date']] += $row['total_requests'];
            }
        }
    }
    
    return $data;
}

function generateReportHTML($title, $data, $startDate, $endDate) {
    $html = '<div class="report-container">';
    $html .= '<h4 class="mb-4">' . $title . '</h4>';
    
    // Summary cards
    $html .= '<div class="row mb-4">';
    $html .= '<div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body text-center"><h5>' . $data['total_requests'] . '</h5><p>Total Requests</p></div></div></div>';
    
    $pending = $data['by_status']['Pending'] ?? 0;
    $approved = $data['by_status']['Approved'] ?? 0;
    $rejected = $data['by_status']['Rejected'] ?? 0;
    
    $html .= '<div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body text-center"><h5>' . $pending . '</h5><p>Pending</p></div></div></div>';
    $html .= '<div class="col-md-3"><div class="card bg-success text-white"><div class="card-body text-center"><h5>' . $approved . '</h5><p>Approved</p></div></div></div>';
    $html .= '<div class="col-md-3"><div class="card bg-danger text-white"><div class="card-body text-center"><h5>' . $rejected . '</h5><p>Rejected</p></div></div></div>';
    $html .= '</div>';
    
    // Charts and Analytics Section
    if (!empty($data['by_type'])) {
        $html .= '<div class="row mb-4">';
        
        // Request Type Chart
        $html .= '<div class="col-md-6">';
        $html .= '<h5>Requests by Type</h5>';
        $html .= '<canvas id="requestTypeChart" width="400" height="200"></canvas>';
        $html .= '</div>';
        
        // Daily Breakdown Chart (if available)
        if (!empty($data['daily_breakdown'])) {
            $html .= '<div class="col-md-6">';
            $html .= '<h5>Daily Breakdown</h5>';
            $html .= '<canvas id="dailyBreakdownChart" width="400" height="200"></canvas>';
            $html .= '</div>';
        } else {
            // Show status breakdown instead
            $html .= '<div class="col-md-6">';
            $html .= '<h5>Status Breakdown</h5>';
            $html .= '<table class="table table-striped">';
            $html .= '<thead><tr><th>Status</th><th>Count</th></tr></thead><tbody>';
            foreach ($data['by_status'] as $status => $count) {
                $html .= '<tr><td>' . $status . '</td><td>' . $count . '</td></tr>';
            }
            $html .= '</tbody></table>';
            $html .= '</div>';
        }
        $html .= '</div>';
        
        // Detailed tables below charts
        $html .= '<div class="row mb-4">';
        $html .= '<div class="col-md-6">';
        $html .= '<h6>Request Type Details</h6>';
        $html .= '<table class="table table-sm table-striped">';
        $html .= '<thead><tr><th>Type</th><th>Count</th><th>%</th></tr></thead><tbody>';
        $total = array_sum($data['by_type']);
        foreach ($data['by_type'] as $type => $count) {
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $html .= '<tr><td>' . $type . '</td><td>' . $count . '</td><td>' . $percentage . '%</td></tr>';
        }
        $html .= '</tbody></table>';
        $html .= '</div>';
        
        if (!empty($data['daily_breakdown'])) {
            $html .= '<div class="col-md-6">';
            $html .= '<h6>Daily Details</h6>';
            $html .= '<table class="table table-sm table-striped">';
            $html .= '<thead><tr><th>Date</th><th>Requests</th></tr></thead><tbody>';
            foreach ($data['daily_breakdown'] as $date => $count) {
                $html .= '<tr><td>' . date('M j', strtotime($date)) . '</td><td>' . $count . '</td></tr>';
            }
            $html .= '</tbody></table>';
            $html .= '</div>';
        }
        $html .= '</div>';
    }
    
    // Export buttons
    $html .= '<div class="row mt-4">';
    $html .= '<div class="col-12 text-center">';
    $html .= '<button class="btn btn-primary me-2" onclick="exportPDF(\'' . $startDate . '\', \'' . $endDate . '\')"><i class="fas fa-file-pdf"></i> Export PDF</button>';
    $html .= '<button class="btn btn-success me-2" onclick="exportExcel(\'' . $startDate . '\', \'' . $endDate . '\')"><i class="fas fa-file-excel"></i> Export Excel</button>';
    $html .= '<button class="btn btn-secondary" onclick="printReport()"><i class="fas fa-print"></i> Print</button>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    return $html;
}
?>
