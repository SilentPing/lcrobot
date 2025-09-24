<?php
session_start();

// Prevent caching of dashboard pages
// header("Cache-Control: no-cache, no-store, must-revalidate");
// header("Pragma: no-cache");
// header("Expires: 0");

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
    <h1 class="h3 mb-0 text-gray-800">LCRO Admin Dashboard</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
        class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
  </div>

  <!-- Real-time Statistics Row -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-gray-800"><i class="fas fa-chart-line text-primary"></i> Real-time Statistics</h4>
        <div class="d-flex align-items-center">
          <small class="text-muted mr-3">Last updated: <span id="lastUpdate">Loading...</span></small>
          <div class="spinner-border spinner-border-sm text-primary" id="loadingSpinner" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards Row 1 -->
  <div class="row mb-4">
    <!-- Pending Requests -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-warning shadow h-100 py-2 stats-card" data-stat="pending_requests">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Requests</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending_requests">0</div>
              <div class="text-xs text-muted">Awaiting approval</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-clock fa-2x text-warning"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Approved Requests -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2 stats-card" data-stat="approved_requests">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved Requests</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="approved_requests">0</div>
              <div class="text-xs text-muted">Ready for release</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-check-circle fa-2x text-success"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Released Requests -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-info shadow h-100 py-2 stats-card" data-stat="released_requests">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Released Documents</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="released_requests">0</div>
              <div class="text-xs text-muted">Successfully delivered</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-box-arrow-up fa-2x text-info"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Users -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2 stats-card" data-stat="total_users">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Registered Users</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="total_users">0</div>
              <div class="text-xs text-muted">Active users</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-users fa-2x text-primary"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards Row 2 -->
  <div class="row mb-4">
    <!-- Today's Requests -->
    <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-warning shadow h-100 py-2 stats-card" data-stat="today_requests">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Today's Requests</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="today_requests">0</div>
              <div class="text-xs text-muted">New submissions</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-calendar-day fa-2x text-secondary"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Today's Released -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-dark shadow h-100 py-2 stats-card" data-stat="today_released">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Today's Released</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="today_released">0</div>
              <div class="text-xs text-muted">Documents delivered</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-hand-holding fa-2x text-dark"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Rejected Requests -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-danger shadow h-100 py-2 stats-card" data-stat="rejected_requests">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected Requests</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="rejected_requests">0</div>
              <div class="text-xs text-muted">Require attention</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-times-circle fa-2x text-danger"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Average Processing Time -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-light shadow h-100 py-2 stats-card" data-stat="avg_processing_days">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-light text-uppercase mb-1">Avg Processing Time</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="avg_processing_days">0</div>
              <div class="text-xs text-muted">Days to complete</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-stopwatch fa-2x text-light"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards Row 3 - Birthplace Statistics -->
  <div class="row mb-4">
    <div class="col-12">
      <h4 class="text-gray-800 mb-3"><i class="fas fa-map-marker-alt text-info"></i> User Birthplace Statistics</h4>
    </div>
  </div>
  
  <div class="row mb-4">
    <!-- Users Born in Botolan -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2 stats-card" data-stat="botolan_users">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Botolan Residents</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="botolan_users">0</div>
              <div class="text-xs text-muted">LCRO eligible</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-home fa-2x text-success"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Users Born Outside Botolan -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-info shadow h-100 py-2 stats-card" data-stat="non_botolan_users">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Non-Botolan Users</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="non_botolan_users">0</div>
              <div class="text-xs text-muted">PSA only</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-globe fa-2x text-info"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Complete Birthplace Data -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2 stats-card" data-stat="complete_birthplace_users">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Complete Profiles</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="complete_birthplace_users">0</div>
              <div class="text-xs text-muted">With birthplace data</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-check-circle fa-2x text-primary"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Incomplete Birthplace Data -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-warning shadow h-100 py-2 stats-card" data-stat="incomplete_birthplace_users">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Incomplete Profiles</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800" id="incomplete_birthplace_users">0</div>
              <div class="text-xs text-muted">Missing birthplace</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Pricing Management Section -->
  <div class="row mb-4">
    <div class="col-12">
      <h4 class="text-gray-800 mb-3"><i class="fas fa-dollar-sign text-success"></i> Document Pricing Management</h4>
    </div>
  </div>
  
  <div class="row mb-4">
    <!-- Pricing Overview Card -->
    <div class="col-xl-8 col-lg-7">
      <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary">Current Document Pricing</h6>
          <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
              <div class="dropdown-header">Pricing Actions:</div>
              <a class="dropdown-item" href="#" onclick="refreshPricingData()">
                <i class="fas fa-sync-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                Refresh Data
              </a>
              <div class="dropdown-divider"></div>
              <div class="dropdown-header">Export Options:</div>
              <a class="dropdown-item" href="#" onclick="exportPricingData('csv')">
                <i class="fas fa-file-csv fa-sm fa-fw mr-2 text-success"></i>
                Export as CSV
              </a>
              <a class="dropdown-item" href="#" onclick="exportPricingData('pdf')">
                <i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-danger"></i>
                Export as PDF
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="#" onclick="showAddPricingModal()">
                <i class="fas fa-plus fa-sm fa-fw mr-2 text-gray-400"></i>
                Add New Pricing
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered" id="pricingTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th>Document Type</th>
                  <th>Form Type</th>
                  <th>Form Number</th>
                  <th>Price</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="pricingTableBody">
                <tr>
                  <td colspan="6" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                      <span class="sr-only">Loading...</span>
                    </div>
                    <br>Loading pricing data...
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Pricing Statistics Card -->
    <div class="col-xl-4 col-lg-5">
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">Pricing Statistics</h6>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-6">
              <div class="text-center">
                <div class="h4 mb-0 font-weight-bold text-primary" id="totalPricingItems">0</div>
                <div class="text-xs text-muted">Total Items</div>
              </div>
            </div>
            <div class="col-6">
              <div class="text-center">
                <div class="h4 mb-0 font-weight-bold text-success" id="activePricingItems">0</div>
                <div class="text-xs text-muted">Active Items</div>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-6">
              <div class="text-center">
                <div class="h4 mb-0 font-weight-bold text-info" id="originalDocuments">0</div>
                <div class="text-xs text-muted">Original Docs</div>
              </div>
            </div>
            <div class="col-6">
              <div class="text-center">
                <div class="h4 mb-0 font-weight-bold text-warning" id="transcriptions">0</div>
                <div class="text-xs text-muted">Transcriptions</div>
              </div>
            </div>
          </div>
          <hr>
          <div class="text-center">
            <div class="h5 mb-0 font-weight-bold text-success" id="averagePrice">₱0.00</div>
            <div class="text-xs text-muted">Average Price</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Pricing Modal -->
  <div class="modal fade" id="editPricingModal" tabindex="-1" aria-labelledby="editPricingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editPricingModalLabel">
            <i class="fas fa-edit text-primary me-2"></i>Edit Document Pricing
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editPricingForm">
            <input type="hidden" id="editPricingId" name="id">
            
            <div class="mb-3">
              <label for="editDocumentType" class="form-label">Document Type</label>
              <input type="text" class="form-control" id="editDocumentType" name="document_type" readonly>
            </div>
            
            <div class="mb-3">
              <label for="editFormType" class="form-label">Form Type</label>
              <input type="text" class="form-control" id="editFormType" name="form_type" readonly>
            </div>
            
            <div class="mb-3">
              <label for="editFormNumber" class="form-label">Form Number</label>
              <input type="text" class="form-control" id="editFormNumber" name="form_number" readonly>
            </div>
            
            <div class="mb-3">
              <label for="editPrice" class="form-label">Price (₱)</label>
              <input type="number" class="form-control" id="editPrice" name="price" step="0.01" min="0" required>
            </div>
            
            <div class="mb-3">
              <label for="editDescription" class="form-label">Description</label>
              <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
            </div>
            
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="editIsActive" name="is_active" checked>
                <label class="form-check-label" for="editIsActive">
                  Active (Available for users)
                </label>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-primary" onclick="updatePricing()">
            <i class="fas fa-save me-2"></i>Update Pricing
          </button>
        </div>
      </div>
    </div>
  </div>

 <!-- Footer -->
 <footer class="sticky-footer bg-white">
    <div class="container my-auto">
      <div class="copyright text-center my-auto">
        <span>Copyright &copy; MCRO 2025</span>
      </div>
    </div>
  </footer>

  <?php
