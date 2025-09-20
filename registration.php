<!DOCTYPE html>
<html lang="en">

<head>
  <title>Registration</title>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="images/lcrobot.png" type="images/png">


  <!-- Bootstrap CSS v5.2.1 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

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

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }

    .form-control-lg {
        border-radius: 8px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control-lg:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .form-select-lg {
        border-radius: 8px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-select-lg:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    /* Mobile Responsive Adjustments */
    @media (max-width: 768px) {
        .form-control-lg, .form-select-lg {
            font-size: 16px; /* Prevent zoom on iOS */
            padding: 12px 15px;
            margin-bottom: 12px;
            border-radius: 8px;
        }
        
        .form-label {
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .card {
            margin: 5px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-body {
            padding: 20px 15px;
        }
        
        .btn-lg {
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 25px;
            min-height: 48px; /* Better touch target */
        }
        
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .h2 {
            font-size: 1.75rem;
        }
        
        .text-muted {
            font-size: 14px;
        }
    }

    /* Extra small devices (phones, 576px and down) */
    @media (max-width: 576px) {
        .form-control-lg, .form-select-lg {
            font-size: 16px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }
        
        .form-label {
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .card-body {
            padding: 15px 10px;
        }
        
        .btn-lg {
            padding: 10px 15px;
            font-size: 15px;
        }
        
        .h2 {
            font-size: 1.5rem;
        }
    }

    .match {
        color: green;
        font-size: 12px;
    }

    .no-match {
        color: red;
        font-size: 12px;
    }

    /* Password strength indicator */
    #password-strength {
        font-size: 12px;
        margin-top: 5px;
    }

    /* Warning messages */
    #emailWarning, #contactNumberWarning {
        font-size: 12px;
        margin-top: 5px;
    }

   </style>

<body>

  <?php
    session_start();

 require_once __DIR__ . '/db.php';
    
 date_default_timezone_set('Asia/Manila');
    
    // When form submitted, insert values into the database.
    if (isset($_REQUEST['username'])) {

       // Capture the email address from the registration form
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        // removes backslashes
        $_SESSION['user_email'] = $email;

        $username = stripslashes($_REQUEST['username']);
        $u_ln = $_POST['u_ln'];
        $u_fn = $_POST['u_fn'];
        $u_mn = !empty($_POST['u_mn']) ? trim($_POST['u_mn']) : null;
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $contact_no = $_POST['contact_no'];
        $create_datetime = date("F d, Y h:ia");
        $house_no = $_POST['house_no'];
        $street_brgy = $_POST['street_brgy'];
        $city_municipality = $_POST['city_municipality'];
        $province = $_POST['province'];
        $birthplace_municipality = $_POST['birthplace_municipality'];
        $birthplace_province = $_POST['birthplace_province'];
        
        


        $_SESSION['user_data'] = [
      'u_ln' => $u_ln,
      'u_fn' => $u_fn,
      'u_mn' => $u_mn,
      'username' => $username,
      'email' => $email,
      'contact_no' => $contact_no,
      'house_no' => $house_no,
      'street_brgy' => $street_brgy,
      'city_municipality' => $city_municipality,
      'province' => $province,
      'birthplace_municipality' => $birthplace_municipality,
      'birthplace_province' => $birthplace_province,
      'residence_municipality' => $city_municipality,
      'residence_province' => $province,
];

       
        //escapes special characters in a string
        // $u_ln = mysqli_real_escape_string($con, $u_ln);
        $u_ln = mysqli_real_escape_string($conn, $u_ln);
        $u_fn = mysqli_real_escape_string($conn, $u_fn);
        $u_mn = $u_mn ? mysqli_real_escape_string($conn, $u_mn) : '';
        $username = mysqli_real_escape_string($conn, $username);
        $email    = stripslashes($_REQUEST['email']);
        $email    = mysqli_real_escape_string($conn, $email);
        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($conn, $password);
        $contact_no = mysqli_real_escape_string($conn, $contact_no);
        $create_datetime = date("F d, Y h:ia");
        $house_no = mysqli_real_escape_string($conn, $house_no);
        $street_brgy = mysqli_real_escape_string($conn, $street_brgy);
        $city_municipality = mysqli_real_escape_string($conn, $city_municipality);
        $province = mysqli_real_escape_string($conn, $province);
        $birthplace_municipality = mysqli_real_escape_string($conn, $birthplace_municipality);
        $birthplace_province = mysqli_real_escape_string($conn, $birthplace_province);
        // $usertype    = stripslashes($_REQUEST['usertype']);
        // Assuming you have a way to determine if the user is an admin, e.g., through an additional form field or some other method.
      $is_admin = false; // Change this to determine if the user is an admin or not.

      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $query = "INSERT INTO `users` (u_ln, u_fn, u_mn, username, email, password, contact_no, create_datetime, house_no, street_brgy, city_municipality, province, birthplace_municipality, birthplace_province, residence_municipality, residence_province, usertype)
      VALUES ('$u_ln', '$u_fn', '$u_mn', '$username', '$email', '$hashed_password', '$contact_no', '$create_datetime', '$house_no', '$street_brgy', '$city_municipality', '$province', '$birthplace_municipality', '$birthplace_province', '$city_municipality', '$province', '" . ($is_admin ? 'admin' : 'user') . "')";

        // Debug: Log the values being inserted
        error_log("INSERT VALUES - Birthplace Province: " . $birthplace_province);
        error_log("INSERT VALUES - Birthplace Municipality: " . $birthplace_municipality);
        error_log("INSERT VALUES - Residence Municipality: " . $city_municipality);
        error_log("INSERT VALUES - Residence Province: " . $province);
        
        $result   = mysqli_query($conn, $query);

        if ($result) {
            error_log("INSERT SUCCESS - User registered with birthplace data");
          echo '<script>
                  Swal.fire({
                    title: "Good job!",
                    text: "You are registered successfully",
                    icon: "success",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                      toast.addEventListener("mouseenter", Swal.stopTimer);
                      toast.addEventListener("mouseleave", Swal.resumeTimer);
                    },
                  });
  
                  setTimeout(function () {
                    window.location.href = "login.php"; // Redirect to login page after 3 seconds
                  }, 3000);
                </script>';
          exit;
        } else {
            $error_message = mysqli_error($conn);
            error_log("INSERT FAILED: " . $error_message);
            echo '<script>
                  Swal.fire({
                      title: "Registration Failed",
                      text: "There was an error creating your account. Please try again.",
                      icon: "error",
                      confirmButtonText: "Try Again"
                  }).then((result) => {
                      if (result.isConfirmed) {
                          window.location.href = "registration.php";
                      }
                  });
                </script>';
          exit;
      }
  } else {
?>



  <section class="vh-100" style="background-color: #eee; min-height: 100vh;">
    <div class="container-fluid h-100 py-3">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-lg-10 col-xl-8">
          <div class="card text-black shadow-lg" style="border-radius: 20px; border: none;">
            <div class="card-body p-3 p-md-5">
              <div class="row justify-content-center">
                <div class="col-12 text-center mb-4">
                  <h2 class="fw-bold mb-0 text-danger">Register Here</h2>
                  <p class="text-muted mt-2">Create your account to access Civil Registry Services</p>
                </div>
                <div class="col-12 col-lg-10">
                <form id="registration-form" class="mx-1 mx-md-4" action="" method="post">
                <!-- Name Fields Row -->
                <div class="row">
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="lastName">Last Name</label>
                      <input type="text" id="lastName" class="form-control form-control-lg" name="u_ln" autocomplete="off" placeholder="Last Name" required />
                    </div>
                  </div>
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="firstName">First Name</label>
                      <input type="text" id="firstName" class="form-control form-control-lg" name="u_fn" autocomplete="off" placeholder="First Name" required />
                    </div>
                  </div>
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="middleName">Middle Name <span class="text-muted">(Optional)</span></label>
                      <input type="text" id="middleName" class="form-control form-control-lg" name="u_mn" autocomplete="off" placeholder="Middle Name (Optional)" />
                    </div>
                  </div>
                </div>

                <!-- Account Credentials Row -->
                <div class="row">
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="username">Username</label>
                      <input type="text" id="username" class="form-control form-control-lg" name="username" autocomplete="off" placeholder="Username" required />
                    </div>
                  </div>
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="password">Password</label>
                      <input type="password" id="password" class="form-control form-control-lg" name="password" autocomplete="off" placeholder="Password" required />
                      <div id="password-strength"></div>
                    </div>
                  </div>
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="confirmpassword">Confirm Password</label>
                      <input type="password" id="confirmpassword" class="form-control form-control-lg" name="confirm_password" autocomplete="off" placeholder="Confirm Password" required />
                      <span id="passwordMatch"></span>
                    </div>
                  </div>
                </div>

                <!-- Contact Information Row -->
                <div class="row">
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="email">Email</label>
                      <input type="email" id="email" class="form-control form-control-lg" name="email" autocomplete="off" placeholder="Email" required />
                      <div id="emailWarning" style="color: red;"></div>
                    </div>
                  </div>
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="number">Contact number</label>
                      <input type="text" id="number" class="form-control form-control-lg" name="contact_no" autocomplete="off" placeholder="Contact number" required />
                      <div id="contactNumberWarning" style="color: red;"></div>
                    </div>
                  </div>
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="housenumber">House number/Street Name</label>
                      <input type="text" id="housenumber" class="form-control form-control-lg" name="house_no" autocomplete="off" placeholder="House number/street name" required />
                    </div>
                  </div>
                </div>

                <!-- Current Address Information Row -->
                <div class="row">
                  <div class="col-12">
                    <h5 class="text-primary mb-3"><i class="bi bi-house-door"></i> Current Address (Where you live now)</h5>
                  </div>
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="province">Province</label>
                      <select id="province" class="form-select form-select-lg" name="province" required>
                        <option value="">Select Province</option>
                        <?php 
                          // Use the existing connection from the top of the file
                          if (!isset($conn)) {
                            require_once __DIR__ . '/db.php';
                          }

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
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="city">City/Municipality</label>
                      <select id="city" class="form-select form-select-lg" name="city_municipality" required>
                        <option value="">Select City/Municipality</option>
                        <!-- Add your city/municipality options here -->
                      </select>
                    </div>
                  </div>
                  <div class="col-12 col-md-4">
                    <div class="form-outline">
                      <label class="form-label" for="barangay">Barangay</label>
                      <select id="barangay" class="form-select form-select-lg" name="street_brgy" required>
                        <option value="">Select Barangay</option>
                        <!-- Add your barangay options here -->
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Birthplace Information Row -->
                <div class="row mt-4">
                  <div class="col-12">
                    <h5 class="text-success mb-3"><i class="bi bi-geo-alt"></i> Birthplace Information (Where you were born)</h5>
                    <p class="text-muted small mb-3">This information is required to determine which civil registry documents you can request.</p>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="form-outline">
                      <label class="form-label" for="birthplace_province">Birthplace Province</label>
                      <select id="birthplace_province" class="form-select form-select-lg" name="birthplace_province" required>
                        <option value="">Select Birthplace Province</option>
                        <?php 
                          // Use the existing connection from the top of the file
                          if (!isset($conn)) {
                            require_once __DIR__ . '/db.php';
                          }
                          
                          if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                          }
                          $sql = "SELECT provDesc, provCode FROM refprovince ORDER BY provDesc";
                          $result = $conn->query($sql);
                          if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                              echo "<option value='" . $row['provCode'] . "'>" . $row['provDesc'] . "</option>";
                            }
                          }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="form-outline">
                      <label class="form-label" for="birthplace_city">Birthplace City/Municipality</label>
                      <select id="birthplace_city" class="form-select form-select-lg" name="birthplace_municipality" required>
                        <option value="">Select Birthplace City/Municipality</option>
                        <!-- Birthplace city options will be populated dynamically -->
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Document Type Information -->
                <div class="row mt-4">
                  <div class="col-12">
                    <div class="alert alert-info" role="alert">
                      <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Document Request Policy</h6>
                      <hr>
                      <p class="mb-2"><strong>PSA Documents</strong> (Birth, Marriage, Death, CENOMAR): Available to all users regardless of birthplace.</p>
                      <p class="mb-0"><strong>LCRO Documents</strong> (CTC, Varied Forms): Only available if you were born in Botolan, Zambales or currently live in Botolan, Zambales.</p>
                    </div>
                  </div>
                </div>
                 
                <!-- Submit Button -->
                <div class="row">
                  <div class="col-12">
                    <div class="d-flex justify-content-center mb-3">
                      <button type="submit" name="register" id="registerButton" class="btn btn-primary btn-lg text-light w-100" style="border-radius: 25px; font-weight: 600; padding: 12px 20px;">
                        <i class="bi bi-person-plus me-2"></i>Register
                      </button>
                    </div>
                  </div>
                </div>
                </form>
                
                <!-- Login Link -->
                <div class="row">
                  <div class="col-12">
                    <p class="text-center mb-0">
                      I already have an account 
                      <a href="login.php" class="text-danger fw-bold text-decoration-none">Login</a>
                    </p>
                  </div>
                </div>
              </div>
             
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </section>

  <!-- Bootstrap JavaScript Libraries -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>


  <script>

