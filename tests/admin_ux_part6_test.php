<?php

declare(strict_types=1);

/** Destructive integration checks: run ONLY against a disposable *_test database. */
require __DIR__ . '/bootstrap.php';

use Tests\Reports\Fixture;
use App\Core\Request;
use App\Core\Router;
use App\Services\AuthService;

putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . (getenv('TEST_DB_NAME') ?: 'almarah_test'));
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
putenv('APP_CURRENCY=PKR');
$db = Fixture::connect();
Fixture::migrate($db);
Fixture::seed($db);
$app = require __DIR__ . '/../bootstrap/app.php';
$router = $app->make(Router::class);
$auth = $app->make(AuthService::class);
$app->make(\App\Core\Session::class)->start();
$assertions = 0;
function checkAdmin(bool $test, string $message): void { global $assertions; if (!$test) throw new RuntimeException('FAIL: ' . $message); $assertions++; }
function adminPage(Router $router, $app, string $path): string {
    $_SERVER['REQUEST_URI'] = $path;
    $response = $router->dispatch(new Request('GET', $path, [], [], [], []), $app);
    checkAdmin($response->status() === 200, $path . ' returns 200');
    $html = $response->body();
    if (getenv('ADMIN_PREVIEW_DIR') && in_array($path, ['/admin','/admin/donations','/admin/reports','/admin/inbox/contact','/admin/campaigns','/admin/settings'], true)) {
        $preview = preg_replace_callback('~(href|src)="https://almarah\.org/(assets/[^"?]+)(?:\?[^" ]*)?"~', static fn ($m) => $m[1] . '="file://' . realpath(__DIR__ . '/../' . $m[2]) . '"', $html);
        $filename = (string) getenv('ADMIN_PREVIEW_DIR') . '/.admin-' . str_replace('/', '-', ltrim($path, '/')) . '.html';
        if (!is_file($filename)) file_put_contents($filename, $preview);
    }
    return $html;
}
function cardValue(string $html, string $title): string {
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xp = new DOMXPath($doc);
    foreach ($xp->query("//*[contains(concat(' ',normalize-space(@class),' '),' stat-card ')]") as $card) {
        $key = $xp->query(".//*[contains(concat(' ',normalize-space(@class),' '),' k ')]", $card)->item(0);
        $value = $xp->query(".//*[contains(concat(' ',normalize-space(@class),' '),' v ')]", $card)->item(0);
        if (trim($key?->textContent ?? '') === $title) return trim($value?->textContent ?? '');
    }
    throw new RuntimeException('Missing dashboard card: ' . $title);
}
$permissions = ['dashboard.view','content.manage','donations.view','donations.manage','volunteers.manage','forms.view','reports.view','logs.view','settings.manage','users.manage','finance.manage','backups.manage'];
foreach ($permissions as $name) {
    $id = $db->insert('permissions', ['name' => $name, 'group_name' => explode('.', $name)[0], 'label' => $name]);
    $db->insert('role_permissions', ['role_id' => 1, 'permission_id' => $id]);
}
$db->insert('donations', ['public_reference'=>'ADMIN-LEGACY','donor_id'=>1,'amount_minor'=>99900000,'currency'=>'PKR','status'=>'completed','verification_status'=>'legacy','completed_at'=>gmdate('Y-m-d H:i:s')]);
$db->insert('donations', ['public_reference'=>'ADMIN-USD','donor_id'=>1,'amount_minor'=>88800000,'currency'=>'USD','status'=>'completed','verification_status'=>'verified','completed_at'=>gmdate('Y-m-d H:i:s')]);
$db->insert('donations', ['public_reference'=>'ADMIN-REVIEW','donor_id'=>1,'amount_minor'=>20000,'currency'=>'PKR','status'=>'pending','verification_status'=>'submitted']);
$db->insert('contact_messages', ['name'=>'Demo Inquiry','email'=>'inquiry@example.org','subject'=>'Assistance','message'=>'Could you help?','status'=>'new']);
$db->insert('volunteers', ['first_name'=>'Demo','last_name'=>'Helper','email'=>'helper@example.org','phone'=>'03000000000','status'=>'pending']);
$auth->login($db->selectOne('SELECT * FROM users WHERE id=1'), '127.0.0.1');
$html = adminPage($router, $app, '/admin');
if (getenv('ADMIN_PREVIEW_HTML')) {
    $preview = preg_replace_callback('~(href|src)="https://almarah\.org/(assets/[^"?]+)(?:\?[^" ]*)?"~', static fn ($m) => $m[1] . '="file://' . realpath(__DIR__ . '/../' . $m[2]) . '"', $html);
    file_put_contents((string) getenv('ADMIN_PREVIEW_HTML'), $preview);
}

