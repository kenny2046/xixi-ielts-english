#!/bin/bash
# ================================================================
# 熙熙雅思题库 一键升级脚本
# 作用：
#   1. 修改 词汇工厂 v16.1.html  — ZIP 内 mp3 不再放子文件夹
#   2. 修改 library/index.php    — 支持上传 .zip 并自动解压
#   3. 新建 library/upload.php   — 同时处理 .html 和 .zip 上传
# 用法：
#   chmod +x upgrade_v16.1.sh
#   ./upgrade_v16.1.sh /path/to/your/project
# ================================================================

set -e

# ── 参数检查 ──────────────────────────────────────────────────
if [ -z "$1" ]; then
    echo "用法: $0 /path/to/project"
    echo "  project 目录下应包含:"
    echo "    IELTS_词汇工厂_v16.1.html"
    echo "    library/index.php"
    exit 1
fi

PROJECT="$1"
FACTORY_HTML="$PROJECT/IELTS_词汇工厂_v16.1.html"
LIBRARY_INDEX="$PROJECT/library/index.php"
LIBRARY_UPLOAD="$PROJECT/library/upload.php"

# 检查文件是否存在
for f in "$FACTORY_HTML" "$LIBRARY_INDEX"; do
    if [ ! -f "$f" ]; then
        echo "❌ 找不到文件: $f"
        exit 1
    fi
done

echo "🔍 检查文件存在... OK"

# ── 备份原文件 ─────────────────────────────────────────────────
TS=$(date +%Y%m%d_%H%M%S)
cp "$FACTORY_HTML"  "${FACTORY_HTML}.bak_${TS}"
cp "$LIBRARY_INDEX" "${LIBRARY_INDEX}.bak_${TS}"
echo "📦 已备份原文件（后缀 .bak_${TS}）"

# ================================================================
# 改动 1：词汇工厂 v16.1.html
# 目标：finalizeExport 函数中，ZIP 打包时去掉子文件夹层级
#        原来：zip.file(id + '.mp3', ...)  ← id 里无子目录，已是平级
#        但 exportName 被用于 ZIP 内子目录名 —— 实际上原代码已经是平级
#        真正的问题是：用户手动解压时会产生 voc_xxxx_audio/ 子文件夹
#        解决：把 ZIP 文件名改成不带 _audio 后缀，并在 HTML 内说明
#              直接解压到 html 同级目录即可，mp3 天然同级
#
# 另外修改 HTML 中的提示文字，让用户知道解压到同级目录
# ================================================================

echo "✏️  修改词汇工厂：调整 ZIP 提示与 PLAY 按钮路径..."

python3 - "$FACTORY_HTML" << 'PYEOF'
import sys, re

path = sys.argv[1]
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# ── 改动 1a：finalizeExport 中的 ZIP 文件名去掉 _audio 后缀
# 原: triggerDownload(zipBlob, `voc_${name}_audio.zip`);
# 改: triggerDownload(zipBlob, `voc_${name}_mp3.zip`);
# （_mp3.zip 语义更清晰：直接解压到 voc_xxxx.html 同目录）
content = content.replace(
    "triggerDownload(zipBlob, `voc_${name}_audio.zip`);",
    "triggerDownload(zipBlob, `voc_${name}_mp3.zip`);"
)

# ── 改动 1b：showToast 中的提示文字
content = content.replace(
    "showToast(`🎉 导出完成！${generatedIds.length} 个音频已打包`);",
    "showToast(`🎉 导出完成！请将 mp3.zip 解压到与 HTML 同一文件夹`);"
)

# ── 改动 1c：导出的 voc_xxxx.html 内 audio.onerror 提示文字
content = content.replace(
    "tip.textContent = '⚠️ 请先解压 _audio.zip 到同一文件夹';",
    "tip.textContent = '⚠️ 请将 _mp3.zip 解压到与此 HTML 同一目录';"
)
# 同一文件中另一处相同提示
content = content.replace(
    "tip.textContent = '⚠️ 请先解压 _audio.zip 到同一文件夹';",
    "tip.textContent = '⚠️ 请将 _mp3.zip 解压到与此 HTML 同一目录';"
)

# ── 改动 1d：callout 说明文字
content = content.replace(
    "导出时自动生成所有例句 MP3；跳过则只导出 HTML</div>",
    "导出时自动生成所有例句 MP3；先下载 HTML，再将 _mp3.zip 解压到同目录</div>"
)

# ── 改动 1e：callout 内容
content = content.replace(
    "① 语境识词 — 先句后词，猜完再翻牌，每句带 PLAY 按钮<br>\n        ② 题眼训练 — paraphrase识别 + 同义替换选择<br>\n        ③ 做题模拟 — 真题填空，练做题感<br>\n        ④ 强化复习 — 生词自动二轮，连对才算掌握",
    "① 语境识词 — 先句后词，猜完再翻牌，每句带 PLAY 按钮<br>\n        ② 题眼训练 — paraphrase识别 + 同义替换选择<br>\n        ③ 做题模拟 — 真题填空，练做题感<br>\n        ④ 强化复习 — 生词自动二轮，连对才算掌握<br>\n        ⑤ 将 _mp3.zip 解压到 HTML 同目录后 PLAY 按钮生效"
)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("  ✅ 词汇工厂修改完成")
PYEOF

# ================================================================
# 改动 2：library/index.php
# 目标：
#   - 上传区增加 .zip 支持（接受 .html 和 .zip）
#   - 保持界面完全不变（只改 accept 属性和按钮文字）
#   - 其他逻辑全部保持原样
# ================================================================

echo "✏️  修改 library/index.php：支持上传 .zip..."

python3 - "$LIBRARY_INDEX" << 'PYEOF'
import sys, re

