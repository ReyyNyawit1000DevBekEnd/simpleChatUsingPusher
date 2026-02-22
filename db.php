<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

$host = "nozomi.proxy.rlwy.net";
$user = "root";
$pass = "HuxKiAmwohhVQvBiUJMUKcZDhqGJYKLf";
$db   = "railway";
$port = 51309;

$conn = new mysqli($host,$user,$pass,$db,$port);

if($conn->connect_error){
    die("DB Connection Failed");
}
?>
