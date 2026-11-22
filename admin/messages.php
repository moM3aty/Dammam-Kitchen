<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$currentPage = 'messages';
$pageTitle = 'الرسائل';

$viewId = isset($_GET['view']) ? (int)$_GET['view'] : 0;

// حذف
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $do = $_POST['do'] ?? '';
    if ($do === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$id]);
            flash('ok', 'تم حذف الرسالة');
        }
        redirect(SITE_URL . '/admin/messages.php');
    }
    if ($do === 'mark_read') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$id]);
        }
        redirect(SITE_URL . '/admin/messages.php?view=' . $id);
    }
    if ($do === 'mark_all_read') {
        $pdo->exec("UPDATE messages SET is_read = 1 WHERE is_read = 0");
        flash('ok', 'تم تعليم كل الرسائل كمقروءة');
        redirect(SITE_URL . '/admin/messages.php');
    }
}

// عرض رسالة
$singleMsg = null;
if ($viewId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$viewId]);
    $singleMsg = $stmt->fetch();
    if ($singleMsg && !$singleMsg['is_read']) {
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$viewId]);
        $singleMsg['is_read'] = 1;
    }
}

// البحث
$search = trim($_GET['q'] ?? '');
$filter = $_GET['f'] ?? 'all';
$perPage = 20;
$page = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
if ($search !== '') {
    $where[] = "(name LIKE ? OR phone LIKE ? OR message LIKE ? OR city LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}
if ($filter === 'unread') $where[] = "is_read = 0";
elseif ($filter === 'read') $where[] = "is_read = 1";
$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

$totalCount = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages $whereSQL");
    $stmt->execute($params);
    $totalCount = (int)$stmt->fetchColumn();
} catch (Exception $e) {}

$totalPages = max(1, (int)ceil($totalCount / $perPage));

$list = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM messages $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);
    $list = $stmt->fetchAll();
} catch (Exception $e) {}

$flashes = get_flashes();
include __DIR__ . '/includes/header.php';
?>

<?php foreach ($flashes as $f): ?><div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endforeach; ?>

<div class="page-h">
    <div>
        <h1><?= $singleMsg ? '📩 تفاصيل الرسالة' : '📬 الرسائل' ?></h1>
        <p><?= $singleMsg ? 'عرض الرسالة والرد عليها' : "إجمالي: $totalCount رسالة" ?></p>
    </div>
    <div class="page-actions">
        <?php if ($singleMsg): ?>
            <a href="<?= SITE_URL ?>/admin/messages.php" class="btn btn-ghost">← رجوع للقائمة</a>
        <?php else: ?>
            <form method="post" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="do" value="mark_all_read">
                <button type="submit" class="btn btn-ghost">✓ تعليم الكل كمقروء</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($singleMsg): ?>

    <div class="msg-detail">
        <div class="msg-head">
            <h2>
                <?= e($singleMsg['name']) ?>
                <?php if (!$singleMsg['is_read']): ?><span class="badge badge-err">جديدة</span><?php endif; ?>
            </h2>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="tel:<?= e($singleMsg['phone']) ?>" class="btn btn-primary btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    اتصال
                </a>
                <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $singleMsg['phone'])) ?>" target="_blank" rel="noopener" class="btn btn-dark btn-sm">
                    رد واتساب
                </a>
                <form method="post" class="inline-form" onsubmit="return confirm('حذف الرسالة نهائياً؟')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="do" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$singleMsg['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                </form>
            </div>
        </div>

        <div class="msg-meta">
            <div class="msg-meta-item"><small>الاسم</small><b><?= e($singleMsg['name']) ?></b></div>
            <div class="msg-meta-item"><small>الجوال</small><b dir="ltr" style="font-family:monospace"><?= e($singleMsg['phone']) ?></b></div>
            <div class="msg-meta-item"><small>المدينة</small><b><?= e($singleMsg['city'] ?: '—') ?></b></div>
            <div class="msg-meta-item"><small>الحي</small><b><?= e($singleMsg['area'] ?: '—') ?></b></div>
            <div class="msg-meta-item"><small>الخدمة</small><b><?= e($singleMsg['service'] ?: '—') ?></b></div>
            <div class="msg-meta-item"><small>التاريخ</small><b><?= e(format_date_ar($singleMsg['created_at'])) ?></b></div>
        </div>

        <?php if ($singleMsg['message']): ?>
            <div class="msg-meta-item" style="margin-bottom:14px"><small>تفاصيل الطلب</small></div>
            <div class="msg-body"><?= e($singleMsg['message']) ?></div>
        <?php else: ?>
            <div class="msg-meta-item" style="text-align:center;color:var(--tx3)">لا توجد تفاصيل إضافية</div>
        <?php endif; ?>
    </div>

