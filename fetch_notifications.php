<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "civ_reg";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all pending requests
$sql = "SELECT * FROM reqtracking_tbl WHERE status = 'Pending' ORDER BY registration_date DESC";
$result = $conn->query($sql);

$requests = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
$conn->close();

echo json_encode($requests);
?>
