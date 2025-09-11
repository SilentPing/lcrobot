<?php
include('includes/header.php'); 
include('includes/navbar.php');
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success"><i class="bi bi-check-circle-fill"></i> Approved Civil Requests</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="approvedDataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Registration Date</th>
                            <th>Registrar Name</th>
                            <th>Type of Request</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>QR Code</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                         require_once __DIR__ . '/db.php';
                         
                        // Get approved requests from approved_requests table (not yet released)
                        $query = "SELECT * FROM approved_requests WHERE released_date IS NULL OR released_date = ''";
                        $query_run = mysqli_query($conn, $query);

                        if ($query_run && mysqli_num_rows($query_run) > 0) {
                            $row_number = 1;
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                // Get contact information directly from approved_requests table
                                $contact_no = $row['contact_no'] ?: 'Not available';
                                $email = !empty($row['email']) ? $row['email'] : 'Not provided';
                                ?>
                                <tr>
                                    <td><?php echo $row_number; ?></td>
                                    <td><?php echo $row['registration_date']; ?></td>
                                    <td><?php echo $row['registrar_name']; ?></td>
                                    <td><?php echo $row['type_request']; ?></td>
                                    <td><?php echo $contact_no; ?></td>
                                    <td><?php echo $email; ?></td>
                                    <td><span class="badge bg-success"><?php echo $row['status']; ?></span></td>
                                    <td>
                                        <?php if (!empty($row['qr_reference'])): ?>
                                            <div class="d-flex flex-column align-items-center">
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=<?php echo urlencode($row['qr_reference']); ?>" 
                                                     alt="QR Code" class="mb-1" style="width: 60px; height: 60px;">
                                                <small class="text-muted"><?php echo $row['qr_reference']; ?></small>
                                                <div class="btn-group btn-group-sm mt-1">
                                                    <button class="btn btn-outline-primary btn-sm" onclick="printQRReceipt('<?php echo $row['qr_reference']; ?>')" title="Print Receipt">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info btn-sm" onclick="regenerateQR('<?php echo $row['request_id']; ?>')" title="Regenerate QR">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <button class="btn btn-success btn-sm" onclick="generateQR('<?php echo $row['request_id']; ?>')">
                                                <i class="fas fa-qrcode me-1"></i>
                                                Generate QR
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['qr_reference'])): ?>
                                            <button type="button" class="btn btn-success btn-sm" onclick="verifyAndRelease('<?php echo $row['qr_reference']; ?>', '<?php echo $row['request_id']; ?>', '<?php echo htmlspecialchars($row['registrar_name']); ?>', '<?php echo htmlspecialchars($row['type_request']); ?>', '<?php echo htmlspecialchars($contact_no); ?>', '<?php echo htmlspecialchars($email); ?>')" title="Verify QR Code & Release Document">
                                                <i class="fas fa-qrcode me-1"></i>
                                                Verify & Release
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#releaseModal_<?php echo $row['request_id']; ?>">
                                                <i class="bi bi-box-arrow-up"></i> Release
                                            </button>
                                        <?php endif; ?>
                                        
                                        <!-- Release Modal -->
                                        <div class="modal fade" id="releaseModal_<?php echo $row['request_id']; ?>" tabindex="-1" aria-labelledby="releaseModalLabel_<?php echo $row['request_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="releaseModalLabel_<?php echo $row['request_id']; ?>">
                                                            <i class="bi bi-box-arrow-up"></i> Release Document
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-info">
                                                            <i class="bi bi-info-circle"></i> <strong>Document Ready for Release</strong>
                                                        </div>
                                                        <p><strong>Registrant Name:</strong> <?php echo $row['registrar_name']; ?></p>
                                                        <p><strong>Type of Request:</strong> <?php echo $row['type_request']; ?></p>
                                                        <p><strong>Contact Number:</strong> <?php echo $contact_no; ?></p>
                                                        <p><strong>Email:</strong> <?php echo $email; ?></p>
                                                        
                                                        <?php if ($contact_no == 'Not available' || $email == 'Not provided'): ?>
                                                        <div class="alert alert-warning">
                                                            <i class="bi bi-exclamation-triangle"></i> 
                                                            <strong>Contact Information Missing:</strong> Contact details are not available. 
                                                            Please contact the applicant directly or update the contact details before releasing.
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <form id="releaseForm_<?php echo $row['request_id']; ?>">
                                                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                            <input type="hidden" name="contact_no" value="<?php echo $contact_no; ?>">
                                                            <input type="hidden" name="registrar_name" value="<?php echo $row['registrar_name']; ?>">
                                                            <input type="hidden" name="type_request" value="<?php echo $row['type_request']; ?>">
                                                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="released_by" class="form-label">Released By:</label>
                                                                <input type="text" class="form-control" id="released_by" name="released_by" required placeholder="Enter your name">
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="release_sms_message" class="form-label">SMS Message:</label>
                                                                <textarea class="form-control" id="release_sms_message" name="sms_message" rows="4" required><?php
                                                                echo "Good Day " . $row['registrar_name'] . "!\n";
                                                                echo "Your requested Civil Document (" . $row['type_request'] . ") is now ready for claiming at the MCRO Office.\n";
                                                                echo "Please bring:\n";
                                                                echo "- Valid ID\n";
                                                                echo "- Exact Amount of Payment\n";
                                                                echo "- Reference number: " . $row['request_id'] . "\n";
                                                                echo "Office Hours: 8:00 AM - 5:00 PM\n";
                                                                echo "Contact: [Office Number]\n\n";
                                                                echo "Thank you!\n";
                                                                echo "LCRO Botolan";
                                                                ?></textarea>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="button" class="btn btn-primary" onclick="releaseDocument(<?php echo $row['request_id']; ?>)">
                                                            <i class="bi bi-box-arrow-up"></i> Release & Send SMS
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                $row_number++;
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>";
                            include('includes/no_data_component.php');
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MCRO 2023</span>
                    </div>
                </div>
            </footer>

