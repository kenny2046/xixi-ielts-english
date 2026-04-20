<?php
// ==================== 雅思学习导航 - 明亮简洁版 ====================
$jsonFile = 'navigation.json';

if (!file_exists($jsonFile)) {
    $default = ["title" => "🌐 雅思学习导航", "categories" => []];
    file_put_contents($jsonFile, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents($jsonFile), true) ?: ["title" => "🌐 雅思学习导航", "categories" => []];
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category' && !empty($_POST['cat_name'])) {
        $data['categories'][] = ["name" => trim($_POST['cat_name']), "links" => []];
        $message = '✅ 分类添加成功！';
    } 
    elseif ($action === 'add_link' && !empty($_POST['cat_name']) && !empty($_POST['link_text']) && !empty($_POST['link_url'])) {
        foreach ($data['categories'] as &$cat) {
            if ($cat['name'] === $_POST['cat_name']) {
                $cat['links'][] = ["text" => trim($_POST['link_text']), "url" => trim($_POST['link_url'])];
                $message = '✅ 链接添加成功！';
                break;
            }
        }
    } 
    elseif ($action === 'delete_link' && !empty($_POST['cat_name']) && !empty($_POST['url'])) {
        foreach ($data['categories'] as &$cat) {
            if ($cat['name'] === $_POST['cat_name']) {
                $cat['links'] = array_values(array_filter($cat['links'], fn($l) => $l['url'] !== $_POST['url']));
                $message = '🗑️ 链接已删除';
                break;
            }
        }
    } 
    elseif ($action === 'delete_category' && !empty($_POST['cat_name'])) {
        $data['categories'] = array_values(array_filter($data['categories'], fn($c) => $c['name'] !== $_POST['cat_name']));
        $message = '🗑️ 分类已删除';
    }

    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: navigation.php?msg=" . urlencode($message));
    exit;
}

