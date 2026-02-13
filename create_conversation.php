<?php
require 'db.php';

$buyer_id = $_POST['buyer_id'];
$seller_id = $_POST['seller_id'];

$stmt = $conn->prepare("SELECT id FROM conversations WHERE buyer_id=? AND seller_id=?");
$stmt->bind_param("ii", $buyer_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo $row['id'];
} else {
    $stmt = $conn->prepare("INSERT INTO conversations (buyer_id, seller_id) VALUES (?,?)");
    $stmt->bind_param("ii", $buyer_id, $seller_id);
    $stmt->execute();
    echo $conn->insert_id;
}
?>
