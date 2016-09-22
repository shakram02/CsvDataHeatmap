<?php
include('index.php');

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = sizeof($_FILES["fileToUpload"]["tmp_name"]);
    if($check == false){
        echo "File is not valid";
        return;
    }

    if($imageFileType != "csv" )
    {
        echo "Sorry, only Csv files are allowed.";
        $uploadOk = 0;
    }
    else
    {
        echo "File is valid - " . $check["mime"] . ".";
        $uploadOk = 1;
    }

    if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"],$target_file))
    {
        echo "Pass";
    }
    else{
        echo "Fail";
    }

    if($uploadOk)
    {
        parse_csv_file($target_file);
    }
}
?>