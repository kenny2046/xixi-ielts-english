<?php
header('Content-Type: application/json; charset=utf-8');
$dir = __DIR__ . '/library/';
$deleted = 0;
foreach ($_POST['files'] ?? [] as $f) {
    $path = $dir . basename($f);
    if (file_exists($path) && str_ends_with(strtolower($f), '.html')) {
        if (unlink($path)) $deleted++;
    }
}
echo json_encode(['success' => true, 'message' => "✅ 已删除 $deleted 个文件"]);
?>
