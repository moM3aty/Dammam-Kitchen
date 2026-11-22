<?php
// save_message.php — حفظ رسائل نموذج التواصل في قاعدة البيانات
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Method not allowed']);
    exit;
}

$name    = trim((string)($_POST['name']    ?? ''));
$phone   = trim((string)($_POST['phone']   ?? ''));
$city    = trim((string)($_POST['city']    ?? ''));
$area    = trim((string)($_POST['area']    ?? ''));
$service = trim((string)($_POST['service'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

// تحقق بسيط
$digits = preg_replace('/[^0-9٠-٩]/', '', $phone);
if (mb_strlen($name) < 2 || mb_strlen($digits) < 8) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'msg' => 'بيانات غير صحيحة']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO messages (name, phone, city, area, service, message, is_read) VALUES (?,?,?,?,?,?,0)");
    $stmt->execute([
        mb_substr($name,    0, 120),
        mb_substr($phone,   0, 30),
        mb_substr($city,    0, 120),
        mb_substr($area,    0, 120),
        mb_substr($service, 0, 200),
        mb_substr($message, 0, 5000),
    ]);
    echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'خطأ في الحفظ']);
}