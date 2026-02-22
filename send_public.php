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
TITLE SYSTEM
=====================================
*/

if(!isset($_SESSION['title'])){
$_SESSION['title']="npc";
}

/* NEW USER AUTO TITLE */
if(!isset($_SESSION['first_join'])){
$_SESSION['first_join']=true;
$_SESSION['title']="npc";
}

/*
TITLE LIST
*/

$titles = [
"npc",
"knight",
"earl",
"archduke",
"king",
"emperor",
"kang spoiler",
"kang bokep",
"developer",
"etmin"
];

/*
=====================================
BOT COMMAND ENGINE
=====================================
*/

$botReply="";

/* MENU COMMAND */
if(str_contains(strtolower($msg),".menu")){

$botReply =
"💀 REYY CHAT MENU\n\n".
"Commands:\n".
".menu = show menu\n".
".ai = random ai reply\n".
".title = show your title\n".
".giveme title = change title (test)\n\n".
"🏆 TITLE LIST:\n".
"- npc\n".
"- knight\n".
"- earl\n".
"- archduke\n".
"- king\n".
"- emperor\n".
"- kang spoiler\n".
"- kang bokep\n".
"- developer\n".
"- etmin";
}

/* AI RANDOM */
$aiReplies=[
"Chat enjoy 😈",
"Stay cool bro 🔥",
"I am ReyyBot 🤖",
"Need help? type .menu",
"Keep chatting 🧠"
];

if(str_contains(strtolower($msg),".ai")){
$botReply = $aiReplies[array_rand($aiReplies)];
}

/* SHOW TITLE */
if(str_contains(strtolower($msg),".title")){
$botReply="🏆 Your Title : ".strtoupper($_SESSION['title']);
}

/* CHANGE TITLE TEST */
if(str_contains(strtolower($msg),"giveme title")){
foreach($titles as $t){
if(str_contains(strtolower($msg),$t)){
$_SESSION['title']=$t;
$botReply="✅ Title changed to ".strtoupper($t);
}
}
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

$pusher->trigger("public-chat","new-message",[
"user"=>$user,
"avatar"=>$avatar,
"msg"=>$msg,
"title"=>$_SESSION['title']
]);

/* BOT REPLY */

if($botReply){

sleep(1);

$pusher->trigger("public-chat","new-message",[
"user"=>"ChatBot 🤖",
"avatar"=>"https://api.dicebear.com/7.x/bottts/svg?seed=bot",
"msg"=>$botReply,
"title"=>"system"
]);

}

?>
