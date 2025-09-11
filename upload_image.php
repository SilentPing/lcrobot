<?php
// $target_dir = "uploads/";
// $target_file = $target_dir . basename($_FILES["userImage"]["name"]);
// $uploadOk = 1;
// $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// // Check if image file is a valid image
// if (isset($_POST["submit"])) {
//     $check = getimagesize($_FILES["userImage"]["tmp_name"]);
//     if ($check !== false) {
//         echo "File is an image - " . $check["mime"] . ".";
//         $uploadOk = 1;
//     } else {
//         echo "File is not an image.";
//         $uploadOk = 0;
//     }
// }

// // Check if file already exists
// if (file_exists($target_file)) {
//     echo "Sorry, file already exists.";
//     $uploadOk = 0;
// }

// // Check file size (adjust the size limit as needed)
// if ($_FILES["userImage"]["size"] > 500000) {
//     echo "Sorry, your file is too large.";
//     $uploadOk = 0;
// }

// // Allow certain file formats (you can customize this list)
// $allowedFormats = array("jpg", "jpeg", "png", "gif");
// if (!in_array($imageFileType, $allowedFormats)) {
//     echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
//     $uploadOk = 0;
// }

// // Check if $uploadOk is set to 0 by an error
// if ($uploadOk == 0) {
//     echo "Sorry, your file was not uploaded.";
// } else {
//     if (move_uploaded_file($_FILES["userImage"]["tmp_name"], $target_file)) {
//         echo "The file " . htmlspecialchars(basename($_FILES["userImage"]["name"])) . " has been uploaded.";
//         // Now, you can store the file path in your database or perform any other necessary actions.
//     } else {
//         echo "Sorry, there was an error uploading your file.";
//     }
// }

?>
