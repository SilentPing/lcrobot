 <?php
include('includes/header.php');
include('includes/navbar.php');

require('db.php');

// Function to sanitize and output data
function displayField($label, $value) {
    echo "<div class='row mb-2'>";
    echo "<div class='col-3'><strong>$label:</strong></div>";
    echo "<div class='col-7'><input type='text' class='form-control' value='$value' readonly></div>";
    echo "</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['id_user'])) {
        // Retrieve the id_user from the URL parameters
        $id_user = $_GET['id_user'];

        // Fetch details from birthceno_tbl based on id_user
        $birthDetailsSql = "SELECT * FROM birthceno_tbl WHERE id_user = '$id_user'";
        $birthDetailsResult = $conn->query($birthDetailsSql);

        if ($birthDetailsResult->num_rows > 0) {
            // Display the form with marriage details for verification
            echo "<div class='container'>";
            
            // Row for logos
            echo "<div class='row'>";

            
            // Logo in the top left
            echo "<div class='col-6'>";
            echo "<img src='images/lgu2.png' alt='' style='width: 130px; height: auto;'>";
            echo "</div>";
            
            // Logo in the top right
            echo "<div class='col-6 text-right'>";
            echo "<img src='images/civ.png' alt='' style='width: 130px; height: auto;'>";
            echo "</div>";

            echo "</div>";

            echo "<h1 class='text-center mb-1 mt-1'>Verification of Request</h1>";

            // Back button to navigate to the previous page
            echo "<a href='javascript:history.back()' class='btn btn-primary mb-3'>Back</a>";

            echo "<form>";

            echo "<div class='container' style='opacity: 0; animation: fadeIn 0.5s forwards;'>"; // Added style for fade-in effect

            echo "<style>
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
            </style>";

            while ($row = $birthDetailsResult->fetch_assoc()) {
                // Display relevant details using the displayField function
                displayField("Last Name", $row['lastname']);
                displayField("Middle Name", $row['firstname']);
                displayField("First Name", $row['middlename']);
                displayField("Place of Birth/Country", $row['pob_country']);
                displayField("Place of Birth/Provice", $row['pob_province']);
                displayField("Place of Birth/Municipality", $row['pob_municipality']);
                $dobFormatted = date("F j, Y", strtotime($row['dob']));
                displayField("Date of Birth", $dobFormatted);
                displayField("Sex", $row['sex']);
                displayField("Fathers Lastname", $row['fath_ln']);
                displayField("Fathers Firstname", $row['fath_fn']);
                displayField("Fathers Middlename", $row['fath_mn']);
                displayField("Mothers Maiden Lastname", $row['moth_maiden_ln']);
                displayField("Mothers Maiden Firstname", $row['moth_maiden_fn']);
                displayField("Mothers Maiden Middlename", $row['moth_maiden_mn']);
                displayField("Relationship", $row['relationship']);
                displayField("Purpose of Request", $row['purpose_of_request']);
                displayField("Type of Request", $row['type_request']);
                // Add more fields as needed

                // Display buttons or additional form elements if necessary
            }

            echo "</form>";
            echo "</div>";
        } else {
            // Display a message if no details are found
            echo "<div class='container'>";
            echo "<p class='text-center'>No details found for the selected request.</p>";
            echo "<a href='javascript:history.back()' class='btn btn-primary'>Back</a>";
            echo "</div>";
        }
    }
}

include('includes/script.php');
include('includes/footer.php');
?>

