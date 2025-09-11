<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['name']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-qrcode text-primary me-2"></i>
            QR Code Scanner
        </h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="toggleScanner()">
                <i class="fas fa-camera me-1"></i>
                <span id="scannerToggleText">Start Scanner</span>
            </button>
            <button class="btn btn-outline-secondary" onclick="showManualEntry()">
                <i class="fas fa-keyboard me-1"></i>
                Manual Entry
            </button>
        </div>
    </div>

    <!-- Scanner Section -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-camera me-2"></i>
                        Document Claim Scanner
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Camera Scanner -->
                    <div id="scannerContainer" class="text-center" style="display: none;">
                        <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
                        <div class="mt-3">
                            <button class="btn btn-danger" onclick="stopScanner()">
                                <i class="fas fa-stop me-1"></i>
                                Stop Scanner
                            </button>
                        </div>
                    </div>
                    
                    <!-- Manual Entry Form -->
                    <div id="manualEntryContainer" class="text-center">
                        <div class="mb-3">
                            <label for="manualReference" class="form-label">Enter Reference Number:</label>
                            <input type="text" class="form-control form-control-lg text-center" 
                                   id="manualReference" placeholder="LCRO-BIR-2024-001234"
                                   style="font-family: monospace; font-size: 1.2rem;">
                        </div>
                        <button class="btn btn-primary btn-lg" onclick="processManualEntry()">
                            <i class="fas fa-search me-2"></i>
                            Verify Document
                        </button>
                    </div>
                    
                    <!-- Scanner Instructions -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle me-2"></i>How to Use:</h6>
                        <ul class="mb-0">
                            <li><strong>QR Scanner:</strong> Click "Start Scanner" and point camera at QR code</li>
                            <li><strong>Manual Entry:</strong> Type the reference number if QR code is damaged</li>
                            <li><strong>Claim Process:</strong> Verify document details and mark as claimed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Document Details Panel -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt me-2"></i>
                        Document Details
                    </h6>
                </div>
                <div class="card-body" id="documentDetails">
                    <div class="text-center text-muted">
                        <i class="fas fa-qrcode fa-3x mb-3"></i>
                        <p>Scan a QR code or enter reference number to view document details</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Claims -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>
                        Recent Claims
                    </h6>
                </div>
                <div class="card-body">
                    <div id="recentClaims">
                        <div class="text-center text-muted">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <p class="small">No recent claims</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Claim Document Modal -->
<div class="modal fade" id="claimModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Claim Document
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="claimModalBody">
                <!-- Document details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmClaim()">
                    <i class="fas fa-check me-1"></i>
                    Confirm Claim
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include QR Scanner Library -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
let html5QrcodeScanner = null;
let currentReference = null;

function toggleScanner() {
    const container = document.getElementById('scannerContainer');
    const toggleText = document.getElementById('scannerToggleText');
    
    if (container.style.display === 'none') {
        startScanner();
        container.style.display = 'block';
        toggleText.textContent = 'Stop Scanner';
    } else {
        stopScanner();
        container.style.display = 'none';
        toggleText.textContent = 'Start Scanner';
    }
}

function startScanner() {
    html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-reader",
        { 
            fps: 10, 
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        },
        false
    );
    
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
}

function stopScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear().catch(err => {
            console.error("Error stopping scanner:", err);
        });
        html5QrcodeScanner = null;
    }
}

function onScanSuccess(decodedText, decodedResult) {
    console.log(`QR Code detected: ${decodedText}`);
    processQRCode(decodedText);
    stopScanner();
    document.getElementById('scannerContainer').style.display = 'none';
    document.getElementById('scannerToggleText').textContent = 'Start Scanner';
}

function onScanFailure(error) {
    // Handle scan failure silently
}

function showManualEntry() {
    document.getElementById('scannerContainer').style.display = 'none';
    document.getElementById('manualEntryContainer').style.display = 'block';
    stopScanner();
    document.getElementById('scannerToggleText').textContent = 'Start Scanner';
}

function processManualEntry() {
    const reference = document.getElementById('manualReference').value.trim();
    if (!reference) {
        Swal.fire('Error', 'Please enter a reference number', 'error');
        return;
    }
    processQRCode(reference);
}

function processQRCode(reference) {
    currentReference = reference;
    
    // Show loading
    document.getElementById('documentDetails').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Verifying document...</p>
        </div>
    `;
    
    // Fetch document details
    fetch('api/verify_qr_code.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reference: reference })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayDocumentDetails(data.data);
        } else {
            document.getElementById('documentDetails').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('documentDetails').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error verifying document
            </div>
        `;
    });
}

