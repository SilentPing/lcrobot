<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formType = $_POST['form_type'];
    $id_user = $_POST['id_user'];
    
    try {
        // Server-side validation
        $validationErrors = validateWalkinRequest($_POST, $formType);
        if (!empty($validationErrors)) {
            $errorMessage = 'Please fill in the following required fields: ' . implode(', ', $validationErrors);
            echo json_encode(['success' => false, 'message' => $errorMessage]);
            exit;
        }
        
        switch ($formType) {
            case 'birth':
                $result = submitBirthRequest($_POST);
                break;
            case 'ceno':
                $result = submitCenoRequest($_POST);
                break;
            case 'death':
                $result = submitDeathRequest($_POST);
                break;
            case 'marriage':
                $result = submitMarriageRequest($_POST);
                break;
            default:
                throw new Exception('Invalid form type');
        }
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Request submitted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit request']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function validateWalkinRequest($data, $formType) {
    $errors = [];
    
    // Common validations
    if (empty($data['form_type'])) {
        $errors[] = 'Form type is required';
    }
    
    if (empty($data['id_user'])) {
        $errors[] = 'User ID is required';
    }
    
    // Form-specific validations
    switch ($formType) {
        case 'birth':
        case 'ceno':
            $requiredFields = [
                'lastname', 'firstname', 'middlename', 'pob_country', 'pob_province', 
                'pob_municipality', 'dob', 'sex', 'relationship', 'fath_ln', 'fath_fn', 
                'fath_mn', 'moth_maiden_ln', 'moth_maiden_fn', 'moth_maiden_mn', 'purpose_of_request',
                'applicant_name', 'contact_no'
            ];
            break;
        case 'death':
            $requiredFields = [
                'deceased_ln', 'deceased_fn', 'deceased_mn', 'dob', 'dod', 
                'place_of_death', 'gender', 'purpose_of_request', 'applicant_name', 'contact_no'
            ];
            break;
        case 'marriage':
            $requiredFields = [
                'husband_ln', 'husband_fn', 'husband_mn', 'maiden_wife_ln', 'maiden_wife_fn', 
                'maiden_wife_mn', 'pob_country', 'pob_province', 'pob_municipality', 
                'dob', 'place_of_marriage', 'purpose_of_request', 'applicant_name', 'contact_no'
            ];
            break;
        default:
            $errors[] = 'Invalid form type';
            return $errors;
    }
    
    // Check required fields
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field]) || trim($data[$field]) === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    // Email is optional for walk-in requests, so remove it from validation if it's empty
    if (isset($data['email']) && (empty($data['email']) || trim($data['email']) === '')) {
        // Email is optional, so we don't add it to errors
        unset($data['email']); // Remove empty email from data
    }
    
    // Date validations
    if (isset($data['dob']) && !empty($data['dob'])) {
        $dob = DateTime::createFromFormat('Y-m-d', $data['dob']);
        if (!$dob || $dob->format('Y-m-d') !== $data['dob']) {
            $errors[] = 'Invalid date of birth format';
        } else {
            $today = new DateTime();
            if ($dob > $today) {
                $errors[] = 'Date of birth cannot be in the future';
            }
        }
    }
    
    if (isset($data['dod']) && !empty($data['dod'])) {
        $dod = DateTime::createFromFormat('Y-m-d', $data['dod']);
        if (!$dod || $dod->format('Y-m-d') !== $data['dod']) {
            $errors[] = 'Invalid date of death format';
        } else if (isset($data['dob']) && !empty($data['dob'])) {
            $dob = DateTime::createFromFormat('Y-m-d', $data['dob']);
            if ($dob && $dod < $dob) {
                $errors[] = 'Date of death cannot be before date of birth';
            }
        }
    }
    
    // Additional validations for contact fields
    if (isset($data['contact_no']) && !empty($data['contact_no'])) {
        if (!preg_match('/^09[0-9]{9}$/', $data['contact_no'])) {
            $errors[] = 'Contact number must be in format 09XXXXXXXXX';
        }
    }
    
    if (isset($data['email']) && !empty($data['email'])) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format (if provided)';
        }
    }
    
    // Sanitize and validate input
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            // Check for potential XSS
            if (preg_match('/<script|javascript:|on\w+\s*=/i', $value)) {
                $errors[] = 'Invalid characters detected in ' . $key;
            }
            
            // Check length limits
            if (strlen($value) > 255) {
                $errors[] = ucfirst(str_replace('_', ' ', $key)) . ' is too long (maximum 255 characters)';
            }
        }
    }
    
    return $errors;
}

