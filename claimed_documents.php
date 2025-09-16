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

<!-- Begin Page Content -->
<div class="container-fluid">

  <!-- Page Heading -->
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
      <i class="fas fa-check-circle text-success"></i> Claimed Documents Management
    </h1>
    <div class="d-flex gap-2">
      <button class="btn btn-success btn-sm" onclick="exportToPDF()">
        <i class="fas fa-file-pdf"></i> Export PDF
      </button>
      <button class="btn btn-info btn-sm" onclick="exportToExcel()">
        <i class="fas fa-file-excel"></i> Export Excel
      </button>
    </div>
  </div>

  <!-- Search and Filter Section -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">
        <i class="fas fa-search"></i> Search & Filter Claims
      </h6>
    </div>
    <div class="card-body">
      <form id="searchForm" class="row g-3">
        <div class="col-md-4">
          <label for="searchName" class="form-label">Search by Requestor Name</label>
          <input type="text" class="form-control" id="searchName" name="searchName" 
                 placeholder="Enter requestor name...">
        </div>
        <div class="col-md-3">
          <label for="documentType" class="form-label">Document Type</label>
          <select class="form-control" id="documentType" name="documentType">
            <option value="">All Types</option>
            <option value="Birth Certificate">Birth Certificate</option>
            <option value="Marriage Certificate">Marriage Certificate</option>
            <option value="Death Certificate">Death Certificate</option>
          </select>
        </div>
        <div class="col-md-2">
          <label for="dateFrom" class="form-label">From Date</label>
          <input type="date" class="form-control" id="dateFrom" name="dateFrom">
        </div>
        <div class="col-md-2">
          <label for="dateTo" class="form-label">To Date</label>
          <input type="date" class="form-control" id="dateTo" name="dateTo">
        </div>
        <div class="col-md-1 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Claims</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalClaims">0</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-check-circle fa-2x text-success"></i>
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
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Today's Claims</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="todayClaims">0</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-calendar-day fa-2x text-info"></i>
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
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">This Week</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="weekClaims">0</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-calendar-week fa-2x text-warning"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">This Month</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthClaims">0</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-calendar-alt fa-2x text-primary"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Claims Table -->
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">
        <i class="fas fa-list"></i> Claimed Documents
      </h6>
      <div class="d-flex align-items-center">
        <span class="text-muted mr-3">Total Records: <span id="totalRecords">0</span></span>
        <div class="spinner-border spinner-border-sm text-primary" id="loadingSpinner" role="status" style="display: none;">
          <span class="sr-only">Loading...</span>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="claimsTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>QR Reference</th>
              <th>Requestor Name</th>
              <th>Document Type</th>
              <th>Claimed By</th>
              <th>Admin Processed</th>
              <th>Claim Date</th>
              <th>Notes</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="claimsTableBody">
            <!-- Data will be loaded here -->
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <nav aria-label="Claims pagination" class="mt-3">
        <ul class="pagination justify-content-center" id="pagination">
          <!-- Pagination will be generated here -->
        </ul>
      </nav>
    </div>
  </div>

</div>
<!-- /.container-fluid -->

<!-- Claim Details Modal -->
<div class="modal fade" id="claimDetailsModal" tabindex="-1" role="dialog" aria-labelledby="claimDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="claimDetailsModalLabel">
          <i class="fas fa-info-circle text-primary"></i> Claim Details
        </h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="claimDetailsBody">
        <!-- Details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="printClaimDetails()">
          <i class="fas fa-print"></i> Print
        </button>
      </div>
    </div>
  </div>
</div>

<?php include('includes/script.php'); ?>
<?php include('includes/footer.php'); ?>

