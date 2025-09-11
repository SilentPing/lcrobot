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
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-user-edit"></i> Edit User Profile
            </h6>
            <a href="total_users.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Profile Picture Section -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Profile Picture</h6>
                        </div>
                        <div class="card-body text-center">
                            <div id="profile-picture-container">
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
                            </div>
                            
                            <form id="profile-picture-form" enctype="multipart/form-data">
                                <input type="hidden" name="user_id" value="<?php echo $user['id_user']; ?>">
                                <div class="form-group">
                                    <input type="file" id="profile_picture" name="profile_picture" 
                                           accept="image/*" class="form-control-file" style="display: none;">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('profile_picture').click();">
                                        <i class="fas fa-upload"></i> Upload Photo
                                    </button>
                                </div>
                            </form>
                            
                            <div id="upload-progress" class="mt-2" style="display: none;">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted">Uploading...</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Information Section -->
                <div class="col-md-8">
                    <form id="user-edit-form">
                        <input type="hidden" name="user_id" value="<?php echo $user['id_user']; ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="firstname">First Name *</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" 
                                           value="<?php echo htmlspecialchars($user['u_fn']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lastname">Last Name *</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" 
                                           value="<?php echo htmlspecialchars($user['u_ln']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="middlename">Middle Name</label>
                                    <input type="text" class="form-control" id="middlename" name="middlename" 
                                           value="<?php echo htmlspecialchars($user['u_mn']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_no">Contact Number *</label>
                                    <input type="tel" class="form-control" id="contact_no" name="contact_no" 
                                           value="<?php echo htmlspecialchars($user['contact_no']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="house_no">House Number</label>
                                    <input type="text" class="form-control" id="house_no" name="house_no" 
                                           value="<?php echo htmlspecialchars($user['house_no']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="street_brgy">Street/Barangay</label>
                                    <input type="text" class="form-control" id="street_brgy" name="street_brgy" 
                                           value="<?php echo htmlspecialchars($user['street_brgy']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city_municipality">City/Municipality</label>
                                    <input type="text" class="form-control" id="city_municipality" name="city_municipality" 
                                           value="<?php echo htmlspecialchars($user['city_municipality']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="province">Province</label>
                                    <input type="text" class="form-control" id="province" name="province" 
                                           value="<?php echo htmlspecialchars($user['province']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Account Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Registration Date</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['create_datetime']); ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                            <button type="button" class="btn btn-warning ml-2" data-toggle="modal" data-target="#changePasswordModal">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="change-password-form">
                    <input type="hidden" name="user_id" value="<?php echo $user['id_user']; ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="change-password-btn">Change Password</button>
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
$(document).ready(function() {
    // Profile picture upload
    $('#profile_picture').on('change', function() {
        const file = this.files[0];
        if (file) {
            uploadProfilePicture(file);
        }
    });
    
    // User profile update
    $('#user-edit-form').on('submit', function(e) {
        e.preventDefault();
        updateUserProfile();
    });
    
    // Change password
    $('#change-password-btn').on('click', function() {
        changeUserPassword();
    });
    
    // Status change
    $('#status').on('change', function() {
        updateUserStatus();
    });
});

function uploadProfilePicture(file) {
    const formData = new FormData();
    formData.append('user_id', <?php echo $user['id_user']; ?>);
    formData.append('profile_picture', file);
    
    $('#upload-progress').show();
    
    $.ajax({
        url: 'api/upload_profile_picture.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    const percentComplete = evt.loaded / evt.total * 100;
                    $('.progress-bar').css('width', percentComplete + '%');
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            if (response.success) {
                // Update profile picture display
                const img = `<img src="${response.file_path}?t=${Date.now()}" alt="Profile Picture" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">`;
                $('#profile-picture-container').html(img);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000
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
                text: 'Failed to upload profile picture'
            });
        },
        complete: function() {
            $('#upload-progress').hide();
            $('.progress-bar').css('width', '0%');
        }
    });
}

function updateUserProfile() {
    const formData = $('#user-edit-form').serialize();
    
    $.ajax({
        url: 'api/update_user.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000
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
                text: 'Failed to update user profile'
            });
        }
    });
}

function changeUserPassword() {
    const newPassword = $('#new_password').val();
    const confirmPassword = $('#confirm_password').val();
    
    if (newPassword !== confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Passwords do not match'
        });
        return;
    }
    
    const formData = $('#change-password-form').serialize();
    
    $.ajax({
        url: 'api/update_user.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000
                }).then(() => {
                    $('#changePasswordModal').modal('hide');
                    $('#change-password-form')[0].reset();
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
                text: 'Failed to change password'
            });
        }
    });
}

function updateUserStatus() {
    const status = $('#status').val();
    const user_id = <?php echo $user['id_user']; ?>;
    
    $.ajax({
        url: 'api/update_user.php',
        type: 'POST',
        data: {
            user_id: user_id,
            action: 'update_status',
            status: status
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000
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
</script>

<?php include('includes/footer.php'); ?>
