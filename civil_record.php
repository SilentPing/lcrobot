<?php
session_start();
include('includes/header.php');
include('includes/navbar.php');

// Check if user is LCRO Staff (only LCRO staff can add records)
$is_lcro_staff = isset($_SESSION['usertype']) && ($_SESSION['usertype'] == 'admin' || $_SESSION['usertype'] == 'staff');

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-folder-fill"></i> Civil Record</h6>
            <?php if ($is_lcro_staff): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                <i class="fas fa-plus"></i> Add Record
            </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th> <!-- This column will display row numbers -->
                            <th>Registration Date</th>
                            <th>Name</th>
                            <th>Type of Request</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require('db.php');

                        // Check if the connection is successful
                        if (!$conn) {
                            die("Connection failed: " . mysqli_connect_error());
                        }

                        // Enhanced query to get status from respective tables
                        $query = "SELECT
                                    cr.civ_rec_id,
                                    cr.registration_date,
                                    cr.registrar_name,
                                    cr.type_request,
                                    COALESCE(bc.status_request, mc.status_request, dc.status_request) as status,
                                    COALESCE(bc.id_birth_ceno, mc.id_marriage, dc.id_death) as record_id,
                                    CASE
                                        WHEN cr.id_birth_ceno IS NOT NULL AND cr.id_birth_ceno != 0 THEN 'birth'
                                        WHEN cr.id_marriage IS NOT NULL AND cr.id_marriage != 0 THEN 'marriage'
                                        WHEN cr.id_death IS NOT NULL AND cr.id_death != 0 THEN 'death'
                                    END as record_type,
                                    CASE
                                        WHEN cr.id_birth_ceno IS NOT NULL AND cr.id_birth_ceno != 0 THEN CONCAT(bc.lastname, ', ', bc.firstname, ' ', COALESCE(bc.middlename, ''))
                                        WHEN cr.id_marriage IS NOT NULL AND cr.id_marriage != 0 THEN CONCAT(mc.husband_ln, ', ', mc.husband_fn, ' & ', mc.maiden_wife_ln, ', ', mc.maiden_wife_fn)
                                        WHEN cr.id_death IS NOT NULL AND cr.id_death != 0 THEN CONCAT(dc.deceased_ln, ', ', dc.deceased_fn, ' ', COALESCE(dc.deceased_mn, ''))
                                    END as full_name
                                  FROM civ_record cr
                                  LEFT JOIN birthceno_tbl bc ON cr.id_birth_ceno = bc.id_birth_ceno AND cr.id_birth_ceno != 0
                                  LEFT JOIN marriage_tbl mc ON cr.id_marriage = mc.id_marriage AND cr.id_marriage != 0
                                  LEFT JOIN death_tbl dc ON cr.id_death = dc.id_death AND cr.id_death != 0
                                  WHERE (cr.id_birth_ceno != 0 OR cr.id_marriage != 0 OR cr.id_death != 0)
                                  ORDER BY cr.registration_date DESC";
                        
                        $query_run = mysqli_query($conn, $query);

                        // Check if the query was executed successfully
                        if (!$query_run) {
                            die("Query failed: " . mysqli_error($conn));
                        }

                        $row_number = 1; // Initialize a variable to track row numbers
                        ?>

                        <?php
                        if (mysqli_num_rows($query_run) > 0) {
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                $status = $row['status'] ?? 'PENDING';
                                $record_id = $row['record_id'];
                                $record_type = $row['record_type'];
                                
                                // Status badge styling
                                $status_class = '';
                                switch($status) {
                                    case 'APPROVED':
                                        $status_class = 'badge-success';
                                        break;
                                    case 'PENDING':
                                        $status_class = 'badge-warning';
                                        break;
                                    case 'REJECTED':
                                        $status_class = 'badge-danger';
                                        break;
                                    default:
                                        $status_class = 'badge-secondary';
                                }
                                ?>
                                <tr>
                                    <td><?php echo $row_number; ?></td>
                                    <td><?php echo $row['registration_date']; ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['type_request']); ?></td>
                                    <td><span class="badge <?php echo $status_class; ?>"><?php echo $status; ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewRecord(<?php echo $record_id; ?>, '<?php echo $record_type; ?>')">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-primary" onclick="downloadPDF(<?php echo $record_id; ?>, '<?php echo $record_type; ?>')">
                                            <i class="fas fa-download"></i> PDF
                                        </button>
                                        <?php if ($is_lcro_staff && $status == 'PENDING'): ?>
                                        <button class="btn btn-sm btn-success" onclick="approveRecord(<?php echo $record_id; ?>, '<?php echo $record_type; ?>')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectRecord(<?php echo $record_id; ?>, '<?php echo $record_type; ?>')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                        <?php endif; ?>
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

<!-- Add Record Modal -->
<?php if ($is_lcro_staff): ?>
<div class="modal fade" id="addRecordModal" tabindex="-1" aria-labelledby="addRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRecordModalLabel">Add Civil Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addRecordForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="recordType" class="form-label">Record Type</label>
                            <select class="form-control" id="recordType" name="recordType" required onchange="loadRecordForm()">
                                <option value="">Select Record Type</option>
                                <option value="birth">Birth Certificate</option>
                                <option value="marriage">Marriage Certificate</option>
                                <option value="death">Death Certificate</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="recordSource" class="form-label">Record Source</label>
                            <select class="form-control" id="recordSource" name="recordSource" required>
                                <option value="">Select Source</option>
                                <option value="old_record">Old Record (Immediate Activation)</option>
                                <option value="new_record">New Record (Requires Approval)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="recordFormContainer">
                        <!-- Dynamic form will be loaded here based on record type -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveRecord()">Save Record</button>
            </div>
        </div>
    </div>
