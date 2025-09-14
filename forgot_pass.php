<?php
session_start();

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'phpmailer/vendor/autoload.php';

require_once __DIR__ . '/db.php';
$msg = "";

if (isset($_POST['reset'])) {

    // $email = $_SESSION['user_email'];

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $code = mysqli_real_escape_string($conn, md5(rand()));

    if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE email='{$email}'")) > 0) {
        $query = mysqli_query($conn, "UPDATE users SET code='{$code}' WHERE email='{$email}'");

        if ($query) {        
            echo "<div style='display: none;'>";
            //Create an instance; passing `true` enables exceptions
            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->SMTPDebug = 2;                      //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = 'nolibaluyot@pcb.edu.ph';                     //SMTP username
                $mail->Password   = 'qndv iatj pqdl mcqi';                               //SMTP password
                $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
                $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                //Recipients
                $mail->setFrom('baluyotnli@gmail.com');
                $mail->addAddress($email); // Use the captured email address

                //Content
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = 'Civil Registrar Portal';
                $mail->Body    = 'Here is the reset link <b><a href="localhost/civreg/change_pass.php?reset='.$code.'">localhost/civreg/change_pass.php?reset='.$code.'</a></b>';

                $mail->send();
                echo 'Message has been sent';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
            echo "</div>";        
            $msg = "<div class='alert alert-info'>We've send a verification link on your email address.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>$email - This email address do not found.</div>";
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
    // Function to display the SweetAlert
    function displaySweetAlert() {
      Swal.fire({
        position: 'center',
        icon: 'success',
        title: 'We have send a reset link on your email address',
        showConfirmButton: false,
        timer: 1500
      });
    }

    // Handle the click on the "Send reset link" button
    document.getElementById('resetLinkButton').addEventListener('click', function () {
      // Display the SweetAlert
      displaySweetAlert();

      // Submit the form
      document.getElementById('resetForm').submit();
    });
  </script>

</body>

</html>