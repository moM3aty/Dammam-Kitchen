<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$currentPage = 'profile';
$pageTitle = 'الملف الشخصي';

$user = auth_user();
$errors = [];
$pwErrors = [];

// تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $do = $_POST['do'] ?? '';

    if ($do === 'profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');

        if (mb_strlen($fullName) < 2) $errors[] = 'الاسم مطلوب';
        if (mb_strlen($username) < 3) $errors[] = 'اسم المستخدم 3 أحرف على الأقل';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'البريد الإلكتروني غير صالح';

        // تحقق من عدم تكرار اسم المستخدم/الإيميل
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ?");
        $stmt->execute([$username, $email, $user['id']]);
        if ($stmt->fetch()) $errors[] = 'اسم المستخدم أو البريد مستخدم بالفعل';

        if (empty($errors)) {
            $pdo->prepare("UPDATE users SET full_name=?, email=?, username=? WHERE id=?")
                ->execute([$fullName, $email ?: null, $username, $user['id']]);
            $_SESSION['username'] = $username;
            flash('ok', 'تم تحديث البيانات بنجاح');
            redirect(SITE_URL . '/admin/profile.php');
        }
    }

    if ($do === 'password') {
        $cur = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $conf = (string)($_POST['confirm_password'] ?? '');

        if (!password_verify($cur, $user['password_hash'])) $pwErrors[] = 'كلمة المرور الحالية غير صحيحة';
        if (strlen($new) < 8) $pwErrors[] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        if ($new !== $conf) $pwErrors[] = 'كلمتا المرور غير متطابقتين';

        if (empty($pwErrors)) {
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
                ->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            flash('ok', 'تم تغيير كلمة المرور بنجاح');
            redirect(SITE_URL . '/admin/profile.php');
        }
    }
}

$user = auth_user(); // إعادة جلب
$flashes = get_flashes();
include __DIR__ . '/includes/header.php';

$initial = mb_strtoupper(mb_substr($user['full_name'] ?: $user['username'], 0, 1));
?>
<link rel="stylesheet" href="<?= SITE_URL ?>/admin/assets/css/profile.css">

<?php foreach ($flashes as $f): ?>
    <div class="flash flash-<?= e($f['type']) ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <?php if ($f['type'] === 'ok'): ?><path d="M20 6L9 17l-5-5"/>
            <?php elseif ($f['type'] === 'err'): ?><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/>
            <?php else: ?><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/><?php endif; ?>
        </svg>
        <span><?= e($f['msg']) ?></span>
    </div>
<?php endforeach; ?>

<!-- ═══════════ PAGE HEAD ═══════════ -->
<div class="pf-page-head">
    <div class="pf-page-head-txt">
        <span class="pf-eyebrow">إدارة الحساب</span>
        <h1>الملف الشخصي</h1>
        <p>قم بتحديث بياناتك الشخصية أو تغيير كلمة المرور الخاصة بك</p>
    </div>
</div>

<div class="pf-layout">
    <!-- ═══════════ MAIN FORMS ═══════════ -->
    <div class="pf-main">
        
        <!-- بيانات الحساب -->
        <div class="pf-card">
            <div class="pf-card-h">
                <div class="pf-card-ic pf-card-ic-mint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div>
                    <h3>بيانات الحساب</h3>
                    <p>تحديث الاسم، اسم المستخدم والبريد الإلكتروني</p>
                </div>
            </div>
            <div class="pf-card-b">
                <?php if ($errors): ?>
                    <div class="pf-alert">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <div>
                            <b>يرجى مراجعة التالي:</b>
                            <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="do" value="profile">
                    
                    <div class="pf-field">
                        <label class="pf-label">الاسم الكامل <span class="pf-req">*</span></label>
                        <input type="text" name="full_name" class="pf-input" value="<?= e($user['full_name']) ?>" required>
                    </div>
                    
                    <div class="pf-field">
                        <label class="pf-label">اسم المستخدم <span class="pf-req">*</span></label>
                        <input type="text" name="username" class="pf-input" value="<?= e($user['username']) ?>" required dir="ltr">
                    </div>
                    
                    <div class="pf-field">
                        <label class="pf-label">البريد الإلكتروني</label>
                        <input type="email" name="email" class="pf-input" value="<?= e($user['email']) ?>" dir="ltr">
                    </div>

                    <div class="pf-form-actions">
                        <button type="submit" class="pf-btn pf-btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                            </svg>
                            حفظ البيانات
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- كلمة المرور -->
        <div class="pf-card">
            <div class="pf-card-h">
                <div class="pf-card-ic pf-card-ic-cop">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <div>
                    <h3>تغيير كلمة المرور</h3>
                    <p>تأكد من استخدام كلمة مرور قوية لحماية حسابك</p>
                </div>
            </div>
            <div class="pf-card-b">
                <?php if ($pwErrors): ?>
                    <div class="pf-alert">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <div>
                            <b>يرجى مراجعة التالي:</b>
                            <ul><?php foreach ($pwErrors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="do" value="password">
                    
                    <div class="pf-field">
                        <label class="pf-label">كلمة المرور الحالية <span class="pf-req">*</span></label>
                        <input type="password" name="current_password" class="pf-input" required autocomplete="current-password">
                    </div>
                    
                    <div class="pf-field">
                        <label class="pf-label">كلمة المرور الجديدة <span class="pf-req">*</span></label>
                        <input type="password" name="new_password" class="pf-input" required minlength="8" autocomplete="new-password">
                        <small class="pf-help">8 أحرف على الأقل. يُفضل استخدام مزيج من الحروف والأرقام والرموز.</small>
                    </div>
                    
                    <div class="pf-field">
                        <label class="pf-label">تأكيد كلمة المرور الجديدة <span class="pf-req">*</span></label>
                        <input type="password" name="confirm_password" class="pf-input" required autocomplete="new-password">
                    </div>

                    <div class="pf-form-actions">
                        <button type="submit" class="pf-btn" style="background:#0a1214; color:#fff;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            تحديث كلمة المرور
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- ═══════════ SIDEBAR ═══════════ -->
    <div class="pf-side">
        <div class="pf-user-card">
            <div class="pf-avatar"><?= e($initial) ?></div>
            <h2 class="pf-user-name"><?= e($user['full_name'] ?: $user['username']) ?></h2>
            <p class="pf-user-email"><?= e($user['email'] ?: 'لا يوجد بريد إلكتروني') ?></p>
            
            <div class="pf-user-meta">
                <div class="pf-meta-item">
                    <span class="pf-meta-lbl">الرتبة / الصلاحية</span>
                    <span class="pf-badge pf-badge-role"><?= e($user['role']) ?></span>
                </div>
                <div class="pf-meta-item">
                    <span class="pf-meta-lbl">آخر تسجيل دخول</span>
                    <span class="pf-badge pf-badge-date" dir="ltr"><?= e(format_date_ar($user['last_login'] ?? '')) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>