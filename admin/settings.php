<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$currentPage = 'settings';
$pageTitle = 'الإعدادات';

$allowedKeys = [
    'site_name','site_tagline','site_phone','site_whatsapp','site_email',
    'site_city','site_region','site_country',
    'hero_tag','hero_name_1','hero_name_2','hero_location','hero_h1_sub','hero_desc_1','hero_desc_2',
    'about_lead','about_p1','about_p2',
    'meta_title','meta_description','meta_keywords','geo_region','geo_placename',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $posted = $_POST['settings'] ?? [];
    foreach ($posted as $k => $v) {
        if (in_array($k, $allowedKeys, true)) {
            set_setting($k, trim((string)$v));
        }
    }
    flash('ok', 'تم حفظ الإعدادات بنجاح');
    redirect(SITE_URL . '/admin/settings.php');
}

$flashes = get_flashes();
include __DIR__ . '/includes/header.php';

// helper لجلب قيمة
function v($k) { return e(setting($k, '')); }
?>
<link rel="stylesheet" href="<?= SITE_URL ?>/admin/assets/css/settings.css">

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
<div class="set-page-head">
    <div class="set-page-head-txt">
        <span class="set-eyebrow">إعدادات النظام</span>
        <h1>الإعدادات العامة</h1>
        <p>التحكم الكامل في نصوص الموقع، بيانات التواصل، وتهيئة محركات البحث (SEO)</p>
    </div>
    <div class="set-page-head-actions">
        <!-- زرار الحفظ العلوي -->
        <button type="submit" form="settingsForm" class="set-btn set-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>
            حفظ التغييرات
        </button>
    </div>
</div>

