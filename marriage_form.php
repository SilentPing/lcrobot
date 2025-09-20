<?php

session_start();

// Check if the user is logged in or registered
if (!isset($_SESSION['name'])) {
    // Redirect to login page or handle authentication
    header("Location: login.php");
    exit;
}

$u_ln = '';
$u_fn = '';
$u_mn = '';
$username = '';
$email = '';
$contact_no = '';
$house_no = '';
$street_brgy = '';
$city_municipality = '';
$province = '';


if (isset($_GET['type_request'])) {
    $typeRequest = $_GET['type_request'];
} else {
    // Default value if the parameter is not set
    $typeRequest = "";
}

// Check if user data is stored in the session
if (isset($_SESSION['user_data'])) {
    $user_data = $_SESSION['user_data'];

    // Now, you can pre-fill the form fields with the user's data
    $u_ln = $user_data['u_ln'];
    $u_fn = $user_data['u_fn'];
    $u_mn = $user_data['u_mn'];
    $username = $user_data['username'];
    $email = $user_data['email'];
    $contact_no = $user_data['contact_no'];
    $house_no = $user_data['house_no'];
    $street_brgy = $user_data['street_brgy'];
    $city_municipality = $user_data['city_municipality'];
    $province = $user_data['province'];

    // Unset or clear the session data to avoid pre-filling the form on subsequent visits
    unset($_SESSION['user_data']);
}


// Load database connection and encryption functions
require_once __DIR__ . '/db.php';

// Get user's document eligibility
$document_eligibility = 'PSA Only'; // Default
$birthplace_municipality = '';
$birthplace_province = '';

