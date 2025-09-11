<?php
/**
 * API Endpoint: Get User Edit Form for Edit Modal
 */

session_start();
require_once __DIR__ . '/../db.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in as admin
if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SESSION['usertype'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $user_id = intval($_GET['user_id']);
    
    if (!$user_id) {
        throw new Exception('Invalid user ID');
    }
    
    // Fetch user details
    $query = "SELECT * FROM users WHERE id_user = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception('User not found');
    }
    
    $user = mysqli_fetch_assoc($result);
    
    // Get location names from codes
    function getProvinceName($provinceCode) {
        global $conn;
        $query = "SELECT provDesc FROM refprovince WHERE provCode = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $provinceCode);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ? $row['provDesc'] : $provinceCode;
    }

    function getCityMunicipalityName($cityCode) {
        global $conn;
        $query = "SELECT citymunDesc FROM refcitymun WHERE citymunCode = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $cityCode);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ? $row['citymunDesc'] : $cityCode;
    }

    function getBarangayName($brgyCode) {
        global $conn;
        $query = "SELECT brgyDesc FROM refbrgy WHERE brgyCode = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $brgyCode);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ? $row['brgyDesc'] : $brgyCode;
    }

    // Convert codes to names
    $provinceName = getProvinceName($user['province']);
    $cityMunicipalityName = getCityMunicipalityName($user['city_municipality']);
    $barangayName = getBarangayName($user['street_brgy']);
    
    // Generate HTML content
    $html = '
    <div class="row">
        <!-- Profile Picture Section -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Picture</h6>
                </div>
                <div class="card-body text-center">
                    <div id="profile-picture-container">';
    
    if (!empty($user['profile_picture'])) {
        // Make sure the path is correct
        $profile_path = $user['profile_picture'];
        if (!file_exists($profile_path)) {
            // Try with absolute path
            $profile_path = __DIR__ . '/../' . $user['profile_picture'];
        }
        
        if (file_exists($profile_path)) {
            $html .= '<img src="' . htmlspecialchars($user['profile_picture']) . '" 
                         alt="Profile Picture" class="img-fluid rounded-circle mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover;">';
        } else {
            $html .= '<div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" 
                         style="width: 150px; height: 150px;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                      </div>';
        }
    } else {
        $html .= '<div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" 
                     style="width: 150px; height: 150px;">
                    <i class="fas fa-user fa-3x text-muted"></i>
                  </div>';
    }
    
    $html .= '</div>
                    
                    <form id="profile-picture-form" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="' . $user['id_user'] . '">
                        <div class="form-group">
                            <input type="file" id="profile_picture" name="profile_picture" 
                                   accept="image/*" class="form-control-file" style="display: none;">
                            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById(\'profile_picture\').click();">
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
                <input type="hidden" name="user_id" value="' . $user['id_user'] . '">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="firstname">First Name *</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" 
                                   value="' . htmlspecialchars($user['u_fn']) . '" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="lastname">Last Name *</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" 
                                   value="' . htmlspecialchars($user['u_ln']) . '" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="middlename">Middle Name</label>
                            <input type="text" class="form-control" id="middlename" name="middlename" 
                                   value="' . htmlspecialchars($user['u_mn']) . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="' . htmlspecialchars($user['username']) . '" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="' . htmlspecialchars($user['email']) . '" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contact_no">Contact Number *</label>
                            <input type="tel" class="form-control" id="contact_no" name="contact_no" 
                                   value="' . htmlspecialchars($user['contact_no']) . '" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="house_no">House Number</label>
                            <input type="text" class="form-control" id="house_no" name="house_no" 
                                   value="' . htmlspecialchars($user['house_no']) . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="street_brgy">Street/Barangay</label>
                            <input type="text" class="form-control" id="street_brgy" name="street_brgy" 
                                   value="' . htmlspecialchars($barangayName) . '">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="city_municipality">City/Municipality</label>
                            <input type="text" class="form-control" id="city_municipality" name="city_municipality" 
                                   value="' . htmlspecialchars($cityMunicipalityName) . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="province">Province</label>
                            <input type="text" class="form-control" id="province" name="province" 
                                   value="' . htmlspecialchars($provinceName) . '">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Account Status</label>
                            <select class="form-control" id="status" name="status">';
    
    $status = $user['status'] ?? 'active';
    $html .= '<option value="active"' . ($status === 'active' ? ' selected' : '') . '>Active</option>';
    $html .= '<option value="inactive"' . ($status === 'inactive' ? ' selected' : '') . '>Inactive</option>';
    
    $html .= '</select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Registration Date</label>
                            <input type="text" class="form-control" value="' . htmlspecialchars($user['create_datetime']) . '" readonly>
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
                        <input type="hidden" name="user_id" value="' . $user['id_user'] . '">
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
    
    <script>
    $(document).ready(function() {
        // Profile picture upload
        $("#profile_picture").on("change", function() {
            const file = this.files[0];
            if (file) {
                uploadProfilePicture(file);
            }
        });
        
        // User profile update
        $("#user-edit-form").on("submit", function(e) {
            e.preventDefault();
            updateUserProfile();
        });
        
        // Change password
        $("#change-password-btn").on("click", function() {
            changeUserPassword();
        });
        
        // Status change
        $("#status").on("change", function() {
            updateUserStatus();
        });
    });
    
    function uploadProfilePicture(file) {
        const formData = new FormData();
        formData.append("user_id", ' . $user['id_user'] . ');
        formData.append("profile_picture", file);
        
        $("#upload-progress").show();
        
        $.ajax({
            url: "api/upload_profile_picture.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = evt.loaded / evt.total * 100;
                        $(".progress-bar").css("width", percentComplete + "%");
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    const img = `<img src="${response.file_path}?t=${Date.now()}" alt="Profile Picture" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">`;
                    $("#profile-picture-container").html(img);
                    
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: response.message,
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Failed to upload profile picture"
                });
            },
            complete: function() {
                $("#upload-progress").hide();
                $(".progress-bar").css("width", "0%");
            }
        });
    }
    
    function updateUserProfile() {
        const formData = $("#user-edit-form").serialize();
        
        $.ajax({
            url: "api/update_user.php",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: response.message,
                        timer: 2000
                    }).then(() => {
                        // Close modal and refresh the page
                        $("#editUserModal").modal("hide");
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                let errorMsg = "Failed to update user profile";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: errorMsg
                });
            }
        });
    }
    
    function changeUserPassword() {
        const newPassword = $("#new_password").val();
        const confirmPassword = $("#confirm_password").val();
        
        if (newPassword !== confirmPassword) {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Passwords do not match"
            });
            return;
        }
        
        const formData = $("#change-password-form").serialize();
        
        $.ajax({
            url: "api/update_user.php",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: response.message,
                        timer: 2000
                    }).then(() => {
                        $("#changePasswordModal").modal("hide");
                        $("#change-password-form")[0].reset();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Failed to change password"
                });
            }
        });
    }
    
    function updateUserStatus() {
        const status = $("#status").val();
        const user_id = ' . $user['id_user'] . ';
        
        $.ajax({
            url: "api/update_user.php",
            type: "POST",
            data: {
                user_id: user_id,
                action: "update_status",
                status: status
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: response.message,
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Failed to update user status"
                });
            }
        });
    }
    </script>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
