<?php
/**
 * Update Pricing API
 * This API endpoint updates pricing data in the database
 */

session_start();
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access',
        'message' => 'Admin access required'
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If no JSON input, try POST data
    if (!$input) {
        $input = $_POST;
    }
    
    // Validate required fields
    if (!isset($input['id']) || empty($input['id'])) {
        throw new Exception('Pricing ID is required');
    }
    
    $id = intval($input['id']);
    $price = isset($input['price']) ? floatval($input['price']) : null;
    $description = isset($input['description']) ? trim($input['description']) : null;
    $is_active = isset($input['is_active']) ? intval($input['is_active']) : null;
    
    // Build update query dynamically
    $updateFields = [];
    $params = [];
    $types = "";
    
    if ($price !== null) {
        $updateFields[] = "price = ?";
        $params[] = $price;
        $types .= "d";
    }
    
    if ($description !== null) {
        $updateFields[] = "description = ?";
        $params[] = $description;
        $types .= "s";
    }
    
    if ($is_active !== null) {
        $updateFields[] = "is_active = ?";
        $params[] = $is_active;
        $types .= "i";
    }
    
    if (empty($updateFields)) {
        throw new Exception('No fields to update');
    }
    
    // Add ID parameter
    $params[] = $id;
    $types .= "i";
    
    // Build the query
    $query = "UPDATE document_pricing SET " . implode(", ", $updateFields) . " WHERE id = ?";
    
    // Prepare and execute the query
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to execute update: ' . mysqli_stmt_error($stmt));
    }
    
    $affectedRows = mysqli_stmt_affected_rows($stmt);
    
    if ($affectedRows === 0) {
        throw new Exception('No pricing record found with ID: ' . $id);
    }
    
    mysqli_stmt_close($stmt);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Pricing updated successfully',
        'affected_rows' => $affectedRows,
        'updated_fields' => array_keys($input)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Failed to update pricing data'
    ], JSON_PRETTY_PRINT);
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>
