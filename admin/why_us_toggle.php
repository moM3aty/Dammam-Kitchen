<?php
require_once __DIR__ . '/includes/init.php';

header('Content-Type: application/json; charset=utf-8');

if (!auth_check()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'انتهت الجلسة']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'طريقة الطلب غير مسموحة']);
    exit;
}

$token = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'msg' => 'رمز الحماية غير صالح — أعد تحميل الصفحة']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'معرف غير صالح']);
    exit;
}

try {
    $pdo->prepare("UPDATE why_us SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
    $stmt = $pdo->prepare("SELECT is_active FROM why_us WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'msg' => 'الميزة غير موجودة']);
        exit;
    }
    echo json_encode([
        'ok'        => true,
        'is_active' => (int)$row['is_active'],
        'msg'       => (int)$row['is_active'] ? 'تم إظهار الميزة' : 'تم إخفاء الميزة',
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'خطأ في الخادم']);
}