include('includes/script.php');
include('includes/footer.php');
?>

<script>
$(document).ready(function() {
    // Real-time Statistics System
    let statsInterval;
    let isUpdating = false;
    
    // Function to fetch and update statistics
    function updateStatistics() {
        if (isUpdating) return;
        
        isUpdating = true;
        $('#loadingSpinner').show();
        
        $.ajax({
            url: 'api/dashboard_stats.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    // Update each statistic with animation
                    updateStatCard('pending_requests', data.pending_requests);
                    updateStatCard('approved_requests', data.approved_requests);
                    updateStatCard('released_requests', data.released_requests);
                    updateStatCard('total_users', data.total_users);
                    updateStatCard('today_requests', data.today_requests);
                    updateStatCard('today_released', data.today_released);
                    updateStatCard('rejected_requests', data.rejected_requests);
                    updateStatCard('avg_processing_days', data.avg_processing_days);
                    
                    // Update birthplace statistics
                    updateStatCard('botolan_users', data.botolan_users);
                    updateStatCard('non_botolan_users', data.non_botolan_users);
                    updateStatCard('complete_birthplace_users', data.complete_birthplace_users);
                    updateStatCard('incomplete_birthplace_users', data.incomplete_birthplace_users);
                    
                    // Update last update time
                    $('#lastUpdate').text(data.system_health.last_update);
                    
                    // Add success indicator
                    showUpdateSuccess();
                } else {
                    console.error('Failed to fetch statistics:', data.error);
                    showUpdateError();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showUpdateError();
            },
            complete: function() {
                isUpdating = false;
                $('#loadingSpinner').hide();
            }
        });
    }
    
    // Function to update individual stat cards with animation
    function updateStatCard(statId, newValue) {
        const $element = $('#' + statId);
        const currentValue = parseInt($element.text()) || 0;
        
        if (currentValue !== newValue) {
            // Add highlight effect
            $element.parent().addClass('stat-updated');
            
            // Animate the number change
            animateNumber($element, currentValue, newValue, 1000);
            
            // Remove highlight after animation
            setTimeout(() => {
                $element.parent().removeClass('stat-updated');
            }, 2000);
        }
    }
    
    // Function to animate number changes
    function animateNumber($element, start, end, duration) {
        const startTime = performance.now();
        
        function updateNumber(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function for smooth animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const current = Math.round(start + (end - start) * easeOutQuart);
            
            $element.text(current);
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            }
        }
        
        requestAnimationFrame(updateNumber);
    }
    
    // Function to show update success
    function showUpdateSuccess() {
        const $lastUpdate = $('#lastUpdate');
        $lastUpdate.addClass('text-success');
        setTimeout(() => {
            $lastUpdate.removeClass('text-success');
        }, 2000);
    }
    
    // Function to show update error
    function showUpdateError() {
        const $lastUpdate = $('#lastUpdate');
        $lastUpdate.text('Update failed').addClass('text-danger');
        setTimeout(() => {
            $lastUpdate.removeClass('text-danger');
        }, 3000);
    }
    
    // Initial load
    updateStatistics();
    
    // Set up auto-refresh every 30 seconds
    statsInterval = setInterval(updateStatistics, 30000);
    
    // Manual refresh on card click
    $('.stats-card').click(function() {
        updateStatistics();
    });
    
    // Pause auto-refresh when tab is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(statsInterval);
        } else {
            updateStatistics(); // Refresh immediately when tab becomes visible
            statsInterval = setInterval(updateStatistics, 30000);
        }
    });
    
    // Clean up on page unload
    $(window).on('beforeunload', function() {
        clearInterval(statsInterval);
    });
    
    // Pricing Management System
    loadPricingData();
    
    // Load pricing data every 5 minutes
    setInterval(loadPricingData, 300000);
    
    // Initialize dropdown functionality
    initializeDropdowns();
});

