<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$currentPage = 'materials';
$pageTitle = 'الخامات';

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
            $pdo->prepare("DELETE FROM materials WHERE id = ?")->execute([$id]);
            flash('ok', 'تم حذف الخامة بنجاح');
        }
        redirect(SITE_URL . '/admin/materials.php');
    }

    if ($do === 'save') {
        $id     = (int)($_POST['id'] ?? 0);
        $icon   = trim($_POST['icon'] ?? '');
        $name   = trim($_POST['name'] ?? '');
        $desc   = trim($_POST['description'] ?? '');
        $sort   = (int)($_POST['sort_order'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if (mb_strlen($name) < 2) $errors[] = 'الاسم مطلوب (حرفان على الأقل)';
        if ($icon === '') $icon = '🧱';

        if (empty($errors)) {
            if ($id > 0) {
                $pdo->prepare("UPDATE materials SET icon=?, name=?, description=?, sort_order=?, is_active=? WHERE id=?")
                    ->execute([$icon, $name, $desc, $sort, $active, $id]);
                flash('ok', 'تم تحديث الخامة');
            } else {
                $pdo->prepare("INSERT INTO materials (icon, name, description, sort_order, is_active) VALUES (?,?,?,?,?)")
                    ->execute([$icon, $name, $desc, $sort, $active]);
                flash('ok', 'تمت إضافة الخامة');
            }
            redirect(SITE_URL . '/admin/materials.php');
        }
    }
}

/* ═══════════ EDIT LOAD ═══════════ */
$editItem = null;
if ($action === 'edit' && $editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM materials WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
    if (!$editItem) { flash('err', 'الخامة غير موجودة'); redirect(SITE_URL . '/admin/materials.php'); }
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
    $where[] = "(name LIKE ? OR description LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
}
$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("SELECT * FROM materials $whereSQL ORDER BY sort_order ASC, id ASC");
$stmt->execute($params);
$items = $stmt->fetchAll();

/* Counts */
$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) AS actives,
        SUM(CASE WHEN is_active=0 THEN 1 ELSE 0 END) AS inactives
    FROM materials
")->fetch();

$totalCount    = (int)$stats['total'];
$activeCount   = (int)$stats['actives'];
$inactiveCount = (int)$stats['inactives'];