$(document).ready(function() {
    var formSubmitted = false; // Track whether the form has been submitted
    
    // Email validation
    $('#email').on('input', function() {
        $('#emailWarning').html(''); // Clear the existing error message
        
        if (formSubmitted) {
            // If the form has already been submitted, prevent further submissions
            event.preventDefault();
            return;
        }
        
        var email = $('#email').val();
        
        if (email !== "") {
            $.ajax({
                type: 'POST',
                url: 'check_email.php',
                data: {
                    email: email
                },
                success: function(response) {
                    if (response.trim() === "Email already taken") {
                        $('#emailWarning').html('<i class="bi bi-exclamation-circle-fill" style="color: red;"></i> This email is already taken.');
                        // Disable the submit button 
                        $('#registerButton').prop('disabled', true);
                    } else {
                        // Enable the submit button
                        $('#registerButton').prop('disabled', false);
                    }
                    formSubmitted = false; // Reset the form submission flag
                }
            });
            
            return false; // Prevent the form from submitting immediately
        } else {
            // Enable the submit button if the email is empty
            $('#registerButton').prop('disabled', false);
        }
    });

    // Define a function to fetch and populate the City/Municipality dropdown
    function findCities(selectedProvinceCode) {
        $.ajax({
            type: 'POST',
            url: 'get_cities.php',
            data: { province_code: selectedProvinceCode },
            success: function (data) {
                var cityDropdown = document.getElementById('city');
                cityDropdown.innerHTML = '<option value="">Select City/Municipality</option>';
                var cities = JSON.parse(data);

                if (cities.length > 0) {
                    cities.forEach(function (city) {
                        var option = document.createElement('option');
                        option.value = city.code;
                        option.textContent = city.name;
                        cityDropdown.appendChild(option);
                    });
                }
            }
        });
    }

    // Add an event listener to the Province dropdown to call the function when it changes
    $('#province').on('change', function () {
        var selectedProvinceCode = this.value;
        if (selectedProvinceCode !== '') {
            findCities(selectedProvinceCode);
        } else {
            // Clear the City/Municipality dropdown if no Province is selected
            $('#city').html('<option value="">Select City/Municipality</option>');
            $('#barangay').html('<option value="">Select Barangay</option>');
        }
    });

    // Define a function to fetch and populate the Barangay dropdown
    function findBarangays(selectedCityCode) {
        $.ajax({
            type: 'POST',
            url: 'get_barangay.php',
            data: { city_code: selectedCityCode },
            success: function (data) {
                var barangayDropdown = document.getElementById('barangay');
                barangayDropdown.innerHTML = '<option value="">Select Barangay</option>';
                var barangays = JSON.parse(data);

                if (barangays.length > 0) {
                    barangays.forEach(function (barangay) {
                        var option = document.createElement('option');
                        option.value = barangay;
                        option.textContent = barangay;
                        barangayDropdown.appendChild(option);
                    });
                }
            }
        });
    }

    // Add an event listener to the City/Municipality dropdown to call the function when it changes
    $('#city').on('change', function () {
        var selectedCityCode = this.value;
        if (selectedCityCode !== '') {
            findBarangays(selectedCityCode);
        } else {
            // Clear the Barangay dropdown if no City/Municipality is selected
            $('#barangay').html('<option value="">Select Barangay</option>');
        }
    });

    // Define a function to fetch and populate the Birthplace City/Municipality dropdown
    function findBirthplaceCities(selectedProvinceCode) {
        $.ajax({
            type: 'POST',
            url: 'get_cities.php',
            data: { province_code: selectedProvinceCode },
            success: function (data) {
                var birthplaceCityDropdown = document.getElementById('birthplace_city');
                birthplaceCityDropdown.innerHTML = '<option value="">Select Birthplace City/Municipality</option>';
                var cities = JSON.parse(data);

                if (cities.length > 0) {
                    cities.forEach(function (city) {
                        var option = document.createElement('option');
                        option.value = city.code;
                        option.textContent = city.name;
                        birthplaceCityDropdown.appendChild(option);
                    });
                }
                
            },
            error: function(xhr, status, error) {
                console.error('Error loading birthplace cities:', error);
            }
        });
    }

    // Add an event listener to the Birthplace Province dropdown to call the function when it changes
    $('#birthplace_province').on('change', function () {
        var selectedProvinceCode = this.value;
        if (selectedProvinceCode !== '') {
            findBirthplaceCities(selectedProvinceCode);
        } else {
            // Clear the Birthplace City/Municipality dropdown if no Province is selected
            $('#birthplace_city').html('<option value="">Select Birthplace City/Municipality</option>');
        }
    });

    // Form validation before submission
    $('#registration-form').on('submit', function(e) {
        var birthplaceProvince = $('#birthplace_province').val();
        var birthplaceCity = $('select[name="birthplace_municipality"]').val();
        
        
        if (!birthplaceProvince || !birthplaceCity) {
            e.preventDefault();
            Swal.fire({
                title: 'Missing Birthplace Information',
                text: 'Please select both your birthplace province and city/municipality.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        // Show loading state
        Swal.fire({
            title: 'Registering...',
            text: 'Please wait while we create your account.',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });
});




</script>


<script>
// Function to check password strength
function checkPasswordStrength(password) {
    // Minimum password length
    var minLength = 8;
    
    // Regular expressions for password strength
    var regex = {
        lowerCase: /[a-z]/,
        upperCase: /[A-Z]/,
        numbers: /[0-9]/,
        specialChars: /[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]/
    };
    
    var strength = 0;

    // Check if password meets minimum length requirement
    if (password.length >= minLength) {
        strength++;
    }

    // Check if password contains lowercase, uppercase, numbers, and special characters
    if (regex.lowerCase.test(password)) strength++;
    if (regex.upperCase.test(password)) strength++;
    if (regex.numbers.test(password)) strength++;
    if (regex.specialChars.test(password)) strength++;

    return strength;
}

// Event listener for the password field
document.getElementById('password').addEventListener('input', function() {
    var password = this.value.trim(); // Trim spaces from the password input

    if (password === '') {
        // If password is empty, hide the password strength indicator
        document.getElementById('password-strength').innerHTML = '';
        return; // Exit the function
    }

    var strength = checkPasswordStrength(password);

    var strengthText;
    var indicatorColor;
    switch (strength) {
        case 0:
        case 1:
            strengthText = 'Weak';
            indicatorColor = 'red'; // Weak password indicator color
            break;
        case 2:
        case 3:
            strengthText = 'Medium';
            indicatorColor = 'orange'; // Medium password indicator color
            break;
        case 4:
            strengthText = 'Strong';
            indicatorColor = 'green'; // Strong password indicator color
            break;
        default:
            strengthText = 'Weak';
            indicatorColor = 'red'; // Default color for weak passwords
            break;
    }

    // Update the password strength indicator with color
    document.getElementById('password-strength').innerHTML = '<span style="color: ' + indicatorColor + ';">' + strengthText + '</span>';
});
</script>

<style>
  .match {
    color: green;
  }

  .no-match {
    color: red;
  }
</style>

<script>
  document.getElementById("confirmpassword").addEventListener("input", function() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirmpassword").value;
    var passwordMatch = document.getElementById("passwordMatch");

    if (confirmPassword === '') {
      passwordMatch.innerHTML = "";
    } else if (password !== confirmPassword) {
      passwordMatch.innerHTML = "Password do not match!";
      passwordMatch.classList.remove("match");
      passwordMatch.classList.add("no-match");
    } else {
      passwordMatch.innerHTML = "Password match!";
      passwordMatch.classList.remove("no-match");
      passwordMatch.classList.add("match");
    }
  });
</script>

<script>
  document.getElementById("number").addEventListener("input", function() {
    var contactNumber = this.value;
    var contactNumberWarning = document.getElementById("contactNumberWarning");

    // Remove any non-numeric characters
    contactNumber = contactNumber.replace(/\D/g, '');

    // Check if the contact number is exactly 11 digits
    if (contactNumber.length === 11) {
      contactNumberWarning.innerHTML = "";
    } else {
      contactNumberWarning.innerHTML = "Please enter a valid 11-digit contact number.";
    }

    // Limit the input to numbers only and truncate to 11 digits
    this.value = contactNumber.substring(0, 11);
  });
</script>
<?php
    } 
    
    // Close database connection at the end
    if (isset($conn)) {
        mysqli_close($conn);
    }
?>


</body>

</html>