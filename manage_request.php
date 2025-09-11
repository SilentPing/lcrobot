<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit;
}

// Check if user is admin
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit;
}

include('includes/header.php'); 
include('includes/navbar.php');
?>


<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-file-text-fill"></i> Civil Requests</h6>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#walkinModal">
                <i class="bi bi-person-plus-fill"></i> Walk-in Request
            </button>
        </div>

        <style>
            .approved-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 100px; /* Adjust the width as needed */
            height: 100px; /* Adjust the height as needed */
            z-index: 9999; /* Ensure it's above other elements */
        }

         .reject-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 140px; /* Adjust the width as needed */
            height: 140px; /* Adjust the height as needed */
            z-index: 9999; /* Ensure it's above other elements */
        }

        /* Modern Action Buttons */
        .modern-action-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            min-width: 100px;
        }

        .modern-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .modern-action-btn:active {
            transform: translateY(0);
        }

        .modern-action-btn i {
            font-size: 16px;
        }

        .modern-action-btn .btn-text {
            font-size: 13px;
        }

        /* Button Colors */
        .modern-action-btn.btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .modern-action-btn.btn-success:hover {
            background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
            color: white;
        }

        .modern-action-btn.btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .modern-action-btn.btn-danger:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
            color: white;
        }

        .modern-action-btn.btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }

        .modern-action-btn.btn-info:hover {
            background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
            color: white;
        }

        /* Mobile Responsiveness for Action Buttons */
        @media (max-width: 768px) {
            .modern-action-btn {
                min-width: 80px;
                padding: 6px 12px;
                font-size: 12px;
            }

            .modern-action-btn i {
                font-size: 14px;
            }

            .modern-action-btn .btn-text {
                font-size: 11px;
            }
        }

        @media (max-width: 576px) {
            .modern-action-btn {
                min-width: 70px;
                padding: 5px 10px;
                gap: 4px;
            }

            .modern-action-btn .btn-text {
                display: none;
            }

            .modern-action-btn i {
                font-size: 16px;
            }
        }

        /* Mobile Responsiveness for Walk-in Modal */
        @media (max-width: 767.98px) {
            .modal-dialog {
                margin: 0.5rem !important;
                max-width: calc(100% - 1rem) !important;
            }
            
            .modal-content {
                border-radius: 0.5rem !important;
            }
            
            .modal-header {
                padding: 0.75rem 1rem !important;
            }
            
            .modal-title {
                font-size: 1.1rem !important;
            }
            
            .modal-body {
                padding: 1rem !important;
            }
            
            .modal-footer {
                padding: 0.75rem 1rem !important;
                flex-direction: column !important;
                gap: 0.5rem !important;
            }
            
            .modal-footer .btn {
                width: 100% !important;
                margin: 0 !important;
            }
            
            /* Mobile button styling */
            .d-grid .btn {
                padding: 0.75rem 1rem !important;
                font-size: 0.9rem !important;
                border-radius: 0.375rem !important;
            }
            
            /* Form container mobile styling */
            #formContainer {
                margin-top: 1rem !important;
            }
            
            #formContainer .form-control,
            #formContainer .form-select {
                font-size: 0.9rem !important;
                padding: 0.5rem 0.75rem !important;
            }
            
            #formContainer .form-label {
                font-size: 0.85rem !important;
                margin-bottom: 0.25rem !important;
            }
        }

        /* Tablet responsiveness */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .modal-dialog {
                max-width: 90% !important;
            }
            
            .btn-group .btn {
                font-size: 0.85rem !important;
                padding: 0.5rem 0.75rem !important;
            }
        }
        </style>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead thead-light>
                        <tr>
                            <th>#</th>
                            <th>Registration Date</th>
                            <th>Name</th>
                            <th>Type of Request</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Load database connection and encryption functions
                        require_once __DIR__ . '/db.php';

                            if (isset($_POST['send_sms_btn'])) {
                                $approvedRequestId = $_POST['request_id'];

                                // Update status in the original table
                                $queryUpdateStatus = "UPDATE reqtracking_tbl SET status = 'Approved' WHERE request_id = $approvedRequestId";
                                $queryUpdateStatusRun = mysqli_query($conn, $queryUpdateStatus);

                                if (!$queryUpdateStatusRun) {
                                    die("Query to update status failed: " . mysqli_error($conn));
                                }

                                // Fetch the approved request details from the original table
                                $queryFetchApprovedRequest = "SELECT * FROM reqtracking_tbl WHERE request_id = $approvedRequestId";
                                $resultFetchApprovedRequest = mysqli_query($conn, $queryFetchApprovedRequest);

                                if (!$resultFetchApprovedRequest) {
                                    die("Query to fetch approved request details failed: " . mysqli_error($conn));
                                }
                                // Delete the approved request from the original table
                                $queryDeleteOriginalRequest = "DELETE FROM reqtracking_tbl WHERE request_id = $approvedRequestId";
                                $queryDeleteOriginalRequestRun = mysqli_query($conn, $queryDeleteOriginalRequest);

                                if (!$queryDeleteOriginalRequestRun) {
                                    die("Query to delete approved request from the original table failed: " . mysqli_error($conn));
                                }

                                // Insert the approved request into the separate table
                                if ($rowApprovedRequest = mysqli_fetch_assoc($resultFetchApprovedRequest)) {
                                    // Get the correct contact information using the same logic as the display
                                    $id_user = $rowApprovedRequest['user_id'];
                                    
                                    // Check if this is a walk-in request (has contact info in reqtracking_tbl)
                                    if (!empty($rowApprovedRequest['contact_no'])) {
                                        // Walk-in request - use contact info from reqtracking_tbl
                                        $contact_no = $rowApprovedRequest['contact_no'];
                                        $email = !empty($rowApprovedRequest['email']) ? $rowApprovedRequest['email'] : '';
                                    } else {
                                        // Regular user request - get contact info from users table
                                        $query1 = "SELECT * FROM users WHERE id_user = $id_user";
                                        $query_run1 = mysqli_query($conn, $query1);
                                        $row1 = mysqli_fetch_assoc($query_run1);
                                        $contact_no = $row1['contact_no'] ?? '';
                                        $email = !empty($row1['email']) ? $row1['email'] : '';
                                    }
                                    
                                    $queryInsertApprovedRequest = "INSERT INTO approved_requests (registration_date, registrar_name, type_request, status, contact_no, email, user_id)
                                    VALUES ('{$rowApprovedRequest['registration_date']}', '{$rowApprovedRequest['registrar_name']}', '{$rowApprovedRequest['type_request']}', 'Approved', '$contact_no', '$email', '{$rowApprovedRequest['user_id']}')";
                                    $queryInsertApprovedRequestRun = mysqli_query($conn, $queryInsertApprovedRequest);

                                    if (!$queryInsertApprovedRequestRun) {
                                        die("Query to insert the approved request failed: " . mysqli_error($conn));
                                    }
                                }

                                // Add your code to send SMS notification
                                include('sms.php');
                            }

                        // Check if the connection is successful
                        if (!$conn) {
                            die("Connection failed: " . mysqli_connect_error());
                        }

                        $query = "SELECT * FROM reqtracking_tbl WHERE status != 'Approved'";
                        $query_run = mysqli_query($conn, $query);

                        // Check if the query was executed successfully
                        if (!$query_run) {
                            die("Query failed: " . mysqli_error($conn));
                        }

                        $row_number = 1;

                        if (mysqli_num_rows($query_run) > 0) {
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                $id_user = $row['user_id'];
                                
                                // Check if this is a walk-in request (has contact info in reqtracking_tbl)
                                if (!empty($row['contact_no'])) {
                                    // Walk-in request - use contact info from reqtracking_tbl
                                    $contact_no = $row['contact_no'];
                                    $email = !empty($row['email']) ? $row['email'] : 'Not provided';
                                } else {
                                    // Regular user request - get contact info from users table
                                    $query1 = "SELECT * FROM users WHERE id_user = $id_user";
                                    $query_run1 = mysqli_query($conn, $query1);
                                    $row1 = mysqli_fetch_assoc($query_run1);
                                    $contact_no = $row1['contact_no'] ?? '';
                                    $email = !empty($row1['email']) ? $row1['email'] : 'Not provided';
                                }
                                ?>
                                <tr>
                                    <td><?php echo $row_number; ?></td>
                                    <td><?php echo $row['registration_date']; ?></td>
                                    <td><?php echo $row['registrar_name']; ?></td>
                                    <td><?php echo $row['type_request']; ?></td>
                                    <td><?php echo $contact_no; ?></td>
                                    <td><?php echo $email; ?></td>
                                    <td><?php echo $row['status']; ?></td>
                                   <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                        <button type="submit" name="send_sms_btn" class="btn btn-success approve-button modern-action-btn" data-bs-toggle="modal" data-bs-target="#approveModal_<?php echo $row['request_id']; ?>" title="Approve Request">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="btn-text">Approve</span>
                                        </button>
                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal_<?php echo $row['request_id']; ?>" tabindex="-1" aria-labelledby="approveModalLabel_<?php echo $row['request_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="approveModalLabel_<?php echo $row['request_id']; ?>">Approve Request</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- Content for the approve modal -->
                                                        <img src="images/approve.png" alt="Approved Badge" class="approved-badge">
                                                        <p><strong>Registrant Name:</strong> <?php echo $row['registrar_name']; ?></p>
                                                        <p><strong>Type of Request:</strong> <?php echo $row['type_request']; ?></p>
                                                        <p><strong>Contact Number:</strong> <?php echo $contact_no; ?></p>
                                                        <input type="hidden" name="contact_no" value="<?php echo $contact_no; ?>">
                                                      <textarea class="form-control" rows="4" name="sms_message" placeholder="Enter SMS message"><?php
                                                      echo "Good Day " . $row['registrar_name'] . "! Your Request Has Been Successfully Approved, Civil Documents Requested " . $row['type_request'] . "! Please note that the estimated waiting time for processing is 14 working days.\n";

                                                      echo "We will notify you as soon as the requested civil documents arrive at the MCRO Office.\n\n";
                                                      echo "Sincerely,\n";
                                                      echo "THE MCRO BOTOLAN TEAM";
                                                    ?>
                                                    </textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary" name="send_sms_btn">Send</button>
                                                    </div>
                                                 </div>
                                              </div>
                                          </div>
                                       </form>

                                    <form action="request_reject.php" method="post">
                                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                            <button type="button" name="reject_btn" class="btn btn-danger modern-action-btn" data-bs-toggle="modal" data-bs-target="#rejectModal_<?php echo $row['request_id']; ?>" title="Reject Request">
                                                <i class="fas fa-times-circle"></i>
                                                <span class="btn-text">Reject</span>
                                            </button>
                                            <div class="modal fade" id="rejectModal_<?php echo $row['request_id']; ?>" tabindex="-1" aria-labelledby="rejectModalLabel_<?php echo $row['request_id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="rejectModalLabel_<?php echo $row['request_id']; ?>">Reject Request</h1>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Content for the reject modal -->
                                                            <img src="images/reject1.png" alt="Rejected Badge" class="reject-badge">
                                                            <p><strong>Registrant Name:</strong> <?php echo $row['registrar_name']; ?></p>
                                                            <p><strong>Type of Request:</strong> <?php echo $row['type_request']; ?></p>
                                                            <input type="hidden" name="registrarName" value="<?php echo $row['registrar_name']; ?>">
                                                            <input type="hidden" name="typeOfRequest" value="<?php echo $row['type_request']; ?>">
                                                            <p><strong>Email:</strong> <?php echo $email; ?></p>
                                                            <input type="hidden" name="email" value="<?php echo $email; ?>">
                                                            <textarea class="form-control" rows="4" name="rejectionReason" placeholder="Enter Rejection message">Your request has been rejected.</textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary" name="rejectEmail">Send</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                        <?php
                                        // Dynamic routing for verification based on request type
                                        $verifyUrl = '';
                                        switch($row['type_request']) {
                                            case 'Birth Certificate':
                                                $verifyUrl = 'birth_verify_request.php';
                                                break;
                                            case 'Marriage Certificate':
                                                $verifyUrl = 'marriage_verify_request.php';
                                                break;
                                            case 'CENOMAR':
                                                $verifyUrl = 'birth_verify_request.php'; // CENOMAR uses same table as Birth
                                                break;
                                            case 'Death Certificate':
                                                $verifyUrl = 'death_verify_request.php';
                                                break;
                                            default:
                                                $verifyUrl = 'birth_verify_request.php'; // Default fallback
                                                break;
                                        }
                                        ?>
                                        <a href="<?php echo $verifyUrl; ?>?type=<?php echo $row['type_request']; ?>&id_user=<?php echo $row['user_id']; ?>" class="btn btn-info modern-action-btn" title="Verify Request">
                                            <i class="fas fa-eye"></i>
                                            <span class="btn-text">Verify</span>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                $row_number++;
                            }
                        } else {

                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to handle form submission
    function handleFormSubmit(requestId) {
        // Get the form data
        var formData = new FormData(document.getElementById('form_' + requestId));

        // Perform AJAX submission
        $.ajax({
            type: 'POST',
            url: 'manage_request.php', // Correct the file name to process the approval logic
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // Handle the response if needed
                console.log(response);

                // Close the modal after sending SMS
                $('#approveModal_' + requestId).modal('hide');

                // You can add additional logic here if needed
            }
        });
    }

    // Add click event listener to "Send" button inside the modal
    $('.approve-button').on('click', function(event) {
        event.preventDefault(); // Prevent the default form submission behavior

        var requestId = $(this).data('request-id');

        // Open the modal
        $('#approveModal_' + requestId).modal('show');

        // Add click event listener to the "Submit" button inside the modal
        $('#submitBtn_' + requestId).on('click', function() {
            // Call the function to handle form submission
            handleFormSubmit(requestId);
        });
    });
});
</script>

