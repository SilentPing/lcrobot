<?php
session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in
if (!isset($_SESSION['name'])) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Unauthorized access.</div>';
    exit;
}

$recordId = $_GET['id'] ?? '';
$recordType = $_GET['type'] ?? '';

if (empty($recordId) || empty($recordType)) {
    echo '<div class="alert alert-danger">Invalid record ID or type.</div>';
    exit;
}

try {
    switch($recordType) {
        case 'birth':
            displayBirthRecord($recordId);
            break;
        case 'marriage':
            displayMarriageRecord($recordId);
            break;
        case 'death':
            displayDeathRecord($recordId);
            break;
        default:
            echo '<div class="alert alert-danger">Invalid record type.</div>';
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error loading record: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

function displayBirthRecord($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM birthceno_tbl WHERE id_birth_ceno = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h6><strong>Personal Information</strong></h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Last Name:</strong></td><td>' . htmlspecialchars($row['lastname']) . '</td></tr>';
        echo '<tr><td><strong>First Name:</strong></td><td>' . htmlspecialchars($row['firstname']) . '</td></tr>';
        echo '<tr><td><strong>Middle Name:</strong></td><td>' . htmlspecialchars($row['middlename'] ?: 'N/A') . '</td></tr>';
        echo '<tr><td><strong>Date of Birth:</strong></td><td>' . date('M d, Y', strtotime($row['dob'])) . '</td></tr>';
        echo '<tr><td><strong>Sex:</strong></td><td>' . htmlspecialchars($row['sex']) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<h6><strong>Place of Birth</strong></h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Country:</strong></td><td>' . htmlspecialchars($row['pob_country']) . '</td></tr>';
        echo '<tr><td><strong>Province:</strong></td><td>' . htmlspecialchars($row['pob_province']) . '</td></tr>';
        echo '<tr><td><strong>City/Municipality:</strong></td><td>' . htmlspecialchars($row['pob_municipality']) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="row mt-3">';
        echo '<div class="col-md-6">';
        echo '<h6><strong>Father\'s Information</strong></h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Last Name:</strong></td><td>' . htmlspecialchars($row['fath_ln']) . '</td></tr>';
        echo '<tr><td><strong>First Name:</strong></td><td>' . htmlspecialchars($row['fath_fn']) . '</td></tr>';
        echo '<tr><td><strong>Middle Name:</strong></td><td>' . htmlspecialchars($row['fath_mn']) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<h6><strong>Mother\'s Information</strong></h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Maiden Last Name:</strong></td><td>' . htmlspecialchars($row['moth_maiden_ln']) . '</td></tr>';
        echo '<tr><td><strong>Maiden First Name:</strong></td><td>' . htmlspecialchars($row['moth_maiden_fn']) . '</td></tr>';
        echo '<tr><td><strong>Maiden Middle Name:</strong></td><td>' . htmlspecialchars($row['moth_maiden_mn']) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="row mt-3">';
        echo '<div class="col-md-12">';
        echo '<h6><strong>Request Information</strong></h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Relationship:</strong></td><td>' . htmlspecialchars($row['relationship']) . '</td></tr>';
        echo '<tr><td><strong>Purpose:</strong></td><td>' . htmlspecialchars($row['purpose_of_request']) . '</td></tr>';
        echo '<tr><td><strong>Type:</strong></td><td>' . htmlspecialchars($row['type_request']) . '</td></tr>';
        echo '<tr><td><strong>Status:</strong></td><td><span class="badge badge-' . getStatusClass($row['status_request']) . '">' . htmlspecialchars($row['status_request']) . '</span></td></tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-warning">Record not found.</div>';
    }
    
    $stmt->close();
}

function displayMarriageRecord($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM marriage_tbl WHERE id_marriage = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h6><strong>Husband\'s Information</strong></h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Last Name:</strong></td><td>' . htmlspecialchars($row['husband_ln']) . '</td></tr>';
        echo '<tr><td><strong>First Name:</strong></td><td>' . htmlspecialchars($row['husband_fn']) . '</td></tr>';
        echo '<tr><td><strong>Middle Name:</strong></td><td>' . htmlspecialchars($row['husband_mn'] ?: 'N/A') . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<h6><strong>Wife\'s Information</strong></h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Maiden Last Name:</strong></td><td>' . htmlspecialchars($row['maiden_wife_ln']) . '</td></tr>';
        echo '<tr><td><strong>Maiden First Name:</strong></td><td>' . htmlspecialchars($row['maiden_wife_fn']) . '</td></tr>';
        echo '<tr><td><strong>Maiden Middle Name:</strong></td><td>' . htmlspecialchars($row['maiden_wife_mn'] ?: 'N/A') . '</td></tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="row mt-3">';
        echo '<div class="col-md-6">';
        echo '<h6><strong>Marriage Details</strong></h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Marriage Date:</strong></td><td>' . date('M d, Y', strtotime($row['marriage_date'])) . '</td></tr>';
        echo '<tr><td><strong>Place of Marriage:</strong></td><td>' . htmlspecialchars($row['place_of_marriage']) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<h6><strong>Request Information</strong></h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Purpose:</strong></td><td>' . htmlspecialchars($row['purpose_of_request']) . '</td></tr>';
        echo '<tr><td><strong>Type:</strong></td><td>' . htmlspecialchars($row['type_request']) . '</td></tr>';
        echo '<tr><td><strong>Status:</strong></td><td><span class="badge badge-' . getStatusClass($row['status_request']) . '">' . htmlspecialchars($row['status_request']) . '</span></td></tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-warning">Record not found.</div>';
    }
    
    $stmt->close();
}

function displayDeathRecord($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM death_tbl WHERE id_death = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h6><strong>Deceased Information</strong></h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Last Name:</strong></td><td>' . htmlspecialchars($row['deceased_ln']) . '</td></tr>';
        echo '<tr><td><strong>First Name:</strong></td><td>' . htmlspecialchars($row['deceased_fn']) . '</td></tr>';
        echo '<tr><td><strong>Middle Name:</strong></td><td>' . htmlspecialchars($row['deceased_mn'] ?: 'N/A') . '</td></tr>';
        echo '<tr><td><strong>Date of Birth:</strong></td><td>' . date('M d, Y', strtotime($row['dob'])) . '</td></tr>';
        echo '<tr><td><strong>Date of Death:</strong></td><td>' . date('M d, Y', strtotime($row['dod'])) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<h6><strong>Death Details</strong></h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Place of Death:</strong></td><td>' . htmlspecialchars($row['place_of_death']) . '</td></tr>';
        echo '<tr><td><strong>Purpose:</strong></td><td>' . htmlspecialchars($row['purpose_of_request']) . '</td></tr>';
        echo '<tr><td><strong>Type:</strong></td><td>' . htmlspecialchars($row['type_request']) . '</td></tr>';
        echo '<tr><td><strong>Status:</strong></td><td><span class="badge badge-' . getStatusClass($row['status_request']) . '">' . htmlspecialchars($row['status_request']) . '</span></td></tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-warning">Record not found.</div>';
    }
    
    $stmt->close();
}

function getStatusClass($status) {
    switch($status) {
        case 'APPROVED':
            return 'success';
        case 'PENDING':
            return 'warning';
        case 'REJECTED':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>
