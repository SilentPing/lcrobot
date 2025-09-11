<?php
// approved_requests.php
include('includes/header.php'); 
include('includes/navbar.php');
?>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Begin Page Content -->
        <div class="container-fluid">

          <!-- Page Heading -->
          <h1 class="h3 mb-4 text-gray-800">Request Report</h1>

          <!-- Tables Section -->
          <div class="row">

             <!-- Total Approve Table -->
            <div class="col-lg-6 mb-4">
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Total Approvals</h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-bordered" id="totalApproveTable" width="100%" cellspacing="0">
                      <thead>
                        <tr>
                          <th>Date</th>
                          <th>Total Approved Requests</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        require_once __DIR__ . '/db.php';

                        // Query to fetch total approved requests grouped by date
                        $query = "SELECT registration_date, COUNT(*) as total_approved FROM approved_requests GROUP BY registration_date";
                        $query_run = mysqli_query($conn, $query);

                        if ($query_run) {
                          while ($row = mysqli_fetch_assoc($query_run)) {
                            ?>
                            <tr>
                              <td><?php echo $row['registration_date']; ?></td>
                              <td><?php echo $row['total_approved']; ?></td>
                            </tr>
                            <?php
                          }
                        } else {
                          echo "No Approved Requests Found";
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Reject Table -->
            <div class="col-lg-6 mb-4">
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-danger">Total Rejects</h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-bordered" id="totalRejectTable" width="100%" cellspacing="0">
                     <thead>
                        <tr>
                          <th>Date</th>
                          <th>Total Rejected Requests</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        require_once __DIR__ . '/db.php';

                        // Query to fetch total approved requests grouped by date
                        $query = "SELECT registration_date, COUNT(*) as total_reject FROM rejected_requests GROUP BY registration_date";
                        $query_run = mysqli_query($conn, $query);

                        if ($query_run) {
                          while ($row = mysqli_fetch_assoc($query_run)) {
                            ?>
                            <tr>
                              <td><?php echo $row['registration_date']; ?></td>
                              <td><?php echo $row['total_reject']; ?></td>
                            </tr>
                            <?php
                          }
                        } else {
                          echo "No Approved Requests Found";
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Chart Section -->
          <div class="row">
            <div class="col-lg-8 mb-4">
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-success">Chart</h6>
                </div>
               <div class="card-body">
                <div class="chart-container" style="position: relative; height:100vh; width:50vw">
                  <canvas id="polarChart"></canvas>
                </div>
              </div>
            </div>
          </div>

        </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->
    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Custom scripts for this page -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"></script>

<?php
// Fetching data from the database for approved and rejected requests
require_once __DIR__ . '/db.php';

// Fetching approved requests data
$queryApproved = "SELECT registration_date, COUNT(*) as total_approved FROM approved_requests GROUP BY registration_date";
$queryApprovedRun = mysqli_query($conn, $queryApproved);

$polarLabels = []; // Store labels for polar chart
$polarApprovedData = []; // Store approved requests data for the chart

if ($queryApprovedRun) {
  while ($row = mysqli_fetch_assoc($queryApprovedRun)) {
    // Format the date to include day and time
    $formattedDate = date("D, Y-m-d H:i:s", strtotime($row['registration_date']));
    $polarLabels[] = $formattedDate;
    $polarApprovedData[] = $row['total_approved'];
  }
} else {
  echo "No Approved Requests Found";
}

// Fetching rejected requests data
$queryRejected = "SELECT registration_date, COUNT(*) as total_reject FROM rejected_requests GROUP BY registration_date";
$queryRejectedRun = mysqli_query($conn, $queryRejected);

$polarRejectedData = []; // Store rejected requests data for the chart

if ($queryRejectedRun) {
  while ($row = mysqli_fetch_assoc($queryRejectedRun)) {
    $polarRejectedData[] = $row['total_reject'];
  }
} else {
  echo "No Rejected Requests Found";
}
?>

  <script>
<!-- Script for Chart.js -->
// Fetching data from the PHP variables
const polarLabels = <?php echo json_encode($polarLabels); ?>;
const polarApprovedData = <?php echo json_encode($polarApprovedData); ?>;
const polarRejectedData = <?php echo json_encode($polarRejectedData); ?>;

// Define the dataset with the specified colors
const data = {
  labels: polarLabels,
  datasets: [
    {
      label: 'Approved Requests',
      data: polarApprovedData,
      backgroundColor: [
        'rgb(255, 99, 132)',
        'rgb(75, 192, 192)',
        'rgb(255, 205, 86)',
        'rgb(201, 203, 207)',
        'rgb(54, 162, 235)'
      ].slice(0, polarLabels.length),
      borderColor: 'rgba(30, 144, 255, 1)',
      borderWidth: 1
    },
    {
      label: 'Rejected Requests',
      data: polarRejectedData,
      backgroundColor: [
        'rgb(255, 99, 132)',
        'rgb(75, 192, 192)',
        'rgb(255, 205, 86)',
        'rgb(201, 203, 207)',
        'rgb(54, 162, 235)'
      ].slice(0, polarLabels.length),
      borderColor: 'rgba(255, 99, 71, 1)',
      borderWidth: 1
    }
  ]
};

// Create a polar chart using the fetched data and specified colors
const polarCtx = document.getElementById('polarChart');
new Chart(polarCtx, {
  type: 'polarArea',
  data: data,
  options: {
    scales: {
      y: {
        beginAtZero: true
      }
    },
    plugins: {
      legend: {
        position: 'top'
      }
    }
  }
});
</script>


  <script>
    // Initialize DataTable for both tables
    $(document).ready(function () {
      $('#totalApproveTable').DataTable();
      $('#totalRejectTable').DataTable();
    });
  </script>

</body>

<?php
include('includes/script.php');
include('includes/footer.php');
?>