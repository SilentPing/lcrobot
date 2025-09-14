<?php
session_start();

// Include configuration files
require_once __DIR__ . '/config/mailgun_config.php';
require_once __DIR__ . '/config/email_templates.php';
require_once __DIR__ . '/db.php';

$msg = "";
$success = false;

if (isset($_POST['reset'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "<div class='alert alert-danger'>Please enter a valid email address.</div>";
    } else {
        // Check if user exists
        $user_query = "SELECT id, username, email FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Check rate limiting
            if (!checkResetRateLimit($email)) {
                $msg = "<div class='alert alert-warning'>Too many reset attempts. Please wait 1 hour before trying again.</div>";
                logResetAttempt($email, false);
            } else {
                // Generate secure reset token
                $reset_token = generateResetToken();
                $expires_at = date('Y-m-d H:i:s', time() + RESET_TOKEN_EXPIRY);
                
                // Store reset token in database
                $token_query = "INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at), used = 0";
                $stmt = mysqli_prepare($conn, $token_query);
                mysqli_stmt_bind_param($stmt, 'sss', $email, $reset_token, $expires_at);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Create reset link
                    $reset_link = SITE_URL . "/change_pass.php?token=" . $reset_token;
                    
                    // Generate email content
                    $user_name = $user['username'];
                    $expiry_time = "1 hour";
                    $html_body = getPasswordResetTemplate($user_name, $reset_link, $expiry_time);
                    $text_body = getPasswordResetTextTemplate($user_name, $reset_link, $expiry_time);
                    
                    // Send email via Mailgun
                    $email_result = sendMailgunEmail(
                        $email,
                        'Password Reset Request - Botolan Civil Registry',
                        $html_body,
                        $text_body
                    );
                    
                    if ($email_result['success']) {
                        $msg = "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Password reset link has been sent to your email address. Please check your inbox and spam folder.</div>";
                        $success = true;
                        logResetAttempt($email, true);
                        logEmailAttempt($email, 'password_reset', true);
                    } else {
                        $msg = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Failed to send email. Please try again later or contact support.</div>";
                        logResetAttempt($email, false);
                        logEmailAttempt($email, 'password_reset', false, $email_result['error']);
                    }
                } else {
                    $msg = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Database error. Please try again later.</div>";
                    logResetAttempt($email, false);
                }
            }
        } else {
            $msg = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> No account found with this email address.</div>";
            logResetAttempt($email, false);
        }
    }
}


?>
<!doctype html>
<html lang="en">

<head>
  <title>Forgot Password</title>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="images/civ.png" type="images/png">

  <!-- Bootstrap CSS v5.2.1 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

  <section class="vh-100" style="background-color: #eee;">
    <div class="container h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-lg-12 col-xl-11">
          <div class="card text-black" style="border-radius: 25px;">
            <div class="card-body p-md-2">
              <div class="row justify-content-center">
              <p class="text-center h1 fw-bold mb-4 mx-1 mx-md-3 mt-3">Reset Password Here</p>
                <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">

                  

                  <form id="resetForm" class="mx-1 mx-md-4" action="" method="post" onsubmit="return displaySweetAlert();">
                    <div class="d-flex flex-row align-items-center mb-4">
                      <i class="fas fa-user fa-lg me-3 fa-fw"></i>
                      <div class="form-outline flex-fill mb-0">
                        <label class="form-label" for="resetButton"><i class="bi bi-envelope-at-fill"></i> Your email</label>
                        <input type="text" id="resetButton" class="form-control form-control-lg py-3" name="email" autocomplete="off" placeholder="Enter your email" style="border-radius:25px ;" />

                      </div>
                    </div>
                    <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
                      <input type="submit" id="resetLinkButton" value="Send reset link" name="reset" class="btn btn-danger btn-lg text-light my-2 py-3" style="width:100% ; border-radius: 30px; font-weight:600;" style="border-radius:25px ;" />

                    </div>


                       </form>
                  <p align="center">I have already account <a href="login.php" class="text-danger" style="font-weight:600; text-decoration:none;">Login</a></p>
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
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js" integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
  </script>

  <script>
    // Function to display the SweetAlert based on success state
    function displaySweetAlert() {
      <?php if ($success): ?>
        Swal.fire({
          position: 'center',
          icon: 'success',
          title: 'Email Sent Successfully!',
          text: 'Password reset link has been sent to your email address. Please check your inbox and spam folder.',
          showConfirmButton: true,
          confirmButtonText: 'OK',
          confirmButtonColor: '#c41e67'
        });
      <?php else: ?>
        // Only show alert if there's an error message
        <?php if (!empty($msg) && !$success): ?>
          Swal.fire({
            position: 'center',
            icon: 'error',
            title: 'Error',
            html: '<?php echo addslashes($msg); ?>',
            showConfirmButton: true,
            confirmButtonText: 'Try Again',
            confirmButtonColor: '#c41e67'
          });
        <?php endif; ?>
      <?php endif; ?>
    }

    // Handle the click on the "Send reset link" button
    document.getElementById('resetLinkButton').addEventListener('click', function (e) {
      e.preventDefault();
      
      // Get email input
      const emailInput = document.getElementById('resetButton');
      const email = emailInput.value.trim();
      
      // Validate email
      if (!email) {
        Swal.fire({
          icon: 'error',
          title: 'Email Required',
          text: 'Please enter your email address.',
          confirmButtonColor: '#c41e67'
        });
        return;
      }
      
      if (!isValidEmail(email)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Email',
          text: 'Please enter a valid email address.',
          confirmButtonColor: '#c41e67'
        });
        return;
      }
      
      // Show loading
      Swal.fire({
        title: 'Sending Reset Link...',
        text: 'Please wait while we send the password reset link to your email.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      // Submit the form
      document.getElementById('resetForm').submit();
    });
    
    // Email validation function
    function isValidEmail(email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    }
    
    // Display alert on page load if there's a message
    document.addEventListener('DOMContentLoaded', function() {
      displaySweetAlert();
    });
  </script>

</body>

</html>