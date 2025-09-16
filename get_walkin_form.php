<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit;
}

require_once __DIR__ . '/db.php';

if (isset($_POST['formType'])) {
    $formType = $_POST['formType'];
    error_log("Form type received: " . $formType);
    
    switch ($formType) {
        case 'birth':
            echo getBirthForm();
            break;
        case 'ceno':
            echo getCenoForm();
            break;
        case 'death':
            echo getDeathForm();
            break;
        case 'marriage':
            echo getMarriageForm();
            break;
        default:
            echo '<div class="alert alert-danger">Invalid form type selected: ' . $formType . '</div>';
    }
} else {
    echo '<div class="alert alert-danger">No form type specified.</div>';
}

function getBirthForm() {
    $provinceOptions = getProvinceOptions();
    return '
    <form id="walkinForm">
        <input type="hidden" name="form_type" value="birth">
        <input type="hidden" name="id_user" value="' . $_SESSION['id_user'] . '">
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="lastname" class="form-label">Last Name *</label>
                    <input type="text" class="form-control" id="lastname" name="lastname" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="firstname" class="form-label">First Name *</label>
                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="middlename" class="form-label">Middle Name *</label>
                    <input type="text" class="form-control" id="middlename" name="middlename" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="pob_country" class="form-label">Place of Birth (Country) *</label>
                    <input type="text" class="form-control" id="pob_country" name="pob_country" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="pob_province" class="form-label">Place of Birth (Province) *</label>
                    <select class="form-control" id="pob_province" name="pob_province" required>
                        <option value="">Select Province</option>
                        ' . $provinceOptions . '
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="pob_municipality" class="form-label">Place of Birth (City/Municipality) *</label>
                    <input type="text" class="form-control" id="pob_municipality" name="pob_municipality" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="dob" class="form-label">Date of Birth *</label>
                    <input type="date" class="form-control" id="dob" name="dob" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="sex" class="form-label">Sex *</label>
                    <select class="form-control" id="sex" name="sex" required>
                        <option value="">Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="relationship" class="form-label">Relationship to Document Owner *</label>
                    <select class="form-control" id="relationship" name="relationship" required>
                        <option value="">Select Relationship</option>
                        <option value="Registrant">Registrant</option>
                        <option value="Parent">Parent</option>
                        <option value="Sibling">Sibling</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="fath_ln" class="form-label">Father\'s Last Name *</label>
                    <input type="text" class="form-control" id="fath_ln" name="fath_ln" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="fath_fn" class="form-label">Father\'s First Name *</label>
                    <input type="text" class="form-control" id="fath_fn" name="fath_fn" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="fath_mn" class="form-label">Father\'s Middle Name *</label>
                    <input type="text" class="form-control" id="fath_mn" name="fath_mn" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="moth_maiden_ln" class="form-label">Mother\'s Maiden Last Name *</label>
                    <input type="text" class="form-control" id="moth_maiden_ln" name="moth_maiden_ln" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="moth_maiden_fn" class="form-label">Mother\'s Maiden First Name *</label>
                    <input type="text" class="form-control" id="moth_maiden_fn" name="moth_maiden_fn" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="moth_maiden_mn" class="form-label">Mother\'s Maiden Middle Name *</label>
                    <input type="text" class="form-control" id="moth_maiden_mn" name="moth_maiden_mn" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="purpose_of_request" class="form-label">Purpose of Request *</label>
                    <select class="form-control" id="purpose_of_request" name="purpose_of_request" required>
                        <option value="">Select Purpose</option>
                        <option value="Registration">Registration</option>
                        <option value="Credentials Update">Credentials Update</option>
                        <option value="Record Keeping">Record Keeping</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="type_request" class="form-label">Type of Request</label>
                    <input type="text" class="form-control" id="type_request" name="type_request" value="Birth Certificate" readonly>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        <h6 class="text-primary mb-3"><i class="bi bi-person-lines-fill"></i> Applicant Contact Information</h6>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="applicant_name" class="form-label">Applicant Full Name *</label>
                    <input type="text" class="form-control" id="applicant_name" name="applicant_name" required placeholder="Enter applicant\'s full name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="contact_no" class="form-label">Contact Number * <small class="text-muted">(Primary communication method)</small></label>
                    <input type="tel" class="form-control" id="contact_no" name="contact_no" required placeholder="09XXXXXXXXX" pattern="09[0-9]{9}">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <small class="text-muted">(Optional - if available)</small></label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="applicant@example.com (optional)">
                </div>
            </div>
        </div>
    </form>';
}

