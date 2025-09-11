<?php
include('includes/header.php'); 
include('includes/navbar.php');
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-people-fill"></i> Registered Users</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Profile</th>
                            <th>Registration Date</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require('db.php');

                        // Check if the connection is successful
                        if (!$conn) {
                            die("Connection failed: " . mysqli_connect_error());
                        }

                        $query = "SELECT * FROM users ORDER BY create_datetime DESC"; // Sort by registration date
                        $query_run = mysqli_query($conn, $query);

                        // Check if the query was executed successfully
                        if (!$query_run) {
                            die("Query failed: " . mysqli_error($conn));
                        }

                        $row_number = 1;

                        if (mysqli_num_rows($query_run) > 0) {
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                ?>
                                <tr>
                                    <td><?php echo $row_number; ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $profile_path = $row['profile_picture'];
                                        if (!empty($profile_path) && !file_exists($profile_path)) {
                                            $profile_path = __DIR__ . '/' . $row['profile_picture'];
                                        }
                                        ?>
                                        <?php if (!empty($row['profile_picture']) && file_exists($profile_path)): ?>
                                            <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" 
                                                 alt="Profile" class="rounded-circle" 
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['create_datetime'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['u_ln']); ?></td>
                                    <td><?php echo htmlspecialchars($row['u_fn']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                    <td>
                                        <?php 
                                        $status = $row['status'] ?? 'active';
                                        $status_class = $status === 'active' ? 'success' : 'danger';
                                        $status_text = $status === 'active' ? 'Active' : 'Inactive';
                                        ?>
                                        <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-info btn-sm" 
                                                    onclick="viewUser(<?php echo $row['id_user']; ?>)" 
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm" 
                                                    onclick="editUser(<?php echo $row['id_user']; ?>)" 
                                                    title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-<?php echo $status === 'active' ? 'danger' : 'success'; ?> btn-sm" 
                                                    onclick="toggleUserStatus(<?php echo $row['id_user']; ?>, '<?php echo $status; ?>')" 
                                                    title="<?php echo $status === 'active' ? 'Deactivate' : 'Activate'; ?> User">
                                                <i class="fas fa-<?php echo $status === 'active' ? 'ban' : 'check'; ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                $row_number++;
                            }
                        } else {
                            include('includes/no_data_component.php');
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewUserContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User Profile</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="editUserContent">
                <!-- Content will be loaded here -->
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

<?php include('includes/script.php'); ?>

<script>
function viewUser(userId) {
    $('#viewUserContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...</div>');
    $('#viewUserModal').modal('show');
    
    $.ajax({
        url: 'api/get_user_details.php',
        type: 'GET',
        data: { user_id: userId },
        success: function(response) {
            if (response.success) {
                $('#viewUserContent').html(response.html);
            } else {
                $('#viewUserContent').html('<div class="alert alert-danger">Error: ' + (response.message || 'Unknown error') + '</div>');
            }
        },
        error: function(xhr, status, error) {
            let errorMsg = 'Failed to load user details';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#viewUserContent').html('<div class="alert alert-danger">Error: ' + errorMsg + '</div>');
        }
    });
}

function editUser(userId) {
    $('#editUserContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...</div>');
    $('#editUserModal').modal('show');
    
    $.ajax({
        url: 'api/get_user_edit_form.php',
        type: 'GET',
        data: { user_id: userId },
        success: function(response) {
            if (response.success) {
                $('#editUserContent').html(response.html);
            } else {
                $('#editUserContent').html('<div class="alert alert-danger">Error: ' + (response.message || 'Unknown error') + '</div>');
            }
        },
        error: function(xhr, status, error) {
            let errorMsg = 'Failed to load edit form';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#editUserContent').html('<div class="alert alert-danger">Error: ' + errorMsg + '</div>');
        }
    });
}

function toggleUserStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? 'activate' : 'deactivate';
    
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${action} this user account?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: newStatus === 'active' ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${action} it!`
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/update_user.php',
                type: 'POST',
                data: {
                    user_id: userId,
                    action: 'update_status',
                    status: newStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update user status'
                    });
                }
            });
        }
    });
}
</script>

<?php include('includes/footer.php'); ?>