<!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MCRO 2023</span>
                    </div>
                </div>
            </footer>

    <?php
// Check if the Send SMS button is clicked within the modal
// if (isset($_POST['send_sms_btn'])) {
//     // Retrieve the phone number of the registrant from the database or wherever it's stored
//     $phone_number = $_POST['contact_no']; // Replace this with the appropriate phone number retrieval method

//     // Retrieve the SMS message from the form textarea
//     $sms_message = $_POST['sms_message'];

//     // Your Semaphore API Key
//     $api_key = '3b0a653cc759c73537ac5e57bf133e8c'; // Replace this with your Semaphore API key

//     // Semaphore API Endpoint
//     $api_url = 'https://semaphore.co/api/v4/messages';

//     // Sender Name (Optional)
//     $sender_name = 'BOTOLANMCRO'; // Replace this with your desired sender name

//     // Initialize cURL session
//     $ch = curl_init();

//     // Set parameters for sending SMS
//     $parameters = array(
//         'apikey' => $api_key,
//         'number' => $phone_number,
//         'message' => $sms_message,
//         'sendername' => $sender_name
//     );

//     // Set cURL options
//     curl_setopt($ch, CURLOPT_URL, $api_url);
//     curl_setopt($ch, CURLOPT_POST, 1);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//     // Execute cURL request
//     $output = curl_exec($ch);

