<?php
// session_start();

include('includes/header.php'); 
include('includes/navbar.php');

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'phpmailer/vendor/autoload.php';


include 'db.php';

if (isset($_POST['rejectEmail'])) {
    // Fetch necessary data from the form
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $rejectionReason = mysqli_real_escape_string($conn, $_POST['rejectionReason']);
    $registrarName = mysqli_real_escape_string($conn, $_POST['registrarName']);
    $typeOfRequest = mysqli_real_escape_string($conn, $_POST['typeOfRequest']);
    $requestId = mysqli_real_escape_string($conn, $_POST['request_id']); // Fetch the request ID to be used for deletion

    // Insert the rejected request into the rejected_requests table
    $queryInsertRejected = "INSERT INTO rejected_requests (registration_date, registrar_name, type_request, status)
                            SELECT registration_date, registrar_name, type_request, 'Rejected'
                            FROM reqtracking_tbl
                            WHERE request_id = '$requestId'";
    $queryInsertRejectedRun = mysqli_query($conn, $queryInsertRejected);

    if (!$queryInsertRejectedRun) {
        die("Query to insert rejected request failed: " . mysqli_error($conn));
    }

    // Delete the rejected request from the original table
    $queryDeleteOriginal = "DELETE FROM reqtracking_tbl WHERE request_id = '$requestId'";
    $queryDeleteOriginalRun = mysqli_query($conn, $queryDeleteOriginal);

    if (!$queryDeleteOriginalRun) {
        die("Query to delete original request failed: " . mysqli_error($conn));
    }

    // Send the rejection email
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nolibaluyot@pcb.edu.ph';
        $mail->Password = 'qndv iatj pqdl mcqi';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('nolibaluyot@pcb.edu.ph', 'THE MCRO BOTOLAN TEAM');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Request has been Rejected';
        $mail->Body = 'Dear ' . $registrarName . ',<br><br>Your request for <b>' . $typeOfRequest . '</b> has been rejected due to the following reason: <b>' . $rejectionReason . '</b><br><br>Sincerely,<br>THE MCRO BOTOLAN TEAM';

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

    echo '<script>
      Swal.fire({
        title: "Great!",
        text: "Your message has been sent!",
        icon: "success",
        confirmButtonColor: "#3085d6",
        confirmButtonText: "OK"
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "manage_request.php";
        }
      });
    </script>';
}
?>

<?php
include('includes/script.php');
include('includes/footer.php');
?>

