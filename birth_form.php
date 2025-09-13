<?php

session_start();

$u_ln = '';
$u_fn = '';
$u_mn = '';
$username = '';
$email = '';
$contact_no = '';
$house_no = '';
$street_brgy = '';


if (isset($_GET['type_request'])) {
    $typeRequest = $_GET['type_request'];
} else {
    // Default value if the parameter is not set
    $typeRequest = "";
}


// Check if the user is logged in or registered
if (!isset($_SESSION['name'])) {
    // Redirect to login page or handle authentication
    header("Location: login.php");
    exit;
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

    // Unset or clear the session data to avoid pre-filling the form on subsequent visits
    unset($_SESSION['user_data']);
}


// Load database connection and encryption functions
require_once __DIR__ . '/db.php';

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the form
    $lastname = $_POST["lastname"];
    $firstname = $_POST["firstname"];
    $middlename = $_POST["middlename"];
    $pob_country = $_POST["pob_country"];
    $pob_province = $_POST["pob_province"];
    $pob_municipality = $_POST["pob_municipality"];
    $dob = $_POST["dob"];
    $sex = $_POST["sex"];
    $fath_ln = $_POST["fath_ln"];
    $fath_fn = $_POST["fath_fn"];
    $fath_mn = $_POST["fath_mn"];
    $moth_maiden_ln = $_POST["moth_maiden_ln"];
    $moth_maiden_fn = $_POST["moth_maiden_fn"];
    $moth_maiden_mn = $_POST["moth_maiden_mn"];
    $relationship = $_POST["relationship"];
    $purpose_of_request = $_POST["purpose_of_request"];
    $type_request = $_POST["type_request"];
    $status_request = 'PENDING';
    $id_user = $_POST['id_user']; // Retrieve id_user from the form data


    // Encrypt PII fields
    $lastname_data = encryptAndTokenize($lastname);
    $firstname_data = encryptAndTokenize($firstname);
    $middlename_data = encryptAndTokenize($middlename);
    $dob_data = encryptAndTokenize($dob);
    $sex_data = encryptAndTokenize($sex);

    // Assign values to registration_date and registrar_name
    $registration_date = date('Y-m-d H:i:s'); // Current date and time
    $registrar_name = "$firstname $middlename $lastname"; // Replace with the actual registrar's name

    // SQL query to insert data into the birthceno_tbl table with encrypted fields
    $stmt = $conn->prepare("INSERT INTO birthceno_tbl (id_user, lastname, lastname_enc, lastname_tok, firstname, firstname_enc, firstname_tok, middlename, middlename_enc, middlename_tok, pob_country, pob_province, pob_municipality, dob, dob_enc, dob_tok, sex, sex_enc, sex_tok, fath_ln, fath_fn, fath_mn, moth_maiden_ln, moth_maiden_fn, moth_maiden_mn, relationship, purpose_of_request, type_request, status_request) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters to the placeholders
    $stmt->bind_param("issssssssssssssssssssssssssss", 
        $id_user, 
        $lastname, // Keep original for backward compatibility
        $lastname_data['encrypted'], $lastname_data['token'],
        $firstname, // Keep original for backward compatibility
        $firstname_data['encrypted'], $firstname_data['token'],
        $middlename, // Keep original for backward compatibility
        $middlename_data['encrypted'], $middlename_data['token'],
        $pob_country, $pob_province, $pob_municipality, 
        $dob, // Keep original for backward compatibility
        $dob_data['encrypted'], $dob_data['token'],
        $sex, // Keep original for backward compatibility
        $sex_data['encrypted'], $sex_data['token'],
        $fath_ln, $fath_fn, $fath_mn, $moth_maiden_ln, $moth_maiden_fn, $moth_maiden_mn, 
        $relationship, $purpose_of_request, $type_request, $status_request);
    
    if ($stmt->execute()) {
        // Insert data into civ_record table
        $id_birth_ceno = $stmt->insert_id; // Get the ID of the inserted record

        $stmt->close();

        // SQL query to insert data into the civ_record table
        $civRecordSql = "INSERT INTO civ_record (id_birth_ceno, registration_date, registrar_name, type_request) VALUES ('$id_birth_ceno', '$registration_date', '$registrar_name', '$type_request')";

        // Execute the civ_record query
        if ($conn->query($civRecordSql) === TRUE) {
            // Display success message for both insertions
            echo '<div class="alert-popup" id="success-alert">';
            echo 'Request Successfully';
            echo '</div>';
            echo '<script>
                    var successAlert = document.getElementById("success-alert");
                    successAlert.style.display = "block";
                    setTimeout(function(){ successAlert.style.display = "none"; redirectToDashboard(); }, 3000);
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
    <link rel="stylesheet" href="birth.css">
    
     
    <!----===== Iconscout CSS ===== -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

    <!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>



   <title>Birth Certificate Registration Form</title>

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
            <img src="images/civ.png" alt="MCRO Logo" class="mcro-logo">
            <span>Fill up the Request Form</span>
        </header>

        <form method="POST" action="" class="form-wizard" id="multiStepForm">
            <!-- Success State -->
            <div class="completed" hidden>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <h3>Request Submitted Successfully!</h3>
                <p>Your birth certificate request has been submitted and is being processed.</p>
            </div>

            <h1>Birth Certificate Request</h1>

            <!-- Progress Container -->
            <div class="progress-container">
                <div class="progress"></div>
                <ol>
                    <li class="current">Personal</li>
                    <li>Parents</li>
                    <li>Details</li>
                </ol>
            </div>

            <!-- Steps Container -->
            <div class="steps-container">
                <!-- Step 1: Personal Information -->
                <div class="step">
                    <h3>Personal Information</h3>
                    <div class="fields">
                        <div class="input-field">
                            <label>Last Name</label>
                            <input type="text" name="lastname" class="form-control" value="<?php echo $u_ln; ?>" required  data-toggle="popover" data-trigger="focus" data-placement="top" title="Instructions">
                        </div>


                        <div class="input-field exclude-popover">
                            <label>First Name</label>
                            <input type="text" name="firstname" class="form-control" value="<?php echo $u_fn; ?>" required>
                        </div>


                        <div class="input-field exclude-popover">
                            <label>Middle Name</label>
                            <input type="text" name="middlename" class="form-control" value="<?php echo $u_mn; ?>" required>
                        </div>

                        <div class="input-field exclude-popover">
                            <label>Place of Birth (Country)</label>
                            <input type="text" name="pob_country" class="form-control" required>
                        </div>

                       <div class="input-field">
                                <label>Place of Birth (Province)</label>
                                <select id="placeOfBirth" name="pob_province" class="form-control" required>
                                    <option value="">Select Province</option>
                                    <!-- Options will be dynamically populated using JavaScript -->
                                    <?php 
                                        // Establish a database connection (replace these values with your database credentials)
                                      require_once __DIR__ . '/db.php';

                                        // Check the connection
                                        if ($conn->connect_error) {
                                            die("Connection failed: " . $conn->connect_error);
                                        }

                                        // Fetch data from the 'refprovince' table and populate the dropdown
                                        $sql = "SELECT provDesc, provCode FROM refprovince ORDER BY provDesc";
                                        $result = $conn->query($sql);

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<option value='" . $row['provCode'] . "'>" . $row['provDesc'] . "</option>";
                                            }
                                        }

                                        // Database connection will be closed automatically
                                    ?>
                                </select> 
                            </div>

                          <div class="input-field exclude-popover">
                            <label>Place of Birth(City/Municipality)</label>
                            <input type="text" name="pob_municipality" class="form-control">
                        </div>


                    </div>
                </div>

                <!-- Step 2: Parents Information -->
                <div class="step">
                    <h3>Parents Information</h3>
                    <div class="fields">
                        <div class="input-field">
                            <label>Father's Last Name</label>
                            <input type="text" name="fath_ln" class="form-control" required data-toggle="popover" data-trigger="focus" data-placement="top" title="Instructions">
                        </div>

                         <div class="input-field exclude-popover">
                            <label>Father's First Name</label>
                            <input type="text" name="fath_fn" class="form-control" required>
                        </div>

                         <div class="input-field exclude-popover">
                            <label>Father's Middle Name</label>
                            <input type="text" name="fath_mn" class="form-control" required>
                        </div>

                         <div class="input-field">
                            <label>Mother's Maiden Last Name</label>
                            <input type="text" name="moth_maiden_ln" class="form-control" required data-toggle="popover" data-trigger="focus" data-placement="top" title="Instructions">
                        </div>


                        <div class="input-field exclude-popover">
                            <label>Mother's Maiden First Name</label>
                            <input type="text" name="moth_maiden_fn" class="form-control" required>
                        </div>


                         <div class="input-field exclude-popover">
                            <label>Mother's Maiden Middle Name</label>
                              <input type="text" name="moth_maiden_mn" class="form-control" required>
                        </div>  

                    </div>
                </div>

                <!-- Step 3: Request Details -->
                <div class="step">
                    <h3>Request Details</h3>
                    <div class="fields">
                        <div class="input-field">
                            <label>Sex</label>
                            <select name="sex" class="form-control" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            </select>
                        </div>

                         <div class="input-field second-dob exclude-popover">
                            <label>Date of Birth</label>
                            <input type="date" id="dob" name="dob" class="form-control" required onchange="validateDate(this)">
                        </div>

                        <div class="input-field">
                            <label>Relationship to the Document Owner</label>
                             <select name="relationship" class="form-control" required>
                                <option value="">Select Relationship</option>
                                <option value="Registrant">Registrant</option>
                                <option value="Parent">Parent</option>
                                <option value="Sibling">Sibling</option>
                                <!-- Add other relevant relationship options -->
                            </select>
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
                            
                        <div class="input-field second-dob exclude-popover">
                            <label>Type of Request</label>
                            <input type="text" id="type_request" name="type_request" value="<?php echo $typeRequest; ?>" required readonly>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="controls">
                <button type="button" class="prev-btn">Previous</button>
                <button type="button" class="next-btn">Next</button>
                <button type="submit" class="submit-btn">Submit Request</button>
            </div>

            <input type="hidden" name="id_user" value="<?php echo $_SESSION['id_user']; ?>">
        </form>
    </div>

    <!-- Your existing HTML form -->
<!-- ... -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Multi-Step Form Functionality
    const form = document.querySelector(".form-wizard");
    const progress = form.querySelector(".progress");
    const stepsContainer = form.querySelector(".steps-container");
    const steps = form.querySelectorAll(".step");
    const stepIndicators = form.querySelectorAll(".progress-container li");
    const prevButton = form.querySelector(".prev-btn");
    const nextButton = form.querySelector(".next-btn");
    const submitButton = form.querySelector(".submit-btn");

    // Set CSS variable for number of steps
    document.documentElement.style.setProperty("--steps", stepIndicators.length);

    let currentStep = 0;

    const updateProgress = () => {
        let width = currentStep / (steps.length - 1);
        progress.style.transform = `scaleX(${width})`;

        // Update container height
        stepsContainer.style.height = steps[currentStep].offsetHeight + "px";

        // Update step indicators
        stepIndicators.forEach((indicator, index) => {
            indicator.classList.toggle("current", currentStep === index);
            indicator.classList.toggle("done", currentStep > index);
        });

        // Update steps
        steps.forEach((step, index) => {
            const percentage = document.documentElement.dir === "rtl" ? 100 : -100;
            step.style.transform = `translateX(${currentStep * percentage}%)`;
            step.classList.toggle("current", currentStep === index);
        });

        updateButtons();
    };

    const updateButtons = () => {
        prevButton.hidden = currentStep === 0;
        nextButton.hidden = currentStep >= steps.length - 1;
        submitButton.hidden = !nextButton.hidden;
    };

    const isValidStep = () => {
        const fields = steps[currentStep].querySelectorAll("input, select, textarea");
        return [...fields].every((field) => field.reportValidity());
    };

    // Event listeners
    const inputs = form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) =>
        input.addEventListener("focus", (e) => {
            const focusedElement = e.target;

            // Get the step where the focused element belongs
            const focusedStep = [...steps].findIndex((step) =>
                step.contains(focusedElement)
            );

            if (focusedStep !== -1 && focusedStep !== currentStep) {
                if (!isValidStep()) return;

                currentStep = focusedStep;
                updateProgress();
            }

            stepsContainer.scrollTop = 0;
            stepsContainer.scrollLeft = 0;
        })
    );

    // Form submission
    form.addEventListener("submit", (e) => {
        e.preventDefault();

        if (!form.checkValidity()) return;

        // Show loading state
        submitButton.disabled = true;
        submitButton.textContent = "Submitting...";

        // Simulate form submission
        setTimeout(() => {
            // Show success state
            form.querySelector(".completed").hidden = false;
            
            // Actually submit the form after showing success
            setTimeout(() => {
                form.submit();
            }, 2000);
        }, 1500);
    });

    // Previous button
    prevButton.addEventListener("click", (e) => {
        e.preventDefault();

        if (currentStep > 0) {
            currentStep--;
            updateProgress();
        }
    });

    // Next button
    nextButton.addEventListener("click", (e) => {
        e.preventDefault();

        if (!isValidStep()) {
            // Show validation error
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Information',
                text: 'Please fill in all required fields before proceeding to the next step.',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (currentStep < steps.length - 1) {
            currentStep++;
            updateProgress();
        }
    });

    // Initialize form
    updateProgress();
</script>

<script src="birthform.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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

<script>
    // JavaScript function to redirect the user to the dashboard
    function redirectToDashboard() {
        window.location.href = 'user_dashboard.php';
    }

    // Attach an event listener to the "Back" button
    document.getElementById('backButton').addEventListener('click', redirectToDashboard);
</script>

<script>
    $(document).ready(function () {
        // Initialize Bootstrap popover for common input fields
        $('.input-field:not(.exclude-popover) input').popover({
            trigger: 'manual',
            placement: 'top',
            html: true,
            content: function () {
                return '<div class="popover-content">If the Last Name starts with "DE", "DEL", "DE LA", or "DE LOS", enter these in the Last Name. Use "DE LA" or "DE LOS" (with space) instead of "DELA" or "DELOS".</div>';
            }
        });

        // Show popover when a common input field is focused
        $('.input-field:not(.exclude-popover) input').focus(function () {
            $(this).popover('show');
        });

        // Hide popover when a common input field is blurred or when the user starts typing
        $('.input-field:not(.exclude-popover) input').on('blur input', function () {
            var inputField = $(this);
            setTimeout(function () {
                inputField.popover('hide');
            }, 200); // Adjust the delay time (in milliseconds) as needed
        });
    });
</script>

    <!-- Add this code after the <script src="script.js"></script> line -->

</body>
</html>