function displayDocumentDetails(data) {
    const statusBadge = getStatusBadge(data.status);
    const expiresBadge = getExpiresBadge(data.expires_at);
    
    document.getElementById('documentDetails').innerHTML = `
        <div class="mb-3">
            <h6 class="text-primary">${data.reference_number}</h6>
            <span class="badge ${statusBadge.class}">${statusBadge.text}</span>
            ${expiresBadge}
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <small class="text-muted">Document Type</small>
                <div class="fw-bold">${data.document_type}</div>
            </div>
            <div class="col-6">
                <small class="text-muted">Generated</small>
                <div class="fw-bold">${formatDate(data.generated_at)}</div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <small class="text-muted">Requestor</small>
                <div class="fw-bold">${data.requestor_name}</div>
            </div>
            <div class="col-6">
                <small class="text-muted">Contact</small>
                <div class="fw-bold">${data.contact_no}</div>
            </div>
        </div>
        
        <div class="d-grid gap-2">
            ${data.status === 'active' ? `
                <button class="btn btn-success" onclick="showClaimModal('${data.reference_number}')">
                    <i class="fas fa-check me-1"></i>
                    Claim Document
                </button>
            ` : `
                <button class="btn btn-secondary" disabled>
                    <i class="fas fa-ban me-1"></i>
                    Already Claimed
                </button>
            `}
            <button class="btn btn-outline-primary" onclick="printReceipt('${data.reference_number}')">
                <i class="fas fa-print me-1"></i>
                Print Receipt
            </button>
        </div>
    `;
}

function getStatusBadge(status) {
    const badges = {
        'active': { class: 'bg-success', text: 'Active' },
        'claimed': { class: 'bg-info', text: 'Claimed' },
        'expired': { class: 'bg-warning', text: 'Expired' }
    };
    return badges[status] || { class: 'bg-secondary', text: 'Unknown' };
}

function getExpiresBadge(expiresAt) {
    const expires = new Date(expiresAt);
    const now = new Date();
    const daysLeft = Math.ceil((expires - now) / (1000 * 60 * 60 * 24));
    
    if (daysLeft < 0) {
        return '<span class="badge bg-danger">Expired</span>';
    } else if (daysLeft <= 3) {
        return `<span class="badge bg-warning">Expires in ${daysLeft} days</span>`;
    } else {
        return `<span class="badge bg-info">Expires in ${daysLeft} days</span>`;
    }
}

function showClaimModal(reference) {
    // Load claim modal content
    document.getElementById('claimModalBody').innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Confirm that the document with reference <strong>${reference}</strong> has been claimed by the requestor.
        </div>
        <div class="mb-3">
            <label for="claimNotes" class="form-label">Notes (Optional)</label>
            <textarea class="form-control" id="claimNotes" rows="3" 
                      placeholder="Any additional notes about the claim..."></textarea>
        </div>
    `;
    
    // Try Bootstrap 5 modal first, fallback to jQuery if needed
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = new bootstrap.Modal(document.getElementById('claimModal'));
            modal.show();
        } else if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#claimModal').modal('show');
        } else {
            // Fallback: show modal using basic JavaScript
            document.getElementById('claimModal').style.display = 'block';
            document.getElementById('claimModal').classList.add('show');
            document.body.classList.add('modal-open');
        }
    } catch (error) {
        console.error('Error showing modal:', error);
        // Fallback: show modal using basic JavaScript
        document.getElementById('claimModal').style.display = 'block';
        document.getElementById('claimModal').classList.add('show');
        document.body.classList.add('modal-open');
    }
}

function closeModal() {
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('claimModal'));
            if (modal) {
                modal.hide();
            } else {
                // Fallback: hide modal using basic JavaScript
                document.getElementById('claimModal').style.display = 'none';
                document.getElementById('claimModal').classList.remove('show');
                document.body.classList.remove('modal-open');
            }
        } else if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#claimModal').modal('hide');
        } else {
            // Fallback: hide modal using basic JavaScript
            document.getElementById('claimModal').style.display = 'none';
            document.getElementById('claimModal').classList.remove('show');
            document.body.classList.remove('modal-open');
        }
    } catch (error) {
        console.error('Error closing modal:', error);
        // Fallback: hide modal using basic JavaScript
        document.getElementById('claimModal').style.display = 'none';
        document.getElementById('claimModal').classList.remove('show');
        document.body.classList.remove('modal-open');
    }
}

function confirmClaim() {
    const notes = document.getElementById('claimNotes').value;
    
    fetch('api/claim_document.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            reference: currentReference,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Document claimed successfully!', 'success');
            closeModal();
            processQRCode(currentReference); // Refresh details
            loadRecentClaims();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Failed to claim document', 'error');
    });
}

function printReceipt(reference) {
    window.open(`api/print_receipt.php?reference=${reference}`, '_blank');
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function loadRecentClaims() {
    fetch('api/get_recent_claims.php')
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.length > 0) {
            let html = '';
            data.data.forEach(claim => {
                html += `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="fw-bold small">${claim.reference}</div>
                            <div class="text-muted small">${formatDate(claim.claimed_at)}</div>
                        </div>
                        <span class="badge bg-success">Claimed</span>
                    </div>
                `;
            });
            document.getElementById('recentClaims').innerHTML = html;
        }
    })
    .catch(error => {
        console.error('Error loading recent claims:', error);
    });
}

// Load recent claims on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecentClaims();
});
</script>

<?php include 'includes/script.php'; ?>
<?php include 'includes/footer.php'; ?>