function submitBirthRequest($data) {
    global $conn;
    
    // Encrypt PII fields
    $lastname_data = encryptAndTokenize($data['lastname']);
    $firstname_data = encryptAndTokenize($data['firstname']);
    $middlename_data = encryptAndTokenize($data['middlename']);
    $dob_data = encryptAndTokenize($data['dob']);
    $sex_data = encryptAndTokenize($data['sex']);
    
    $stmt = $conn->prepare("INSERT INTO birthceno_tbl (id_user, lastname, lastname_enc, lastname_tok, firstname, firstname_enc, firstname_tok, middlename, middlename_enc, middlename_tok, pob_country, pob_province, pob_municipality, dob, dob_enc, dob_tok, sex, sex_enc, sex_tok, fath_ln, fath_fn, fath_mn, moth_maiden_ln, moth_maiden_fn, moth_maiden_mn, relationship, purpose_of_request, type_request, status_request) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $status_request = 'PENDING';
    
    $stmt->bind_param("issssssssssssssssssssssssssss", 
        $data['id_user'], 
        $data['lastname'], // Keep original for backward compatibility
        $lastname_data['encrypted'], $lastname_data['token'],
        $data['firstname'], // Keep original for backward compatibility
        $firstname_data['encrypted'], $firstname_data['token'],
        $data['middlename'], // Keep original for backward compatibility
        $middlename_data['encrypted'], $middlename_data['token'],
        $data['pob_country'], $data['pob_province'], $data['pob_municipality'], 
        $data['dob'], // Keep original for backward compatibility
        $dob_data['encrypted'], $dob_data['token'],
        $data['sex'], // Keep original for backward compatibility
        $sex_data['encrypted'], $sex_data['token'],
        $data['fath_ln'], $data['fath_fn'], $data['fath_mn'], 
        $data['moth_maiden_ln'], $data['moth_maiden_fn'], $data['moth_maiden_mn'], 
        $data['relationship'], $data['purpose_of_request'], $data['type_request'], $status_request);
    
    if ($stmt->execute()) {
        $id_birth_ceno = $stmt->insert_id;
        $stmt->close();
        
        // Insert into civ_record table
        $registration_date = date('Y-m-d H:i:s');
        $registrar_name = $data['firstname'] . ' ' . $data['middlename'] . ' ' . $data['lastname'];
        
        $civRecordSql = "INSERT INTO civ_record (id_birth_ceno, registration_date, registrar_name, type_request) VALUES ('$id_birth_ceno', '$registration_date', '$registrar_name', '{$data['type_request']}')";
        
        if ($conn->query($civRecordSql) === TRUE) {
            // Insert into reqtracking_tbl with applicant's contact info
            $email = !empty($data['email']) ? $data['email'] : '';
            $gender = $data['gender'] ?? 'Male'; // Use gender field for death requests
            $reqTrackingSql = "INSERT INTO reqtracking_tbl (type_request, registration_date, registrar_name, user_id, status, contact_no, email, gender) VALUES ('{$data['type_request']}', '$registration_date', '{$data['applicant_name']}', '{$data['id_user']}', 'Pending', '{$data['contact_no']}', '$email', '$gender')";
            $conn->query($reqTrackingSql);
            
            return true;
        }
    }
    
    return false;
}

function submitCenoRequest($data) {
    // CENO form uses the same structure as birth form
    return submitBirthRequest($data);
}

function submitDeathRequest($data) {
    global $conn;
    
    // Encrypt PII fields
    $deceased_ln_data = encryptAndTokenize($data['deceased_ln']);
    $deceased_fn_data = encryptAndTokenize($data['deceased_fn']);
    $deceased_mn_data = encryptAndTokenize($data['deceased_mn']);
    $dob_data = encryptAndTokenize($data['dob']);
    
    $stmt = $conn->prepare("INSERT INTO death_tbl (id_user, deceased_ln, deceased_ln_enc, deceased_ln_tok, deceased_fn, deceased_fn_enc, deceased_fn_tok, deceased_mn, deceased_mn_enc, deceased_mn_tok, dob, dob_enc, dob_tok, dod, place_of_death, purpose_of_request, type_request, status_request) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $status_request = 'PENDING';
    
    $stmt->bind_param("isssssssssssssssss", 
        $data['id_user'], 
        $data['deceased_ln'], // Keep original for backward compatibility
        $deceased_ln_data['encrypted'], $deceased_ln_data['token'],
        $data['deceased_fn'], // Keep original for backward compatibility
        $deceased_fn_data['encrypted'], $deceased_fn_data['token'],
        $data['deceased_mn'], // Keep original for backward compatibility
        $deceased_mn_data['encrypted'], $deceased_mn_data['token'],
        $data['dob'], // Keep original for backward compatibility
        $dob_data['encrypted'], $dob_data['token'],
        $data['dod'], $data['place_of_death'], 
        $data['purpose_of_request'], $data['type_request'], $status_request);
    
    if ($stmt->execute()) {
        $id_death = $stmt->insert_id;
        $stmt->close();
        
        // Insert into civ_record table
        $registration_date = date('Y-m-d H:i:s');
        $registrar_name = $data['deceased_fn'] . ' ' . $data['deceased_mn'] . ' ' . $data['deceased_ln'];
        
        $civRecordSql = "INSERT INTO civ_record (id_death, registration_date, registrar_name, type_request) VALUES ('$id_death', '$registration_date', '$registrar_name', '{$data['type_request']}')";
        
        if ($conn->query($civRecordSql) === TRUE) {
            // Insert into reqtracking_tbl with applicant's contact info
            $email = !empty($data['email']) ? $data['email'] : '';
            $gender = $data['gender'] ?? 'Male'; // Use gender field for death requests
            $reqTrackingSql = "INSERT INTO reqtracking_tbl (type_request, registration_date, registrar_name, user_id, status, contact_no, email, gender) VALUES ('{$data['type_request']}', '$registration_date', '{$data['applicant_name']}', '{$data['id_user']}', 'Pending', '{$data['contact_no']}', '$email', '$gender')";
            $conn->query($reqTrackingSql);
            
            return true;
        }
    }
    
    return false;
}

function submitMarriageRequest($data) {
    global $conn;
    
    // Encrypt PII fields
    $husband_ln_data = encryptAndTokenize($data['husband_ln']);
    $husband_fn_data = encryptAndTokenize($data['husband_fn']);
    $husband_mn_data = encryptAndTokenize($data['husband_mn']);
    $maiden_wife_ln_data = encryptAndTokenize($data['maiden_wife_ln']);
    $maiden_wife_fn_data = encryptAndTokenize($data['maiden_wife_fn']);
    $maiden_wife_mn_data = encryptAndTokenize($data['maiden_wife_mn']);
    
    $stmt = $conn->prepare("INSERT INTO marriage_tbl (id_user, husband_ln, husband_ln_enc, husband_ln_tok, husband_fn, husband_fn_enc, husband_fn_tok, husband_mn, husband_mn_enc, husband_mn_tok, maiden_wife_ln, maiden_wife_ln_enc, maiden_wife_ln_tok, maiden_wife_fn, maiden_wife_fn_enc, maiden_wife_fn_tok, maiden_wife_mn, maiden_wife_mn_enc, maiden_wife_mn_tok, marriage_date, place_of_marriage, purpose_of_request, type_request, status_request) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $status_request = 'PENDING';
    $marriage_date = $data['dob']; // Use dob as marriage_date since that's what the form provides
    
    $stmt->bind_param("isssssssssssssssssssssss", 
        $data['id_user'], 
        $data['husband_ln'], // Keep original for backward compatibility
        $husband_ln_data['encrypted'], $husband_ln_data['token'],
        $data['husband_fn'], // Keep original for backward compatibility
        $husband_fn_data['encrypted'], $husband_fn_data['token'],
        $data['husband_mn'], // Keep original for backward compatibility
        $husband_mn_data['encrypted'], $husband_mn_data['token'],
        $data['maiden_wife_ln'], // Keep original for backward compatibility
        $maiden_wife_ln_data['encrypted'], $maiden_wife_ln_data['token'],
        $data['maiden_wife_fn'], // Keep original for backward compatibility
        $maiden_wife_fn_data['encrypted'], $maiden_wife_fn_data['token'],
        $data['maiden_wife_mn'], // Keep original for backward compatibility
        $maiden_wife_mn_data['encrypted'], $maiden_wife_mn_data['token'],
        $marriage_date, 
        $data['place_of_marriage'], 
        $data['purpose_of_request'], $data['type_request'], $status_request);
    
    if ($stmt->execute()) {
        $id_marriage = $stmt->insert_id;
        $stmt->close();
        
        // Insert into civ_record table
        $registration_date = date('Y-m-d H:i:s');
        $registrar_name = $data['husband_fn'] . ' ' . $data['husband_mn'] . ' ' . $data['husband_ln'] . ' & ' . $data['maiden_wife_fn'] . ' ' . $data['maiden_wife_mn'] . ' ' . $data['maiden_wife_ln'];
        
        $civRecordSql = "INSERT INTO civ_record (id_marriage, registration_date, registrar_name, type_request) VALUES ('$id_marriage', '$registration_date', '$registrar_name', '{$data['type_request']}')";
        
        if ($conn->query($civRecordSql) === TRUE) {
            // Insert into reqtracking_tbl with applicant's contact info
            $email = !empty($data['email']) ? $data['email'] : '';
            $gender = $data['gender'] ?? 'Male'; // Use gender field for death requests
            $reqTrackingSql = "INSERT INTO reqtracking_tbl (type_request, registration_date, registrar_name, user_id, status, contact_no, email, gender) VALUES ('{$data['type_request']}', '$registration_date', '{$data['applicant_name']}', '{$data['id_user']}', 'Pending', '{$data['contact_no']}', '$email', '$gender')";
            $conn->query($reqTrackingSql);
            
            return true;
        }
    }
    
    return false;
}
?>
