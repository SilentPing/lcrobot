<?php
session_start();
require('../db.php');

if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

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

// Get user data
$user_id = $_SESSION['id_user'];
$query = "SELECT * FROM users WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($user) {
    // Convert codes to names
    $provinceName = getProvinceName($user['province']);
    $cityMunicipalityName = getCityMunicipalityName($user['city_municipality']);
    $barangayName = getBarangayName($user['street_brgy']);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'province_name' => $provinceName,
            'city_municipality_name' => $cityMunicipalityName,
            'barangay_name' => $barangayName,
            'house_no' => $user['house_no']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
?>
