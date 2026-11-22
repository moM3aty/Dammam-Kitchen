<?php
require_once __DIR__ . '/includes/init.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

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
    $stmt = $pdo->prepare("SELECT file_path FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'msg' => 'العنصر غير موجود']);
        exit;
    }

    if (strpos($row['file_path'], 'uploads/') === 0) {
        $f = __DIR__ . '/../' . $row['file_path'];
        if (is_file($f)) @unlink($f);
    }

    $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$id]);
    echo json_encode(['ok' => true, 'msg' => 'تم حذف العنصر']);
} catch (Exception $e) {
    error_log('gallery_delete error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'خطأ في الخادم']);
}