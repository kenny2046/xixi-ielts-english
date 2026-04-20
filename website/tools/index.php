<?php
require_once "../auth/auth_middleware.php";
?>
<?php
// ===== 熙熙雅思工具箱首页 v2.0 =====
$exclude = ['index.php','index.html','navigation.json','listening_materials.json','order.json'];

// 读取工具描述
$descFile = __DIR__ . '/../shared/tools_desc.json';
$toolsDesc = file_exists($descFile) ? (json_decode(file_get_contents($descFile), true) ?: []) : [];

$files = glob(__DIR__ . "/*.html") ?: [];
$tools = [];

foreach ($files as $filePath) {
    $file = basename($filePath);
    if (in_array($file, $exclude)) continue;

    $content = @file_get_contents($filePath);
    $title   = '';
    if ($content && preg_match('/<title>(.*?)<\/title>/is', $content, $m)) {
        $title = trim(strip_tags($m[1]));
    }
    if (!$title) {
        $name  = basename($file, '.html');
        $title = str_replace(['IAGS_','_v','_'],[' ',' v',' '], $name);
        $title = trim(preg_replace('/\s+/',' ', $title));
    }

    $emojiMap = ['写作'=>'✍️','口语'=>'🎙️','听力'=>'👂','阅读'=>'📖','词汇'=>'📚',
                 'PDF'=>'📄','音频'=>'🎵','行列'=>'📊','电子书'=>'📱','工厂'=>'🏭','切片'=>'✂️'];
    $emoji = '🛠️';
    foreach ($emojiMap as $k => $e) { if (mb_strpos($title,$k) !== false) { $emoji = $e; break; } }

    $desc = $toolsDesc[$file]['desc'] ?? '点击进入，探索此工具的功能';

    $tools[] = ['filename'=>$file,'title'=>$title,'emoji'=>$emoji,'mtime'=>filemtime($filePath),'desc'=>$desc];
}

// 拖拽排序 POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
    header('Content-Type: application/json');
    $order = json_decode($_POST['order'], true);
    if (is_array($order)) {
        file_put_contents(__DIR__ . '/order.json', json_encode($order, JSON_UNESCAPED_UNICODE));
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false]);
    }
    exit;
}

// 排序
$orderFile = __DIR__ . '/order.json';
if (file_exists($orderFile)) {
    $saved = json_decode(file_get_contents($orderFile), true);
    if (is_array($saved)) {
        $om = array_flip($saved);
        usort($tools, fn($a,$b) => ($om[$a['filename']] ?? 9999) <=> ($om[$b['filename']] ?? 9999));
    }
} else {
    usort($tools, fn($a,$b) => $b['mtime'] <=> $a['mtime']);
}

