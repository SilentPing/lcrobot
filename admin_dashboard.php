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
    <h1 class="h3 mb-0 text-gray-800">Botolan Civil Registry Online Portal Admin Dashboard</h1>
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
});
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


</style>