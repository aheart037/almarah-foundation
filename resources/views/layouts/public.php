<?php
/**
 * Public website layout — Al Marah Foundation (almarah.org).
 *
 * @var string      $content
 * @var string      $pageTitle
 * @var string|null $metaDescription
 * @var string|null $robots            e.g. 'noindex,follow'
 * @var string|null $canonicalPath
 * @var string|null $ogImage
 * @var string|null $ogType
 * @var array|null  $structuredData    extra JSON-LD block(s)
 * @var array|null  $breadcrumbs       [['name' => '', 'path' => ''], ...]
 * @var string|null $bodyClass
 */

use App\Core\Config;

$appName = (string) Config::get('app.name', 'Almarah Foundation');
$org = (array) Config::get('app.org', []);

try {
    $seoPath = request()->path();
} catch (\Throwable) {
    $seoPath = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
}
$seoPath = $seoPath === '' ? '/' : $seoPath;
$currentPath = '/' . trim($seoPath, '/');

// --- Admin-managed overrides (Admin → SEO) ---------------------------------
$seoMeta = null;
try {
    $seoMeta = app(\App\Services\SeoService::class)->metaFor($seoPath);
} catch (\Throwable) {
    $seoMeta = null;
}

$defaultTitle = $appName . ' | Charity Foundation, Orphan Care, Education & Mental Health';
$title = !empty($seoMeta['title'])
    ? (string) $seoMeta['title']
    : ((isset($pageTitle) && $pageTitle !== '') ? $pageTitle . ' - ' . $appName : $defaultTitle);
$description = !empty($seoMeta['description']) ? (string) $seoMeta['description'] : ($metaDescription ?? (string) ($org['tagline'] ?? ''));

$isAuthPage = preg_match('~^/(?:login|forgot-password|reset-password|verify-email)(?:/|$)~', $currentPath) === 1;
$seoPrivate = $isAuthPage
    || preg_match('~^/(?:donation|payments|setup|admin|thank-you|unsubscribe|newsletter/confirm)~', $currentPath) === 1
    || (isset($status) && (int) $status >= 400);

$seoHasFilter = false;
foreach (['q', 'page', 'album', 'sort', 'search', 'type'] as $seoKey) {
    if (isset($_GET[$seoKey]) && $_GET[$seoKey] !== '' && $seoKey !== 'page') {
        $seoHasFilter = true;
        break;
    }
}

if (!empty($seoMeta['robots'])) {
    $robotsContent = (string) $seoMeta['robots'];
} elseif (!empty($robots)) {
    $robotsContent = (string) $robots;
} elseif ($seoPrivate) {
    $robotsContent = 'noindex,nofollow';
} elseif ($seoHasFilter) {
    $robotsContent = 'noindex,follow';
} else {
    $robotsContent = 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
}
$seoIndexable = str_starts_with($robotsContent, 'index');

if (!empty($seoMeta['canonical_url'])) {
    $canonicalUrl = (string) $seoMeta['canonical_url'];
} elseif (!empty($canonicalPath)) {
    $canonicalUrl = base_url(ltrim((string) $canonicalPath, '/'));
} else {
    $canonicalUrl = $seoPath === '/' ? base_url() . '/' : base_url(ltrim($seoPath, '/')) . '/';
    $seoPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($seoPage > 1 && !$seoHasFilter) {
        $canonicalUrl .= '?page=' . $seoPage;
    }
}

$description = trim((string) preg_replace('/\s+/u', ' ', strip_tags($description)));
if (mb_strlen($description) > 160) {
    $cut = mb_substr($description, 0, 157);
    $space = mb_strrpos($cut, ' ');
    $description = rtrim($space !== false && $space > 100 ? mb_substr($cut, 0, $space) : $cut, " ,.;:-") . '...';
}

$ogTitle = !empty($seoMeta['og_title']) ? (string) $seoMeta['og_title'] : $title;
$ogDescription = !empty($seoMeta['og_description']) ? (string) $seoMeta['og_description'] : $description;
$ogImageRaw = !empty($seoMeta['og_image']) ? (string) $seoMeta['og_image'] : (!empty($ogImage) ? (string) $ogImage : 'assets/images/banners/Header-1.jpg');
$ogImageUrl = preg_match('~^https?://~i', $ogImageRaw) === 1 ? $ogImageRaw : base_url(ltrim($ogImageRaw, '/'));
$ogTypeValue = !empty($ogType) ? (string) $ogType : 'website';
$twitterCard = !empty($seoMeta['twitter_card']) ? (string) $seoMeta['twitter_card'] : 'summary_large_image';

