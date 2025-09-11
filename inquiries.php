<?php
include('includes/header.php'); 
include('includes/navbar.php');
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success"><i class="bi bi-check-circle-fill"></i> User's Inquiries</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Number</th>
                            <th>Address</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                         require('db.php');
                         
                        $query = "SELECT * FROM inquiries";
                        $query_run = mysqli_query($conn, $query);

                        if ($query) {
                            $row_number = 1;
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                ?>
                                <tr>
                                    <td><?php echo $row_number; ?></td>
                                    <td><?php echo $row['fullname']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['number']; ?></td>
                                    <td><?php echo $row['address']; ?></td>
                                    <td><?php echo $row['message']; ?></td>
                                </tr>
                                <?php
                                $row_number++;
                            }
                        } else {
                            include('includes/no_data_component.php');
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

<?php
include('includes/script.php');
include('includes/footer.php');
?>