function getCenoForm() {
    $provinceOptions = getProvinceOptions();
    return '
    <form id="walkinForm">
        <input type="hidden" name="form_type" value="ceno">
        <input type="hidden" name="id_user" value="' . $_SESSION['id_user'] . '">
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="lastname" class="form-label">Last Name *</label>
                    <input type="text" class="form-control" id="lastname" name="lastname" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="firstname" class="form-label">First Name *</label>
                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="middlename" class="form-label">Middle Name *</label>
                    <input type="text" class="form-control" id="middlename" name="middlename" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="pob_country" class="form-label">Place of Birth (Country) *</label>
                    <input type="text" class="form-control" id="pob_country" name="pob_country" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="pob_province" class="form-label">Place of Birth (Province) *</label>
                    <select class="form-control" id="pob_province" name="pob_province" required>
                        <option value="">Select Province</option>
                        ' . $provinceOptions . '
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="pob_municipality" class="form-label">Place of Birth (City/Municipality) *</label>
                    <input type="text" class="form-control" id="pob_municipality" name="pob_municipality" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="dob" class="form-label">Date of Birth *</label>
                    <input type="date" class="form-control" id="dob" name="dob" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="sex" class="form-label">Sex *</label>
                    <select class="form-control" id="sex" name="sex" required>
                        <option value="">Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="relationship" class="form-label">Relationship to Document Owner *</label>
                    <select class="form-control" id="relationship" name="relationship" required>
                        <option value="">Select Relationship</option>
                        <option value="Registrant">Registrant</option>
                        <option value="Parent">Parent</option>
                        <option value="Sibling">Sibling</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="fath_ln" class="form-label">Father\'s Last Name *</label>
                    <input type="text" class="form-control" id="fath_ln" name="fath_ln" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="fath_fn" class="form-label">Father\'s First Name *</label>
                    <input type="text" class="form-control" id="fath_fn" name="fath_fn" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="fath_mn" class="form-label">Father\'s Middle Name *</label>
                    <input type="text" class="form-control" id="fath_mn" name="fath_mn" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="moth_maiden_ln" class="form-label">Mother\'s Maiden Last Name *</label>
                    <input type="text" class="form-control" id="moth_maiden_ln" name="moth_maiden_ln" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="moth_maiden_fn" class="form-label">Mother\'s Maiden First Name *</label>
                    <input type="text" class="form-control" id="moth_maiden_fn" name="moth_maiden_fn" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="moth_maiden_mn" class="form-label">Mother\'s Maiden Middle Name *</label>
                    <input type="text" class="form-control" id="moth_maiden_mn" name="moth_maiden_mn" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="purpose_of_request" class="form-label">Purpose of Request *</label>
                    <select class="form-control" id="purpose_of_request" name="purpose_of_request" required>
                        <option value="">Select Purpose</option>
                        <option value="Registration">Registration</option>
                        <option value="Credentials Update">Credentials Update</option>
                        <option value="Record Keeping">Record Keeping</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="type_request" class="form-label">Type of Request</label>
                    <input type="text" class="form-control" id="type_request" name="type_request" value="CENOMAR" readonly>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        <h6 class="text-primary mb-3"><i class="bi bi-person-lines-fill"></i> Applicant Contact Information</h6>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="applicant_name" class="form-label">Applicant Full Name *</label>
                    <input type="text" class="form-control" id="applicant_name" name="applicant_name" required placeholder="Enter applicant\'s full name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="contact_no" class="form-label">Contact Number * <small class="text-muted">(Please Provide Your Contact Number)</small></label>
                    <input type="tel" class="form-control" id="contact_no" name="contact_no" required placeholder="09XXXXXXXXX" pattern="09[0-9]{9}">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <small class="text-muted">(Optional - if available)</small></label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="applicant@example.com (optional)">
                </div>
            </div>
        </div>
    </form>';
}

