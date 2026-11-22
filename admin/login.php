<?php
require_once __DIR__ . '/includes/init.php';

if (auth_check()) { redirect(SITE_URL . '/admin/'); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $u = trim($_POST['username'] ?? '');
    $p = (string)($_POST['password'] ?? '');
    if ($u === '' || $p === '') {
        $error = 'يرجى إدخال بيانات الدخول كاملة.';
    } else {
        $r = auth_login($u, $p);
        if ($r['ok']) { redirect(SITE_URL . '/admin/'); }
        $error = $r['msg'];
    }
}

$sitePhone = setting('site_phone', '0507023985');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="#0a1214">
<title>تسجيل الدخول | شبح المطابخ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Changa:wght@500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/admin/assets/css/admin.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/admin/assets/css/layout.css">
</head>
<body class="lg-body">

<div class="lg-shell">

    <!-- ═══════════ BRAND PANEL ═══════════ -->
    <div class="lg-brand">
        <div class="lg-brand-bg">
            <span class="lg-blob lg-blob-1"></span>
            <span class="lg-blob lg-blob-2"></span>
            <span class="lg-blob lg-blob-3"></span>
            <span class="lg-grain"></span>
        </div>

        <div class="lg-brand-inner">
            <div class="lg-logo-wrap">
                <span class="lg-orbit lg-orbit-1"></span>
                <span class="lg-orbit lg-orbit-2"></span>
                <span class="lg-orbit lg-orbit-3"></span>
                <div class="lg-logo">
                    <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="6" y="6" width="52" height="18" rx="3"/>
                        <path d="M32 6V24M4 35H60"/>
                        <rect x="8" y="39" width="48" height="19" rx="3"/>
                        <path d="M24 39V58M40 39V58"/>
                    </svg>
                </div>
            </div>

            <h1 class="lg-site-name">شبح المطابخ</h1>
            <p class="lg-site-tag">لوحة التحكم الإدارية</p>

            <ul class="lg-features">
                <li>
                    <span class="lg-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
                    إدارة كاملة لكل محتوى الموقع
                </li>
                <li>
                    <span class="lg-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
                    متابعة رسائل العملاء والطلبات
                </li>
                <li>
                    <span class="lg-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
                    رفع وتنظيم صور وأعمال المعرض
                </li>
                <li>
                    <span class="lg-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
                    تحكم في كل الأقسام والنصوص
                </li>
            </ul>

            <div class="lg-version">
                <span class="lg-version-dot"></span>
                v<?= APP_VERSION ?> · dammamkitchen.online
            </div>
        </div>
    </div>

    <!-- ═══════════ FORM PANEL ═══════════ -->
    <div class="lg-form-wrap">
        <div class="lg-form-inner">

            <div class="lg-mobile-brand">
                <div class="lg-logo lg-logo-sm">
                    <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="6" y="6" width="52" height="18" rx="3"/>
                        <path d="M32 6V24M4 35H60"/>
                        <rect x="8" y="39" width="48" height="19" rx="3"/>
                        <path d="M24 39V58M40 39V58"/>
                    </svg>
                </div>
            </div>

            <div class="lg-head">
                <span class="lg-eyebrow">مرحباً بعودتك </span>
                <h2>تسجيل الدخول</h2>
                <p>سجّل الدخول للمتابعة إلى لوحة التحكم</p>
            </div>

            <?php if ($error): ?>
                <div class="lg-alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
                    </svg>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="on" class="lg-form" id="lgForm">
                <?= csrf_field() ?>

                <div class="lg-field">
                    <label class="lg-label" for="lgUser">اسم المستخدم أو البريد</label>
                    <div class="lg-input-wrap">
                        <svg class="lg-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <input
                            type="text" name="username" id="lgUser"
                            class="lg-input" required autofocus
                            value="<?= e($_POST['username'] ?? '') ?>"
                            autocomplete="username"
                            placeholder=""
                        >
                    </div>
                </div>

                <div class="lg-field">
                    <label class="lg-label" for="lgPwd">كلمة المرور</label>
                    <div class="lg-input-wrap">
                        <svg class="lg-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input
                            type="password" name="password" id="lgPwd"
                            class="lg-input" required
                            autocomplete="current-password"
                            placeholder=""
                        >
                        <button type="button" class="lg-pwd-btn" id="lgPwdBtn" aria-label="إظهار كلمة المرور">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div class="lg-caps" id="lgCaps">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M10 4L8 20M16 4l-2 16M4 8h16M4 16h16"/>
                        </svg>
                        Caps Lock مفعّل
                    </div>
                </div>

                <div class="lg-row">
                    <a href="javascript:void(0)" class="lg-link" onclick="alert('لإعادة تعيين كلمة المرور يرجى التواصل مع المسؤول.\n\nالهاتف: <?= e($sitePhone) ?>')">
                        نسيت كلمة المرور؟
                    </a>
                </div>

                <button type="submit" class="lg-submit" id="lgSubmit">
                    <span class="lg-submit-txt">دخول للوحة التحكم</span>
                    <svg class="lg-submit-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 5l-7 7 7 7"/>
                    </svg>
                    <span class="lg-submit-spin"></span>
                </button>
            </form>

            <div class="lg-foot">
                <a href="<?= SITE_URL ?>" class="lg-foot-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 5l-7 7 7 7"/>
                    </svg>
                    العودة للموقع
                </a>
                <span class="lg-foot-cr">© <?= date('Y') ?> شبح المطابخ</span>
            </div>

        </div>
    </div>

</div>

<script>
(function(){
    // Show/hide password
    var btn = document.getElementById('lgPwdBtn');
    var pwd = document.getElementById('lgPwd');
    if (btn && pwd) {
        btn.addEventListener('click', function(){
            var isText = pwd.type === 'text';
            pwd.type = isText ? 'password' : 'text';
            btn.classList.toggle('active', !isText);
            btn.setAttribute('aria-label', isText ? 'إظهار كلمة المرور' : 'إخفاء كلمة المرور');
            pwd.focus();
        });
    }

    // Caps lock detection
    var capsWarn = document.getElementById('lgCaps');
    if (pwd && capsWarn) {
        var checkCaps = function(e){
            if (typeof e.getModifierState === 'function') {
                capsWarn.classList.toggle('show', e.getModifierState('CapsLock'));
            }
        };
        pwd.addEventListener('keydown', checkCaps);
        pwd.addEventListener('keyup', checkCaps);
        pwd.addEventListener('blur', function(){ capsWarn.classList.remove('show'); });
    }

    // Loading state on submit
    var form = document.getElementById('lgForm');
    var submit = document.getElementById('lgSubmit');
    if (form && submit) {
        form.addEventListener('submit', function(){
            submit.classList.add('loading');
            submit.disabled = true;
        });
    }
})();
</script>
</body>
</html>