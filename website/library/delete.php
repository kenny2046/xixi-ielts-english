<?php
header('Content-Type: application/json; charset=utf-8');

$root = __DIR__;

function deleteRecursive($path) {
    if (is_dir($path)) {
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            deleteRecursive($path . '/' . $item);
        }
        rmdir($path);
    } else {
        unlink($path);
    }
}

$path = $_POST['path'] ?? '';
$path = preg_replace('/(\.\.|\/\/|\\\\)/', '', $path);
$items = $_POST['items'] ?? [];

$successCount = 0;

foreach ($items as $item) {
    $fullPath = rtrim($root . '/' . $path, '/') . '/' . $item;
    $fullPath = str_replace('//', '/', $fullPath);
    
    if (file_exists($fullPath)) {
        deleteRecursive($fullPath);
        $successCount++;
    }
}

echo json_encode([
    'success' => $successCount > 0,
    'message' => $successCount > 0 ? "成功删除 {$successCount} 个项目" : '删除失败'
]);
?>
