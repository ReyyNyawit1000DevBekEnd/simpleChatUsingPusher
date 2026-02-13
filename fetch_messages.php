<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user_id'])){
    exit;
}

$user_id = $_SESSION['user_id'];
$conversation_id = $_GET['conversation_id'] ?? 0;

// Prepared statement use karein SQL injection se bachne ke liye
$stmt = $conn->prepare("
    SELECT m.*, u.name, u.role 
    FROM messages m
    LEFT JOIN users u ON m.sender_id = u.id
    WHERE m.conversation_id = ?
    ORDER BY m.created_at ASC
");

$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$result = $stmt->get_result();

// Agar koi message nahi hai
if($result->num_rows == 0) {
    echo '<div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <p>No messages yet. Start the conversation!</p>
          </div>';
    exit;
}

// Har message ko proper design ke saath render karein
while($row = $result->fetch_assoc()) {
    // Check karein ki message current user ka hai ya nahi
    $isOwn = ($row['sender_id'] == $user_id);
    
    // Left ya right class lagayein
    $messageClass = $isOwn ? 'right' : 'left';
    
    // Sender ka naam
    $senderName = $isOwn ? 'You' : ($row['name'] ?? 'User ' . $row['sender_id']);
    
    // Avatar text (first letter)
    if($isOwn) {
        $avatarText = strtoupper(substr($_SESSION['role'] ?? 'Y', 0, 1));
    } else {
        $avatarText = strtoupper(substr($row['name'] ?? 'U', 0, 1));
    }
    
    // Message ko safe banayein (XSS attack se bachne ke liye)
    $message = htmlspecialchars($row['message']);
    
    // Proper HTML structure output karein
    echo '<div class="message ' . $messageClass . '">
            <div class="message-wrapper">
                <div class="message-avatar">' . $avatarText . '</div>
                <div class="message-content">
                    <div class="message-sender">' . htmlspecialchars($senderName) . '</div>
                    <div class="message-bubble">' . $message . '</div>
                </div>
            </div>
          </div>';
}

$stmt->close();
?>