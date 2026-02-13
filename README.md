# 💬 simpleChatUsingPusher

A simple real-time chat application built using **PHP, MySQL, and Pusher**.

This project demonstrates how to implement **live chat without page refresh** using Pusher events.

---

## 🚀 Features

* Real-time messaging using Pusher
* Login system
* Conversation-based chat
* PHP + MySQL backend
* Public channel implementation
* Simple and beginner-friendly structure

---

## 🛠 Tech Stack

* Core PHP
* MySQL
* JavaScript / jQuery
* Pusher Channels
* Composer

---

## 📁 Project Structure

```id="x19s1l"
simpleChatUsingPusher/
│
├── index.php        → Login Screen
├── chat.php         → Chat Screen
├── send_message.php
├── fetch_messages.php
├── db.php
├── config.php       (NOT committed)
└── vendor/
```

---

## 📦 Installation

Clone repository:

```bash id="61a70l"
git clone https://github.com/buntyjangir0/simpleChatUsingPusher.git
cd simpleChatUsingPusher
```

Install Pusher SDK:

```bash id="3cl7j9"
composer require pusher/pusher-php-server
```

---

## 🔐 Pusher Setup

1. Create account → https://pusher.com
2. Create a new app
3. Copy credentials:

```
APP_ID
KEY
SECRET
CLUSTER
```

---

## ⚠️ Security (Important)

Create a local file:

```
config.php
```

Add to `.gitignore`:

```id="pd22gd"
config.php
/vendor
.env
```

---

## ⚙️ config.php Example

```php id="9cqokh"
<?php

define('PUSHER_APP_ID', 'YOUR_APP_ID');
define('PUSHER_KEY', 'YOUR_KEY');
define('PUSHER_SECRET', 'YOUR_SECRET');
define('PUSHER_CLUSTER', 'ap2');
```

---

## 🗄 Database Setup

Create database:

```sql id="vntb0p"
CREATE DATABASE chat_demo;
USE chat_demo;
```

---

### 1️⃣ users table

Stores login users (buyer/seller).

```sql id="mnylue"
CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(150),
    password VARCHAR(255),
    role ENUM('buyer','seller'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Sample data:

```sql id="d3sbgp"
INSERT INTO users (name,email,password,role) VALUES
('Buyer User','buyer@test.com','123456','buyer'),
('Seller User','seller@test.com','123456','seller');
```

---

### 2️⃣ conversations table

Stores chat relationship between users.

```sql id="08njys"
CREATE TABLE conversations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    buyer_id BIGINT NOT NULL,
    seller_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### 3️⃣ messages table

Stores all chat messages.

```sql id="2skk0q"
CREATE TABLE messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT NOT NULL,
    sender_id BIGINT NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🔑 Application Flow

### Login Page

```
index.php
```

* User logs in
* Session created
* Redirects to chat screen

---

### Chat Page

```
chat.php
```

* Loads conversation
* Subscribes to Pusher channel
* Sends & receives messages in real-time

---

## 📡 Pusher Channel

Public channel used:

```
chat-channel
```

---

### Backend Trigger

```php id="9dsbjv"
$pusher->trigger('chat-channel', 'new-message', $data);
```

---

### Frontend Listener

```javascript id="0cjx42"
var channel = pusher.subscribe("chat-channel");

channel.bind("new-message", function(data) {
    console.log(data);
});
```

---

## ▶️ Run Project

Start Apache & MySQL.

Open:

```
http://localhost/simpleChatUsingPusher/index.php
```

Login in two browsers and start chatting.

Messages appear instantly without refresh.

---

## 🧠 How Real-Time Works

```
User A → PHP Backend → MySQL
                      ↓
                   Pusher
                      ↓
User B receives message instantly
```

---

## 🔄 If Credentials Leak

1. Rotate keys from Pusher dashboard
2. Update `config.php`
3. Restart server

---

## 🚀 Future Improvements

* Private chat channels
* Typing indicator
* Online users
* Read receipts
* File sharing
* Laravel broadcasting integration

---

## 👨‍💻 Purpose

Learning project demonstrating real-time communication using PHP + Pusher.

---

⭐ Star the repo if you found it useful!
