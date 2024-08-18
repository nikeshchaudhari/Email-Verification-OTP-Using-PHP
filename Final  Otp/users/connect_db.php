<?php
error_reporting();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "valid";

// connection Database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Database Connection
if ($conn) {
    // echo" Database Connection  "; 
}
else{
    echo "Not Connected";
}

 ?>
