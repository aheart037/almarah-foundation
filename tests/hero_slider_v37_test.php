<?php

declare(strict_types=1);

/** V3.7 cinematic home slider: assets present, wired into the home page, HD images served via srcset.
 *  TEST_DB_NAME=almarah_test php tests/hero_slider_v37_test.php */
require __DIR__ . '/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use Tests\Reports\Fixture;

$name = (string) getenv('TEST_DB_NAME');
if ($name === '' || !str_contains($name, 'test')) { throw new RuntimeException('Set disposable TEST_DB_NAME containing test.'); }
putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . $name);
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
$db = Fixture::connect(); Fixture::migrate($db); Fixture::seed($db);
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\App\Core\Session::class)->start();
$n = 0;
function ok(bool $c, string $l): void { global $n; if (!$c) throw new RuntimeException('FAIL ' . $l); $n++; }

$d = $app->make(\App\Core\Database::class);
if ($d->int('SELECT COUNT(*) FROM cms_slides') === 0) { foreach ([1, 2, 3, 4] as $i) { $d->insert('cms_slides', ['image_path' => "assets/images/banners/Header-$i.jpg", 'image_alt' => 'Almarah', 'is_active' => 1, 'sort_order' => $i]); } }
$html = $app->make(Router::class)->dispatch(new Request('GET', '/', [], [], [], []), $app)->body();
ok(str_contains($html, 'hero-slider-v37.css') && str_contains($html, 'hero-slider-v37.js'), 'home page loads the slider stylesheet and script');
ok(str_contains($html, 'id="heroSlider"') && substr_count($html, 'class="hero-slide') >= 4, 'slides are rendered');
ok(preg_match('#srcset="[^"]*Header-1\.jpg[^"]* 1663w, [^"]*Header-1-hd\.jpg[^"]* 2560w"#', $html) === 1, 'HD srcset offered for the first slide');
foreach (['assets/css/hero-slider-v37.css', 'assets/js/hero-slider-v37.js'] as $f) { ok(is_file(__DIR__ . '/../' . $f) && filesize(__DIR__ . '/../' . $f) > 1000, "$f exists"); }
foreach ([1, 2, 3, 4] as $i) { [$w] = getimagesize(__DIR__ . "/../assets/images/banners/Header-$i-hd.jpg"); ok($w === 2560, "Header-$i-hd.jpg is 2560px wide"); }
$js = (string) file_get_contents(__DIR__ . '/../assets/js/hero-slider-v37.js');
ok(!str_contains($js, "subtree: true"), 'script does not observe its own class changes (no re-trigger loop)');
ok(str_contains((string) file_get_contents(__DIR__ . '/../assets/css/hero-slider-v37.css'), 'prefers-reduced-motion'), 'reduced-motion users get a calm slider');
echo "$n hero slider checks passed.\n";