// Pricing Management Functions
function loadPricingData() {
    $.ajax({
        url: 'api/get_pricing.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                populatePricingTable(response.data);
                updatePricingStatistics(response.data);
            } else {
                showPricingError('Failed to load pricing data: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            showPricingError('Error loading pricing data: ' + error);
        }
    });
}

function populatePricingTable(data) {
    const tbody = $('#pricingTableBody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.html('<tr><td colspan="6" class="text-center text-muted">No pricing data found</td></tr>');
        return;
    }
    
    data.forEach(function(item) {
        const row = `
            <tr>
                <td>${item.document_type}</td>
                <td><span class="badge ${item.form_type === 'original' ? 'bg-primary' : 'bg-success'}">${item.form_type}</span></td>
                <td><strong>Form ${item.form_number}</strong></td>
                <td><strong class="text-success">₱${parseFloat(item.price).toFixed(2)}</strong></td>
                <td>
                    <span class="badge ${item.is_active ? 'bg-success' : 'bg-secondary'}">
                        ${item.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editPricing(${item.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-${item.is_active ? 'warning' : 'success'}" 
                            onclick="togglePricingStatus(${item.id}, ${item.is_active})" 
                            title="${item.is_active ? 'Deactivate' : 'Activate'}">
                        <i class="fas fa-${item.is_active ? 'pause' : 'play'}"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function updatePricingStatistics(data) {
    const total = data.length;
    const active = data.filter(item => item.is_active).length;
    const original = data.filter(item => item.form_type === 'original').length;
    const transcription = data.filter(item => item.form_type === 'transcription').length;
    const average = data.length > 0 ? data.reduce((sum, item) => sum + parseFloat(item.price), 0) / data.length : 0;
    
    $('#totalPricingItems').text(total);
    $('#activePricingItems').text(active);
    $('#originalDocuments').text(original);
    $('#transcriptions').text(transcription);
    $('#averagePrice').text('₱' + average.toFixed(2));
}

function editPricing(id) {
    // Find the pricing item
    $.ajax({
        url: 'api/get_pricing.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const item = response.data.find(p => p.id === id);
                if (item) {
                    $('#editPricingId').val(item.id);
                    $('#editDocumentType').val(item.document_type);
                    $('#editFormType').val(item.form_type);
                    $('#editFormNumber').val(item.form_number);
                    $('#editPrice').val(item.price);
                    $('#editDescription').val(item.description);
                    $('#editIsActive').prop('checked', item.is_active);
                    
                    $('#editPricingModal').modal('show');
                }
            }
        }
    });
}

function updatePricing() {
    const formData = {
        id: $('#editPricingId').val(),
        price: $('#editPrice').val(),
        description: $('#editDescription').val(),
        is_active: $('#editIsActive').is(':checked') ? 1 : 0
    };
    
    $.ajax({
        url: 'api/update_pricing.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editPricingModal').modal('hide');
                loadPricingData();
                showSuccessMessage('Pricing updated successfully!');
            } else {
                showErrorMessage('Failed to update pricing: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            showErrorMessage('Error updating pricing: ' + error);
        }
    });
}

function togglePricingStatus(id, currentStatus) {
    const newStatus = !currentStatus;
    const action = newStatus ? 'activate' : 'deactivate';
    
    Swal.fire({
        title: 'Confirm Action',
        text: `Are you sure you want to ${action} this pricing item?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: newStatus ? '#28a745' : '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${action}!`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/update_pricing.php',
                method: 'POST',
                data: {
                    id: id,
                    is_active: newStatus ? 1 : 0
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadPricingData();
                        showSuccessMessage(`Pricing item ${action}d successfully!`);
                    } else {
                        showErrorMessage('Failed to update pricing status: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showErrorMessage('Error updating pricing status: ' + error);
                }
            });
        }
    });
}

