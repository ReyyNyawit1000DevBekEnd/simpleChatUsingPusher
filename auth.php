<?php
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);

ob_start();
session_start();

header("Content-Type: application/json");

require 'vendor/autoload.php';
require 'config.php';
require 'db.php';

/*
=====================================
CHECK SESSION LOGIN
=====================================
*/

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode([
        "error" => "unauthorized"
    ]);
    exit;
}

$user_id = intval($_SESSION['user_id']);

/*
=====================================
GET POST DATA
=====================================
*/

$channel_name = $_POST['channel_name'] ?? '';
$socket_id = $_POST['socket_id'] ?? '';

if (empty($channel_name) || empty($socket_id)) {
    http_response_code(400);
    exit;
}

/*
=====================================
VALIDATE CHANNEL
Format: private-chat-123
=====================================
*/

if (!preg_match('/private-chat-(\d+)/', $channel_name, $matches)) {
    http_response_code(403);
    exit;
}

$conversation_id = intval($matches[1]);

/*
=====================================
CHECK ACCESS CONVERSATION
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
    $user_id,
    $user_id
);

$stmt->execute();

if ($stmt->get_result()->num_rows == 0) {
    http_response_code(403);
    exit;
}

/*
=====================================
PUSHER AUTH
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

echo $pusher->socket_auth($channel_name, $socket_id);

ob_end_flush();
