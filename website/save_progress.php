<?php
session_start();
require_once 'database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$user_id       = $_SESSION['user_id'];
$exercise_type = $data['type'] ?? '';
$exercise_id   = $data['exercise_id'] ?? '';
$attempt       = (int)($data['attempt'] ?? 1);
$correct_rate  = (float)($data['correct_rate'] ?? 0);
$band          = $data['band'] ?? null;
$json_data     = json_encode($data['answers'] ?? []);

$stmt = $pdo->prepare("INSERT INTO user_progress 
    (user_id, exercise_type, exercise_id, attempt_count, correct_rate, band_estimate, json_data)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
    attempt_count = attempt_count + 1,
    correct_rate = GREATEST(correct_rate, ?),
    band_estimate = ?,
    last_attempt = NOW(),
    json_data = ?");

$stmt->execute([$user_id, $exercise_type, $exercise_id, $attempt, $correct_rate, $band, $json_data,
                $correct_rate, $band, $json_data]);

echo json_encode(['success' => true, 'message' => '✅ 成绩已保存到云端']);
?>
