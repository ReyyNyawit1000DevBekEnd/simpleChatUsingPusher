<?php
session_start();
require "vendor/autoload.php";
require "config.php";

if(!isset($_SESSION['username'])){
exit;
}

$msg = trim($_POST['msg'] ?? "");

if(empty($msg)) exit;

$user = $_SESSION['username'];
$avatar = $_SESSION['avatar'] ?? "";

/*
============================
BOT SMART REPLY ENGINE
============================
*/

$botReply = "";

/* Greeting */
if(str_contains(strtolower($msg),"halo")){
$botReply = "Halo juga 👋";
}

/* Help command */
if(str_contains(strtolower($msg),".menu")){
$botReply = "💀 BOT MENU\n\n".
".menu = Show menu\n".
".ai = Random AI reply\n".
".music = Play music\n".
".gif = Show gif";
}

/* AI Random */
$aiReplies = [
"Chat enjoy 😈",
"I am ReyyBot 🤖",
"Need help? Type .menu",
"Stay cool bro 🔥",
];

if(str_contains(strtolower($msg),".ai")){
$botReply = $aiReplies[array_rand($aiReplies)];
}

/*
============================
PUSHER SEND USER MESSAGE
============================
*/

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
"avatar"=>$avatar,
"msg"=>$msg
]
);

/*
============================
BOT AUTO REPLY
============================
*/

if(!empty($botReply)){

sleep(1);

$pusher->trigger(
"public-chat",
"new-message",
[
"user"=>"ChatBot 🤖",
"avatar"=>"https://api.dicebear.com/7.x/bottts/svg?seed=bot",
"msg"=>$botReply
]
);

}

?>
