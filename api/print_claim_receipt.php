<?php
session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || $_SESSION['usertype'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $qrReference = isset($_GET['qr_reference']) ? trim($_GET['qr_reference']) : '';
    
    if (empty($qrReference)) {
        throw new Exception('QR reference is required');
    }
    
    // Get claim details
    $query = "
        SELECT 
            qc.qr_reference,
            qc.claimed_by,
            qc.admin_id,
            qc.claimed_at,
            qc.notes,
            qr.document_type,
            qr.generated_at,
            qr.expires_at,
            u.u_fn,
            u.u_ln,
            u.contact_no,
            CONCAT(u.u_fn, ' ', u.u_ln) as requestor_name
        FROM qr_claims qc
        LEFT JOIN qr_codes qr ON qc.qr_reference = qr.reference_number
        LEFT JOIN users u ON qr.user_id = u.id_user
        WHERE qc.qr_reference = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $qrReference);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Claim not found');
    }
    
    $claim = $result->fetch_assoc();
    
    // Generate HTML receipt
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Claim Receipt - ' . $claim['qr_reference'] . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #f5f5f5;
            }
            .receipt {
                max-width: 400px;
                margin: 0 auto;
                background: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .header {
                text-align: center;
                border-bottom: 2px solidrgb(0, 0, 0);
                padding-bottom: 15px;
                margin-bottom: 20px;
            }
            .header h1 {
                color:rgb(0, 0, 0);
                margin: 0;
                font-size: 18px;
            }
            .header h2 {
                color: #333;
                margin: 5px 0 0 0;
                font-size: 14px;
                font-weight: normal;
            }
            .info-section {
                margin-bottom: 20px;
            }
            .info-section h3 {
                color:rgb(0, 0, 0);
                font-size: 14px;
                margin: 0 0 10px 0;
                border-bottom: 1px solid #eee;
                padding-bottom: 5px;
            }
            .info-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
                font-size: 12px;
            }
            .info-label {
                font-weight: bold;
                color: #555;
            }
            .info-value {
                color: #333;
            }
            .status-badge {
                background: #28a745;
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: bold;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                padding-top: 15px;
                border-top: 1px solid #eee;
                font-size: 11px;
                color: #666;
            }
            .qr-code {
                text-align: center;
                margin: 15px 0;
                padding: 10px;
                background: #f8f9fa;
                border-radius: 5px;
            }
            @media print {
                body { background: white; }
                .receipt { box-shadow: none; }
            }
        </style>
    </head>
    <body>
        <div class="receipt">
            <div class="header">
                <h1>LOCAL CIVIL REGISTRY OFFICE</h1>
                <h2>Botolan, Zambales</h2>
            </div>
            
            <div class="info-section">
                <h3>Document Information</h3>
                <div class="info-row">
                    <span class="info-label">QR Reference:</span>
                    <span class="info-value">' . $claim['qr_reference'] . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Document Type:</span>
                    <span class="info-value">' . $claim['document_type'] . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Requestor:</span>
                    <span class="info-value">' . $claim['requestor_name'] . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Contact:</span>
                    <span class="info-value">' . ($claim['contact_no'] ?: 'N/A') . '</span>
                </div>
            </div>
            
            <div class="info-section">
                <h3>Claim Information</h3>
                <div class="info-row">
                    <span class="info-label">Claimed By:</span>
                    <span class="info-value">' . $claim['claimed_by'] . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Admin ID:</span>
                    <span class="info-value">' . $claim['admin_id'] . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Claim Date:</span>
                    <span class="info-value">' . date('F d, Y', strtotime($claim['claimed_at'])) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Claim Time:</span>
                    <span class="info-value">' . date('H:i:s A', strtotime($claim['claimed_at'])) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge">CLAIMED</span>
                </div>
            </div>
            
            ' . ($claim['notes'] ? '
            <div class="info-section">
                <h3>Notes</h3>
                <div style="font-size: 12px; color: #333; background: #f8f9fa; padding: 10px; border-radius: 5px;">
                    ' . htmlspecialchars($claim['notes']) . '
                </div>
            </div>
            ' : '') . '
            
            <div class="footer">
                <p>This receipt serves as proof that the document has been claimed.</p>
                <p>Generated on: ' . date('F d, Y H:i:s') . '</p>
            </div>
        </div>
        
        <script>
            // Auto-print when page loads
            window.onload = function() {
                window.print();
            };
        </script>
    </body>
    </html>';
    
    echo $html;
    
} catch (Exception $e) {
    echo '<html><body><h1>Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p></body></html>';
}
?>
