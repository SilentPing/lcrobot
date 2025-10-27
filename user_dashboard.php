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

// Load database connection
require_once __DIR__ . '/db.php';

// Get user's document eligibility and profile picture
$email = $_SESSION['name']; // This is actually the email address
$query = "SELECT birthplace_municipality, birthplace_province, profile_picture FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Clear any cached profile picture data
unset($_SESSION['user_profile_picture']);

$documentEligibility = null;
$userProfilePicture = 'images/lcro.jpg'; // Default profile picture

if ($row = mysqli_fetch_assoc($result)) {
    $birthplaceMunicipality = $row['birthplace_municipality'];
    $birthplaceProvince = $row['birthplace_province'];
    $profilePicture = $row['profile_picture'];
    
    // Set profile picture path
    if (!empty($profilePicture)) {
        // Try different path variations to find the profile picture
        $path_variations = [
            $profilePicture, // Original path from database
            __DIR__ . '/' . $profilePicture, // With full directory path
            'uploads/profile_pictures/' . basename($profilePicture), // Just filename in uploads dir
        ];
        
        foreach ($path_variations as $path) {
            if (file_exists($path)) {
                // Ensure the path is web-accessible (relative to web root)
                if (strpos($path, 'uploads/profile_pictures/') !== false) {
                    $userProfilePicture = $path;
                } else {
                    // Convert absolute path to relative web path
                    $userProfilePicture = 'uploads/profile_pictures/' . basename($path);
                }
                break;
            }
        }
    }
    
    // Check if user was born in Botolan, Zambales
    $isBornInBotolan = ($birthplaceMunicipality === '037101' && $birthplaceProvince === '0371');
    
    $documentEligibility = [
        'isBornInBotolan' => $isBornInBotolan,
        'canRequestPSA' => true, // Everyone can request PSA documents
        'canRequestLCRO' => $isBornInBotolan, // Only those born in Botolan can request LCRO documents
        'message' => $isBornInBotolan ? 
            'You can request both PSA and LCRO documents since you were born in Botolan, Zambales.' :
            'You can only request PSA documents. LCRO documents are only available for those born in Botolan, Zambales.'
    ];
    
    // Store in session for quick access
    $_SESSION['document_eligibility'] = $documentEligibility;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="cache-buster" content="<?php echo time(); ?>">
    <title>BMCROP | User Dashboard</title>
    <link rel="icon" href="images/lcrobot.png" type="images/png">


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
        
        /* Document type badges styling */
        .document-type-badges {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .document-type-badges .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .box {
            position: relative;
        }
        
        .box .document-type-badges {
            margin-top: 10px;
        }

  /* FAQ Section Styling - Improved Readability */
  .review {
    padding: 60px 0;
    background-color: #f8f9fa;
  }

  .review .heading {
    font-size: 2.5rem;
    font-weight: 700;
    color:rgb(255, 255, 255);
    margin-bottom: 20px;
    text-align: center;
  }

  .review .description {
    font-size: 2.2rem;
    color: rgb(255, 255, 255);
    text-align: center;
    margin-bottom: 40px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
  }

  .accordion {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
  }

  /* Accordion items with better spacing */
  .accordion-item {
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
  }

  /* Accordion buttons with larger, more readable text */
  .accordion-button {
    font-size: 20px;
    font-weight: 600;
    padding: 20px 25px;
    line-height: 1.4;
    background-color: #f8f9fa;
    border: none;
    color: #333;
    transition: all 0.3s ease;
  }

  .accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color: #1976d2;
    box-shadow: none;
  }

  .accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(25, 118, 210, 0.25);
  }

  .accordion-button:hover {
    background-color: #e9ecef;
  }

  .accordion-button:not(.collapsed):hover {
    background-color: #bbdefb;
  }

  /* Accordion body with better typography and spacing */
  .accordion-body {
    padding: 25px 30px;
    font-size: 16px;
    line-height: 1.6;
    background-color: #ffffff;
    color: #333;
  }

  /* Better styling for content inside accordion body */
  .accordion-body strong {
    font-size: 17px;
    color: #1976d2;
    font-weight: 600;
  }

  /* Responsive design for mobile devices */
  @media (max-width: 768px) {
    .accordion-button {
      font-size: 18px;
      padding: 18px 20px;
    }
    
    .accordion-body {
      padding: 20px 25px;
      font-size: 15px;
    }
    
    .accordion-body strong {
      font-size: 16px;
    }
  }

  @media (max-width: 576px) {
    .accordion-button {
      font-size: 16px;
      padding: 15px 18px;
    }
    
    .accordion-body {
      padding: 18px 20px;
      font-size: 14px;
    }
    
    .accordion-body strong {
      font-size: 15px;
    }
  }

    .certificate-header {
        display: flex;
        align-items: center;
    }

    .mcro-logo {
        margin-right: 20px; /* Adjust this value to your preference */
    }

    /* Profile picture size constraints */
    #user-profile-picture {
        width: 32px !important;
        height: 32px !important;
        max-width: 32px !important;
        max-height: 32px !important;
        min-width: 32px !important;
        min-height: 32px !important;
    }
    
    /* Ensure navbar doesn't expand */
    .navbar {
        min-height: auto !important;
    }
    
    .dropdown.text-end {
        display: flex;
        align-items: center;
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
            <img src="api/serve_profile_picture.php?t=<?php echo time(); ?>&r=<?php echo rand(1000, 9999); ?>" alt="User Profile Picture" width="32" height="32" class="rounded-circle" style="object-fit: cover; max-width: 32px; max-height: 32px;" id="user-profile-picture">
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
        
        <!-- Document Eligibility Status -->
        <!-- <?php if ($documentEligibility): ?>
        <div class="alert <?php echo $documentEligibility['isBornInBotolan'] ? 'alert-success' : 'alert-info'; ?> mt-3" role="alert">
            <h5><i class="fas fa-info-circle"></i> Document Eligibility Status</h5>
            <p class="mb-0"><?php echo $documentEligibility['message']; ?></p>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <strong>PSA Documents:</strong> 
                    <span class="badge bg-success">Available</span>
                    <small class="d-block text-muted">Birth, Marriage, Death, CENOMAR</small>
                </div>
                <div class="col-md-6">
                    <strong>LCRO Documents:</strong> 
                    <?php if ($documentEligibility['canRequestLCRO']): ?>
                        <span class="badge bg-success">Available</span>
                        <small class="d-block text-muted">CTC, Varied Forms</small>
                    <?php else: ?>
                        <span class="badge bg-warning">Restricted</span>
                        <small class="d-block text-muted">Only for Botolan residents</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?> -->
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
    <img src="images/lcrobot.png" alt="MCRO Logo" class="mcro-logo" style="float: right; margin-left: 10px;">
    <h3>Birth Certificate</h3>
    <p>Birth certificate is a vital record documenting a person's birth, either as the original document or a certified copy</p>
    <div class="document-type-badges mb-2">
        <span class="badge bg-primary">PSA Document</span>
        <?php if ($documentEligibility && $documentEligibility['canRequestLCRO']): ?>
            <span class="badge bg-success">LCRO Available</span>
        <?php endif; ?>
    </div>
    <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#pricingModal" data-document-type="Birth Certificate">Request Now</a>
</div>


    <div class="box">
        <img src="images/marriage1.svg" alt="">
        <img src="images/lcrobot.png" alt="MCRO Logo" class="mcro-logo" style="float: right; margin-left: 10px;">
        <h3>Marriage Certificate</h3>
        <p>Marriage certificate is an official document confirming marriage, issued by a government official after civil registration</p>
        <div class="document-type-badges mb-2">
            <span class="badge bg-primary">PSA Document</span>
            <?php if ($documentEligibility && $documentEligibility['canRequestLCRO']): ?>
                <span class="badge bg-success">LCRO Available</span>
            <?php endif; ?>
        </div>
        <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#pricingModal" data-document-type="Marriage Certificate">Request Now</a>
    </div>

    <div class="box">
        <img src="images/death1.svg" alt="">
        <img src="images/lcrobot.png" alt="MCRO Logo" class="mcro-logo" style="float: right; margin-left: 10px;">
        <h3>Death Certificate</h3>
        <p>A death certificate is a legal document from a medical practitioner or a government civil registration office stating the date, location, and cause of a person's death</p>
        <div class="document-type-badges mb-2">
            <span class="badge bg-primary">PSA Document</span>
            <?php if ($documentEligibility && $documentEligibility['canRequestLCRO']): ?>
                <span class="badge bg-success">LCRO Available</span>
            <?php endif; ?>
        </div>
        <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#pricingModal" data-document-type="Death Certificate">Request Now</a>
    </div>

    <div class="box">
        <img src="images/cen1.svg" alt="">
        <img src="images/lcrobot.png" alt="MCRO Logo" class="mcro-logo" style="float: right; margin-left: 10px;">
        <h3>CENOMAR</h3>
        <p>A Certificate of No Marriage Record (CENOMAR) is simply what its name implies. It is a certification issued by the PSA stating that a person has not contracted any marriage</p>
        <div class="document-type-badges mb-2">
            <span class="badge bg-primary">PSA Document</span>
            <?php if ($documentEligibility && $documentEligibility['canRequestLCRO']): ?>
                <span class="badge bg-success">LCRO Available</span>
            <?php endif; ?>
        </div>
        <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#pricingModal" data-document-type="CENOMAR">Request Now</a>
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

   <div class="container-fluid px-4">
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
            <h3>Local Civil Registry of Botolan</h3>
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

    <h1 class="credit"><a href="https://web.facebook.com/mcrobotolan/?locale=fr_FR&_rdc=1&_rdr">LCRO</a> All Rights Reserve</h1>

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

<!-- Pricing Modal -->
<div class="modal fade" id="pricingModal" tabindex="-1" aria-labelledby="pricingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pricingModalLabel">
          <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
          <span id="modalDocumentTitle">Document Pricing</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="alert alert-info" role="alert">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Please select the type of document you need:</strong>
            </div>
          </div>
        </div>
        
        <div class="row">
          <!-- Original Document Option -->
          <div class="col-md-6 mb-3">
            <div class="card h-100 border-primary">
              <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                  <i class="fas fa-file-alt me-2"></i>Original Document
                </h6>
              </div>
              <div class="card-body">
                <div class="text-center mb-3">
                  <h3 class="text-primary">₱80.00</h3>
                  <small class="text-muted">per copy</small>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="documentType" id="originalDocument" value="original">
                  <label class="form-check-label" for="originalDocument">
                    <strong>Select Original Document</strong>
                  </label>
                </div>
                <hr>
                <div class="form-details">
                  <h6 class="text-muted mb-2">Form Numbers:</h6>
                  <ul class="list-unstyled small">
                    <li id="originalForm102" class="d-none">• Form 102 (Birth Certificate)</li>
                    <li id="originalForm97" class="d-none">• Form 97 (Marriage Certificate)</li>
                    <li id="originalForm103" class="d-none">• Form 103 (Death Certificate)</li>
                    <li id="originalFormCENO" class="d-none">• Form 102 (CENOMAR)</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Transcription Option -->
          <div class="col-md-6 mb-3">
            <div class="card h-100 border-success">
              <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                  <i class="fas fa-file-signature me-2"></i>Transcription
                </h6>
              </div>
              <div class="card-body">
                <div class="text-center mb-3">
                  <h3 class="text-success">₱100.00</h3>
                  <small class="text-muted">per copy</small>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="documentType" id="transcriptionDocument" value="transcription">
                  <label class="form-check-label" for="transcriptionDocument">
                    <strong>Select Transcription</strong>
                  </label>
                </div>
                <hr>
                <div class="form-details">
                  <h6 class="text-muted mb-2">Form Numbers:</h6>
                  <ul class="list-unstyled small">
                    <li id="transcriptionForm1A" class="d-none">• Form 1A (Birth Certificate)</li>
                    <li id="transcriptionForm2A" class="d-none">• Form 2A (Death Certificate)</li>
                    <li id="transcriptionForm3A" class="d-none">• Form 3A (Marriage Certificate)</li>
                    <li id="transcriptionFormCENO" class="d-none">• Form 1A (CENOMAR)</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="row mt-3">
          <div class="col-12">
            <div class="alert alert-warning" role="alert">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>Important:</strong> Please ensure you have all required documents and information before proceeding with your request.
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-2"></i>Cancel
        </button>
        <button type="button" class="btn btn-primary" id="proceedToRequest" disabled>
          <i class="fas fa-arrow-right me-2"></i>Proceed to Request
        </button>
      </div>
    </div>
  </div>
</div>

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

  // Pricing Modal JavaScript
  let currentDocumentType = '';
  let selectedDocumentForm = '';
  let pricingData = {};

  // Load pricing data when page loads
  loadPricingData();
  
  // Refresh profile picture when page loads or when returning from profile page
  refreshProfilePicture();

  // Handle request button clicks
  $('[data-bs-target="#pricingModal"]').click(function(e) {
    e.preventDefault();
    currentDocumentType = $(this).data('document-type');
    $('#modalDocumentTitle').text(currentDocumentType + ' - Pricing');
    
    // Reset form selection
    $('input[name="documentType"]').prop('checked', false);
    $('#proceedToRequest').prop('disabled', true);
    
    // Hide all form details initially
    $('.form-details li').addClass('d-none');
    
    // Show relevant form details based on document type
    showRelevantForms(currentDocumentType);
  });

  // Handle document type selection
  $('input[name="documentType"]').change(function() {
    if ($(this).is(':checked')) {
      selectedDocumentForm = $(this).val();
      $('#proceedToRequest').prop('disabled', false);
    }
  });

  // Handle proceed to request button
  $('#proceedToRequest').click(function() {
    if (selectedDocumentForm && currentDocumentType) {
      // Determine the form URL based on document type
      let formUrl = '';
      switch(currentDocumentType) {
        case 'Birth Certificate':
          formUrl = 'birth_form.php';
          break;
        case 'Marriage Certificate':
          formUrl = 'marriage_form.php';
          break;
        case 'Death Certificate':
          formUrl = 'death_form.php';
          break;
        case 'CENOMAR':
          formUrl = 'ceno_form.php';
          break;
      }
      
      // Add document type and form type as URL parameters
      let url = formUrl + '?type_request=' + encodeURIComponent(currentDocumentType) + 
                '&document_form=' + encodeURIComponent(selectedDocumentForm);
      
      // Close modal and redirect
      $('#pricingModal').modal('hide');
      window.location.href = url;
    }
  });

  // Function to load pricing data from API
  function loadPricingData() {
    $.ajax({
      url: 'api/get_pricing.php',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          pricingData = {};
          response.data.forEach(function(item) {
            if (!pricingData[item.document_type]) {
              pricingData[item.document_type] = {};
            }
            pricingData[item.document_type][item.form_type] = item;
          });
          console.log('Pricing data loaded successfully:', pricingData);
        } else {
          console.error('Failed to load pricing data:', response.message);
        }
      },
      error: function(xhr, status, error) {
        console.error('Error loading pricing data:', error);
      }
    });
  }

  // Function to show relevant forms based on document type
  function showRelevantForms(documentType) {
    // Hide all first
    $('.form-details li').addClass('d-none');
    
    // Update pricing from database
    updatePricingFromDatabase(documentType);
    
    // Show relevant form details
    if (pricingData[documentType]) {
      if (pricingData[documentType]['original']) {
        const originalForm = pricingData[documentType]['original'];
        $('#originalForm102, #originalForm97, #originalForm103, #originalFormCENO').addClass('d-none');
        $(`#originalForm${originalForm.form_number}`).removeClass('d-none');
      }
      
      if (pricingData[documentType]['transcription']) {
        const transcriptionForm = pricingData[documentType]['transcription'];
        $('#transcriptionForm1A, #transcriptionForm2A, #transcriptionForm3A, #transcriptionFormCENO').addClass('d-none');
        $(`#transcriptionForm${transcriptionForm.form_number}`).removeClass('d-none');
      }
    }
  }

  // Function to update pricing from database
  function updatePricingFromDatabase(documentType) {
    if (pricingData[documentType]) {
      // Update original document pricing
      if (pricingData[documentType]['original']) {
        const originalData = pricingData[documentType]['original'];
        $('.card.border-primary .text-primary').text('₱' + originalData.price.toFixed(2));
        $('.card.border-primary .form-check-label strong').text('Select Original Document');
      }
      
      // Update transcription pricing
      if (pricingData[documentType]['transcription']) {
        const transcriptionData = pricingData[documentType]['transcription'];
        $('.card.border-success .text-success').text('₱' + transcriptionData.price.toFixed(2));
        $('.card.border-success .form-check-label strong').text('Select Transcription');
      }
    }
  }

  // Reset modal when closed
  $('#pricingModal').on('hidden.bs.modal', function () {
    $('input[name="documentType"]').prop('checked', false);
    $('#proceedToRequest').prop('disabled', true);
    currentDocumentType = '';
    selectedDocumentForm = '';
  });

  // Function to refresh profile picture
  function refreshProfilePicture() {
    const profileImg = $('#user-profile-picture');
    if (profileImg.length) {
      // Use direct image endpoint with cache-busting
      const newSrc = 'api/serve_profile_picture.php?t=' + Date.now() + '&r=' + Math.random();
      
      // Create new image element
      const newImg = $('<img>', {
        src: newSrc,
        alt: 'User Profile Picture',
        width: '32',
        height: '32',
        class: 'rounded-circle',
        style: 'object-fit: cover; max-width: 32px; max-height: 32px;',
        id: 'user-profile-picture'
      });
      
      // Replace the old image with the new one
      profileImg.replaceWith(newImg);
      
      console.log('Profile picture refreshed with direct endpoint:', newSrc);
    }
  }

  // Refresh profile picture when page becomes visible (user returns from profile page)
  document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
      refreshProfilePicture();
    }
  });

  // Also refresh when page gains focus
  window.addEventListener('focus', function() {
    refreshProfilePicture();
  });

  // Force refresh on page load to clear any cached images
  $(document).ready(function() {
    // Immediate refresh on page load
    refreshProfilePicture();
    
    // Also refresh after a short delay
    setTimeout(function() {
      refreshProfilePicture();
    }, 1000);
    
  });

  // Nuclear option: completely replace the image container
  function nuclearProfilePictureRefresh() {
    const newSrc = 'api/serve_profile_picture.php?t=' + Date.now() + '&r=' + Math.random() + '&nuclear=' + Date.now();
    
    // Find the dropdown container
    const dropdown = $('.dropdown.text-end');
    if (dropdown.length) {
      // Replace the entire dropdown content
      const newContent = `
        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="${newSrc}" alt="User Profile Picture" width="32" height="32" class="rounded-circle" style="object-fit: cover; max-width: 32px; max-height: 32px;" id="user-profile-picture">
        </a>
        <ul class="dropdown-menu text-small">
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="user_profile.php">Profile</a></li>
          <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
        </ul>
      `;
      
      dropdown.html(newContent);
      console.log('Nuclear profile picture refresh completed:', newSrc);
    }
  }
</script>


</body>
</html>

 