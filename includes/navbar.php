<!-- Sidebar -->
   <ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">

<!-- Sidebar - Brand -->
<a class="sidebar-brand d-flex align-items-center justify-content-center" href="admin_dashboard.php">
  <div class="sidebar-brand-icon">
    <!-- Replace 'path_to_your_logo_image' with the actual path to your LGU logo image -->
    <img src="images/civ.png" alt="LGU Logo" style="width: 50px; height: 50px;"> <!-- Adjust width and height as needed -->
  </div>
  <div class="sidebar-brand-text mx-2">CIVIL REGISTRY <sup>BOTOLAN</sup></div>
</a>


<!-- Divider -->
<hr class="sidebar-divider my-0">

<!-- Nav Item - Dashboard -->
<li class="nav-item">
  <a class="nav-link" href="admin_dashboard.php">
    <i class="fas fa-fw fa-tachometer-alt"></i>
    <span>Dashboard</span></a>
</li>

<!-- Divider -->
<hr class="sidebar-divider">

<!-- Heading -->
<div class="sidebar-heading">
  Interface
</div>

<!-- Nav Item - Pages Collapse Menu -->


<li class="nav-item">
  <a class="nav-link" href="manage_request.php">
    <i class="bi bi-file-text-fill"></i>
    <span>Civil Request</span></a>
</li>

<li class="nav-item">
  <a class="nav-link" href="reports.php">
    <i class="fas fa-chart-bar"></i>
    <span>Reports</span></a>
</li>

<li class="nav-item">
  <a class="nav-link" href="qr_dashboard.php">
    <i class="fas fa-qrcode"></i>
    <span>QR Dashboard</span></a>
</li>



<!-- Nav Item - Utilities Collapse Menu -->


<!-- Divider -->
<hr class="sidebar-divider">

<!-- Heading -->
<div class="sidebar-heading">
  Civil Information
</div>

<!-- Nav Item - Pages Collapse Menu -->
<!-- <li class="nav-item">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true" aria-controls="collapsePages">
    <i class="bi bi-plus-circle-fill"></i>
    <span>Pages</span>
  </a>
  <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
      <h6 class="collapse-header">Civil Documents:</h6>
      <a class="collapse-item" href="approved_request.php">Approved Request</a> -->
      <!-- <a class="collapse-item" href="register.html">Register</a> -->
      <!-- <a class="collapse-item" href="rejected_request.php">Rejected Request</a>
      <div class="collapse-divider"></div>
      <h6 class="collapse-header">Other Pages:</h6> -->
      <!-- <a class="collapse-item" href="request_reject.php">Reject Request Notification</a> -->
      <!-- <a class="collapse-item" href="report.php">Reports</a> -->
   <!--  </div>
  </div>
</li>
 -->
<!-- Nav Item - Charts -->
<li class="nav-item">
  <a class="nav-link" href="approved_request.php">
    <i class="bi bi-person-fill-check"></i>
    <span>Total Approved Request</span></a>
</li>

<li class="nav-item">
  <a class="nav-link" href="released_request.php">
    <i class="bi bi-box-arrow-up"></i>
    <span>Total Released Request</span></a>
</li>

<li class="nav-item">
  <a class="nav-link" href="rejected_request.php">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>Total Rejected Request</span></a>
</li>

<li class="nav-item">
  <a class="nav-link" href="total_users.php">
    <i class="bi bi-people-fill"></i>
    <span>Total Users</span></a>
</li>

<!-- Heading -->
<div class="sidebar-heading">
  Reports
</div>


<!-- <li class="nav-item">
  <a class="nav-link" href="report.php">
    <i class="bi bi-graph-up-arrow"></i>
    <span>Request Reports</span></a>
</li> -->

<li class="nav-item">
  <a class="nav-link" href="inquiries.php">
    <i class="bi bi-file-text-fill"></i>
    <span>Inquiries</span></a>
</li>

