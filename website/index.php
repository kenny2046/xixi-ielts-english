<?php
// ===== 熙熙雅思英语首页 v2.0 =====
date_default_timezone_set('Asia/Shanghai');

$counterFile = __DIR__ . '/visits.txt';
$logFile     = __DIR__ . '/visit_log.json';

if (!file_exists($counterFile)) file_put_contents($counterFile, '0');
$totalVisits = (int)file_get_contents($counterFile) + 1;
file_put_contents($counterFile, $totalVisits);

$today     = date('Y-m-d');
$todayFile = __DIR__ . '/visits_today_' . $today . '.txt';
if (!file_exists($todayFile)) file_put_contents($todayFile, '0');
$todayVisits = (int)file_get_contents($todayFile) + 1;
file_put_contents($todayFile, $todayVisits);

$ip   = $_SERVER['REMOTE_ADDR'] ?? '未知';
$city = '未知';
if (filter_var($ip, FILTER_VALIDATE_IP) && !in_array($ip, ['::1','127.0.0.1'])) {
    $geoJson = @file_get_contents("http://ip-api.com/json/{$ip}?lang=zh-CN");
    if ($geoJson) {
        $geo  = json_decode($geoJson, true);
        $city = $geo['city'] ?? $geo['regionName'] ?? $geo['country'] ?? '未知';
    }
}

$log = file_exists($logFile) ? (json_decode(file_get_contents($logFile), true) ?: []) : [];
$log[] = ['time' => date('Y-m-d H:i:s'), 'ip' => $ip, 'city' => $city];
if (count($log) > 50) array_shift($log);
file_put_contents($logFile, json_encode($log, JSON_UNESCAPED_UNICODE));

$scrollItems = [];
foreach (array_reverse(array_slice($log, -12)) as $entry) {
    $scrollItems[] = substr($entry['time'], 11, 5) . '　' . $entry['city'];
}
$scrollText = implode('<br>', $scrollItems);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="/shared/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>熙熙雅思英语</title>
    <link rel="stylesheet" href="shared/vars.css">
    <style>
        .hero { text-align:center; padding: 48px 0 36px; }
        .hero h1 { font-size:3rem; font-weight:700; color:var(--primary-dark); margin-bottom:12px; letter-spacing:-1px; }
        .hero .subtitle { font-size:1.3rem; color:var(--text-muted); margin-bottom:20px; }
        .hero .tagline {
            display:inline-block; background:var(--primary-light); color:var(--primary);
            padding:8px 24px; border-radius:var(--radius-btn); font-size:1rem; font-weight:500;
        }

        /* 三大模块卡片 */
        .module-grid {
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap:24px; margin-bottom:48px;
        }
        .module-card {
            display:block; text-decoration:none; color:inherit;
            background:var(--card-bg); border-radius:var(--radius-card);
            border:1px solid var(--border);
            box-shadow:0 4px 6px -1px rgb(0 0 0/0.05);
            padding:36px 28px; text-align:center;
            transition:transform 0.3s, box-shadow 0.3s, border-color 0.3s;
        }
        .module-card:hover { transform:translateY(-8px); box-shadow:0 20px 25px -5px rgb(0 0 0/0.1); border-color:var(--border-hover); }
        .module-card .icon { font-size:3.4rem; display:block; margin-bottom:14px; }
        .module-card h3 { font-size:1.3rem; color:var(--primary-dark); margin:0 0 10px; font-weight:600; }
        .module-card p  { color:var(--text-muted); font-size:0.97rem; margin:0 0 6px; }
        .module-card .tag {
            display:inline-block; margin-top:12px;
            background:var(--primary-light); color:var(--primary);
            padding:3px 12px; border-radius:var(--radius-btn); font-size:0.82rem;
        }

        /* 功能说明栏 */
        .intro-box {
            max-width:760px; margin:0 auto 56px; background:var(--card-bg);
            border-radius:var(--radius-card); border:1px solid var(--border);
            padding:36px 40px; text-align:center; color:var(--text-muted); font-size:1.05rem;
        }
        .intro-box strong { color:var(--primary-dark); }
        .intro-box .steps {
            display:flex; gap:20px; margin-top:24px; justify-content:center; flex-wrap:wrap;
        }
        .intro-box .step-item {
            background:var(--bg); border-radius:var(--radius-sm); padding:12px 18px;
            font-size:0.9rem; color:var(--text-muted); flex:1; min-width:140px; max-width:180px;
        }
        .intro-box .step-item span { display:block; font-size:1.4rem; margin-bottom:4px; }

        /* 底部统计 */
        .stats-bar {
            max-width:var(--max-width); margin:0 auto; padding:0 24px 48px;
        }
        .stats-inner {
            background:var(--card-bg); border-radius:var(--radius-card);
            box-shadow:0 4px 12px rgba(0,0,0,0.08);
            display:flex; align-items:center; gap:24px; flex-wrap:wrap; padding:18px 28px;
        }
        .stats-total { font-size:1.2rem; font-weight:600; color:var(--primary-dark); white-space:nowrap; }
        .stats-total b { color:var(--success); font-size:1.45rem; }
        .stats-scroll {
            flex:1; min-width:220px; height:52px; overflow:hidden; position:relative;
            border-left:3px solid var(--primary-light); padding-left:18px;
        }
        .scroll-inner {
            position:absolute; width:100%;
            animation:vertMarquee 18s linear infinite;
            line-height:1.8; font-size:0.88rem; color:var(--text-muted);
        }
        .stats-today { font-size:0.88rem; color:var(--text-muted); white-space:nowrap; }
        @keyframes vertMarquee { 0%{transform:translateY(0)} 100%{transform:translateY(-50%)} }
        @media(max-width:640px){
            .hero h1{font-size:2.2rem;}
            .stats-inner{flex-direction:column;gap:14px;text-align:center;}
            .stats-scroll{border-left:none;padding-left:0;height:66px;}
        }
    </style>
