<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit;
}

// Check if user is admin
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit;
}

include('includes/header.php'); 
include('includes/navbar.php'); 
?>

<style>
/* Excel-like preview styling */
.excel-preview-container {
    border: 2px solid #d0d7de;
    border-radius: 4px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.excel-table-container {
    overflow: auto;
    max-height: 400px;
}

.excel-preview-table {
    border-collapse: collapse;
    width: 100%;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 12px;
    background: #ffffff;
}

.excel-header-cell {
    background: #f1f3f4;
    border: 1px solid #dadce0;
    padding: 4px 8px;
    text-align: center;
    font-weight: bold;
    color: #5f6368;
    min-width: 30px;
    height: 25px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.excel-row-header {
    background: #f1f3f4 !important;
    border: 1px solid #dadce0;
    padding: 4px 8px;
    text-align: center;
    font-weight: bold;
    color: #5f6368;
    min-width: 30px;
    width: 30px;
    position: sticky;
    left: 0;
    z-index: 5;
}

.excel-col-header {
    background: #f1f3f4 !important;
    border: 1px solid #dadce0;
    padding: 4px 8px;
    text-align: center;
    font-weight: bold;
    color: #5f6368;
    min-width: 80px;
    height: 25px;
}

.excel-cell {
    border: 1px solid #dadce0;
    padding: 4px 8px;
    min-width: 80px;
    height: 25px;
    vertical-align: middle;
    background: #ffffff;
    color: #202124;
}

.excel-data-cell {
    border: 1px solid #dadce0;
    padding: 4px 8px;
    min-width: 80px;
    height: 25px;
    vertical-align: middle;
    background: #ffffff;
    color: #202124;
}

.excel-data-cell:hover {
    background: #f8f9fa;
}

/* Excel-like grid lines */
.excel-preview-table tr:nth-child(even) .excel-data-cell {
    background: #fafafa;
}

.excel-preview-table tr:nth-child(even) .excel-data-cell:hover {
    background: #f0f0f0;
}

/* Scrollbar styling */
.excel-table-container::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}

.excel-table-container::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.excel-table-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 6px;
}

.excel-table-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Modal adjustments for Excel preview */
.modal-lg {
    max-width: 90%;
}

@media (max-width: 768px) {
    .modal-lg {
        max-width: 95%;
    }
    
    .excel-preview-table {
        font-size: 10px;
    }
    
    .excel-cell, .excel-header-cell {
        padding: 2px 4px;
        min-width: 60px;
        height: 20px;
    }
}
</style>

<!-- Begin Page Content -->
<div class="container-fluid">

  <!-- Page Heading -->
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Timely Birth Records</h1>
    <div class="d-flex gap-2">
      <button class="btn btn-success btn-sm" onclick="exportToExcel()">
        <i class="fas fa-file-excel"></i> Export to Excel
      </button>
      <button class="btn btn-info btn-sm" onclick="generateAllFormattedExcel()">
        <i class="fas fa-magic"></i> Generate All Formatted
      </button>
      <button class="btn btn-info btn-sm" onclick="refreshData()">
        <i class="fas fa-sync-alt"></i> Refresh
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
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Submissions</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalSubmissions">0</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-file-alt fa-2x text-gray-300"></i>
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
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingSubmissions">0</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-clock fa-2x text-gray-300"></i>
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
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Processing</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="processingSubmissions">0</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-cogs fa-2x text-gray-300"></i>
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
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="completedSubmissions">0</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-check-circle fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <label for="statusFilter" class="form-label">Status</label>
              <select class="form-control" id="statusFilter" onchange="filterRecords()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="dateFrom" class="form-label">Date From</label>
              <input type="date" class="form-control" id="dateFrom" onchange="filterRecords()">
            </div>
            <div class="col-md-3">
              <label for="dateTo" class="form-label">Date To</label>
              <input type="date" class="form-control" id="dateTo" onchange="filterRecords()">
            </div>
            <div class="col-md-3">
              <label for="searchInput" class="form-label">Search</label>
              <input type="text" class="form-control" id="searchInput" placeholder="Search by name or submission number..." onkeyup="filterRecords()">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Records Table -->
  <div class="row">
    <div class="col-12">
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">Timely Birth Submissions</h6>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered" id="recordsTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th>Submission #</th>
                  <th>Requestor Name</th>
                  <th>Status</th>
                  <th>Submitted Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="recordsTableBody">
                <!-- Data will be loaded here -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
