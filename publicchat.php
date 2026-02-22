<?php
session_start();

if(isset($_POST['username'])){
    $_SESSION['username'] = $_POST['username'];
    header("Location: publicchat.php");
    exit;
}

if(!isset($_SESSION['username'])){
?>

<form method="POST">
<h3>Enter Username</h3>
<input name="username" required placeholder="Username">
<button>Join Chat</button>
</form>

<?php
exit;
}
?>

<html>
<head>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

<h3>Public Chat</h3>

<div id="chat"></div>

<input id="msg" placeholder="Type message">
<button onclick="sendMsg()">Send</button>

<script>

var pusher = new Pusher("<?= getenv('PUSHER_KEY') ?>",{
    cluster:"<?= getenv('PUSHER_CLUSTER') ?>",
    forceTLS:true
});

var channel = pusher.subscribe("public-chat");

channel.bind("new-message", function(data){

    $("#chat").append(
        "<p><b>"+data.user+":</b> "+data.msg+"</p>"
    );

});

function sendMsg(){

    $.post("send_public.php",{
        msg:$("#msg").val()
    });

    $("#msg").val("");

}

</script>

</body>
</html>
