<?php
// admin/services_duplicate.php — AJAX duplicate service
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
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $src = $stmt->fetch();
    if (!$src) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'msg' => 'الخدمة غير موجودة']);
        exit;
    }

    // New title, sort order after max
    $newTitle = $src['title'] . ' (نسخة)';
    $maxSort  = (int)$pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM services")->fetchColumn();

    $pdo->prepare("INSERT INTO services (title, description, details, icon, features, keywords, button_text, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?)")
        ->execute([
            $newTitle,
            $src['description'],
            $src['details'],
            $src['icon'],
            $src['features'],
            $src['keywords'],
            $src['button_text'],
            $maxSort + 1,
            0, // نسخة معطلة افتراضياً
        ]);

    echo json_encode([
        'ok'  => true,
        'id'  => (int)$pdo->lastInsertId(),
        'msg' => 'تم إنشاء نسخة من الخدمة (معطلة افتراضياً)',
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'خطأ في الخادم']);
}