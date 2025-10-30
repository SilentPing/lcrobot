<?php
session_start();

require_once __DIR__ . '/db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['name']);
$user = null;

if ($isLoggedIn) {
    // Get user information if logged in
    $email = $_SESSION['name'];
    $query = "SELECT id_user, u_fn, u_ln FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Timely Birth Registration - Civil Registry Botolan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="images/lcrobot.png" type="images/png">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .upload-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            margin: 50px auto;
            max-width: 600px;
            padding: 40px;
        }
        .upload-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }
        .file-input {
            position: absolute;
            left: -9999px;
        }
        .file-input-label {
            display: block;
            padding: 20px;
            border: 2px dashed #667eea;
            border-radius: 10px;
            text-align: center;
            background: #f8f9ff;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .file-input-label:hover {
            border-color: #764ba2;
            background: #f0f2ff;
        }
        .file-selected {
            border-color: #28a745;
            background: #f0fff4;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .steps {
            background: #f8f9ff;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
        }
        .step {
            display: flex;
            align-items: center;
            margin: 15px 0;
            padding: 10px;
            border-radius: 10px;
            background: white;
        }
        .step-number {
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $isLoggedIn ? 'user_dashboard.php' : 'civ_dashboard.php'; ?>">
                <img src="images/lcrobot.png" alt="LCRO" height="40" class="me-2">
                Civil Registry Botolan
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?php echo $isLoggedIn ? 'user_dashboard.php' : 'civ_dashboard.php'; ?>">
                    <i class="fas fa-home me-1"></i><?php echo $isLoggedIn ? 'Dashboard' : 'Home'; ?>
                </a>
                <?php if ($isLoggedIn): ?>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
                <?php else: ?>
                <a class="nav-link" href="login.php">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="upload-container">
            <div class="text-center">
                <div class="upload-icon">
                    <i class="fas fa-file-excel"></i>
                </div>
                <h2 class="mb-3">Submit Timely Birth Registration</h2>
                <p class="text-muted mb-4">Upload your completed Excel form for birth certificate processing</p>
            </div>

            <!-- Instructions -->
            <div class="steps">
                <h5 class="mb-3"><i class="fas fa-list-ol me-2"></i>Submission Steps</h5>
                <div class="step">
                    <div class="step-number">1</div>
                    <div>Download the Excel template from the dashboard</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div>Fill up all required information in the Excel form</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div>Upload the completed Excel file below</div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div>Provide your name for record identification</div>
                </div>
            </div>

            <!-- User Information Form (for non-logged-in users) -->
            <?php if (!$isLoggedIn): ?>
            <div class="mb-4">
                <h5 class="text-center mb-3">Your Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="requestorName" class="form-label fw-bold">Full Name *</label>
                            <input type="text" class="form-control" id="requestorName" name="requestor_name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="requestorEmail" class="form-label fw-bold">Email Address (Optional)</label>
                            <input type="email" class="form-control" id="requestorEmail" name="requestor_email">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="requestorPhone" class="form-label fw-bold">Phone Number *</label>
                            <input type="tel" class="form-control" id="requestorPhone" name="requestor_phone" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="requestorAddress" class="form-label fw-bold">Address *</label>
                            <input type="text" class="form-control" id="requestorAddress" name="requestor_address" required>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Upload Form -->
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="form-label fw-bold">Select Excel File (.xlsx)</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="excelFile" name="excel_file" class="file-input" accept=".xlsx" required>
                        <label for="excelFile" class="file-input-label" id="fileLabel">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-3 text-muted"></i>
                            <div class="fw-bold">Click to select Excel file</div>
                            <div class="text-muted">Only .xlsx files are accepted</div>
                        </label>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                        <i class="fas fa-upload me-2"></i>Submit Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('excelFile');
            const fileLabel = document.getElementById('fileLabel');
            const submitBtn = document.getElementById('submitBtn');
            const uploadForm = document.getElementById('uploadForm');

            // File input change handler
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file type
                    if (!file.name.toLowerCase().endsWith('.xlsx')) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid File Type',
                            text: 'Please select an Excel file (.xlsx)',
                            confirmButtonText: 'OK'
                        });
                        fileInput.value = '';
                        return;
                    }

                    // Update label
                    fileLabel.innerHTML = `
                        <i class="fas fa-file-excel fa-2x mb-3 text-success"></i>
                        <div class="fw-bold text-success">${file.name}</div>
                        <div class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                    `;
                    fileLabel.classList.add('file-selected');
                    submitBtn.disabled = false;
                } else {
                    // Reset label
                    fileLabel.innerHTML = `
                        <i class="fas fa-cloud-upload-alt fa-2x mb-3 text-muted"></i>
                        <div class="fw-bold">Click to select Excel file</div>
                        <div class="text-muted">Only .xlsx files are accepted</div>
                    `;
                    fileLabel.classList.remove('file-selected');
                    submitBtn.disabled = true;
                }
            });

            // Form submission
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData();
                formData.append('excel_file', fileInput.files[0]);

                // Add user information for non-logged-in users
                <?php if (!$isLoggedIn): ?>
                const requestorName = document.getElementById('requestorName').value;
                const requestorEmail = document.getElementById('requestorEmail').value;
                const requestorPhone = document.getElementById('requestorPhone').value;
                const requestorAddress = document.getElementById('requestorAddress').value;
                
                if (!requestorName || !requestorPhone || !requestorAddress) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Information',
                        text: 'Please fill in your name, phone number, and address.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                formData.append('requestor_name', requestorName);
                formData.append('requestor_email', requestorEmail);
                formData.append('requestor_phone', requestorPhone);
                formData.append('requestor_address', requestorAddress);
                <?php endif; ?>

                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we process your Excel file.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit form
                fetch('api/upload_excel_form.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Form Submitted Successfully!',
                            text: `Submission Number: ${data.submission_number}. You will be notified once your certificate is ready.`,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '<?php echo $isLoggedIn ? 'user_dashboard.php' : 'civ_dashboard.php'; ?>';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Submission Failed',
                            text: data.message || 'An error occurred while processing your form.',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while submitting your form.',
                        confirmButtonText: 'OK'
                    });
                });
            });
        });
    </script>
</body>
</html>