$totalTools = count($tools) + 2; // +2 固定卡片
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>实用工具箱 - 熙熙雅思英语</title>
    <link rel="stylesheet" href="../shared/vars.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        .tip-banner {
            background:var(--primary-light); border-radius:var(--radius-sm);
            padding:12px 20px; margin-bottom:28px;
            font-size:0.9rem; color:var(--primary-dark); display:flex; gap:10px; align-items:flex-start;
        }

        .grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));
            gap:22px;
        }
        .card {
            background:var(--card-bg); border-radius:var(--radius-card);
            border:1px solid var(--border);
            box-shadow:0 4px 6px -1px rgb(0 0 0/0.05);
            padding:26px; display:flex; flex-direction:column;
            transition:transform 0.3s, box-shadow 0.3s, border-color 0.3s;
            cursor:grab;
        }
        .card:active { cursor:grabbing; }
        .card:hover { transform:translateY(-6px); box-shadow:0 20px 25px -5px rgb(0 0 0/0.1); border-color:var(--border-hover); }
        .card h3 {
            margin:0 0 10px; color:var(--primary-dark);
            font-size:1.3rem; display:flex; align-items:center; gap:10px; font-weight:600;
        }
        .card-desc { color:var(--text-muted); font-size:0.92rem; flex:1; margin-bottom:18px; line-height:1.6; }
        .card-meta { font-size:0.8rem; color:#94a3b8; margin-bottom:16px; }

        .sortable-ghost { opacity:0.4; background:var(--primary-light); }

        .sort-hint {
            text-align:center; font-size:0.88rem; color:var(--text-muted); margin-bottom:20px;
            background:var(--card-bg); padding:10px 20px; border-radius:var(--radius-btn);
            display:inline-block; border:1px solid var(--border);
        }

        @media(max-width:640px){
            .grid { grid-template-columns:1fr; }
            .card:hover { transform:none; }
        }
    </style>
</head>
<body>
<?php $xh_page='tools'; $xh_depth=1; $xh_root='../'; include __DIR__.'/../shared/topnav.php'; ?>

<div class="xh-container" style="padding-top:32px; padding-bottom:60px;">
    <h1 style="text-align:center;color:var(--primary-dark);font-size:2rem;margin-bottom:6px;font-weight:700;">🛠️ 实用工具箱</h1>
    <p style="text-align:center;color:var(--text-muted);margin-bottom:20px;">
        共 <strong><?= $totalTools ?></strong> 个工具 · AI 驱动 + 实用小工具全套覆盖
    </p>

    <!-- 使用说明 -->
    <div class="tip-banner">
        <span>💡</span>
        <div>
            <b>使用说明：</b>
            点击工具卡片下方的"立即进入"按钮即可使用。
            长按卡片可 <b>拖拽排序</b>，排序结果自动保存。
            如需添加新工具，将 HTML 文件上传到 <code>tools/</code> 目录即可自动出现。
        </div>
    </div>

    <div style="text-align:center;margin-bottom:18px;">
        <span class="sort-hint">👆 长按卡片拖拽排序 · 自动保存</span>
    </div>

    <div class="grid" id="tools-grid">

        <!-- 固定卡片：雅思学习导航 -->
        <div class="card tool-card" data-filename="navigation.php">
            <h3>🌐 雅思学习导航</h3>
            <p class="card-desc">精选雅思备考必备网站，卡片式导航，可自定义添加/删除链接，一键直达。</p>
            <a href="navigation.php" class="xh-btn" target="_blank" style="text-align:center;">立即进入 →</a>
        </div>

        <!-- 固定卡片：雅思听力素材库 -->
        <div class="card tool-card" data-filename="listening_materials.php">
            <h3>🎧 雅思听力素材库</h3>
            <p class="card-desc">上传音频/视频素材，自定义名称和分类，支持拖拽排序，适合老师整理教学资源。</p>
            <a href="listening_materials.php" class="xh-btn" target="_blank" style="text-align:center;">立即进入 →</a>
        </div>

        <?php foreach ($tools as $tool): ?>
        <div class="card tool-card" data-filename="<?= htmlspecialchars($tool['filename']) ?>">
            <h3><?= $tool['emoji'] ?> <?= htmlspecialchars($tool['title']) ?></h3>
            <p class="card-desc"><?= htmlspecialchars($tool['desc']) ?></p>
            <div class="card-meta">📅 最后更新：<?= date('Y-m-d H:i', $tool['mtime']) ?></div>
            <a href="<?= htmlspecialchars($tool['filename']) ?>" class="xh-btn" target="_blank" style="text-align:center;">立即进入 →</a>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="text-align:center;margin-top:52px;">
        <a href="../" class="xh-btn teal" style="padding:14px 48px;font-size:1rem;">← 返回首页</a>
    </div>
</div>

<?php $xh_root='../'; include __DIR__.'/../shared/footer_stats.php'; ?>

<script>
new Sortable(document.getElementById('tools-grid'), {
    animation:180, ghostClass:'sortable-ghost',
    onEnd: function() {
        const order = Array.from(document.querySelectorAll('.tool-card')).map(c=>c.dataset.filename);
        fetch('',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'order='+encodeURIComponent(JSON.stringify(order))})
        .then(r=>r.json()).catch(()=>{});
    }
});
</script>
</body>
</html>