</head>
<body>

<?php
$xh_page  = 'home';
$xh_depth = 0;
$xh_root  = '';
include __DIR__ . '/shared/topnav.php';
?>

<div class="xh-container" style="padding-top:40px;">

    <!-- Hero -->
    <div class="hero">
        <h1>🎯 熙熙雅思英语</h1>
        <p class="subtitle">你的雅思备考全能平台</p>
        <div class="tagline">专业 · 实用 · 一站式</div>
    </div>

    <!-- 三大模块 -->
    <div class="module-grid">
        <a href="library/" class="module-card">
            <span class="icon">📖</span>
            <h3>雅思题库</h3>
            <p>剑桥真题 · 口语专项 · 听力词汇</p>
            <p style="font-size:0.88rem;">按文件夹分类管理，点击卡片即可练习</p>
            <span class="tag">支持上传 · 新建文件夹</span>
        </a>

        <a href="tools/navigation.php" class="module-card" target="_blank">
            <span class="icon">🌐</span>
            <h3>资源导航</h3>
            <p>精选雅思高分网站 · 一键快速访问</p>
            <p style="font-size:0.88rem;">可自定义添加、删除和分类管理</p>
            <span class="tag">网页管理 · 实时更新</span>
        </a>

        <a href="tools/" class="module-card">
            <span class="icon">🛠️</span>
            <h3>实用工具箱</h3>
            <p>写作 · 口语 · 词汇 · PDF · 听力素材</p>
            <p style="font-size:0.88rem;">AI 驱动工具 + 实用小工具，全套覆盖</p>
            <span class="tag">支持拖拽排序</span>
        </a>
    </div>

    <!-- 功能介绍 -->
    <div class="intro-box">
        <p>欢迎来到 <strong>熙熙雅思英语</strong>！这是一个为雅思备考打造的综合学习平台。</p>
        <div class="steps">
            <div class="step-item"><span>📖</span>题库刷题<br>分类精练</div>
            <div class="step-item"><span>🛠️</span>AI工具<br>写作口语</div>
            <div class="step-item"><span>🌐</span>资源导航<br>精选网站</div>
            <div class="step-item"><span>🎧</span>听力素材<br>自定义上传</div>
        </div>
    </div>

</div>

<!-- 底部统计 -->
<div class="stats-bar">
    <div class="stats-inner">
        <div class="stats-total">累计访问 <b><?= number_format($totalVisits) ?></b> 次</div>
        <?php if ($scrollText): ?>
        <div class="stats-scroll">
            <div class="scroll-inner"><?= $scrollText ?><br><br><?= $scrollText ?></div>
        </div>
        <?php endif; ?>
        <div class="stats-today">今日 <b style="color:var(--primary)"><?= $todayVisits ?></b> 次</div>
    </div>
</div>

</body>
</html>
