<?php
// header('Content-Type: application/json');

// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "civ_reg";

// // Create a connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// if ($conn->connect_error) {
//     die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
// }

// // Get the selected Province code from the request
// $selectedProvinceCode = $_POST['province_code'];

// // Fetch city/municipality data based on the selected Province
// $sql = "SELECT citymunDesc, citymunCode FROM refcitymun WHERE provCode = '$selectedProvinceCode' ORDER BY citymunDesc";
// $result = $conn->query($sql);

// if ($result === false) {
//     die(json_encode(['error' => 'Error executing the query: ' . $conn->error]));
// }

// $cities = array();

// while ($row = $result->fetch_assoc()) {
//     $cities[] = array(
//         'code' => $row['citymunCode'],
//         'name' => $row['citymunDesc']
//     );
// }

// echo json_encode($cities);

// $conn->close();
?>
