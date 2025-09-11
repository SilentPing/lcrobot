<?php
/**
 * API Endpoint: Get User Details for View Modal
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
    $status = $user['status'] ?? 'active';
    $status_class = $status === 'active' ? 'success' : 'danger';
    $status_text = $status === 'active' ? 'Active' : 'Inactive';
    
    $html = '
    <div class="row">
        <!-- Profile Picture Section -->
        <div class="col-md-4 text-center">
            <div class="mb-4">';
    
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
    
    $html .= '<span class="badge badge-' . $status_class . ' badge-lg">' . $status_text . '</span>
            </div>
        </div>

        <!-- User Information Section -->
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Registration Date:</strong> ' . date('M d, Y H:i A', strtotime($user['create_datetime'])) . '</p>
                    <p><strong>Last Name:</strong> ' . htmlspecialchars($user['u_ln']) . '</p>
                    <p><strong>First Name:</strong> ' . htmlspecialchars($user['u_fn']) . '</p>
                    <p><strong>Middle Name:</strong> ' . htmlspecialchars($user['u_mn']) . '</p>
                    <p><strong>Username:</strong> ' . htmlspecialchars($user['username']) . '</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> ' . htmlspecialchars($user['email']) . '</p>
                    <p><strong>Contact Number:</strong> ' . htmlspecialchars($user['contact_no']) . '</p>
                    <p><strong>Address:</strong> ' . 
                        htmlspecialchars($user['house_no']) . ', ' . 
                        htmlspecialchars($barangayName) . ', ' . 
                        htmlspecialchars($cityMunicipalityName) . ', ' . 
                        htmlspecialchars($provinceName) . 
                    '</p>
                    <p><strong>User Type:</strong> ' . htmlspecialchars($user['usertype']) . '</p>';
    
    if (!empty($user['last_login'])) {
        $html .= '<p><strong>Last Login:</strong> ' . date('M d, Y H:i A', strtotime($user['last_login'])) . '</p>';
    } else {
        $html .= '<p><strong>Last Login:</strong> <span class="text-muted">Never</span></p>';
    }
    
    $html .= '</div>
            </div>
        </div>
    </div>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