// Get user's birthplace information to determine document eligibility
$user_email = $_SESSION['name'];
$user_query = "SELECT birthplace_municipality, birthplace_province FROM users WHERE email = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $user_email);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $birthplace_municipality = $user_data['birthplace_municipality'] ?? '';
    $birthplace_province = $user_data['birthplace_province'] ?? '';
    
    // Check if user is eligible for LCRO documents (born in Botolan)
    if ($birthplace_municipality === '037101' && $birthplace_province === '0371') {
        $document_eligibility = 'LCRO + PSA';
    } else if (!empty($birthplace_municipality) && !empty($birthplace_province)) {
        $document_eligibility = 'PSA Only';
    } else {
        $document_eligibility = 'Incomplete Profile';
    }
}
$user_stmt->close();

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the form
    // $id_birth_ceno = $_POST["id_birth_ceno"];
    // $id_user = $_POST["id_user"];
    $husband_ln = $_POST["husband_ln"];
    $husband_fn = $_POST["husband_fn"];
    $husband_mn = $_POST["husband_mn"];
    $maiden_wife_ln = $_POST["maiden_wife_ln"];
    $maiden_wife_fn = $_POST["maiden_wife_fn"];
    $maiden_wife_mn = $_POST["maiden_wife_mn"];
    $marriage_date = $_POST["marriage_date"];
    $place_of_marriage = $_POST["place_of_marriage"];
    $purpose_of_request = $_POST["purpose_of_request"];
    $type_request = $_POST["type_request"];
    $document_type = $_POST["document_type"];
    $status_request = 'PENDING';
    $id_user = $_POST['id_user']; // Retrieve id_user from the form data
    // Add other fields here

    // Encrypt PII fields
    $husband_ln_data = encryptAndTokenize($husband_ln);
    $husband_fn_data = encryptAndTokenize($husband_fn);
    $husband_mn_data = encryptAndTokenize($husband_mn);
    $wife_ln_data = encryptAndTokenize($maiden_wife_ln);
    $wife_fn_data = encryptAndTokenize($maiden_wife_fn);
    $wife_mn_data = encryptAndTokenize($maiden_wife_mn);

     // Assign values to registration_date and registrar_name
    $registration_date = date('Y-m-d H:i:s'); // Current date and time
    $registrar_name = "$maiden_wife_ln $maiden_wife_mn $maiden_wife_fn"; // Replace with the actual registrar's name

    // SQL query to insert data into the marriage_tbl table with encrypted fields
    $stmt = $conn->prepare("INSERT INTO marriage_tbl (id_user, husband_ln, husband_fn, husband_mn, maiden_wife_ln, maiden_wife_fn, maiden_wife_mn, husband_ln_enc, husband_ln_tok, husband_fn_enc, husband_fn_tok, husband_mn_enc, husband_mn_tok, maiden_wife_ln_enc, maiden_wife_ln_tok, maiden_wife_fn_enc, maiden_wife_fn_tok, maiden_wife_mn_enc, maiden_wife_mn_tok, marriage_date, place_of_marriage, purpose_of_request, type_request, document_type, status_request) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("issssssssssssssssssssssss", 
        $id_user, 
        $husband_ln, $husband_fn, $husband_mn, $maiden_wife_ln, $maiden_wife_fn, $maiden_wife_mn, // Keep original for backward compatibility
        $husband_ln_data['encrypted'], $husband_ln_data['token'],
        $husband_fn_data['encrypted'], $husband_fn_data['token'],
        $husband_mn_data['encrypted'], $husband_mn_data['token'],
        $wife_ln_data['encrypted'], $wife_ln_data['token'],
        $wife_fn_data['encrypted'], $wife_fn_data['token'],
        $wife_mn_data['encrypted'], $wife_mn_data['token'],
        $marriage_date, $place_of_marriage, $purpose_of_request, $type_request, $document_type, $status_request);

    if ($stmt->execute()) {
        // Insert data into civ_record table
        $id_marriage = $stmt->insert_id; // Get the ID of the inserted record

        // SQL query to insert data into the civ_record table
        // Use prepared statements to insert data into civ_record
        $civRecordSql = "INSERT INTO civ_record (id_marriage, registration_date, registrar_name, type_request) VALUES (?, ?, ?, ?)";
        $civRecordStmt = $conn->prepare($civRecordSql);
        $civRecordStmt->bind_param("isss", $id_marriage, $registration_date, $registrar_name, $type_request);
    
       // Execute the query
            if ($civRecordStmt->execute()) {
            // Display success message for both insertions
            echo '<div class="alert-popup" id="success-alert">';
            echo 'Request Successfully';
            echo '</div>';
            echo '<script>
                    var successAlert = document.getElementById("success-alert");
                    successAlert.style.display = "block";
                    setTimeout(function(){ successAlert.style.display = "none"; }, 3000);
                  </script>';
        } else {
            // Display error message for civ_record insertion
            echo '<div class="alert alert-danger mt-3" role="alert">';
            echo 'Error: ' . $civRecordSql . '<br>' . $conn->error;
            echo '</div>';
        }
    } else {
        // Display error message for birthceno_tbl insertion
        echo '<div class="alert alert-danger mt-3" role="alert">';
        echo 'Error: ' . $sql . '<br>' . $conn->error;
        echo '</div>';
        }

         // Now, insert a record into the reqtracking_tbl
    $reqTrackingSql = "INSERT INTO reqtracking_tbl (type_request, registration_date, registrar_name, user_id, status) VALUES ('$type_request', '$registration_date', '$registrar_name', '$id_user', 'Pending')";

    
    if ($conn->query($reqTrackingSql) === TRUE) {
        // Display success message for reqtracking_tbl insertion
        echo '<div class="alert-popup" id="reqtracking-success-alert">';
        echo 'Request Successfully Tracked';
        echo '</div>';
        echo '<script>
                var reqtrackingSuccessAlert = document.getElementById("reqtracking-success-alert");
                reqtrackingSuccessAlert.style.display = "block";
                setTimeout(function(){ reqtrackingSuccessAlert.style display = "none"; }, 3000);
              </script>';
    } else {
        // Display error message for reqtracking_tbl insertion
        echo '<div class="alert alert-danger mt-3" role="alert">';
        echo 'Error: ' . $reqTrackingSql . '<br>' . $conn->error;
        echo '</div>';
    }
}

// Database connection will be closed automatically by shutdown function
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="icon" href="images/civ.png" type="images/png">


   
    
    <!----======== CSS ======== -->
    <link rel="stylesheet" href="marriage.css">
     
    <!----===== Iconscout CSS ===== -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

   <title>Marriage Certificate Registration Form</title>
