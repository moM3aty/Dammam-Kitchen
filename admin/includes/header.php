<?php
if (!defined('SK_ADMIN')) { exit; }
$currentPage = $currentPage ?? '';
$user = auth_user();
$initial = $user ? mb_strtoupper(mb_substr($user['full_name'] ?: $user['username'], 0, 1)) : '؟';
$unreadCount = 0;
$recentNotifications = [];
try {
    $unreadCount = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
    $recentNotifications = $pdo->query("SELECT id, name, message, service, created_at FROM messages WHERE is_read = 0 ORDER BY created_at DESC LIMIT 3")->fetchAll();
} catch (Exception $e) {}
$pageTitle = $pageTitle ?? 'لوحة التحكم';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="#0a1214">
<title><?= e($pageTitle) ?> | <?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Changa:wght@600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
// Cache-busting: يحدّث الـ version تلقائياً لما الملف يتغير
function cssAsset($file) {
    $path = __DIR__ . '/../assets/css/' . $file;
    $v = file_exists($path) ? filemtime($path) : time();
    return SITE_URL . '/admin/assets/css/' . $file . '?v=' . $v;
}
?>
<link rel="stylesheet" href="<?= cssAsset('admin.css') ?>">
<link rel="stylesheet" href="<?= cssAsset('forms.css') ?>">
<link rel="stylesheet" href="<?= cssAsset('dashboard.css') ?>">
<link rel="stylesheet" href="<?= cssAsset('layout.css') ?>">
<link rel="stylesheet" href="<?= cssAsset('sidebar.css') ?>">
<link rel="stylesheet" href="<?= cssAsset('services.css') ?>">
<link rel="stylesheet" href="<?= cssAsset('gallery.css') ?>">
<link rel="stylesheet" href="<?= cssAsset('testimonials.css') ?>"> 
<link rel="stylesheet" href="<?= cssAsset('faqs.css') ?>">  
<link rel="stylesheet" href="<?= cssAsset('steps.css') ?>">   
<link rel="stylesheet" href="<?= cssAsset('why-us.css') ?>">    
<link rel="stylesheet" href="<?= cssAsset('materials.css') ?>">   
<link rel="stylesheet" href="<?= cssAsset('kitchen-types.css') ?>">   
<link rel="stylesheet" href="<?= cssAsset('areas.css') ?>">   
<link rel="stylesheet" href="<?= cssAsset('settings.css') ?>">   
<link rel="stylesheet" href="<?= cssAsset('profile.css') ?>">   
<link rel="stylesheet" href="<?= cssAsset('sweetalert-theme.css') ?>">
</head>
<body class="admin-body" id="adminBody">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="main">

<header class="topbar">
    <button type="button" class="tb-toggle" id="sbToggle" aria-label="إظهار/إخفاء القائمة">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="M3 6h18M3 12h18M3 18h18"/>
        </svg>
    </button>

    <nav class="tb-crumb" aria-label="مسار التنقل">
        <a href="<?= SITE_URL ?>/admin/" class="tb-crumb-home" title="لوحة التحكم">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </a>
        <svg class="tb-crumb-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="M15 18l-6-6 6-6"/>
        </svg>
        <b class="tb-crumb-cur"><?= e($pageTitle) ?></b>
    </nav>

    <div class="tb-spacer"></div>

    <a href="<?= SITE_URL ?>" target="_blank" rel="noopener" class="tb-icon tb-icon-view" title="زيارة الموقع">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
            <path d="M15 3h6v6M10 14L21 3"/>
        </svg>
    </a>

    <div class="tb-dd-wrap">
        <button type="button" class="tb-icon tb-icon-notif" id="tbNotifBtn" aria-haspopup="true" aria-expanded="false" title="الإشعارات">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <?php if ($unreadCount > 0): ?>
                <span class="tb-notif-dot"></span>
                <span class="tb-notif-count"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
            <?php endif; ?>
        </button>

        <div class="tb-dd tb-dd-notif" id="tbNotifMenu" aria-hidden="true">
            <div class="tb-dd-head">
                <div>
                    <b>الإشعارات</b>
                    <small><?= $unreadCount > 0 ? $unreadCount . ' رسالة جديدة' : 'لا جديد' ?></small>
                </div>
            </div>
            <div class="tb-dd-body">
                <?php if (!$recentNotifications): ?>
                    <div class="tb-dd-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <p>لا توجد إشعارات جديدة</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentNotifications as $n): ?>
                        <a href="<?= SITE_URL ?>/admin/messages.php?view=<?= (int)$n['id'] ?>" class="tb-notif-item">
                            <div class="tb-notif-av"><?= e(mb_substr($n['name'], 0, 1)) ?></div>
                            <div class="tb-notif-body">
                                <div class="tb-notif-top">
                                    <b><?= e($n['name']) ?></b>
                                    <small><?= e(format_date($n['created_at'], 'H:i')) ?></small>
                                </div>
                                <p><?= e($n['service'] ?: 'رسالة جديدة') ?></p>
                                <?php if ($n['message']): ?>
                                    <span class="tb-notif-prev"><?= e(mb_substr($n['message'], 0, 50)) ?>…</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <a href="<?= SITE_URL ?>/admin/messages.php" class="tb-dd-foot">
                عرض كل الرسائل
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="tb-dd-wrap">
        <button type="button" class="tb-user" id="tbUserBtn" aria-haspopup="true" aria-expanded="false">
            <div class="tb-avatar"><?= e($initial) ?></div>
            <div class="tb-uinfo">
                <b><?= e($user['full_name'] ?: $user['username']) ?></b>
                <small><?= $user['role'] === 'admin' ? 'مدير الموقع' : 'محرر' ?></small>
            </div>
            <svg class="tb-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </button>

        <div class="tb-dd tb-dd-user" id="tbUserMenu" aria-hidden="true">
            <div class="tb-dd-user-head">
                <div class="tb-avatar tb-avatar-lg"><?= e($initial) ?></div>
                <div class="tb-dd-user-info">
                    <b><?= e($user['full_name'] ?: $user['username']) ?></b>
                    <small dir="ltr"><?= e($user['email'] ?: '@' . $user['username']) ?></small>
                    <span class="tb-dd-user-role"><?= $user['role'] === 'admin' ? 'مدير الموقع' : 'محرر' ?></span>
                </div>
            </div>
            <div class="tb-dd-links">
                <a href="<?= SITE_URL ?>/admin/profile.php" class="tb-dd-link">
                    <span class="tb-dd-link-ic tb-dd-ic-mint">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    الملف الشخصي
                </a>
                <a href="<?= SITE_URL ?>/admin/settings.php" class="tb-dd-link">
                    <span class="tb-dd-link-ic tb-dd-ic-blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </span>
                    الإعدادات
                </a>
                <a href="<?= SITE_URL ?>/admin/logout.php" class="tb-dd-link tb-dd-link-danger">
                    <span class="tb-dd-link-ic tb-dd-ic-red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                    </span>
                    تسجيل الخروج
                </a>
            </div>
        </div>
    </div>
</header>

<div class="content">