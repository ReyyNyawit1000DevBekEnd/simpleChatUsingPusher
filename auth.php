<?php
ob_start();
session_start();

require 'vendor/autoload.php';
require 'config.php';
require 'db.php';

if(!isset($_SESSION['user_id'])){
    http_response_code(403);
    exit("Unauthorized");
}

$user_id = $_SESSION['user_id'];

$channel_name = $_POST['channel_name'] ?? '';
$socket_id = $_POST['socket_id'] ?? '';

if(!preg_match('/private-chat-(\d+)/', $channel_name, $matches)){
    http_response_code(403);
    exit;
}

$conversation_id = intval($matches[1]);

$stmt = $conn->prepare("
SELECT id FROM conversations 
WHERE id=? AND (buyer_id=? OR seller_id=?)
");

$stmt->bind_param("iii", $conversation_id, $user_id, $user_id);
$stmt->execute();

if($stmt->get_result()->num_rows == 0){
    http_response_code(403);
    exit;
}

$options = [
    'cluster' => PUSHER_CLUSTER,
    'useTLS' => true
];

$pusher = new Pusher\Pusher(
    PUSHER_KEY,
    PUSHER_SECRET,
    PUSHER_APP_ID,
    $options
);

echo $pusher->socket_auth($channel_name, $socket_id);
ob_end_flush();