<?php else: ?>

    <div class="search-bar">
        <form method="get" style="display:flex;gap:8px;flex:1;flex-wrap:wrap">
            <div class="search-input">
                <input type="text" name="q" placeholder="ابحث في الاسم، الجوال، الرسالة..." value="<?= e($search) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            </div>
            <input type="hidden" name="f" value="<?= e($filter) ?>">
            <button type="submit" class="btn btn-primary">بحث</button>
            <?php if ($search): ?><a href="<?= SITE_URL ?>/admin/messages.php" class="btn btn-ghost">مسح</a><?php endif; ?>
        </form>
        <div style="display:flex;gap:4px">
            <a href="?f=all&q=<?= e($search) ?>" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-ghost' ?> btn-sm">الكل</a>
            <a href="?f=unread&q=<?= e($search) ?>" class="btn <?= $filter === 'unread' ? 'btn-primary' : 'btn-ghost' ?> btn-sm">غير مقروءة</a>
            <a href="?f=read&q=<?= e($search) ?>" class="btn <?= $filter === 'read' ? 'btn-primary' : 'btn-ghost' ?> btn-sm">مقروءة</a>
        </div>
    </div>

    <div class="card">
        <?php if (!$list): ?>
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <h4>لا توجد رسائل</h4>
                <p><?= $search ? 'لا توجد نتائج لبحثك' : 'ستظهر رسائل العملاء هنا' ?></p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th style="width:50px"></th>
                            <th>الاسم</th>
                            <th>الجوال</th>
                            <th>المدينة</th>
                            <th>الخدمة</th>
                            <th>الرسالة</th>
                            <th style="width:110px">التاريخ</th>
                            <th style="width:120px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list as $it): ?>
                            <tr style="<?= !$it['is_read'] ? 'background:#fef9f5' : '' ?>">
                                <td>
                                    <?php if (!$it['is_read']): ?>
                                        <span title="جديدة" style="display:inline-block;width:8px;height:8px;background:#ef4444;border-radius:50%"></span>
                                    <?php endif; ?>
                                </td>
                                <td><b><?= e($it['name']) ?></b></td>
                                <td dir="ltr" style="text-align:right;font-family:monospace"><?= e($it['phone']) ?></td>
                                <td style="font-size:13px"><?= e($it['city']) ?></td>
                                <td style="font-size:12.5px"><?= e($it['service']) ?></td>
                                <td style="font-size:12.5px;color:var(--tx3)"><?= e(mb_substr($it['message'] ?? '', 0, 40)) ?><?= mb_strlen($it['message'] ?? '') > 40 ? '...' : '' ?></td>
                                <td style="font-size:12px;color:var(--tx3)"><?= e(format_date($it['created_at'], 'm/d H:i')) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="?view=<?= (int)$it['id'] ?>" class="btn btn-primary btn-sm">عرض</a>
                                        <form method="post" class="inline-form" onsubmit="return confirm('حذف الرسالة؟')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="do" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 1): ?>
                <div style="padding:16px">
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="cur"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?p=<?= $i ?>&q=<?= e($search) ?>&f=<?= e($filter) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>