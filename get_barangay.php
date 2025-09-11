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

// Get the selected City/Municipality code from the AJAX request
$selectedCityCode = $_POST['city_code']; // Use 'city_code' here

// Fetch data from the 'refbrgy' table based on the selected City/Municipality code
$sql = "SELECT brgyDesc FROM refbrgy WHERE citymunCode = '$selectedCityCode'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $barangays = [];
    while ($row = $result->fetch_assoc()) {
        $barangays[] = $row['brgyDesc'];
    }
    echo json_encode($barangays);
} else {
    echo json_encode([]);
}

// Close the database connection
$conn->close();
?>
