<?php
session_start();
require 'vendor/autoload.php';
require 'config.php';
require 'db.php';

header("Content-Type: application/json");

/*
=====================================
CHECK SESSION
=====================================
*/

if(!isset($_SESSION['user_id'])){
    http_response_code(403);
    exit(json_encode([
        "error" => "unauthorized"
    ]));
}

$sender_id = intval($_SESSION['user_id']);

$conversation_id = intval($_POST['conversation_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if($conversation_id <= 0 || empty($message)){
    http_response_code(400);
    exit(json_encode([
        "error" => "invalid_request"
    ]));
}

/*
=====================================
VERIFY USER ACCESS CONVERSATION
=====================================
*/

$stmt = $conn->prepare("
SELECT id 
FROM conversations 
WHERE id=? 
AND (buyer_id=? OR seller_id=?)
");

$stmt->bind_param("iii",
    $conversation_id,
    $sender_id,
    $sender_id
);

$stmt->execute();

if($stmt->get_result()->num_rows == 0){
    http_response_code(403);
    exit(json_encode([
        "error" => "unauthorized_conversation"
    ]));
}

/*
=====================================
INSERT MESSAGE
=====================================
*/

$stmt = $conn->prepare("
INSERT INTO messages 
(conversation_id, sender_id, message) 
VALUES (?,?,?)
");

$stmt->bind_param("iis",
    $conversation_id,
    $sender_id,
    $message
);

$stmt->execute();

/*
=====================================
PUSHER TRIGGER REALTIME
=====================================
*/

$pusher = new Pusher\Pusher(
    PUSHER_KEY,
    PUSHER_SECRET,
    PUSHER_APP_ID,
    [
        'cluster' => PUSHER_CLUSTER,
        'useTLS' => true
    ]
);

$data = [
    "conversation_id" => $conversation_id,
    "sender_id" => $sender_id,
    "message" => htmlspecialchars($message)
];

$pusher->trigger(
    "private-chat-".$conversation_id,
    "new-message",
    $data
);

echo json_encode([
    "success" => true
]);
