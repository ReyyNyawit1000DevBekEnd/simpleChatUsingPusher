<?php

$host = getenv("MYSQLHOST");
$user = getenv("MYSQLUSER");
$pass = getenv("MYSQLPASSWORD");
$db   = getenv("MYSQLDATABASE");
$port = getenv("MYSQLPORT");

if(!$host){
    die("ENV Database not loaded");
}

$conn = new mysqli($host,$user,$pass,$db,$port);

if($conn->connect_error){
    die("DB Connection Failed : ".$conn->connect_error);
}

?>
