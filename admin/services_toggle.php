<?php
// admin/services_toggle.php — AJAX toggle active/inactive
require_once __DIR__ . '/includes/init.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Method not allowed']);
    exit;
}

// CSRF
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
    // Toggle
    $pdo->prepare("UPDATE services SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
    $stmt = $pdo->prepare("SELECT is_active FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'msg' => 'الخدمة غير موجودة']);
        exit;
    }
    echo json_encode([
        'ok'        => true,
        'is_active' => (int)$row['is_active'],
        'msg'       => (int)$row['is_active'] ? 'تم تفعيل الخدمة' : 'تم تعطيل الخدمة',
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'خطأ في الخادم']);
}