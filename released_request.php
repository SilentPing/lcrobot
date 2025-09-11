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

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-info"><i class="bi bi-box-arrow-up"></i> Released Civil Requests</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="releasedDataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Registration Date</th>
                            <th>Registrar Name</th>
                            <th>Type of Request</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Released Date</th>
                            <th>Released By</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require('db.php');
                        
                        $query = "SELECT * FROM released_requests ORDER BY released_date DESC";
                        $query_run = mysqli_query($conn, $query);

                        if ($query_run && mysqli_num_rows($query_run) > 0) {
                            $row_number = 1;
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                // Check if user has email in users table (for registered users)
                                $display_email = $row['email'];
                                if (empty($display_email) && !empty($row['contact_no'])) {
                                    // Try to find user by contact number (prioritize regular users over admins)
                                    $userQuery = "SELECT email, usertype FROM users WHERE contact_no = ? ORDER BY CASE WHEN usertype = 'user' THEN 1 ELSE 2 END, id_user ASC LIMIT 1";
                                    $stmt = $conn->prepare($userQuery);
                                    $stmt->bind_param("s", $row['contact_no']);
                                    $stmt->execute();
                                    $userResult = $stmt->get_result();
                                    if ($userResult->num_rows > 0) {
                                        $userRow = $userResult->fetch_assoc();
                                        $display_email = !empty($userRow['email']) ? $userRow['email'] : 'Not provided';
                                    } else {
                                        $display_email = 'Not provided';
                                    }
                                    $stmt->close();
                                } else {
                                    $display_email = !empty($display_email) ? $display_email : 'Not provided';
                                }
                                ?>
                                <tr>
                                    <td><?php echo $row_number; ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($row['registration_date'])); ?></td>
                                    <td><?php echo $row['registrar_name']; ?></td>
                                    <td><?php echo $row['type_request']; ?></td>
                                    <td><?php echo $row['contact_no']; ?></td>
                                    <td><?php echo $display_email; ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($row['released_date'])); ?></td>
                                    <td><?php echo $row['released_by']; ?></td>
                                    <td><span class="badge bg-info"><?php echo $row['status']; ?></span></td>
                                </tr>
                                <?php
                                $row_number++;
                            }
                        } else {
                            echo "<tr><td colspan='9' class='text-center'>";
                            include('includes/no_data_component.php');
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; MCRO 2023</span>
        </div>
    </div>
</footer>

<script>
$(document).ready(function() {
    // Initialize DataTable for released requests
    if ($.fn.DataTable) {
        $('#releasedDataTable').DataTable({
            "order": [[ 6, "desc" ]], // Sort by released date descending
            "pageLength": 25,
            "responsive": true,
            "language": {
                "search": "Search released requests:",
                "lengthMenu": "Show _MENU_ entries per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ released requests",
                "infoEmpty": "No released requests found",
                "infoFiltered": "(filtered from _MAX_ total entries)"
            }
        });
    }
});
</script>

<?php
include('includes/script.php');
include('includes/footer.php');
?>
