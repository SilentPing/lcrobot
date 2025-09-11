                <?  
                      

                                // Check if the form for approving requests is submitted
                                // Check if the form for approving requests is submitted
                        // if(isset($_POST['send_sms_btn'])) {
                        //     $approvedRequestId = $_POST['request_id'];

                        //     // Update status in the original table
                        //     $queryUpdateStatus = "UPDATE reqtracking_tbl SET status = 'Approved' WHERE request_id = $approvedRequestId";
                        //     $queryUpdateStatusRun = mysqli_query($conn, $queryUpdateStatus);

                        //     if (!$queryUpdateStatusRun) {
                        //         die("Query to update status failed: " . mysqli_error($conn));
                        //     }

                        //     // Fetch the approved request details from the original table
                        //     $queryFetchApprovedRequest = "SELECT * FROM reqtracking_tbl WHERE request_id = $approvedRequestId";
                        //     $resultFetchApprovedRequest = mysqli_query($conn, $queryFetchApprovedRequest);

                        //     if (!$resultFetchApprovedRequest) {
                        //         die("Query to fetch approved request details failed: " . mysqli_error($conn));
                        //     }

                        //     // Insert the approved request into the separate table
                        //     if ($rowApprovedRequest = mysqli_fetch_assoc($resultFetchApprovedRequest)) {
                        //         $queryInsertApprovedRequest = "INSERT INTO approved_requests (registration_date, registrar_name, type_request, status)
                        //                                       VALUES ('{$rowApprovedRequest['registration_date']}', '{$rowApprovedRequest['registrar_name']}', '{$rowApprovedRequest['type_request']}', 'Approved')";
                        //         $queryInsertApprovedRequestRun = mysqli_query($conn, $queryInsertApprovedRequest);

                        //         if (!$queryInsertApprovedRequestRun) {
                        //             die("Query to insert approved request failed: " . mysqli_error($conn));
                        //         }
                        //     }

                        //     // Add your code to send SMS notification
                        //     include('sms.php');
                        // }
                    ?>