path = sys.argv[1]
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# ── 改动 2a：input accept 属性，增加 .zip
content = content.replace(
    'accept=".html"',
    'accept=".html,.zip"'
)

# ── 改动 2b：按钮文字，体现同时支持两种格式
content = content.replace(
    '📤 上传 .html',
    '📤 上传 .html / .zip'
)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("  ✅ index.php 修改完成")
PYEOF

# ================================================================
# 改动 3：新建/覆盖 library/upload.php
# 目标：
#   - 同时处理 .html 上传（原有逻辑）
#   - 处理 .zip 上传：解压到目标目录，mp3/html 全部释放到同一层
#   - 安全校验：只允许 zip 内的 .mp3 和 .html 文件
#   - 不依赖任何外部命令，纯 PHP ZipArchive 实现
# ================================================================

echo "✏️  生成 library/upload.php（支持 html + zip 自动解压）..."

cat > "$LIBRARY_UPLOAD" << 'PHPEOF'
<?php
/**
 * upload.php — 熙熙雅思题库上传处理
 * 支持：.html 直接上传 / .zip 上传后自动解压（只释放 .mp3 和 .html）
 * mp3 文件解压到与 html 同级目录，PLAY 按钮相对路径天然生效
 */
require_once "../auth/auth_middleware.php";

header('Content-Type: application/json');

$root      = __DIR__;
$path      = $_POST['path'] ?? '';
$path      = preg_replace('/(\.\.|\/\/|\\\\)/', '', $path);
$path      = trim($path, '/');
$target    = rtrim($root . '/' . $path, '/');

// 安全：目标目录必须在 root 内
if (!is_dir($target) || strpos(realpath($target), realpath($root)) !== 0) {
    echo json_encode(['success' => false, 'message' => '❌ 非法目标目录']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => '❌ 文件上传失败']);
    exit;
}

$file     = $_FILES['file'];
$origName = basename($file['name']);
$ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

// ── 处理 .html 上传 ──────────────────────────────────────────
if ($ext === 'html') {
    $dest = $target . '/' . $origName;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['success' => true, 'message' => '✅ HTML 上传成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '❌ 保存失败']);
    }
    exit;
}

// ── 处理 .zip 上传并自动解压 ────────────────────────────────
if ($ext === 'zip') {
    if (!class_exists('ZipArchive')) {
        echo json_encode(['success' => false, 'message' => '❌ 服务器不支持 ZipArchive']);
        exit;
    }

    $zip = new ZipArchive();
    if ($zip->open($file['tmp_name']) !== true) {
        echo json_encode(['success' => false, 'message' => '❌ ZIP 文件损坏或无法打开']);
        exit;
    }

    // 允许解压的扩展名白名单
    $allowed = ['mp3', 'html'];
    $extracted = 0;
    $skipped   = 0;

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry    = $zip->getNameIndex($i);
        $basename = basename($entry);            // 只取文件名，丢弃 zip 内子目录
        $entryExt = strtolower(pathinfo($basename, PATHINFO_EXTENSION));

        // 跳过目录条目、隐藏文件、不允许的类型
        if (substr($entry, -1) === '/' || $basename === '' || $basename[0] === '.') {
            continue;
        }
        if (!in_array($entryExt, $allowed, true)) {
            $skipped++;
            continue;
        }

        // 安全：文件名只允许字母数字下划线连字符点
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $basename)) {
            $skipped++;
            continue;
        }

        $dest = $target . '/' . $basename;
        $data = $zip->getFromIndex($i);
        if ($data === false) { $skipped++; continue; }

        if (file_put_contents($dest, $data) !== false) {
            $extracted++;
        } else {
            $skipped++;
        }
    }

    $zip->close();
    @unlink($file['tmp_name']);

    if ($extracted > 0) {
        $msg = "✅ 解压完成：{$extracted} 个文件已释放到当前目录";
        if ($skipped > 0) $msg .= "（跳过 {$skipped} 个不支持的文件）";
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        echo json_encode(['success' => false, 'message' => '❌ ZIP 内无可用文件（仅支持 .mp3 和 .html）']);
    }
    exit;
}

// 其他格式不支持
echo json_encode(['success' => false, 'message' => '❌ 不支持的文件类型，仅接受 .html 和 .zip']);
PHPEOF

echo "  ✅ upload.php 生成完成"

# ================================================================
# 完成提示
# ================================================================
echo ""
echo "════════════════════════════════════════════════"
echo "✅ 升级完成！共修改/新建 3 个文件："
echo ""
echo "  📝 IELTS_词汇工厂_v16.1.html"
echo "     → ZIP 文件名改为 voc_xxxx_mp3.zip（无子文件夹）"
echo "     → 提示文字更新：解压到 HTML 同目录"
echo ""
echo "  📝 library/index.php"
echo "     → 上传按钮支持 .html 和 .zip"
echo ""
echo "  📝 library/upload.php（新建/覆盖）"
echo "     → .html 直接上传保存"
echo "     → .zip 自动解压，mp3/html 平铺到目标目录"
echo "     → 安全白名单：只释放 .mp3 和 .html"
echo ""
echo "📌 使用流程："
echo "  1. 在词汇工厂导出 voc_xxxx.html + voc_xxxx_mp3.zip"
echo "  2. 在导航页进入目标目录（如 Cambridge4/Test1/Vocabulary）"
echo "  3. 先上传 voc_xxxx.html"
echo "  4. 再上传 voc_xxxx_mp3.zip → 自动解压，mp3 与 html 同级"
echo "  5. 打开 voc_xxxx.html，PLAY 按钮直接可用 ✅"
echo "════════════════════════════════════════════════"
