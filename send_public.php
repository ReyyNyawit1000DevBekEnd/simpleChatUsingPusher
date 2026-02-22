<?php
session_start();
require "vendor/autoload.php";
require "config.php";
require "db.php";

if(!isset($_SESSION['username'])) exit;

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

$msgLower = strtolower($msg);

/* Greeting */
if(preg_match("/halo|hai|hi|pagi|siang|malam/i",$msgLower)){
$botReply = "Halo juga 👋";
}

/* Menu Command */
if(str_contains($msgLower,".menu")){
$botReply =
"💀 REYY BOT MENU\n\n".
".menu = Show menu\n".
".ai = Random AI reply\n".
".music = Music link\n".
".gif = Random gif\n".
".xp = Check XP\n".
".help = Help info";
}

/* XP Info */
if(str_contains($msgLower,".xp")){

$stmt = $conn->prepare("SELECT xp,level FROM users WHERE username=?");
$stmt->bind_param("s",$user);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

$botReply =
"⭐ XP INFO\n".
"Level : ".($data['level'] ?? 1)."\n".
"XP : ".($data['xp'] ?? 0);
}

/* AI Random Reply */
$aiReplies = [
"Chat santai 😈",
"ReyyBot online 🤖",
"Stay cool 🔥",
"Need help? type .menu",
"Enjoy chatting 😎"
];

if(str_contains($msgLower,".ai")){
$botReply = $aiReplies[array_rand($aiReplies)];
}

/* Music Command */
if(str_contains($msgLower,".music")){
$botReply = "🎵 Music Auto Play:\n".
"https://files.catbox.moe/c13r4x.mp4";
}

/* GIF Command */
if(str_contains($msgLower,".gif")){
$botReply = "🔥 Random GIF:\n".
"https://media.giphy.com/media/ICOgUNjpvO0PC/giphy.gif";
}

/*
============================
SEND USER MESSAGE
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
