<?php
session_start();
require_once __DIR__ . '/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Reports Dashboard</h1>
        </div>

        <!-- Quick Reports Row -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Quick Reports</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-primary btn-block w-100" onclick="generateReport('daily')">
                                    <i class="fas fa-calendar-day"></i> Today's Report
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-success btn-block w-100" onclick="generateReport('weekly')">
                                    <i class="fas fa-calendar-week"></i> This Week
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-info btn-block w-100" onclick="generateReport('monthly')">
                                    <i class="fas fa-calendar-alt"></i> This Month
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-warning btn-block w-100" onclick="showCustomReport()">
                                    <i class="fas fa-calendar"></i> Custom Period
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Content Area -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Report Results</h6>
                    </div>
                    <div class="card-body" id="reportContent">
                        <div class="text-center text-muted">
                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                            <p>Select a report type above to generate your report</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
include('includes/script.php');
include('includes/footer.php');
?>

    <!-- Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        let currentReportData = null;
        
        function generateReport(type) {
            document.getElementById('reportContent').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p>Generating report...</p></div>';
            
            // Simple AJAX call to generate report
            fetch('api/generate_report.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({type: type})
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    currentReportData = data;
                    document.getElementById('reportContent').innerHTML = data.html;
                    // Initialize charts after content is loaded
                    setTimeout(initializeCharts, 100);
                } else {
                    document.getElementById('reportContent').innerHTML = '<div class="alert alert-danger">Error: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('reportContent').innerHTML = '<div class="alert alert-danger">Error generating report. Please try again.</div>';
            });
        }
        
        function showCustomReport() {
            // Create custom date range modal
            const modal = `
                <div class="modal fade" id="customReportModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Custom Report Period</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="startDate" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="startDate" value="${new Date().toISOString().split('T')[0]}">
                                </div>
                                <div class="mb-3">
                                    <label for="endDate" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="endDate" value="${new Date().toISOString().split('T')[0]}">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="generateCustomReport()">Generate Report</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('customReportModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modal);
            
            // Show modal
            const modalInstance = new bootstrap.Modal(document.getElementById('customReportModal'));
            modalInstance.show();
        }
        
        function generateCustomReport() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }
            
            if (new Date(startDate) > new Date(endDate)) {
                alert('Start date cannot be after end date');
                return;
            }
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('customReportModal'));
            modal.hide();
            
            // Generate report with custom dates
            document.getElementById('reportContent').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p>Generating custom report...</p></div>';
            
            fetch('api/generate_report.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({type: 'custom', startDate: startDate, endDate: endDate})
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    currentReportData = data;
                    document.getElementById('reportContent').innerHTML = data.html;
                    setTimeout(initializeCharts, 100);
                } else {
                    document.getElementById('reportContent').innerHTML = '<div class="alert alert-danger">Error: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('reportContent').innerHTML = '<div class="alert alert-danger">Error generating custom report. Please try again.</div>';
            });
        }
        
        function initializeCharts() {
            if (!currentReportData || !currentReportData.chartData) return;
            
            // Initialize request type chart
            if (currentReportData.chartData.byType) {
                const ctx1 = document.getElementById('requestTypeChart');
                if (ctx1) {
                    new Chart(ctx1, {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(currentReportData.chartData.byType),
                            datasets: [{
                                data: Object.values(currentReportData.chartData.byType),
                                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1']
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            }
            
            // Initialize daily breakdown chart
            if (currentReportData.chartData.dailyBreakdown) {
                const ctx2 = document.getElementById('dailyBreakdownChart');
                if (ctx2) {
                    const dates = Object.keys(currentReportData.chartData.dailyBreakdown);
                    const counts = Object.values(currentReportData.chartData.dailyBreakdown);
                    
                    new Chart(ctx2, {
                        type: 'line',
                        data: {
                            labels: dates.map(date => new Date(date).toLocaleDateString()),
                            datasets: [{
                                label: 'Requests',
                                data: counts,
                                borderColor: '#007bff',
                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }
        }
        
        function exportPDF(startDate, endDate) {
            window.open(`api/export_pdf.php?start=${startDate}&end=${endDate}`, '_blank');
        }
        
        function exportExcel(startDate, endDate) {
            window.open(`api/export_excel.php?start=${startDate}&end=${endDate}`, '_blank');
        }
        
        function printReport() {
            window.print();
        }
    </script>
</body>
</html>
