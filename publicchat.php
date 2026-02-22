<?php
session_start();
require "vendor/autoload.php";
require "config.php";

/*
LOGIN CHECK
*/

if(!isset($_SESSION['username'])){
if($_SERVER['REQUEST_METHOD']=="POST"){
$_SESSION['username']=$_POST['username'];
$_SESSION['avatar']="https://api.dicebear.com/7.x/bottts/svg?seed=".$_POST['username'];

$_SESSION['xp']=0;
$_SESSION['level']=1;
$_SESSION['title']="npc";

header("Location: publicchat.php");
exit;
}
?>

<form method="POST" style="text-align:center;margin-top:150px">
<h2>Enter Username</h2>
<input name="username" required placeholder="Username">
<button>Join Chat</button>
</form>

<?php
exit;
}

if(!isset($_SESSION['xp'])){
$_SESSION['xp']=0;
$_SESSION['level']=1;
$_SESSION['title']="npc";
}

$username=$_SESSION['username'];
$avatar=$_SESSION['avatar'];
?>

<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width,initial-scale=1">

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{
font-family:Inter,sans-serif;

background:url("https://files.catbox.moe/c13r4x.mp4");
background-size:cover;
background-position:center;

height:100vh;
display:flex;
justify-content:center;
align-items:center;
}

.chat{
width:95%;
max-width:900px;
height:90vh;

background:rgba(255,255,255,.9);
backdrop-filter:blur(15px);

border-radius:20px;
display:flex;
flex-direction:column;
overflow:hidden;
}

/* HEADER */

.header{
padding:15px;
background:linear-gradient(135deg,#667eea,#764ba2);
color:white;
display:flex;
justify-content:space-between;
}

/* CHAT BOX */

#chat-box{
flex:1;
padding:20px;
overflow-y:auto;
display:flex;
flex-direction:column;
gap:12px;
}

/* MESSAGE */

.msg{
display:flex;
gap:10px;
max-width:75%;
animation:pop .3s ease;
}

.msg.right{
margin-left:auto;
flex-direction:row-reverse;
}

.avatar{
width:40px;
height:40px;
border-radius:50%;
}

/* BUBBLE */

.bubble{
padding:12px 16px;
border-radius:18px;
background:white;
word-break:break-word;
}

.msg.right .bubble{
background:linear-gradient(135deg,#667eea,#764ba2);
color:white;
}

/* INPUT */

.input{
padding:15px;
display:flex;
gap:10px;
border-top:1px solid #eee;
}

input{
flex:1;
padding:14px;
border-radius:25px;
border:2px solid #eee;
}

button{
padding:12px 20px;
border:none;
border-radius:25px;
background:#667eea;
color:white;
font-weight:600;
cursor:pointer;
}

@keyframes pop{
from{opacity:0;transform:translateY(10px);}
to{opacity:1;transform:translateY(0);}
}

</style>

</head>

<body>

<audio autoplay loop>
<source src="https://files.catbox.moe/c13r4x.mp4">
</audio>

<div class="chat">

<div class="header">
<div>
<h3>Public Chat | ReyyZhouu</h3>
<div style="font-size:12px">
<?= htmlspecialchars($username) ?>
</div>
</div>

<div>
<?= $_SESSION['title'] ?> | Lv <?= $_SESSION['level'] ?>
</div>
</div>

<div id="chat-box">
<div class="empty" style="text-align:center;color:#888">
Start chatting 😈
</div>
</div>

<div class="input">
<input id="msg" placeholder="Type message..."
onkeypress="if(event.keyCode==13) sendMsg()">

<button onclick="sendMsg()">Send</button>
</div>

</div>

<script>

var user="<?= $username ?>";
var avatar="<?= $avatar ?>";

var pusher=new Pusher("<?= PUSHER_KEY ?>",{
cluster:"<?= PUSHER_CLUSTER ?>",
forceTLS:true
});

var channel=pusher.subscribe("public-chat");

/* RECEIVE */

channel.bind("new-message",function(data){

$(".empty").remove();

$("#chat-box").append(`
<div class="msg ${data.user==user?'right':''}">
<img class="avatar" src="${data.avatar}">
<div>
<div style="font-size:12px;color:#666">
${data.user} [${data.title}]
</div>

<div class="bubble">
${escapeHtml(data.msg)}
</div>

</div>
</div>
`);

scrollBottom();

});

/* SEND */

function sendMsg(){

let msg=$("#msg").val();
if(msg.trim()=="") return;

/*
BOT COMMANDS
*/

if(msg.toLowerCase().includes(".menu")){
msg="📜 MENU\n.ai\n.menu\n.music\n.gif";
}

if(msg.toLowerCase().includes(".ai")){
let ai=[
"Chat enjoy 😈",
"Stay cool 🔥",
"ReyyBot Online 🤖"
];

msg=ai[Math.floor(Math.random()*ai.length)];
}

/* XP LEVEL UP */

<?php
$_SESSION['xp']+=5;

if($_SESSION['xp']>100){
$_SESSION['level']++;

$_SESSION['xp']=0;

$titles=[
"npc","knight","earl","archduke",
"king","emperor","developer"
];

$idx=min($_SESSION['level']-1,count($titles)-1);
$_SESSION['title']=$titles[$idx];
}
?>

$.post("send_public.php",{msg:msg});

$("#msg").val("");

}

function scrollBottom(){
let box=$("#chat-box")[0];
box.scrollTop=box.scrollHeight;
}

function escapeHtml(text){
return $("<div>").text(text).html();
}

</script>

</body>
</html>
