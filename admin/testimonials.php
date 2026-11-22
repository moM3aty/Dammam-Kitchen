<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$currentPage = 'testimonials';
$pageTitle = 'آراء العملاء';

$action = $_GET['action'] ?? 'list';
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

/* ═══════════ POST ACTIONS ═══════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $do = $_POST['do'] ?? '';

    if ($do === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$id]);
            flash('ok', 'تم حذف الرأي بنجاح');
        }
        redirect(SITE_URL . '/admin/testimonials.php');
    }

    if ($do === 'save') {
        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['client_name'] ?? '');
        $letter   = trim($_POST['avatar_letter'] ?? '');
        $content  = trim($_POST['content'] ?? '');
        $rating   = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $sort     = (int)($_POST['sort_order'] ?? 0);
        $active   = isset($_POST['is_active']) ? 1 : 0;

        if (mb_strlen($name) < 2) $errors[] = 'اسم العميل مطلوب (حرفان على الأقل)';
        if (mb_strlen($content) < 10) $errors[] = 'محتوى الرأي قصير جداً (10 أحرف على الأقل)';

        if (empty($errors)) {
            // لو الـ avatar فاضي، خذ أول حرف
            if ($letter === '') {
                $letter = mb_substr($name, 0, 1);
            }

            if ($id > 0) {
                $pdo->prepare("UPDATE testimonials SET client_name=?, avatar_letter=?, content=?, rating=?, sort_order=?, is_active=? WHERE id=?")
                    ->execute([$name, $letter, $content, $rating, $sort, $active, $id]);
                flash('ok', 'تم تحديث الرأي');
            } else {
                $pdo->prepare("INSERT INTO testimonials (client_name, avatar_letter, content, rating, sort_order, is_active) VALUES (?,?,?,?,?,?)")
                    ->execute([$name, $letter, $content, $rating, $sort, $active]);
                flash('ok', 'تمت إضافة الرأي');
            }
            redirect(SITE_URL . '/admin/testimonials.php');
        }
    }
}

$editItem = null;
if ($action === 'edit' && $editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
    if (!$editItem) { flash('err', 'الرأي غير موجود'); redirect(SITE_URL . '/admin/testimonials.php'); }
}
$showForm = ($action === 'new' || ($action === 'edit' && $editItem));

/* ═══════════ LIST ═══════════ */
$filter = $_GET['f'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($filter === 'active')   $where[] = "is_active = 1";
if ($filter === 'inactive') $where[] = "is_active = 0";
if ($filter === '5star')    $where[] = "rating = 5";
if ($search !== '') {
    $where[] = "(client_name LIKE ? OR content LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
}
$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("SELECT * FROM testimonials $whereSQL ORDER BY sort_order ASC, id ASC");
$stmt->execute($params);
$items = $stmt->fetchAll();

/* Counts */
$totalCount    = (int)$pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
$activeCount   = (int)$pdo->query("SELECT COUNT(*) FROM testimonials WHERE is_active=1")->fetchColumn();
$inactiveCount = $totalCount - $activeCount;
$fiveStarCount = (int)$pdo->query("SELECT COUNT(*) FROM testimonials WHERE rating=5")->fetchColumn();

/* Avg rating */
$avgRating = 0;
try {
    $avgRating = round((float)$pdo->query("SELECT COALESCE(AVG(rating), 0) FROM testimonials WHERE is_active=1")->fetchColumn(), 1);
} catch (Exception $e) {}

$flashes = get_flashes();
include __DIR__ . '/includes/header.php';
?>

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
<div class="tst-page-head">
    <div class="tst-page-head-txt">
        <span class="tst-eyebrow">إدارة المحتوى</span>
        <h1><?= $showForm ? ($editItem ? 'تعديل رأي العميل' : 'إضافة رأي جديد') : 'آراء العملاء' ?></h1>
        <p><?= $showForm ? 'املأ البيانات واختر التقييم' : 'آراء العملاء المعروضة في الصفحة الرئيسية' ?></p>
    </div>
    <div class="tst-page-head-actions">
        <?php if ($showForm): ?>
            <a href="<?= SITE_URL ?>/admin/testimonials.php" class="tst-btn tst-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                العودة للقائمة
            </a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/admin/testimonials.php?action=new" class="tst-btn tst-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                إضافة رأي جديد
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($showForm): ?>

    <!-- ═══════════ FORM ═══════════ -->
    <?php if ($errors): ?>
        <div class="tst-alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <div>
                <b>يرجى مراجعة التالي:</b>
                <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" class="tst-form">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="save">
        <input type="hidden" name="id" value="<?= $editItem ? (int)$editItem['id'] : 0 ?>">

        <div class="tst-form-grid">
            <div class="tst-form-main">
                <!-- Content card -->
                <div class="tst-card">
                    <div class="tst-card-h">
                        <div class="tst-card-ic tst-card-ic-mint">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3>محتوى الرأي</h3>
                            <p>اسم العميل ونص الرأي والتقييم</p>
                        </div>
                    </div>
                    <div class="tst-card-b">
                        <div class="tst-field">
                            <label class="tst-label">اسم العميل <span class="tst-req">*</span></label>
                            <input type="text" name="client_name" class="tst-input" required maxlength="120"
                                   id="tstName"
                                   value="<?= e($editItem['client_name'] ?? '') ?>"
                                   placeholder="مثال: أبو فيصل">
                        </div>

                        <div class="tst-field">
                            <label class="tst-label">نص الرأي <span class="tst-req">*</span></label>
                            <textarea name="content" class="tst-input tst-textarea" rows="6" required
                                      placeholder="مثال: فصّلنا مطبخ ألمنيوم لشقتنا، وكان القياس دقيقاً والتركيب مرتباً."><?= e($editItem['content'] ?? '') ?></textarea>
                            <small class="tst-help"><span id="tstCharCount">0</span> حرف</small>
                        </div>

                        <div class="tst-field">
                            <label class="tst-label">التقييم <span class="tst-req">*</span></label>
                            <div class="tst-rating" id="tstRating">
                                <?php 
                                $currentRating = (int)($editItem['rating'] ?? 5);
                                for ($i = 1; $i <= 5; $i++): 
                                ?>
                                    <button type="button" class="tst-star<?= $i <= $currentRating ? ' active' : '' ?>" data-value="<?= $i ?>" aria-label="<?= $i ?> نجوم">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </button>
                                <?php endfor; ?>
                                <input type="hidden" name="rating" id="tstRatingVal" value="<?= $currentRating ?>">
                                <span class="tst-rating-lbl" id="tstRatingLbl">
                                    <?= $currentRating ?> / 5
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tst-form-side">
                <!-- Settings -->
                <div class="tst-card">
                    <div class="tst-card-h">
                        <div class="tst-card-ic tst-card-ic-violet">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                        </div>
                        <div>
                            <h3>الإعدادات</h3>
                            <p>Avatar والترتيب والحالة</p>
                        </div>
                    </div>
                    <div class="tst-card-b">

                        <!-- Avatar preview -->
                        <div class="tst-field">
                            <label class="tst-label">Avatar (حرف/حرفين)</label>
                            <div class="tst-avatar-picker">
                                <div class="tst-avatar-preview" id="tstAvatarPreview">
                                    <?= e($editItem['avatar_letter'] ?? mb_substr($editItem['client_name'] ?? '؟', 0, 1)) ?>
                                </div>
                                <input type="text" name="avatar_letter" id="tstAvatarInput" class="tst-input" maxlength="3"
                                       value="<?= e($editItem['avatar_letter'] ?? '') ?>"
                                       placeholder="حرف واحد">
                            </div>
                            <small class="tst-help">حرف أو حرفان يظهران في الدائرة. اتركه فارغاً لأخذ أول حرف من الاسم.</small>
                        </div>

                        <div class="tst-field">
                            <label class="tst-label">ترتيب العرض</label>
                            <input type="number" name="sort_order" class="tst-input"
                                   value="<?= (int)($editItem['sort_order'] ?? 0) ?>">
                            <small class="tst-help">الأرقام الأصغر تظهر أولاً</small>
                        </div>

                        <div class="tst-switch-wrap">
                            <label class="tst-switch">
                                <input type="checkbox" name="is_active" value="1" <?= ($editItem['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <span class="tst-switch-track"><span class="tst-switch-thumb"></span></span>
                                <span class="tst-switch-lbl">
                                    <b>ظاهر في الموقع</b>
                                    <small>يظهر الرأي في الصفحة الرئيسية</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="tst-card tst-card-actions">
                    <div class="tst-card-b">
                        <button type="submit" class="tst-btn tst-btn-primary tst-btn-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            <?= $editItem ? 'حفظ التعديلات' : 'إضافة الرأي' ?>
                        </button>
                        <a href="<?= SITE_URL ?>/admin/testimonials.php" class="tst-btn tst-btn-ghost tst-btn-block">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
    (function(){
        // Interactive star rating
        var stars = document.querySelectorAll('.tst-star');
        var val = document.getElementById('tstRatingVal');
        var lbl = document.getElementById('tstRatingLbl');
        var labels = {1:'ضعيف', 2:'مقبول', 3:'جيد', 4:'جيد جداً', 5:'ممتاز'};

        function setRating(n) {
            val.value = n;
            lbl.textContent = n + ' / 5 — ' + labels[n];
            stars.forEach(function(s, i){
                s.classList.toggle('active', i < n);
            });
        }

        stars.forEach(function(s){
            s.addEventListener('click', function(){
                setRating(parseInt(this.dataset.value, 10));
            });
            s.addEventListener('mouseenter', function(){
                var n = parseInt(this.dataset.value, 10);
                stars.forEach(function(st, i){ st.classList.toggle('hover', i < n); });
            });
            s.addEventListener('mouseleave', function(){
                stars.forEach(function(st){ st.classList.remove('hover'); });
            });
        });

        // Avatar live preview
        var nameIn = document.getElementById('tstName');
        var avatarIn = document.getElementById('tstAvatarInput');
        var avatarPrev = document.getElementById('tstAvatarPreview');

        function updateAvatar() {
            var v = avatarIn.value.trim() || (nameIn.value.trim() ? nameIn.value.trim().charAt(0) : '؟');
            avatarPrev.textContent = v || '؟';
        }
        if (nameIn) nameIn.addEventListener('input', updateAvatar);
        if (avatarIn) avatarIn.addEventListener('input', updateAvatar);

        // Character count
        var textarea = document.querySelector('textarea[name="content"]');
        var counter = document.getElementById('tstCharCount');
        if (textarea && counter) {
            counter.textContent = textarea.value.length;
            textarea.addEventListener('input', function(){
                counter.textContent = this.value.length;
            });
        }

        // Initial label
        setRating(parseInt(val.value, 10) || 5);
    })();
    </script>

<?php else: ?>

    <!-- ═══════════ LIST ═══════════ -->
    <div class="tst-stats">
        <div class="tst-stat">
            <div class="tst-stat-ic tst-stat-mint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="tst-stat-body">
                <span class="tst-stat-num"><?= $totalCount ?></span>
                <span class="tst-stat-lbl">إجمالي الآراء</span>
            </div>
        </div>
        <div class="tst-stat">
            <div class="tst-stat-ic tst-stat-ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
            <div class="tst-stat-body">
                <span class="tst-stat-num"><?= $activeCount ?></span>
                <span class="tst-stat-lbl">ظاهر</span>
            </div>
        </div>
        <div class="tst-stat">
            <div class="tst-stat-ic tst-stat-cop">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <div class="tst-stat-body">
                <span class="tst-stat-num"><?= $avgRating ?></span>
                <span class="tst-stat-lbl">متوسط التقييم</span>
            </div>
        </div>
        <div class="tst-stat">
            <div class="tst-stat-ic tst-stat-gold">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <div class="tst-stat-body">
                <span class="tst-stat-num"><?= $fiveStarCount ?></span>
                <span class="tst-stat-lbl">تقييم 5 نجوم</span>
            </div>
        </div>
    </div>

    <?php if (!$items && !$search && $filter === 'all'): ?>
        <div class="tst-empty">
            <div class="tst-empty-ic">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <h3>لا توجد آراء بعد</h3>
            <p>ابدأ بإضافة أول رأي عميل لعرضه في الموقع</p>
            <a href="<?= SITE_URL ?>/admin/testimonials.php?action=new" class="tst-btn tst-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                إضافة رأي
            </a>
        </div>
    <?php else: ?>

        <!-- Toolbar -->
        <div class="tst-toolbar">
            <form method="get" class="tst-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="ابحث بالاسم أو نص الرأي...">
                <input type="hidden" name="f" value="<?= e($filter) ?>">
                <?php if ($search): ?>
                    <a href="?f=<?= e($filter) ?>" class="tst-search-clear" aria-label="مسح">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </a>
                <?php endif; ?>
            </form>

            <div class="tst-tabs">
                <a href="?f=all&q=<?= e($search) ?>" class="tst-tab <?= $filter === 'all' ? 'on' : '' ?>">
                    الكل <span class="tst-tab-count"><?= $totalCount ?></span>
                </a>
                <a href="?f=active&q=<?= e($search) ?>" class="tst-tab <?= $filter === 'active' ? 'on' : '' ?>">
                    ظاهر <span class="tst-tab-count"><?= $activeCount ?></span>
                </a>
                <a href="?f=inactive&q=<?= e($search) ?>" class="tst-tab <?= $filter === 'inactive' ? 'on' : '' ?>">
                    مخفي <span class="tst-tab-count"><?= $inactiveCount ?></span>
                </a>
                <a href="?f=5star&q=<?= e($search) ?>" class="tst-tab <?= $filter === '5star' ? 'on' : '' ?>">
                    5★ <span class="tst-tab-count"><?= $fiveStarCount ?></span>
                </a>
            </div>
        </div>

        <?php if (!$items): ?>
            <div class="tst-no-results">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <h4>لا توجد نتائج</h4>
                <p>جرّب كلمات بحث أخرى أو غيّر الفلتر</p>
            </div>
        <?php else: ?>
            <div class="tst-cards">
                <?php foreach ($items as $it): 
                    $av = $it['avatar_letter'] ?: mb_substr($it['client_name'], 0, 1);
                ?>
                    <article class="tst-item<?= !$it['is_active'] ? ' tst-item-off' : '' ?>"
                             data-id="<?= (int)$it['id'] ?>">
                        <div class="tst-item-quote">"</div>

                        <div class="tst-item-head">
                            <div class="tst-item-av"><?= e($av) ?></div>
                            <div class="tst-item-info">
                                <b><?= e($it['client_name']) ?></b>
                                <div class="tst-item-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg class="tst-mini-star<?= $i <= (int)$it['rating'] ? ' filled' : '' ?>" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>

                        <blockquote class="tst-item-content"><?= e($it['content']) ?></blockquote>

                        <div class="tst-item-meta">
                            <span class="tst-tag <?= $it['is_active'] ? 'tst-tag-ok' : 'tst-tag-mute' ?>">
                                <span class="tst-tag-dot"></span>
                                <?= $it['is_active'] ? 'ظاهر' : 'مخفي' ?>
                            </span>
                            <span class="tst-tag tst-tag-order">ترتيب: <?= (int)$it['sort_order'] ?></span>
                        </div>

                        <div class="tst-item-actions">
                            <button type="button" class="tst-icon-btn tst-icon-btn-toggle" data-action="toggle" title="تغيير الحالة">
                                <svg class="tst-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <span class="tst-btn-lbl"><?= $it['is_active'] ? 'إخفاء' : 'إظهار' ?></span>
                            </button>

                            <a href="<?= SITE_URL ?>/admin/testimonials.php?action=edit&id=<?= (int)$it['id'] ?>" class="tst-icon-btn tst-icon-btn-edit" title="تعديل">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>

                            <button type="button" class="tst-icon-btn tst-icon-btn-del" data-action="delete" title="حذف">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <script>
            (function(){
                document.querySelectorAll('.tst-item').forEach(function(card){
                    var id = card.dataset.id;
                    var toggleBtn = card.querySelector('[data-action="toggle"]');
                    var delBtn = card.querySelector('[data-action="delete"]');

                    if (toggleBtn) {
                        toggleBtn.addEventListener('click', function(){
                            toggleBtn.classList.add('loading');
                            SKAjax('<?= SITE_URL ?>/admin/testimonials_toggle.php', { id: id })
                                .then(function(res){
                                    if (!res.ok) throw new Error(res.msg);
                                    var isActive = res.is_active === 1;
                                    card.classList.toggle('tst-item-off', !isActive);
                                    var tag = card.querySelector('.tst-tag');
                                    if (tag) {
                                        tag.classList.toggle('tst-tag-ok', isActive);
                                        tag.classList.toggle('tst-tag-mute', !isActive);
                                        tag.innerHTML = '<span class="tst-tag-dot"></span> ' + (isActive ? 'ظاهر' : 'مخفي');
                                    }
                                    var lbl = toggleBtn.querySelector('.tst-btn-lbl');
                                    if (lbl) lbl.textContent = isActive ? 'إخفاء' : 'إظهار';
                                    SKSwal.toast(res.msg, 'ok');
                                })
                                .catch(function(err){ SKSwal.toast(err.message || 'خطأ', 'err'); })
                                .finally(function(){ toggleBtn.classList.remove('loading'); });
                        });
                    }

                    if (delBtn) {
                        delBtn.addEventListener('click', function(){
                            SKSwal.danger({
                                title: 'حذف الرأي',
                                text: 'سيتم حذف هذا الرأي نهائياً. هل أنت متأكد؟',
                                confirmButtonText: 'نعم، احذف'
                            }).then(function(r){
                                if (!r.isConfirmed) return;
                                delBtn.classList.add('loading');
                                SKAjax('<?= SITE_URL ?>/admin/testimonials_delete.php', { id: id })
                                    .then(function(res){
                                        if (!res.ok) throw new Error(res.msg);
                                        card.style.transition = 'opacity .3s, transform .3s';
                                        card.style.opacity = '0';
                                        card.style.transform = 'scale(.9)';
                                        setTimeout(function(){ card.remove(); }, 300);
                                        SKSwal.toast(res.msg, 'ok');
                                    })
                                    .catch(function(err){ SKSwal.toast(err.message || 'خطأ', 'err'); delBtn.classList.remove('loading'); });
                            });
                        });
                    }
                });
            })();
            </script>
        <?php endif; ?>
    <?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>