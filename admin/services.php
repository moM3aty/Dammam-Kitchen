<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$currentPage = 'services';
$pageTitle = 'الخدمات';

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
            $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
            flash('ok', 'تم حذف الخدمة بنجاح');
        }
        redirect(SITE_URL . '/admin/services.php');
    }

    if ($do === 'save') {
        $id        = (int)($_POST['id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $details   = trim($_POST['details'] ?? '');
        $icon      = trim($_POST['icon'] ?? '');
        $btnText   = trim($_POST['button_text'] ?? '');
        $keywords  = trim($_POST['keywords'] ?? '');
        $sort      = (int)($_POST['sort_order'] ?? 0);
        $active    = isset($_POST['is_active']) ? 1 : 0;
        $featuresRaw = $_POST['features'] ?? '';
        $featLines = array_filter(array_map('trim', explode("\n", $featuresRaw)));
        $featuresJson = json_encode(array_values($featLines), JSON_UNESCAPED_UNICODE);

        if (mb_strlen($title) < 3) $errors[] = 'العنوان مطلوب (3 أحرف على الأقل)';

        if (empty($errors)) {
            $data = [$title, $desc, $details, $icon, $featuresJson, $keywords, $btnText, $sort, $active];
            if ($id > 0) {
                $data[] = $id;
                $pdo->prepare("UPDATE services SET title=?, description=?, details=?, icon=?, features=?, keywords=?, button_text=?, sort_order=?, is_active=? WHERE id=?")->execute($data);
                flash('ok', 'تم تحديث الخدمة بنجاح');
            } else {
                $pdo->prepare("INSERT INTO services (title, description, details, icon, features, keywords, button_text, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?)")->execute($data);
                flash('ok', 'تمت إضافة الخدمة بنجاح');
            }
            redirect(SITE_URL . '/admin/services.php');
        }
    }
}

$editItem = null;
if ($action === 'edit' && $editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
    if (!$editItem) { flash('err', 'الخدمة غير موجودة'); redirect(SITE_URL . '/admin/services.php'); }
}

$showForm = ($action === 'new' || ($action === 'edit' && $editItem));

$items = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC")->fetchAll();
$activeCount = 0;
foreach ($items as $it) { if ($it['is_active']) $activeCount++; }
$inactiveCount = count($items) - $activeCount;

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

<?php if (($_GET['flash_ok'] ?? '') === '1'): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        SKSwal.toast('تم حفظ الخدمة بنجاح', 'ok');
    });
    </script>
<?php endif; ?>

