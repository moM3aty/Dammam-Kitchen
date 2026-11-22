<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$currentPage = 'gallery';
$pageTitle = 'المعرض';

$action = $_GET['action'] ?? 'list';
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

$uploadDir = __DIR__ . '/../uploads/gallery';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
if (!is_dir($uploadDir . '/thumbs')) @mkdir($uploadDir . '/thumbs', 0755, true);

/* ═══════════ POST ACTIONS ═══════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $do = $_POST['do'] ?? '';

    if ($do === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT file_path, type FROM gallery WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                if (strpos($row['file_path'], 'uploads/') === 0) {
                    $f = __DIR__ . '/../' . $row['file_path'];
                    if (is_file($f)) @unlink($f);
                }
                $thumb = $uploadDir . '/thumbs/vid_' . $id . '.jpg';
                if (is_file($thumb)) @unlink($thumb);
            }
            $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$id]);
            flash('ok', 'تم حذف العنصر بنجاح');
        }
        redirect(SITE_URL . '/admin/gallery.php');
    }

    if ($do === 'save') {
        $editIdPost = (int)($_POST['id'] ?? 0);
        $altText    = trim($_POST['alt_text'] ?? '');
        $sortBase   = (int)($_POST['sort_order'] ?? 0);
        $active     = isset($_POST['is_active']) ? 1 : 0;

        if ($editIdPost > 0) {
            $newFile = null;
            if (isset($_FILES['file']) && ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $newFile = upload_image($_FILES['file'], 'uploads/gallery');
                if (!$newFile) $errors[] = 'فشل رفع الملف.';
            }

            if (empty($errors)) {
                if ($newFile) {
                    $type = preg_match('/\.(mp4|webm|mov|avi)$/i', $newFile) ? 'vid' : 'img';
                    $stmt = $pdo->prepare("SELECT file_path FROM gallery WHERE id = ?");
                    $stmt->execute([$editIdPost]);
                    $old = $stmt->fetch();
                    if ($old && strpos($old['file_path'], 'uploads/') === 0) {
                        $f = __DIR__ . '/../' . $old['file_path'];
                        if (is_file($f)) @unlink($f);
                    }
                    // حذف thumb قديم
                    $oldThumb = $uploadDir . '/thumbs/vid_' . $editIdPost . '.jpg';
                    if (is_file($oldThumb)) @unlink($oldThumb);

                    $pdo->prepare("UPDATE gallery SET file_path=?, type=?, alt_text=?, sort_order=?, is_active=? WHERE id=?")
                        ->execute([$newFile, $type, $altText, $sortBase, $active, $editIdPost]);
                } else {
                    $pdo->prepare("UPDATE gallery SET alt_text=?, sort_order=?, is_active=? WHERE id=?")
                        ->execute([$altText, $sortBase, $active, $editIdPost]);
                }
                flash('ok', 'تم تحديث العنصر');
                redirect(SITE_URL . '/admin/gallery.php');
            }
        } else {
            $files = $_FILES['files'] ?? null;
            $success = 0;
            $failed  = 0;

            if ($files && is_array($files['tmp_name'])) {
                $count = count($files['tmp_name']);
                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] !== UPLOAD_ERR_OK) { $failed++; continue; }
                    $single = [
                        'name'     => $files['name'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'type'     => $files['type'][$i],
                        'error'    => $files['error'][$i],
                        'size'     => $files['size'][$i],
                    ];
                    $path = upload_image($single, 'uploads/gallery');
                    if (!$path) { $failed++; continue; }
                    $type = preg_match('/\.(mp4|webm|mov|avi)$/i', $path) ? 'vid' : 'img';
                    $alt  = $altText ?: pathinfo($single['name'], PATHINFO_FILENAME);
                    $pdo->prepare("INSERT INTO gallery (type, file_path, alt_text, sort_order, is_active) VALUES (?,?,?,?,?)")
                        ->execute([$type, $path, $alt, $sortBase + $i, $active]);
                    $success++;
                }
            }

            if ($success > 0) {
                flash('ok', "تم رفع $success عنصر بنجاح" . ($failed ? " · $failed فشل" : ''));
                redirect(SITE_URL . '/admin/gallery.php');
            } else {
                $errors[] = 'لم يتم رفع أي ملف.';
            }
        }
    }
}

/* ═══════════ EDIT LOAD ═══════════ */
$editItem = null;
if ($action === 'edit' && $editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
    if (!$editItem) { flash('err', 'العنصر غير موجود'); redirect(SITE_URL . '/admin/gallery.php'); }
}
$showForm = ($action === 'new' || ($action === 'edit' && $editItem));

