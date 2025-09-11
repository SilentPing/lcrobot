<?php

include('db.php'); // Assuming this file contains your database connection code

// Fetch user activity data from the database
$query = "SELECT * FROM user_activity_logs ORDER BY timestamp DESC";
$result = mysqli_query($conn, $query);

// Check if there are any results
if ($result && mysqli_num_rows($result) > 0) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title> User Activity Log </title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="images/civ.png" type="images/png">
  <link rel="stylesheet" type="text/css" href="css1/style.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    .button {
      border-radius: 4px;
      background-color: #f4511e;
      border: none;
      color: #FFFFFF;
      text-align: center;
      font-size: 28px;
      padding: 20px;
      width: 200px;
      transition: all 0.5s;
      cursor: pointer;
      margin: 5px;
      position: fixed;
      top: 10px;
      left: 10px;
    }

    .button span {
      cursor: pointer;
      display: inline-block;
      position: relative;
      transition: 0.5s;
    }

    .button span:after {
      content: '\00ab'; /* Change from \00bb to \00ab */
      position: absolute;
      opacity: 0;
      top: 0;
      left: -20px; /* Change right to left here */
      transition: 0.5s;
    }

    .button:hover span {
      padding-left: 25px; /* Change padding-right to padding-left */
    }

    .button:hover span:after {
      opacity: 1;
      left: 0; /* Change right to left here */
    }
  </style>
</head>
<body>

<section class="timeline-section">
  <div class="timeline-items">
    <?php
    // Loop through the results and generate timeline items
    while ($row = mysqli_fetch_assoc($result)) {
    ?>
    
      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <div class="timeline-date"><?php echo date('l, F j, Y g:i A', strtotime($row['timestamp'])); ?></div>
        <div class="timeline-content">
          <h3><?php echo $row['username'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']; ?> - <?php echo $row['activity_description']; ?></h3>
        </div>
      </div>
    <?php
    }
    ?>
  </div>

  <button class="button" onclick="goBack()"><span>Back </span></button> <!-- Animated Back Button -->

  <script>
    function goBack() {
      window.history.back();
    }
  </script>

</section>

</body>
</html>
<?php
} else {
    echo "No user activity data found.";
}

?>
