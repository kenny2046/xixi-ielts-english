#!/bin/bash
# =====================================================
# 熙熙雅思题库 - 【最终彻底修复“操作失败”】
# 问题原因：AJAX 处理的 if-elseif 结构被多次 sed 操作弄乱，导致 update_title 永远进不了分支
# 这个脚本会：
#   1. 完整备份
#   2. 彻底删除所有残留的 update_title 代码
#   3. 用**最干净、最完整的** AJAX 处理代码（包含 create_folder + rename + update_title）替换整个区块
#   4. 自动添加/修复工具栏按钮和 JS 函数
# =====================================================

FILE="index.php"

if [ ! -f "$FILE" ]; then
  echo "❌ 错误：当前目录未找到 $FILE"
  exit 1
fi

# 1. 备份
BACKUP="${FILE}.bak.finalfix.$(date +%Y%m%d_%H%M%S)"
cp "$FILE" "$BACKUP"
echo "✅ 已备份 → $BACKUP"

# 2. 彻底清理所有 update_title 残留（防止重复）
sed -i '/update_title/d' "$FILE"

# 3. 用完整正确的 AJAX 区块替换（直接覆盖原来的 // AJAX 处理 部分）
sed -i '/\/\/ AJAX 处理/,/echo json_encode(\$response); exit;/c\// ===== AJAX 处理（已彻底修复）=====
if ($_SERVER['REQUEST_METHOD'] === '\''POST'\'' && isset($_POST['\''action'\''])) {
    header('\''Content-Type: application/json'\'');
    $response = ['\''success'\'' => false, '\''message'\'' => '\''操作失败'\''];
    $current_full = rtrim($root . '\''/'\'' . ($_POST['\''path'\''] ?? '\'''\''), '\''/'\'');

    if ($_POST['\''action'\''] === '\''create_folder'\'' && !empty($_POST['\''folder_name'\''])) {
        $folder_name = trim($_POST['\''folder_name'\'']);
        if (preg_match($name_pattern, $folder_name)) {
            $new_dir = $current_full . '\''/'\'' . $folder_name;
            $response = (!file_exists($new_dir) && mkdir($new_dir, 0755, true))
                ? ['\''success'\'' => true, '\''message'\'' => '\''✅ 文件夹创建成功'\'']
                : ['\''success'\'' => false, '\''message'\'' => '\''❌ 创建失败'\''];
        } else {
            $response = ['\''success'\'' => false, '\''message'\'' => '\''❌ 名称包含非法字符'\''];
        }
    } elseif ($_POST['\''action'\''] === '\''rename'\'') {
        $old_name = $_POST['\''old_name'\''] ?? '\'''\''; 
        $new_name = $_POST['\''new_name'\''] ?? '\'''\''; 
        if ($old_name && $new_name && preg_match($name_pattern, $new_name)) {
            $old_full = $current_full . '\''/'\'' . $old_name;
            $new_full = $current_full . '\''/'\'' . $new_name;
            $response = (file_exists($old_full) && !file_exists($new_full) && rename($old_full, $new_full))
                ? ['\''success'\'' => true, '\''message'\'' => '\''✅ 重命名成功'\'']
                : ['\''success'\'' => false, '\''message'\'' => '\''❌ 重命名失败'\''];
        } else {
            $response = ['\''success'\'' => false, '\''message'\'' => '\''❌ 新名称含非法字符'\''];
        }
    } elseif ($_POST["action"] === "update_title" && !empty($_POST["file"]) && isset($_POST["new_title"])) {
        $filename = $_POST["file"];
        $new_title = trim($_POST["new_title"]);
        $current_full = rtrim($root . "/" . ($_POST["path"] ?? ""), "/");
        $full_file = $current_full . "/" . $filename;

        if (file_exists($full_file) && strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === "html") {
            $content = @file_get_contents($full_file);
            if ($content) {
                $new_content = preg_replace("/<title>[\s\S]*?<\/title>/i", "<title>" . htmlspecialchars($new_title, ENT_QUOTES) . "<\/title>", $content, 1);
                if (file_put_contents($full_file, $new_content) !== false) {
                    $response = ["success" => true, "message" => "✅ 卡片显示名称修改成功！"];
                } else {
                    $response = ["success" => false, "message" => "❌ 保存文件失败（文件夹权限不足）"];
                }
            } else {
                $response = ["success" => false, "message" => "❌ 无法读取文件"];
            }
        } else {
            $response = ["success" => false, "message" => "❌ 只能修改 .html 文件的卡片名称"];
        }
    }

    echo json_encode($response); exit;
}' "$FILE"

