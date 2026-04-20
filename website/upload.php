<?php
header('Content-Type: application/json; charset=utf-8');
$targetDir = __DIR__ . '/library/';
$file = $_FILES['file'] ?? null;
if (!$file) {
    echo json_encode(['success'=>false, 'message'=>'没有收到文件']);
    exit;
}
$filename = basename($file['name']);
$target = $targetDir . $filename;
if (move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode(['success'=>true, 'message'=>'✅ 上传成功！页面即将刷新...']);
} else {
    echo json_encode(['success'=>false, 'message'=>'上传失败']);
}
?>