</div>

<!-- View Record Modal -->
<div class="modal fade" id="viewRecordModal" tabindex="-1" aria-labelledby="viewRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRecordModalLabel">View Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewRecordContent">
                <!-- Record details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


 <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MCRO 2025</span>
                    </div>
                </div>
            </footer>

<script>
// Load dynamic form based on record type
function loadRecordForm() {
    const recordType = document.getElementById('recordType').value;
    const container = document.getElementById('recordFormContainer');
    
    if (!recordType) {
        container.innerHTML = '<div class="alert alert-info">Please select a record type to load the form.</div>';
        return;
    }
    
    // Show loading
    container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading form...</div>';
    
    // Load form via AJAX
    fetch(`api/get_record_form.php?type=${recordType}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            container.innerHTML = '<div class="alert alert-danger">Error loading form. Please try again.</div>';
            console.error('Error:', error);
        });
}

// Save record
function saveRecord() {
    const form = document.getElementById('addRecordForm');
    const formData = new FormData(form);
    
    // Add validation
    if (!validateForm()) {
        return;
    }
    
    // Show loading
    const saveBtn = document.querySelector('#addRecordModal .btn-primary');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    fetch('api/save_civil_record.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Record saved successfully!',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload(); // Refresh the page to show new record
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error saving record. Please try again.',
            confirmButtonText: 'OK'
        });
        console.error('Error:', error);
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Validate form
function validateForm() {
    const recordType = document.getElementById('recordType').value;
    const recordSource = document.getElementById('recordSource').value;
    
    if (!recordType) {
        Swal.fire({
            icon: 'warning',
            title: 'Required Field',
            text: 'Please select a record type.',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    if (!recordSource) {
        Swal.fire({
            icon: 'warning',
            title: 'Required Field',
            text: 'Please select a record source.',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    // Additional validation based on form type
    const requiredFields = document.querySelectorAll('#recordFormContainer input[required], #recordFormContainer select[required]');
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            const fieldName = field.previousElementSibling ? field.previousElementSibling.textContent : field.name;
            Swal.fire({
                icon: 'warning',
                title: 'Required Field',
                text: `Please fill in the ${fieldName} field.`,
                confirmButtonText: 'OK'
            }).then(() => {
                field.focus();
            });
            return false;
        }
    }
    
    return true;
}

// View record details
function viewRecord(recordId, recordType) {
    const modalElement = document.getElementById('viewRecordModal');
    const content = document.getElementById('viewRecordContent');
    
    if (!modalElement) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Modal not found. Please refresh the page.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading record details...</div>';
    
    // Try Bootstrap 5 first, then fallback to Bootstrap 4
    let modal;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        modal = new bootstrap.Modal(modalElement);
    } else if (typeof $ !== 'undefined' && $.fn.modal) {
        $(modalElement).modal('show');
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Bootstrap modal not available. Please refresh the page.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    if (modal) {
        modal.show();
    }
    
    fetch(`api/get_record_details.php?id=${recordId}&type=${recordType}`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Error loading record details.</div>';
            console.error('Error:', error);
        });
}

// Approve record
function approveRecord(recordId, recordType) {
    Swal.fire({
        title: 'Approve Record',
        text: 'Are you sure you want to approve this record?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            updateRecordStatus(recordId, recordType, 'APPROVED');
        }
    });
}

// Reject record
function rejectRecord(recordId, recordType) {
    Swal.fire({
        title: 'Reject Record',
        text: 'Are you sure you want to reject this record?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, reject it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            updateRecordStatus(recordId, recordType, 'REJECTED');
        }
    });
}

// Download PDF report
function downloadPDF(recordId, recordType) {
    Swal.fire({
        title: 'Generating PDF',
        text: 'Please wait while we generate your report...',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Create a form to submit the request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'api/generate_civil_record_pdf.php';
    form.target = '_blank';
    
    const recordIdInput = document.createElement('input');
    recordIdInput.type = 'hidden';
    recordIdInput.name = 'record_id';
    recordIdInput.value = recordId;
    
    const recordTypeInput = document.createElement('input');
    recordTypeInput.type = 'hidden';
    recordTypeInput.name = 'record_type';
    recordTypeInput.value = recordType;
    
    form.appendChild(recordIdInput);
    form.appendChild(recordTypeInput);
    document.body.appendChild(form);
    
    // Submit the form
    form.submit();
    
    // Clean up
    document.body.removeChild(form);
    
    // Close the loading alert after a short delay
    setTimeout(() => {
        Swal.close();
        Swal.fire({
            icon: 'success',
            title: 'PDF Generated!',
            text: 'Your report has been generated and should start downloading shortly.',
            confirmButtonText: 'OK'
        });
    }, 2000);
}

// Update record status
function updateRecordStatus(recordId, recordType, status) {
    fetch('api/update_record_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: recordId,
            type: recordType,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: `Record ${status.toLowerCase()} successfully!`,
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error updating record status.',
            confirmButtonText: 'OK'
        });
        console.error('Error:', error);
    });
}

</script>

<?php
include('includes/script.php');
include('includes/footer.php');
?>
