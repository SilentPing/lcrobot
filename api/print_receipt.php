<?php
session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$reference = $_GET['reference'] ?? '';

if (empty($reference)) {
    die('Reference number is required');
}

try {
    // Get document details
    $stmt = $conn->prepare("
        SELECT 
            qr.reference_number,
            qr.document_type,
            qr.generated_at,
            qr.expires_at,
            qr.claimed_at,
            qr.status,
            ar.request_id,
            u.u_fn,
            u.u_ln,
            u.contact_no,
            u.email,
            u.house_no,
            u.street_brgy,
            u.city_municipality,
            u.province
        FROM qr_codes qr
        LEFT JOIN approved_requests ar ON qr.reference_number = ar.qr_reference
        LEFT JOIN users u ON qr.user_id = u.id_user
        WHERE qr.reference_number = ?
    ");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die('Document not found');
    }
    
    $document = $result->fetch_assoc();
    
    // Generate QR code image URL
    $qr_image_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($reference);
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Receipt - <?php echo $reference; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
            background: white;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 18px;
            color: #666;
        }
        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }
        .qr-code {
            margin: 10px 0;
        }
        .reference {
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        .details {
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        .detail-label {
            font-weight: bold;
            color: #333;
        }
        .detail-value {
            color: #666;
            text-align: right;
        }
        .status {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .status.active {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.claimed {
            background: #cce5ff;
            color: #004085;
            border: 1px solid #b3d7ff;
        }
        .status.expired {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #333;
            font-size: 12px;
            color: #666;
        }
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .instructions h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .instructions ul {
            margin: 0;
            padding-left: 20px;
            color: #856404;
        }
        @media print {
            body { margin: 0; padding: 10px; }
            .receipt { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>LOCAL CIVIL REGISTRY OFFICE BOTOLAN, ZAMBALES</h1>
            <h2>Document Receipt</h2>
        </div>
        
        <div class="qr-section">
            <div class="qr-code">
                <img src="<?php echo $qr_image_url; ?>" alt="QR Code" style="width: 150px; height: 150px;">
            </div>
            <div class="reference"><?php echo $reference; ?></div>
        </div>
        
        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Document Type:</span>
                <span class="detail-value"><?php echo htmlspecialchars($document['document_type']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Requestor:</span>
                <span class="detail-value"><?php echo htmlspecialchars($document['u_fn'] . ' ' . $document['u_ln']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact:</span>
                <span class="detail-value"><?php echo htmlspecialchars($document['contact_no']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Generated:</span>
                <span class="detail-value"><?php echo date('M d, Y H:i', strtotime($document['generated_at'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Expires:</span>
                <span class="detail-value"><?php echo date('M d, Y H:i', strtotime($document['expires_at'])); ?></span>
            </div>
            <?php if ($document['claimed_at']): ?>
            <div class="detail-row">
                <span class="detail-label">Claimed:</span>
                <span class="detail-value"><?php echo date('M d, Y H:i', strtotime($document['claimed_at'])); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="status <?php echo $document['status']; ?>">
            <strong>Status: <?php echo strtoupper($document['status']); ?></strong>
        </div>
        
        <?php if ($document['status'] === 'active'): ?>
        <div class="instructions">
            <h4>📋 Claiming Instructions:</h4>
            <ul>
                <li>Present this receipt at the LCRO counter</li>
                <li>Show valid ID for verification</li>
                <li>QR code will be scanned for validation</li>
                <li>Document will be released upon verification</li>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p><strong>Local Civil Registry Office</strong></p>
            <p>Municipality of Botolan, Zambales</p>
            <p>Generated on <?php echo date('F d, Y \a\t H:i A'); ?></p>
            <p>For inquiries, contact: 090-5280-3518</p>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
