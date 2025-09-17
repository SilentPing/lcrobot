<?php
session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');

try {
    // Get gender statistics from reqtracking_tbl
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
    
    // Get additional statistics by request type
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
    
    echo json_encode([
        'success' => true,
        'statistics' => $statistics,
        'typeStatistics' => $typeStatistics,
        'message' => 'Gender statistics retrieved successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
