<?php
// includes/auth.php
if (!defined('SK_APP')) { exit('No direct access'); }

function auth_session_start() {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params([
        'lifetime' => ADMIN_SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name(ADMIN_SESSION_NAME);
    session_start();
}

function auth_user() {
    global $pdo;
    if (empty($_SESSION['user_id'])) return null;
    static $u = null;
    if ($u && $u['id'] == $_SESSION['user_id']) return $u;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $u = $stmt->fetch() ?: null;
        return $u;
    } catch (Exception $e) { return null; }
}

function auth_check() {
    return auth_user() !== null;
}

function require_login() {
    if (!auth_check()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

function auth_login($username, $password) {
    global $pdo;

    // rate limit: max 5 failed attempts per IP in last 15 min
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts 
            WHERE ip_address = ? AND success = 0 AND attempted_at > (NOW() - INTERVAL 15 MINUTE)");
        $stmt->execute([$ip]);
        if ((int)$stmt->fetchColumn() >= 5) {
            return ['ok' => false, 'msg' => 'تم تجاوز عدد المحاولات. يرجى الانتظار 15 دقيقة.'];
        }
    } catch (Exception $e) { /* table may not exist yet */ }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    $ok = $user && password_verify($password, $user['password_hash']);

    try {
        $pdo->prepare("INSERT INTO login_attempts (ip_address, username, success) VALUES (?,?,?)")
            ->execute([$ip, mb_substr($username, 0, 100), $ok ? 1 : 0]);
    } catch (Exception $e) {}

    if (!$ok) {
        return ['ok' => false, 'msg' => 'اسم المستخدم أو كلمة المرور غير صحيحة.'];
    }

    // upgrade hash if needed
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
            ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }

    // login success
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['logged_in'] = time();
    $_SESSION['csrf']      = bin2hex(random_bytes(32));

    try {
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
    } catch (Exception $e) {}

    return ['ok' => true, 'user' => $user];
}

function auth_logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }
    session_destroy();
}