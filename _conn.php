<?php

$dbServername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "we_good_coffee";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($dbServername, $dbUsername, $dbPassword, $dbName);

?>