<script>
// Test function to check if JavaScript is working
function testFunction(qrCode) {
    alert('Test function works! QR: ' + qrCode);
}

function releaseDocument(requestId) {
    const form = document.getElementById('releaseForm_' + requestId);
    const formData = new FormData(form);
    
    // Validate form
    if (!formData.get('released_by') || !formData.get('sms_message')) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please fill in all required fields'
        });
        return;
    }
    
    // Check if contact information is available
    const contactNo = formData.get('contact_no');
    if (contactNo === 'Not available' || contactNo === 'Walk-in Request') {
        Swal.fire({
            icon: 'warning',
            title: 'Contact Information Missing',
            text: 'Contact information is not available. SMS cannot be sent. Do you want to continue with the release?',
            showCancelButton: true,
            confirmButtonText: 'Yes, Release Anyway',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                proceedWithRelease(formData, requestId);
            }
        });
        return;
    }
    
    proceedWithRelease(formData, requestId);
}

function proceedWithRelease(formData, requestId) {
    // Show loading
    Swal.fire({
        title: 'Releasing Document...',
        text: 'Please wait while we process the release',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Send AJAX request
    fetch('process_release.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const smsMessage = data.sms_sent ? ' and SMS notification sent successfully!' : ' but SMS could not be sent.';
            Swal.fire({
                icon: 'success',
                title: 'Document Released!',
                text: 'Document has been released' + smsMessage,
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                // Close modal and reload page
                $('#releaseModal_' + requestId).modal('hide');
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Release Failed',
                text: data.message || 'Failed to release document. Please try again.'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while releasing the document.'
        });
    });
}

// QR Code Management Functions
function generateQR(requestId) {
    Swal.fire({
        title: 'Generate QR Code',
        text: 'Are you sure you want to generate a QR code for this document?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Generate!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Generating QR Code...',
                text: 'Please wait while we generate the QR code',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Send AJAX request
            fetch('api/generate_qr_code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    request_id: requestId,
                    action: 'generate'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'QR Code Generated!',
                        text: 'QR code has been generated successfully',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Generation Failed',
                        text: data.message || 'Failed to generate QR code. Please try again.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while generating the QR code.'
                });
            });
        }
    });
}

