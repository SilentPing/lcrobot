<?php
// // session_start();

// include('includes/header.php'); 
// include('includes/navbar.php');

// //Import PHPMailer classes into the global namespace
// //These must be at the top of your script, not inside a function
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception;

// //Load Composer's autoloader
// require 'phpmailer/vendor/autoload.php';


// include 'db.php';

// if (isset($_POST['approve_btn'])) {

//     $email = mysqli_real_escape_string($conn, $_POST['email']);
//     // Construct the Approve email
//     $mail = new PHPMailer(true);
//     try {
//         // Server settings
//         // $mail->SMTPDebug = 2; // Enable verbose debug output
//         $mail->isSMTP(); // Send using SMTP
//         $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
//         $mail->SMTPAuth = true; // Enable SMTP authentication
//         $mail->Username = 'nolibaluyot@pcb.edu.ph'; // SMTP username
//         $mail->Password = 'baluyot2020!'; // SMTP password
//         $mail->SMTPSecure = 'tls'; // Enable implicit TLS encryption
//         $mail->Port = 587; // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

//         // Recipients
//         $mail->setFrom('nolibaluyot@pcb.edu.ph');
//         $mail->addAddress($email); // Use the captured recipient email address

//        $mail->isHTML(true); // Set email format to HTML
//        $mail->Subject = 'Your Request has been Approved';
//        $mail->Body = 'Dear User,<br><br><b>Your request has been Approved!</b><br><br>
//                        You have requested the following Civil Documents: Birth Certificate<br>
//                        Please note that the estimated waiting time for processing is 7 working days.<br><br>
//                        We will notify you as soon as the requested civil documents arrive at the MCRO Office.<br><br>
//                        Sincerely,<br>THE MCRO BOTOLAN TEAM';


//          $mail->send();
//         echo "<script>
//             Swal.fire({
//               position: 'center',
//               icon: 'success',
//               title: 'Approval email has been sent to $email',
//               showConfirmButton: false,
//               timer: 1500
//             });
//           </script>";
//         // You can also set a success message or redirect the admin to another page upon successful sending of the approval email.
//     } catch (Exception $e) {
//         echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
//         // Handle the error case, display an error message, or log the error as required.
//     }
// }
// ?>
