<?php
/**
 * Dashboard Statistics API
 * Provides real-time statistics for the admin dashboard
 */

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

try {

    date_default_timezone_set('Asia/Manila');

    // Get current date for today's calculations
    $today = date('Y-m-d');
    
    // Initialize statistics array
    $stats = [];
    
    // 1. Total Pending Requests
    $query = "SELECT COUNT(*) as count FROM reqtracking_tbl WHERE status = 'Pending'";
    $result = mysqli_query($conn, $query);
    $stats['pending_requests'] = mysqli_fetch_assoc($result)['count'];
    
    // 2. Total Approved Requests (not yet released)
    $query = "SELECT COUNT(*) as count FROM approved_requests WHERE released_date IS NULL OR released_date = ''";
    $result = mysqli_query($conn, $query);
    $stats['approved_requests'] = mysqli_fetch_assoc($result)['count'];
    
    // 3. Total Released Requests
    $query = "SELECT COUNT(*) as count FROM released_requests";
    $result = mysqli_query($conn, $query);
    $stats['released_requests'] = mysqli_fetch_assoc($result)['count'];
    
    // 4. Total Rejected Requests
    $query = "SELECT COUNT(*) as count FROM rejected_requests";
    $result = mysqli_query($conn, $query);
    $stats['rejected_requests'] = mysqli_fetch_assoc($result)['count'];
    
    // 5. Total Registered Users
    $query = "SELECT COUNT(*) as count FROM users WHERE usertype = 'user'";
    $result = mysqli_query($conn, $query);
    $stats['total_users'] = mysqli_fetch_assoc($result)['count'];
    
    // 5a. Users Born in Botolan
    $query = "SELECT COUNT(*) as count FROM users WHERE usertype = 'user' AND birthplace_municipality = '037101' AND birthplace_province = '0371'";
    $result = mysqli_query($conn, $query);
    $stats['botolan_users'] = mysqli_fetch_assoc($result)['count'];
    
    // 5b. Users Born Outside Botolan
    $query = "SELECT COUNT(*) as count FROM users WHERE usertype = 'user' AND (birthplace_municipality != '037101' OR birthplace_province != '0371' OR birthplace_municipality IS NULL OR birthplace_province IS NULL)";
    $result = mysqli_query($conn, $query);
    $stats['non_botolan_users'] = mysqli_fetch_assoc($result)['count'];
    
    // 5c. Users with Complete Birthplace Data
    $query = "SELECT COUNT(*) as count FROM users WHERE usertype = 'user' AND birthplace_municipality IS NOT NULL AND birthplace_province IS NOT NULL AND birthplace_municipality != '' AND birthplace_province != ''";
    $result = mysqli_query($conn, $query);
    $stats['users_with_birthplace'] = mysqli_fetch_assoc($result)['count'];
    
    // 5d. Users Missing Birthplace Data
    $query = "SELECT COUNT(*) as count FROM users WHERE usertype = 'user' AND (birthplace_municipality IS NULL OR birthplace_province IS NULL OR birthplace_municipality = '' OR birthplace_province = '')";
    $result = mysqli_query($conn, $query);
    $stats['users_missing_birthplace'] = mysqli_fetch_assoc($result)['count'];
    
    // 6. Today's New Requests
    $query = "SELECT COUNT(*) as count FROM reqtracking_tbl WHERE DATE(registration_date) = '$today'";
    $result = mysqli_query($conn, $query);
    $stats['today_requests'] = mysqli_fetch_assoc($result)['count'];
    
    // 7. Today's Released Documents
    $query = "SELECT COUNT(*) as count FROM released_requests WHERE DATE(released_date) = '$today'";
    $result = mysqli_query($conn, $query);
    $stats['today_released'] = mysqli_fetch_assoc($result)['count'];
    
    // 8. Total Admin Users
    $query = "SELECT COUNT(*) as count FROM users WHERE usertype = 'admin'";
    $result = mysqli_query($conn, $query);
    $stats['admin_users'] = mysqli_fetch_assoc($result)['count'];
    
    // 9. Users Born in Botolan (LCRO Eligible)
    $query = "SELECT COUNT(*) as count FROM users WHERE usertype = 'user' AND birthplace_municipality = '037101' AND birthplace_province = '0371'";
    $result = mysqli_query($conn, $query);
    $stats['botolan_users'] = mysqli_fetch_assoc($result)['count'];
    
    // 10. Users Born Outside Botolan (PSA Only)
    $query = "SELECT COUNT(*) as count FROM users WHERE usertype = 'user' AND (birthplace_municipality != '037101' OR birthplace_province != '0371' OR birthplace_municipality IS NULL OR birthplace_province IS NULL)";
    $result = mysqli_query($conn, $query);
    $stats['non_botolan_users'] = mysqli_fetch_assoc($result)['count'];
    
    // 11. Users with Complete Birthplace Data
    $query = "SELECT COUNT(*) as count FROM users WHERE usertype = 'user' AND birthplace_municipality IS NOT NULL AND birthplace_province IS NOT NULL AND birthplace_municipality != '' AND birthplace_province != ''";
    $result = mysqli_query($conn, $query);
    $stats['complete_birthplace_users'] = mysqli_fetch_assoc($result)['count'];
    
    // 12. Users with Missing Birthplace Data
    $query = "SELECT COUNT(*) as count FROM users WHERE usertype = 'user' AND (birthplace_municipality IS NULL OR birthplace_province IS NULL OR birthplace_municipality = '' OR birthplace_province = '')";
    $result = mysqli_query($conn, $query);
    $stats['incomplete_birthplace_users'] = mysqli_fetch_assoc($result)['count'];
    
    // 13. Average Processing Time (in days)
    $query = "SELECT AVG(DATEDIFF(released_date, registration_date)) as avg_days 
              FROM released_requests 
              WHERE released_date IS NOT NULL AND registration_date IS NOT NULL";
    $result = mysqli_query($conn, $query);
    $avg_days = mysqli_fetch_assoc($result)['avg_days'];
    $stats['avg_processing_days'] = $avg_days ? round($avg_days, 1) : 0;
    
    // 14. Requests by Type (this week)
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $query = "SELECT type_request, COUNT(*) as count 
              FROM reqtracking_tbl 
              WHERE DATE(registration_date) >= '$week_start'
              GROUP BY type_request";
    $result = mysqli_query($conn, $query);
    $stats['requests_by_type'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['requests_by_type'][$row['type_request']] = $row['count'];
    }
    
    // 15. System Health Indicators
    $stats['system_health'] = [
        'database_connected' => $conn ? true : false,
        'last_update' => date('F d, Y h:i:s A'), 
        'server_time' => date('H:i:s')          
    ];
    
    // Add success status
    $stats['success'] = true;
    
    echo json_encode($stats);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch statistics: ' . $e->getMessage()
    ]);
}
?>
