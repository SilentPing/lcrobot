<?php
/**
 * Get Pricing API
 * This API endpoint fetches pricing data from the database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db.php';

try {
    // Check if specific document type is requested
    $documentType = isset($_GET['document_type']) ? $_GET['document_type'] : null;
    
    // Build the query
    $query = "SELECT * FROM document_pricing WHERE is_active = 1";
    $params = [];
    $types = "";
    
    if ($documentType) {
        $query .= " AND document_type = ?";
        $params[] = $documentType;
        $types .= "s";
    }
    
    $query .= " ORDER BY document_type, form_type";
    
    // Prepare and execute the query
    $stmt = mysqli_prepare($conn, $query);
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $pricingData = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pricingData[] = [
            'id' => (int)$row['id'],
            'document_type' => $row['document_type'],
            'form_type' => $row['form_type'],
            'form_number' => $row['form_number'],
            'price' => (float)$row['price'],
            'description' => $row['description'],
            'is_active' => (bool)$row['is_active'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    mysqli_stmt_close($stmt);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $pricingData,
        'count' => count($pricingData),
        'message' => 'Pricing data retrieved successfully'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'message' => 'Failed to retrieve pricing data'
    ], JSON_PRETTY_PRINT);
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>
