<?php
session_start();
require_once __DIR__ . '/../db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is LCRO Staff
if (!isset($_SESSION['usertype']) || ($_SESSION['usertype'] != 'admin' && $_SESSION['usertype'] != 'staff')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Only LCRO staff can add records.']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

try {
    $recordType = $_POST['recordType'] ?? '';
    $recordSource = $_POST['recordSource'] ?? '';
    
    if (empty($recordType) || empty($recordSource)) {
        throw new Exception('Record type and source are required.');
    }
    
    // Determine status based on record source
    $status = ($recordSource === 'old_record') ? 'APPROVED' : 'PENDING';
    
    switch($recordType) {
        case 'birth':
            $result = saveBirthRecord($_POST, $status);
            break;
        case 'marriage':
            $result = saveMarriageRecord($_POST, $status);
            break;
        case 'death':
            $result = saveDeathRecord($_POST, $status);
            break;
        default:
            throw new Exception('Invalid record type.');
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function saveBirthRecord($data, $status) {
    global $conn;
    
    // Validate required fields
    $required_fields = ['lastname', 'firstname', 'pob_country', 'pob_province', 'pob_municipality', 'dob', 'sex', 'fath_ln', 'fath_fn', 'fath_mn', 'moth_maiden_ln', 'moth_maiden_fn', 'moth_maiden_mn', 'relationship', 'purpose_of_request', 'registration_date'];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field {$field} is required.");
        }
    }
    
    // Check for duplicates
    $duplicate_check = checkBirthDuplicate($data['lastname'], $data['firstname'], $data['dob']);
    if ($duplicate_check['found']) {
        throw new Exception($duplicate_check['message']);
    }
    
    // Prepare data
    $lastname = trim($data['lastname']);
    $firstname = trim($data['firstname']);
    $middlename = !empty($data['middlename']) ? trim($data['middlename']) : '';
    $pob_country = trim($data['pob_country']);
    $pob_province = trim($data['pob_province']);
    $pob_municipality = trim($data['pob_municipality']);
    $dob = $data['dob'];
    $sex = $data['sex'];
    $fath_ln = trim($data['fath_ln']);
    $fath_fn = trim($data['fath_fn']);
    $fath_mn = trim($data['fath_mn']);
    $moth_maiden_ln = trim($data['moth_maiden_ln']);
    $moth_maiden_fn = trim($data['moth_maiden_fn']);
    $moth_maiden_mn = trim($data['moth_maiden_mn']);
    $relationship = $data['relationship'];
    $purpose_of_request = $data['purpose_of_request'];
    $registration_date = $data['registration_date'];
    $type_request = 'Birth Certificate';
    
    // Encrypt PII fields
    $lastname_data = encryptAndTokenize($lastname);
    $firstname_data = encryptAndTokenize($firstname);
    $middlename_data = encryptAndTokenize($middlename);
    $dob_data = encryptAndTokenize($dob);
    $sex_data = encryptAndTokenize($sex);
    
    // Insert into birthceno_tbl
    $stmt = $conn->prepare("INSERT INTO birthceno_tbl (id_user, lastname, lastname_enc, lastname_tok, firstname, firstname_enc, firstname_tok, middlename, middlename_enc, middlename_tok, pob_country, pob_province, pob_municipality, dob, dob_enc, dob_tok, sex, sex_enc, sex_tok, fath_ln, fath_fn, fath_mn, moth_maiden_ln, moth_maiden_fn, moth_maiden_mn, relationship, purpose_of_request, type_request, status_request) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $id_user = $_SESSION['id_user']; // LCRO staff user ID
    $stmt->bind_param("issssssssssssssssssssssssssss", 
        $id_user, 
        $lastname, $lastname_data['encrypted'], $lastname_data['token'],
        $firstname, $firstname_data['encrypted'], $firstname_data['token'],
        $middlename, $middlename_data['encrypted'], $middlename_data['token'],
        $pob_country, $pob_province, $pob_municipality, 
        $dob, $dob_data['encrypted'], $dob_data['token'],
        $sex, $sex_data['encrypted'], $sex_data['token'],
        $fath_ln, $fath_fn, $fath_mn, $moth_maiden_ln, $moth_maiden_fn, $moth_maiden_mn, 
        $relationship, $purpose_of_request, $type_request, $status);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save birth record: ' . $stmt->error);
    }
    
    $id_birth_ceno = $stmt->insert_id;
    $stmt->close();
    
    // Insert into civ_record
    $registrar_name = "$firstname $middlename $lastname";
    $civRecordSql = "INSERT INTO civ_record (id_birth_ceno, registration_date, registrar_name, type_request) VALUES ('$id_birth_ceno', '$registration_date', '$registrar_name', '$type_request')";
    
    if (!$conn->query($civRecordSql)) {
        throw new Exception('Failed to save civil record: ' . $conn->error);
    }
    
    return ['success' => true, 'message' => 'Birth record saved successfully!'];
}

function saveMarriageRecord($data, $status) {
    global $conn;
    
    // Validate required fields
    $required_fields = ['husband_ln', 'husband_fn', 'maiden_wife_ln', 'maiden_wife_fn', 'marriage_date', 'place_of_marriage', 'purpose_of_request', 'registration_date'];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field {$field} is required.");
        }
    }
    
    // Check for duplicates
    $duplicate_check = checkMarriageDuplicate($data['husband_ln'], $data['husband_fn'], $data['maiden_wife_ln'], $data['maiden_wife_fn'], $data['marriage_date']);
    if ($duplicate_check['found']) {
        throw new Exception($duplicate_check['message']);
    }
    
    // Prepare data
    $husband_ln = trim($data['husband_ln']);
    $husband_fn = trim($data['husband_fn']);
    $husband_mn = !empty($data['husband_mn']) ? trim($data['husband_mn']) : '';
    $maiden_wife_ln = trim($data['maiden_wife_ln']);
    $maiden_wife_fn = trim($data['maiden_wife_fn']);
    $maiden_wife_mn = !empty($data['maiden_wife_mn']) ? trim($data['maiden_wife_mn']) : '';
    $marriage_date = $data['marriage_date'];
    $place_of_marriage = trim($data['place_of_marriage']);
    $purpose_of_request = $data['purpose_of_request'];
    $registration_date = $data['registration_date'];
    $type_request = 'Marriage Certificate';
    
    // Encrypt PII fields
    $husband_ln_data = encryptAndTokenize($husband_ln);
    $husband_fn_data = encryptAndTokenize($husband_fn);
    $husband_mn_data = encryptAndTokenize($husband_mn);
    $wife_ln_data = encryptAndTokenize($maiden_wife_ln);
    $wife_fn_data = encryptAndTokenize($maiden_wife_fn);
    $wife_mn_data = encryptAndTokenize($maiden_wife_mn);
    
    // Insert into marriage_tbl
    $stmt = $conn->prepare("INSERT INTO marriage_tbl (id_user, husband_ln, husband_fn, husband_mn, maiden_wife_ln, maiden_wife_fn, maiden_wife_mn, husband_ln_enc, husband_ln_tok, husband_fn_enc, husband_fn_tok, husband_mn_enc, husband_mn_tok, maiden_wife_ln_enc, maiden_wife_ln_tok, maiden_wife_fn_enc, maiden_wife_fn_tok, maiden_wife_mn_enc, maiden_wife_mn_tok, marriage_date, place_of_marriage, purpose_of_request, type_request, status_request) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $id_user = $_SESSION['id_user'];
    $stmt->bind_param("isssssssssssssssssssssss", 
        $id_user, 
        $husband_ln, $husband_fn, $husband_mn, $maiden_wife_ln, $maiden_wife_fn, $maiden_wife_mn,
        $husband_ln_data['encrypted'], $husband_ln_data['token'],
        $husband_fn_data['encrypted'], $husband_fn_data['token'],
        $husband_mn_data['encrypted'], $husband_mn_data['token'],
        $wife_ln_data['encrypted'], $wife_ln_data['token'],
        $wife_fn_data['encrypted'], $wife_fn_data['token'],
        $wife_mn_data['encrypted'], $wife_mn_data['token'],
        $marriage_date, $place_of_marriage, $purpose_of_request, $type_request, $status);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save marriage record: ' . $stmt->error);
    }
    
    $id_marriage = $stmt->insert_id;
    $stmt->close();
    
    // Insert into civ_record
    $registrar_name = "$maiden_wife_ln $maiden_wife_mn $maiden_wife_fn & $husband_ln $husband_mn $husband_fn";
    $civRecordSql = "INSERT INTO civ_record (id_marriage, registration_date, registrar_name, type_request) VALUES ('$id_marriage', '$registration_date', '$registrar_name', '$type_request')";
    
    if (!$conn->query($civRecordSql)) {
        throw new Exception('Failed to save civil record: ' . $conn->error);
    }
    
    return ['success' => true, 'message' => 'Marriage record saved successfully!'];
}

