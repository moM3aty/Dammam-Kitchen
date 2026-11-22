<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$currentPage = 'kitchen';
$pageTitle = 'أنواع المطابخ';

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
            $pdo->prepare("DELETE FROM kitchen_types WHERE id = ?")->execute([$id]);
            flash('ok', 'تم حذف النوع بنجاح');
        }
        redirect(SITE_URL . '/admin/kitchen-types.php');
    }

    if ($do === 'save') {
        $id     = (int)($_POST['id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $sort   = (int)($_POST['sort_order'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if (mb_strlen($name) < 2) $errors[] = 'الاسم مطلوب (حرفان على الأقل)';

        if (empty($errors)) {
            if ($id > 0) {
                $pdo->prepare("UPDATE kitchen_types SET name=?, sort_order=?, is_active=? WHERE id=?")
                    ->execute([$name, $sort, $active, $id]);
                flash('ok', 'تم تحديث النوع');
            } else {
                $pdo->prepare("INSERT INTO kitchen_types (name, sort_order, is_active) VALUES (?,?,?)")
                    ->execute([$name, $sort, $active]);
                flash('ok', 'تمت إضافة النوع');
            }
            redirect(SITE_URL . '/admin/kitchen-types.php');
        }
    }
}

/* ═══════════ EDIT LOAD ═══════════ */
$editItem = null;
if ($action === 'edit' && $editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM kitchen_types WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
    if (!$editItem) { flash('err', 'النوع غير موجود'); redirect(SITE_URL . '/admin/kitchen-types.php'); }
}
$showForm = ($action === 'new' || ($action === 'edit' && $editItem));

/* ═══════════ LIST DATA ═══════════ */
$filter = $_GET['f'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($filter === 'active')   $where[] = "is_active = 1";
if ($filter === 'inactive') $where[] = "is_active = 0";
if ($search !== '') {
    $where[] = "name LIKE ?"; // بحث بالاسم بس
    $like = "%$search%";
    $params[] = $like;
}
$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("SELECT * FROM kitchen_types $whereSQL ORDER BY sort_order ASC, id ASC");
$stmt->execute($params);
$items = $stmt->fetchAll();

/* Counts */
$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) AS actives,
        SUM(CASE WHEN is_active=0 THEN 1 ELSE 0 END) AS inactives
    FROM kitchen_types
")->fetch();

$totalCount    = (int)$stats['total'];
$activeCount   = (int)$stats['actives'];
$inactiveCount = (int)$stats['inactives'];

$flashes = get_flashes();
include __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= SITE_URL ?>/admin/assets/css/kitchen-types.css">

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
<div class="kt-page-head">
    <div class="kt-page-head-txt">
        <span class="kt-eyebrow">إدارة المحتوى</span>
        <h1><?= $showForm ? ($editItem ? 'تعديل نوع' : 'إضافة نوع جديد') : 'أنواع المطابخ' ?></h1>
        <p><?= $showForm ? 'املأ الحقول التالية ثم اضغط حفظ' : 'الأنواع المعروضة في قسم "أنواع المطابخ التي نفصّلها"' ?></p>
    </div>
    <div class="kt-page-head-actions">
        <?php if ($showForm): ?>
            <a href="<?= SITE_URL ?>/admin/kitchen-types.php" class="kt-btn kt-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                العودة للقائمة
            </a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/admin/kitchen-types.php?action=new" class="kt-btn kt-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                إضافة نوع جديد
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($showForm): ?>

    <!-- ═══════════ FORM ═══════════ -->
    <?php if ($errors): ?>
        <div class="kt-alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <div>
                <b>يرجى مراجعة التالي:</b>
                <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" class="kt-form">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="save">
        <input type="hidden" name="id" value="<?= $editItem ? (int)$editItem['id'] : 0 ?>">

        <div class="kt-form-grid">
            <div class="kt-form-main">
                <div class="kt-card">
                    <div class="kt-card-h">
                        <div class="kt-card-ic kt-card-ic-mint">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2l9 5v10l-9 5-9-5V7z"/>
                                <path d="M12 22V12M3 7l9 5 9-5"/>
                            </svg>
                        </div>
                        <div>
                            <h3>بيانات النوع</h3>
                            <p>الاسم والترتيب</p>
                        </div>
                    </div>
                    <div class="kt-card-b">
                        <div class="kt-field">
                            <label class="kt-label">اسم النوع <span class="kt-req">*</span></label>
                            <input type="text" name="name" class="kt-input" required maxlength="120"
                                   value="<?= e($editItem['name'] ?? '') ?>"
                                   placeholder="مثال: مطبخ أمريكي">
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-form-side">
                <div class="kt-card">
                    <div class="kt-card-h">
                        <div class="kt-card-ic kt-card-ic-cop">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                        </div>
                        <div>
                            <h3>الإعدادات</h3>
                            <p>الترتيب والحالة</p>
                        </div>
                    </div>
                    <div class="kt-card-b">
                        <div class="kt-field">
                            <label class="kt-label">ترتيب العرض</label>
                            <input type="number" name="sort_order" class="kt-input"
                                   value="<?= (int)($editItem['sort_order'] ?? 0) ?>">
                            <small class="kt-help">الأرقام الأصغر تظهر أولاً</small>
                        </div>

                        <div class="kt-switch-wrap">
                            <label class="kt-switch">
                                <input type="checkbox" name="is_active" value="1" <?= ($editItem['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <span class="kt-switch-track"><span class="kt-switch-thumb"></span></span>
                                <span class="kt-switch-lbl">
                                    <b>ظاهر في الموقع</b>
                                    <small>يظهر في قسم أنواع المطابخ</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="kt-card kt-card-actions">
                    <div class="kt-card-b">
                        <button type="submit" class="kt-btn kt-btn-primary kt-btn-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            <?= $editItem ? 'حفظ التعديلات' : 'إضافة النوع' ?>
                        </button>
                        <a href="<?= SITE_URL ?>/admin/kitchen-types.php" class="kt-btn kt-btn-ghost kt-btn-block">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

<?php else: ?>

    <!-- ═══════════ LIST ═══════════ -->
    <div class="kt-stats">
        <div class="kt-stat">
            <div class="kt-stat-ic kt-stat-mint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l9 5v10l-9 5-9-5V7z"/>
                    <path d="M12 22V12M3 7l9 5 9-5"/>
                </svg>
            </div>
            <div class="kt-stat-body">
                <span class="kt-stat-num"><?= $totalCount ?></span>
                <span class="kt-stat-lbl">إجمالي الأنواع</span>
            </div>
        </div>
        <div class="kt-stat">
            <div class="kt-stat-ic kt-stat-ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
            <div class="kt-stat-body">
                <span class="kt-stat-num"><?= $activeCount ?></span>
                <span class="kt-stat-lbl">ظاهرة</span>
            </div>
        </div>
        <div class="kt-stat">
            <div class="kt-stat-ic kt-stat-mute">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
            </div>
            <div class="kt-stat-body">
                <span class="kt-stat-num"><?= $inactiveCount ?></span>
                <span class="kt-stat-lbl">مخفية</span>
            </div>
        </div>
    </div>

    <?php if (!$items && !$search && $filter === 'all'): ?>
        <div class="kt-empty">
            <div class="kt-empty-ic">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l9 5v10l-9 5-9-5V7z"/>
                    <path d="M12 22V12M3 7l9 5 9-5"/>
                </svg>
            </div>
            <h3>لا توجد أنواع بعد</h3>
            <p>ابدأ بإضافة أول نوع لعرضه في الموقع</p>
            <a href="<?= SITE_URL ?>/admin/kitchen-types.php?action=new" class="kt-btn kt-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                إضافة نوع
            </a>
        </div>
    <?php else: ?>

        <!-- Toolbar -->
        <div class="kt-toolbar">
            <form method="get" class="kt-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="ابحث بالاسم...">
                <input type="hidden" name="f" value="<?= e($filter) ?>">
                <?php if ($search): ?>
                    <a href="?f=<?= e($filter) ?>" class="kt-search-clear" aria-label="مسح">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </a>
                <?php endif; ?>
            </form>

            <div class="kt-tabs">
                <a href="?f=all&q=<?= e($search) ?>" class="kt-tab <?= $filter === 'all' ? 'on' : '' ?>">
                    الكل <span class="kt-tab-count"><?= $totalCount ?></span>
                </a>
                <a href="?f=active&q=<?= e($search) ?>" class="kt-tab <?= $filter === 'active' ? 'on' : '' ?>">
                    ظاهرة <span class="kt-tab-count"><?= $activeCount ?></span>
                </a>
                <a href="?f=inactive&q=<?= e($search) ?>" class="kt-tab <?= $filter === 'inactive' ? 'on' : '' ?>">
                    مخفية <span class="kt-tab-count"><?= $inactiveCount ?></span>
                </a>
            </div>
        </div>

        <?php if (!$items): ?>
            <div class="kt-no-results">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <h4>لا توجد نتائج</h4>
                <p>جرّب كلمات بحث أخرى أو غيّر الفلتر</p>
            </div>
        <?php else: ?>
            <div class="kt-cards">
                <?php foreach ($items as $idx => $it): ?>
                    <article class="kt-item<?= !$it['is_active'] ? ' kt-item-off' : '' ?>"
                             data-id="<?= (int)$it['id'] ?>">

                        <div class="kt-item-top">
                            <div class="kt-item-icon">🍽️</div>
                            <div class="kt-item-num">#<?= str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) ?></div>
                        </div>

                        <h3 class="kt-item-title"><?= e($it['name']) ?></h3>

                        <div class="kt-item-meta">
                            <span class="kt-tag <?= $it['is_active'] ? 'kt-tag-ok' : 'kt-tag-mute' ?>">
                                <span class="kt-tag-dot"></span>
                                <?= $it['is_active'] ? 'ظاهرة' : 'مخفية' ?>
                            </span>
                            <span class="kt-tag kt-tag-order">ترتيب: <?= (int)$it['sort_order'] ?></span>
                        </div>

                        <div class="kt-item-actions">
                            <button type="button" class="kt-icon-btn kt-icon-btn-toggle" data-action="toggle" title="تغيير الحالة">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <span class="kt-btn-lbl"><?= $it['is_active'] ? 'إخفاء' : 'إظهار' ?></span>
                            </button>

                            <a href="<?= SITE_URL ?>/admin/kitchen-types.php?action=edit&id=<?= (int)$it['id'] ?>" class="kt-icon-btn kt-icon-btn-edit" title="تعديل">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>

                            <button type="button" class="kt-icon-btn kt-icon-btn-del" data-action="delete" title="حذف">
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
                'use strict';
                var CSRF = '<?= csrf_token() ?>';
                var BASE = '<?= SITE_URL ?>';

                function apiCall(endpoint, data) {
                    var body = new FormData();
                    for (var k in data) if (Object.prototype.hasOwnProperty.call(data, k)) body.append(k, data[k]);
                    body.append('csrf', CSRF);
                    return fetch(BASE + '/admin/' + endpoint, {
                        method: 'POST', body: body, credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(function(r){
                        return r.text().then(function(text){
                            var json;
                            try { json = JSON.parse(text); }
                            catch(e) { throw new Error('استجابة غير صالحة'); }
                            if (!r.ok) throw new Error(json.msg || ('HTTP ' + r.status));
                            return json;
                        });
                    });
                }

                function notify(msg, type) {
                    if (window.SKSwal && typeof window.SKSwal.toast === 'function') {
                        try { window.SKSwal.toast(msg, type || 'ok'); return; } catch(e) {}
                    }
                    var t = document.createElement('div');
                    t.textContent = msg;
                    t.style.cssText = 'position:fixed;top:24px;left:50%;transform:translateX(-50%);padding:12px 22px;border-radius:10px;color:#fff;font:600 14px/1.4 sans-serif;z-index:99999;background:' + (type === 'err' ? '#ef4444' : '#10b981') + ';direction:rtl';
                    document.body.appendChild(t);
                    setTimeout(function(){ t.remove(); }, 3000);
                }

                function confirmAction(opts) {
                    if (window.SKSwal && typeof window.SKSwal.danger === 'function') return window.SKSwal.danger(opts);
                    return Promise.resolve({ isConfirmed: window.confirm((opts.title || 'تأكيد') + '\n\n' + (opts.text || '')) });
                }

                document.querySelectorAll('.kt-item').forEach(function(card){
                    var id = card.dataset.id;
                    var toggleBtn = card.querySelector('[data-action="toggle"]');
                    var delBtn = card.querySelector('[data-action="delete"]');

                    if (toggleBtn) {
                        toggleBtn.addEventListener('click', function(e){
                            e.preventDefault();
                            if (toggleBtn.disabled) return;
                            toggleBtn.disabled = true;
                            toggleBtn.classList.add('loading');
                            apiCall('kitchen_types_toggle.php', { id: id })
                                .then(function(res){
                                    if (!res || res.ok !== true) throw new Error((res && res.msg) || 'فشلت');
                                    var isActive = Number(res.is_active) === 1;
                                    card.classList.toggle('kt-item-off', !isActive);
                                    var tag = card.querySelector('.kt-tag');
                                    if (tag) {
                                        tag.classList.toggle('kt-tag-ok', isActive);
                                        tag.classList.toggle('kt-tag-mute', !isActive);
                                        tag.innerHTML = '<span class="kt-tag-dot"></span> ' + (isActive ? 'ظاهرة' : 'مخفية');
                                    }
                                    var lbl = toggleBtn.querySelector('.kt-btn-lbl');
                                    if (lbl) lbl.textContent = isActive ? 'إخفاء' : 'إظهار';
                                    notify(res.msg, 'ok');
                                })
                                .catch(function(err){ notify(err.message || 'خطأ', 'err'); })
                                .finally(function(){ toggleBtn.disabled = false; toggleBtn.classList.remove('loading'); });
                        });
                    }

                    if (delBtn) {
                        delBtn.addEventListener('click', function(e){
                            e.preventDefault();
                            if (delBtn.disabled) return;
                            confirmAction({ title: 'حذف النوع', text: 'سيتم حذف هذا النوع نهائياً. هل أنت متأكد؟', confirmButtonText: 'نعم، احذف' })
                                .then(function(r){
                                    if (!r.isConfirmed) return;
                                    delBtn.disabled = true;
                                    delBtn.classList.add('loading');
                                    apiCall('kitchen_types_delete.php', { id: id })
                                        .then(function(res){
                                            if (!res || res.ok !== true) throw new Error((res && res.msg) || 'فشل');
                                            card.style.transition = 'opacity .3s, transform .3s';
                                            card.style.opacity = '0';
                                            card.style.transform = 'scale(.9)';
                                            setTimeout(function(){ card.remove(); }, 300);
                                            notify(res.msg, 'ok');
                                        })
                                        .catch(function(err){ notify(err.message || 'خطأ', 'err'); delBtn.disabled = false; delBtn.classList.remove('loading'); });
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