<?php
session_start();
require_once '../database.php';

$uploadDir = 'listening_materials/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (isset($_FILES['file'])) {
    $filename = uniqid() . '.' . strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    $target = $uploadDir . $filename;
    $customName = $_POST['custom_name'] ?: $_FILES['file']['name'];

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $stmt = $pdo->prepare("INSERT INTO listening_materials (filename, original_name, category, uploaded_by) 
                               VALUES (?, ?, '未分类', ?)");
        $stmt->execute([$filename, $customName, $_SESSION['user_id']]);
        header("Location: listening_materials.php?msg=上传成功");
    } else {
        echo "上传失败";
    }
}
?>
