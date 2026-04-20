<?php
session_start();
require_once '../database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        header("Location: ../index.php");
        exit;
    } else {
        $error = "用户名或密码错误";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="/shared/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 熙熙雅思英语</title>
    <link rel="stylesheet" href="../shared/vars.css">
    <style>
        body { background: linear-gradient(135deg, #0f172a, #1e2937); }
        .login-box {
            max-width: 420px;
            margin: 80px auto;
            background: white;
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }
        .xh-btn { width: 100%; padding: 14px; font-size: 1.1rem; }
    </style>
</head>
<body>
<div class="login-box">
    <h1 style="text-align:center;color:#1e40af;margin-bottom:8px;">🎯 熙熙雅思</h1>
    <p style="text-align:center;color:#64748b;margin-bottom:30px;">请登录你的账号</p>

    <?php if($error): ?>
        <div style="background:#fee2e2;color:#ef4444;padding:12px;border-radius:12px;margin-bottom:20px;text-align:center;"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="用户名" required 
               style="width:100%;padding:14px;margin-bottom:16px;border:2px solid #e2e8f0;border-radius:12px;font-size:1.05rem;">
        <input type="password" name="password" placeholder="密码" required 
               style="width:100%;padding:14px;margin-bottom:24px;border:2px solid #e2e8f0;border-radius:12px;font-size:1.05rem;">
        <button type="submit" class="xh-btn">立即登录</button>
    </form>

    <p style="text-align:center;margin-top:24px;color:#64748b;">
        <a href="../index.php" style="color:#3b82f6;">← 返回首页</a>
    </p>
</div>
</body>
</html>