$data = json_decode(file_get_contents($jsonFile), true) ?: ["title" => "🌐 雅思学习导航", "categories" => []];
$editMode = $_GET['edit'] ?? '0';
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>雅思学习导航</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap');
        
        :root {
            --primary: #3b82f6;
        }
        
        * { box-sizing: border-box; }
        body {
            font-family: 'Noto Sans SC', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            background: #f8fafc;
            color: #1e2937;
            line-height: 1.6;
        }
        .container {
            max-width: 1280px;
            margin: 40px auto;
            padding: 0 24px;
        }
        h1 {
            text-align: center;
            color: #1e40af;
            font-size: 2.4rem;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 32px;
            font-size: 1.15rem;
        }
        
        .msg {
            text-align: center;
            padding: 14px 24px;
            background: #10b981;
            color: white;
            border-radius: 9999px;
            margin: 20px auto;
            max-width: 420px;
            font-weight: 500;
        }
        
        .toggle {
            text-align: center;
            margin-bottom: 32px;
        }
        .toggle label {
            background: #fff;
            padding: 12px 28px;
            border-radius: 9999px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
            font-size: 1.05rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 24px;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.3s;
        }
        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }
        .card h3 {
            margin: 0 0 20px 0;
            color: #1e40af;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .link {
            display: block;
            padding: 14px 0;
            color: #475569;
            text-decoration: none;
            font-size: 1.05rem;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s;
        }
        .link:hover {
            color: var(--primary);
            padding-left: 8px;
        }
        .link:last-child {
            border-bottom: none;
        }
        
        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 9999px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-danger {
            background: #ef4444;
        }
        
        .icon-delete {
            color: #ef4444;
            font-size: 1.35rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .icon-delete:hover {
            transform: scale(1.2);
        }
        
        /* 添加表单弹窗 */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 999;
        }
        .modal-content {
            background: #fff;
            padding: 32px;
            border-radius: 20px;
            width: 90%;
            max-width: 440px;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }
        
        .return-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            margin-top: 60px;
        }
        
        @media (max-width: 640px) {
            .nav-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <h1>🌐 雅思学习导航</h1>
        <p class="subtitle">精选高分网站 · 一键快速访问</p>

        <?php if ($msg): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="toggle">
            <label>
                <input type="checkbox" id="editToggle" <?= $editMode==='1'?'checked':'' ?> onchange="location='navigation.php?edit='+(this.checked?1:0)">
                ✏️ 进入编辑模式
            </label>
        </div>

        <?php if ($editMode==='1'): ?>
        <div style="text-align:center; margin-bottom:32px;">
            <button class="btn" onclick="showAddCatModal()">+ 新增分类</button>
            <button class="btn" onclick="showAddLinkModal()" style="margin-left:12px;">+ 新增链接</button>
        </div>
        <?php endif; ?>

        <div class="nav-grid">
            <?php foreach ($data['categories'] as $cat): ?>
            <div class="card">
                <h3>
                    <?= htmlspecialchars($cat['name']) ?>
                    <?php if ($editMode==='1'): ?>
                    <span class="icon-delete" onclick="if(confirm('确定删除整个分类？')){document.getElementById('delCatForm').cat_name.value='<?= addslashes($cat['name']) ?>';document.getElementById('delCatForm').submit();}">🗑️</span>
                    <?php endif; ?>
                </h3>
                
                <?php foreach ($cat['links'] as $link): ?>
                    <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" class="link">
                        → <?= htmlspecialchars($link['text']) ?>
                    </a>
                    <?php if ($editMode==='1'): ?>
                    <span class="icon-delete" style="float:right; margin-top:-22px;" onclick="if(confirm('删除此链接？')){document.getElementById('delLinkForm').cat_name.value='<?= addslashes($cat['name']) ?>';document.getElementById('delLinkForm').url.value='<?= addslashes($link['url']) ?>';document.getElementById('delLinkForm').submit();}">🗑️</span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;">
            <a href="index.php" class="return-btn">← 返回工具首页</a>
        </div>
    </div>

    <!-- 删除表单（隐藏） -->
    <form id="delCatForm" method="post" style="display:none;">
        <input type="hidden" name="action" value="delete_category">
        <input type="hidden" name="cat_name" value="">
    </form>
    <form id="delLinkForm" method="post" style="display:none;">
        <input type="hidden" name="action" value="delete_link">
        <input type="hidden" name="cat_name" value="">
        <input type="hidden" name="url" value="">
    </form>

    <!-- 新增分类弹窗 -->
    <div id="addCatModal" class="modal">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="add_category">
                <h3 style="color:#1e40af; margin-bottom:20px;">新增分类</h3>
                <input type="text" name="cat_name" placeholder="输入分类名称" required style="width:100%; padding:14px; border:2px solid #e2e8f0; border-radius:12px; margin-bottom:20px;">
                <div style="display:flex; gap:12px;">
                    <button type="submit" class="btn" style="flex:1;">确认添加</button>
                    <button type="button" class="btn" style="background:#64748b; flex:1;" onclick="hideAddCatModal()">取消</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 新增链接弹窗 -->
    <div id="addLinkModal" class="modal">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="add_link">
                <h3 style="color:#1e40af; margin-bottom:20px;">新增链接</h3>
                <select name="cat_name" style="width:100%; padding:14px; border:2px solid #e2e8f0; border-radius:12px; margin-bottom:15px;" required>
                    <?php foreach ($data['categories'] as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="link_text" placeholder="链接名称" required style="width:100%; padding:14px; border:2px solid #e2e8f0; border-radius:12px; margin-bottom:15px;">
                <input type="url" name="link_url" placeholder="https://..." required style="width:100%; padding:14px; border:2px solid #e2e8f0; border-radius:12px; margin-bottom:20px;">
                <div style="display:flex; gap:12px;">
                    <button type="submit" class="btn" style="flex:1;">确认添加</button>
                    <button type="button" class="btn" style="background:#64748b; flex:1;" onclick="hideAddLinkModal()">取消</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddCatModal() {
            document.getElementById('addCatModal').style.display = 'flex';
        }
        function hideAddCatModal() {
            document.getElementById('addCatModal').style.display = 'none';
        }
        function showAddLinkModal() {
            document.getElementById('addLinkModal').style.display = 'flex';
        }
        function hideAddLinkModal() {
            document.getElementById('addLinkModal').style.display = 'none';
        }
        
        // 点击遮罩关闭
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