$headerLogo = function_exists('site_brand_asset') ? site_brand_asset('site.header_logo') : null;
$headerLogoUrl = $headerLogo !== null && $headerLogo !== '' ? $headerLogo : asset('assets/images/logo/header-logo-01.png');
$footerLogo = function_exists('site_brand_asset') ? site_brand_asset('site.footer_logo') : null;
$footerLogoUrl = $footerLogo !== null && $footerLogo !== '' ? $footerLogo : asset('assets/images/logo/footer-logo.png');

$social = [
    'facebook'  => 'https://www.facebook.com/almarahfoundation',
    'instagram' => 'https://www.instagram.com/almarahfoundation',
    'youtube'   => 'https://www.youtube.com/@almarahfoundation',
    'linkedin'  => 'https://www.linkedin.com/company/almarahfoundation/home/',
    'tiktok'    => 'https://www.tiktok.com/@almarahfoundation1',
];
try {
    $siteSettings = app(\App\Services\SettingsService::class);
    foreach ($social as $k => $v) {
        $saved = $siteSettings->get('social.' . $k);
        if (is_string($saved) && $saved !== '') {
            $social[$k] = $saved;
        }
    }
    $orgPhone = $siteSettings->get('site.phone', (string) ($org['phone'] ?? '')) ?? '';
    $orgEmail = $siteSettings->get('site.email', (string) ($org['email'] ?? '')) ?? '';
    $orgAddress = $siteSettings->get('site.address', (string) ($org['address'] ?? '')) ?? '';
    $orgLandline = $siteSettings->get('site.landline', '(042) 111 111 130') ?? '';
} catch (\Throwable) {
    $orgPhone = (string) ($org['phone'] ?? '');
    $orgEmail = (string) ($org['email'] ?? '');
    $orgAddress = (string) ($org['address'] ?? '');
    $orgLandline = '(042) 111 111 130';
}

$jsonLd = [[
    '@context'  => 'https://schema.org',
    '@type'     => 'NGO',
    '@id'       => base_url() . '/#organization',
    'name'      => (string) ($org['legal_name'] ?? $appName),
    'url'       => base_url() . '/',
    'logo'      => preg_replace('/\?v=\d+$/', '', $headerLogoUrl),
    'image'     => $ogImageUrl,
    'slogan'    => (string) ($org['tagline'] ?? ''),
    'email'     => $orgEmail,
    'telephone' => $orgPhone,
    'foundingDate' => (string) ($org['founded'] ?? ''),
    'address'   => ['@type' => 'PostalAddress', 'streetAddress' => '3 Canal Park, Gulberg-II', 'addressLocality' => 'Lahore', 'addressRegion' => 'Punjab', 'addressCountry' => 'PK'],
    'sameAs'    => array_values($social),
]];
if ($seoPath === '/') {
    $jsonLd[] = ['@context' => 'https://schema.org', '@type' => 'WebSite', '@id' => base_url() . '/#website', 'url' => base_url() . '/', 'name' => $appName, 'publisher' => ['@id' => base_url() . '/#organization']];
}
if (!empty($breadcrumbs) && is_array($breadcrumbs)) {
    $crumbItems = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => base_url() . '/']];
    foreach (array_values($breadcrumbs) as $i => $crumb) {
        $crumbItems[] = ['@type' => 'ListItem', 'position' => $i + 2, 'name' => (string) ($crumb['name'] ?? ''), 'item' => base_url(ltrim((string) ($crumb['path'] ?? ''), '/')) . '/'];
    }
    $jsonLd[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $crumbItems];
}
if (!empty($seoMeta['schema_json'])) {
    $extraSchema = json_decode((string) $seoMeta['schema_json'], true);
    if (is_array($extraSchema) && $extraSchema !== []) {
        $structuredData = array_merge(isset($structuredData['@type']) ? [$structuredData] : (array) ($structuredData ?? []), isset($extraSchema['@type']) ? [$extraSchema] : $extraSchema);
    }
}
if (!empty($structuredData) && is_array($structuredData)) {
    foreach ((isset($structuredData['@type']) ? [$structuredData] : $structuredData) as $block) {
        if (is_array($block) && $block !== []) {
            $jsonLd[] = array_filter($block, static fn ($v): bool => $v !== null);
        }
    }
}
if (!$seoIndexable) {
    $jsonLd = [];
}

