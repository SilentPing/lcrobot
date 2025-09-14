<?php
session_start();

// Include configuration files
require_once __DIR__ . '/config/mailgun_config.php';
require_once __DIR__ . '/config/email_templates.php';
require_once __DIR__ . '/db.php';

$msg = "";
$valid_token = false;
$user_email = "";

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    // Check if token exists and is valid
    $token_query = "SELECT email, expires_at FROM password_reset_tokens 
                    WHERE token = ? AND used = 0 AND expires_at > NOW()";
    $stmt = mysqli_prepare($conn, $token_query);
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $token_data = mysqli_fetch_assoc($result);
        $user_email = $token_data['email'];
        $valid_token = true;
        
        if (isset($_POST['reset'])) {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validate passwords
            if (empty($password) || empty($confirm_password)) {
                $msg = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Please fill in all fields.</div>";
            } elseif (strlen($password) < 6) {
                $msg = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Password must be at least 6 characters long.</div>";
            } elseif ($password !== $confirm_password) {
                $msg = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Password and Confirm Password do not match.</div>";
            } else {
                // Hash the new password using password_hash for better security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Update user password
                $update_query = "UPDATE users SET password = ? WHERE email = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, 'ss', $hashed_password, $user_email);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Mark token as used
                    $mark_used_query = "UPDATE password_reset_tokens SET used = 1 WHERE token = ?";
                    $stmt = mysqli_prepare($conn, $mark_used_query);
                    mysqli_stmt_bind_param($stmt, 's', $token);
                    mysqli_stmt_execute($stmt);
                    
                    // Get user info for success email
                    $user_query = "SELECT username FROM users WHERE email = ?";
                    $stmt = mysqli_prepare($conn, $user_query);
                    mysqli_stmt_bind_param($stmt, 's', $user_email);
                    mysqli_stmt_execute($stmt);
                    $user_result = mysqli_stmt_get_result($stmt);
                    $user = mysqli_fetch_assoc($user_result);
                    
                    // Send success email
                    $html_body = getPasswordResetSuccessTemplate($user['username']);
                    $text_body = "Your password has been successfully reset! You can now log in to your account.";
                    
                    $email_result = sendMailgunEmail(
                        $user_email,
                        'Password Reset Successful - Botolan Civil Registry',
                        $html_body,
                        $text_body
                    );
                    
                    // Log the successful reset
                    logResetAttempt($user_email, true);
                    logEmailAttempt($user_email, 'password_reset_success', $email_result['success']);
                    
                    // Redirect to login with success message
                    header("Location: login.php?reset=success");
                    exit();
                } else {
                    $msg = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Database error. Please try again later.</div>";
                }
            }
        }
    } else {
        $msg = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Invalid or expired reset link. Please request a new password reset.</div>";
    }
} else {
    header("Location: forgot_pass.php");
    exit();
}

?>





<!doctype html>
<html lang="en">

<head>
  <title>Reset</title>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="images/civ.png" type="images/png">

  <!-- Bootstrap CSS v5.2.1 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
</head>

<body>

  <section class="vh-100" style="background-color: #eee;">
    <div class="container h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-lg-12 col-xl-11">
          <div class="card text-black" style="border-radius: 25px;">
            <div class="card-body p-md-2">
              <div class="row justify-content-center">
              <p class="text-center h1 fw-bold mb-4 mx-1 mx-md-3 mt-3">Reset your password</p>
                <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">

                  

                  <form class="mx-1 mx-md-4" action="change_pass.php?token=<?php echo $_GET['token']; ?>" method="post">
                    <div class="d-flex flex-row align-items-center mb-4">
                      <i class="fas fa-user fa-lg me-3 fa-fw"></i>
                      <div class="form-outline flex-fill mb-0">
                        <label class="form-label" for="form3Example1c"><i class="bi bi-lock-fill"></i> New password</label>
                        <input type="password" id="form3Example1c" class="form-control form-control-lg py-3" name="password" autocomplete="off" placeholder="enter your new password" style="border-radius:25px ;" />

                      </div>
                    </div>


                    <div class="d-flex flex-row align-items-center mb-4">
                      <i class="fas fa-lock fa-lg me-3 fa-fw"></i>
                      <div class="form-outline flex-fill mb-0">
                        <label class="form-label" for="form3Example4c"><i class="bi bi-arrow-counterclockwise"></i>Confirm Password</label>
                        <input type="password" id="form3Example4c" class="form-control form-control-lg py-3" name="confirm_password" autocomplete="off" placeholder="enter your password" style="border-radius:25px ;" />
                      </div>
                    </div>
                    <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
                      <input type="submit" value="Reset password" name="reset" class="btn btn-warning btn-lg text-light my-2 py-3" style="width:100% ; border-radius: 30px; font-weight:600;" style="border-radius:25px ;" />

                    </div>

                  </form>
                  <p align="center">I have already account <a href="login.php" class="text-warning" style="font-weight:600; text-decoration:none;">Login</a></p>
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
</body>

</html>