<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$currentPage = 'steps';
$pageTitle = 'خطوات العمل';

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
            $pdo->prepare("DELETE FROM steps WHERE id = ?")->execute([$id]);
            flash('ok', 'تم حذف الخطوة بنجاح');
        }
        redirect(SITE_URL . '/admin/steps.php');
    }

    if ($do === 'save') {
        $id     = (int)($_POST['id'] ?? 0);
        $num    = (int)($_POST['step_number'] ?? 0);
        $title  = trim($_POST['title'] ?? '');
        $desc   = trim($_POST['description'] ?? '');
        $sort   = (int)($_POST['sort_order'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if (mb_strlen($title) < 2) $errors[] = 'العنوان مطلوب (حرفان على الأقل)';
        if ($num <= 0) $errors[] = 'رقم الخطوة مطلوب (رقم موجب)';

        if (empty($errors)) {
            if ($id > 0) {
                $pdo->prepare("UPDATE steps SET step_number=?, title=?, description=?, sort_order=?, is_active=? WHERE id=?")
                    ->execute([$num, $title, $desc, $sort, $active, $id]);
                flash('ok', 'تم تحديث الخطوة');
            } else {
                $pdo->prepare("INSERT INTO steps (step_number, title, description, sort_order, is_active) VALUES (?,?,?,?,?)")
                    ->execute([$num, $title, $desc, $sort, $active]);
                flash('ok', 'تمت إضافة الخطوة');
            }
            redirect(SITE_URL . '/admin/steps.php');
        }
    }
}

/* ═══════════ EDIT LOAD ═══════════ */
$editItem = null;
if ($action === 'edit' && $editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM steps WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
    if (!$editItem) { flash('err', 'الخطوة غير موجودة'); redirect(SITE_URL . '/admin/steps.php'); }
}
$showForm = ($action === 'new' || ($action === 'edit' && $editItem));

/* الخطوة التالية */
$nextNum = (int)$pdo->query("SELECT COALESCE(MAX(step_number), 0) + 1 FROM steps")->fetchColumn();

/* ═══════════ LIST DATA ═══════════ */
$filter = $_GET['f'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($filter === 'active')   $where[] = "is_active = 1";
if ($filter === 'inactive') $where[] = "is_active = 0";
if ($search !== '') {
    $where[] = "(title LIKE ? OR description LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
}
$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("SELECT * FROM steps $whereSQL ORDER BY sort_order ASC, step_number ASC, id ASC");
$stmt->execute($params);
$items = $stmt->fetchAll();

/* Counts */
$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) AS actives,
        SUM(CASE WHEN is_active=0 THEN 1 ELSE 0 END) AS inactives
    FROM steps
")->fetch();

$totalCount    = (int)$stats['total'];
$activeCount   = (int)$stats['actives'];
$inactiveCount = (int)$stats['inactives'];

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
<div class="stp-page-head">
    <div class="stp-page-head-txt">
        <span class="stp-eyebrow">إدارة المحتوى</span>
        <h1><?= $showForm ? ($editItem ? 'تعديل خطوة' : 'إضافة خطوة جديدة') : 'خطوات العمل' ?></h1>
        <p><?= $showForm ? 'املأ الحقول التالية ثم اضغط حفظ' : 'الخطوات المعروضة في قسم "كيف نعمل"' ?></p>
    </div>
    <div class="stp-page-head-actions">
        <?php if ($showForm): ?>
            <a href="<?= SITE_URL ?>/admin/steps.php" class="stp-btn stp-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                العودة للقائمة
            </a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/admin/steps.php?action=new" class="stp-btn stp-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                إضافة خطوة جديدة
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($showForm): ?>

    <!-- ═══════════ FORM ═══════════ -->
    <?php if ($errors): ?>
        <div class="stp-alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <div>
                <b>يرجى مراجعة التالي:</b>
                <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" class="stp-form">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="save">
        <input type="hidden" name="id" value="<?= $editItem ? (int)$editItem['id'] : 0 ?>">

        <div class="stp-form-grid">
            <div class="stp-form-main">
                <div class="stp-card">
                    <div class="stp-card-h">
                        <div class="stp-card-ic stp-card-ic-mint">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h6v6H4zM14 14h6v6h-6zM4 14h6M14 4h6"/>
                            </svg>
                        </div>
                        <div>
                            <h3>محتوى الخطوة</h3>
                            <p>العنوان والوصف</p>
                        </div>
                    </div>
                    <div class="stp-card-b">
                        <div class="stp-field">
                            <label class="stp-label">عنوان الخطوة <span class="stp-req">*</span></label>
                            <input type="text" name="title" class="stp-input" required maxlength="255"
                                   value="<?= e($editItem['title'] ?? '') ?>"
                                   placeholder="مثال: تواصل معنا">
                        </div>

                        <div class="stp-field">
                            <label class="stp-label">وصف الخطوة</label>
                            <textarea name="description" class="stp-input stp-textarea" rows="4"
                                      placeholder="شرح مختصر لما يحدث في هذه الخطوة..."><?= e($editItem['description'] ?? '') ?></textarea>
                            <small class="stp-help"><span id="stpDescCount">0</span> حرف</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stp-form-side">
                <div class="stp-card">
                    <div class="stp-card-h">
                        <div class="stp-card-ic stp-card-ic-violet">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                        </div>
                        <div>
                            <h3>الإعدادات</h3>
                            <p>الرقم والترتيب</p>
                        </div>
                    </div>
                    <div class="stp-card-b">
                        <div class="stp-field">
                            <label class="stp-label">رقم الخطوة <span class="stp-req">*</span></label>
                            <input type="number" name="step_number" class="stp-input" min="1" required
                                   value="<?= (int)($editItem['step_number'] ?? $nextNum) ?>">
                            <small class="stp-help">يظهر في الدائرة (1، 2، 3...)</small>
                        </div>

                        <div class="stp-field">
                            <label class="stp-label">ترتيب العرض</label>
                            <input type="number" name="sort_order" class="stp-input"
                                   value="<?= (int)($editItem['sort_order'] ?? 0) ?>">
                            <small class="stp-help">الأرقام الأصغر تظهر أولاً</small>
                        </div>

                        <div class="stp-switch-wrap">
                            <label class="stp-switch">
                                <input type="checkbox" name="is_active" value="1" <?= ($editItem['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <span class="stp-switch-track"><span class="stp-switch-thumb"></span></span>
                                <span class="stp-switch-lbl">
                                    <b>ظاهرة في الموقع</b>
                                    <small>تظهر في قسم "كيف نعمل"</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="stp-card stp-card-actions">
                    <div class="stp-card-b">
                        <button type="submit" class="stp-btn stp-btn-primary stp-btn-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            <?= $editItem ? 'حفظ التعديلات' : 'إضافة الخطوة' ?>
                        </button>
                        <a href="<?= SITE_URL ?>/admin/steps.php" class="stp-btn stp-btn-ghost stp-btn-block">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
    (function(){
        var textarea = document.querySelector('textarea[name="description"]');
        var counter = document.getElementById('stpDescCount');
        if (textarea && counter) {
            counter.textContent = textarea.value.length;
            textarea.addEventListener('input', function(){
                counter.textContent = this.value.length;
            });
        }
    })();
    </script>

<?php else: ?>

    <!-- ═══════════ LIST ═══════════ -->
    <div class="stp-stats">
        <div class="stp-stat">
            <div class="stp-stat-ic stp-stat-mint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h6v6H4zM14 14h6v6h-6zM4 14h6M14 4h6"/>
                </svg>
            </div>
            <div class="stp-stat-body">
                <span class="stp-stat-num"><?= $totalCount ?></span>
                <span class="stp-stat-lbl">إجمالي الخطوات</span>
            </div>
        </div>
        <div class="stp-stat">
            <div class="stp-stat-ic stp-stat-ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
            <div class="stp-stat-body">
                <span class="stp-stat-num"><?= $activeCount ?></span>
                <span class="stp-stat-lbl">ظاهرة</span>
            </div>
        </div>
        <div class="stp-stat">
            <div class="stp-stat-ic stp-stat-mute">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
            </div>
            <div class="stp-stat-body">
                <span class="stp-stat-num"><?= $inactiveCount ?></span>
                <span class="stp-stat-lbl">مخفية</span>
            </div>
        </div>
    </div>

    <?php if (!$items && !$search && $filter === 'all'): ?>
        <div class="stp-empty">
            <div class="stp-empty-ic">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h6v6H4zM14 14h6v6h-6zM4 14h6M14 4h6"/>
                </svg>
            </div>
            <h3>لا توجد خطوات بعد</h3>
            <p>ابدأ بإضافة أول خطوة لعرضها في الموقع</p>
            <a href="<?= SITE_URL ?>/admin/steps.php?action=new" class="stp-btn stp-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                إضافة خطوة
            </a>
        </div>
    <?php else: ?>

        <!-- Toolbar -->
        <div class="stp-toolbar">
            <form method="get" class="stp-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="ابحث في العنوان أو الوصف...">
                <input type="hidden" name="f" value="<?= e($filter) ?>">
                <?php if ($search): ?>
                    <a href="?f=<?= e($filter) ?>" class="stp-search-clear" aria-label="مسح">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </a>
                <?php endif; ?>
            </form>

            <div class="stp-tabs">
                <a href="?f=all&q=<?= e($search) ?>" class="stp-tab <?= $filter === 'all' ? 'on' : '' ?>">
                    الكل <span class="stp-tab-count"><?= $totalCount ?></span>
                </a>
                <a href="?f=active&q=<?= e($search) ?>" class="stp-tab <?= $filter === 'active' ? 'on' : '' ?>">
                    ظاهرة <span class="stp-tab-count"><?= $activeCount ?></span>
                </a>
                <a href="?f=inactive&q=<?= e($search) ?>" class="stp-tab <?= $filter === 'inactive' ? 'on' : '' ?>">
                    مخفية <span class="stp-tab-count"><?= $inactiveCount ?></span>
                </a>
            </div>
        </div>

        <?php if (!$items): ?>
            <div class="stp-no-results">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <h4>لا توجد نتائج</h4>
                <p>جرّب كلمات بحث أخرى أو غيّر الفلتر</p>
            </div>
        <?php else: ?>
            <div class="stp-timeline">
                <?php foreach ($items as $idx => $it): ?>
                    <article class="stp-item<?= !$it['is_active'] ? ' stp-item-off' : '' ?>"
                             data-id="<?= (int)$it['id'] ?>">

                        <div class="stp-item-marker">
                            <div class="stp-item-num">
                                <span class="stp-num-inner"><?= (int)$it['step_number'] ?></span>
                            </div>
                            <?php if ($idx < count($items) - 1): ?>
                                <div class="stp-item-line"></div>
                            <?php endif; ?>
                        </div>

                        <div class="stp-item-card">
                            <div class="stp-item-head">
                                <h3 class="stp-item-title"><?= e($it['title']) ?></h3>
                                <div class="stp-item-order">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                        <line x1="4" y1="9" x2="20" y2="9"/>
                                        <line x1="4" y1="15" x2="20" y2="15"/>
                                    </svg>
                                    ترتيب: <?= (int)$it['sort_order'] ?>
                                </div>
                            </div>

                            <?php if ($it['description']): ?>
                                <p class="stp-item-desc"><?= e($it['description']) ?></p>
                            <?php endif; ?>

                            <div class="stp-item-footer">
                                <div class="stp-item-meta">
                                    <span class="stp-tag <?= $it['is_active'] ? 'stp-tag-ok' : 'stp-tag-mute' ?>">
                                        <span class="stp-tag-dot"></span>
                                        <?= $it['is_active'] ? 'ظاهرة' : 'مخفية' ?>
                                    </span>
                                </div>

                                <div class="stp-item-actions">
                                    <button type="button" class="stp-icon-btn stp-icon-btn-toggle" data-action="toggle" title="تغيير الحالة">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        <span class="stp-btn-lbl"><?= $it['is_active'] ? 'إخفاء' : 'إظهار' ?></span>
                                    </button>

                                    <a href="<?= SITE_URL ?>/admin/steps.php?action=edit&id=<?= (int)$it['id'] ?>" class="stp-icon-btn stp-icon-btn-edit" title="تعديل">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>

                                    <button type="button" class="stp-icon-btn stp-icon-btn-del" data-action="delete" title="حذف">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
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

                document.querySelectorAll('.stp-item').forEach(function(card){
                    var id = card.dataset.id;
                    var toggleBtn = card.querySelector('[data-action="toggle"]');
                    var delBtn = card.querySelector('[data-action="delete"]');

                    if (toggleBtn) {
                        toggleBtn.addEventListener('click', function(e){
                            e.preventDefault();
                            if (toggleBtn.disabled) return;
                            toggleBtn.disabled = true;
                            toggleBtn.classList.add('loading');
                            apiCall('steps_toggle.php', { id: id })
                                .then(function(res){
                                    if (!res || res.ok !== true) throw new Error((res && res.msg) || 'فشلت');
                                    var isActive = Number(res.is_active) === 1;
                                    card.classList.toggle('stp-item-off', !isActive);
                                    var tag = card.querySelector('.stp-tag');
                                    if (tag) {
                                        tag.classList.toggle('stp-tag-ok', isActive);
                                        tag.classList.toggle('stp-tag-mute', !isActive);
                                        tag.innerHTML = '<span class="stp-tag-dot"></span> ' + (isActive ? 'ظاهرة' : 'مخفية');
                                    }
                                    var lbl = toggleBtn.querySelector('.stp-btn-lbl');
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
                            confirmAction({ title: 'حذف الخطوة', text: 'سيتم حذف هذه الخطوة نهائياً. هل أنت متأكد؟', confirmButtonText: 'نعم، احذف' })
                                .then(function(r){
                                    if (!r.isConfirmed) return;
                                    delBtn.disabled = true;
                                    delBtn.classList.add('loading');
                                    apiCall('steps_delete.php', { id: id })
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