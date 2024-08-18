<?php
error_reporting();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "valid";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn) {
    // echo" Database Connection  "; 
}
else{
    echo "Not Connected";
}

// // SMTP Settings for PHPMailer
// $smtp_host = 'smtp.gmail.com';
// $smtp_port = 587;
// $smtp_user = 'info.nikesh12@gmail.com';
// $smtp_pass = 'gfkq blcf wgjx jlto'; // Generate from Google account
// ?>
