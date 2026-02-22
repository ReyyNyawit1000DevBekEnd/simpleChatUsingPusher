<?php
session_start();
require "vendor/autoload.php";
require "config.php";

if(!isset($_SESSION['username'])) exit;

$msg = trim($_POST['msg'] ?? "");
if($msg=="") exit;

/*
=====================================
USER DATA
=====================================
*/

$user = $_SESSION['username'];
$avatar = $_SESSION['avatar'] ?? "https://api.dicebear.com/7.x/bottts/svg?seed=".$user;

/*
=====================================
XP + LEVEL SYSTEM
=====================================
*/

if(!isset($_SESSION['xp'])) $_SESSION['xp']=0;
if(!isset($_SESSION['level'])) $_SESSION['level']=1;
if(!isset($_SESSION['title'])) $_SESSION['title']="npc";

/*
XP GAIN PER CHAT
*/

$_SESSION['xp'] += rand(5,15);

/*
LEVEL CALCULATION
*/

$levelUpXp = $_SESSION['level'] * 100;

if($_SESSION['xp'] >= $levelUpXp){
$_SESSION['level']++;
$_SESSION['xp']=0;

/* TITLE REWARD */

$level = $_SESSION['level'];

if($level>=2) $_SESSION['title']="knight";
if($level>=5) $_SESSION['title']="earl";
if($level>=8) $_SESSION['title']="archduke";
if($level>=12) $_SESSION['title']="king";
if($level>=18) $_SESSION['title']="emperor";

$levelUpMsg = "🏆 LEVEL UP!\n".
"Level : ".$_SESSION['level']."\n".
"Title : ".strtoupper($_SESSION['title']);

}

/*
=====================================
BOT COMMAND
=====================================
*/

$botReply="";

/* MENU */
if(str_contains(strtolower($msg),".menu")){

$botReply=
"💀 REYY GOD BOT MENU\n\n".
"Commands:\n".
".menu\n".
".ai\n".
".title\n".
".level\n".
".xp\n\n".
"🏆 TITLES:\n".
"npc → starter\n".
"knight → lvl2\n".
"earl → lvl5\n".
"archduke → lvl8\n".
"king → lvl12\n".
"emperor → lvl18\n".
"kang spoiler\n".
"kang bokep\n".
"developer\n".
"etmin";
}

/* AI */
$aiReplies=[
"Chat enjoy 😈",
"Stay cool 🔥",
"I am ReyyBot 🤖",
"Type .menu for help",
"Keep grinding XP 🧠"
];

if(str_contains(strtolower($msg),".ai")){
$botReply=$aiReplies[array_rand($aiReplies)];
}

/* SHOW LEVEL */
if(str_contains(strtolower($msg),".level")){
$botReply=
"🏆 STATUS\n".
"Level: ".$_SESSION['level']."\n".
"XP: ".$_SESSION['xp']." / ".$levelUpXp."\n".
"Title: ".strtoupper($_SESSION['title']);
}

/* SHOW XP */
if(str_contains(strtolower($msg),".xp")){
$botReply="🔥 XP : ".$_SESSION['xp']." / ".$levelUpXp;
}

/*
=====================================
PUSHER SEND MESSAGE
=====================================
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

/* SEND USER MESSAGE */

$pusher->trigger(
"public-chat",
"new-message",
[
"user"=>$user,
"avatar"=>$avatar,
"msg"=>$msg,
"title"=>$_SESSION['title'],
"level"=>$_SESSION['level']
]
);

/* BOT REPLY */

if(!empty($botReply)){

sleep(1);

$pusher->trigger(
"public-chat",
"new-message",
[
"user"=>"ChatBot 🤖",
"avatar"=>"https://api.dicebear.com/7.x/bottts/svg?seed=bot",
"msg"=>$botReply,
"title"=>"system"
]
);

}

/* LEVEL UP MESSAGE */

if(isset($levelUpMsg)){

$pusher->trigger(
"public-chat",
"new-message",
[
"user"=>"System 🏆",
"avatar"=>"",
"msg"=>$levelUpMsg,
"title"=>"system"
]
);

}

?>
