<?php
session_start();

$validUsername = 'admin';
$validPassword = 'admin123';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    if ($user === $validUsername && $pass === $validPassword) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: /admin.php');
        exit;
    } else {
        $err = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Login</title>
    <style>
        body{font-family:Arial, sans-serif;background:#f5f5f5}
        .login{max-width:360px;margin:80px auto;padding:20px;background:#fff;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,.1)}
        input{width:100%;padding:10px;margin:8px 0;border:1px solid #ddd;border-radius:4px}
        button{padding:10px 16px;background:#2c3e50;color:#fff;border:none;border-radius:4px;cursor:pointer}
        .error{color:#e74c3c}
    </style>
</head>
<body>
<div class="login">
    <h2>Admin Login</h2>
    <?php if ($err): ?><div class="error"><?=htmlspecialchars($err)?></div><?php endif; ?>
    <form method="post">
        <label>Username</label>
        <input name="username" required autofocus />
        <label>Password</label>
        <input type="password" name="password" required />
        <div style="text-align:right;margin-top:10px">
            <button type="submit">Login</button>
        </div>
    </form>
</div>
</body>
</html>