function refreshPricingData() {
    loadPricingData();
    showSuccessMessage('Pricing data refreshed!');
}

function exportPricingData(format = 'csv') {
    if (format === 'csv') {
        window.open('api/export_pricing.php?format=csv', '_blank');
    } else if (format === 'pdf') {
        window.open('api/export_pricing.php?format=pdf', '_blank');
    } else {
        showErrorMessage('Invalid export format');
    }
}

function showAddPricingModal() {
    showErrorMessage('Add new pricing feature coming soon!');
}

function showPricingError(message) {
    $('#pricingTableBody').html(`<tr><td colspan="6" class="text-center text-danger">${message}</td></tr>`);
}

function showSuccessMessage(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
}

function showErrorMessage(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545'
    });
}

// Initialize dropdown functionality
function initializeDropdowns() {
    // Ensure dropdowns work with both Bootstrap 4 and 5
    $('.dropdown-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $dropdown = $(this).closest('.dropdown');
        const $menu = $dropdown.find('.dropdown-menu');
        
        // Close other dropdowns
        $('.dropdown-menu').not($menu).removeClass('show');
        $('.dropdown').not($dropdown).removeClass('show');
        
        // Toggle current dropdown
        $menu.toggleClass('show');
        $dropdown.toggleClass('show');
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').removeClass('show');
            $('.dropdown').removeClass('show');
        }
    });
    
    // Close dropdown when clicking on dropdown items
    $('.dropdown-item').on('click', function() {
        $('.dropdown-menu').removeClass('show');
        $('.dropdown').removeClass('show');
    });
}
</script>

