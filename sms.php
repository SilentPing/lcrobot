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
// Check if the Send SMS button is clicked within the modal
if (isset($_POST['send_sms_btn'])) {
    // Retrieve the phone number of the registrant from the database or wherever it's stored
    $phone_number = $_POST['contact_no']; // Replace this with the appropriate phone number retrieval method

    // Retrieve the SMS message from the form textarea
    $sms_message = $_POST['sms_message'];

    // Your Semaphore API Key
    $api_key = '3b0a653cc759c73537ac5e57bf133e8c'; // Replace this with your Semaphore API key

    // Semaphore API Endpoint
    $api_url = 'https://semaphore.co/api/v4/messages';

    // Sender Name (Optional)
    $sender_name = 'BOTOLANMCRO'; // Replace this with your desired sender name

    // Initialize cURL session
    $ch = curl_init();

    // Set parameters for sending SMS
    $parameters = array(
        'apikey' => $api_key,
        'number' => $phone_number,
        'message' => $sms_message,
        'sendername' => $sender_name
    );

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL request
    $output = curl_exec($ch);

    // Check for errors and handle the response from Semaphore
    if ($output === FALSE) {
        echo "Error: " . curl_error($ch);
    } else {
        // Handle the response from Semaphore (you might want to log or process this response
            echo "<script>
            Swal.fire({
              title: 'Message sent!',
              text: 'Message sent to $phone_number',
              icon: 'success',
              confirmButtonText: 'OK'
            }).then(() => {
              window.location.href = 'manage_request.php';
            });
            </script>"; " . $output . <br>";

                }

    // Close cURL session
    curl_close($ch);
}
?>