<script>
$(document).ready(function() {
    let currentPage = 1;
    const recordsPerPage = 10;
    let totalRecords = 0;
    let currentFilters = {};

    // Load initial data
    loadClaimsData();

    // Search form submission
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        currentFilters = {
            searchName: $('#searchName').val(),
            documentType: $('#documentType').val(),
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val()
        };
        loadClaimsData();
    });

    // Load claims data
    function loadClaimsData() {
        $('#loadingSpinner').show();
        
        $.ajax({
            url: 'api/get_claimed_documents.php',
            type: 'GET',
            data: {
                page: currentPage,
                limit: recordsPerPage,
                ...currentFilters
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayClaimsData(response.data);
                    updateStatistics(response.statistics);
                    updatePagination(response.pagination);
                } else {
                    showError('Failed to load claims data: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                showError('Error loading claims data: ' + error);
            },
            complete: function() {
                $('#loadingSpinner').hide();
            }
        });
    }

    // Display claims data in table
    function displayClaimsData(data) {
        const tbody = $('#claimsTableBody');
        tbody.empty();

        if (data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-3x mb-3"></i><br>
                        No claimed documents found
                    </td>
                </tr>
            `);
            return;
        }

        data.forEach(function(claim) {
            const row = `
                <tr>
                    <td>
                        <span class="badge badge-primary">${claim.qr_reference}</span>
                    </td>
                    <td>${claim.requestor_name}</td>
                    <td>
                        <span class="badge badge-info">${claim.document_type}</span>
                    </td>
                    <td>${claim.claimed_by}</td>
                    <td>
                        <span class="badge badge-success">Admin #${claim.admin_id}</span>
                    </td>
                    <td>
                        <i class="fas fa-calendar text-muted"></i>
                        ${formatDateTime(claim.claimed_at)}
                    </td>
                    <td>
                        ${claim.notes ? claim.notes : '<span class="text-muted">No notes</span>'}
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewClaimDetails('${claim.qr_reference}')" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-success" onclick="printClaimReceipt('${claim.qr_reference}')" title="Print Receipt">
                            <i class="fas fa-print"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Update statistics
    function updateStatistics(stats) {
        $('#totalClaims').text(stats.total || 0);
        $('#todayClaims').text(stats.today || 0);
        $('#weekClaims').text(stats.week || 0);
        $('#monthClaims').text(stats.month || 0);
        $('#totalRecords').text(stats.total || 0);
    }

    // Update pagination
    function updatePagination(pagination) {
        const paginationContainer = $('#pagination');
        paginationContainer.empty();

        if (pagination.totalPages <= 1) return;

        // Previous button
        const prevDisabled = currentPage === 1 ? 'disabled' : '';
        paginationContainer.append(`
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
            </li>
        `);

        // Page numbers
        for (let i = 1; i <= pagination.totalPages; i++) {
            const active = i === currentPage ? 'active' : '';
            paginationContainer.append(`
                <li class="page-item ${active}">
                    <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                </li>
            `);
        }

        // Next button
        const nextDisabled = currentPage === pagination.totalPages ? 'disabled' : '';
        paginationContainer.append(`
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
            </li>
        `);
    }

    // Change page
    window.changePage = function(page) {
        if (page >= 1) {
            currentPage = page;
            loadClaimsData();
        }
    };

    // View claim details
    window.viewClaimDetails = function(qrReference) {
        // Encode the QR reference to handle special characters
        const encodedReference = encodeURIComponent(qrReference);
        
        $.ajax({
            url: 'api/get_claim_details.php',
            type: 'GET',
            data: { qr_reference: qrReference },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayClaimDetails(response.data);
                    $('#claimDetailsModal').modal('show');
                } else {
                    console.error('API Error:', response);
                    showError('Failed to load claim details: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                let errorMessage = 'Error loading claim details: ' + error;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showError(errorMessage);
            }
        });
    };

    // Display claim details in modal
    function displayClaimDetails(data) {
        const modalBody = $('#claimDetailsBody');
        modalBody.html(`
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-bold text-primary">Document Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>QR Reference:</strong></td><td>${data.qr_reference}</td></tr>
                        <tr><td><strong>Document Type:</strong></td><td>${data.document_type}</td></tr>
                        <tr><td><strong>Requestor:</strong></td><td>${data.requestor_name}</td></tr>
                        <tr><td><strong>Contact:</strong></td><td>${data.contact_no || 'N/A'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold text-success">Claim Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Claimed By:</strong></td><td>${data.claimed_by}</td></tr>
                        <tr><td><strong>Admin ID:</strong></td><td>${data.admin_id}</td></tr>
                        <tr><td><strong>Claim Date:</strong></td><td>${formatDateTime(data.claimed_at)}</td></tr>
                        <tr><td><strong>Notes:</strong></td><td>${data.notes || 'No notes'}</td></tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="font-weight-bold text-warning">Request Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Status:</strong></td><td>${data.status || 'N/A'}</td></tr>
                        <tr><td><strong>Registrar Name:</strong></td><td>${data.registrar_name || 'N/A'}</td></tr>
                        <tr><td><strong>Registration Date:</strong></td><td>${data.registration_date ? formatDateTime(data.registration_date) : 'N/A'}</td></tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="font-weight-bold text-info">Timeline</h6>
                    <div class="timeline">
                        <div class="timeline-item">
                            <i class="fas fa-qrcode text-primary"></i>
                            <div class="timeline-content">
                                <strong>QR Code Generated</strong><br>
                                <small class="text-muted">${formatDateTime(data.generated_at)}</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <div class="timeline-content">
                                <strong>Document Claimed</strong><br>
                                <small class="text-muted">${formatDateTime(data.claimed_at)}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    // Print claim receipt
    window.printClaimReceipt = function(qrReference) {
        window.open(`api/print_claim_receipt.php?qr_reference=${qrReference}`, '_blank');
    };

    // Print claim details
    window.printClaimDetails = function() {
        const printContent = $('#claimDetailsBody').html();
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Claim Details - ${$('#claimDetailsModalLabel').text()}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
                    <style>
                        body { font-size: 12px; }
                        .timeline { position: relative; padding-left: 30px; }
                        .timeline-item { position: relative; margin-bottom: 20px; }
                        .timeline-item i { position: absolute; left: -25px; top: 0; }
                    </style>
                </head>
                <body>
                    <div class="container mt-3">
                        <h4>Claim Details Report</h4>
                        ${printContent}
                    </div>
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    };

    // Export to PDF
    window.exportToPDF = function() {
        const filters = Object.keys(currentFilters).length > 0 ? 
            '?filters=' + encodeURIComponent(JSON.stringify(currentFilters)) : '';
        window.open(`api/export_claims_pdf.php${filters}`, '_blank');
    };

    // Export to Excel
    window.exportToExcel = function() {
        const filters = Object.keys(currentFilters).length > 0 ? 
            '?filters=' + encodeURIComponent(JSON.stringify(currentFilters)) : '';
        window.open(`api/export_claims_excel.php${filters}`, '_blank');
    };

    // Utility functions
    function formatDateTime(dateTime) {
        if (!dateTime) return 'N/A';
        const date = new Date(dateTime);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'OK'
        });
    }
});
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item i {
    position: absolute;
    left: -25px;
    top: 0;
    font-size: 16px;
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.badge {
    font-size: 0.75em;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
