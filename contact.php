<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

</body>
</html>

<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/vendor/autoload.php';

if (isset($_POST['send'])) {
    $fullname = htmlentities($_POST['fullname']);
    $email = htmlentities($_POST['email']);
    $number = htmlentities($_POST['number']);
    $address = htmlentities($_POST['address']);
    $message = htmlentities($_POST['message']);

    // Database connection
        $servername = "localhost";
          $username = "root";
          $password = "";
          $dbname = "civ_reg";
          
    $con = new mysqli($servername, $username, $password, $dbname);

    if ($con->connect_error) {
        error_log("Connection failed: " . $con->connect_error);
        die("Connection failed: " . $con->connect_error);
    }

    $mail = new PHPMailer(true);

   $sql = "INSERT INTO inquiries (fullname, email, number, address, message) VALUES ('$fullname', '$email', '$number', '$address', '$message')";

    if (mysqli_query($con, $sql)) {
        error_log("Data inserted successfully.");

}

    try {
        // $mail->SMTPDebug = 2;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nolibaluyot@pcb.edu.ph';
        $mail->Password = 'qndv iatj pqdl mcqi';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('nolibaluyot@pcb.edu.ph');
        $mail->addAddress($email); // Replace with the admin's email address

        $mail->isHTML(true);
        $mail->Subject = 'Users Inquiries';
        $mail->Body = "
            <p><strong>Full Name:</strong> $fullname</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone Number:</strong> $number</p>
            <p><strong>Address:</strong> $address</p>
            <p><strong>Message:</strong> $message</p>
        ";
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        echo '<script>';
        echo 'Swal.fire({
            title: "Good job!",
            text: "Your Inquiry has been Sent",
            icon: "success"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "user_dashboard.php"; // Redirect to desired page
            }
        });';
        echo '</script>';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
        error_log("Execute failed: " . mysqli_error($con));
        echo "Execute failed: " . mysqli_error($con);
    }

    $con->close();
?>
