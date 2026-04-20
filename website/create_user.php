<?php
require_once 'database.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("只有管理员才能创建新账号");
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    if (strlen($password) < 6) {
        $message = "❌ 密码至少需要6个字符";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) 
                               VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $hash, $role])) {
            $message = "✅ 新账号创建成功！<br>用户名：<strong>$username</strong><br>密码：<strong>$password</strong>";
        } else {
            $message = "❌ 创建失败（用户名或邮箱可能已存在）";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>创建新账号 - 管理员面板</title>
    <link rel="stylesheet" href="shared/vars.css">
</head>
<body style="padding:40px;background:#f8fafc;">
<div style="max-width:500px;margin:0 auto;background:white;padding:40px;border-radius:20px;box-shadow:0 10px 30px rgba(0,0,0,0.1);">
    <h1>👤 创建新用户账号</h1>
    
    <?php if($message): ?>
        <div style="padding:15px;border-radius:12px;margin:20px 0;background:#ecfdf5;color:#10b981;"><?= $message ?></div>
    <?php endif; ?>

    <form method="post">
        <p>用户名：<input type="text" name="username" required style="width:100%;padding:12px;margin:8px 0;border:2px solid #e2e8f0;border-radius:12px;"></p>
        <p>邮箱：<input type="email" name="email" required style="width:100%;padding:12px;margin:8px 0;border:2px solid #e2e8f0;border-radius:12px;"></p>
        <p>密码：<input type="text" name="password" required style="width:100%;padding:12px;margin:8px 0;border:2px solid #e2e8f0;border-radius:12px;" placeholder="建议至少6位"></p>
        
        <p>角色：
            <select name="role" style="width:100%;padding:12px;margin:8px 0;border:2px solid #e2e8f0;border-radius:12px;">
                <option value="student">学生 (Student)</option>
                <option value="teacher">老师 (Teacher)</option>
                <option value="admin">管理员 (Admin)</option>
            </select>
        </p>
        
        <button type="submit" class="xh-btn" style="width:100%;margin-top:20px;">创建账号</button>
    </form>

    <p style="text-align:center;margin-top:30px;">
        <a href="index.php" style="color:#3b82f6;">← 返回首页</a>
    </p>
</div>
</body>
</html>