//     // Check for errors and handle the response from Semaphore
//     if ($output === FALSE) {
//         echo "Error: " . curl_error($ch);
//     } else {
//         // Handle the response from Semaphore (you might want to log or process this response
//             echo "<script>
//             Swal.fire({
//               title: 'Message sent!',
//               text: 'Message sent to $phone_number',
//               icon: 'success',
//               confirmButtonText: 'OK'
//             }).then(() => {
//               window.location.href = 'manage_request.php';
//             });
//             </script>"; " . $output . <br>";

//                 }

//     // Close cURL session
//     curl_close($ch);
// }
?>

<!-- Walk-in Request Modal -->
<div class="modal fade" id="walkinModal" tabindex="-1" aria-labelledby="walkinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="walkinModalLabel">
                    <i class="bi bi-person-plus-fill"></i> Walk-in Request Form
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form Type Selection -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="mb-3">Select Request Type:</h6>
                        <!-- Desktop: Horizontal button group -->
                        <div class="btn-group w-100 d-none d-md-flex" role="group">
                            <input type="radio" class="btn-check" name="requestType" id="birthType" value="birth" autocomplete="off">
                            <label class="btn btn-outline-primary" for="birthType">
                                <i class="bi bi-baby"></i> Birth Certificate
                            </label>

                            <input type="radio" class="btn-check" name="requestType" id="cenoType" value="ceno" autocomplete="off">
                            <label class="btn btn-outline-primary" for="cenoType">
                                <i class="bi bi-heart"></i> CENOMAR
                            </label>

                            <input type="radio" class="btn-check" name="requestType" id="deathType" value="death" autocomplete="off">
                            <label class="btn btn-outline-primary" for="deathType">
                                <i class="bi bi-cross"></i> Death Certificate
                            </label>

                            <input type="radio" class="btn-check" name="requestType" id="marriageType" value="marriage" autocomplete="off">
                            <label class="btn btn-outline-primary" for="marriageType">
                                <i class="bi bi-heart-fill"></i> Marriage Certificate
                            </label>
                        </div>
                        
                        <!-- Mobile: Vertical button group -->
                        <div class="d-md-none">
                            <div class="d-grid gap-2">
                                <input type="radio" class="btn-check" name="requestType" id="birthTypeMobile" value="birth" autocomplete="off">
                                <label class="btn btn-outline-primary" for="birthTypeMobile">
                                    <i class="bi bi-baby"></i> Birth Certificate
                                </label>

                                <input type="radio" class="btn-check" name="requestType" id="cenoTypeMobile" value="ceno" autocomplete="off">
                                <label class="btn btn-outline-primary" for="cenoTypeMobile">
                                    <i class="bi bi-heart"></i> CENOMAR
                                </label>

                                <input type="radio" class="btn-check" name="requestType" id="deathTypeMobile" value="death" autocomplete="off">
                                <label class="btn btn-outline-primary" for="deathTypeMobile">
                                    <i class="bi bi-cross"></i> Death Certificate
                                </label>

                                <input type="radio" class="btn-check" name="requestType" id="marriageTypeMobile" value="marriage" autocomplete="off">
                                <label class="btn btn-outline-primary" for="marriageTypeMobile">
                                    <i class="bi bi-heart-fill"></i> Marriage Certificate
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Form Container -->
                <div id="formContainer">
                    <div class="text-center text-muted">
                        <i class="bi bi-arrow-up-circle fs-1"></i>
                        <p>Please select a request type above to load the form</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="submitWalkinRequest" disabled>
                    <i class="bi bi-check-circle"></i> Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/script.php');