<!-- /.container-fluid -->

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="statusModalLabel">Update Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="statusForm">
          <input type="hidden" id="submissionId" name="submission_id">
          <div class="mb-3">
            <label for="newStatus" class="form-label">New Status</label>
            <select class="form-select" id="newStatus" name="status" required>
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="completed">Completed</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="statusNotes" class="form-label">Notes (Optional)</label>
            <textarea class="form-control" id="statusNotes" name="notes" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="updateStatus()">Update Status</button>
      </div>
    </div>
  </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsModalLabel">Submission Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="detailsModalBody">
        <!-- Details will be loaded here -->
      </div>
             <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
               <button type="button" class="btn btn-info" onclick="generateFormattedExcel(currentSubmissionId)">
                 <i class="fas fa-file-excel"></i> Generate Formatted Excel
               </button>
               <button type="button" class="btn btn-success" onclick="downloadExcelFile(currentSubmissionId)">
                 <i class="fas fa-download"></i> Download Original
               </button>
             </div>
    </div>
  </div>
</div>

<script>
let allRecords = [];
let currentSubmissionId = null;

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecords();
    loadStatistics();
});

// Create Excel-like preview
function createExcelPreview(data, details) {
    // Group data by rows
    const rows = {};
    data.forEach(item => {
        const match = item.field_name.match(/row_(\d+)_col_(\d+)/);
        if (match) {
            const rowNum = parseInt(match[1]);
            const colNum = parseInt(match[2]);
            
            if (!rows[rowNum]) {
                rows[rowNum] = {};
            }
            rows[rowNum][colNum] = item.field_value;
        }
    });
    
    // Find the maximum row and column
    const rowNumbers = Object.keys(rows).map(Number).sort((a, b) => a - b);
    if (rowNumbers.length === 0) {
        return '<div class="alert alert-info">No data available in this Excel file.</div>';
    }
    
    const maxRow = Math.max(...rowNumbers);
    const maxCol = Math.max(...Object.values(rows).map(row => {
        const cols = Object.keys(row).map(Number);
        return cols.length > 0 ? Math.max(...cols) : 0;
    }));
    
    // Create Excel-like table
    let html = `
        <div class="excel-table-container">
            <table class="excel-preview-table">
                <thead>
                    <tr>
                        <th class="excel-header-cell excel-row-header"></th>
    `;
    
    // Add column headers (A, B, C, etc.)
    for (let col = 1; col <= maxCol; col++) {
        const colLetter = String.fromCharCode(64 + col);
        html += `<th class="excel-header-cell excel-col-header">${colLetter}</th>`;
    }
    
    html += `
                    </tr>
                </thead>
                <tbody>
    `;
    
    // Add data rows
    for (let row = 1; row <= maxRow; row++) {
        html += `<tr>`;
        html += `<td class="excel-cell excel-row-header">${row}</td>`;
        
        for (let col = 1; col <= maxCol; col++) {
            const value = rows[row] && rows[row][col] ? rows[row][col] : '';
            html += `<td class="excel-cell excel-data-cell">${value}</td>`;
        }
        
        html += `</tr>`;
    }
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    return html;
}

