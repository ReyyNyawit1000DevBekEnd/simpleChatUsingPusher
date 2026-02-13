<?php
session_start();
require 'vendor/autoload.php';
require 'config.php';
require 'db.php';

if(!isset($_SESSION['user_id'])){
    die("Unauthorized - No Session");
}

$conversation_id = intval($_POST['conversation_id']);
$sender_id = $_SESSION['user_id']; // IMPORTANT FIX
$message = trim($_POST['message']);

if(empty($message)){
    die("Empty message");
}

// verify user belongs to conversation
$stmt = $conn->prepare("
SELECT id FROM conversations 
WHERE id=? AND (buyer_id=? OR seller_id=?)
");
$stmt->bind_param("iii", $conversation_id, $sender_id, $sender_id);
$stmt->execute();

if($stmt->get_result()->num_rows == 0){
    die("Unauthorized access");
}

// insert message
$stmt = $conn->prepare("
INSERT INTO messages (conversation_id, sender_id, message) 
VALUES (?,?,?)
");
$stmt->bind_param("iis", $conversation_id, $sender_id, $message);
$stmt->execute();

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

$data = [
    'conversation_id' => $conversation_id,
    'sender_id' => $sender_id,
    'message' => $message
];

$pusher->trigger('chat-test', 'new-message', $data);
print_r([
    "app_id" => PUSHER_APP_ID,
    "key" => PUSHER_KEY,
    "cluster" => PUSHER_CLUSTER
]);
exit;

echo "sent";
var_dump($pusher->trigger('private-chat-'.$conversation_id, 'new-message', $data));
exit;
?>
