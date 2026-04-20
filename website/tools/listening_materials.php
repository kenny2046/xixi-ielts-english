<?php
session_start();
require_once '../auth/auth_middleware.php';
require_once '../database.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>🎧 雅思听力素材库 - 熙熙雅思</title>
    <link rel="stylesheet" href="../shared/vars.css">
    <style>
        .item { background:#f8fafc; padding:15px; border-radius:12px; margin:12px 0; }
        .upload-box { background:#fff; padding:30px; border-radius:20px; border:2px dashed #3b82f6; text-align:center; }
    </style>
</head>
<body>
<?php include '../shared/topnav.php'; ?>

<div class="xh-container" style="padding-top:32px;">
    <h1>🎧 雅思听力素材库</h1>
    <p style="color:#64748b;">支持音频/视频 • 数据库管理 • 仅登录用户可用</p>

    <!-- 上传区域 -->
    <div class="upload-box">
        <form method="post" enctype="multipart/form-data" action="listening_materials_upload.php">
            <input type="file" name="file" accept="audio/*,video/*" required><br><br>
            <input type="text" name="custom_name" placeholder="自定义素材名称（可选）" style="width:80%;padding:12px;border-radius:12px;border:1px solid #e2e8f0;">
            <br><br>
            <button type="submit" class="xh-btn">📤 上传素材</button>
        </form>
    </div>

    <!-- 素材列表 -->
    <?php
    $stmt = $pdo->query("SELECT * FROM listening_materials ORDER BY uploaded_at DESC");
    $items = $stmt->fetchAll();
    if (empty($items)): ?>
        <p style="text-align:center;color:#64748b;padding:60px;">暂无听力素材</p>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
        <div class="item">
            <strong><?= htmlspecialchars($item['original_name']) ?></strong><br>
            <?php if (str_ends_with($item['filename'], ['.mp3','.wav','.m4a'])): ?>
                <audio controls src="listening_materials/<?= htmlspecialchars($item['filename']) ?>"></audio>
            <?php else: ?>
                <video controls width="100%" src="listening_materials/<?= htmlspecialchars($item['filename']) ?>"></video>
            <?php endif; ?>
            <small style="color:#94a3b8;">上传时间：<?= $item['uploaded_at'] ?></small>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