<form method="post" id="settingsForm">
    <?= csrf_field() ?>

    <!-- Tabs -->
    <div class="set-tabs-wrap">
        <div class="set-tabs">
            <button type="button" class="set-tab on" data-tab="general">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                عام
            </button>
            <button type="button" class="set-tab" data-tab="contact">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                التواصل
            </button>
            <button type="button" class="set-tab" data-tab="hero">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"/></svg>
                الصفحة الرئيسية (Hero)
            </button>
            <button type="button" class="set-tab" data-tab="about">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                من نحن
            </button>
            <button type="button" class="set-tab" data-tab="seo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                SEO
            </button>
        </div>
    </div>

    <!-- Panes -->
    <div class="set-panes">
        
        <!-- General -->
        <div class="set-pane on" data-pane="general">
            <div class="set-card">
                <div class="set-card-h">
                    <div class="set-card-ic set-card-ic-mint">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                    <div>
                        <h3>معلومات الموقع الأساسية</h3>
                        <p>الاسم، الشعار الفرعي، والموقع الجغرافي</p>
                    </div>
                </div>
                <div class="set-card-b">
                    <div class="set-form-grid">
                        <div class="set-field">
                            <label class="set-label">اسم الموقع</label>
                            <input type="text" name="settings[site_name]" class="set-input" value="<?= v('site_name') ?>">
                        </div>
                        <div class="set-field">
                            <label class="set-label">الشعار الفرعي (Tagline)</label>
                            <input type="text" name="settings[site_tagline]" class="set-input" value="<?= v('site_tagline') ?>">
                        </div>
                        <div class="set-field">
                            <label class="set-label">المدينة</label>
                            <input type="text" name="settings[site_city]" class="set-input" value="<?= v('site_city') ?>">
                        </div>
                        <div class="set-field">
                            <label class="set-label">المنطقة</label>
                            <input type="text" name="settings[site_region]" class="set-input" value="<?= v('site_region') ?>">
                        </div>
                        <div class="set-field">
                            <label class="set-label">الدولة (رمز ISO)</label>
                            <input type="text" name="settings[site_country]" class="set-input" value="<?= v('site_country') ?>" maxlength="2" dir="ltr">
                            <small class="set-help">مثال: SA</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="set-pane" data-pane="contact">
            <div class="set-card">
                <div class="set-card-h">
                    <div class="set-card-ic set-card-ic-cop">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <div>
                        <h3>بيانات التواصل</h3>
                        <p>أرقام الهواتف والبريد الإلكتروني للعملاء</p>
                    </div>
                </div>
                <div class="set-card-b">
                    <div class="set-form-grid">
                        <div class="set-field">
                            <label class="set-label">رقم الهاتف (يظهر للعميل)</label>
                            <input type="text" name="settings[site_phone]" class="set-input" dir="ltr" value="<?= v('site_phone') ?>">
                        </div>
                        <div class="set-field">
                            <label class="set-label">رقم واتساب (بصيغة دولية بدون +)</label>
                            <input type="text" name="settings[site_whatsapp]" class="set-input" dir="ltr" value="<?= v('site_whatsapp') ?>">
                            <small class="set-help">مثال: 966507023985</small>
                        </div>
                        <div class="set-field">
                            <label class="set-label">البريد الإلكتروني</label>
                            <input type="email" name="settings[site_email]" class="set-input" dir="ltr" value="<?= v('site_email') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hero -->
        <div class="set-pane" data-pane="hero">
            <div class="set-card">
                <div class="set-card-h">
                    <div class="set-card-ic set-card-ic-mint">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"/></svg>
                    </div>
                    <div>
                        <h3>نصوص الصفحة الرئيسية (Hero)</h3>
                        <p>النصوص التي تظهر في أعلى الموقع</p>
                    </div>
                </div>
                <div class="set-card-b">
                    <div class="set-form-grid">
                        <div class="set-field full">
                            <label class="set-label">الشريط العلوي (Tag)</label>
                            <input type="text" name="settings[hero_tag]" class="set-input" value="<?= v('hero_tag') ?>">
                        </div>
                        <div class="set-field">
                            <label class="set-label">الاسم - الكلمة الأولى</label>
                            <input type="text" name="settings[hero_name_1]" class="set-input" value="<?= v('hero_name_1') ?>">
                        </div>
                        <div class="set-field">
                            <label class="set-label">الاسم - الكلمة الثانية</label>
                            <input type="text" name="settings[hero_name_2]" class="set-input" value="<?= v('hero_name_2') ?>">
                        </div>
                        <div class="set-field full">
                            <label class="set-label">سطر الموقع</label>
                            <input type="text" name="settings[hero_location]" class="set-input" value="<?= v('hero_location') ?>">
                        </div>
                        <div class="set-field full">
                            <label class="set-label">العنوان الفرعي (H1 Sub)</label>
                            <input type="text" name="settings[hero_h1_sub]" class="set-input" value="<?= v('hero_h1_sub') ?>">
                        </div>
                        <div class="set-field full">
                            <label class="set-label">الفقرة الأولى</label>
                            <textarea name="settings[hero_desc_1]" class="set-input set-textarea" rows="4"><?= v('hero_desc_1') ?></textarea>
                        </div>
                        <div class="set-field full">
                            <label class="set-label">الفقرة الثانية</label>
                            <textarea name="settings[hero_desc_2]" class="set-input set-textarea" rows="4"><?= v('hero_desc_2') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- About -->
        <div class="set-pane" data-pane="about">
            <div class="set-card">
                <div class="set-card-h">
                    <div class="set-card-ic set-card-ic-cop">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <h3>نصوص من نحن</h3>
                        <p>الفقرات التعريفية بالشركة أو الموقع</p>
                    </div>
                </div>
                <div class="set-card-b">
                    <div class="set-form-grid">
                        <div class="set-field full">
                            <label class="set-label">الفقرة التعريفية الرئيسية</label>
                            <textarea name="settings[about_lead]" class="set-input set-textarea" rows="4"><?= v('about_lead') ?></textarea>
                        </div>
                        <div class="set-field full">
                            <label class="set-label">الفقرة 1</label>
                            <textarea name="settings[about_p1]" class="set-input set-textarea" rows="5"><?= v('about_p1') ?></textarea>
                        </div>
                        <div class="set-field full">
                            <label class="set-label">الفقرة 2</label>
                            <textarea name="settings[about_p2]" class="set-input set-textarea" rows="5"><?= v('about_p2') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO -->
        <div class="set-pane" data-pane="seo">
            <div class="set-card">
                <div class="set-card-h">
                    <div class="set-card-ic set-card-ic-mint">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </div>
                    <div>
                        <h3>إعدادات محركات البحث (SEO)</h3>
                        <p>تحسين ظهور الموقع في نتائج البحث</p>
                    </div>
                </div>
                <div class="set-card-b">
                    <div class="set-form-grid">
                        <div class="set-field full">
                            <label class="set-label">Meta Title</label>
                            <input type="text" name="settings[meta_title]" class="set-input" value="<?= v('meta_title') ?>">
                        </div>
                        <div class="set-field full">
                            <label class="set-label">Meta Description</label>
                            <textarea name="settings[meta_description]" class="set-input set-textarea" rows="3"><?= v('meta_description') ?></textarea>
                        </div>
                        <div class="set-field full">
                            <label class="set-label">Meta Keywords</label>
                            <textarea name="settings[meta_keywords]" class="set-input set-textarea" rows="3"><?= v('meta_keywords') ?></textarea>
                        </div>
                        <div class="set-field">
                            <label class="set-label">Geo Region</label>
                            <input type="text" name="settings[geo_region]" class="set-input" value="<?= v('geo_region') ?>">
                        </div>
                        <div class="set-field">
                            <label class="set-label">Geo Placename</label>
                            <input type="text" name="settings[geo_placename]" class="set-input" value="<?= v('geo_placename') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bottom Save Action -->
    <div class="set-bottom-actions">
        <button type="submit" class="set-btn set-btn-primary set-btn-block">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>
            حفظ جميع التغييرات
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabs = document.querySelectorAll('.set-tab');
    var panes = document.querySelectorAll('.set-pane');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs and panes
            tabs.forEach(function(t) { t.classList.remove('on'); });
            panes.forEach(function(p) { p.classList.remove('on'); });
            
            // Add active class to clicked tab and corresponding pane
            this.classList.add('on');
            var targetPane = document.querySelector('.set-pane[data-pane="' + this.dataset.tab + '"]');
            if (targetPane) {
                targetPane.classList.add('on');
            }
        });
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>