<?php
session_start();
require('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['usertype'] !== 'admin') {
    header('Location: user_dashboard.php');
    exit;
}

include('includes/header.php');
include('includes/navbar.php');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch user details from the database
    $query = "SELECT * FROM users WHERE id_user = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
        } else {
            echo "<div class='alert alert-danger'>No user found with the provided ID.</div>";
            exit;
        }
    } else {
        echo "<div class='alert alert-danger'>Failed to prepare the statement.</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-danger'>No user ID provided.</div>";
    exit;
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">User Details</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Profile Picture Section -->
                <div class="col-md-4 text-center">
                    <div class="mb-4">
                        <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                 alt="Profile Picture" class="img-fluid rounded-circle mb-3" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" 
                                 style="width: 150px; height: 150px;">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <?php 
                        $status = $user['status'] ?? 'active';
                        $status_class = $status === 'active' ? 'success' : 'danger';
                        $status_text = $status === 'active' ? 'Active' : 'Inactive';
                        ?>
                        <span class="badge badge-<?php echo $status_class; ?> badge-lg"><?php echo $status_text; ?></span>
                    </div>
                </div>

                <!-- User Information Section -->
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Registration Date:</strong> <?php echo date('M d, Y H:i A', strtotime($user['create_datetime'])); ?></p>
                            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['u_ln']); ?></p>
                            <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['u_fn']); ?></p>
                            <p><strong>Middle Name:</strong> <?php echo htmlspecialchars($user['u_mn']); ?></p>
                            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($user['contact_no']); ?></p>
                            <p><strong>Address:</strong> <?php 
                                echo htmlspecialchars($user['house_no']) . ', ' . 
                                     htmlspecialchars($user['street_brgy']) . ', ' . 
                                     htmlspecialchars($user['city_municipality']) . ', ' . 
                                     htmlspecialchars($user['province']); 
                            ?></p>
                            <p><strong>User Type:</strong> <?php echo htmlspecialchars($user['usertype']); ?></p>
                            <?php if (!empty($user['last_login'])): ?>
                                <p><strong>Last Login:</strong> <?php echo date('M d, Y H:i A', strtotime($user['last_login'])); ?></p>
                            <?php else: ?>
                                <p><strong>Last Login:</strong> <span class="text-muted">Never</span></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="total_users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users List
                </a>
                <a href="edit_user.php?id=<?php echo $user['id_user']; ?>" class="btn btn-warning ml-2">
                    <i class="fas fa-edit"></i> Edit User
                </a>
                <button type="button" class="btn btn-<?php echo $status === 'active' ? 'danger' : 'success'; ?> ml-2" 
                        onclick="toggleUserStatus(<?php echo $user['id_user']; ?>, '<?php echo $status; ?>')">
                    <i class="fas fa-<?php echo $status === 'active' ? 'ban' : 'check'; ?>"></i> 
                    <?php echo $status === 'active' ? 'Deactivate' : 'Activate'; ?> User
                </button>
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
