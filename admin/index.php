<?php
// admin/index.php — Full CMS Dashboard with Full-Width Quick Actions
require_once __DIR__ . '/includes/init.php';
require_login();

$currentPage = 'dashboard';
$pageTitle = 'لوحة التحكم';
$pageDesc  = 'إحصائيات شاملة لكل محتوى الموقع';

/* ============ 1. الإحصائيات العامة ============ */
$stats = [
    'messages'     => 0, 'unread'       => 0,
    'gallery'      => 0, 'gallery_img'  => 0, 'gallery_vid' => 0,
    'services'     => 0, 'testimonials' => 0,
    'faqs'         => 0, 'areas'        => 0,
    'materials'    => 0, 'kitchen'      => 0,
    'steps'        => 0, 'why'          => 0,
];

try {
    $stats['messages']     = (int)$pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    $stats['unread']       = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
    $stats['gallery']      = (int)$pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
    $stats['gallery_img']  = (int)$pdo->query("SELECT COUNT(*) FROM gallery WHERE type = 'img'")->fetchColumn();
    $stats['gallery_vid']  = (int)$pdo->query("SELECT COUNT(*) FROM gallery WHERE type = 'vid'")->fetchColumn();
    $stats['services']     = (int)$pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
    $stats['testimonials'] = (int)$pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
    $stats['faqs']         = (int)$pdo->query("SELECT COUNT(*) FROM faqs")->fetchColumn();
    $stats['areas']        = (int)$pdo->query("SELECT COUNT(*) FROM areas")->fetchColumn();
    $stats['materials']    = (int)$pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
    $stats['kitchen']      = (int)$pdo->query("SELECT COUNT(*) FROM kitchen_types")->fetchColumn();
    $stats['steps']        = (int)$pdo->query("SELECT COUNT(*) FROM steps")->fetchColumn();
    $stats['why']          = (int)$pdo->query("SELECT COUNT(*) FROM why_us")->fetchColumn();
} catch (Exception $e) {}

/* ============ 2. بيانات محتوى الموقع (للرسم البياني) ============ */
$content_data = [
    ['label' => 'أنواع المطابخ',  'count' => $stats['kitchen'],      'color' => 'linear-gradient(90deg, #3b82f6, #60a5fa)', 'icon' => '🍽️'],
    ['label' => 'الخامات',        'count' => $stats['materials'],    'color' => 'linear-gradient(90deg, #10b981, #34d399)', 'icon' => '🧱'],
    ['label' => 'مناطق الخدمة',   'count' => $stats['areas'],        'color' => 'linear-gradient(90deg, #f59e0b, #fbbf24)', 'icon' => '📍'],
    ['label' => 'الأسئلة الشائعة', 'count' => $stats['faqs'],         'color' => 'linear-gradient(90deg, #8b5cf6, #a78bfa)', 'icon' => '❓'],
    ['label' => 'خطوات العمل',    'count' => $stats['steps'],        'color' => 'linear-gradient(90deg, #ec4899, #f472b6)', 'icon' => '📋'],
    ['label' => 'مميزات الشركة',  'count' => $stats['why'],          'color' => 'linear-gradient(90deg, #14b8a6, #2dd4bf)', 'icon' => '✨'],
];
$max_content = 1;
foreach ($content_data as $c) { if ($c['count'] > $max_content) $max_content = $c['count']; }

