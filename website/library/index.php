<?php
require_once "../auth/auth_middleware.php";
?>
<?php
// ===== 熙熙雅思题库 v2.4（卡片美观版 + 优化面包屑）=====
$root = __DIR__;

$path = isset($_GET['path']) ? $_GET['path'] : '';
$path = preg_replace('/(\.\.|\/\/|\\\\)/', '', $path);
$path = trim($path, '/');
$full_path = rtrim($root . '/' . $path, '/');

if (!is_dir($full_path) || strpos(realpath($full_path), realpath($root)) !== 0) {
    $path = ''; $full_path = $root;
}

$name_pattern = '/^[a-zA-Z0-9_\-\s\x{4e00}-\x{9fa5}.()（）【】《》！？、，。]+$/u';

// AJAX 处理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => '操作失败'];
    $current_full = rtrim($root . '/' . ($_POST['path'] ?? ''), '/');

    if ($_POST['action'] === 'create_folder' && !empty($_POST['folder_name'])) {
        $folder_name = trim($_POST['folder_name']);
        if (preg_match($name_pattern, $folder_name)) {
            $new_dir = $current_full . '/' . $folder_name;
            $response = (!file_exists($new_dir) && mkdir($new_dir, 0755, true))
                ? ['success' => true, 'message' => '✅ 文件夹创建成功']
                : ['success' => false, 'message' => '❌ 创建失败'];
        } else {
            $response = ['success' => false, 'message' => '❌ 名称包含非法字符'];
        }
    } elseif ($_POST['action'] === 'rename') {
        $old_name = $_POST['old_name'] ?? '';
        $new_name = $_POST['new_name'] ?? '';
        if ($old_name && $new_name && preg_match($name_pattern, $new_name)) {
            $old_full = $current_full . '/' . $old_name;
            $new_full = $current_full . '/' . $new_name;
            $response = (file_exists($old_full) && !file_exists($new_full) && rename($old_full, $new_full))
                ? ['success' => true, 'message' => '✅ 重命名成功']
                : ['success' => false, 'message' => '❌ 重命名失败'];
        } else {
            $response = ['success' => false, 'message' => '❌ 新名称含非法字符'];
        }
    }
    echo json_encode($response); exit;
}

// 读取目录
$folders = []; $files = [];
if (is_dir($full_path)) {
    foreach (scandir($full_path) as $item) {
        if ($item === '.' || $item === '..') continue;
        $item_path = $full_path . '/' . $item;
        if (is_dir($item_path)) {
            $folders[] = $item;
        } elseif (strtolower(pathinfo($item, PATHINFO_EXTENSION)) === 'html') {
            $html_content = @file_get_contents($item_path);
            $display_name = $item;
            if ($html_content && preg_match('/<title>(.*?)<\/title>/is', $html_content, $m)) {
                $t = trim(strip_tags($m[1]));
                if ($t) $display_name = $t;
            }
            $files[] = ['file' => $item, 'title' => $display_name];
        }
    }
}