// Load records from API
function loadRecords() {
    fetch('api/get_timely_birth_records.php')
        .then(response => response.json())
        .then(data => {
            console.log('Records loaded:', data);
            if (data.success) {
                allRecords = data.records;
                displayRecords(allRecords);
            } else {
                console.error('Error loading records:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Load statistics
function loadStatistics() {
    fetch('api/get_timely_birth_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalSubmissions').textContent = data.stats.total;
                document.getElementById('pendingSubmissions').textContent = data.stats.pending;
                document.getElementById('processingSubmissions').textContent = data.stats.processing;
                document.getElementById('completedSubmissions').textContent = data.stats.completed;
            }
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
}

// Display records in table
function displayRecords(records) {
    const tbody = document.getElementById('recordsTableBody');
    tbody.innerHTML = '';
    
    records.forEach(record => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${record.submission_number}</td>
            <td>${record.requestor_name}</td>
            <td><span class="badge badge-${getStatusBadgeClass(record.status)}">${record.status.toUpperCase()}</span></td>
            <td>${new Date(record.created_at).toLocaleDateString()}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick="viewDetails(${record.id})" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="updateStatusModal(${record.id})" title="Update Status">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-success" onclick="downloadExcelFile(${record.id})" title="Download Excel">
                    <i class="fas fa-download"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Get status badge class
function getStatusBadgeClass(status) {
    switch(status) {
        case 'pending': return 'warning';
        case 'processing': return 'info';
        case 'completed': return 'success';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}

// Filter records
function filterRecords() {
    const statusFilter = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    
    let filteredRecords = allRecords.filter(record => {
        // Status filter
        if (statusFilter && record.status !== statusFilter) return false;
        
        // Date filter
        if (dateFrom && new Date(record.created_at) < new Date(dateFrom)) return false;
        if (dateTo && new Date(record.created_at) > new Date(dateTo)) return false;
        
        // Search filter
        if (searchInput) {
            const searchText = `${record.submission_number} ${record.requestor_name}`.toLowerCase();
            if (!searchText.includes(searchInput)) return false;
        }
        
        return true;
    });
    
    displayRecords(filteredRecords);
}

// View details
function viewDetails(submissionId) {
    console.log('View details clicked for ID:', submissionId);
    currentSubmissionId = submissionId;
    
    // Show loading state
    document.getElementById('detailsModalBody').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading Excel preview...</p>
        </div>
    `;
    
    const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
    detailsModal.show();
    
    fetch(`api/get_timely_birth_details.php?id=${submissionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const details = data.details;
                // Create Excel-like preview
                const excelPreview = createExcelPreview(data.data, details);
                
                document.getElementById('detailsModalBody').innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Submission Information</h6>
                            <p><strong>Submission Number:</strong> ${details.submission_number}</p>
                            <p><strong>Requestor Name:</strong> ${details.requestor_name}</p>
                            <p><strong>Status:</strong> <span class="badge bg-${getStatusBadgeClass(details.status)}">${details.status.toUpperCase()}</span></p>
                            <p><strong>Submitted Date:</strong> ${new Date(details.created_at).toLocaleString()}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>File Information</h6>
                            <p><strong>Excel File:</strong> ${details.excel_file_path ? 'Available' : 'Not available'}</p>
                            <p><strong>Data Records:</strong> ${data.data_count} fields</p>
                        </div>
                    </div>
                    <hr>
                    <h6>Excel File Preview</h6>
                    <div class="excel-preview-container">
                        ${excelPreview}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading details:', error);
        });
}

// Update status modal
function updateStatusModal(submissionId) {
    console.log('Update status clicked for ID:', submissionId);
    currentSubmissionId = submissionId;
    const record = allRecords.find(r => r.id == submissionId);
    if (record) {
        document.getElementById('newStatus').value = record.status;
    }
    const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    statusModal.show();
}

// Update status
function updateStatus() {
    const status = document.getElementById('newStatus').value;
    const notes = document.getElementById('statusNotes').value;
    
    fetch('api/update_timely_birth_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            submission_id: currentSubmissionId,
            status: status,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const statusModal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
            statusModal.hide();
            loadRecords();
            loadStatistics();
            alert('Status updated successfully!');
        } else {
            alert('Error updating status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
}

// Download Excel file
function downloadExcelFile(submissionId) {
    window.open(`api/download_timely_birth_excel.php?id=${submissionId}`, '_blank');
}

// Generate formatted Excel file
function generateFormattedExcel(submissionId) {
    console.log('Generate formatted Excel clicked for ID:', submissionId);
    window.open(`api/generate_timely_birth_excel.php?id=${submissionId}`, '_blank');
}

// Export to Excel
function exportToExcel() {
    window.open('api/export_timely_birth_excel.php', '_blank');
}

// Generate all formatted Excel files
function generateAllFormattedExcel() {
    if (confirm('This will generate formatted Excel files for all pending submissions. Continue?')) {
        // For now, we'll show a message. In a real implementation, you might want to create a bulk generation API
        alert('Bulk generation feature coming soon! For now, use the "Generate Formatted Excel" button in individual record details.');
    }
}

// Refresh data
function refreshData() {
    loadRecords();
    loadStatistics();
}
</script>

<?php include('includes/footer.php'); ?>
<?php include('includes/script.php'); ?>
