<?php
session_start();
require_once 'auth/auth_middleware.php';
require_once 'database.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>我的进度 - 熙熙雅思</title>
    <link rel="stylesheet" href="shared/vars.css">
    <style>body{padding-top:20px;}</style>
</head>
<body>
<?php include 'shared/topnav.php'; ?>

<div class="xh-container">
    <h1>📊 我的练习进度</h1>
    <?php
    $stmt = $pdo->prepare("SELECT * FROM user_progress WHERE user_id = ? ORDER BY last_attempt DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $rows = $stmt->fetchAll();
    if (empty($rows)): ?>
        <p style="color:#64748b;text-align:center;padding:60px;">暂无练习记录，开始刷题后这里会显示你的进度～</p>
    <?php else: ?>
    <table style="width:100%; border-collapse:collapse; margin-top:20px;">
        <tr style="background:#f1f5f9;">
            <th style="padding:12px;text-align:left;">练习类型</th>
            <th style="padding:12px;text-align:left;">题目编号</th>
            <th style="padding:12px;">尝试次数</th>
            <th style="padding:12px;">正确率</th>
            <th style="padding:12px;">Band估分</th>
            <th style="padding:12px;">最后练习</th>
        </tr>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td style="padding:12px;"><?= htmlspecialchars($row['exercise_type']) ?></td>
            <td style="padding:12px;"><?= htmlspecialchars($row['exercise_id']) ?></td>
            <td style="padding:12px;text-align:center;"><?= $row['attempt_count'] ?></td>
            <td style="padding:12px;text-align:center;"><?= $row['correct_rate'] ?>%</td>
            <td style="padding:12px;text-align:center;"><?= $row['band_estimate'] ?></td>
            <td style="padding:12px;"><?= $row['last_attempt'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>
</body>
</html>
