<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

$host = getenv("MYSQLHOST");
$user = getenv("MYSQLUSER");
$pass = getenv("MYSQLPASSWORD");
$db   = getenv("MYSQLDATABASE");
$port = getenv("MYSQLPORT");

$conn = new mysqli($host,$user,$pass,$db,$port);

if($conn->connect_error){
die("DB Connection Failed : ".$conn->connect_error);
}
?>
