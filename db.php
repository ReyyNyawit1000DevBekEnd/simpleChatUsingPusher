<?php
$conn = new mysqli("localhost", "root", "", "chat_demo");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
