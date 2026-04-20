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