<li class="nav-item">
  <a class="nav-link" href="timeline.php">
    <i class="bi bi-person-lines-fill"></i>
    <span>User Activity</span></a>
</li>

<!-- Nav Item - Tables -->
<li class="nav-item">
  <a class="nav-link" href="civil_record.php">
    <i class="fas fa-folder-open"></i>
    <span>Civil Record</span></a>
</li>

<!-- Divider -->
<hr class="sidebar-divider d-none d-md-block">

<!-- Sidebar Toggler (Sidebar) -->
<div class="text-center d-none d-md-inline">
  <button class="rounded-circle border-0" id="sidebarToggle"></button>
</div>

</ul>
<!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Topbar Search -->
          <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
            <div class="input-group">
              <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
              <div class="input-group-append">
                <button class="btn btn-primary" type="button">
                  <i class="fas fa-search fa-sm"></i>
                </button>
              </div>
            </div>
          </form>


          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">

            <!-- Nav Item - Search Dropdown (Visible Only XS) -->
            <li class="nav-item dropdown no-arrow d-sm-none">
              <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
              </a>
              <!-- Dropdown - Messages -->
              <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                  <div class="input-group">
                    <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                      <button class="btn btn-primary" type="button">
                        <i class="fas fa-search fa-sm"></i>
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </li>


           <!-- Nav Item - Alerts -->
            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell fa-fw"></i>
                    <!-- Counter - Alerts -->
                    <span id="alertCounter" class="badge badge-danger badge-counter"></span>
                </a>
                <!-- Dropdown - Alerts -->
                <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                    <h6 class="dropdown-header">REQUEST ALERT</h6>
                    <div id="alertList"></div>
                    <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                </div>
            </li>

          <script>
              document.addEventListener("DOMContentLoaded", function() {
                  fetchNotifications();
                  setInterval(fetchNotifications, 10000); // Fetch every 10 seconds

                  function fetchNotifications() {
                      fetch('fetch_notifications.php')
                          .then(response => response.json())
                          .then(data => {
                              let alertList = document.getElementById('alertList');
                              let alertCounter = document.getElementById('alertCounter');

                              // Clear current alerts
                              alertList.innerHTML = '';

                              // Update alert count
                              if (data.length > 0) {
                                  alertCounter.textContent = data.length;
                                  alertCounter.style.display = "inline";
                              } else {
                                  alertCounter.style.display = "none";
                              }

                              // Populate alerts
                              data.forEach(request => {
                                  let requesterName = `${request.firstname} ${request.lastname}`;
                                  let registrarName = request.registrar_name;
                                  let alertItem = `
                                      <a class="dropdown-item d-flex align-items-center" href="manage_request.php?request_id=${request.request_id}">
                                          <div class="mr-3">
                                              <div class="icon-circle bg-primary">
                                                  <i class="fas fa-file-alt text-white"></i>
                                              </div>
                                          </div>
                                          <div>
                                              <div class="small text-gray-500">${new Date(request.registration_date).toLocaleDateString()}</div>
                                              <span class="font-weight-bold">New ${request.type_request} request from ${registrarName}</span>
                                          </div>
                                      </a>
                                  `;
                                  alertList.innerHTML += alertItem;
                              });
                          })
                          .catch(error => console.error('Error fetching notifications:', error));
                  }
              });
              </script>



            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                  
               CIVREG ADMIN
                  
                </span>
                <img class="img-profile rounded-circle" src="images/lcro.jpg">
              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profile
                </a>
                <a class="dropdown-item" href="#">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                  Settings
                </a>
                <a class="dropdown-item" href="#">
                  <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                  Activity Log
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>

          </ul>

        </nav>
        <!-- End of Topbar -->


  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  
  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>

          <form action="logout.php" method="POST"> 
          
            <button type="submit" name="logout_btn" class="btn btn-primary">Logout</button>

          </form>


        </div>
      </div>
    </div>
  </div>