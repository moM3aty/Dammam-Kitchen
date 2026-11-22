<?php
// config.php
if (!defined('SK_APP')) { define('SK_APP', true); }

define('DB_HOST', 'localhost');
define('DB_NAME', 'u315614949_kitchen');
define('DB_USER', 'u315614949_kitchen');
define('DB_PASS', 'OlvCTS9*');
define('DB_CHARSET', 'utf8mb4');

define('SITE_URL', 'https://dammamkitchen.online');
define('APP_NAME', 'شبح المطابخ');
define('APP_VERSION', '1.0.0');
define('ADMIN_SESSION_NAME', 'SK_ADMIN');
define('ADMIN_SESSION_LIFETIME', 86400 * 7);

date_default_timezone_set('Asia/Riyadh');

$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die('<div style="font-family:sans-serif;padding:30px;text-align:center;color:#c33">
        <h2>تعذّر الاتصال بقاعدة البيانات</h2>
        <p>يرجى التحقق من بيانات الاتصال في config.php</p></div>');
}