try {
    $nav = app(\App\Services\NavigationService::class);
    $headerNav = $nav->items('header');
    $footerQuick = $nav->items('footer_quick');
    $footerMore = $nav->items('footer_more');
} catch (\Throwable) {
    $nav = null;
    $headerNav = $footerQuick = $footerMore = [];
}
$fundraiseUrl = rtrim((string) Config::get('app.fundraise.url', 'https://fundraise.almarah.org'), '/') . '/';

try {
    $topBanner = app(\App\Services\CmsService::class)->activeBanner('top_bar');
} catch (\Throwable) {
    $topBanner = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<?php if (!empty($seoMeta['keywords'])): ?><meta name="keywords" content="<?= e((string) $seoMeta['keywords']) ?>">
<?php endif; ?>
<meta name="robots" content="<?= e($robotsContent) ?>">
<link rel="canonical" href="<?= e($canonicalUrl) ?>">
<meta name="theme-color" content="#a92d63">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta property="og:site_name" content="<?= e($appName) ?>">
<meta property="og:locale" content="en_US">
<meta property="og:type" content="<?= e($ogTypeValue) ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">
<meta property="og:title" content="<?= e($ogTitle) ?>">
<meta property="og:description" content="<?= e($ogDescription) ?>">
<meta property="og:image" content="<?= e($ogImageUrl) ?>">
<meta property="og:image:alt" content="<?= e(isset($pageTitle) && $pageTitle !== '' ? (string) $pageTitle : $appName) ?>">
<meta name="twitter:card" content="<?= e($twitterCard) ?>">
<meta name="twitter:title" content="<?= e($ogTitle) ?>">
<meta name="twitter:description" content="<?= e($ogDescription) ?>">
<meta name="twitter:image" content="<?= e($ogImageUrl) ?>">
<?php foreach ($jsonLd as $jsonLdBlock): ?>
<script type="application/ld+json"><?= json_encode($jsonLdBlock, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endforeach; ?>
<link rel="preload" href="<?= e(asset('assets/fonts/manrope-latin-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(asset('assets/fonts/open-sans-latin-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/hero-v32.css')) ?>">
<?php require __DIR__ . '/../partials/favicon.php'; ?>
</head>
<body class="site-body<?= !empty($bodyClass) ? ' ' . e((string) $bodyClass) : '' ?><?= $isAuthPage ? ' auth-page-body' : '' ?>">
<a class="skip-link" href="#main">Skip to content</a>

<?php require __DIR__ . '/../partials/site-header.php'; ?>

<main id="main" class="site-main">
<?php require __DIR__ . '/../partials/alerts.php'; ?>
<?php if (!empty($breadcrumbs) && is_array($breadcrumbs) && empty($hideBreadcrumbs)): ?>
<nav class="crumbs" aria-label="Breadcrumb">
  <div class="container">
    <ol>
      <li><a href="<?= e(base_url()) ?>/">Home</a></li>
      <?php foreach (array_values($breadcrumbs) as $i => $crumb): ?>
        <?php if ($i === count($breadcrumbs) - 1): ?>
          <li aria-current="page"><?= e((string) ($crumb['name'] ?? '')) ?></li>
        <?php else: ?>
          <li><a href="<?= e(base_url(ltrim((string) ($crumb['path'] ?? ''), '/'))) ?>"><?= e((string) ($crumb['name'] ?? '')) ?></a></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ol>
  </div>
</nav>
<?php endif; ?>
<?= $content ?>
</main>

<?php require __DIR__ . '/../partials/site-footer.php'; ?>

<script src="<?= e(asset('assets/js/site.js')) ?>" defer></script>
</body>
</html>