</head>
<body>

     <style>
    /* Custom CSS for the pop-up alert */
    .alert-popup {
        position: fixed;
        bottom: 10px;
        right: 10px;
        z-index: 9999;
        padding: 15px;
        border-radius: 5px;
        background-color: #28a745; /* Green background color for success */
        color: #fff; /* White text color */
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        display: none; /* Initially hidden */
    }

    #type_request {
        background-color: #f0f0f0; /* Change background color */
        cursor: not-allowed; /* Change cursor style to indicate non-editable */
        /* Add more styles as needed */
    }

    .mcro-logo {
        max-width: 100px; /* Adjust the max-width as needed */
        height: auto; /* Maintain aspect ratio */
        margin-right: 10px; /* Adjust the margin as needed */
    }

    header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    /* Mobile Responsive Improvements */
    .container {
        padding: 10px;
        max-width: 100%;
    }

    .form-control {
        font-size: 16px; /* Prevent zoom on iOS */
        padding: 12px 15px;
        border-radius: 8px;
        border: 1px solid #ddd;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .input-field {
        margin-bottom: 15px;
    }

    .input-field label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
        font-size: 14px;
    }

    /* Mobile Responsive Adjustments */
    @media (max-width: 768px) {
        .container {
            padding: 5px;
        }
        
        .mcro-logo {
            max-width: 80px;
        }
        
        header {
            flex-direction: column;
            text-align: center;
            margin-bottom: 15px;
        }
    }

    /* Extra small devices (phones, 576px and down) */
    @media (max-width: 576px) {
        .form-control {
            font-size: 16px;
            padding: 8px 10px;
        }
        
        .input-field label {
            font-size: 12px;
        }
        
        .mcro-logo {
            max-width: 60px;
        }
        
        header {
            font-size: 14px;
        }
    }
    
   </style>

    <div class="container">
         <header>
            <img src="images/civreg.jpg" alt="" class="mcro-logo">
        Fill up the Request Form
         </header>

        <form method="POST" action="">
            <div class="form first">
                <div class="details personal">

                    <div class="fields">
                        <div class="input-field">
                            <label>Husband Last Name</label>
                            <input type="text" name="husband_ln" class="form-control" value="<?php echo $u_ln; ?>" required>
                        </div>


                        <div class="input-field">
                            <label>Husband First Name</label>
                            <input type="text" name="husband_fn" class="form-control" value="<?php echo $u_fn; ?>" required>
                        </div>


                        <div class="input-field">
                            <label>Husband Middle Name</label>
                            <input type="text" name="husband_mn" class="form-control" value="<?php echo $u_mn; ?>" required>
                        </div>

                        <div class="input-field">
                            <label>Place of Birth (Country)</label>
                            <input type="text" name="pob_country" class="form-control" required>
                        </div>

                        <div class="input-field">
                            <label>Place of Birth (Province)</label>
                            <input type="text" name="pob_province" class="form-control" value="<?php echo $province; ?>" required>
                        </div>

                        <div class="input-field">
                            <label>Place of Birth (City/Municipality)</label>
                            <input type="text" name="pob_municipality" class="form-control" value="<?php echo $city_municipality; ?>" required>
                        </div>

                         <div class="input-field">
                            <label>Maiden Wife Last Name</label>
                            <input type="text" name="maiden_wife_ln" class="form-control">
                        </div>

                         <div class="input-field">
                            <label>Maiden Wife First Name</label>
                            <input type="text" name="maiden_wife_fn" class="form-control">
                        </div>

                         <div class="input-field">
                            <label>Maiden Wife Middle Name</label>
                            <input type="text" name="maiden_wife_mn" class="form-control">
                        </div>

                         <div class="input-field">
                            <label>Date of Birth</label>
                            <input type="date" id="marriage_date" name="marriage_date" class="form-control" required onchange="validateDate(this)">
                        </div>

                         <div class="input-field">
                            <label>Place of Marriage</label>
                           <input type="text" name="place_of_marriage" class="form-control" required>
                        </div>

                         <div class="input-field">
                                <label for="purpose_of_request">Purpose of Request</label>
                                <select name="purpose_of_request" id="purpose_of_request" class="form-control" required>
                                    <option value="" disabled selected>Select a purpose</option>
                                    <option value="Registration">Registration</option>
                                    <option value="Credentials Update">Credentials Update</option>
                                    <option value="Record Keeping">Record Keeping</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="input-field">
                                <label for="document_type">Document Type</label>
                                <select name="document_type" id="document_type" class="form-control" required>
                                    <option value="PSA" selected>PSA Document</option>
                                    <option value="LCRO" <?php echo ($document_eligibility === 'LCRO + PSA') ? '' : 'disabled'; ?>>LCRO Document (Botolan Residents Only)</option>
                                </select>
                                <small class="form-text text-muted">
                                    <?php if ($document_eligibility === 'LCRO + PSA'): ?>
                                        <span class="text-success">✓ You are eligible for both PSA and LCRO documents</span>
                                    <?php elseif ($document_eligibility === 'PSA Only'): ?>
                                        <span class="text-info">ℹ You can only request PSA documents</span>
                                    <?php else: ?>
                                        <span class="text-warning">⚠ Please complete your profile to see document options</span>
                                    <?php endif; ?>
                                </small>
                            </div>

                        <div class="input-field">
                            <label>Type of Request</label>
                            <input type="text" id="type_request" name="type_request" value="<?php echo $typeRequest; ?>" required readonly>
                        </div> 

                    </div>
                </div>

                <input type="hidden" name="id_user" value="<?php echo $_SESSION['id_user']; ?>">
                        
                        <div class="buttons">
                            <button class="submitBtn">
                                <span class="btnText">Submit</span>
                            <i class="uil uil-navigator"></i>
                            </button>

                        <button class="backBtn" style="background-color: #1164f2" id="backButton">
                            <span class="btnText">Back</span>
                         <i class="uil-arrow-right"></i>
                        </button>
                    </div>
                </div> 
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Function to show SweetAlert confirmation modal
    function showConfirmationModal() {
        Swal.fire({
            title: "Are you sure You want to submit it?",
            text: "Please review your information before submitting.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, submit!"
        }).then((result) => {
            if (result.isConfirmed) {
                // Check if the form is empty before submitting
                var formIsValid = validateForm();
                if (formIsValid) {
                    // If the form is valid, submit it
                    document.querySelector("form").submit();
                } else {
                    // If the form is not valid, show an error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please fill in all the required fields before submitting.',
                    });
                }
            }
        });
    }

    // Function to validate the form
    function validateForm() {
        // You can customize this function based on your form structure
        var requiredFields = document.querySelectorAll('input[required], select[required]');
        var formIsValid = true;

        requiredFields.forEach(function (field) {
            if (field.value.trim() === '') {
                formIsValid = false;
                return;
            }
        });

        return formIsValid;
    }

    // Attach an event listener to the Submit button
    document.querySelector('.submitBtn').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default form submission
        showConfirmationModal(); // Show SweetAlert confirmation modal
    });

    // Document eligibility check
    document.addEventListener('DOMContentLoaded', function() {
        const documentTypeSelect = document.getElementById('document_type');
        const lcroOption = documentTypeSelect.querySelector('option[value="LCRO"]');
        
        // Check if LCRO option is disabled
        if (lcroOption && lcroOption.disabled) {
            // Add visual indication that LCRO is not available
            lcroOption.style.color = '#999';
            lcroOption.style.backgroundColor = '#f5f5f5';
        }
        
        // Add change event listener to document type
        documentTypeSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            const lcroOption = this.querySelector('option[value="LCRO"]');
            
            if (selectedValue === 'LCRO' && lcroOption && lcroOption.disabled) {
                // Show warning if user tries to select disabled LCRO
                Swal.fire({
                    icon: 'warning',
                    title: 'LCRO Not Available',
                    text: 'You are not eligible for LCRO documents. Only PSA documents are available for your location.',
                    confirmButtonText: 'OK'
                });
                // Reset to PSA
                this.value = 'PSA';
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Select the input fields by their name attribute
        var textOnlyFields = document.querySelectorAll('input[type="text"]');
        
        // Add event listener to each text input field
        textOnlyFields.forEach(function (field) {
            field.addEventListener('keypress', function (event) {
                // Check if the pressed key is a number
                if (event.key >= '0' && event.key <= '9') {
                    // Prevent the input if it's a number
                    event.preventDefault();
                }
            });
        });
    });
</script>

<script src="birthform.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // JavaScript function to redirect the user to the dashboard
    function redirectToDashboard() {
        window.location.href = 'user_dashboard.php';
    }

    // Attach an event listener to the "Back" button
    document.getElementById('backButton').addEventListener('click', redirectToDashboard);
</script>

<script>
    function validateDate(inputField) {
        // Get the current date
        var currentDate = new Date();

        // Get the selected date from the input field
        var selectedDate = new Date(inputField.value);

        // Check if the selected date is in the future or invalid
        if (isNaN(selectedDate) || selectedDate > currentDate) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date',
                text: 'Please select a valid date that is not ahead of the current date.',
                confirmButtonText: 'OK'
            });
            inputField.value = ''; // Clear the input field
        } else {
            // Get the year of the selected date
            var selectedYear = selectedDate.getFullYear();

            // Get the current year
            var currentYear = currentDate.getFullYear();

            // Compare the selected year with the current year
            if (selectedYear > currentYear) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Please select a date in the past.',
                    confirmButtonText: 'OK'
                });
                inputField.value = ''; // Clear the input field
            }
        }
    }
</script>

    <!-- Add this code after the <script src="script.js"></script> line -->

</body>
</html>
