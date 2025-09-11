<?php
/**
 * Process Release Document
 * Handles the release of approved documents with SMS notification
 */

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $request_id = $_POST['request_id'];
        $released_by = $_POST['released_by'];
        $contact_no = $_POST['contact_no'];
        $registrar_name = $_POST['registrar_name'];
        $type_request = $_POST['type_request'];
        $sms_message = $_POST['sms_message'];
        $user_id = $_POST['user_id'] ?? 0;
        
        // Get contact information from reqtracking_tbl first (for walk-in requests), then from users table
        $email = '';
        $actual_user_id = $user_id;
        
        // First try reqtracking_tbl
        $trackingQuery = "SELECT contact_no, email FROM reqtracking_tbl WHERE request_id = ?";
        $stmt = $conn->prepare($trackingQuery);
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $trackingResult = $stmt->get_result();
        if ($trackingResult->num_rows > 0) {
            $trackingRow = $trackingResult->fetch_assoc();
            $contact_no = $trackingRow['contact_no'] ?: $contact_no;
            $email = $trackingRow['email'] ?: '';
        }
        $stmt->close();
        
        // If no contact info found in reqtracking_tbl and user_id is 0, try to find correct user_id by contact number
        if ((empty($contact_no) || empty($email)) && $user_id == 0) {
            // First try to find user by contact number (prioritize regular users over admins)
            if (!empty($contact_no)) {
                $userQuery = "SELECT id_user, email, usertype FROM users WHERE contact_no = ? ORDER BY CASE WHEN usertype = 'user' THEN 1 ELSE 2 END, id_user ASC";
                $stmt = $conn->prepare($userQuery);
                $stmt->bind_param("s", $contact_no);
                $stmt->execute();
                $userResult = $stmt->get_result();
                if ($userResult->num_rows > 0) {
                    $userRow = $userResult->fetch_assoc();
                    $actual_user_id = $userRow['id_user'];
                    $email = $email ?: (!empty($userRow['email']) ? $userRow['email'] : '');
                }
                $stmt->close();
            }
            
            // If still no user found, try name matching as fallback
            if ($actual_user_id == 0) {
                $name_parts = explode(' ', $registrar_name);
                $first_name = $name_parts[0];
                $last_name = end($name_parts);
                
                $birthQuery = "SELECT id_user FROM birthceno_tbl WHERE firstname = ? AND lastname = ? LIMIT 1";
                $stmt = $conn->prepare($birthQuery);
                $stmt->bind_param("ss", $first_name, $last_name);
                $stmt->execute();
                $birthResult = $stmt->get_result();
                if ($birthResult->num_rows > 0) {
                    $birthRow = $birthResult->fetch_assoc();
                    $actual_user_id = $birthRow['id_user'];
                }
                $stmt->close();
            }
        }
        
        // Get contact info from users table using the correct user_id
        if (empty($email) && $actual_user_id > 0) {
            $userQuery = "SELECT contact_no, email FROM users WHERE id_user = ?";
            $stmt = $conn->prepare($userQuery);
            $stmt->bind_param("i", $actual_user_id);
            $stmt->execute();
            $userResult = $stmt->get_result();
            if ($userResult->num_rows > 0) {
                $userRow = $userResult->fetch_assoc();
                $contact_no = $contact_no ?: $userRow['contact_no'];
                $email = $email ?: (!empty($userRow['email']) ? $userRow['email'] : '');
            }
            $stmt->close();
        }
        
        // Validate required fields
        if (empty($request_id) || empty($released_by) || empty($sms_message)) {
            throw new Exception('Required fields must be filled: request_id, released_by, sms_message');
        }
        
        // Log the contact information for debugging
        error_log("Release Process - Request ID: $request_id, Contact: $contact_no, Email: $email, User ID: $actual_user_id");
        
        // Start transaction
        $conn->begin_transaction();
        
        // 1. Get the approved request details
        $getRequestQuery = "SELECT * FROM approved_requests WHERE request_id = ?";
        $stmt = $conn->prepare($getRequestQuery);
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            throw new Exception('Request not found in approved requests');
        }
        
        $requestData = $result->fetch_assoc();
        $stmt->close();
        
        // 2. Update the approved request with release information
        $updateQuery = "UPDATE approved_requests SET 
                        released_date = NOW(), 
                        released_by = ? 
                        WHERE request_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $released_by, $request_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update approved request status');
        }
        $stmt->close();
        
        // 3. Insert into released_requests table
        $insertReleasedQuery = "INSERT INTO released_requests (
            request_id, 
            type_request, 
            registrar_name, 
            contact_no, 
            email, 
            registration_date, 
            approved_date, 
            released_date, 
            released_by, 
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'Released')";
        
        $stmt = $conn->prepare($insertReleasedQuery);
        $approved_date = isset($requestData['approved_date']) ? $requestData['approved_date'] : $requestData['registration_date'];
        $stmt->bind_param("isssssss", 
            $request_id,
            $type_request,
            $registrar_name,
            $contact_no,
            $email,
            $requestData['registration_date'],
            $approved_date,
            $released_by
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert into released_requests table');
        }
        $stmt->close();
        
        // 4. Send SMS notification with PDF receipt (only if contact_no is available)
        $smsResult = ['success' => false, 'message' => 'No contact number available'];
        if (!empty($contact_no) && $contact_no !== 'Not available') {
            // Check if PDF receipt should be sent
            $send_pdf_receipt = $_POST['send_pdf_receipt'] ?? false;
            
            if ($send_pdf_receipt) {
                // Generate PDF receipt and send with SMS
                $qr_reference = $_POST['qr_reference'] ?? '';
                $smsResult = sendSMSWithPDFReceipt($contact_no, $sms_message, $qr_reference);
            } else {
                // Send regular SMS
                $smsResult = sendReleaseSMS($contact_no, $sms_message);
            }
            
            if (!$smsResult['success']) {
                // Log SMS failure but don't fail the entire transaction
                error_log("SMS sending failed for request ID $request_id: " . $smsResult['message']);
            }
        } else {
            error_log("SMS not sent for request ID $request_id: No valid contact number");
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Document released successfully' . ($smsResult['success'] ? ' and SMS sent' : ' but SMS failed'),
            'sms_sent' => $smsResult['success']
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->in_transaction) {
            $conn->rollback();
        }
        error_log("Release process error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function sendSMSWithPDFReceipt($phone_number, $message, $qr_reference) {
    try {
        error_log("SMS with PDF Function called with phone: $phone_number");
        
        // Your Semaphore API Key
        $api_key = '3b0a653cc759c73537ac5e57bf133e8c';
        
        // Sender name
        $sender_name = 'MCROBOTOLAN';
        
        // Create public URL for PDF (you can modify this to your actual domain)
        $base_url = 'https://lcrobot.pcbics.net/';
        $pdf_url = $base_url . 'download_receipt.php?ref=' . urlencode($qr_reference);
        
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

function sendReleaseSMS($phone_number, $message) {
    try {
        error_log("SMS Function called with phone: $phone_number");
        
        // Your Semaphore API Key
        $api_key = '3b0a653cc759c73537ac5e57bf133e8c';
        
        // Sender name
        $sender_name = 'MCROBOTOLAN';
        
        // Initialize cURL session
        $ch = curl_init();
        
        // Set parameters for sending SMS
        $parameters = array(
            'apikey' => $api_key,
            'number' => $phone_number,
            'message' => $message
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
            return ['success' => true, 'message' => 'SMS sent successfully', 'message_id' => $response[0]['message_id']];
        } else {
            return ['success' => false, 'message' => 'SMS API Error: ' . $output];
        }
        
    } catch (Exception $e) {
        error_log("SMS Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'SMS Error: ' . $e->getMessage()];
    }
}
?>
