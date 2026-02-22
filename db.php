<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = trim(getenv("MYSQLHOST"));
$user = trim(getenv("MYSQLUSER"));
$pass = trim(getenv("MYSQLPASSWORD"));
$db   = trim(getenv("MYSQLDATABASE"));
$port = trim(getenv("MYSQLPORT"));

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    http_response_code(500);
    exit("DB Connection Error");
}
?>