/* ============ 3. سجل الدخول ============ */
$recent_logins = [];
try {
    $recent_logins = $pdo->query("SELECT * FROM login_attempts ORDER BY attempted_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) {}

/* ============ 4. آخر الرسائل ============ */
$recentMessages = [];
try {
    $recentMessages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 4")->fetchAll();
} catch (Exception $e) {}

/* ============ التحية والتاريخ ============ */
$h = (int)date('H');
if ($h >= 5 && $h < 12)       { $greet = 'صباح الخير'; $greetIcon = '🌤️'; }
elseif ($h >= 12 && $h < 17)  { $greet = 'مساء الخير'; $greetIcon = '☀️'; }
elseif ($h >= 17 && $h < 21)  { $greet = 'مساء الخير'; $greetIcon = '🌇'; }
else                          { $greet = 'مساء النور';  $greetIcon = '🌙'; }

$flashes   = get_flashes();
$user      = auth_user();
$firstName = $user ? explode(' ', ($user['full_name'] ?: $user['username']))[0] : '';

$arabicDays = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
$arabicMonths = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
$today = $arabicDays[(int)date('w')] . '، ' . (int)date('j') . ' ' . $arabicMonths[(int)date('n') - 1] . ' ' . date('Y');

// حساب نسبة صور المعرض
$gallery_total = max(1, $stats['gallery']);
$img_pct = round(($stats['gallery_img'] / $gallery_total) * 100);
$vid_pct = round(($stats['gallery_vid'] / $gallery_total) * 100);

include __DIR__ . '/includes/header.php';
?>

<style>
/* ═══════════════════════════════════════════
   FULL CMS DASHBOARD STYLES
   ═══════════════════════════════════════════ */
.db-wrap { font-family: 'Changa', system-ui, -apple-system, sans-serif; max-width: 1400px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px; padding-bottom: 40px; }

/* Hero */
.db-hero { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 20px; padding: 35px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2); position: relative; overflow: hidden; }
.db-hero::after { content: ''; position: absolute; top: -50px; left: 10%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(111,242,208,0.1) 0%, transparent 70%); border-radius: 50%; }
.db-hero-txt h1 { font-size: 30px; font-weight: 800; color: #fff; margin: 0 0 8px 0; }
.db-hero-txt h1 span { color: #6ff2d0; }
.db-hero-txt p { font-size: 14px; color: #94a3b8; margin: 0; }
.db-hero-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; color: #e2e8f0; margin-bottom: 12px; }
.db-hero-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #6ff2d0, #14c19e); color: #0f172a; border-radius: 12px; font-weight: 800; text-decoration: none; transition: 0.3s; box-shadow: 0 4px 15px rgba(20,193,158,0.3); }
.db-hero-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(20,193,158,0.5); }

/* Top KPIs */
.db-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.db-kpi-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 20px; transition: 0.3s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
.db-kpi-card:hover { transform: translateY(-4px); box-shadow: 0 12px 25px -5px rgba(0,0,0,0.08); border-color: #cbd5e1; }
.db-kpi-icon { width: 60px; height: 60px; border-radius: 16px; display: grid; place-items: center; flex: none; }
.db-kpi-icon svg { width: 28px; height: 28px; }
.db-kpi-info { display: flex; flex-direction: column; }
.db-kpi-value { font-size: 32px; font-weight: 800; color: #0f172a; line-height: 1.1; }
.db-kpi-label { font-size: 14px; color: #64748b; font-weight: 600; margin-top: 4px; }

/* ══════════ FULL WIDTH QUICK ACTIONS ══════════ */
.db-qa-section { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
.db-qa-head { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 20px; }
.db-qa-head svg { width: 20px; height: 20px; color: #14c19e; }
.db-qa-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 16px; }
.db-qa-card { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; padding: 18px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; text-decoration: none; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
.db-qa-card:hover { background: #fff; border-color: #14c19e; transform: translateY(-4px); box-shadow: 0 10px 20px -8px rgba(20, 193, 158, 0.25); }
.db-qa-card-icon { width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; font-size: 22px; }
.db-qa-card-text { font-size: 13.5px; font-weight: 800; color: #1e293b; text-align: center; }

/* Main Grid */
.db-main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start; }
.db-col { display: flex; flex-direction: column; gap: 24px; }

/* Panels */
.db-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
.db-panel-head { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fafafa; }
.db-panel-title { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 10px; }
.db-panel-title svg { width: 20px; height: 20px; color: #14c19e; }
.db-panel-body { padding: 24px; }

/* Content Bars */
.db-bar-item { margin-bottom: 20px; }
.db-bar-item:last-child { margin-bottom: 0; }
.db-bar-header { display: flex; justify-content: space-between; font-size: 14px; font-weight: 700; margin-bottom: 8px; color: #1e293b; }
.db-bar-header span:first-child { display: flex; align-items: center; gap: 6px; }
.db-bar-track { height: 12px; background: #f1f5f9; border-radius: 6px; overflow: hidden; }
.db-bar-fill { height: 100%; border-radius: 6px; transition: width 1s ease-out; }

/* Donut Chart */
.db-donut-wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; }
.db-donut { width: 180px; height: 180px; border-radius: 50%; position: relative; display: grid; place-items: center; margin-bottom: 24px; }
.db-donut-inner { width: 140px; height: 140px; background: #fff; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: inset 0 2px 10px rgba(0,0,0,0.05); }
.db-donut-val { font-size: 32px; font-weight: 800; color: #0f172a; line-height: 1; }
.db-donut-lbl { font-size: 13px; color: #64748b; font-weight: 600; margin-top: 4px; }
.db-legend { display: flex; gap: 20px; width: 100%; justify-content: center; }
.db-legend-item { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #475569; }
.db-legend-dot { width: 12px; height: 12px; border-radius: 50%; }

/* Lists (Messages & Logins) */
.db-list { display: flex; flex-direction: column; }
.db-list-item { display: flex; gap: 16px; padding: 16px 24px; border-bottom: 1px solid #f1f5f9; text-decoration: none; transition: 0.2s; }
.db-list-item:last-child { border-bottom: none; }
.db-list-item:hover { background: #f8fafc; }
.db-list-avatar { width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; font-size: 16px; font-weight: 800; flex: none; }
.db-list-content { flex: 1; display: flex; flex-direction: column; gap: 4px; }
.db-list-title { font-size: 14px; font-weight: 800; color: #0f172a; }
.db-list-sub { font-size: 13px; color: #64748b; font-weight: 500; }
.db-list-meta { font-size: 12px; color: #94a3b8; font-weight: 600; }

@media(max-width: 1100px) {
    .db-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .db-main-grid { grid-template-columns: 1fr; }
}
@media(max-width: 600px) {
    .db-kpi-grid { grid-template-columns: 1fr; }
    .db-hero { flex-direction: column; text-align: center; gap: 20px; padding: 30px 20px; }
    .db-qa-row { grid-template-columns: repeat(3, 1fr); }
}
</style>

<?php foreach ($flashes as $f): ?>
    <div class="flash flash-<?= e($f['type']) ?>" style="margin-bottom:20px; border-radius:12px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span><?= e($f['msg']) ?></span>
    </div>
<?php endforeach; ?>

<div class="db-wrap">

    <!-- ══════════ HERO ══════════ -->
    <div class="db-hero">
        <div class="db-hero-txt">
            <div class="db-hero-badge"><?= $greetIcon ?> <?= e($today) ?></div>
            <h1><?= e($greet) ?>، <span><?= e($firstName) ?></span></h1>
            <p>إليك نظرة شاملة على محتوى الموقع وأدائه اليوم.</p>
        </div>
        <div>
            <a href="<?= SITE_URL ?>" target="_blank" class="db-hero-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6M10 14L21 3"/></svg>
                زيارة الموقع
            </a>
        </div>
    </div>

    <!-- ══════════ TOP KPIs ══════════ -->
    <div class="db-kpi-grid">
        <!-- Gallery -->
        <div class="db-kpi-card">
            <div class="db-kpi-icon" style="background: #eff6ff; color: #3b82f6;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
            <div class="db-kpi-info">
                <span class="db-kpi-value"><?= $stats['gallery'] ?></span>
                <span class="db-kpi-label">عناصر المعرض</span>
            </div>
        </div>

        <!-- Services -->
        <div class="db-kpi-card">
            <div class="db-kpi-icon" style="background: #faf5ff; color: #a855f7;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div class="db-kpi-info">
                <span class="db-kpi-value"><?= $stats['services'] ?></span>
                <span class="db-kpi-label">الخدمات المنشورة</span>
            </div>
        </div>

        <!-- Areas -->
        <div class="db-kpi-card">
            <div class="db-kpi-icon" style="background: #fff7ed; color: #f97316;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div class="db-kpi-info">
                <span class="db-kpi-value"><?= $stats['areas'] ?></span>
                <span class="db-kpi-label">مناطق الخدمة</span>
            </div>
        </div>

        <!-- Messages -->
        <div class="db-kpi-card">
            <div class="db-kpi-icon" style="background: #ecfdf5; color: #10b981;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="db-kpi-info">
                <span class="db-kpi-value"><?= $stats['messages'] ?></span>
                <span class="db-kpi-label">إجمالي الرسائل</span>
            </div>
        </div>
    </div>

    <!-- ══════════ FULL WIDTH QUICK ACTIONS ══════════ -->
    <div class="db-qa-section">
        <div class="db-qa-head">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            إجراءات سريعة
        </div>
        <div class="db-qa-row">
            <a href="<?= SITE_URL ?>/admin/kitchen-types.php?action=new" class="db-qa-card">
                <span class="db-qa-card-icon" style="background: #eff6ff; color: #3b82f6;">🍽️</span>
                <span class="db-qa-card-text">نوع مطبخ</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/materials.php?action=new" class="db-qa-card">
                <span class="db-qa-card-icon" style="background: #ecfdf5; color: #10b981;">🧱</span>
                <span class="db-qa-card-text">خامة جديدة</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/areas.php?action=new" class="db-qa-card">
                <span class="db-qa-card-icon" style="background: #fff7ed; color: #f97316;">📍</span>
                <span class="db-qa-card-text">منطقة خدمة</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/services.php?action=new" class="db-qa-card">
                <span class="db-qa-card-icon" style="background: #faf5ff; color: #a855f7;">🛠️</span>
                <span class="db-qa-card-text">خدمة جديدة</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/gallery.php?action=new" class="db-qa-card">
                <span class="db-qa-card-icon" style="background: #f0f9ff; color: #0ea5e9;">🖼️</span>
                <span class="db-qa-card-text">معرض أعمال</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/faqs.php?action=new" class="db-qa-card">
                <span class="db-qa-card-icon" style="background: #fdf4ff; color: #d946ef;">❓</span>
                <span class="db-qa-card-text">سؤال شائع</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/testimonials.php?action=new" class="db-qa-card">
                <span class="db-qa-card-icon" style="background: #fef2f2; color: #ef4444;">⭐</span>
                <span class="db-qa-card-text">رأي عميل</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/steps.php?action=new" class="db-qa-card">
                <span class="db-qa-card-icon" style="background: #f8fafc; color: #64748b;">📋</span>
                <span class="db-qa-card-text">خطوة عمل</span>
            </a>
        </div>
    </div>

    <!-- ══════════ MAIN GRID ══════════ -->
    <div class="db-main-grid">
        
        <!-- LEFT COLUMN (Content Stats) -->
        <div class="db-col">
            <div class="db-panel">
                <div class="db-panel-head">
                    <h3 class="db-panel-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10M18 20V4M6 20v-4"/></svg>
                        إحصائيات محتوى الموقع
                    </h3>
                </div>
                <div class="db-panel-body">
                    <?php foreach ($content_data as $item): 
                        $pct = round(($item['count'] / $max_content) * 100);
                    ?>
                        <div class="db-bar-item">
                            <div class="db-bar-header">
                                <span><?= $item['icon'] ?> <?= $item['label'] ?></span>
                                <span><?= $item['count'] ?> عنصر</span>
                            </div>
                            <div class="db-bar-track">
                                <div class="db-bar-fill" style="width: <?= $pct ?>%; background: <?= $item['color'] ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="db-panel">
                <div class="db-panel-head">
                    <h3 class="db-panel-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        أحدث الرسائل
                    </h3>
                    <a href="<?= SITE_URL ?>/admin/messages.php" style="font-size:13px; font-weight:800; color:#14c19e; text-decoration:none">عرض الكل ←</a>
                </div>
                <div class="db-list">
                    <?php if (!$recentMessages): ?>
                        <div style="text-align:center; padding:30px; color:#94a3b8;">لا توجد رسائل.</div>
                    <?php else: ?>
                        <?php foreach ($recentMessages as $m): ?>
                            <a href="<?= SITE_URL ?>/admin/messages.php?view=<?= (int)$m['id'] ?>" class="db-list-item">
                                <div class="db-list-avatar" style="background:<?= !$m['is_read'] ? '#ecfdf5' : '#f1f5f9' ?>; color:<?= !$m['is_read'] ? '#10b981' : '#64748b' ?>;">
                                    <?= e(mb_substr($m['name'], 0, 1)) ?>
                                </div>
                                <div class="db-list-content">
                                    <div style="display:flex; justify-content:space-between;">
                                        <span class="db-list-title"><?= e($m['name']) ?></span>
                                        <span class="db-list-meta"><?= e(format_date($m['created_at'], 'm/d H:i')) ?></span>
                                    </div>
                                    <span class="db-list-sub" dir="ltr" style="text-align:right;"><?= e($m['phone']) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (Gallery & Logins) -->
        <div class="db-col">
            <!-- Gallery Donut -->
            <div class="db-panel">
                <div class="db-panel-head">
                    <h3 class="db-panel-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
                        وسائط المعرض
                    </h3>
                </div>
                <div class="db-panel-body db-donut-wrap">
                    <div class="db-donut" style="background: conic-gradient(#3b82f6 0% <?= $img_pct ?>%, #f59e0b <?= $img_pct ?>% 100%);">
                        <div class="db-donut-inner">
                            <span class="db-donut-val"><?= $stats['gallery'] ?></span>
                            <span class="db-donut-lbl">إجمالي العناصر</span>
                        </div>
                    </div>
                    <div class="db-legend">
                        <div class="db-legend-item"><span class="db-legend-dot" style="background:#3b82f6"></span> صور (<?= $stats['gallery_img'] ?>)</div>
                        <div class="db-legend-item"><span class="db-legend-dot" style="background:#f59e0b"></span> فيديو (<?= $stats['gallery_vid'] ?>)</div>
                    </div>
                </div>
            </div>

            <!-- Login Attempts -->
            <div class="db-panel">
                <div class="db-panel-head">
                    <h3 class="db-panel-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        سجل الدخول
                    </h3>
                </div>
                <div class="db-list">
                    <?php if (!$recent_logins): ?>
                        <div style="text-align:center; padding:20px; color:#94a3b8;">لا يوجد سجل.</div>
                    <?php else: ?>
                        <?php foreach ($recent_logins as $log): ?>
                            <div class="db-list-item">
                                <div class="db-list-avatar" style="background:<?= $log['success'] ? '#eff6ff' : '#fef2f2' ?>; color:<?= $log['success'] ? '#3b82f6' : '#ef4444' ?>;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div class="db-list-content">
                                    <div style="display:flex; justify-content:space-between;">
                                        <span class="db-list-title"><?= e($log['username']) ?></span>
                                        <span class="db-list-meta"><?= e(format_date($log['attempted_at'], 'm/d H:i')) ?></span>
                                    </div>
                                    <span class="db-list-sub" dir="ltr" style="text-align:right;">IP: <?= e($log['ip_address']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>