<!-- ═══════════ PAGE HEADER ═══════════ -->
<div class="svc-page-head">
    <div class="svc-page-head-txt">
        <span class="svc-eyebrow">إدارة المحتوى</span>
        <h1><?= $showForm ? ($editItem ? 'تعديل الخدمة' : 'إضافة خدمة جديدة') : 'إدارة الخدمات' ?></h1>
        <p><?= $showForm ? 'املأ الحقول التالية ثم اضغط حفظ' : 'تحكم كامل في الخدمات المعروضة في الصفحة الرئيسية' ?></p>
    </div>
    <div class="svc-page-head-actions">
        <?php if ($showForm): ?>
            <a href="<?= SITE_URL ?>/admin/services.php" class="svc-btn svc-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                العودة للقائمة
            </a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/admin/services.php?action=new" class="svc-btn svc-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/>
                </svg>
                إضافة خدمة جديدة
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($showForm): ?>

    <!-- ═══════════ FORM VIEW ═══════════ -->
    <?php if ($errors): ?>
        <div class="svc-alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
            </svg>
            <div>
                <b>يرجى مراجعة الحقول التالية:</b>
                <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" class="svc-form">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="save">
        <input type="hidden" name="id" value="<?= $editItem ? (int)$editItem['id'] : 0 ?>">

        <div class="svc-form-grid">
            <div class="svc-form-main">
                <div class="svc-card">
                    <div class="svc-card-h">
                        <div class="svc-card-ic svc-card-ic-mint">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 7h16M4 12h16M4 17h10"/>
                            </svg>
                        </div>
                        <div><h3>المعلومات الأساسية</h3><p>العنوان والوصف الرئيسي للخدمة</p></div>
                    </div>
                    <div class="svc-card-b">
                        <div class="svc-field">
                            <label class="svc-label">عنوان الخدمة <span class="svc-req">*</span></label>
                            <input type="text" name="title" class="svc-input" required maxlength="255"
                                   value="<?= e($editItem['title'] ?? '') ?>"
                                   placeholder="مثال: تفصيل مطابخ ألمنيوم في الدمام">
                            <small class="svc-help">سيظهر هذا العنوان في بطاقة الخدمة على الموقع</small>
                        </div>
                        <div class="svc-field">
                            <label class="svc-label">الوصف الرئيسي</label>
                            <textarea name="description" class="svc-input svc-textarea" rows="5"
                                      placeholder="الوصف الأساسي الذي يظهر في بطاقة الخدمة"><?= e($editItem['description'] ?? '') ?></textarea>
                        </div>
                        <div class="svc-field">
                            <label class="svc-label">وصف تفصيلي <small class="svc-opt">(اختياري)</small></label>
                            <textarea name="details" class="svc-input svc-textarea" rows="3"
                                      placeholder="فقرة إضافية تظهر بعد الوصف الرئيسي"><?= e($editItem['details'] ?? '') ?></textarea>
                            <small class="svc-help">إذا تركت فارغاً، لن يظهر هذا القسم</small>
                        </div>
                    </div>
                </div>

                <div class="svc-card">
                    <div class="svc-card-h">
                        <div class="svc-card-ic svc-card-ic-cop">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/>
                            </svg>
                        </div>
                        <div><h3>المميزات</h3><p>النقاط التي تظهر كنقاط تحقق</p></div>
                    </div>
                    <div class="svc-card-b">
                        <div class="svc-field">
                            <label class="svc-label">المميزات (كل ميزة في سطر)</label>
                            <textarea name="features" class="svc-input svc-textarea svc-textarea-code" rows="7"
                                      placeholder="تفصيل على مقاس المطبخ&#10;هيكل وواجهات ألمنيوم&#10;ألوان وتشطيبات متنوعة&#10;أدراج ومفصلات وملحقات"><?= e(implode("\n", json_or_empty($editItem['features'] ?? ''))) ?></textarea>
                            <div class="svc-tips">
                                <span class="svc-tip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                                    اكتب كل ميزة في سطر منفصل
                                </span>
                                <span class="svc-tip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 6L9 17l-5-5"/></svg>
                                    ستظهر كنقاط تحقق أخضر
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="svc-card">
                    <div class="svc-card-h">
                        <div class="svc-card-ic svc-card-ic-blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </div>
                        <div><h3>SEO والكلمات المفتاحية</h3><p>تحسين ظهور الخدمة في محركات البحث</p></div>
                    </div>
                    <div class="svc-card-b">
                        <div class="svc-field">
                            <label class="svc-label">الكلمات المفتاحية</label>
                            <input type="text" name="keywords" class="svc-input"
                                   value="<?= e($editItem['keywords'] ?? '') ?>"
                                   placeholder="مطابخ ألمنيوم الدمام, تفصيل مطابخ, مطابخ ألمنيوم مودرن">
                            <small class="svc-help">افصل بين الكلمات بفاصلة ( , )</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="svc-form-side">
                <div class="svc-card">
                    <div class="svc-card-h">
                        <div class="svc-card-ic svc-card-ic-violet">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                        </div>
                        <div><h3>الإعدادات</h3><p>الحالة والترتيب</p></div>
                    </div>
                    <div class="svc-card-b">
                        <div class="svc-field">
                            <label class="svc-label">الأيقونة (Emoji)</label>
                            <input type="text" name="icon" id="svcIcon" class="svc-input svc-input-icon" maxlength="10"
                                   value="<?= e($editItem['icon'] ?? '🛠️') ?>">
                            <div class="svc-icon-preview" id="svcIconPreview"><?= e($editItem['icon'] ?? '🛠️') ?></div>
                            <small class="svc-help">اختر Emoji مناسب مثل: 🪟 🪵 🪨 🛠️ 🏠 🎨</small>
                        </div>
                        <div class="svc-field">
                            <label class="svc-label">نص زر الطلب</label>
                            <input type="text" name="button_text" class="svc-input" maxlength="150"
                                   value="<?= e($editItem['button_text'] ?? '') ?>"
                                   placeholder="اطلب تفصيل مطبخ ألمنيوم">
                        </div>
                        <div class="svc-field">
                            <label class="svc-label">ترتيب العرض</label>
                            <input type="number" name="sort_order" class="svc-input"
                                   value="<?= (int)($editItem['sort_order'] ?? 0) ?>">
                            <small class="svc-help">الأرقام الأصغر تظهر أولاً</small>
                        </div>
                        <div class="svc-switch-wrap">
                            <label class="svc-switch">
                                <input type="checkbox" name="is_active" value="1" <?= ($editItem['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <span class="svc-switch-track"><span class="svc-switch-thumb"></span></span>
                                <span class="svc-switch-lbl">
                                    <b>نشطة</b>
                                    <small>تظهر في الموقع</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="svc-card svc-card-actions">
                    <div class="svc-card-b">
                        <button type="submit" class="svc-btn svc-btn-primary svc-btn-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            <?= $editItem ? 'حفظ التعديلات' : 'إضافة الخدمة' ?>
                        </button>
                        <a href="<?= SITE_URL ?>/admin/services.php" class="svc-btn svc-btn-ghost svc-btn-block">إلغاء والعودة</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
    (function(){
        var input = document.getElementById('svcIcon');
        var preview = document.getElementById('svcIconPreview');
        if (input && preview) {
            input.addEventListener('input', function(){
                preview.textContent = input.value || '🛠️';
            });
        }
    })();
    </script>

<?php else: ?>

    <!-- ═══════════ LIST VIEW ═══════════ -->
    <div class="svc-stats">
        <div class="svc-stat">
            <div class="svc-stat-ic svc-stat-ic-mint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <div class="svc-stat-body">
                <span class="svc-stat-num"><?= count($items) ?></span>
                <span class="svc-stat-lbl">إجمالي الخدمات</span>
            </div>
        </div>
        <div class="svc-stat">
            <div class="svc-stat-ic svc-stat-ic-ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>
            <div class="svc-stat-body">
                <span class="svc-stat-num" id="svcActiveCount"><?= $activeCount ?></span>
                <span class="svc-stat-lbl">خدمة نشطة</span>
            </div>
        </div>
        <div class="svc-stat">
            <div class="svc-stat-ic svc-stat-ic-mute">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><path d="M12 2v10"/>
                </svg>
            </div>
            <div class="svc-stat-body">
                <span class="svc-stat-num" id="svcInactiveCount"><?= $inactiveCount ?></span>
                <span class="svc-stat-lbl">معطّلة</span>
            </div>
        </div>
    </div>

    <?php if (!$items): ?>
        <div class="svc-empty">
            <div class="svc-empty-ic">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <h3>لا توجد خدمات بعد</h3>
            <p>ابدأ بإضافة أول خدمة لعرضها في الصفحة الرئيسية</p>
            <a href="<?= SITE_URL ?>/admin/services.php?action=new" class="svc-btn svc-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/>
                </svg>
                إضافة خدمة
            </a>
        </div>
    <?php else: ?>

        <!-- Toolbar: Search + Filter Tabs -->
        <div class="svc-toolbar">
            <div class="svc-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" id="svcSearch" placeholder="ابحث باسم الخدمة أو الوصف أو الكلمات المفتاحية...">
                <button type="button" class="svc-search-clear" id="svcSearchClear" aria-label="مسح">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="svc-filter-tabs" role="tablist">
                <button type="button" class="svc-tab on" data-filter="all" role="tab" aria-selected="true">
                    الكل
                    <span class="svc-tab-count"><?= count($items) ?></span>
                </button>
                <button type="button" class="svc-tab" data-filter="active" role="tab" aria-selected="false">
                    نشطة
                    <span class="svc-tab-count"><?= $activeCount ?></span>
                </button>
                <button type="button" class="svc-tab" data-filter="inactive" role="tab" aria-selected="false">
                    معطّلة
                    <span class="svc-tab-count"><?= $inactiveCount ?></span>
                </button>
            </div>
        </div>

        <div class="svc-cards" id="svcCards">
            <?php foreach ($items as $idx => $it): 
                $features = json_or_empty($it['features']);
                $kws = array_filter(array_map('trim', explode(',', (string)$it['keywords'])));
            ?>
                <article class="svc-item<?= !$it['is_active'] ? ' svc-item-off' : '' ?>"
                         data-id="<?= (int)$it['id'] ?>"
                         data-active="<?= (int)$it['is_active'] ?>"
                         data-search="<?= e(strtolower($it['title'] . ' ' . $it['description'] . ' ' . $it['keywords'])) ?>">

                    <div class="svc-item-top">
                        <div class="svc-item-icon"><?= e($it['icon']) ?></div>
                        <div class="svc-item-order">#<?= (int)($idx + 1) ?></div>
                    </div>

                    <div class="svc-item-body">
                        <h3 class="svc-item-title"><?= e($it['title']) ?></h3>
                        <?php if ($it['description']): ?>
                            <p class="svc-item-desc"><?= e(mb_substr($it['description'], 0, 120)) ?><?= mb_strlen($it['description']) > 120 ? '…' : '' ?></p>
                        <?php endif; ?>

                        <div class="svc-item-features">
                            <?php if ($features): ?>
                                <span class="svc-item-feat-count">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 6L9 17l-5-5"/></svg>
                                    <?= count($features) ?> مميزات
                                </span>
                            <?php endif; ?>
                            <?php if ($kws): ?>
                                <span class="svc-item-feat-count svc-item-kw-count">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                                    <?= count($kws) ?> كلمات
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="svc-item-meta">
                            <span class="svc-tag <?= $it['is_active'] ? 'svc-tag-ok' : 'svc-tag-mute' ?>" data-status-tag>
                                <span class="svc-tag-dot"></span>
                                <span data-status-text><?= $it['is_active'] ? 'نشطة' : 'معطّلة' ?></span>
                            </span>
                            <span class="svc-tag svc-tag-order">ترتيب: <?= (int)$it['sort_order'] ?></span>
                        </div>
                    </div>

                    <div class="svc-item-actions">
                        <button type="button" class="svc-icon-btn svc-icon-btn-toggle" data-action="toggle" title="تغيير الحالة">
                            <svg class="svc-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <span class="svc-btn-lbl"><?= $it['is_active'] ? 'إخفاء' : 'إظهار' ?></span>
                        </button>

                        <button type="button" class="svc-icon-btn svc-icon-btn-preview" data-action="preview" title="معاينة">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12z"/>
                            </svg>
                        </button>

                        <button type="button" class="svc-icon-btn svc-icon-btn-dup" data-action="duplicate" title="نسخ">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="9" y="9" width="13" height="13" rx="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                        </button>

                        <a href="<?= SITE_URL ?>/admin/services.php?action=edit&id=<?= (int)$it['id'] ?>" class="svc-icon-btn svc-icon-btn-edit" title="تعديل">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>

                        <form method="post" class="inline-form"
                              data-confirm="سيتم حذف هذه الخدمة نهائياً ولن تتمكن من استعادتها"
                              data-confirm-title="حذف الخدمة"
                              data-confirm-btn="نعم، احذف"
                              data-confirm-type="danger"
                              data-confirm-icon="warning">
                            <?= csrf_field() ?>
                            <input type="hidden" name="do" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                            <button type="submit" class="svc-icon-btn svc-icon-btn-del" title="حذف">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="svc-no-results" id="svcNoResults" style="display:none">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
            <h4>لا توجد نتائج</h4>
            <p>جرّب كلمات بحث أخرى أو غيّر الفلتر</p>
        </div>

        <script>
        (function(){
            var searchInput = document.getElementById('svcSearch');
            var searchClear = document.getElementById('svcSearchClear');
            var cards = document.querySelectorAll('.svc-item');
            var noResults = document.getElementById('svcNoResults');
            var tabs = document.querySelectorAll('.svc-tab');
            var activeTab = 'all';
            var searchQuery = '';

            function applyFilters() {
                var visible = 0;
                cards.forEach(function(card){
                    var active = card.dataset.active === '1';
                    var txt = card.dataset.search || '';
                    var matchesTab = activeTab === 'all' || (activeTab === 'active' && active) || (activeTab === 'inactive' && !active);
                    var matchesSearch = !searchQuery || txt.indexOf(searchQuery) > -1;
                    var show = matchesTab && matchesSearch;
                    card.style.display = show ? '' : 'none';
                    if (show) visible++;
                });
                if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
            }

            if (searchInput) {
                searchInput.addEventListener('input', function(){
                    searchQuery = this.value.toLowerCase().trim();
                    if (searchClear) searchClear.style.display = searchQuery ? 'flex' : 'none';
                    applyFilters();
                });
            }
            if (searchClear) {
                searchClear.addEventListener('click', function(){
                    searchInput.value = '';
                    searchQuery = '';
                    searchClear.style.display = 'none';
                    applyFilters();
                    searchInput.focus();
                });
                searchClear.style.display = 'none';
            }
            tabs.forEach(function(tab){
                tab.addEventListener('click', function(){
                    tabs.forEach(function(t){ t.classList.remove('on'); t.setAttribute('aria-selected', 'false'); });
                    tab.classList.add('on');
                    tab.setAttribute('aria-selected', 'true');
                    activeTab = tab.dataset.filter;
                    applyFilters();
                });
            });

            /* ═══════════ AJAX Actions ═══════════ */
            cards.forEach(function(card){
                var id = card.dataset.id;

                /* Toggle */
                var toggleBtn = card.querySelector('[data-action="toggle"]');
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', function(){
                        toggleBtn.classList.add('loading');
                        SKAjax('<?= SITE_URL ?>/admin/services_toggle.php', { id: id })
                            .then(function(res){
                                if (!res.ok) throw new Error(res.msg || 'خطأ');
                                var isActive = res.is_active === 1;
                                card.dataset.active = isActive ? '1' : '0';
                                card.classList.toggle('svc-item-off', !isActive);

                                var tag = card.querySelector('[data-status-tag]');
                                var txt = card.querySelector('[data-status-text]');
                                if (tag) {
                                    tag.classList.toggle('svc-tag-ok', isActive);
                                    tag.classList.toggle('svc-tag-mute', !isActive);
                                }
                                if (txt) txt.textContent = isActive ? 'نشطة' : 'معطّلة';

                                var lbl = toggleBtn.querySelector('.svc-btn-lbl');
                                if (lbl) lbl.textContent = isActive ? 'إخفاء' : 'إظهار';

                                // Update counters
                                var ac = document.getElementById('svcActiveCount');
                                var ic = document.getElementById('svcInactiveCount');
                                if (ac && ic) {
                                    var a = parseInt(ac.textContent, 10) || 0;
                                    var b = parseInt(ic.textContent, 10) || 0;
                                    ac.textContent = isActive ? a + 1 : a - 1;
                                    ic.textContent = isActive ? b - 1 : b + 1;
                                }

                                SKSwal.toast(res.msg, 'ok');
                                applyFilters();
                            })
                            .catch(function(err){
                                SKSwal.toast(err.message || 'خطأ في العملية', 'err');
                            })
                            .finally(function(){
                                toggleBtn.classList.remove('loading');
                            });
                    });
                }

                /* Duplicate */
                var dupBtn = card.querySelector('[data-action="duplicate"]');
                if (dupBtn) {
                    dupBtn.addEventListener('click', function(){
                        SKSwal.confirm({
                            title: 'نسخ الخدمة',
                            text: 'سيتم إنشاء نسخة جديدة من هذه الخدمة (معطلة افتراضياً). هل تريد المتابعة؟',
                            icon: 'question',
                            confirmButtonText: 'نعم، انسخ',
                            cancelButtonText: 'إلغاء'
                        }).then(function(r){
                            if (!r.isConfirmed) return;
                            dupBtn.classList.add('loading');
                            SKAjax('<?= SITE_URL ?>/admin/services_duplicate.php', { id: id })
                                .then(function(res){
                                    if (!res.ok) throw new Error(res.msg || 'خطأ');
                                    SKSwal.toast(res.msg, 'ok');
                                    setTimeout(function(){ location.reload(); }, 900);
                                })
                                .catch(function(err){
                                    SKSwal.toast(err.message || 'خطأ في العملية', 'err');
                                })
                                .finally(function(){
                                    dupBtn.classList.remove('loading');
                                });
                        });
                    });
                }

                /* Preview */
                var prevBtn = card.querySelector('[data-action="preview"]');
                if (prevBtn) {
                    prevBtn.addEventListener('click', function(){
                        var title = card.querySelector('.svc-item-title').textContent;
                        var desc  = (card.querySelector('.svc-item-desc') || {textContent: ''}).textContent;
                        var icon  = card.querySelector('.svc-item-icon').textContent.trim();
                        var feats = [];
                        card.querySelectorAll('.svc-item-feat-count').forEach(function(b){ feats.push(b.textContent.trim()); });
                        Swal.fire({
                            title: '<span style="font-size:34px">' + icon + '</span>',
                            html: '<div style="text-align:right;direction:rtl">' +
                                  '<h3 style="font-family:Changa,sans-serif;font-size:19px;font-weight:800;color:#0a1214;margin:0 0 10px">' + title + '</h3>' +
                                  '<p style="color:#546869;font-size:13.5px;line-height:1.8;margin:0 0 12px">' + desc + '</p>' +
                                  (feats.length ? '<div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end">' + feats.map(function(f){ return '<span style="padding:3px 10px;background:rgba(111,242,208,.15);color:#0d7d66;border-radius:8px;font-size:11.5px;font-weight:700">' + f + '</span>'; }).join('') + '</div>' : '') +
                                  '</div>',
                            showCloseButton: true,
                            showConfirmButton: false,
                            customClass: { popup: 'sk-swal-popup', htmlContainer: 'sk-swal-html' },
                            width: 460
                        });
                    });
                }
            });
        })();
        </script>

    <?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>