<?php

// // Include your database connection code if it's not already included
// include('db.php'); // Replace with the actual path to your DB connection script

// // Check if the user is logged in and retrieve their user ID
// session_start();
// if (isset($_SESSION['id_user'])) {
//     $id_user = $_SESSION['id_user'];

//     // Query the database for new notifications for this user
//     $notificationSql = "SELECT * FROM notifications WHERE id_user = $id_user ORDER BY timestamp DESC";

//     $notifications = $con->query($notificationSql);

//     // Create an HTML representation of notifications
//     if ($notifications->num_rows > 0) {
//         echo '<div class="container-fluid">
//                 <div class="card shadow mb-4">
//                     <div class="card-header py-3">
//                         <h6 class="m-0 font-weight-bold text-primary">Notifications</h6>
//                     </div>
//                     <div class="card-body">
//                         <div class="table-responsive">
//                             <table class="table table-bordered" width="100%" cellspacing="0">
//                                 <tbody>';
//         while ($row = $notifications->fetch_assoc()) {
//             echo '<tr>
//                     <td>' . $row['timestamp'] . '</td>
//                     <td>' . $row['message'] . '</td>
//                   </tr>';
//         }
//         echo '</tbody>
//             </table>
//         </div>
//     </div>
// </div>
// </div>';
//     } else {
//         echo '<p>No new notifications</p>';
//     }
// } else {
//     echo 'User not logged in';
// }

?>