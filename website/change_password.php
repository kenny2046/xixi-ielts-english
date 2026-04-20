<?php
session_start();
require_once 'auth/auth_middleware.php';   // 必须登录才能访问
require_once 'database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "❌ 两次输入的新密码不一致";
    } elseif (strlen($new_password) < 6) {
        $message = "❌ 新密码至少需要6个字符";
    } else {
        // 验证旧密码
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($old_password, $user['password_hash'])) {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            if ($stmt->execute([$new_hash, $_SESSION['user_id']])) {
                $message = "✅ 密码修改成功！下次登录请使用新密码";
            } else {
                $message = "❌ 修改失败，请稍后再试";
            }
        } else {
            $message = "❌ 旧密码错误";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>修改密码 - 熙熙雅思</title>
    <link rel="stylesheet" href="shared/vars.css">
    <style>body { padding: 40px; background: #f8fafc; }</style>
</head>
<body>
<?php include 'shared/topnav.php'; ?>

<div class="xh-container" style="max-width:460px;margin:40px auto;background:white;padding:40px;border-radius:20px;box-shadow:0 10px 30px rgba(0,0,0,0.1);">
    <h1>🔐 修改密码</h1>

    <?php if($message): ?>
        <div style="padding:15px;border-radius:12px;margin:20px 0;<?= strpos($message,'✅')!==false?'background:#ecfdf5;color:#10b981':'background:#fee2e2;color:#ef4444' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <p>旧密码：<input type="password" name="old_password" required style="width:100%;padding:12px;margin:8px 0;border:2px solid #e2e8f0;border-radius:12px;"></p>
        <p>新密码：<input type="password" name="new_password" required style="width:100%;padding:12px;margin:8px 0;border:2px solid #e2e8f0;border-radius:12px;"></p>
        <p>确认新密码：<input type="password" name="confirm_password" required style="width:100%;padding:12px;margin:8px 0;border:2px solid #e2e8f0;border-radius:12px;"></p>
        
        <button type="submit" class="xh-btn" style="width:100%;margin-top:20px;">确认修改密码</button>
    </form>

    <p style="text-align:center;margin-top:30px;">
        <a href="index.php" style="color:#3b82f6;">← 返回首页</a>
    </p>
</div>
</body>
</html>
