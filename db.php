<?php

$db_url = getenv("MYSQL_PUBLIC_URL");

if(!$db_url){
    $db_url = getenv("MYSQL_URL");
}

if(!$db_url){
    die("Database URL not found");
}

$parsed = preg_replace("#^mysql://#", "", $db_url);
$parsed = explode("@", $parsed);

list($userpass,$hostdb) = $parsed;

list($user,$pass) = explode(":", $userpass);

list($hostport,$dbname) = explode("/", $hostdb);

list($host,$port) = explode(":", $hostport);

$conn = new mysqli(
    $host,
    $user,
    $pass,
    $dbname,
    $port
);

if($conn->connect_error){
    die("DB Connection Failed : ".$conn->connect_error);
}

?>
