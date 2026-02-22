<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

/*
=====================================
REDIRECT IF LOGIN
=====================================
*/

if (isset($_SESSION['user_id'])) {
    header("Location: publicchat.php");
    exit;
}

/*
=====================================
LOGIN PROCESS
=====================================
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT * FROM users 
        WHERE email=?
        LIMIT 1
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {

        /*
        IMPORTANT:
        Gunakan password_verify untuk keamanan
        */

        if(password_verify($password,$user['password'])){

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: publicchat.php");
            exit;
        }
    }

    $error = "Invalid email or password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login Chat</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{
font-family:Inter,sans-serif;
background:linear-gradient(135deg,#667eea,#764ba2);
height:100vh;
display:flex;
justify-content:center;
align-items:center;
}

.login-container{
width:100%;
max-width:450px;
background:white;
border-radius:20px;
box-shadow:0 20px 60px rgba(0,0,0,.3);
overflow:hidden;
}

.login-header{
padding:40px;
text-align:center;
color:white;
background:linear-gradient(135deg,#667eea,#764ba2);
}

.login-form{
padding:40px;
}

.form-group{
margin-bottom:20px;
}

.form-group input{
width:100%;
padding:14px;
border-radius:12px;
border:2px solid #e2e8f0;
outline:none;
transition:.3s;
}

.form-group input:focus{
border-color:#667eea;
}

.login-btn{
width:100%;
padding:14px;
border:none;
border-radius:12px;
background:linear-gradient(135deg,#667eea,#764ba2);
color:white;
font-weight:600;
cursor:pointer;
}

.error{
background:#fee;
padding:12px;
border-left:4px solid red;
margin-bottom:20px;
border-radius:8px;
}

.signup-link{
text-align:center;
margin-top:20px;
font-size:14px;
}
</style>

</head>

<body>

<div class="login-container">

<div class="login-header">
<h2>Welcome Back</h2>
<p>Login to continue chat</p>
</div>

<div class="login-form">

<?php if(isset($error)): ?>
<div class="error">
<?php echo $error; ?>
</div>
<?php endif; ?>

<form method="POST">

<div class="form-group">
<input type="email" name="email" placeholder="Email" required>
</div>

<div class="form-group">
<input type="password" name="password" placeholder="Password" required>
</div>

<button class="login-btn" type="submit">Login</button>

</form>

<div class="signup-link">
Start chat using username only<br>
<a href="publicchat.php" style="color:#667eea;font-weight:600">
Join Public Chat
</a>
</div>

</div>
</div>

</body>
</html>
