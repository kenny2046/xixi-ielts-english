<?php
session_start();
require_once 'database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$user_id     = $_SESSION['user_id'];
$factory     = $data['factory'] ?? 'unknown';
$title       = $data['title'] ?? '未命名练习';
$json_input  = json_encode($data['json'] ?? []);

$stmt = $pdo->prepare("INSERT INTO practice_records (user_id, factory_type, title, json_input) 
                       VALUES (?, ?, ?, ?)");
$stmt->execute([$user_id, $factory, $title, $json_input]);

echo json_encode(['success' => true, 'message' => '练习记录已保存']);
?>
