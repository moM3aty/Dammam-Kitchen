<?php
// includes/functions.php
if (!defined('SK_APP')) { exit('No direct access'); }

function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify() {
    $t = $_POST['csrf'] ?? '';
    if (!$t || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
        http_response_code(419);
        die('انتهت صلاحية الجلسة. أعد المحاولة من فضلك.');
    }
}

function flash($type, $msg) {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function get_flashes() {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function setting($key, $default = '') {
    global $pdo;
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
            foreach ($rows as $r) { $cache[$r['setting_key']] = $r['setting_value']; }
        } catch (Exception $e) { $cache = []; }
    }
    return $cache[$key] ?? $default;
}

function set_setting($key, $value) {
    global $pdo;
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    $pdo->prepare($sql)->execute([$key, $value]);
}

function format_date($dt, $format = 'Y-m-d H:i') {
    if (!$dt) return '';
    try { return (new DateTime($dt))->format($format); }
    catch (Exception $e) { return $dt; }
}

function format_date_ar($dt) {
    if (!$dt) return '';
    try {
        $d = new DateTime($dt);
        $months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
        return $d->format('j') . ' ' . $months[(int)$d->format('n')-1] . ' ' . $d->format('Y');
    } catch (Exception $e) { return $dt; }
}

function slugify($s) {
    $s = trim((string)$s);
    $s = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $s);
    $s = preg_replace('/\s+/u', '-', $s);
    return mb_strtolower($s, 'UTF-8');
}

function json_or_empty($str) {
    if (!$str) return [];
    $d = json_decode($str, true);
    return is_array($d) ? $d : [];
}

function upload_image($file, $dir = 'uploads') {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['jpg','jpeg','png','gif','webp','mp4','webm','mov'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) return null;
    $name = bin2hex(random_bytes(10)) . '.' . $ext;
    $destDir = __DIR__ . '/../' . $dir;
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $destDir . '/' . $name)) {
        return $dir . '/' . $name;
    }
    return null;
}
function media_url($path) {
    if (!$path) return '';
    $path = trim((string)$path);
    if (preg_match('#^(https?:)?//#i', $path)) return $path;
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}