<style>
/* Real-time Statistics Styles */
.stats-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.stat-updated {
    animation: pulse 0.6s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

#loadingSpinner {
    display: none;
}

#lastUpdate {
    font-weight: 500;
    transition: color 0.3s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-card .h5 {
        font-size: 1.5rem;
    }
    
    .stats-card .text-xs {
        font-size: 0.7rem;
    }
    
    .stats-card i {
        font-size: 1.5rem !important;
    }
}

/* Color enhancements for better visibility */
.border-left-light {
    border-left: 0.25rem solid #e3e6f0 !important;
}

.text-light {
    color: #5a5c69 !important;
}

/* Dropdown Styles */
.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    z-index: 1000;
    min-width: 200px;
    padding: 0.5rem 0;
    margin: 0.125rem 0 0;
    font-size: 0.875rem;
    color: #212529;
    text-align: left;
    list-style: none;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 0.25rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175);
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.25rem 1rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    text-align: inherit;
    text-decoration: none;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
    cursor: pointer;
}

.dropdown-item:hover,
.dropdown-item:focus {
    color: #16181b;
    text-decoration: none;
    background-color: #f8f9fa;
}

.dropdown-header {
    display: block;
    padding: 0.5rem 1rem;
    margin-bottom: 0;
    font-size: 0.75rem;
    color: #6c757d;
    white-space: nowrap;
    font-weight: 600;
    text-transform: uppercase;
}

.dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    overflow: hidden;
    border-top: 1px solid #e9ecef;
}

.dropdown-toggle::after {
    display: none;
}

.dropdown.no-arrow .dropdown-toggle::after {
    display: none;
}


</style>