/* مقترحات أيقونات */
$suggestedIcons = ['🪟','🪵','🌳','🪨','💎','🔩','🧱','🛠️','⚙️','🏠','🎨','✨','🔧','📐','🪑','🚪','🖼️','🏗️'];

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
<div class="mat-page-head">
    <div class="mat-page-head-txt">
        <span class="mat-eyebrow">إدارة المحتوى</span>
        <h1><?= $showForm ? ($editItem ? 'تعديل خامة' : 'إضافة خامة جديدة') : 'الخامات' ?></h1>
        <p><?= $showForm ? 'املأ الحقول التالية ثم اضغط حفظ' : 'الخامات المعروضة في قسم "الخامات التي نتعامل معها"' ?></p>
    </div>
    <div class="mat-page-head-actions">
        <?php if ($showForm): ?>
            <a href="<?= SITE_URL ?>/admin/materials.php" class="mat-btn mat-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                العودة للقائمة
            </a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/admin/materials.php?action=new" class="mat-btn mat-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                إضافة خامة جديدة
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($showForm): ?>

    <!-- ═══════════ FORM ═══════════ -->
    <?php if ($errors): ?>
        <div class="mat-alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <div>
                <b>يرجى مراجعة التالي:</b>
                <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" class="mat-form">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="save">
        <input type="hidden" name="id" value="<?= $editItem ? (int)$editItem['id'] : 0 ?>">

        <div class="mat-form-grid">
            <div class="mat-form-main">
                <div class="mat-card">
                    <div class="mat-card-h">
                        <div class="mat-card-ic mat-card-ic-mint">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2l9 5v10l-9 5-9-5V7z"/>
                                <path d="M12 22V12M3 7l9 5 9-5"/>
                            </svg>
                        </div>
                        <div>
                            <h3>محتوى الخامة</h3>
                            <p>الاسم والوصف</p>
                        </div>
                    </div>
                    <div class="mat-card-b">
                        <div class="mat-field">
                            <label class="mat-label">اسم الخامة <span class="mat-req">*</span></label>
                            <input type="text" name="name" class="mat-input" required maxlength="120"
                                   value="<?= e($editItem['name'] ?? '') ?>"
                                   placeholder="مثال: الألمنيوم">
                        </div>

                        <div class="mat-field">
                            <label class="mat-label">وصف الخامة</label>
                            <textarea name="description" class="mat-input mat-textarea" rows="4"
                                      placeholder="شرح مختصر للخامة واستخداماتها..."><?= e($editItem['description'] ?? '') ?></textarea>
                            <small class="mat-help"><span id="matDescCount">0</span> حرف</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mat-form-side">
                <div class="mat-card">
                    <div class="mat-card-h">
                        <div class="mat-card-ic mat-card-ic-cop">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                        </div>
                        <div>
                            <h3>الإعدادات</h3>
                            <p>الأيقونة والترتيب</p>
                        </div>
                    </div>
                    <div class="mat-card-b">

                        <div class="mat-field">
                            <label class="mat-label">الأيقونة</label>
                            <div class="mat-icon-picker">
                                <div class="mat-icon-preview" id="matIconPreview"><?= e($editItem['icon'] ?? '🧱') ?></div>
                                <input type="text" name="icon" id="matIconInput" class="mat-input"
                                       maxlength="10"
                                       value="<?= e($editItem['icon'] ?? '🧱') ?>"
                                       placeholder="🧱">
                            </div>
                            <small class="mat-help">اختر Emoji أو اكتب واحداً</small>
                            <div class="mat-icon-suggestions">
                                <?php foreach ($suggestedIcons as $ic): ?>
                                    <button type="button" class="mat-icon-sugg" data-icon="<?= e($ic) ?>"><?= e($ic) ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mat-field">
                            <label class="mat-label">ترتيب العرض</label>
                            <input type="number" name="sort_order" class="mat-input"
                                   value="<?= (int)($editItem['sort_order'] ?? 0) ?>">
                            <small class="mat-help">الأرقام الأصغر تظهر أولاً</small>
                        </div>

                        <div class="mat-switch-wrap">
                            <label class="mat-switch">
                                <input type="checkbox" name="is_active" value="1" <?= ($editItem['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <span class="mat-switch-track"><span class="mat-switch-thumb"></span></span>
                                <span class="mat-switch-lbl">
                                    <b>ظاهرة في الموقع</b>
                                    <small>تظهر في قسم الخامات</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mat-card mat-card-actions">
                    <div class="mat-card-b">
                        <button type="submit" class="mat-btn mat-btn-primary mat-btn-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            <?= $editItem ? 'حفظ التعديلات' : 'إضافة الخامة' ?>
                        </button>
                        <a href="<?= SITE_URL ?>/admin/materials.php" class="mat-btn mat-btn-ghost mat-btn-block">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
    (function(){
        var iconIn = document.getElementById('matIconInput');
        var iconPrev = document.getElementById('matIconPreview');
        var suggs = document.querySelectorAll('.mat-icon-sugg');

        function updateIcon(v) { iconPrev.textContent = v || '🧱'; }

        if (iconIn) iconIn.addEventListener('input', function(){ updateIcon(this.value); });

        suggs.forEach(function(btn){
            btn.addEventListener('click', function(){
                iconIn.value = this.dataset.icon;
                updateIcon(this.dataset.icon);
            });
        });

        var textarea = document.querySelector('textarea[name="description"]');
        var counter = document.getElementById('matDescCount');
        if (textarea && counter) {
            counter.textContent = textarea.value.length;
            textarea.addEventListener('input', function(){ counter.textContent = this.value.length; });
        }
    })();
    </script>

<?php else: ?>

    <!-- ═══════════ LIST ═══════════ -->
    <div class="mat-stats">
        <div class="mat-stat">
            <div class="mat-stat-ic mat-stat-mint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l9 5v10l-9 5-9-5V7z"/>
                    <path d="M12 22V12M3 7l9 5 9-5"/>
                </svg>
            </div>
            <div class="mat-stat-body">
                <span class="mat-stat-num"><?= $totalCount ?></span>
                <span class="mat-stat-lbl">إجمالي الخامات</span>
            </div>
        </div>
        <div class="mat-stat">
            <div class="mat-stat-ic mat-stat-ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
            <div class="mat-stat-body">
                <span class="mat-stat-num"><?= $activeCount ?></span>
                <span class="mat-stat-lbl">ظاهرة</span>
            </div>
        </div>
        <div class="mat-stat">
            <div class="mat-stat-ic mat-stat-mute">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
            </div>
            <div class="mat-stat-body">
                <span class="mat-stat-num"><?= $inactiveCount ?></span>
                <span class="mat-stat-lbl">مخفية</span>
            </div>
        </div>
    </div>

    <?php if (!$items && !$search && $filter === 'all'): ?>
        <div class="mat-empty">
            <div class="mat-empty-ic">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l9 5v10l-9 5-9-5V7z"/>
                    <path d="M12 22V12M3 7l9 5 9-5"/>
                </svg>
            </div>
            <h3>لا توجد خامات بعد</h3>
            <p>ابدأ بإضافة أول خامة لعرضها في الموقع</p>
            <a href="<?= SITE_URL ?>/admin/materials.php?action=new" class="mat-btn mat-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                إضافة خامة
            </a>
        </div>
    <?php else: ?>

        <!-- Toolbar -->
        <div class="mat-toolbar">
            <form method="get" class="mat-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="ابحث في الاسم أو الوصف...">
                <input type="hidden" name="f" value="<?= e($filter) ?>">
                <?php if ($search): ?>
                    <a href="?f=<?= e($filter) ?>" class="mat-search-clear" aria-label="مسح">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </a>
                <?php endif; ?>
            </form>

            <div class="mat-tabs">
                <a href="?f=all&q=<?= e($search) ?>" class="mat-tab <?= $filter === 'all' ? 'on' : '' ?>">
                    الكل <span class="mat-tab-count"><?= $totalCount ?></span>
                </a>
                <a href="?f=active&q=<?= e($search) ?>" class="mat-tab <?= $filter === 'active' ? 'on' : '' ?>">
                    ظاهرة <span class="mat-tab-count"><?= $activeCount ?></span>
                </a>
                <a href="?f=inactive&q=<?= e($search) ?>" class="mat-tab <?= $filter === 'inactive' ? 'on' : '' ?>">
                    مخفية <span class="mat-tab-count"><?= $inactiveCount ?></span>
                </a>
            </div>
        </div>

        <?php if (!$items): ?>
            <div class="mat-no-results">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <h4>لا توجد نتائج</h4>
                <p>جرّب كلمات بحث أخرى أو غيّر الفلتر</p>
            </div>
        <?php else: ?>
            <div class="mat-cards">
                <?php foreach ($items as $idx => $it): ?>
                    <article class="mat-item<?= !$it['is_active'] ? ' mat-item-off' : '' ?>"
                             data-id="<?= (int)$it['id'] ?>">

                        <div class="mat-item-top">
                            <div class="mat-item-icon"><?= e($it['icon']) ?></div>
                            <div class="mat-item-num">#<?= str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) ?></div>
                        </div>

                        <h3 class="mat-item-title"><?= e($it['name']) ?></h3>

                        <?php if ($it['description']): ?>
                            <p class="mat-item-desc"><?= e($it['description']) ?></p>
                        <?php endif; ?>

                        <div class="mat-item-meta">
                            <span class="mat-tag <?= $it['is_active'] ? 'mat-tag-ok' : 'mat-tag-mute' ?>">
                                <span class="mat-tag-dot"></span>
                                <?= $it['is_active'] ? 'ظاهرة' : 'مخفية' ?>
                            </span>
                            <span class="mat-tag mat-tag-order">ترتيب: <?= (int)$it['sort_order'] ?></span>
                        </div>

                        <div class="mat-item-actions">
                            <button type="button" class="mat-icon-btn mat-icon-btn-toggle" data-action="toggle" title="تغيير الحالة">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <span class="mat-btn-lbl"><?= $it['is_active'] ? 'إخفاء' : 'إظهار' ?></span>
                            </button>

                            <a href="<?= SITE_URL ?>/admin/materials.php?action=edit&id=<?= (int)$it['id'] ?>" class="mat-icon-btn mat-icon-btn-edit" title="تعديل">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>

                            <button type="button" class="mat-icon-btn mat-icon-btn-del" data-action="delete" title="حذف">
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

                document.querySelectorAll('.mat-item').forEach(function(card){
                    var id = card.dataset.id;
                    var toggleBtn = card.querySelector('[data-action="toggle"]');
                    var delBtn = card.querySelector('[data-action="delete"]');

                    if (toggleBtn) {
                        toggleBtn.addEventListener('click', function(e){
                            e.preventDefault();
                            if (toggleBtn.disabled) return;
                            toggleBtn.disabled = true;
                            toggleBtn.classList.add('loading');
                            apiCall('materials_toggle.php', { id: id })
                                .then(function(res){
                                    if (!res || res.ok !== true) throw new Error((res && res.msg) || 'فشلت');
                                    var isActive = Number(res.is_active) === 1;
                                    card.classList.toggle('mat-item-off', !isActive);
                                    var tag = card.querySelector('.mat-tag');
                                    if (tag) {
                                        tag.classList.toggle('mat-tag-ok', isActive);
                                        tag.classList.toggle('mat-tag-mute', !isActive);
                                        tag.innerHTML = '<span class="mat-tag-dot"></span> ' + (isActive ? 'ظاهرة' : 'مخفية');
                                    }
                                    var lbl = toggleBtn.querySelector('.mat-btn-lbl');
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
                            confirmAction({ title: 'حذف الخامة', text: 'سيتم حذف هذه الخامة نهائياً. هل أنت متأكد؟', confirmButtonText: 'نعم، احذف' })
                                .then(function(r){
                                    if (!r.isConfirmed) return;
                                    delBtn.disabled = true;
                                    delBtn.classList.add('loading');
                                    apiCall('materials_delete.php', { id: id })
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