function saveDeathRecord($data, $status) {
    global $conn;
    
    // Validate required fields
    $required_fields = ['deceased_ln', 'deceased_fn', 'dob', 'dod', 'place_of_death', 'purpose_of_request', 'registration_date'];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field {$field} is required.");
        }
    }
    
    // Check for duplicates
    $duplicate_check = checkDeathDuplicate($data['deceased_ln'], $data['deceased_fn'], $data['dob'], $data['dod']);
    if ($duplicate_check['found']) {
        throw new Exception($duplicate_check['message']);
    }
    
    // Prepare data
    $deceased_ln = trim($data['deceased_ln']);
    $deceased_fn = trim($data['deceased_fn']);
    $deceased_mn = !empty($data['deceased_mn']) ? trim($data['deceased_mn']) : '';
    $dob = $data['dob'];
    $dod = $data['dod'];
    $place_of_death = trim($data['place_of_death']);
    $purpose_of_request = $data['purpose_of_request'];
    $registration_date = $data['registration_date'];
    $type_request = 'Death Certificate';
    
    // Encrypt PII fields
    $deceased_ln_data = encryptAndTokenize($deceased_ln);
    $deceased_fn_data = encryptAndTokenize($deceased_fn);
    $deceased_mn_data = encryptAndTokenize($deceased_mn);
    $dob_data = encryptAndTokenize($dob);
    
    // Insert into death_tbl
    $stmt = $conn->prepare("INSERT INTO death_tbl (id_user, deceased_ln, deceased_fn, deceased_mn, deceased_ln_enc, deceased_ln_tok, deceased_fn_enc, deceased_fn_tok, deceased_mn_enc, deceased_mn_tok, dob_enc, dob_tok, dob, dod, place_of_death, purpose_of_request, type_request, status_request) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $id_user = $_SESSION['id_user'];
    $stmt->bind_param("isssssssssssssssss", 
        $id_user, 
        $deceased_ln, $deceased_fn, $deceased_mn,
        $deceased_ln_data['encrypted'], $deceased_ln_data['token'],
        $deceased_fn_data['encrypted'], $deceased_fn_data['token'],
        $deceased_mn_data['encrypted'], $deceased_mn_data['token'],
        $dob_data['encrypted'], $dob_data['token'],
        $dob, $dod, $place_of_death, $purpose_of_request, $type_request, $status);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save death record: ' . $stmt->error);
    }
    
    $id_death = $stmt->insert_id;
    $stmt->close();
    
    // Insert into civ_record
    $registrar_name = "$deceased_ln $deceased_mn $deceased_fn";
    $civRecordSql = "INSERT INTO civ_record (id_death, registration_date, registrar_name, type_request) VALUES ('$id_death', '$registration_date', '$registrar_name', '$type_request')";
    
    if (!$conn->query($civRecordSql)) {
        throw new Exception('Failed to save civil record: ' . $conn->error);
    }
    
    return ['success' => true, 'message' => 'Death record saved successfully!'];
}

