<?php
// index.php — الصفحة الرئيسية الديناميكية
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

// ============================================================
// تحميل الإعدادات
// ============================================================
$siteName      = setting('site_name', 'شبح المطابخ');
$siteTagline   = setting('site_tagline', 'الدمام الشرقية');
$sitePhone     = setting('site_phone', '0507023985');
$siteWhatsapp  = setting('site_whatsapp', '966507023985');
$siteEmail     = setting('site_email', '');

$metaTitle     = setting('meta_title', 'شبح المطابخ | تفصيل مطابخ ألمنيوم وخشب وتغيير رخام وصيانة مطابخ في الدمام والمنطقة الشرقية');
$metaDesc      = setting('meta_description', 'شبح المطابخ في الدمام – المنطقة الشرقية: تفصيل مطابخ ألمنيوم وخشب، تغيير رخام المطابخ، وصيانة جميع أنواع المطابخ. اتصل 0507023985');
$metaKeywords  = setting('meta_keywords', 'شبح المطابخ, تفصيل مطابخ الدمام, مطابخ ألمنيوم, مطابخ خشب');
$geoRegion     = setting('geo_region', 'SA-04');
$geoPlacename  = setting('geo_placename', 'الدمام - المنطقة الشرقية');

$heroTag       = setting('hero_tag', '📍 الدمام · المنطقة الشرقية · المملكة العربية السعودية');
$heroName1     = setting('hero_name_1', 'شبح');
$heroName2     = setting('hero_name_2', 'المطابخ');
$heroLocation  = setting('hero_location', 'الدمام الشرقية');
$heroH1Sub     = setting('hero_h1_sub', 'تفصيل مطابخ ألمنيوم وخشب، وتغيير رخام، وصيانة جميع أنواع المطابخ في الدمام');
$heroDesc1     = setting('hero_desc_1', '');
$heroDesc2     = setting('hero_desc_2', '');

$aboutLead     = setting('about_lead', '');
$aboutP1       = setting('about_p1', '');
$aboutP2       = setting('about_p2', '');

