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
    $pdo->prepare("DELETE FROM steps WHERE id = ?")->execute([$id]);
    echo json_encode(['ok' => true, 'msg' => 'تم حذف الخطوة']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'خطأ في الخادم']);
}