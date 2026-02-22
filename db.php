<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_url = getenv("MYSQL_URL");

$parsed = parse_url($db_url);

$host = $parsed["host"];
$user = $parsed["user"];
$pass = $parsed["pass"];
$dbname = ltrim($parsed["path"], "/");
$port = $parsed["port"] ?? 3306;

$conn = new mysqli($host,$user,$pass,$dbname,$port);

if($conn->connect_error){
    die("DB Connection Failed");
}

?>
