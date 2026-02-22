<?php
session_start();
require 'vendor/autoload.php';
require 'config.php';

$msg = trim($_POST['msg']);

if(empty($msg)) exit;

$user = $_SESSION['username'] ?? "Guest";

/*
BOT AUTO REPLY
*/

$botReply = "";

if(strpos(strtolower($msg),"halo") !== false){
    $botReply = "Halo juga 👋";
}

$pusher = new Pusher\Pusher(
    PUSHER_KEY,
    PUSHER_SECRET,
    PUSHER_APP_ID,
    [
        "cluster"=>PUSHER_CLUSTER,
        "useTLS"=>true
    ]
);

$pusher->trigger(
    "public-chat",
    "new-message",
    [
        "user"=>$user,
        "msg"=>$msg
    ]
);

/*
BOT MESSAGE
*/

if($botReply){

    sleep(1);

    $pusher->trigger(
        "public-chat",
        "new-message",
        [
            "user"=>"ChatBot 🤖",
            "msg"=>$botReply
        ]
    );
}

?>
