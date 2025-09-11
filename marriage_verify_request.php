<?php
include('includes/header.php');
include('includes/navbar.php');

require('db.php');

function displayField($label, $value) {
    echo "<div class='row mb-2'>";
    echo "<div class='col-4'><strong>$label:</strong></div>";
    echo "<div class='col-8'><input type='text' class='form-control' value='$value' readonly></div>";
    echo "</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['id_user'])) {
        $id_user = $_GET['id_user'];

        // Fetch marriage details from marriage_tbl based on id_user
        $marriageDetailsSql = "SELECT * FROM marriage_tbl WHERE id_user = '$id_user'";
        $marriageDetailsResult = $conn->query($marriageDetailsSql);

        if ($marriageDetailsResult->num_rows > 0) {
            echo "<div class='container'>";
            // Row for logos
            echo "<div class='row'>";
            
            // Logo in the top left
            echo "<div class='col-6'>";
            echo "<img src='images/lgu2.png' alt='' style='width: 100px; height: auto;'>";
            echo "</div>";
            
            // Logo in the top right
            echo "<div class='col-6 text-right'>";
            echo "<img src='images/civ.png' alt='' style='width: 100px; height: auto;'>";
            echo "</div>";

            echo "</div>";

            echo "<h1 class='text-center mb-1 mt-1'>Verification of Request</h1>";

            while ($marriageRow = $marriageDetailsResult->fetch_assoc()) {
                // Display marriage details using the displayField function
                displayField("Husband Last Name", $marriageRow['husband_ln']);
                displayField("Husband Middle Name", $marriageRow['husband_fn']);
                displayField("Husband Middle Name", $marriageRow['husband_mn']);
                displayField("Maiden Wife Last Name", $marriageRow['maiden_wife_ln']);
                displayField("Maiden Wife First Name", $marriageRow['maiden_wife_fn']);
                displayField("Maiden Wife Middle Name", $marriageRow['maiden_wife_mn']);
                displayField("Marriage Date", $marriageRow['marriage_date']);
                displayField("Place of Marriage", $marriageRow['place_of_marriage']);
                displayField("Purpose of Request", $marriageRow['purpose_of_request']);
                displayField("Type of Request", $marriageRow['type_request']);
                // ... (other marriage details)
            }

            echo "</div>";
        } else {
            echo "<div class='container'>";
            echo "<p class='text-center'>No marriage details found for the selected request.</p>";
            echo "<a href='javascript:history.back()' class='btn btn-primary'>Back</a>";
            echo "</div>";
        }
    }
}

include('includes/script.php');
include('includes/footer.php');
?>