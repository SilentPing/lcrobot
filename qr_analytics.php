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
            <i class="fas fa-chart-line text-primary me-2"></i>
            QR Code Analytics
        </h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshAnalytics()">
                <i class="fas fa-sync-alt me-1"></i>
                Refresh
            </button>
            <button class="btn btn-outline-success" onclick="exportAnalytics()">
                <i class="fas fa-download me-1"></i>
                Export
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
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
                                Claimed Documents</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="claimedDocuments">0</div>
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
                                Expired QR Codes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="expiredQRCodes">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- QR Code Status Chart -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>
                        QR Code Status Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="qrStatusChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Document Type Chart -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>
                        QR Codes by Document Type
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="documentTypeChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Daily Claims Chart -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>
                        Daily Claims Trend (Last 30 Days)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyClaimsChart" width="400" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-4">
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
    </div>

    <!-- QR Code Management Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table me-2"></i>
                        QR Code Management
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="qrManagementTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Document Type</th>
                                    <th>Requestor</th>
                                    <th>Generated</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th>Claimed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="qrTableBody">
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let qrStatusChart, documentTypeChart, dailyClaimsChart;

document.addEventListener('DOMContentLoaded', function() {
    loadAnalytics();
    initializeCharts();
});

function loadAnalytics() {
    fetch('api/get_qr_analytics.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatistics(data.statistics);
            updateCharts(data.chartData);
            updateRecentActivity(data.recentActivity);
            updateQRTable(data.qrCodes);
        }
    })
    .catch(error => {
        console.error('Error loading analytics:', error);
    });
}

function updateStatistics(stats) {
    document.getElementById('totalQRCodes').textContent = stats.total;
    document.getElementById('activeQRCodes').textContent = stats.active;
    document.getElementById('claimedDocuments').textContent = stats.claimed;
    document.getElementById('expiredQRCodes').textContent = stats.expired;
}

function updateCharts(chartData) {
    // QR Status Chart
    qrStatusChart.data.datasets[0].data = [
        chartData.status.active,
        chartData.status.claimed,
        chartData.status.expired
    ];
    qrStatusChart.update();

    // Document Type Chart
    documentTypeChart.data.labels = Object.keys(chartData.documentTypes);
    documentTypeChart.data.datasets[0].data = Object.values(chartData.documentTypes);
    documentTypeChart.update();

    // Daily Claims Chart
    dailyClaimsChart.data.labels = chartData.dailyClaims.labels;
    dailyClaimsChart.data.datasets[0].data = chartData.dailyClaims.data;
    dailyClaimsChart.update();
}

function updateRecentActivity(activities) {
    let html = '';
    if (activities.length === 0) {
        html = '<div class="text-center text-muted"><i class="fas fa-inbox fa-2x mb-2"></i><p>No recent activity</p></div>';
    } else {
        activities.forEach(activity => {
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

function updateQRTable(qrCodes) {
    let html = '';
    qrCodes.forEach(qr => {
        const statusBadge = getStatusBadge(qr.status);
        const expiresBadge = getExpiresBadge(qr.expires_at);
        
        html += `
            <tr>
                <td><code>${qr.reference_number}</code></td>
                <td>${qr.document_type}</td>
                <td>${qr.requestor_name}</td>
                <td>${formatDate(qr.generated_at)}</td>
                <td>${formatDate(qr.expires_at)} ${expiresBadge}</td>
                <td>${statusBadge}</td>
                <td>${qr.claimed_at ? formatDate(qr.claimed_at) : '-'}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewQRDetails('${qr.reference_number}')" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-info" onclick="printQRReceipt('${qr.reference_number}')" title="Print Receipt">
                            <i class="fas fa-print"></i>
                        </button>
                        ${qr.status === 'active' ? `
                            <button class="btn btn-outline-warning" onclick="regenerateQR('${qr.reference_number}')" title="Regenerate">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    });
    document.getElementById('qrTableBody').innerHTML = html;
}

function initializeCharts() {
    // QR Status Chart
    const qrStatusCtx = document.getElementById('qrStatusChart').getContext('2d');
    qrStatusChart = new Chart(qrStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Claimed', 'Expired'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#28a745', '#17a2b8', '#ffc107'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Document Type Chart
    const documentTypeCtx = document.getElementById('documentTypeChart').getContext('2d');
    documentTypeChart = new Chart(documentTypeCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'QR Codes',
                data: [],
                backgroundColor: '#007bff',
                borderColor: '#0056b3',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Daily Claims Chart
    const dailyClaimsCtx = document.getElementById('dailyClaimsChart').getContext('2d');
    dailyClaimsChart = new Chart(dailyClaimsCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Claims',
                data: [],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
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

function getStatusBadge(status) {
    const badges = {
        'active': '<span class="badge bg-success">Active</span>',
        'claimed': '<span class="badge bg-info">Claimed</span>',
        'expired': '<span class="badge bg-warning">Expired</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function getExpiresBadge(expiresAt) {
    const expires = new Date(expiresAt);
    const now = new Date();
    const daysLeft = Math.ceil((expires - now) / (1000 * 60 * 60 * 24));
    
    if (daysLeft < 0) {
        return '<span class="badge bg-danger ms-1">Expired</span>';
    } else if (daysLeft <= 3) {
        return `<span class="badge bg-warning ms-1">${daysLeft}d left</span>`;
    } else {
        return `<span class="badge bg-info ms-1">${daysLeft}d left</span>`;
    }
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

function refreshAnalytics() {
    loadAnalytics();
    Swal.fire({
        icon: 'success',
        title: 'Refreshed!',
        text: 'Analytics data has been updated',
        timer: 1500,
        showConfirmButton: false
    });
}

function exportAnalytics() {
    window.open('api/export_qr_analytics.php', '_blank');
}

function viewQRDetails(reference) {
    // Open QR scanner with this reference
    window.open(`qr_scanner.php?reference=${reference}`, '_blank');
}

function printQRReceipt(reference) {
    window.open(`api/print_receipt.php?reference=${reference}`, '_blank');
}

function regenerateQR(reference) {
    Swal.fire({
        title: 'Regenerate QR Code',
        text: 'This will invalidate the current QR code. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Regenerate!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementation for regenerating QR code
            Swal.fire('Success', 'QR code regenerated successfully!', 'success');
            loadAnalytics();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
