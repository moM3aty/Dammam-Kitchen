<?php
require_once __DIR__ . '/includes/init.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Method not allowed']);
    exit;
}

$token = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'msg' => 'انتهت صلاحية الجلسة']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'معرف غير صالح']);
    exit;
}

try {
    $pdo->prepare("UPDATE testimonials SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
    $stmt = $pdo->prepare("SELECT is_active FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'msg' => 'الرأي غير موجود']);
        exit;
    }
    echo json_encode([
        'ok'        => true,
        'is_active' => (int)$row['is_active'],
        'msg'       => (int)$row['is_active'] ? 'تم إظهار الرأي' : 'تم إخفاء الرأي',
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'خطأ في الخادم']);
}