// Duplicate checking functions
function checkBirthDuplicate($lastname, $firstname, $dob) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM birthceno_tbl WHERE lastname = ? AND firstname = ? AND dob = ?");
    $stmt->bind_param("sss", $lastname, $firstname, $dob);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['count'] > 0) {
        return ['found' => true, 'message' => 'WARNING: A birth record with the same name and date of birth already exists. Please verify if this is intentional.'];
    }
    
    // Check for similar names (fuzzy matching)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM birthceno_tbl WHERE (lastname LIKE ? OR lastname LIKE ?) AND (firstname LIKE ? OR firstname LIKE ?) AND dob = ?");
    $lastname1 = "%$lastname%";
    $lastname2 = "%" . str_replace([' ', '-'], ['%', '%'], $lastname) . "%";
    $firstname1 = "%$firstname%";
    $firstname2 = "%" . str_replace([' ', '-'], ['%', '%'], $firstname) . "%";
    $stmt->bind_param("sssss", $lastname1, $lastname2, $firstname1, $firstname2, $dob);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['count'] > 0) {
        return ['found' => true, 'message' => 'WARNING: A similar birth record exists. Please verify the name spelling and date of birth.'];
    }
    
    return ['found' => false];
}

function checkMarriageDuplicate($husband_ln, $husband_fn, $wife_ln, $wife_fn, $marriage_date) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM marriage_tbl WHERE husband_ln = ? AND husband_fn = ? AND maiden_wife_ln = ? AND maiden_wife_fn = ? AND marriage_date = ?");
    $stmt->bind_param("sssss", $husband_ln, $husband_fn, $wife_ln, $wife_fn, $marriage_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['count'] > 0) {
        return ['found' => true, 'message' => 'WARNING: A marriage record with the same couple and marriage date already exists. Please verify if this is intentional.'];
    }
    
    return ['found' => false];
}

function checkDeathDuplicate($deceased_ln, $deceased_fn, $dob, $dod) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM death_tbl WHERE deceased_ln = ? AND deceased_fn = ? AND dob = ? AND dod = ?");
    $stmt->bind_param("ssss", $deceased_ln, $deceased_fn, $dob, $dod);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['count'] > 0) {
        return ['found' => true, 'message' => 'WARNING: A death record with the same name, birth date, and death date already exists. Please verify if this is intentional.'];
    }
    
    return ['found' => false];
}
?>
