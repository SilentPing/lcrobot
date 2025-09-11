<?php
session_start();

// Prevent caching of dashboard pages
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if(!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMCROP | User Dashboard</title>
    <link rel="icon" href="images/civ.png" type="images/png">


    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
   

   <script src="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/js/shepherd.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@10.0.1/dist/css/shepherd.css"/>

    <!-- Include Bootstrap CSS and JavaScript files -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">



    <!-- custom css file link  -->
    <link rel="stylesheet" href="dashboard.css">

</head>
<body>
    <style>
       .custom-ul {
             font-size: 17px;
        }

        .custom-ul .dropdown-item li {
    font-size: 16px; /* You can adjust the font size for dropdown menu items */
        }

  /* Increase the width of the accordion container */
  .accordion {
    width: 100%; /* You can adjust the width as needed */
  }

  /* Adjust the height of the accordion items */
  .accordion-item {
    margin-bottom: 10px; /* Add some space between accordion items */
  }

  /* Customize the font size and padding of accordion buttons */
  .accordion-button {
    font-size: 18px; /* You can adjust the font size */
    padding: 10px; /* You can adjust the padding */
  }

  /* Customize the padding of accordion bodies */
  .accordion-body {
    padding: 15px; /* You can adjust the padding */
  }

    .certificate-header {
        display: flex;
        align-items: center;
    }

    .mcro-logo {
        margin-right: 20px; /* Adjust this value to your preference */
    }

    </style>

    
<!-- header section starts  -->

<!-- </div> -->

<div class="b-example-divider"></div>

  <header class="p-3 mb-3 border-bottom">
    <div class="container">
      <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
        <a href="/" class="d-flex align-items-center mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
          <svg class="bi me-2" width="40" height="32" role="img" aria-label="Bootstrap"><use xlink:href="#bootstrap"/></svg>
        </a>

        <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0 custom-ul">
          <li><a href="#home" class="nav-link px-3 link-secondary">Home</a></li>
          <li><a href="#service" class="nav-link px-3 link-secondary">CRD</a></li>
          <li><a href="#process" class="nav-link px-3 link-body-emphasis">Application Process</a></li>
          <li><a href="#review" class="nav-link px-3 link-body-emphasis">FAQ</a></li>
          <li><a href="#contact" class="nav-link px-3 link-body-emphasis">Contacts</a></li>
        </ul>

        <div class="dropdown text-end">
          <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="images/lcro.jpg" alt="User Image" width="32" height="32" class="rounded-circle">
          </a>
          <ul class="dropdown-menu text-small">
            <li><hr class="dropdown-divider"></li>
             <li><a class="dropdown-item" href="user_profile.php">Profile</a></li>
            <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
          </ul>
        </div>
      </div>
    </div>
  </header>

<!-- header section ends -->

<!-- home section starts  -->

<section class="home" id="home">

    <div class="content">
        <h3>Welcome <?php echo $_SESSION['fname']; ?>!</h3>
        <span>Are you Looking for Civil Documents ?</span>
        <h3>we are here for you!</h3>
        <p>Request your Document now Here at Botolan Municipal Civil Registrar</p>
    </div>

       <div class="image">
          <img src="images/lgu2.png" alt="">
       </div>


</section>

<!-- home section ends -->


<!-- icons section  -->

<section class="icons-container">

    <div class="icons">
        <img src="images/birth2.svg" alt="">
        <div class="info">
            <h3>Birth Certificate</h3>
            <p>Birth certificate is a vital record documenting a person's birth, either as the original document or a certified copy.</p>
        </div>
    </div>

    <div class="icons">
        <img src="images/marriage2.svg" alt="">
        <div class="info">
            <h3>Marriage Certificate</h3>
            <p>Marriage certificate is an official document confirming marriage, issued by a government official after civil registration.</p>
        </div>
    </div>

    <div class="icons">
        <img src="images/death2.svg" alt="">
        <div class="info">
            <h3>Death Certificate</h3>
            <p>A death certificate is a legal document from a medical practitioner or a government civil registration office stating the date, location, and cause of a person's death.</p>
        </div>
    </div>

    <div class="icons">
        <img src="images/cen2.svg" alt="">
        <div class="info">
            <h3>CENOMAR</h3>
            <p>A Certificate of No Marriage Record (CENOMAR) is simply what its name implies. It is a certification issued by the PSA stating that a person has not contracted any marriage.</p>
        </div>
    </div>

</section>

<!-- service section starts  -->

<section class="service" id="service">

 <h1 class="heading">Civil Registry Documents</h1>
 <p class="description"></p>

 <div class="box-container">
<div class="box">
    <img src="images/birth1.svg" alt="">
    <img src="images/civ.png" alt="MCRO Logo" class="mcro-logo" style="float: right; margin-left: 10px;">
    <h3>Birth Certificate</h3>
    <p>Birth certificate is a vital record documenting a person's birth, either as the original document or a certified copy</p>
    <a href="birth_form.php?type_request=Birth Certificate" class="btn">Request Now</a>
</div>


    <div class="box">
        <img src="images/marriage1.svg" alt="">
        <img src="images/civ.png" alt="MCRO Logo" class="mcro-logo" style="float: right; margin-left: 10px;">
        <h3>Marriage Certificate</h3>
        <p>Marriage certificate is an official document confirming marriage, issued by a government official after civil registration</p>
        <a href="marriage_form.php?type_request=Marriage Certificate" class="btn">Request Now</a>
    </div>

    <div class="box">
        <img src="images/death1.svg" alt="">
        <img src="images/civ.png" alt="MCRO Logo" class="mcro-logo" style="float: right; margin-left: 10px;">
        <h3>Death Certificate</h3>
        <p>A death certificate is a legal document from a medical practitioner or a government civil registration office stating the date, location, and cause of a person's death</p>
        <a href="death_form.php?type_request=Death Certificate" class="btn">Request Now</a>
    </div>

    <div class="box">
        <img src="images/cen1.svg" alt="">
        <img src="images/civ.png" alt="MCRO Logo" class="mcro-logo" style="float: right; margin-left: 10px;">
        <h3>CENOMAR</h3>
        <p>A Certificate of No Marriage Record (CENOMAR) is simply what its name implies. It is a certification issued by the PSA stating that a person has not contracted any marriage</p>
        <a href="ceno_form.php?type_request=CENOMAR" class="btn">Request Now</a>
    </div>

 </div>

</section>

<!-- service section ends -->

<!-- process section starts  -->

<section class="process" id="process">

    <h1 class="heading">Application Process</h1>
    <p class="description">The Guide for your papers.</p>
   
    <div class="box-container">

        <div class="box">
            <span>1</span>
            <h3>Fill up the Application Form</h3>
            <p>All you have to do is fill up the application form you want to request. insert all your details and information.</p>
        </div>

        <div class="box">
            <span>2</span>
            <h3>Wait the Status of your Application</h3>
            <p>Wait for the approval of the administrator for your request and you will be notify if the admin approve,execute your status.</p>
        </div>

        <div class="box">
            <span>3</span>
            <h3>Wait for the Email or SMS Notification</h3>
            <p>Once you recieved the email or sms notification your request have been arrived at the office.</p>
        </div>

        <div class="box">
            <span>4</span>
            <h3>Claim your Document Request</h3>
            <p>After you recieved the email or sms notification you can now go to the Civil Registrar Office of Botolan to claim your document.</p>
        </div>


        <div class="box">
            <span>5</span>
            <h3>Pay the exact amount to the counter</h3>
            <p>After claiming the document pay the exact amount of what civil registry document you requested.</p>
        </div>

    </div>

</section>

<!-- process section ends -->

<!-- review section starts  -->
<section class="review" id="review">

    <h1 class="heading">Frequently Asked Questions</h1>
    <p class="description">Looking for information about Civil Registration. This section provides answers to your common questions about Civil Records, Registration and Documents</p>

   <div class="accordion accordion-flush" id="accordionFlushExample">
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
        What Civil Registry Documents does the PSA issue?/<br>Anong mga Civil Registry Documents ang inilalabas ng PSA?
      </button>
    </h2>
    <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
      <div class="accordion-body"><strong>Birth certificate</strong>
                                  <br><strong>Marriage certificate</strong>
                                  <br><strong>Death certificate</strong>
                                  <br><strong>Certificate of No Marriage Record (CENOMAR)
                                  <br>
                                  <br><strong>Sertipiko ng Kapanganakan</strong>
                                  <br><strong>Sertipiko ng Kasal</strong>
                                  <br><strong>Sertipiko ng Kamatayan</strong>
                                  <br><strong>Sertipiko ng Walang Rekord ng Kasal (CENOMAR)</strong></div>

    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
        What are the requirements for Requesting/Applying for a Civil Registry Document from the PSA?/ <br>Ano ang mga kinakailangan para sa Paghiling/Pag-aaplay para sa isang Civil Registry Document mula sa PSA?
      </button>
    </h2>
    <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
      <div class="accordion-body"><strong>You must be a Filipino citizen.</strong>
                                  <br><strong>You must be at least 18 years old.</strong>
                                  <br><strong>You must present a valid government-issued ID.</strong>
                                  <br><strong>You must pay the applicable processing fee
                                  <br>
                                  <br><strong>Ikaw ay dapat na mamamayang Pilipino</strong>
                                  <br><strong>Ikaw ay dapat na hindi bababa sa 18 (labingwalong taong) gulang</strong>
                                  <br><strong>Dapat kang magpakita ng valid na government-issued ID</strong>
                                  <br><strong>Dapat mong bayaran ang naaangkop na bayad sa pagproseso</strong></div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
        Can I apply for a Civil Registry Document on behalf of someone else?/<br>Maaari ba akong mag-aplay para sa isang Civil Registry Document sa ngalan ng ibang tao?
      </button>
    </h2>
    <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
      <div class="accordion-body"><strong>Yes, you can apply for a civil registry document on behalf of someone else. However, you will need to submit an authorization letter from the document owner, as well as your own valid government-issued ID.</strong></div>
    </div>
  </div>
   <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
        List of valid IDs for PSA Birth Certificate
      </button>
    </h2>
    <div id="flush-collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
      <div class="accordion-body"><strong>Philippine Identification Card</strong>
                                  <br><strong>Philippine Identification System Digital ID (ePHILID)</strong>
                                  <br><strong>Philippine Passport issued by the Department of Foreign Affairs (DFA)</strong>
                                  <br><strong>Driver's License issued by the Land Transportation Office (LTO)</strong>
                                  <br><strong>Professional Regulations Commission (PRC) ID</strong>
                                  <br><strong>Integrated Bar of the Philippines (IBP) ID</strong></div>
    </div>
  </div>
   <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFive" aria-expanded="false" aria-controls="flush-collapseFive">
        Can I use NSO instead of PSA?<br>Maaari ko bang gamitin ang NSO sa halip na PSA?
      </button>
    </h2>
    <div id="flush-collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
      <div class="accordion-body"><strong>This is because the NSO is one of the four statistical agencies that were merged under Republic Act No. 10625 in order to create the PSA. As such, the NSO copy of the birth certificate can be used for purposes of enrollment and it is needless to require learners to secure a new PSA birth certificate.</strong></div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseSix" aria-expanded="false" aria-controls="flush-collapseSix">
        What should I do if I lose my Civil Registry Document?/ <br> Ano ang dapat kong gawin kung mawala ko ang aking Civil Registry Document?
      </button>
    </h2>
    <div id="flush-collapseSix" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
      <div class="accordion-body"><strong>If you lose your civil registry document, you can apply for a replacement copy from the PSA. You will need to pay a replacement fee and submit the required documents, such as a copy of your valid government-issued ID and a police report if your document was stolen.</strong></div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseSeven" aria-expanded="false" aria-controls="flush-collapseSeven">
        What is the meaning of LCR in Birth Certificate?
      </button>
    </h2>
    <div id="flush-collapseSeven" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
      <div class="accordion-body"><strong>This is the process of acquiring the certified true copy (CTC) or Local Civil Registry (LCR) copy of the Certificates of Live Birth</strong></div>
    </div>
  </div>
</div>

</section>


<!-- review section ends -->

<!-- contact section starts  -->

<section class="contact" id="contact">

    <h1 class="heading">contact us</h1>

    <div class="box-container">

        <div class="box">
            <i class="fas fa-route"></i>
            <h3>our location</h3>
            <p>Botolan Zambales, Philippines</p>
        </div>

        <div class="box">
            <i class="fas fa-phone"></i>
            <h3>our number</h3>
            <p>090-5280-3518</p>
            <p>093-632-72051.</p>
        </div>

        <div class="box">
            <i class="fas fa-envelope-open"></i>
            <h3>our email</h3>
            <p>BMCROP@gmail.com</p>
        </div>

    </div>

    <form action="contact.php" method="post">

    <input type="text" name="fullname" placeholder="full name" class="box">
    <input type="email" name="email" placeholder="your email" class="box">
    <input type="number" name="number" placeholder="your number" class="box">
    <input type="text" name="address" placeholder="your address" class="box">
    <textarea name="message" cols="30" rows="10" class="box message" placeholder="message"></textarea>
    <input type="submit" value="send" name="send" class="btn">

</form>

</section>

<!-- contact section ends -->

<!-- footer section starts  -->

<div class="footer">

    <div class="box-container">

        <div class="box">
            <h3>Civil Registrar of Botolan</h3>
            <p>2023</p>
        </div>

        <div class="box">
            <h3>quick links</h3>
            <a href="#">home</a>
            <a href="#">service</a>
            <a href="#">process</a>
            <a href="#">review</a>
            <a href="#">contact</a>
        </div>

        <div class="box">
            <h3>follow us</h3>
            <a href="https://web.facebook.com/mcrobotolan/?locale=fr_FR&_rdc=1&_rdr">facebook</a>
        </div>

    </div>

    <h1 class="credit"><a href="https://web.facebook.com/mcrobotolan/?locale=fr_FR&_rdc=1&_rdr">MCRO</a> All Rights Reserve</h1>

</div>

<!-- footer section ends -->

<!-- scroll top  -->
<a href="#home" class="scroll-top">
    <img src="civ_reg_system/images/scroll-img.png" alt="">
</a>

<!-- jquery cdn link  -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php if (isset($_SESSION['login_success'])) { ?>
<!-- Modal -->
<div class="modal fade" id="exampleModalLong" data-bs-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-15" id="exampleModalLongTitle">Data Privacy Notice</h1>
        <!--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
      </div>
      <div class="modal-body" style="min-height: 450px">
           <p style="font-size: 15px;">Botolan Municipal Civil Registrar Online Portal is committed to protecting your privacy and the confidentiality of your personal data. This Data Privacy Notice is intended to inform you about the collection, use, and processing of your personal data by Botolan Municipal Civil Registrar Online Portal in accordance with the DPA. All civil registry documents necessarily contain personal and/or sensitive personal information. Hence, under <em style="font-weight: bold;">R.A. 10173 or the Data Privacy Act of 2012</em>, it is unlawful for anyone to access, use, store, or process in any way these documents without the consent and/or authority from the document owner.</p>
        <hr class="my-2">
        <h1 class="mb-3">
            <b style="font-size: 20px">USER'S CONSENT</b>
        </h1>
        <!-- <h4 style="font-style: Palatino Linotype, Book Antiqua, Palatino, serif; font-size: 15px;">USER'S CONSENT</h4> -->
        <p>
          <em style="font-size: 15px;">I agree/consent to the collection of my personal information through this medium. I understand that the Botolan Municipal Civil Registrar Online Portal will abide by the policy except for cases not within their control.</em>
        </p>
        <input type="checkbox" class id="checkYes" required>
        <label class="form-check-label" for="checkYes" style="font-size: 15px;">Yes</label>
         <input type="checkbox" class id="checkNo" required>
        <label class="form-check-label" for="checkNo" style="font-size: 15px;">No</label>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-close1 btn-lg" data-bs-dismiss="modal" aria-label="modal" id="agreeButton">Agree</button>
      </div>
    </div>
  </div>
</div>


<?php unset($_SESSION['login_success']); } ?>


<!-- Add this script at the end of your HTML, just before </body> tag -->
<script>
  $(document).ready(function () {
    // Select the modal element by its ID and show it
    $('#exampleModalLong').modal('show');

    // Add an event listener to the close button
    $('.btn-close1').click(function () {
      if ($('#checkNo').prop('checked')) {
        // Redirect the user or perform any other action for "No" response
        alert('You clicked "No". Redirecting or taking action accordingly...');
      } else {
        // Continue with your existing logic for "Yes" response
        // For example, redirect the user to the dashboard
        window.location.href = 'user_dashboard.php';
      }

      // Select the modal element by its ID and hide it
      $('#exampleModalLong').modal('hide');
    });

    // Disable the "Agree" button initially if "No" is checked
    $('#agreeButton').prop('disabled', $('#checkNo').prop('checked'));

    // Add a change event listener to the "No" checkbox
    $('#checkNo').change(function () {
      // Disable the "Agree" button if "No" is checked, enable otherwise
      $('#agreeButton').prop('disabled', $(this).prop('checked'));
    });

    // Add a click event listener to the modal backdrop
    $('.modal-backdrop').click(function () {
      // If "No" is checked, prevent hiding the modal
      if ($('#checkNo').prop('checked')) {
        return false;
      }
    });
  });
</script>


</body>
</html>

 