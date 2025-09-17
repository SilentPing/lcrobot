<?php
session_start();
require_once __DIR__ . '/../db.php';

// Check if user is LCRO Staff
if (!isset($_SESSION['usertype']) || ($_SESSION['usertype'] != 'admin' && $_SESSION['usertype'] != 'staff')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Only LCRO staff can add records.']);
    exit;
}

$recordType = $_GET['type'] ?? '';

switch($recordType) {
    case 'birth':
        include 'forms/birth_record_form.php';
        break;
    case 'marriage':
        include 'forms/marriage_record_form.php';
        break;
    case 'death':
        include 'forms/death_record_form.php';
        break;
    default:
        echo '<div class="alert alert-danger">Invalid record type selected.</div>';
}
?>
