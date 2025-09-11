<?php
session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || $_SESSION['usertype'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');

function sendSMSWithPDFReceipt($phone_number, $message, $pdf_path, $reference) {
    try {
        error_log("SMS with PDF Function called with phone: $phone_number");
        
        // Your Semaphore API Key
        $api_key = '3b0a653cc759c73537ac5e57bf133e8c';
        
        // Sender name
        $sender_name = 'MCROBOTOLAN';
        
        // Create public URL for PDF (you can modify this to your actual domain)
        $base_url = 'http://localhost/civreg/';
        $pdf_url = $base_url . 'download_receipt.php?ref=' . urlencode($reference);
        
        // Enhanced message with PDF link
        $enhanced_message = $message . "\n\nDownload receipt: " . $pdf_url;
        
        // Initialize cURL session
        $ch = curl_init();
        
        // Set parameters for sending SMS
        $parameters = array(
            'apikey' => $api_key,
            'number' => $phone_number,
            'message' => $enhanced_message
        );
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Execute cURL request
        $output = curl_exec($ch);
        
        // Check for errors
        if (curl_error($ch)) {
            curl_close($ch);
            return ['success' => false, 'message' => 'cURL Error: ' . curl_error($ch)];
        }
        
        curl_close($ch);
        
        // Parse response
        $response = json_decode($output, true);
        
        if (isset($response[0]['message_id'])) {
            return [
                'success' => true, 
                'message' => 'SMS with PDF link sent successfully', 
                'message_id' => $response[0]['message_id'],
                'pdf_url' => $pdf_url
            ];
        } else {
            return ['success' => false, 'message' => 'SMS API Error: ' . $output];
        }
        
    } catch (Exception $e) {
        error_log("SMS with PDF Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'SMS Error: ' . $e->getMessage()];
    }
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $phone_number = $input['phone_number'] ?? '';
    $message = $input['message'] ?? '';
    $pdf_path = $input['pdf_path'] ?? '';
    $reference = $input['reference'] ?? '';
    
    if (empty($phone_number) || empty($message) || empty($pdf_path) || empty($reference)) {
        throw new Exception('Missing required parameters');
    }
    
    // Send SMS with PDF link
    $result = sendSMSWithPDFReceipt($phone_number, $message, $pdf_path, $reference);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("SMS with PDF API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