function getDeathForm() {
    return '
    <form id="walkinForm">
        <input type="hidden" name="form_type" value="death">
        <input type="hidden" name="id_user" value="' . $_SESSION['id_user'] . '">
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="deceased_ln" class="form-label">Deceased Last Name *</label>
                    <input type="text" class="form-control" id="deceased_ln" name="deceased_ln" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="deceased_fn" class="form-label">Deceased First Name *</label>
                    <input type="text" class="form-control" id="deceased_fn" name="deceased_fn" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="deceased_mn" class="form-label">Deceased Middle Name *</label>
                    <input type="text" class="form-control" id="deceased_mn" name="deceased_mn" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="dob" class="form-label">Date of Birth *</label>
                    <input type="date" class="form-control" id="dob" name="dob" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="dod" class="form-label">Date of Death *</label>
                    <input type="date" class="form-control" id="dod" name="dod" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="place_of_death" class="form-label">Place of Death *</label>
                    <input type="text" class="form-control" id="place_of_death" name="place_of_death" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="purpose_of_request" class="form-label">Purpose of Request *</label>
                    <select class="form-control" id="purpose_of_request" name="purpose_of_request" required>
                        <option value="">Select Purpose</option>
                        <option value="Registration">Registration</option>
                        <option value="Credentials Update">Credentials Update</option>
                        <option value="Record Keeping">Record Keeping</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="type_request" class="form-label">Type of Request</label>
                    <input type="text" class="form-control" id="type_request" name="type_request" value="Death Certificate" readonly>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        <h6 class="text-primary mb-3"><i class="bi bi-person-lines-fill"></i> Applicant Contact Information</h6>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="applicant_name" class="form-label">Applicant Full Name *</label>
                    <input type="text" class="form-control" id="applicant_name" name="applicant_name" required placeholder="Enter applicant\'s full name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="contact_no" class="form-label">Contact Number * <small class="text-muted">(Primary communication method)</small></label>
                    <input type="tel" class="form-control" id="contact_no" name="contact_no" required placeholder="09XXXXXXXXX" pattern="09[0-9]{9}">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <small class="text-muted">(Optional - if available)</small></label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="applicant@example.com (optional)">
                </div>
            </div>
        </div>
    </form>';
}

function getMarriageForm() {
    return '
    <form id="walkinForm">
        <input type="hidden" name="form_type" value="marriage">
        <input type="hidden" name="id_user" value="' . $_SESSION['id_user'] . '">
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="husband_ln" class="form-label">Husband Last Name *</label>
                    <input type="text" class="form-control" id="husband_ln" name="husband_ln" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="husband_fn" class="form-label">Husband First Name *</label>
                    <input type="text" class="form-control" id="husband_fn" name="husband_fn" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="husband_mn" class="form-label">Husband Middle Name *</label>
                    <input type="text" class="form-control" id="husband_mn" name="husband_mn" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="pob_country" class="form-label">Place of Birth (Country) *</label>
                    <input type="text" class="form-control" id="pob_country" name="pob_country" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="pob_province" class="form-label">Place of Birth (Province) *</label>
                    <input type="text" class="form-control" id="pob_province" name="pob_province" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="pob_municipality" class="form-label">Place of Birth (City/Municipality) *</label>
                    <input type="text" class="form-control" id="pob_municipality" name="pob_municipality" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="maiden_wife_ln" class="form-label">Maiden Wife Last Name *</label>
                    <input type="text" class="form-control" id="maiden_wife_ln" name="maiden_wife_ln" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="maiden_wife_fn" class="form-label">Maiden Wife First Name *</label>
                    <input type="text" class="form-control" id="maiden_wife_fn" name="maiden_wife_fn" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="maiden_wife_mn" class="form-label">Maiden Wife Middle Name *</label>
                    <input type="text" class="form-control" id="maiden_wife_mn" name="maiden_wife_mn" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="dob" class="form-label">Date of Birth *</label>
                    <input type="date" class="form-control" id="dob" name="dob" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="place_of_marriage" class="form-label">Place of Marriage *</label>
                    <input type="text" class="form-control" id="place_of_marriage" name="place_of_marriage" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="purpose_of_request" class="form-label">Purpose of Request *</label>
                    <select class="form-control" id="purpose_of_request" name="purpose_of_request" required>
                        <option value="">Select Purpose</option>
                        <option value="Registration">Registration</option>
                        <option value="Credentials Update">Credentials Update</option>
                        <option value="Record Keeping">Record Keeping</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="type_request" class="form-label">Type of Request</label>
                    <input type="text" class="form-control" id="type_request" name="type_request" value="Marriage Certificate" readonly>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        <h6 class="text-primary mb-3"><i class="bi bi-person-lines-fill"></i> Applicant Contact Information</h6>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="applicant_name" class="form-label">Applicant Full Name *</label>
                    <input type="text" class="form-control" id="applicant_name" name="applicant_name" required placeholder="Enter applicant\'s full name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="contact_no" class="form-label">Contact Number * <small class="text-muted">(Primary communication method)</small></label>
                    <input type="tel" class="form-control" id="contact_no" name="contact_no" required placeholder="09XXXXXXXXX" pattern="09[0-9]{9}">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <small class="text-muted">(Optional - if available)</small></label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="applicant@example.com (optional)">
                </div>
            </div>
        </div>
    </form>';
}

function getProvinceOptions() {
    global $conn;
    $options = '';
    
    try {
        $sql = "SELECT provDesc, provCode FROM refprovince ORDER BY provDesc";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $options .= '<option value="' . $row['provCode'] . '">' . $row['provDesc'] . '</option>';
            }
        }
    } catch (Exception $e) {
        error_log("Error getting province options: " . $e->getMessage());
        $options = '<option value="">Error loading provinces</option>';
    }
    
    return $options;
}
?>
