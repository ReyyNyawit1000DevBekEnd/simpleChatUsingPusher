<?php
session_start();
require 'db.php';
require 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$other_id = $_GET['user_id'] ?? 1;

// conversation check (both directions)
$stmt = $conn->prepare("
SELECT id FROM conversations 
WHERE (buyer_id=? AND seller_id=?) 
   OR (buyer_id=? AND seller_id=?)
");

$stmt->bind_param("iiii", $user_id, $other_id, $other_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $conversation_id = $row['id'];
} else {
    // assign buyer/seller correctly
    if($role == 'buyer'){
        $buyer_id = $user_id;
        $seller_id = $other_id;
    } else {
        $buyer_id = $other_id;
        $seller_id = $user_id;
    }

    $stmt = $conn->prepare("INSERT INTO conversations (buyer_id, seller_id) VALUES (?,?)");
    $stmt->bind_param("ii", $buyer_id, $seller_id);
    $stmt->execute();
    $conversation_id = $conn->insert_id;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?php echo ucfirst($role); ?></title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .chat-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 900px;
            height: 600px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .header-info h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .header-info .role-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.25);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        #chat-box {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        #chat-box::-webkit-scrollbar {
            width: 8px;
        }

        #chat-box::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #chat-box::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        #chat-box::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .message {
            display: flex;
            margin-bottom: 8px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Left side - Other user messages */
        .message.left {
            justify-content: flex-start;
        }

        /* Right side - Own messages */
        .message.right {
            justify-content: flex-end;
        }

        .message-wrapper {
            display: flex;
            align-items: flex-end;
            max-width: 70%;
            gap: 8px;
        }

        .message.right .message-wrapper {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 13px;
            flex-shrink: 0;
        }

        .message.left .message-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .message.right .message-avatar {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .message-content {
            display: flex;
            flex-direction: column;
        }

        .message-sender {
            font-size: 11px;
            color: #718096;
            margin-bottom: 4px;
            font-weight: 500;
            padding: 0 4px;
        }

        .message.right .message-sender {
            text-align: right;
        }

        .message-bubble {
            padding: 10px 14px;
            border-radius: 16px;
            word-wrap: break-word;
            line-height: 1.5;
            font-size: 14px;
            position: relative;
        }

        /* Left side bubble styling */
        .message.left .message-bubble {
            background: white;
            color: #2d3748;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Right side bubble styling */
        .message.right .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
            box-shadow: 0 1px 2px rgba(102, 126, 234, 0.3);
        }

        .chat-input-container {
            background: white;
            padding: 20px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        #message {
            flex: 1;
            border: 2px solid #e2e8f0;
            border-radius: 24px;
            padding: 12px 20px;
            font-size: 15px;
            font-family: inherit;
            outline: none;
            transition: all 0.3s ease;
        }

        #message:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .send-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 24px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
        }

        .send-btn:active {
            transform: translateY(0);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #a0aec0;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 16px;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .chat-container {
                height: 100vh;
                max-height: 100vh;
                border-radius: 0;
            }

            .message-wrapper {
                max-width: 85%;
            }

            .chat-header {
                padding: 16px 20px;
            }

            .header-info h3 {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>

<div class="chat-container">
    <div class="chat-header">
        <div class="header-left">
            <div class="user-avatar">
                <?php echo strtoupper(substr($role, 0, 1)); ?>
            </div>
            <div class="header-info">
                <h3>Chat Room</h3>
                <span class="role-badge"><?php echo ucfirst($role); ?></span>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div id="chat-box">
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <p>No messages yet. Start the conversation!</p>
        </div>
    </div>

    <div class="chat-input-container">
        <input type="text" id="message" placeholder="Type your message..." onkeypress="if(event.keyCode==13) sendMessage()">
        <button class="send-btn" onclick="sendMessage()">Send</button>
    </div>
</div>

<script>

var PUSHER_KEY = "<?= PUSHER_KEY ?>";
var PUSHER_CLUSTER = "<?= PUSHER_CLUSTER ?>";

var conversation_id = <?= $conversation_id ?>;
var user_id = <?= $user_id ?>;

var pusher = new Pusher(PUSHER_KEY, {
    cluster: PUSHER_CLUSTER,
    authEndpoint: "auth.php"
});

var channel = pusher.subscribe("chat-test");

$(document).ready(function(){
    $.get("fetch_messages.php", { conversation_id: conversation_id }, function(data){
        if(data.trim()) {
            $("#chat-box").html(data);
        }
        scrollBottom();
    });
});

channel.bind("new-message", function(data) {
    // Check karein ki message bhejne wala current user hai ya nahi
    var isOwn = data.sender_id == user_id;
    var messageClass = isOwn ? 'right' : 'left';
    var senderName = isOwn ? 'You' : 'User ' + data.sender_id;
    var avatarText = isOwn ? 'Y' : 'U' + data.sender_id;
    
    // Agar "No messages yet" wala text hai toh use hata dein
    $('.empty-state').remove();
    
    // Naya message append karein sahi classes ke saath
    $("#chat-box").append(
        '<div class="message ' + messageClass + '">' +
            '<div class="message-wrapper">' +
                '<div class="message-avatar">' + avatarText.charAt(0) + '</div>' +
                '<div class="message-content">' +
                    '<div class="message-sender">' + senderName + '</div>' +
                    '<div class="message-bubble">' + escapeHtml(data.message) + '</div>' +
                '</div>' +
            '</div>' +
        '</div>'
    );
    
    scrollBottom();
});

function sendMessage() {
    var msg = $("#message").val();
    if(msg.trim() == '') return;

    $.post("send_message.php", {
        conversation_id: conversation_id,
        message: msg
    }, function() {
        $("#message").val("");
    });
}

function scrollBottom(){
    $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);
}

function escapeHtml(text) {
    return $('<div>').text(text).html();
}

</script>

</body>
</html>