<?php
// Establish a database connection (replace these values with your database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "civ_reg";
                              
 // Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

 // Check the connection
 if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
  }

// Get the selected Province code from the request
$selectedProvinceCode = $_POST['province_code'];

// Fetch city/municipality data based on the selected Province
$sql = "SELECT citymunDesc, citymunCode FROM refcitymun WHERE provCode = '$selectedProvinceCode' ORDER BY citymunDesc";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $cities = array();
    while ($row = $result->fetch_assoc()) {
        $cities[] = array(
            'code' => $row['citymunCode'],
            'name' => $row['citymunDesc']
        );
    }
    echo json_encode($cities); // Send the city/municipality data as JSON
}

// Close the database connection
$conn->close();
?>
