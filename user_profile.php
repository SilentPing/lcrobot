<?php
session_start();
require('db.php');

if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit;
}

// Get current user data
$user_id = $_SESSION['id_user'];
$query = "SELECT * FROM users WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Civil Registry Portal</title>
    <link rel="icon" href="images/civ.png" type="images/png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .profile-container {
            padding: 2rem 0;
        }
        
        .profile-card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
            overflow: hidden;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .profile-picture {
            width: 120px;
            height: 120px;
            border: 5px solid white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .profile-info {
            padding: 2rem;
        }
        
        .info-item {
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            color: #333;
            font-size: 1.1rem;
        }
        
        .edit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: white;
        }
        
        .status-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .back-btn {
            position: fixed;
            top: 2rem;
            left: 2rem;
            background: rgba(255,255,255,0.9);
            border: none;
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .back-btn:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="user_dashboard.php" class="back-btn">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>

    <div class="container profile-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card profile-card">
                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="profile-picture-container mb-3">
                            <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                     alt="Profile Picture" class="rounded-circle profile-picture">
                            <?php else: ?>
                                <div class="rounded-circle profile-picture bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h2 class="mb-2"><?php echo htmlspecialchars($user['u_fn'] . ' ' . $user['u_ln']); ?></h2>
                        <p class="mb-3">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <span class="status-badge">
                            <i class="fas fa-check-circle me-1"></i>Active User
                        </span>
                    </div>
                    
                    <!-- Profile Information -->
                    <div class="profile-info">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-envelope me-2 text-primary"></i>Email Address
                                    </div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-phone me-2 text-primary"></i>Contact Number
                                    </div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['contact_no']); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i>Registration Date
                                    </div>
                                    <div class="info-value"><?php echo date('M d, Y', strtotime($user['create_datetime'])); ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-user me-2 text-primary"></i>Full Name
                                    </div>
                                    <div class="info-value">
                                        <?php echo htmlspecialchars($user['u_fn'] . ' ' . $user['u_mn'] . ' ' . $user['u_ln']); ?>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-clock me-2 text-primary"></i>Last Login
                                    </div>
                                    <div class="info-value">
                                        <?php echo !empty($user['last_login']) ? date('M d, Y H:i A', strtotime($user['last_login'])) : 'Never'; ?>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>Address
                                    </div>
                                    <div class="info-value">
                                        <?php 
                                        echo htmlspecialchars($user['house_no']) . ', ' . 
                                             htmlspecialchars($barangayName) . ', ' . 
                                             htmlspecialchars($cityMunicipalityName) . ', ' . 
                                             htmlspecialchars($provinceName); 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="button" class="btn edit-btn" onclick="editMyProfile()">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit My Profile</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="editProfileContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
    function editMyProfile() {
        $('#editProfileContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...</div>');
        $('#editProfileModal').modal('show');
        
        $.ajax({
            url: 'api/get_my_edit_form.php',
            type: 'GET',
            data: { user_id: <?php echo $_SESSION['id_user']; ?> },
            success: function(response) {
                if (response.success) {
                    $('#editProfileContent').html(response.html);
                } else {
                    $('#editProfileContent').html('<div class="alert alert-danger">Error: ' + (response.message || 'Unknown error') + '</div>');
                }
            },
            error: function(xhr, status, error) {
                let errorMsg = 'Failed to load edit form';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $('#editProfileContent').html('<div class="alert alert-danger">Error: ' + errorMsg + '</div>');
            }
        });
    }
    </script>
</body>
</html>