function regenerateQR(requestId) {
    Swal.fire({
        title: 'Regenerate QR Code',
        text: 'This will invalidate the current QR code and generate a new one. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Regenerate!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Regenerating QR Code...',
                text: 'Please wait while we regenerate the QR code',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Send AJAX request
            fetch('api/generate_qr_code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    request_id: requestId,
                    action: 'regenerate'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'QR Code Regenerated!',
                        text: 'New QR code has been generated successfully',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Regeneration Failed',
                        text: data.message || 'Failed to regenerate QR code. Please try again.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while regenerating the QR code.'
                });
            });
        }
    });
}

function printQRReceipt(reference) {
    window.open(`api/print_receipt.php?reference=${reference}`, '_blank');
}
</script>

<?php
include('includes/script.php');
include('includes/footer.php');
?>

<!-- QR functions script - loaded after all libraries -->
<script>
// Set admin name from PHP session
const adminName = '<?php echo $_SESSION['name'] ?? 'Admin'; ?>';
// QR script loaded successfully
console.log('QR script loaded successfully');

// QR Verification and Release Function
function verifyAndRelease(qrReference, requestId, registrarName, typeRequest, contactNo, email) {
    console.log('verifyAndRelease called with:', {qrReference, requestId, registrarName, typeRequest, contactNo, email});
    
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        alert('SweetAlert2 not loaded!');
        return;
    }
    
    // QR Verification and Release Confirmation
    Swal.fire({
        title: 'QR Code Verification & Release',
        html: `
            <div class="text-left">
                <p><strong>Document Type:</strong> ${typeRequest}</p>
                <p><strong>Requestor:</strong> ${registrarName}</p>
                <p><strong>QR Reference:</strong> <code>${qrReference}</code></p>
                <p><strong>Contact:</strong> ${contactNo}</p>
                <hr>
                <p><strong>Process:</strong></p>
                <ol class="text-left">
                    <li>Requestor presents QR receipt at office</li>
                    <li>Admin verifies QR reference number</li>
                    <li>Admin releases document</li>
                    <li>SMS sent to requestor with QR reference</li>
                </ol>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Verify & Release Document',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            // Proceed with complete document release
            releaseDocumentComplete(requestId, qrReference, registrarName, typeRequest, contactNo, email);
        }
    });
}

// Complete document release with all parameters (including SMS)
function releaseDocumentComplete(requestId, qrReference, registrarName, typeRequest, contactNo, email) {
    // Show loading
    Swal.fire({
        title: 'Releasing Document...',
        text: 'Please wait while we process the release and send SMS notification',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Create form data for release with all required parameters
    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('released_by', adminName); // Admin name
    formData.append('contact_no', contactNo);
    formData.append('registrar_name', registrarName);
    formData.append('type_request', typeRequest);
    formData.append('email', email);
    formData.append('user_id', '0'); // Default for walk-in requests
    formData.append('qr_reference', qrReference);
    formData.append('release_method', 'qr_verified');
    
    // Create SMS message with PDF receipt
    const smsMessage = `Your ${typeRequest} document is ready for pickup at MCRO Botolan. Please download and print your receipt from the link below, then bring the printed receipt and a valid ID to the LCRO office for verification and claiming. Thank you!`;
    formData.append('sms_message', smsMessage);
    formData.append('send_pdf_receipt', 'true');
    
    // Send release request
    fetch('process_release.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Document Released Successfully!',
                html: `
                    <div class="text-left">
                        <p><strong>QR Reference:</strong> <code>${qrReference}</code></p>
                        <p><strong>Document Type:</strong> ${typeRequest}</p>
                        <p><strong>Requestor:</strong> ${registrarName}</p>
                        <p><strong>SMS Sent:</strong> ${data.sms_sent ? '✅ Yes' : '❌ No'}</p>
                        <p><strong>Contact:</strong> ${contactNo}</p>
                        <hr>
                        <p><strong>Next Steps:</strong></p>
                        <ul class="text-left">
                            <li>Requestor received SMS with QR reference</li>
                            <li>Requestor can present QR receipt at office</li>
                            <li>Document is ready for pickup</li>
                        </ul>
                    </div>
                `,
                showConfirmButton: false,
                timer: 4000
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Release Failed',
                text: data.message || 'Failed to release document. Please try again.'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while releasing the document.'
        });
    });
}
</script>