$verified = $db->selectOne("SELECT COUNT(*) cnt, COALESCE(SUM(amount_minor),0) minor FROM donations WHERE currency='PKR' AND status='completed' AND verification_status='verified'");
checkAdmin(cardValue($html, 'Verified donations') === money_short((int) $verified['minor'], 'PKR'), 'verified dashboard amount excludes legacy, refunded and USD');
checkAdmin(str_contains($html, number_format((int)$verified['cnt']) . ' completed, staff-verified'), 'verified count reflects source records');
$monthMinor = $db->int("SELECT COALESCE(SUM(amount_minor),0) FROM donations WHERE currency='PKR' AND status='completed' AND verification_status='verified' AND COALESCE(completed_at, created_at) >= DATE_FORMAT(UTC_DATE(), '%Y-%m-01')");
checkAdmin(cardValue($html, 'Verified · this month') === money_short($monthMinor, 'PKR'), 'monthly card uses only this-month verified primary-currency gifts');
checkAdmin(str_contains($html, money($monthMinor, 'PKR')), 'monthly chart tooltip shows real this-month verified total');
checkAdmin(cardValue($html, 'Pending verification') === '1', 'pending staff review distinct from awaiting payment');
checkAdmin(cardValue($html, 'Donors') === '4', 'donor card reads donors table');
checkAdmin(cardValue($html, 'Active campaigns') === '1', 'active campaign count respects lifecycle');
checkAdmin(cardValue($html, 'Fundraisers') === '2', 'published approved fundraisers exclude deleted rows');
checkAdmin(cardValue($html, 'Volunteer applications') === '1' && cardValue($html, 'Contact inquiries') === '1', 'inbox cards read submissions');
checkAdmin(str_contains($html, 'Needs your attention') && str_contains($html, '1 pending verification'), 'action queue links to live counts');
checkAdmin(str_contains($html, 'Verified donations per month, last 12 months') && str_contains($html, 'data-tip='), '12-month verified SVG trend with tooltips');
checkAdmin(str_contains($html, 'id="recentTransactions"') && str_contains($html, 'data-table-search="recentTransactions"'), 'local transaction search controls present');
checkAdmin(str_contains($html, 'aria-current="page"') && str_contains($html, 'data-side-close') && str_contains($html, 'aria-expanded="false"'), 'active navigation and accessible drawer controls render');
checkAdmin(str_contains($html, 'href="https://almarah.org/admin/campaigns"'), 'existing campaigns route discoverable for admin');
$doc = new DOMDocument(); @$doc->loadHTML($html); $xp = new DOMXPath($doc);
$routes = new ReflectionProperty(Router::class, 'routes');
$known = array_values(array_filter($routes->getValue($router), static fn ($r) => $r['method'] === 'GET'));
foreach ($xp->query("//nav[contains(@class,'dash-nav')]//a[@href]") as $anchor) {
    $path = parse_url($anchor->getAttribute('href'), PHP_URL_PATH);
    if (!is_string($path) || $path === '/') continue;
    $matches = array_filter($known, static fn ($r) => preg_match($r['regex'], $path) === 1);
    checkAdmin($matches !== [], 'sidebar link resolves to existing GET route: ' . $path);
}
foreach (['/admin/campaigns','/admin/donations','/admin/inbox/contact','/admin/reports','/admin/audit','/admin/settings'] as $path) {
    adminPage($router, $app, $path);
}
foreach (\App\Services\ReportService::catalog() as $slug => $_) {
    $path = $slug === 'overview' ? '/admin/reports' : '/admin/reports/' . $slug;
    adminPage($router, $app, $path);
}
// A reviewer gets finance counts but neither PII inbox nor Reports/CMS/System.
$db->insert('roles', ['id'=>4,'name'=>'initial_reviewer','label'=>'Initial reviewer']);
$db->insert('user_roles', ['user_id'=>2,'role_id'=>4]);
foreach (['dashboard.view','donations.view'] as $name) {
    $db->insert('role_permissions', ['role_id'=>4,'permission_id'=>$db->int('SELECT id FROM permissions WHERE name=:n', ['n'=>$name])]);
}
$auth->logout();
$auth->login($db->selectOne('SELECT * FROM users WHERE id=2'), '127.0.0.1');
$limited = adminPage($router, $app, '/admin');
checkAdmin(str_contains($limited, 'Verified donations') && !str_contains($limited, 'Volunteer applications') && !str_contains($limited, 'Contact inquiries'), 'role-limited dashboard hides private inbox');
checkAdmin(!str_contains($limited, 'class="nav-sub') && !str_contains($limited, 'href="https://almarah.org/admin/audit"') && !str_contains($limited, 'href="https://almarah.org/admin/campaigns"'), 'role-limited navigation hides reports, audit and campaigns');
try { adminPage($router, $app, '/admin/reports'); throw new RuntimeException('Reviewer opened unauthorized report'); }
catch (\App\Exceptions\HttpException $e) { checkAdmin($e->statusCode() === 403, 'server still blocks reports without permission'); }
echo "$assertions Part 6 admin assertions passed.\n";
