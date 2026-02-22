<?php
require 'vendor/autoload.php';
require 'config.php';
require 'db.php';

header("Content-Type: application/json");

$message = trim($_POST['message'] ?? '');
$conversation_id = intval($_POST['conversation_id'] ?? 0);

if(empty($message)){
    exit(json_encode(["error"=>"empty"]));
}

/*
=====================================
INSERT MESSAGE
=====================================
*/

$stmt = $conn->prepare("
INSERT INTO messages (conversation_id, sender_id, message)
VALUES (?,?,?)
");

$sender_id = 0; // public chat mode

$stmt->bind_param("iis",
    $conversation_id,
    $sender_id,
    $message
);

$stmt->execute();

/*
=====================================
PUSHER PUBLIC TRIGGER
=====================================
*/

$pusher = new Pusher\Pusher(
    PUSHER_KEY,
    PUSHER_SECRET,
    PUSHER_APP_ID,
    [
        "cluster" => PUSHER_CLUSTER,
        "useTLS" => true
    ]
);

$pusher->trigger(
    "public-chat",
    "new-message",
    [
        "message" => htmlspecialchars($message),
        "sender_id" => 0
    ]
);

echo json_encode(["success"=>true]);