// ============================================================
// تحميل البيانات من قاعدة البيانات
// ============================================================
$services      = $pdo->query("SELECT * FROM services WHERE is_active=1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$materials     = $pdo->query("SELECT * FROM materials WHERE is_active=1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$kitchenTypes  = $pdo->query("SELECT * FROM kitchen_types WHERE is_active=1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$galleryItems  = $pdo->query("SELECT * FROM gallery WHERE is_active=1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$steps         = $pdo->query("SELECT * FROM steps WHERE is_active=1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$whyUs         = $pdo->query("SELECT * FROM why_us WHERE is_active=1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$aboutValues   = $pdo->query("SELECT * FROM about_values WHERE is_active=1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$areas         = $pdo->query("SELECT * FROM areas WHERE is_active=1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$testimonials  = $pdo->query("SELECT * FROM testimonials WHERE is_active=1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$faqs          = $pdo->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order ASC, id ASC")->fetchAll();

// ============================================================
// JSON-LD
// ============================================================
$offerCatalog = [];
foreach ($services as $svc) {
    $offerCatalog[] = [
        '@type' => 'Offer',
        'itemOffered' => ['@type' => 'Service', 'name' => $svc['title']]
    ];
}

$areaServed = [];
foreach (array_slice($areas, 0, 6) as $a) {
    $areaServed[] = ['@type' => 'City', 'name' => $a['name']];
}
$areaServed[] = ['@type' => 'AdministrativeArea', 'name' => setting('site_region', 'المنطقة الشرقية')];

$jsonLd = [
    '@context'        => 'https://schema.org',
    '@type'           => 'HomeAndConstructionBusiness',
    'name'            => $siteName,
    'telephone'       => '+' . $siteWhatsapp,
    'description'     => $metaDesc,
    'address'         => [
        '@type'           => 'PostalAddress',
        'addressLocality' => setting('site_city', 'الدمام'),
        'addressRegion'   => setting('site_region', 'المنطقة الشرقية'),
        'addressCountry'  => setting('site_country', 'SA')
    ],
    'areaServed'      => $areaServed,
    'hasOfferCatalog' => [
        '@type'            => 'OfferCatalog',
        'name'             => 'خدمات ' . $siteName,
        'itemListElement'  => $offerCatalog
    ]
];
$jsonLdJson = json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// ============================================================
// Helper
// ============================================================
function sk_num($n) { return str_pad((string)$n, 2, '0', STR_PAD_LEFT); }

$materialsTitle = 'الخامات التي نتعامل معها';
$materialsDesc  = 'يختلف اختيار الخامة بحسب المكان والاستخدام والميزانية، ونساعدك في اختيار الأنسب بعد المعاينة.';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($metaTitle) ?></title>
<meta name="description" content="<?= e($metaDesc) ?>">
<meta name="keywords" content="<?= e($metaKeywords) ?>">
<meta name="robots" content="index, follow, max-image-preview:large">
<meta name="geo.region" content="<?= e($geoRegion) ?>">
<meta name="geo.placename" content="<?= e($geoPlacename) ?>">
<meta name="theme-color" content="#0a1214">
<meta property="og:title" content="<?= e($siteName) ?> | تفصيل مطابخ ألمنيوم وخشب وتغيير رخام وصيانة في الدمام">
<meta property="og:description" content="<?= e($metaDesc) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_AR">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Changa:wght@500;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<noscript><style>.rv{opacity:1!important;transform:none!important}</style></noscript>
<script type="application/ld+json">
<?= $jsonLdJson ?>
</script>
</head>
<body>

<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <symbol id="wa" viewBox="0 0 24 24"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></symbol>
  <symbol id="ph" viewBox="0 0 24 24"><path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></symbol>
  <symbol id="pin" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></symbol>
  <symbol id="pl" viewBox="0 0 24 24"><path fill="currentColor" d="M8 5v14l11-7z"/></symbol>
  <symbol id="kt" viewBox="0 0 64 64">
    <g fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
      <rect x="6" y="6" width="52" height="18" rx="3"/>
      <path d="M32 6V24"/>
      <path d="M4 35H60"/>
      <rect x="8" y="39" width="48" height="19" rx="3"/>
      <path d="M24 39V58M40 39V58"/>
    </g>
    <rect x="38" y="25.5" width="14" height="8" rx="2.5" fill="currentColor"/>
    <g fill="currentColor"><circle cx="27" cy="15" r="2"/><circle cx="37" cy="15" r="2"/><circle cx="20" cy="49" r="2"/><circle cx="32" cy="49" r="2"/><circle cx="44" cy="49" r="2"/></g>
  </symbol>
  <symbol id="ut" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3v6a2.5 2.5 0 0 0 2.5 2.5V21M4.5 3v6M9.5 3v6"/><path d="M17 3c-2.2 1.8-3.2 4.4-3.2 8H17v10"/></g></symbol>
  <symbol id="pt" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11h16v7a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3z"/><path d="M2 12.5h2M20 12.5h2"/><path d="M6 11a6 3.2 0 0 1 12 0"/><path d="M12 5.5v2.3"/></g></symbol>
</svg>

<header class="hd" id="hdr">
  <div class="wrap hd-in">
    <a href="#home" class="logo" aria-label="<?= e($siteName) ?> - الرئيسية">
      <span class="lg-b"><svg class="ic" aria-hidden="true" focusable="false"><use href="#kt"/></svg></span>
      <span class="lg-t"><b><?= e($siteName) ?></b><small><?= e($siteTagline) ?></small></span>
    </a>

    <nav class="nav" id="nav" aria-label="القائمة الرئيسية">
      <a href="#home" class="lnk act">الرئيسية</a>
      <a href="#about" class="lnk">من نحن</a>
      <a href="#services" class="lnk">خدماتنا</a>
      <a href="#gallery" class="lnk">معرض الأعمال</a>
      <a href="#how" class="lnk">كيف نعمل</a>
      <a href="#why" class="lnk">لماذا نحن</a>
      <a href="#reviews" class="lnk">آراء العملاء</a>
      <a href="#faq" class="lnk">أسئلة شائعة</a>
      <a href="#contact" class="lnk">تواصل معنا</a>
    </nav>

    <a class="call" href="tel:<?= e($sitePhone) ?>"><svg class="ic"><use href="#ph"/></svg><span>اتصل الآن</span></a>

    <button class="burger" id="burger" type="button" aria-label="فتح القائمة" aria-expanded="false" aria-controls="nav">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>
<div class="veil" id="veil"></div>

<main>

  <section class="hero" id="home">
    <div class="hz hz1"></div>
    <div class="hz hz2">
      <svg class="vein" viewBox="0 0 800 800" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
        <g fill="none" stroke="#6ff2d0" stroke-linecap="round">
          <path d="M-20 120C120 80 200 200 340 160S560 60 640 140 780 120 840 80" stroke-opacity=".12" stroke-width="1.6"/>
          <path d="M-20 300C100 260 220 360 360 320S580 240 680 320 800 300 840 260" stroke-opacity=".09" stroke-width="1.2"/>
          <path d="M-20 520C140 480 240 600 380 560S600 460 700 540 800 520 840 480" stroke-opacity=".12" stroke-width="1.6"/>
          <path d="M-20 700C120 660 260 760 400 720S620 640 720 720 800 700 840 660" stroke-opacity=".08" stroke-width="1.2"/>
          <path d="M200 -20C240 120 160 240 220 380S300 600 260 820" stroke-opacity=".08" stroke-width="1.2"/>
          <path d="M560 -20C520 140 620 260 560 400S520 640 580 820" stroke-opacity=".1" stroke-width="1.4"/>
        </g>
      </svg>
    </div>
    <div class="hglow g1"></div>
    <div class="hglow g2"></div>

    <svg class="wsp w1" aria-hidden="true" focusable="false"><use href="#pt"/></svg>
    <svg class="wsp w2" aria-hidden="true" focusable="false"><use href="#ut"/></svg>
    <svg class="wsp w3" aria-hidden="true" focusable="false"><use href="#kt"/></svg>
    <svg class="wsp w4" aria-hidden="true" focusable="false"><use href="#pt"/></svg>
    <svg class="wsp w5" aria-hidden="true" focusable="false"><use href="#ut"/></svg>

    <div class="wrap hero-grid">
      <div class="hero-in">
        <p class="tag"><?= e($heroTag) ?></p>

        <h1>
          <span class="nm"><em><?= e($heroName1) ?></em> <em><?= e($heroName2) ?></em></span>
          <span class="nl"><?= e($heroLocation) ?></span>
          <span class="h1s"><?= e($heroH1Sub) ?></span>
        </h1>

        <p class="hero-p"><?= e($heroDesc1) ?></p>
        <p class="hero-p hero-p2"><?= e($heroDesc2) ?></p>

        <div class="btns">
          <a href="#services" class="btn btn-mint">اكتشف خدماتنا</a>
          <a href="#gallery" class="btn btn-ghost">شاهد أعمالنا</a>
        </div>

        <div class="qc">
          <a href="tel:<?= e($sitePhone) ?>" class="qc-a"><i class="q-ph"><svg class="ic"><use href="#ph"/></svg></i><span><small>اتصال</small><b dir="ltr"><?= e($sitePhone) ?></b></span></a>
          <a href="https://wa.me/<?= e($siteWhatsapp) ?>" target="_blank" rel="noopener" class="qc-a"><i class="q-wa"><svg class="ic"><use href="#wa"/></svg></i><span><small>واتساب</small><b dir="ltr"><?= e($sitePhone) ?></b></span></a>
        </div>

        <ul class="chips">
          <li>🪟 مطابخ ألمنيوم</li>
          <li>🪵 مطابخ خشب</li>
          <li>🪨 تغيير رخام</li>
          <li>🛠️ صيانة مطابخ</li>
          <li>📐 قياس ومعاينة</li>
        </ul>

        <p class="seo">تفصيل مطابخ الدمام، مطابخ ألمنيوم، مطابخ خشب مودرن وكلاسيك، تغيير رخام المطبخ، رخام وجرانيت وكوارتز، صيانة وتجديد المطابخ، تغيير مفصلات وأبواب وأدراج المطابخ، في الدمام والخبر والظهران والقطيف والجبيل والأحساء وجميع مناطق المنطقة الشرقية.</p>
      </div>

      <div class="art-w" id="artW">
        <div class="scn-c">
          <svg class="scn" viewBox="0 0 460 440" role="img" aria-label="رسم متحرك لمطبخ حديث">
            <defs>
              <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#14323a"/><stop offset="1" stop-color="#0b171a"/></linearGradient>
              <radialGradient id="gl"><stop offset="0" stop-color="#ffe9b8" stop-opacity=".75"/><stop offset="1" stop-color="#ffe9b8" stop-opacity="0"/></radialGradient>
            </defs>
            <rect width="460" height="440" fill="url(#bg)"/>
            <path d="M0 190H460M0 222H460M60 160V250M140 160V250M220 160V250M300 160V250M380 160V250" stroke="#6ff2d0" stroke-opacity=".07"/>
            <rect x="24" y="56" width="118" height="116" rx="8" fill="#1f3d44" stroke="#6ff2d0" stroke-opacity=".35"/>
            <rect x="318" y="56" width="118" height="116" rx="8" fill="#1f3d44" stroke="#6ff2d0" stroke-opacity=".35"/>
            <path d="M83 56V172M24 114H142M377 56V172M318 114H436" stroke="#6ff2d0" stroke-opacity=".25"/>
            <rect x="212" y="26" width="36" height="72" fill="#24454d"/>
            <path d="M170 152L290 152L270 96L190 96Z" fill="#2c5058" stroke="#6ff2d0" stroke-opacity=".4"/>
            <rect class="hlt" x="178" y="152" width="104" height="5" rx="2.5" fill="#6ff2d0"/>

            <g class="lmp"><path d="M83 0V74" stroke="#b7fbe9" stroke-opacity=".6" stroke-width="2"/><path d="M60 100Q60 74 83 74T106 100Z" fill="#e0915a"/><ellipse cx="83" cy="101" rx="23" ry="4" fill="#f3c29b"/><circle cx="83" cy="107" r="6" fill="#fff6d6"/></g>
            <g class="lmp l2"><path d="M377 0V74" stroke="#b7fbe9" stroke-opacity=".6" stroke-width="2"/><path d="M354 100Q354 74 377 74T400 100Z" fill="#e0915a"/><ellipse cx="377" cy="101" rx="23" ry="4" fill="#f3c29b"/><circle cx="377" cy="107" r="6" fill="#fff6d6"/></g>
            <circle class="ghw" cx="83" cy="112" r="46" fill="url(#gl)"/>
            <circle class="ghw g2b" cx="377" cy="112" r="46" fill="url(#gl)"/>

            <circle class="pt p1" cx="70" cy="220" r="3" fill="#6ff2d0"/>
            <circle class="pt p2" cx="300" cy="230" r="2.5" fill="#b7fbe9"/>
            <circle class="pt p3" cx="410" cy="210" r="3" fill="#e0915a"/>
            <circle class="pt p4" cx="160" cy="240" r="2" fill="#6ff2d0"/>
            <rect x="8" y="250" width="444" height="22" rx="4" fill="#eef5f3"/>
            <path d="M30 260Q70 252 100 262T180 260T270 266T370 258T440 262" fill="none" stroke="#a8bdb8" stroke-width="1.6"/>
            <rect x="8" y="272" width="444" height="134" fill="#14303a"/>
            <rect class="gld" x="14" y="280" width="136" height="118" rx="6" fill="#6ff2d0"/>
            <g class="dor">
              <rect x="14" y="280" width="136" height="118" rx="6" fill="#244b53" stroke="#6ff2d0" stroke-opacity=".4"/>
              <rect x="132" y="322" width="5" height="36" rx="2.5" fill="#b7fbe9"/>
            </g>
            <rect x="158" y="280" width="144" height="36" rx="6" fill="#244b53" stroke="#6ff2d0" stroke-opacity=".3"/>
            <rect x="158" y="320" width="144" height="36" rx="6" fill="#244b53" stroke="#6ff2d0" stroke-opacity=".3"/>
            <rect x="158" y="360" width="144" height="38" rx="6" fill="#244b53" stroke="#6ff2d0" stroke-opacity=".3"/>
            <rect x="218" y="295" width="24" height="4" rx="2" fill="#b7fbe9"/>
            <rect x="218" y="335" width="24" height="4" rx="2" fill="#b7fbe9"/>
            <rect x="218" y="376" width="24" height="4" rx="2" fill="#b7fbe9"/>
            <rect x="310" y="280" width="136" height="118" rx="6" fill="#244b53" stroke="#6ff2d0" stroke-opacity=".4"/>
            <rect x="320" y="322" width="5" height="36" rx="2.5" fill="#b7fbe9"/>
            <rect x="0" y="406" width="460" height="34" fill="#0a1214"/>
            <rect x="338" y="224" width="56" height="26" rx="6" fill="#cfe0dc"/>
            <rect x="334" y="216" width="64" height="9" rx="4" fill="#e0915a"/>
            <rect x="360" y="210" width="12" height="7" rx="3" fill="#b9693a"/>
            <path class="stm s1" d="M352 206Q344 192 352 178T352 150" fill="none" stroke="#fff" stroke-opacity=".5" stroke-width="3" stroke-linecap="round"/>
            <path class="stm s2" d="M366 206Q358 192 366 178T366 150" fill="none" stroke="#fff" stroke-opacity=".5" stroke-width="3" stroke-linecap="round"/>
            <path class="stm s3" d="M380 206Q372 192 380 178T380 150" fill="none" stroke="#fff" stroke-opacity=".5" stroke-width="3" stroke-linecap="round"/>
          </svg>
        </div>
        <span class="fl f1">🪟 ألمنيوم</span>
        <span class="fl f2">🪵 خشب</span>
        <span class="fl f3">🪨 رخام</span>
      </div>
    </div>

    <div class="mq" aria-hidden="true">
      <div class="mq-t">
        <div class="mq-g">
          <span><?= e($siteName) ?></span><span>تفصيل مطابخ الدمام</span><span>مطابخ ألمنيوم</span><span>مطابخ خشب</span><span>تغيير رخام</span><span>صيانة مطابخ</span><span>الخبر</span><span>الظهران</span><span>القطيف</span><span>الجبيل</span><span>الأحساء</span><span>المنطقة الشرقية</span>
        </div>
        <div class="mq-g">
          <span><?= e($siteName) ?></span><span>تفصيل مطابخ الدمام</span><span>مطابخ ألمنيوم</span><span>مطابخ خشب</span><span>تغيير رخام</span><span>صيانة مطابخ</span><span>الخبر</span><span>الظهران</span><span>القطيف</span><span>الجبيل</span><span>الأحساء</span><span>المنطقة الشرقية</span>
        </div>
      </div>
    </div>
  </section>

  <section class="sec s-marb about" id="about" style="--prev:#0a1214">
    <div class="bl bl1"></div>
    <div class="wrap ab-grid">
      <div class="ab-vis rv z">
        <div class="kc">
          <svg viewBox="0 0 320 340" role="img" aria-label="رسم مخطط لمطبخ">
            <rect x="10" y="10" width="300" height="320" rx="22" fill="#0f1c1f"/>
            <g fill="none" stroke="#6ff2d0" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
              <path class="ln" pathLength="1" d="M34 40H286V120H34Z" style="--d:.1s"/>
              <path class="ln" pathLength="1" d="M34 40V120M110 40V120M186 40V120M262 40V120" style="--d:.5s"/>
              <path class="ln" pathLength="1" d="M34 160H286" stroke="#e0915a" style="--d:.9s"/>
              <path class="ln" pathLength="1" d="M34 160V300H286V160" style="--d:1.1s"/>
              <path class="ln" pathLength="1" d="M110 160V300M186 160V300" style="--d:1.5s"/>
              <path class="ln" pathLength="1" d="M60 190H84M136 190H160M212 190H236" style="--d:1.9s"/>
              <path class="ln" pathLength="1" d="M70 238H94M146 238H170M222 238H246" style="--d:2.1s"/>
              <circle class="ln" pathLength="1" cx="236" cy="82" r="14" style="--d:2.3s"/>
            </g>
          </svg>
        </div>
        <span class="badge">📍 الدمام</span>
        <span class="badge bdg2">📐 على المقاس</span>
      </div>

      <div class="ab-t">
        <span class="eb rv">من نحن</span>
        <h2 class="rv ab-h" style="--i:1">
          <span class="nm2"><em><?= e($heroName1) ?></em> <em><?= e($heroName2) ?></em></span>
          <span class="sub">الدمام الشرقية · تفصيل وصيانة وتركيب رخام المطابخ</span>
        </h2>

        <p class="ab-lead rv" style="--i:2"><?= e($aboutLead) ?></p>
        <p class="ab-p rv" style="--i:3"><?= e($aboutP1) ?></p>
        <p class="ab-p rv" style="--i:4"><?= e($aboutP2) ?></p>

        <div class="ab-vals">
          <?php foreach ($aboutValues as $vi => $val): ?>
            <div class="ab-val rv" style="--i:<?= $vi + 5 ?>"><i><?= e($val['icon']) ?></i><div><h3><?= e($val['title']) ?></h3><p><?= e($val['description']) ?></p></div></div>
          <?php endforeach; ?>
        </div>

        <a href="#services" class="btn btn-dark rv" style="--i:<?= count($aboutValues) + 5 ?>">اكتشف خدماتنا</a>
      </div>
    </div>
  </section>

  <section class="sec s-dark svs" id="services" style="--prev:#f3f6f5">
    <div class="bl bl2"></div>
    <div class="wrap">
      <div class="hd2">
        <span class="eb rv">خدماتنا</span>
        <h2 class="rv" style="--i:1">تفصيل مطابخ ألمنيوم وخشب وتغيير رخام وصيانة <em>في الدمام</em></h2>
        <p class="rv" style="--i:2">أربع خدمات تغطي مطبخك من التصميم إلى التركيب والصيانة، في الدمام والمنطقة الشرقية. اختر ما تحتاجه واكتب لنا على واتساب لنرتب القياس والمعاينة.</p>
      </div>

      <div class="bn">
        <?php foreach ($services as $si => $svc):
          $n       = $si + 1;
          $bwClass = 'bw' . (($si % 4) + 1);
          $iVal    = $si % 2;
          $features = json_or_empty($svc['features']);
          $kws      = array_filter(array_map('trim', explode(',', (string)$svc['keywords'])));
        ?>
          <article class="bc <?= $bwClass ?> rv" style="--i:<?= $iVal ?>">
            <span class="bn-no"><?= sk_num($n) ?></span>
            <div class="bn-ic"><?= e($svc['icon']) ?></div>
            <h3><?= e($svc['title']) ?></h3>
            <p><?= e($svc['description']) ?></p>
            <?php if (!empty($svc['details'])): ?>
              <p><?= e($svc['details']) ?></p>
            <?php endif; ?>
            <?php if ($features): ?>
              <ul class="tk">
                <?php foreach ($features as $feat): ?>
                  <li><?= e($feat) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
            <?php if ($kws): ?>
              <div class="kws">
                <?php foreach ($kws as $kw): ?><span><?= e($kw) ?></span><?php endforeach; ?>
              </div>
            <?php endif; ?>
            <a href="#contact" class="bn-a"><?= e($svc['button_text'] ?: 'اطلب الخدمة') ?> <span>←</span></a>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($materials): ?>
      <div class="mt-wrap">
        <div class="mt-t rv">
          <h3><?= e($materialsTitle) ?></h3>
          <p><?= e($materialsDesc) ?></p>
        </div>
        <div class="mt-g">
          <?php foreach ($materials as $mi => $mat): ?>
            <div class="mt rv" style="--i:<?= $mi ?>"><i><?= e($mat['icon']) ?></i><h4><?= e($mat['name']) ?></h4><p><?= e($mat['description']) ?></p></div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($kitchenTypes): ?>
      <div class="st rv">
        <h3>أنواع المطابخ التي نفصّلها</h3>
        <ul>
          <?php foreach ($kitchenTypes as $kt): ?><li><?= e($kt['name']) ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="sec s-marb gal" id="gallery" style="--prev:#0f1c1f">
    <div class="bl bl1"></div>
    <div class="wrap">
      <div class="hd2">
        <span class="eb rv">معرض الأعمال</span>
        <h2 class="rv" style="--i:1">لقطات من أعمالنا <em>صور وفيديوهات</em></h2>
        <p class="rv" style="--i:2">نماذج من مطابخ الألمنيوم والخشب وأعمال الرخام والصيانة. اضغط على أي صورة أو فيديو لعرضه بحجم كبير.</p>
      </div>

      <div class="gt rv" style="--i:3" role="group" aria-label="تصفية المعرض">
        <button type="button" class="gt-b on" data-f="all" aria-pressed="true">الكل</button>
        <button type="button" class="gt-b" data-f="img" aria-pressed="false">صور</button>
        <button type="button" class="gt-b" data-f="vid" aria-pressed="false">فيديو</button>
      </div>

      <div class="gl-grid" id="glGrid">
        <?php $imgIdx = 0; $vidIdx = 0; ?>
        <?php foreach ($galleryItems as $g): ?>
          <?php if ($g['type'] === 'img'): $imgIdx++; ?>
            <button type="button" class="gi rv z" data-t="img" aria-label="عرض الصورة <?= $imgIdx ?>">
              <span class="gph"></span>
              <img src="<?= e($g['file_path']) ?>" alt="<?= e($g['alt_text'] ?: 'أعمال ' . $siteName) ?>" loading="lazy" onerror="this.remove()">
            </button>
          <?php else: $vidIdx++; ?>
            <button type="button" class="gi rv z" data-t="vid" aria-label="تشغيل الفيديو <?= $vidIdx ?>">
              <span class="gph"></span>
              <video src="<?= e($g['file_path']) ?>" muted loop playsinline preload="metadata" onerror="this.remove()"></video>
              <span class="pb"><svg class="ic"><use href="#pl"/></svg></span>
            </button>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <div class="gl-go rv">
        <p>أعجبك مطبخ وتريد مثله؟ أرسل لنا صورته على واتساب.</p>
        <a href="https://wa.me/<?= e($siteWhatsapp) ?>" target="_blank" rel="noopener" class="btn btn-wa"><svg class="ic"><use href="#wa"/></svg> أرسل لنا طلبك</a>
      </div>
    </div>
  </section>

  <section class="sec s-dark how" id="how" style="--prev:#f3f6f5">
    <div class="bl bl2"></div>
    <div class="wrap hw-grid">
      <div class="hw-h">
        <span class="eb rv">كيف نعمل</span>
        <h2 class="rv" style="--i:1">ست خطوات من الفكرة إلى <em>مطبخ جاهز</em></h2>
        <p class="rv" style="--i:2">خطوات مرتبة وواضحة سواء كنت تريد مطبخاً جديداً أو تغيير رخام أو صيانة مطبخ قائم، في الدمام وجميع مدن المنطقة الشرقية.</p>
        <a href="#contact" class="btn btn-mint rv" style="--i:3">ابدأ الآن</a>
      </div>

      <div class="hw-l">
        <?php foreach ($steps as $sti => $step): ?>
          <div class="hw rv" style="--i:<?= $sti ?>"><b><?= (int)($step['step_number'] ?: ($sti + 1)) ?></b><div><h3><?= e($step['title']) ?></h3><p><?= e($step['description']) ?></p></div></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="sec s-marb why" id="why" style="--prev:#0f1c1f">
    <div class="bl bl1"></div>
    <div class="wrap">
      <div class="hd2">
        <span class="eb rv">لماذا نحن</span>
        <h2 class="rv" style="--i:1">لماذا تختار <em><?= e($siteName) ?></em> لمطبخك في الدمام</h2>
        <p class="rv" style="--i:2">لأننا نجمع التفصيل والرخام والصيانة في مكان واحد، بتواصل واضح واهتمام بالمقاسات والتفاصيل.</p>
      </div>

      <div class="wy-grid">
        <?php foreach ($whyUs as $wi => $w): ?>
          <div class="wy rv" style="--i:<?= $wi ?>"><i><?= e($w['icon']) ?></i><h3><?= e($w['title']) ?></h3><p><?= e($w['description']) ?></p></div>
        <?php endforeach; ?>
      </div>

      <?php if ($areas): ?>
      <div class="ar rv">
        <div class="ar-t">
          <h3>نصل إليك في الدمام وجميع المنطقة الشرقية</h3>
          <p>مقرنا في الدمام، ونرتب موعد المعاينة والقياس في أحياء الدمام والمدن المجاورة بحسب موقعك.</p>
        </div>
        <ul>
          <?php foreach ($areas as $ar): ?><li><?= e($ar['name']) ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="sec s-dark rw" id="reviews" style="--prev:#f3f6f5">
    <div class="bl bl2"></div>
    <div class="wrap">
      <div class="hd2">
        <span class="eb rv">آراء العملاء</span>
        <h2 class="rv" style="--i:1">ماذا قال <em>عملاؤنا</em> عن تجربتهم</h2>
        <p class="rv" style="--i:2">كلمات من عملاء جرّبوا تفصيل المطابخ وتغيير الرخام والصيانة.</p>
      </div>

      <div class="rw-cols">
        <?php foreach ($testimonials as $ti => $t): ?>
          <figure class="rc rv" style="--i:<?= $ti ?>">
            <div class="rc-s"><?= str_repeat('★', max(1, min(5, (int)$t['rating']))) ?></div>
            <blockquote><?= e($t['content']) ?></blockquote>
            <figcaption><span class="rc-av"><?= e($t['avatar_letter'] ?: mb_substr($t['client_name'], 0, 1)) ?></span><b><?= e($t['client_name']) ?></b></figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="sec s-marb faq" id="faq" style="--prev:#0f1c1f">
    <div class="bl bl1"></div>
    <div class="wrap">
      <div class="hd2">
        <span class="eb rv">أسئلة شائعة</span>
        <h2 class="rv" style="--i:1">إجابات على أكثر ما <em>يسأل عنه العملاء</em></h2>
        <p class="rv" style="--i:2">أسئلة متكررة عن تفصيل المطابخ وتغيير الرخام والصيانة في الدمام.</p>
      </div>

      <div class="fq-grid">
        <div class="fq-list" id="fqList">
          <?php foreach ($faqs as $fi => $f): ?>
            <details class="fq-q rv" style="--i:<?= $fi ?>"<?= $fi === 0 ? ' open' : '' ?>>
              <summary><b><?= sk_num($fi + 1) ?></b><span class="fq-t"><?= e($f['question']) ?></span><span class="fq-pm"></span></summary>
              <div class="fq-a"><p><?= e($f['answer']) ?></p></div>
            </details>
          <?php endforeach; ?>
        </div>

        <aside class="fq-side rv" style="--i:2">
          <span class="fq-k"><svg class="ic" aria-hidden="true" focusable="false"><use href="#kt"/></svg></span>
          <h3>عندك سؤال آخر؟</h3>
          <p>اكتب لنا تفاصيل مطبخك وسنساعدك في اختيار الأنسب.</p>
          <a class="btn btn-wa" href="https://wa.me/<?= e($siteWhatsapp) ?>" target="_blank" rel="noopener"><svg class="ic"><use href="#wa"/></svg> راسلنا على واتساب</a>
          <a class="btn btn-mint" href="tel:<?= e($sitePhone) ?>"><svg class="ic"><use href="#ph"/></svg> اتصل بنا</a>
        </aside>
      </div>
    </div>
  </section>

  <section class="sec s-dark ct" id="contact" style="--prev:#f3f6f5">
    <div class="bl bl2"></div>
    <div class="wrap">
      <div class="hd2">
        <span class="eb rv">تواصل معنا</span>
        <h2 class="rv" style="--i:1">احجز معاينة مطبخك <em>في الدمام والشرقية</em></h2>
        <p class="rv" style="--i:2">تواصل معنا بالطريقة التي تناسبك، أو املأ النموذج وسيفتح لك واتساب برسالة جاهزة لطلب تفصيل مطبخ أو تغيير رخام أو صيانة.</p>
      </div>

      <div class="ct-grid">
        <div class="ct-info">
          <a class="ct-c rv" style="--i:0" href="tel:<?= e($sitePhone) ?>">
            <i class="ci-ph"><svg class="ic"><use href="#ph"/></svg></i>
            <div><b>اتصال مباشر</b><span dir="ltr"><?= e($sitePhone) ?></span></div>
            <em>←</em>
          </a>
          <a class="ct-c rv" style="--i:1" href="https://wa.me/<?= e($siteWhatsapp) ?>" target="_blank" rel="noopener">
            <i class="ci-wa"><svg class="ic"><use href="#wa"/></svg></i>
            <div><b>واتساب</b><span dir="ltr"><?= e($sitePhone) ?></span></div>
            <em>←</em>
          </a>
          <div class="ct-c rv" style="--i:2">
            <i class="ci-pn"><svg class="ic"><use href="#pin"/></svg></i>
            <div><b>الدمام - المنطقة الشرقية</b><span>المملكة العربية السعودية، ونخدم جميع مدن الشرقية بالتنسيق</span></div>
          </div>
          <div class="ct-pts rv" style="--i:3">
            <span>🪟 ألمنيوم</span><span>🪵 خشب</span><span>🪨 رخام</span><span>🛠️ صيانة</span>
          </div>
        </div>

        <form class="ct-form rv" style="--i:2" id="ctForm" novalidate>
          <h3>أرسل طلبك الآن</h3>
          <p class="ct-sub">أخبرنا بما تحتاجه وسنرد عليك على واتساب.</p>

          <label class="ct-f"><span>الاسم</span><input type="text" id="ctName" name="name" placeholder="اكتب اسمك" autocomplete="name" required></label>
          <label class="ct-f"><span>رقم الجوال</span><input type="tel" id="ctPhone" name="phone" placeholder="05xxxxxxxx" inputmode="tel" autocomplete="tel" dir="ltr" required></label>
          <label class="ct-f">
            <span>المدينة</span>
            <select id="ctCity" name="city">
              <option>الدمام</option><option>الخبر</option><option>الظهران</option><option>القطيف</option><option>الجبيل</option><option>الأحساء</option><option>سيهات</option><option>صفوى</option><option>رأس تنورة</option><option>الخفجي</option><option>بقيق</option><option>منطقة أخرى</option>
            </select>
          </label>
          <label class="ct-f"><span>الحي <small>(اختياري)</small></span><input type="text" id="ctArea" name="area" placeholder="مثال: حي الشاطئ"></label>
          <label class="ct-f">
            <span>الخدمة المطلوبة</span>
            <select id="ctService" name="service">
              <option>تفصيل مطبخ ألمنيوم</option>
              <option>تفصيل مطبخ خشب</option>
              <option>تغيير رخام المطبخ</option>
              <option>صيانة مطبخ</option>
              <option>أكثر من خدمة</option>
              <option>خدمة أخرى</option>
            </select>
          </label>
          <label class="ct-f"><span>تفاصيل الطلب <small>(اختياري)</small></span><textarea id="ctMsg" name="message" rows="4" placeholder="مقاس المطبخ التقريبي، نوع الصيانة، أو أي تفاصيل أخرى"></textarea></label>

          <button type="submit" class="btn btn-wa ct-send"><svg class="ic"><use href="#wa"/></svg> إرسال الطلب عبر واتساب</button>
          <p class="ct-note" id="ctNote" role="status" aria-live="polite"></p>
        </form>
      </div>
    </div>
  </section>

</main>

<footer class="ft">
  <svg class="ft-wm" aria-hidden="true" focusable="false"><use href="#kt"/></svg>
  <div class="wrap">
    <div class="ft-grid">
      <div class="ft-col ft-about">
        <a href="#home" class="logo ft-logo" aria-label="<?= e($siteName) ?> - الرئيسية">
          <span class="lg-b"><svg class="ic" aria-hidden="true" focusable="false"><use href="#kt"/></svg></span>
          <span class="lg-t"><b><?= e($siteName) ?></b><small><?= e($siteTagline) ?></small></span>
        </a>
        <p><?= e($siteName) ?> في الدمام: تفصيل مطابخ ألمنيوم وخشب، تغيير رخام المطابخ، وصيانة جميع أنواع المطابخ، في الدمام وجميع مدن المنطقة الشرقية بالمملكة العربية السعودية.</p>
        <div class="soc">
          <a href="https://wa.me/<?= e($siteWhatsapp) ?>" target="_blank" rel="noopener" aria-label="واتساب" class="s-wa"><svg class="ic"><use href="#wa"/></svg></a>
          <a href="tel:<?= e($sitePhone) ?>" aria-label="اتصال" class="s-ph"><svg class="ic"><use href="#ph"/></svg></a>
        </div>
      </div>

      <div class="ft-col">
        <h4>روابط سريعة</h4>
        <ul>
          <li><a href="#home">الرئيسية</a></li>
          <li><a href="#about">من نحن</a></li>
          <li><a href="#services">خدماتنا</a></li>
          <li><a href="#gallery">معرض الأعمال</a></li>
          <li><a href="#how">كيف نعمل</a></li>
          <li><a href="#why">لماذا نحن</a></li>
          <li><a href="#reviews">آراء العملاء</a></li>
          <li><a href="#faq">أسئلة شائعة</a></li>
          <li><a href="#contact">تواصل معنا</a></li>
        </ul>
      </div>

      <div class="ft-col">
        <h4>خدماتنا</h4>
        <ul>
          <?php foreach ($services as $svc): ?>
            <li><a href="#services"><?= e($svc['title']) ?></a></li>
          <?php endforeach; ?>
          <li><a href="#services">مطابخ مودرن وكلاسيك</a></li>
        </ul>
      </div>

      <div class="ft-col">
        <h4>تواصل معنا</h4>
        <ul class="ft-ct">
          <li><a href="tel:<?= e($sitePhone) ?>"><span><svg class="ic"><use href="#ph"/></svg></span><b dir="ltr"><?= e($sitePhone) ?></b></a></li>
          <li><a href="https://wa.me/<?= e($siteWhatsapp) ?>" target="_blank" rel="noopener"><span><svg class="ic"><use href="#wa"/></svg></span><b>راسلنا على واتساب</b></a></li>
          <li><a href="#contact"><span><svg class="ic"><use href="#pin"/></svg></span><b>الدمام - المنطقة الشرقية</b></a></li>
        </ul>
      </div>
    </div>

    <div class="ft-bar">
      <p>© <span id="ftYear"><?= date('Y') ?></span> <?= e($siteName) ?> - جميع الحقوق محفوظة | تفصيل مطابخ ألمنيوم وخشب وتغيير رخام وصيانة مطابخ في الدمام والمنطقة الشرقية</p>
      <p class="ft-credit">تصميم وبرمجة <a href="https://www.facebook.com/share/17UzqgSwiu/" target="_blank" rel="noopener">GMTWeb</a></p>
    </div>
  </div>
</footer>

<div class="lb" id="lb" aria-hidden="true" role="dialog" aria-label="عرض الأعمال">
  <button type="button" class="lb-x" id="lbClose" aria-label="إغلاق">✕</button>
  <button type="button" class="lb-b lb-p" id="lbPrev" aria-label="السابق">→</button>
  <img id="lbImg" class="lb-m" src="" alt="">
  <video id="lbVid" class="lb-m" controls playsinline loop></video>
  <button type="button" class="lb-b lb-n" id="lbNext" aria-label="التالي">←</button>
  <span class="lb-c" id="lbCnt"></span>
</div>

<div class="fx" id="fx">
  <a class="fx-b fx-up" id="fxUp" href="#home" aria-label="العودة للأعلى">↑</a>
  <a class="fx-b fx-call" href="tel:<?= e($sitePhone) ?>" aria-label="اتصل بنا"><svg class="ic"><use href="#ph"/></svg></a>
  <a class="fx-b fx-wa" href="https://wa.me/<?= e($siteWhatsapp) ?>" target="_blank" rel="noopener" aria-label="راسلنا على واتساب"><svg class="ic"><use href="#wa"/></svg></a>
</div>

<script>
// حفظ الطلب في قاعدة البيانات قبل فتح واتساب (Capture phase = ينفذ قبل script.js)
(function(){
  var form = document.getElementById('ctForm');
  if (!form) return;
  form.addEventListener('submit', function(){
    var nameEl  = document.getElementById('ctName');
    var phoneEl = document.getElementById('ctPhone');
    if (!nameEl || !phoneEl) return;

    var name   = nameEl.value.trim();
    var phone  = phoneEl.value.trim();
    var digits = phone.replace(/[^0-9٠-٩]/g, '');
    if (name.length < 2 || digits.length < 8) return;

    try {
      var fd = new FormData(form);
      fetch('save_message.php', { method: 'POST', body: fd, keepalive: true }).catch(function(){});
    } catch (ex) {}
  }, true);
})();
</script>

<script src="script.js" defer></script>
</body>
</html>