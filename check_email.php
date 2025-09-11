<?php
require('db.php');

if (isset($_POST['email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if the email already exists in the database
    $check_query = "SELECT * FROM `users` WHERE email='$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo "Email already taken";
    } else {
        echo "Email available";
    }
}
?>

