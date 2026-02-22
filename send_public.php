<?php
session_start();
require "vendor/autoload.php";
require "config.php";

if(!isset($_SESSION['username'])) exit;

$msg = trim($_POST['msg'] ?? "");
if(empty($msg)) exit;

$user = $_SESSION['username'];
$avatar = $_SESSION['avatar'] ?? "";

/*
================================
SESSION XP SYSTEM
================================
*/

if(!isset($_SESSION['xp'])) $_SESSION['xp'] = 0;
if(!isset($_SESSION['level'])) $_SESSION['level'] = 1;
if(!isset($_SESSION['title'])) $_SESSION['title'] = "Novice";

/*
ADD XP
*/

$_SESSION['xp'] += rand(5,15);

/*
LEVEL UP LOGIC
*/

$nextLevel = $_SESSION['level'] * 120;

if($_SESSION['xp'] >= $nextLevel){

$_SESSION['level']++;

$rankTitles = [
"Novice",
"Warrior 😈",
"Elite 🔥",
"Demon King 💀",
"God Mode 👑"
];

$_SESSION['title'] =
$rankTitles[min($_SESSION['level']-1,4)];

$_SESSION['xp'] = 0;
}

/*
================================
BOT SMART REPLY
================================
*/

$msgLower = strtolower($msg);
$botReply = "";

/* Greeting */
if(preg_match("/halo|hai|hi|hey/i",$msgLower)){
$botReply = "Halo $user 👋";
}

/* Menu */
if(str_contains($msgLower,".menu")){
$botReply =
"💀 GOD BOT MENU\n\n".
".menu = Show menu\n".
".ai = Random AI\n".
".xp = Check XP\n".
".title = Your rank";
}

/* XP Info */
if(str_contains($msgLower,".xp")){
$botReply =
"⭐ STATUS\n".
"Level : ".$_SESSION['level']."\n".
"XP : ".$_SESSION['xp'];
}

/* Title */
if(str_contains($msgLower,".title")){
$botReply = "👑 Title : ".$_SESSION['title'];
}

/* AI Random */
$aiReplies = [
"Chat santai 😈",
"God bot online 👑",
"Stay cool 🔥",
"Enjoy chat 😎"
];

if(str_contains($msgLower,".ai")){
$botReply = $aiReplies[array_rand($aiReplies)];
}

/*
================================
PUSHER SEND MESSAGE
================================
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

/* User Message */
$pusher->trigger(
"public-chat",
"new-message",
[
"user"=>$user." | ".$_SESSION['title'],
"avatar"=>$avatar,
"msg"=>$msg
]
);

/* Bot Reply */
if(!empty($botReply)){

sleep(1);

$pusher->trigger(
"public-chat",
"new-message",
[
"user"=>"ChatBot 👑",
"avatar"=>"https://api.dicebear.com/7.x/bottts/svg?seed=bot",
"msg"=>$botReply
]
);

}

?>
