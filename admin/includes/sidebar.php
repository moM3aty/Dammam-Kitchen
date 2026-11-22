<?php
if (!defined('SK_ADMIN')) { exit; }
$currentPage = $currentPage ?? '';
$user = auth_user();
$initial = $user ? mb_strtoupper(mb_substr($user['full_name'] ?: $user['username'], 0, 1)) : '؟';

function nav_active($key, $curr) { return $key === $curr ? ' active' : ''; }

$unreadSb = 0;
try { $unreadSb = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn(); } catch (Exception $e) {}
?>

<aside class="sb" id="sidebar">
    <button type="button" class="sb-close" id="sbClose" aria-label="إغلاق القائمة">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M18 6L6 18M6 6l12 12"/>
        </svg>
    </button>

    <div class="sb-top">
        <a href="<?= SITE_URL ?>/admin/" class="sb-logo-wrap" aria-label="لوحة التحكم">
            <span class="sb-logo">
                <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="6" y="6" width="52" height="18" rx="3"/>
                    <path d="M32 6V24M4 35H60"/>
                    <rect x="8" y="39" width="48" height="19" rx="3"/>
                    <path d="M24 39V58M40 39V58"/>
                </svg>
            </span>
            <span class="sb-brand">
                <b><?= APP_NAME ?></b>
                <small><span class="sb-live-dot"></span>لوحة التحكم</small>
            </span>
        </a>
    </div>

    <nav class="sb-nav" aria-label="قائمة لوحة التحكم">

        <div class="sb-sec">
            <div class="sb-sec-t">الرئيسية</div>

            <a href="<?= SITE_URL ?>/admin/" class="sb-lnk<?= nav_active('dashboard', $currentPage) ?>" data-title="لوحة القيادة">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                        <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                        <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">لوحة القيادة</span>
            </a>

            <a href="<?= SITE_URL ?>/admin/messages.php" class="sb-lnk<?= nav_active('messages', $currentPage) ?>" data-title="الرسائل">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">الرسائل</span>
                <?php if ($unreadSb > 0): ?>
                    <span class="sb-badge"><?= $unreadSb > 99 ? '99+' : $unreadSb ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="sb-sec">
            <div class="sb-sec-t">المحتوى</div>

            <a href="<?= SITE_URL ?>/admin/services.php" class="sb-lnk<?= nav_active('services', $currentPage) ?>" data-title="الخدمات">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">الخدمات</span>
            </a>

            <a href="<?= SITE_URL ?>/admin/gallery.php" class="sb-lnk<?= nav_active('gallery', $currentPage) ?>" data-title="المعرض">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="M21 15l-5-5L5 21"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">المعرض</span>
            </a>

            <a href="<?= SITE_URL ?>/admin/testimonials.php" class="sb-lnk<?= nav_active('testimonials', $currentPage) ?>" data-title="آراء العملاء">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">آراء العملاء</span>
            </a>

            <a href="<?= SITE_URL ?>/admin/faqs.php" class="sb-lnk<?= nav_active('faqs', $currentPage) ?>" data-title="الأسئلة الشائعة">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">الأسئلة الشائعة</span>
            </a>

            <a href="<?= SITE_URL ?>/admin/steps.php" class="sb-lnk<?= nav_active('steps', $currentPage) ?>" data-title="خطوات العمل">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h6v6H4zM14 14h6v6h-6zM4 14h6M14 4h6"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">خطوات العمل</span>
            </a>

            <a href="<?= SITE_URL ?>/admin/why-us.php" class="sb-lnk<?= nav_active('why', $currentPage) ?>" data-title="لماذا نحن">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4"/>
                        <circle cx="12" cy="12" r="10"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">لماذا نحن</span>
            </a>

            <a href="<?= SITE_URL ?>/admin/materials.php" class="sb-lnk<?= nav_active('materials', $currentPage) ?>" data-title="الخامات">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2l9 5v10l-9 5-9-5V7z"/>
                        <path d="M12 22V12M3 7l9 5 9-5"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">الخامات</span>
            </a>

            <a href="<?= SITE_URL ?>/admin/kitchen-types.php" class="sb-lnk<?= nav_active('kitchen', $currentPage) ?>" data-title="أنواع المطابخ">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v14"/>
                        <path d="M3 12h18M3 21h18"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">أنواع المطابخ</span>
            </a>

            <a href="<?= SITE_URL ?>/admin/areas.php" class="sb-lnk<?= nav_active('areas', $currentPage) ?>" data-title="مناطق الخدمة">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">مناطق الخدمة</span>
            </a>
        </div>

        <div class="sb-sec">
            <div class="sb-sec-t">النظام</div>

            <a href="<?= SITE_URL ?>/admin/settings.php" class="sb-lnk<?= nav_active('settings', $currentPage) ?>" data-title="الإعدادات">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">الإعدادات</span>
            </a>

            <a href="<?= SITE_URL ?>/admin/profile.php" class="sb-lnk<?= nav_active('profile', $currentPage) ?>" data-title="الملف الشخصي">
                <span class="sb-lnk-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </span>
                <span class="sb-lnk-t">الملف الشخصي</span>
            </a>
        </div>
    </nav>

    <div class="sb-foot">
        <a href="<?= SITE_URL ?>/admin/profile.php" class="sb-user-card">
            <span class="sb-user-av"><?= e($initial) ?></span>
            <span class="sb-user-info">
                <b><?= e($user['full_name'] ?: $user['username']) ?></b>
                <small><?= $user['role'] === 'admin' ? 'مدير الموقع' : 'محرر' ?></small>
            </span>
        </a>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="sb-logout" title="تسجيل الخروج" aria-label="تسجيل الخروج">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
        </a>
    </div>
</aside>