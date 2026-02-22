<?php

$db_url = getenv("MYSQL_URL");

if(!$db_url){
    die("DB URL NOT FOUND");
}

$parts = parse_url($db_url);

$host = $parts["host"];
$user = $parts["user"];
$pass = $parts["pass"];
$dbname = ltrim($parts["path"],"/");
$port = $parts["port"] ?? 3306;

$conn = new mysqli($host,$user,$pass,$dbname,$port);

if($conn->connect_error){
    die("DB ERROR : ".$conn->connect_error);
}

?>
