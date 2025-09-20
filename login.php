<?php 
session_start(); 

// Prevent caching of login page
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is already logged in and redirect appropriately
if(isset($_SESSION['name'])) {
    if(isset($_SESSION['usertype']) && $_SESSION['usertype'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <title>Civil Registrar Portal</title>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="images/lcrobot.png" type="images/png">


  <!-- Bootstrap CSS v5.2.1 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <link rel="stylesheet" href="style.css">
</head>

<body>

<?php

require_once __DIR__ . '/db.php';

if (isset($_POST['login'])) {
    $username = stripslashes($_REQUEST['username']);
    $username = mysqli_real_escape_string($conn, $username);
    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($conn, $password);
    
    // Validate input
    if (empty($username) || empty($password)) {
        echo "<script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please enter both email and password.',
                });
              </script>";
    } else {

    $query = "SELECT * FROM `users` WHERE username='$username' OR email='$username'";
    $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
    $rows = mysqli_num_rows($result);

    if ($rows == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verify password (support both MD5 and password_hash)
        $stored_password = $row['password'];
        $password_valid = false;
        
        // Check if stored password is MD5 (32 chars) or password_hash (60+ chars)
        if (strlen($stored_password) === 32) {
            // MD5 password
            $password_valid = (md5($password) === $stored_password);
        } else {
            // password_hash
            $password_valid = password_verify($password, $stored_password);
        }
        
        if ($password_valid) {
            // Regenerate the session ID to prevent session fixation attacks
            session_regenerate_id(true);

            $_SESSION['name'] = $username;
            $_SESSION['fname'] = $row['u_fn'];
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['usertype'] = $row['usertype']; // Add usertype to session
            $_SESSION['login_success'] = "Login Successful!";

            // Update last_login timestamp
            $update_login_query = "UPDATE users SET last_login = NOW() WHERE id_user = ?";
            $update_stmt = $conn->prepare($update_login_query);
            if ($update_stmt) {
                $update_stmt->bind_param("i", $row['id_user']);
                $update_stmt->execute();
                $update_stmt->close();
            }

        // Log user activity
        $user_id = $_SESSION['id_user'];
        $first_name = $row['u_fn'];
        $middle_name = $row['u_mn'];
        $last_name = $row['u_ln'];

        if ($row['usertype'] == 'admin') {
            $activity_description = 'Admin logged in';
        } else {
            $activity_description = 'User logged in';
        }

        $insert_query = "INSERT INTO user_activity_logs (user_id, username, middle_name, last_name, activity_description) VALUES ('$user_id', '$first_name', '$middle_name', '$last_name', '$activity_description')";

        if (mysqli_query($conn, $insert_query)) {
            // Log entry successful
        } else {
            echo "Error logging user activity: " . mysqli_error($conn);
        }

        // Clear any cached content and redirect
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        if ($row['usertype'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }

            exit();
        } else {
            // Password verification failed - user exists but wrong password
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Wrong Password',
                        text: 'The password you entered is incorrect. Please try again.',
                    });
                  </script>";
        }
    } else {
        // User not found - wrong email/username
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Wrong Email',
                    text: 'The email address you entered is not registered. Please check and try again.',
                });
              </script>";
    }
    } // Close the else block for input validation
}

?>


  <section class="vh-100">
    <div class="container py-5 h-100">
      <div class="row d-flex align-items-center justify-content-center h-100">
        <div class="col-md-8 col-lg-5 col-xl-5">
          <img src="images/lcrobot.png" class="img-fluid" alt="Phone image" height="100px" width="500px">
        </div>
        <div class="col-md-7 col-lg-5 col-xl-5 offset-xl-1">
          <form action="" method="post" id="loginForm">
            <p class="text-center h1 fw-bold mb-4 mx-1 mx-md-1 mt-3">A Web Based Civil Registry <br>Portal for Botolan</p>
            
              <!-- Email input -->
            <div class="form-floating mb-4">
              <input type="email" class="form-control" name="username" autocomplete="off" id="floatingInput" placeholder="Enter your Email" style="border-radius:20px ;" >
              <label for="floatingInput">Email address</label>
            </div>
            
             <!-- Password input -->
           <div class="form-floating mb-4">
              <input type="password" class="form-control" name="password" autocomplete="off" id="floatingPassword" placeholder="Enter your Password" style="border-radius:20px ;">
              <label for="floatingPassword">Password</label>
            </div>


            <!-- Submit button -->
            <!-- <button type="submit" class="btn btn-primary btn-lg">Login in</button> -->
            <div class="d-flex justify-content-center mx-1 mb-3 mb-lg-1">
              <input type="submit" value="Login" name="login" class="btn btn-primary btn-lg text-light my-2 py-3" style="width:100% ; border-radius: 30px; font-weight:600;" />
            </div>

          </form><br>
          <p align="center">I don't have any account <a href="registration.php" class="text-danger" style="font-weight:600;text-decoration:none;">Register here</a></p>
           </form>
          <p align="center">Forgot password? <a href="forgot_pass.php" class="text-danger" style="font-weight:600;text-decoration:none;">Click here</a></p>
        </div>
      </div>
    </div>
  </section>



  <!-- Bootstrap JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>

  <script src="https://kit.fontawesome.com/1397afa917.js" crossorigin="anonymous"></script>
  
  <script>
    // Prevent back button issues and clear cache
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Page was loaded from cache, reload to get fresh content
            window.location.reload();
        }
    });

    // Clear form data when page loads
    window.addEventListener('load', function() {
        // Clear any cached form data
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });

    // Prevent form resubmission on refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Client-side form validation
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const username = document.getElementById('floatingInput').value.trim();
        const password = document.getElementById('floatingPassword').value.trim();
        
        if (!username || !password) {
            e.preventDefault(); // Prevent form submission
            
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please enter both email and password.',
            });
            
            return false;
        }
        
        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(username)) {
            e.preventDefault(); // Prevent form submission
            
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Email Format',
                text: 'Please enter a valid email address.',
            });
            
            return false;
        }
    });
  </script>

<?php
    
?>
</body>
</html>