/* ═══════════ LIST DATA — محسّن ═══════════ */
$filter = $_GET['t'] ?? 'all';
$search = trim($_GET['q'] ?? '');
$perPage = 24;
$page = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPage;

/* استعلام واحد لكل الإحصائيات */
$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN type='img' THEN 1 ELSE 0 END) AS imgs,
        SUM(CASE WHEN type='vid' THEN 1 ELSE 0 END) AS vids,
        SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) AS actives
    FROM gallery
")->fetch();

$totalCount  = (int)$stats['total'];
$imgCount    = (int)$stats['imgs'];
$vidCount    = (int)$stats['vids'];
$activeCount = (int)$stats['actives'];

/* بناء WHERE */
$where = [];
$params = [];
if ($filter === 'img' || $filter === 'vid') {
    $where[] = "type = ?";
    $params[] = $filter;
}
if ($search !== '') {
    $where[] = "(alt_text LIKE ? OR file_path LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
}
$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

/* count للفلتر الحالي */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM gallery $whereSQL");
$stmt->execute($params);
$filteredTotal = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($filteredTotal / $perPage));

if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

/* جلب العناصر */
$paramsWithLimit = $params;
$stmt = $pdo->prepare("SELECT * FROM gallery $whereSQL ORDER BY sort_order ASC, id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($paramsWithLimit);
$items = $stmt->fetchAll();

$flashes = get_flashes();
include __DIR__ . '/includes/header.php';

/* Helper: مسار thumb للفيديو */
function vid_thumb($id, $path) {
    $thumbFs = __DIR__ . '/../uploads/gallery/thumbs/vid_' . (int)$id . '.jpg';
    if (is_file($thumbFs)) {
        return SITE_URL . '/uploads/gallery/thumbs/vid_' . (int)$id . '.jpg';
    }
    return null;
}
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

<div class="gal-page-head">
    <div class="gal-page-head-txt">
        <span class="gal-eyebrow">إدارة المحتوى</span>
        <h1><?= $showForm ? ($editItem ? 'تعديل عنصر' : 'رفع عناصر جديدة') : 'معرض الأعمال' ?></h1>
        <p><?= $showForm ? 'ارفع صوراً أو فيديوهات' : 'إدارة صور وفيديوهات أعمالك' ?></p>
    </div>
    <div class="gal-page-head-actions">
        <?php if ($showForm): ?>
            <a href="<?= SITE_URL ?>/admin/gallery.php" class="gal-btn gal-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                العودة للمعرض
            </a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/admin/gallery.php?action=new" class="gal-btn gal-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                رفع عناصر جديدة
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($showForm): ?>

    <?php if ($errors): ?>
        <div class="gal-alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <div><b>يرجى مراجعة التالي:</b><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="gal-form" id="galForm">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="save">
        <input type="hidden" name="id" value="<?= $editItem ? (int)$editItem['id'] : 0 ?>">

        <div class="gal-form-grid">
            <div class="gal-form-main">
                <div class="gal-card">
                    <div class="gal-card-h">
                        <div class="gal-card-ic gal-card-ic-mint">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                        </div>
                        <div>
                            <h3>الملفات</h3>
                            <p><?= $editItem ? 'اتركه فارغاً للإبقاء على الملف الحالي' : 'يدعم رفع عدة ملفات دفعة واحدة' ?></p>
                        </div>
                    </div>
                    <div class="gal-card-b">
                        <?php if ($editItem): ?>
                            <div class="gal-current-file">
                                <div class="gal-current-thumb">
                                    <?php if ($editItem['type'] === 'vid'): 
                                        $tpath = vid_thumb($editItem['id'], $editItem['file_path']);
                                    ?>
                                        <?php if ($tpath): ?>
                                            <img src="<?= e($tpath) ?>" alt="">
                                        <?php else: ?>
                                            <video src="<?= e(media_url($editItem['file_path'])) ?>" muted preload="metadata"></video>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <img src="<?= e(media_url($editItem['file_path'])) ?>" alt="">
                                    <?php endif; ?>
                                </div>
                                <div class="gal-current-info">
                                    <b>الملف الحالي</b>
                                    <small dir="ltr"><?= e(basename($editItem['file_path'])) ?></small>
                                    <span class="gal-type-badge gal-type-<?= $editItem['type'] ?>"><?= $editItem['type'] === 'vid' ? 'فيديو' : 'صورة' ?></span>
                                </div>
                            </div>
                            <div class="gal-field">
                                <label class="gal-label">استبدال الملف <small class="gal-opt">(اختياري)</small></label>
                                <div class="gal-drop" id="galDrop">
                                    <input type="file" name="file" accept="image/*,video/*" id="galFileInput">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="17 8 12 3 7 8"/>
                                        <line x1="12" y1="3" x2="12" y2="15"/>
                                    </svg>
                                    <b>اضغط أو اسحب ملفاً هنا</b>
                                    <small>JPG · PNG · WEBP · MP4 · WEBM</small>
                                </div>
                                <div class="gal-preview" id="galPreview"></div>
                            </div>
                        <?php else: ?>
                            <div class="gal-field">
                                <div class="gal-drop gal-drop-lg" id="galDrop">
                                    <input type="file" name="files[]" accept="image/*,video/*" multiple id="galFileInput">
                                    <div class="gal-drop-vis">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                            <polyline points="17 8 12 3 7 8"/>
                                            <line x1="12" y1="3" x2="12" y2="15"/>
                                        </svg>
                                    </div>
                                    <b>اسحب الملفات هنا أو اضغط للاختيار</b>
                                    <small>يمكنك اختيار عدة ملفات — JPG · PNG · WEBP · MP4 · WEBM</small>
                                </div>
                                <div class="gal-preview gal-preview-grid" id="galPreview"></div>
                                <div class="gal-preview-count" id="galPreviewCount" style="display:none"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="gal-form-side">
                <div class="gal-card">
                    <div class="gal-card-h">
                        <div class="gal-card-ic gal-card-ic-violet">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                        </div>
                        <div><h3>الإعدادات</h3><p>النص البديل والترتيب</p></div>
                    </div>
                    <div class="gal-card-b">
                        <div class="gal-field">
                            <label class="gal-label">النص البديل (Alt Text)</label>
                            <input type="text" name="alt_text" class="gal-input" maxlength="255"
                                   value="<?= e($editItem['alt_text'] ?? '') ?>"
                                   placeholder="مثال: مطبخ ألمنيوم مفصّل في الدمام">
                            <small class="gal-help"><?= $editItem ? 'وصف مختصر للصورة' : 'سيُستخدم نفس النص لكل الملفات المرفوعة' ?></small>
                        </div>
                        <div class="gal-field">
                            <label class="gal-label">ترتيب العرض</label>
                            <input type="number" name="sort_order" class="gal-input" value="<?= (int)($editItem['sort_order'] ?? 0) ?>">
                            <small class="gal-help">الأرقام الأصغر تظهر أولاً</small>
                        </div>
                        <div class="gal-switch-wrap">
                            <label class="gal-switch">
                                <input type="checkbox" name="is_active" value="1" <?= ($editItem['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <span class="gal-switch-track"><span class="gal-switch-thumb"></span></span>
                                <span class="gal-switch-lbl"><b>ظاهر في الموقع</b><small>يمكنك إخفاء العنصر مؤقتاً</small></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="gal-card gal-card-actions">
                    <div class="gal-card-b">
                        <button type="submit" class="gal-btn gal-btn-primary gal-btn-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            <?= $editItem ? 'حفظ التعديلات' : 'رفع العناصر' ?>
                        </button>
                        <a href="<?= SITE_URL ?>/admin/gallery.php" class="gal-btn gal-btn-ghost gal-btn-block">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
    (function(){
        var drop = document.getElementById('galDrop');
        var input = document.getElementById('galFileInput');
        var preview = document.getElementById('galPreview');
        var counter = document.getElementById('galPreviewCount');
        var isMulti = input.hasAttribute('multiple');
        if (!drop || !input) return;

        ['dragenter','dragover'].forEach(function(ev){
            drop.addEventListener(ev, function(e){ e.preventDefault(); drop.classList.add('dragover'); });
        });
        ['dragleave','drop'].forEach(function(ev){
            drop.addEventListener(ev, function(e){ e.preventDefault(); drop.classList.remove('dragover'); });
        });
        drop.addEventListener('drop', function(e){
            if (e.dataTransfer.files.length) {
                try { input.files = e.dataTransfer.files; } catch(err){}
                renderPreview();
            }
        });
        input.addEventListener('change', renderPreview);

        function renderPreview(){
            preview.innerHTML = '';
            if (counter) counter.style.display = 'none';
            if (!input.files || !input.files.length) return;
            if (isMulti && counter) {
                counter.style.display = 'block';
                counter.textContent = input.files.length + ' ملف مختار';
            }
            Array.prototype.forEach.call(input.files, function(f){
                var url = URL.createObjectURL(f);
                var el = document.createElement('div');
                el.className = 'gal-preview-item';
                if (f.type.startsWith('video/')) {
                    el.innerHTML = '<video src="'+url+'" muted preload="metadata"></video><span class="gal-preview-type">فيديو</span>';
                } else {
                    el.innerHTML = '<img src="'+url+'" alt=""><span class="gal-preview-type">صورة</span>';
                }
                preview.appendChild(el);
            });
        }
    })();
    </script>

<?php else: ?>

    <div class="gal-stats">
        <div class="gal-stat">
            <div class="gal-stat-ic gal-stat-mint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
            <div class="gal-stat-body"><span class="gal-stat-num"><?= $totalCount ?></span><span class="gal-stat-lbl">إجمالي العناصر</span></div>
        </div>
        <div class="gal-stat">
            <div class="gal-stat-ic gal-stat-blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
            <div class="gal-stat-body"><span class="gal-stat-num"><?= $imgCount ?></span><span class="gal-stat-lbl">صورة</span></div>
        </div>
        <div class="gal-stat">
            <div class="gal-stat-ic gal-stat-cop">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            </div>
            <div class="gal-stat-body"><span class="gal-stat-num"><?= $vidCount ?></span><span class="gal-stat-lbl">فيديو</span></div>
        </div>
        <div class="gal-stat">
            <div class="gal-stat-ic gal-stat-ok">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
            <div class="gal-stat-body"><span class="gal-stat-num"><?= $activeCount ?></span><span class="gal-stat-lbl">ظاهر</span></div>
        </div>
    </div>

    <?php if (!$items && !$search && $filter === 'all'): ?>
        <div class="gal-empty">
            <div class="gal-empty-ic">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
            <h3>المعرض فارغ</h3>
            <p>ابدأ برفع أول صورة أو فيديو لأعمالك</p>
            <a href="<?= SITE_URL ?>/admin/gallery.php?action=new" class="gal-btn gal-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                رفع عناصر
            </a>
        </div>
    <?php else: ?>

        <div class="gal-toolbar">
            <form method="get" class="gal-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="ابحث في النص البديل أو اسم الملف...">
                <input type="hidden" name="t" value="<?= e($filter) ?>">
                <?php if ($search): ?>
                    <a href="?t=<?= e($filter) ?>" class="gal-search-clear" aria-label="مسح">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </a>
                <?php endif; ?>
            </form>
            <div class="gal-tabs">
                <a href="?t=all&q=<?= e($search) ?>" class="gal-tab <?= $filter === 'all' ? 'on' : '' ?>">الكل <span class="gal-tab-count"><?= $totalCount ?></span></a>
                <a href="?t=img&q=<?= e($search) ?>" class="gal-tab <?= $filter === 'img' ? 'on' : '' ?>">صور <span class="gal-tab-count"><?= $imgCount ?></span></a>
                <a href="?t=vid&q=<?= e($search) ?>" class="gal-tab <?= $filter === 'vid' ? 'on' : '' ?>">فيديو <span class="gal-tab-count"><?= $vidCount ?></span></a>
            </div>
        </div>

        <?php if (!$items): ?>
            <div class="gal-no-results">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <h4>لا توجد نتائج</h4>
                <p>جرّب كلمات بحث أخرى أو غيّر الفلتر</p>
            </div>
        <?php else: ?>
            <div class="gal-grid" id="galGrid">
                <?php foreach ($items as $it): 
                    $thumb = null;
                    if ($it['type'] === 'vid') {
                        $thumb = vid_thumb($it['id'], $it['file_path']);
                    }
                ?>
                    <article class="gal-item<?= !$it['is_active'] ? ' gal-item-off' : '' ?>"
                             data-id="<?= (int)$it['id'] ?>"
                             data-active="<?= (int)$it['is_active'] ?>"
                             data-type="<?= e($it['type']) ?>"
                             data-src="<?= e(media_url($it['file_path'])) ?>"
                             data-alt="<?= e($it['alt_text']) ?>">

                        <div class="gal-thumb">
                            <?php if ($it['type'] === 'vid'): ?>
                                <?php if ($thumb): ?>
                                    <img src="<?= e($thumb) ?>" alt="<?= e($it['alt_text']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="gal-vid-placeholder">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="23 7 16 12 23 17 23 7"/>
                                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <span class="gal-play-btn">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                                <span class="gal-type-tag gal-type-vid">فيديو</span>
                            <?php else: ?>
                                <img src="<?= e(media_url($it['file_path'])) ?>" alt="<?= e($it['alt_text']) ?>" loading="lazy">
                                <span class="gal-type-tag gal-type-img">صورة</span>
                            <?php endif; ?>

                            <?php if (!$it['is_active']): ?>
                                <span class="gal-off-tag">مخفي</span>
                            <?php endif; ?>

                            <div class="gal-overlay">
                                <button type="button" class="gal-ov-btn" data-action="preview" title="معاينة">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12z"/></svg>
                                </button>
                                <a href="<?= SITE_URL ?>/admin/gallery.php?action=edit&id=<?= (int)$it['id'] ?>" class="gal-ov-btn" title="تعديل">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <button type="button" class="gal-ov-btn" data-action="toggle" title="إظهار/إخفاء">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button type="button" class="gal-ov-btn gal-ov-danger" data-action="delete" title="حذف">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="gal-item-info">
                            <b class="gal-item-alt" title="<?= e($it['alt_text']) ?>"><?= e($it['alt_text'] ?: 'بدون نص') ?></b>
                            <small>ترتيب: <?= (int)$it['sort_order'] ?></small>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="gal-pagination">
                    <?php if ($page > 1): ?>
                        <a href="?p=<?= $page - 1 ?>&t=<?= e($filter) ?>&q=<?= e($search) ?>" class="gal-page-btn">← السابق</a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="gal-page-btn current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?p=<?= $i ?>&t=<?= e($filter) ?>&q=<?= e($search) ?>" class="gal-page-btn"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?p=<?= $page + 1 ?>&t=<?= e($filter) ?>&q=<?= e($search) ?>" class="gal-page-btn">التالي →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <div class="gal-lightbox" id="galLightbox" aria-hidden="true">
        <button type="button" class="gal-lb-close" id="galLbClose" aria-label="إغلاق">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
        <div class="gal-lb-content">
            <img id="galLbImg" src="" alt="">
            <video id="galLbVid" controls playsinline loop preload="none"></video>
            <div class="gal-lb-caption" id="galLbCaption"></div>
        </div>
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
                    catch(e) { console.error('❌ Not JSON:', text.substring(0,200)); throw new Error('استجابة غير صالحة'); }
                    if (!r.ok) throw new Error(json.msg || ('HTTP ' + r.status));
                    return json;
                });
            });
        }

        function notify(msg, type) {
            if (window.SKSwal && typeof window.SKSwal.toast === 'function') {
                try { window.SKSwal.toast(msg, type || 'ok'); return; } catch(e) {}
            }
            var old = document.getElementById('galSimpleToast');
            if (old) old.remove();
            var t = document.createElement('div');
            t.id = 'galSimpleToast';
            t.textContent = msg;
            t.style.cssText = 'position:fixed;top:24px;left:50%;transform:translateX(-50%);padding:12px 22px;border-radius:10px;color:#fff;font:600 14px/1.4 "IBM Plex Sans Arabic",sans-serif;z-index:99999;box-shadow:0 8px 24px rgba(0,0,0,.25);background:' + (type === 'err' ? '#ef4444' : '#10b981') + ';direction:rtl;transition:opacity .3s';
            document.body.appendChild(t);
            setTimeout(function(){ t.style.opacity = '0'; }, 2500);
            setTimeout(function(){ t.remove(); }, 3000);
        }

        function confirmAction(opts) {
            if (window.SKSwal && typeof window.SKSwal.danger === 'function') return window.SKSwal.danger(opts);
            return Promise.resolve({ isConfirmed: window.confirm((opts.title || 'تأكيد') + '\n\n' + (opts.text || '')) });
        }

        var lb = document.getElementById('galLightbox');
        var lbImg = document.getElementById('galLbImg');
        var lbVid = document.getElementById('galLbVid');
        var lbCap = document.getElementById('galLbCaption');
        var lbClose = document.getElementById('galLbClose');

        function closeLb(){
            if (!lb) return;
            lb.classList.remove('show');
            lb.setAttribute('aria-hidden', 'true');
            if (lbVid) { lbVid.pause(); lbVid.removeAttribute('src'); lbVid.load(); }
        }
        function openLb(src, type, alt){
            if (!lb) return;
            lbImg.classList.remove('on'); lbVid.classList.remove('on');
            if (type === 'vid') {
                lbVid.src = src; lbVid.classList.add('on');
                lbVid.play().catch(function(){});
            } else {
                lbImg.src = src; lbImg.alt = alt || ''; lbImg.classList.add('on');
            }
            if (lbCap) lbCap.textContent = alt || '';
            lb.classList.add('show');
            lb.setAttribute('aria-hidden', 'false');
        }

        if (lbClose) lbClose.addEventListener('click', closeLb);
        if (lb) lb.addEventListener('click', function(e){ if (e.target === lb) closeLb(); });
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && lb && lb.classList.contains('show')) closeLb(); });

        document.querySelectorAll('.gal-item').forEach(function(card){
            var id = card.dataset.id;
            var type = card.dataset.type;
            var src = card.dataset.src;
            var alt = card.dataset.alt;

            var prevBtn = card.querySelector('[data-action="preview"]');
            var toggleBtn = card.querySelector('[data-action="toggle"]');
            var delBtn = card.querySelector('[data-action="delete"]');

            if (prevBtn) prevBtn.addEventListener('click', function(e){ e.stopPropagation(); openLb(src, type, alt); });
            card.addEventListener('dblclick', function(){ openLb(src, type, alt); });

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e){
                    e.preventDefault(); e.stopPropagation();
                    if (toggleBtn.disabled) return;
                    toggleBtn.disabled = true; toggleBtn.classList.add('loading');
                    apiCall('gallery_toggle.php', { id: id })
                        .then(function(res){
                            if (!res || res.ok !== true) throw new Error((res && res.msg) || 'فشلت العملية');
                            var isActive = Number(res.is_active) === 1;
                            card.dataset.active = isActive ? '1' : '0';
                            card.classList.toggle('gal-item-off', !isActive);
                            var offTag = card.querySelector('.gal-off-tag');
                            if (isActive) { if (offTag) offTag.remove(); }
                            else if (!offTag) {
                                var span = document.createElement('span');
                                span.className = 'gal-off-tag'; span.textContent = 'مخفي';
                                var thumb = card.querySelector('.gal-thumb');
                                if (thumb) thumb.appendChild(span);
                            }
                            notify(res.msg || (isActive ? 'تم الإظهار' : 'تم الإخفاء'), 'ok');
                        })
                        .catch(function(err){ notify(err.message || 'فشل', 'err'); })
                        .finally(function(){ toggleBtn.disabled = false; toggleBtn.classList.remove('loading'); });
                });
            }

            if (delBtn) {
                delBtn.addEventListener('click', function(e){
                    e.preventDefault(); e.stopPropagation();
                    if (delBtn.disabled) return;
                    confirmAction({ title: 'حذف العنصر', text: 'سيتم حذف الملف نهائياً. هل أنت متأكد؟', confirmButtonText: 'نعم، احذف' })
                        .then(function(r){
                            if (!r.isConfirmed) return;
                            delBtn.disabled = true; delBtn.classList.add('loading');
                            apiCall('gallery_delete.php', { id: id })
                                .then(function(res){
                                    if (!res || res.ok !== true) throw new Error((res && res.msg) || 'فشل الحذف');
                                    card.style.transition = 'opacity .3s, transform .3s';
                                    card.style.opacity = '0'; card.style.transform = 'scale(.9)';
                                    setTimeout(function(){ card.remove(); }, 300);
                                    notify(res.msg || 'تم الحذف', 'ok');
                                })
                                .catch(function(err){ notify(err.message || 'فشل الحذف', 'err'); delBtn.disabled = false; delBtn.classList.remove('loading'); });
                        });
                });
            }
        });
    })();
    </script>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>