function getBreadcrumbs($path) {
    if (empty($path)) return [];
    $parts = explode('/', $path); $breadcrumbs = []; $accum = '';
    foreach ($parts as $p) {
        $accum = $accum ? $accum . '/' . $p : $p;
        $breadcrumbs[] = ['name' => $p, 'path' => $accum];
    }
    return $breadcrumbs;
}
$breadcrumbs = getBreadcrumbs($path);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>雅思题库 - 熙熙雅思英语</title>
    <link rel="stylesheet" href="../shared/vars.css">
    <style>
        /* 美观卡片样式 */
        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:22px; }
        .card {
            background:var(--card-bg); border-radius:20px; border:1px solid var(--border);
            box-shadow:0 4px 6px -1px rgb(0 0 0/0.05); padding:24px;
            transition: all 0.3s; cursor:pointer; display:flex; flex-direction:column;
        }
        .card:hover {
            transform:translateY(-6px); box-shadow:0 20px 25px -5px rgb(0 0 0/0.1);
            border-color:var(--border-hover);
        }
        .card-body { flex:1; display:flex; align-items:center; gap:18px; }
        .card-icon { font-size:3rem; flex-shrink:0; transition:transform 0.3s; }
        .card:hover .card-icon { transform:scale(1.1); }
        .card-info { flex:1; }
        .card-name { font-size:1.1rem; font-weight:600; margin-bottom:4px; }
        .card-footer {
            margin-top:16px; padding-top:14px; border-top:1px solid var(--border);
            display:flex; justify-content:space-between; align-items:center;
        }
        .breadcrumb {
            display:flex; align-items:center; gap:8px; font-size:1.05rem;
            background:var(--card-bg); padding:12px 22px; border-radius:var(--radius-btn);
            border:1px solid var(--border); flex:1;
        }
        .breadcrumb a { color:var(--primary); text-decoration:none; padding:4px 12px; border-radius:9999px; }
        .breadcrumb a:hover { background:var(--primary-light); }
        .breadcrumb .separator { color:#94a3b8; }
        .breadcrumb .current { color:var(--primary-dark); font-weight:600; background:#e0f2fe; padding:4px 14px; border-radius:9999px; }
    </style>
</head>
<body>
<?php $xh_page='library'; $xh_depth=1; $xh_root='../'; include __DIR__.'/../shared/topnav.php'; ?>

<div class="xh-container" style="padding-top:32px; padding-bottom:60px;">
    <h1 style="text-align:center;color:var(--primary-dark);font-size:2rem;margin-bottom:6px;font-weight:700;">📖 雅思题库</h1>

    <!-- 优化后的面包屑 -->
    <div class="breadcrumb">
        <a href="?path=">📖 根目录</a>
        <?php foreach ($breadcrumbs as $bc): ?>
            <span class="separator">›</span>
            <a href="?path=<?= urlencode($bc['path']) ?>"><?= htmlspecialchars($bc['name']) ?></a>
        <?php endforeach; ?>
        <?php if ($path): ?>
            <span class="separator">›</span>
            <span class="current"><?= htmlspecialchars(basename($path)) ?></span>
        <?php endif; ?>
    </div>

    <p style="text-align:center;color:var(--text-muted);margin:20px 0 28px;">
        当前目录：<strong><?= $path ?: '根目录' ?></strong>
    </p>

    <!-- 工具栏 -->
    <div class="toolbar" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:24px;">
        <button onclick="createFolder()" class="xh-btn success">➕ 新建文件夹</button>

        <div style="display:flex;align-items:center;gap:8px;">
            <select id="targetFolder" style="padding:10px 16px;border:1px solid var(--border);background:var(--bg);border-radius:var(--radius-btn);">
                <option value="<?= htmlspecialchars($path) ?>">📍 当前目录</option>
                <?php foreach ($folders as $f): ?>
                <option value="<?= htmlspecialchars($path ? $path.'/'.$f : $f) ?>"><?= htmlspecialchars($f) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="fileInput" class="xh-btn success" style="cursor:pointer;">📤 上传 .html / .zip</label>
            <button type="button" onclick="updateCardTitle()" class="xh-btn primary" style="padding:10px 20px;margin-left:8px;">✏️ 修改卡片名称</button>
            <input type="file" id="fileInput" accept=".html,.zip" style="display:none;" onchange="uploadFile()">
        </div>

        <?php if ($path): ?>
        <a href="?path=<?= urlencode(dirname($path)=='.' ? '' : dirname($path)) ?>" class="xh-btn gray">↑ 返回上级</a>
        <?php endif; ?>
    </div>

    <!-- 美观卡片列表 -->
    <form id="deleteForm">
        <div class="grid">
            <?php if (empty($folders) && empty($files)): ?>
            <div class="empty" style="grid-column:1/-1;text-align:center;padding:80px 20px;">
                <div style="font-size:4rem;margin-bottom:12px;">📪</div>
                <p>此目录为空</p>
            </div>
            <?php endif; ?>

            <?php foreach ($folders as $folder): ?>
            <div class="card">
                <div class="card-body" onclick="enterFolder('<?= htmlspecialchars($folder) ?>')">
                    <span class="card-icon">📁</span>
                    <div class="card-info">
                        <div class="card-name"><?= htmlspecialchars($folder) ?></div>
                    </div>
                </div>
                <div class="card-footer">
                    <label style="cursor:pointer;display:flex;align-items:center;gap:6px;">
                        <input class="delete-check cb-item" type="checkbox" name="items[]" value="<?= htmlspecialchars($folder) ?>" data-type="folder">
                        <span style="color:var(--danger);">🗑️</span>
                    </label>
                </div>
            </div>
            <?php endforeach; ?>

            <?php foreach ($files as $f): ?>
            <div class="card">
                <div class="card-body" onclick="openFile('<?= htmlspecialchars($f['file']) ?>')">
                    <span class="card-icon">📄</span>
                    <div class="card-info">
                        <div class="card-name"><?= htmlspecialchars($f['title']) ?></div>
                    </div>
                </div>
                <div class="card-footer">
                    <label style="cursor:pointer;display:flex;align-items:center;gap:6px;">
                        <input class="delete-check cb-item" type="checkbox" name="items[]" value="<?= htmlspecialchars($f['file']) ?>" data-type="file">
                        <span style="color:var(--danger);">🗑️</span>
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:40px;">
            <button type="button" onclick="deleteSelected()" class="xh-btn danger" style="padding:14px 40px;">🗑️ 删除选中项目</button>
            <a href="../" class="xh-btn teal" style="padding:14px 40px;margin-left:12px;">← 返回首页</a>
        </div>
    </form>
</div>

<?php $xh_root='../'; include __DIR__.'/../shared/footer_stats.php'; ?>

<script>
const currentPath = "<?= addslashes($path) ?>";
function openFile(f) {
    let pathPart = currentPath ? currentPath + "/" : "";
    let fullUrl = window.location.origin + "/library/" + pathPart + encodeURIComponent(f);
    window.open(fullUrl, "_blank");
}
function enterFolder(f) {
    location.href = "?path=" + encodeURIComponent(currentPath ? currentPath + "/" + f : f);
}
async function updateCardTitle() {
    const checked = document.querySelectorAll(".cb-item:checked");
    if (checked.length !== 1) return alert("请只勾选一个 HTML 文件");
    const item = checked[0];
    if (item.dataset.type !== "file") return alert("只能修改 HTML 文件的卡片名称");
    const filename = item.value;
    const cardNameEl = item.closest(".card").querySelector(".card-name");
    const oldTitle = cardNameEl ? cardNameEl.textContent.trim() : "";
    const newTitle = prompt("请输入新的卡片显示名称：", oldTitle);
    if (!newTitle || newTitle.trim() === "" || newTitle.trim() === oldTitle) return;
    const fd = new FormData();
    fd.append("action", "update_title");
    fd.append("file", filename);
    fd.append("new_title", newTitle.trim());
    fd.append("path", currentPath);
    try {
        const res = await fetch("index.php", {method: "POST", body: fd});
        const j = await res.json();
        alert(j.message);
        if (j.success) location.reload();
    } catch(e) { alert("网络错误，请重试"); }
}
async function deleteSelected() {
    const checked = document.querySelectorAll(".cb-item:checked");
    if (checked.length === 0) return alert("请勾选要删除的项目");
    if (!confirm("确定删除选中的项目吗？")) return;
    const fd = new FormData();
    checked.forEach(c => fd.append("items[]", c.value));
    fd.append("path", currentPath);
    const res = await fetch("delete.php",{method:"POST",body:fd});
    const j = await res.json();
    alert(j.message || "操作完成");
    if (j.success) location.reload();
}
</script>
</body>
</html>
