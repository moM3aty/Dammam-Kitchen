<?php
/**
 * admin/generate_video_thumb.php
 * يولّد thumbnail من أول إطار في الفيديو باستخدام ffmpeg
 * شغّله مرة واحدة: /admin/generate_video_thumb.php?run=1
 */

require_once __DIR__ . '/includes/init.php';
require_login();

header('Content-Type: text/html; charset=utf-8');

$run = isset($_GET['run']) && $_GET['run'] === '1';

if (!$run) {
    echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>مولّد Thumbnails</title>
    <style>body{font-family:sans-serif;background:#0a1214;color:#fff;padding:50px;text-align:center;min-height:100vh}
    .box{max-width:640px;margin:0 auto;background:rgba(255,255,255,.05);border:1px solid rgba(111,242,208,.3);border-radius:20px;padding:40px}
    h1{color:#6ff2d0}.warn{margin-top:20px;padding:14px;background:rgba(224,145,90,.15);border-radius:10px;color:#f3c29b;font-size:14px}
    a{display:inline-block;margin-top:20px;padding:14px 32px;background:#6ff2d0;color:#0a1214;border-radius:10px;text-decoration:none;font-weight:800}
    code{background:rgba(0,0,0,.3);padding:2px 8px;border-radius:5px;font-size:13px;direction:ltr;display:inline-block}
    </style></head><body><div class="box">
    <h1>🎬 مولّد Thumbnails للفيديوهات</h1>
    <p style="color:#c8dad7;margin-top:15px">سيتم توليد صورة مصغرة لكل فيديو في المعرض لتسريع التحميل.</p>
    <p style="color:#c8dad7;margin-top:10px"><b>متطلبات:</b> ffmpeg مثبت على السيرفر. للتحقق: <code>which ffmpeg</code></p>
    <a href="?run=1">🚀 بدء التوليد</a>
    <div class="warn">⚠️ إذا لم يكن ffmpeg متاحاً، سنستخدم صورة placeholder</div>
    </div></body></html>';
    exit;
}

/* فحص ffmpeg */
$ffmpeg = trim(@shell_exec('which ffmpeg 2>/dev/null') ?: '');
$hasFfmpeg = $ffmpeg !== '' && is_executable($ffmpeg);

if (!$hasFfmpeg) {
    echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>ffmpeg غير متاح</title>
    <style>body{font-family:sans-serif;background:#0a1214;color:#fff;padding:50px;text-align:center;min-height:100vh}
    .box{max-width:640px;margin:0 auto;background:rgba(255,255,255,.05);border:1px solid rgba(111,242,208,.3);border-radius:20px;padding:40px}
    h1{color:#6ff2d0}h2{color:#f3c29b;margin:20px 0}
    code{background:rgba(0,0,0,.3);padding:3px 10px;border-radius:5px;font-size:14px;direction:ltr;display:block;margin:10px 0}
    a{display:inline-block;margin-top:20px;padding:12px 28px;background:#6ff2d0;color:#0a1214;border-radius:10px;text-decoration:none;font-weight:800}
    </style></head><body><div class="box">
    <h1>⚠️ ffmpeg غير مثبت</h1>
    <h2>كيف تثبته على Hostinger؟</h2>
    <p>Hostinger عادةً يمنع تثبيت الحزم عبر SSH على الخطط المشتركة. لكن لديك بديلان:</p>
    <p style="margin-top:20px;text-align:right"><b>البديل 1:</b> ارسل ملف ffmpeg ثنائي (binary) عبر FTP إلى مجلد ثم حدّد المسار</p>
    <p style="text-align:right;margin-top:10px"><b>البديل 2 (الأسهل):</b> استخدم صورة placeholder موحدة لكل الفيديوهات (سيتم تلقائياً)</p>
    <a href="?run=1&fallback=1">🚀 استخدم placeholder</a>
    </div></body></html>';
    exit;
}

$fallback = isset($_GET['fallback']) && $_GET['fallback'] === '1';
$thumbsDir = __DIR__ . '/../uploads/gallery/thumbs';
if (!is_dir($thumbsDir)) @mkdir($thumbsDir, 0755, true);

$videos = $pdo->query("SELECT id, file_path FROM gallery WHERE type='vid'")->fetchAll();
$generated = 0;
$skipped = 0;
$failed = 0;
$log = [];

foreach ($videos as $v) {
    $source = __DIR__ . '/../' . $v['file_path'];
    $thumbName = 'vid_' . $v['id'] . '.jpg';
    $thumbPath = $thumbsDir . '/' . $thumbName;

    if (is_file($thumbPath)) { $skipped++; continue; }

    if (!is_file($source)) {
        $failed++;
        $log[] = "❌ #{$v['id']} — الملف غير موجود: {$v['file_path']}";
        continue;
    }

    // جرّب ffmpeg
    $cmd = escapeshellcmd($ffmpeg) . ' -i ' . escapeshellarg($source)
         . ' -ss 00:00:02 -vframes 1 -vf "scale=640:-2" -q:v 4 -y '
         . escapeshellarg($thumbPath) . ' 2>&1';

    @shell_exec($cmd);

    if (is_file($thumbPath) && filesize($thumbPath) > 1000) {
        $generated++;
        $log[] = "✅ #{$v['id']} — {$thumbName} (" . round(filesize($thumbPath)/1024) . " KB)";
    } else {
        $failed++;
        $log[] = "❌ #{$v['id']} — فشل استخراج الصورة";
        // حذف الملف الفارغ
        if (is_file($thumbPath)) @unlink($thumbPath);
    }
}

echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>النتيجة</title>
<style>body{font-family:sans-serif;background:#0a1214;color:#fff;padding:40px;min-height:100vh}
.box{max-width:800px;margin:0 auto;background:rgba(255,255,255,.05);border:1px solid rgba(111,242,208,.3);border-radius:20px;padding:30px}
h1{color:#6ff2d0;text-align:center}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:20px 0}
.stat{padding:16px;background:rgba(111,242,208,.1);border-radius:10px;text-align:center}
.stat b{display:block;font-size:28px;color:#6ff2d0}
.stat small{font-size:12px;color:#c8dad7}
.log{max-height:400px;overflow-y:auto;background:rgba(0,0,0,.3);padding:15px;border-radius:10px;font-family:monospace;font-size:12px;direction:ltr;text-align:left}
.log div{padding:3px 0;border-bottom:1px solid rgba(255,255,255,.05)}
a{display:inline-block;margin-top:20px;padding:12px 28px;background:#6ff2d0;color:#0a1214;border-radius:10px;text-decoration:none;font-weight:800}
</style></head><body><div class="box">
<h1>✅ تم التوليد</h1>
<div class="stats">
    <div class="stat"><b>' . $generated . '</b><small>تم التوليد</small></div>
    <div class="stat"><b>' . $skipped . '</b><small>موجود مسبقاً</small></div>
    <div class="stat"><b>' . $failed . '</b><small>فشل</small></div>
</div>
<div class="log">' . implode('', array_map(fn($l) => "<div>$l</div>", $log)) . '</div>
<a href="' . SITE_URL . '/admin/gallery.php">← عودة للمعرض</a>
</div></body></html>';