# 4. 确保工具栏有「修改卡片名称」按钮（适配你当前新UI）
if ! grep -q "修改卡片名称" "$FILE"; then
  sed -i '/上传 \.html \/ \.zip/a\            <button type="button" onclick="updateCardTitle()" class="xh-btn primary" style="padding:10px 20px;margin-left:8px;">✏️ 修改卡片名称</button>' "$FILE"
fi

# 5. 确保 JS 代码完整
sed -i '/<script>/,/<\/script>/c\
<script>\
const currentPath = "<?= addslashes($path) ?>";\
function openFile(f) {\
    let pathPart = currentPath ? currentPath + "/" : "";\
    let fullUrl = window.location.origin + "/library/" + pathPart + encodeURIComponent(f);\
    window.open(fullUrl, "_blank");\
}\
function enterFolder(f) {\
    location.href = "?path=" + encodeURIComponent(currentPath ? currentPath + "/" + f : f);\
}\
async function updateCardTitle() {\
    const checked = document.querySelectorAll(".cb-item:checked");\
    if (checked.length !== 1) return alert("请只勾选一个 HTML 文件");\
    const item = checked[0];\
    if (item.dataset.type !== "file") return alert("只能修改 HTML 文件的卡片名称");\
    const filename = item.value;\
    const cardNameEl = item.closest(".card").querySelector(".card-name");\
    const oldTitle = cardNameEl ? cardNameEl.textContent.trim() : "";\
    const newTitle = prompt("请输入新的卡片显示名称：", oldTitle);\
    if (!newTitle || newTitle.trim() === "" || newTitle.trim() === oldTitle) return;\
    const fd = new FormData();\
    fd.append("action", "update_title");\
    fd.append("file", filename);\
    fd.append("new_title", newTitle.trim());\
    fd.append("path", currentPath);\
    try {\
        const res = await fetch("index.php", {method: "POST", body: fd});\
        const j = await res.json();\
        alert(j.message);\
        if (j.success) location.reload();\
    } catch(e) { alert("网络错误，请重试"); }\
}\
async function deleteSelected() {\
    const checked = document.querySelectorAll(".cb-item:checked");\
    if (checked.length === 0) return alert("请勾选要删除的项目");\
    if (!confirm("确定删除选中的项目吗？")) return;\
    const fd = new FormData();\
    checked.forEach(c => fd.append("items[]", c.value));\
    fd.append("path", currentPath);\
    const res = await fetch("delete.php",{method:"POST",body:fd});\
    const j = await res.json();\
    alert(j.message || "操作完成");\
    if (j.success) location.reload();\
}\
</script>' "$FILE"

echo ""
echo "🎉 “操作失败”已彻底修复！"
echo "请立即按 Ctrl + F5 强制刷新页面"
echo ""
echo "✅ 测试方法："
echo "   1. 勾选任意一个 HTML 卡片"
echo "   2. 点击「✏️ 修改卡片名称」"
echo "   3. 输入新名称 → 确定"
echo "   4. 应该弹出「✅ 卡片显示名称修改成功！」"
echo ""
echo "如还有问题，请把弹窗里的**完整文字**发给我（比如“❌ 保存文件失败”）"
echo "备份文件：$BACKUP （恢复命令：cp \"$BACKUP\" index.php）"
echo ""
echo "现在刷新试试吧～"
