<?php
session_start();
$xh_page  = $xh_page  ?? 'home';
$xh_depth = $xh_depth ?? 0;
$xh_root  = str_repeat('../', $xh_depth);
?>
<style>
.xh-topnav { position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); border-bottom: 1px solid #f1f5f9; padding: 0 24px; }
.xh-topnav-inner { max-width: 1280px; margin: 0 auto; display: flex; align-items: center; height: 56px; gap: 12px; }
.xh-topnav-logo { display: flex; align-items: center; gap: 8px; text-decoration: none; }
.xh-topnav-logo img { height: 42px; width: auto; }
.xh-topnav-links { display: flex; gap: 4px; flex: 1; }
.xh-topnav-links a { padding: 6px 14px; border-radius: 9999px; text-decoration: none; font-size: 0.92rem; font-weight: 500; color: #64748b; transition: all 0.2s; }
.xh-topnav-links a:hover  { background: #f1f5f9; color: #1e40af; }
.xh-topnav-links a.active { background: #e0f2fe; color: #1e40af; }
.user-info { margin-left: auto; display: flex; align-items: center; gap: 12px; font-size: 0.9rem; }
.user-info a { color: #3b82f6; text-decoration: none; }
</style>
<nav class="xh-topnav">
    <div class="xh-topnav-inner">
        <!-- 这里就是 Logo 位置 -->
        <a class="xh-topnav-logo" href="<?= $xh_root ?>">
            <img src="<?= $xh_root ?>shared/logo.png" alt="熙熙英语">
        </a>

        <div class="xh-topnav-links">
            <a href="<?= $xh_root ?>"           class="<?= $xh_page==='home'?'active':'' ?>">🏠 首页</a>
            <a href="<?= $xh_root ?>library/"   class="<?= $xh_page==='library'?'active':'' ?>">📖 题库</a>
            <a href="<?= $xh_root ?>tools/"     class="<?= $xh_page==='tools'?'active':'' ?>">🛠️ 工具</a>
            <a href="<?= $xh_root ?>tools/navigation.php" target="_blank" class="<?= $xh_page==='nav'?'active':'' ?>">🌐 资源导航</a>
            <a href="<?= $xh_root ?>my_progress.php" class="<?= $xh_page==='progress'?'active':'' ?>">📈 我的进度</a>
        </div>
        
        <div class="user-info">
            <?php if (isset($_SESSION['username'])): ?>
                👤 <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                <a href="<?= $xh_root ?>auth/logout.php">退出</a>
            <?php else: ?>
                <a href="<?= $xh_root ?>auth/login.php">登录</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
