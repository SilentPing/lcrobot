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
            QR Code Management Dashboard
        </h1>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="window.open('qr_scanner.php', '_blank')">
                <i class="fas fa-camera me-1"></i>
                Open Scanner
            </button>
            <button class="btn btn-success" onclick="window.open('qr_analytics.php', '_blank')">
                <i class="fas fa-chart-line me-1"></i>
                View Analytics
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total QR Codes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalQRCodes">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-qrcode fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active QR Codes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="activeQRCodes">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Claimed Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="claimedToday">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Expiring Soon</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="expiringSoon">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card border-primary h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-camera fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">QR Scanner</h5>
                                    <p class="card-text">Scan QR codes to claim documents</p>
                                    <button class="btn btn-primary" onclick="window.open('qr_scanner.php', '_blank')">
                                        <i class="fas fa-external-link-alt me-1"></i>
                                        Open Scanner
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card border-success h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                                    <h5 class="card-title">Analytics</h5>
                                    <p class="card-text">View QR code statistics and trends</p>
                                    <button class="btn btn-success" onclick="window.open('qr_analytics.php', '_blank')">
                                        <i class="fas fa-external-link-alt me-1"></i>
                                        View Analytics
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card border-info h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-alt fa-3x text-info mb-3"></i>
                                    <h5 class="card-title">Approved Requests</h5>
                                    <p class="card-text">Generate QR codes for approved documents</p>
                                    <button class="btn btn-info" onclick="window.open('approved_request.php', '_blank')">
                                        <i class="fas fa-external-link-alt me-1"></i>
                                        Manage QR Codes
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card border-warning h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-print fa-3x text-warning mb-3"></i>
                                    <h5 class="card-title">Print Receipts</h5>
                                    <p class="card-text">Print QR code receipts for clients</p>
                                    <button class="btn btn-warning" onclick="showPrintModal()">
                                        <i class="fas fa-print me-1"></i>
                                        Print Receipt
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent QR Activity -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>
                        Recent QR Activity
                    </h6>
                </div>
                <div class="card-body">
                    <div id="recentActivity">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        QR Code Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb me-2"></i>How QR Codes Work:</h6>
                        <ul class="mb-0 small">
                            <li><strong>Generate:</strong> QR codes are created when documents are approved</li>
                            <li><strong>Print:</strong> Receipts with QR codes are given to clients</li>
                            <li><strong>Scan:</strong> Admin scans QR code when client claims document</li>
                            <li><strong>Verify:</strong> System validates QR code and marks as claimed</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Notes:</h6>
                        <ul class="mb-0 small">
                            <li>QR codes expire after 30 days</li>
                            <li>Each QR code can only be used once</li>
                            <li>Expired QR codes cannot be claimed</li>
                            <li>Keep QR codes secure and confidential</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Receipt Modal -->
<div class="modal fade" id="printModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-print me-2"></i>
                    Print QR Receipt
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="printReference" class="form-label">Enter Reference Number:</label>
                    <input type="text" class="form-control" id="printReference" 
                           placeholder="LCRO-BIR-2024-001234">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print me-1"></i>
                    Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadQRStats();
    loadRecentActivity();
});

function loadQRStats() {
    fetch('api/get_qr_analytics.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('totalQRCodes').textContent = data.statistics.total;
            document.getElementById('activeQRCodes').textContent = data.statistics.active;
            document.getElementById('claimedToday').textContent = getClaimedToday(data.recentActivity);
            document.getElementById('expiringSoon').textContent = getExpiringSoon(data.qrCodes);
        }
    })
    .catch(error => {
        console.error('Error loading QR stats:', error);
    });
}

function loadRecentActivity() {
    fetch('api/get_qr_analytics.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateRecentActivity(data.recentActivity);
        }
    })
    .catch(error => {
        console.error('Error loading recent activity:', error);
    });
}

function updateRecentActivity(activities) {
    let html = '';
    if (activities.length === 0) {
        html = '<div class="text-center text-muted"><i class="fas fa-inbox fa-2x mb-2"></i><p>No recent activity</p></div>';
    } else {
        activities.slice(0, 10).forEach(activity => {
            const icon = getActivityIcon(activity.type);
            const color = getActivityColor(activity.type);
            html += `
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="${icon} ${color}"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="small text-gray-800">${activity.description}</div>
                        <div class="small text-muted">${formatDate(activity.created_at)}</div>
                    </div>
                </div>
            `;
        });
    }
    document.getElementById('recentActivity').innerHTML = html;
}

function getActivityIcon(type) {
    const icons = {
        'generated': 'fas fa-qrcode',
        'claimed': 'fas fa-hand-holding',
        'expired': 'fas fa-exclamation-triangle',
        'regenerated': 'fas fa-sync-alt'
    };
    return icons[type] || 'fas fa-info-circle';
}

function getActivityColor(type) {
    const colors = {
        'generated': 'text-success',
        'claimed': 'text-info',
        'expired': 'text-warning',
        'regenerated': 'text-primary'
    };
    return colors[type] || 'text-secondary';
}

function getClaimedToday(activities) {
    const today = new Date().toDateString();
    return activities.filter(activity => 
        activity.type === 'claimed' && 
        new Date(activity.created_at).toDateString() === today
    ).length;
}

function getExpiringSoon(qrCodes) {
    const threeDaysFromNow = new Date();
    threeDaysFromNow.setDate(threeDaysFromNow.getDate() + 3);
    
    return qrCodes.filter(qr => 
        qr.status === 'active' && 
        new Date(qr.expires_at) <= threeDaysFromNow
    ).length;
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

function showPrintModal() {
    const modal = new bootstrap.Modal(document.getElementById('printModal'));
    modal.show();
}

function printReceipt() {
    const reference = document.getElementById('printReference').value.trim();
    if (!reference) {
        Swal.fire('Error', 'Please enter a reference number', 'error');
        return;
    }
    
    window.open(`api/print_receipt.php?reference=${reference}`, '_blank');
    bootstrap.Modal.getInstance(document.getElementById('printModal')).hide();
}
</script>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/script.php'; ?>