include('includes/footer.php');
?>

<script>
// Wait for jQuery to be loaded
$(document).ready(function() {
    console.log('jQuery is loaded and ready!');
    
    // Handle form type selection for both desktop and mobile
    $('input[name="requestType"]').change(function() {
        var requestType = $(this).val();
        console.log('Form type changed to:', requestType);
        
        // Sync mobile and desktop selections
        if ($(this).attr('id').includes('Mobile')) {
            // If mobile button clicked, also check desktop button
            var desktopId = $(this).attr('id').replace('Mobile', '');
            $('#' + desktopId).prop('checked', true);
        } else {
            // If desktop button clicked, also check mobile button
            var mobileId = $(this).attr('id') + 'Mobile';
            $('#' + mobileId).prop('checked', true);
        }
        
        loadWalkinForm(requestType);
    });

    function loadWalkinForm(type) {
        console.log('Loading form for type:', type);
        $('#formContainer').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        
        $.ajax({
            url: 'get_walkin_form.php',
            type: 'POST',
            data: { formType: type },
            success: function(response) {
                console.log('Form loaded successfully:', response);
                $('#formContainer').html(response);
                $('#submitWalkinRequest').prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                $('#formContainer').html('<div class="alert alert-danger">Error loading form. Please try again.<br>Error: ' + error + '</div>');
            }
        });
    }

    // Handle form submission
    $('#submitWalkinRequest').click(function() {
        // Validate form before submission
        if (!validateWalkinForm()) {
            return false;
        }
        
        // Show review modal before submission
        showReviewModal();
    });
    
    function validateWalkinForm() {
        var form = $('#walkinForm');
        var formType = $('input[name="form_type"]').val();
        var errors = [];
        
        // Get all required fields based on form type
        var requiredFields = getRequiredFields(formType);
        
        // Check each required field
        requiredFields.forEach(function(field) {
            var input = form.find('[name="' + field.name + '"]');
            var value = input.val();
            
            if (!value || value.trim() === '') {
                errors.push(field.label + ' is required');
            }
        });
        
        // Additional validations
        if (formType === 'birth' || formType === 'ceno') {
            // Validate date of birth
            var dob = form.find('[name="dob"]').val();
            if (dob) {
                var birthDate = new Date(dob);
                var today = new Date();
                if (birthDate > today) {
                    errors.push('Date of Birth cannot be in the future');
                }
            }
        }
        
        if (formType === 'death') {
            // Validate death date
            var dod = form.find('[name="dod"]').val();
            var dob = form.find('[name="dob"]').val();
            if (dod && dob) {
                var deathDate = new Date(dod);
                var birthDate = new Date(dob);
                if (deathDate < birthDate) {
                    errors.push('Date of Death cannot be before Date of Birth');
                }
            }
        }
        
        // Show errors if any
        if (errors.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'All Fields are Required',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        return true;
    }
    
    function getRequiredFields(formType) {
        var fields = [];
        
        switch(formType) {
            case 'birth':
            case 'ceno':
                fields = [
                    {name: 'lastname', label: 'Last Name'},
                    {name: 'firstname', label: 'First Name'},
                    {name: 'middlename', label: 'Middle Name'},
                    {name: 'pob_country', label: 'Place of Birth (Country)'},
                    {name: 'pob_province', label: 'Place of Birth (Province)'},
                    {name: 'pob_municipality', label: 'Place of Birth (City/Municipality)'},
                    {name: 'dob', label: 'Date of Birth'},
                    {name: 'sex', label: 'Sex'},
                    {name: 'relationship', label: 'Relationship to Document Owner'},
                    {name: 'fath_ln', label: 'Father\'s Last Name'},
                    {name: 'fath_fn', label: 'Father\'s First Name'},
                    {name: 'fath_mn', label: 'Father\'s Middle Name'},
                    {name: 'moth_maiden_ln', label: 'Mother\'s Maiden Last Name'},
                    {name: 'moth_maiden_fn', label: 'Mother\'s Maiden First Name'},
                    {name: 'moth_maiden_mn', label: 'Mother\'s Maiden Middle Name'},
                    {name: 'purpose_of_request', label: 'Purpose of Request'},
                    {name: 'applicant_name', label: 'Applicant Full Name'},
                    {name: 'contact_no', label: 'Contact Number'}
                ];
                break;
            case 'death':
                fields = [
                    {name: 'deceased_ln', label: 'Deceased Last Name'},
                    {name: 'deceased_fn', label: 'Deceased First Name'},
                    {name: 'deceased_mn', label: 'Deceased Middle Name'},
                    {name: 'dob', label: 'Date of Birth'},
                    {name: 'dod', label: 'Date of Death'},
                    {name: 'place_of_death', label: 'Place of Death'},
                    {name: 'purpose_of_request', label: 'Purpose of Request'},
                    {name: 'applicant_name', label: 'Applicant Full Name'},
                    {name: 'contact_no', label: 'Contact Number'}
                ];
                break;
            case 'marriage':
                fields = [
                    {name: 'husband_ln', label: 'Husband Last Name'},
                    {name: 'husband_fn', label: 'Husband First Name'},
                    {name: 'husband_mn', label: 'Husband Middle Name'},
                    {name: 'maiden_wife_ln', label: 'Maiden Wife Last Name'},
                    {name: 'maiden_wife_fn', label: 'Maiden Wife First Name'},
                    {name: 'maiden_wife_mn', label: 'Maiden Wife Middle Name'},
                    {name: 'pob_country', label: 'Place of Birth (Country)'},
                    {name: 'pob_province', label: 'Place of Birth (Province)'},
                    {name: 'pob_municipality', label: 'Place of Birth (City/Municipality)'},
                    {name: 'dob', label: 'Date of Birth'},
                    {name: 'place_of_marriage', label: 'Place of Marriage'},
                    {name: 'purpose_of_request', label: 'Purpose of Request'},
                    {name: 'applicant_name', label: 'Applicant Full Name'},
                    {name: 'contact_no', label: 'Contact Number'}
                ];
                break;
        }
        
        return fields;
    }
    
    function showReviewModal() {
        var form = $('#walkinForm');
        var formType = $('input[name="form_type"]').val();
        var formData = form.serializeArray();
        var reviewContent = '<div class="text-left">';
        
        // Create review content based on form type
        reviewContent += '<h6><strong>Request Type:</strong> ' + getFormTypeName(formType) + '</h6><hr>';
        
        formData.forEach(function(field) {
            if (field.value && field.name !== 'form_type' && field.name !== 'id_user') {
                var label = getFieldLabel(field.name, formType);
                reviewContent += '<p><strong>' + label + ':</strong> ' + field.value + '</p>';
            }
        });
        
        reviewContent += '</div>';
        
        Swal.fire({
            title: 'Review Request Details',
            html: reviewContent,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Submit Request',
            cancelButtonText: 'Edit Details',
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                submitWalkinRequest();
            }
        });
    }
    
    function getFormTypeName(formType) {
        switch(formType) {
            case 'birth': return 'Birth Certificate';
            case 'ceno': return 'CENOMAR';
            case 'death': return 'Death Certificate';
            case 'marriage': return 'Marriage Certificate';
            default: return formType;
        }
    }
    
    function getFieldLabel(fieldName, formType) {
        var labels = {
            'lastname': 'Last Name',
            'firstname': 'First Name',
            'middlename': 'Middle Name',
            'pob_country': 'Place of Birth (Country)',
            'pob_province': 'Place of Birth (Province)',
            'pob_municipality': 'Place of Birth (City/Municipality)',
            'dob': 'Date of Birth',
            'sex': 'Sex',
            'relationship': 'Relationship to Document Owner',
            'fath_ln': 'Father\'s Last Name',
            'fath_fn': 'Father\'s First Name',
            'fath_mn': 'Father\'s Middle Name',
            'moth_maiden_ln': 'Mother\'s Maiden Last Name',
            'moth_maiden_fn': 'Mother\'s Maiden First Name',
            'moth_maiden_mn': 'Mother\'s Maiden Middle Name',
            'purpose_of_request': 'Purpose of Request',
            'deceased_ln': 'Deceased Last Name',
            'deceased_fn': 'Deceased First Name',
            'deceased_mn': 'Deceased Middle Name',
            'dod': 'Date of Death',
            'place_of_death': 'Place of Death',
            'husband_ln': 'Husband Last Name',
            'husband_fn': 'Husband First Name',
            'husband_mn': 'Husband Middle Name',
            'maiden_wife_ln': 'Maiden Wife Last Name',
            'maiden_wife_fn': 'Maiden Wife First Name',
            'maiden_wife_mn': 'Maiden Wife Middle Name',
            'place_of_marriage': 'Place of Marriage',
            'applicant_name': 'Applicant Full Name',
            'contact_no': 'Contact Number',
            'email': 'Email Address'
        };
        
        return labels[fieldName] || fieldName;
    }
    
    function submitWalkinRequest() {
        var formData = $('#walkinForm').serialize();
        
        // Show loading
        Swal.fire({
            title: 'Submitting Request...',
            text: 'Please wait while we process your request',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: 'submit_walkin_request.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Walk-in request submitted successfully!',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        $('#walkinModal').modal('hide');
                        location.reload(); // Refresh the page to show new request
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to submit request'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to submit request. Please try again.'
                });
            }
        });
    }
});
</script>
