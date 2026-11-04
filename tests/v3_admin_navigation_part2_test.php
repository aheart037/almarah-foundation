<?php

declare(strict_types=1);

/** Destructive integration test: ONLY use a disposable database with "test" in its name. */
require __DIR__ . '/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\Csrf;
use App\Services\AuthService;
use App\Exceptions\HttpException;
use Tests\Reports\Fixture;

$name = (string) getenv('TEST_DB_NAME');
if ($name === '' || !str_contains($name, 'test')) {
    throw new RuntimeException('Set TEST_DB_NAME to a disposable *_test database.');
}
putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . $name);
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
putenv('APP_CURRENCY=PKR');
$db = Fixture::connect();
Fixture::migrate($db);
Fixture::seed($db);
$app = require __DIR__ . '/../bootstrap/app.php';
$router = $app->make(Router::class);
$auth = $app->make(AuthService::class);
$app->make(Session::class)->start();
$checks = 0;
function verifyNav(bool $yes, string $label): void {
    global $checks;
    if (!$yes) throw new RuntimeException('FAIL ' . $label);
    $checks++;
}
function navRequest(Router $router, $app, string $path, string $method = 'GET', array $data = []): \App\Core\Response {
    $_SERVER['REQUEST_URI'] = $path;
    return $router->dispatch(new Request($method, $path, $method === 'GET' ? $data : [], $method === 'POST' ? $data : [], [], []), $app);
}
function docNav(string $html): DOMXPath {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    return new DOMXPath($dom);
}
function sideLinks(DOMXPath $xp): array {
    $links = [];
    foreach ($xp->query("//nav[contains(concat(' ',normalize-space(@class),' '),' dash-nav ')]//a[@href]") as $a) {
        $path = parse_url($a->getAttribute('href'), PHP_URL_PATH);
        if (is_string($path) && str_starts_with($path, '/admin')) $links[$path] = $a;
    }
    return $links;
}
function statusOrException(Router $router, $app, string $path, string $method = 'GET', array $data = []): int {
    try { return navRequest($router, $app, $path, $method, $data)->status(); }
    catch (HttpException $e) { return $e->statusCode(); }
}

$all = ['dashboard.view','content.manage','donations.view','donations.manage','volunteers.manage','forms.view','reports.view','logs.view','settings.manage','users.manage','finance.manage','backups.manage'];
foreach ($all as $permission) {
    $id = $db->insert('permissions', ['name'=>$permission,'group_name'=>explode('.', $permission)[0], 'label'=>$permission]);
    $db->insert('role_permissions', ['role_id'=>1,'permission_id'=>$id]);
}
$reviewer = $db->insert('roles', ['name'=>'initial_reviewer','label'=>'Reviewer']);
foreach (['dashboard.view','donations.view'] as $permission) {
    $db->insert('role_permissions', ['role_id'=>$reviewer,'permission_id'=>$db->int('SELECT id FROM permissions WHERE name=:n', ['n'=>$permission])]);
}
$db->insert('user_roles', ['user_id'=>2,'role_id'=>$reviewer]);
$db->insert('user_roles', ['user_id'=>3,'role_id'=>3]); // ordinary non-staff user
$login = static function (int $id) use ($db,$auth): void {
    $auth->logout();
    $auth->login($db->selectOne('SELECT * FROM users WHERE id=:id', ['id'=>$id]), '127.0.0.1');
};
$login(1);
$home = navRequest($router,$app,'/admin');
verifyNav($home->status() === 200 && str_contains($home->body(),'Admin overview'), 'dashboard renders');
$links = sideLinks(docNav($home->body()));
verifyNav(count($links) >= 35, 'complete authorized sidebar and reports submenu render');
$routes = (new ReflectionProperty(Router::class, 'routes'))->getValue($router);
foreach ($links as $path => $anchor) {
    $matches = array_filter($routes, static fn($r) => $r['method']==='GET' && preg_match($r['regex'], $path) === 1);
    verifyNav($matches !== [], $path . ' has GET route');
    $response = navRequest($router,$app,$path);
    verifyNav($response->status() === 200 && str_contains($response->body(), 'dash-nav'), $path . ' opens the admin page');
    $again = navRequest($router,$app,$path);
    verifyNav($again->status() === 200, $path . ' survives refresh');
    $xp = docNav($response->body());
    $current = $xp->query("//nav[contains(@class,'dash-nav')]//a[@aria-current='page']");
    // SEO family deliberately uses one parent link for its three routes.
    verifyNav($current->length === 1, $path . ' has exactly one active link');
    $active = parse_url($current->item(0)->getAttribute('href'), PHP_URL_PATH);
    verifyNav($active === $path, $path . ' highlights the correct menu item');
}
foreach (\App\Services\ReportService::catalog() as $slug => $_) {
    $path = $slug === 'overview' ? '/admin/reports' : '/admin/reports/' . $slug;
    verifyNav(isset($links[$path]), $path . ' is in report submenu');
}
$report = navRequest($router,$app,'/admin/reports/donations/pdf');
verifyNav($report->status() === 200, 'report PDF export is protected and accessible to authorized user');

// A subfolder install must highlight the canonical route after browser refresh.
\App\Core\BasePath::boot('https://example.org/subsite');
$folder = navRequest($router,$app,'/subsite/admin/reports/donations');
verifyNav($folder->status() === 200, 'subfolder admin route opens directly');
$activeFolder = docNav($folder->body())->query("//nav[contains(@class,'dash-nav')]//a[@aria-current='page']");
verifyNav($activeFolder->length === 1 &&
          parse_url($activeFolder->item(0)->getAttribute('href'), PHP_URL_PATH) === '/subsite/admin/reports/donations',
          'subfolder submenu URL and active state');
\App\Core\BasePath::boot((string) \App\Core\Config::get('app.url', ''));

// Staff with view-only donations must not be offered an export they cannot open.
$login(2);
$limited = navRequest($router,$app,'/admin');
verifyNav($limited->status() === 200, 'reviewer dashboard works');
$limitedLinks = sideLinks(docNav($limited->body()));
verifyNav(isset($limitedLinks['/admin/donations']) && !isset($limitedLinks['/admin/reports']) && !isset($limitedLinks['/admin/settings']) && !isset($limitedLinks['/admin/users']), 'reviewer menu follows permissions');
$donations = navRequest($router,$app,'/admin/donations');
verifyNav($donations->status()===200 && !str_contains($donations->body(), 'Export filtered CSV'), 'view-only donations screen hides unauthorized export');
foreach (['/admin/campaigns','/admin/users','/admin/email-templates','/admin/settings','/admin/cms/pages','/admin/cms/banks','/admin/inbox/contact','/admin/reports','/admin/reports/donations/pdf','/admin/backups','/admin/audit','/admin/payment-callbacks','/admin/donations/export'] as $path) {
    verifyNav(statusOrException($router,$app,$path)===403, 'reviewer denied ' . $path);
}
foreach (['/admin/users/1/suspend','/admin/email-templates/1','/admin/settings/site','/admin/campaigns',
          '/admin/cms/pages','/admin/cms/pages/reorder','/admin/inbox/contact/1','/admin/menus/header'] as $path) {
    verifyNav(statusOrException($router,$app,$path,'POST',['_token'=>$app->make(Csrf::class)->token()])===403, 'reviewer denied POST ' . $path);
}

$login(3);
foreach (['/admin','/admin/donations','/admin/cms/pages','/admin/users','/admin/reports','/admin/settings','/admin/email-queue'] as $path) {
    verifyNav(statusOrException($router,$app,$path)===403, 'non-staff denied ' . $path);
}
$auth->logout();
foreach (['/admin','/admin/donations','/admin/users','/admin/reports','/admin/settings'] as $path) {
    verifyNav(statusOrException($router,$app,$path)===302, 'guest redirected from ' . $path);
}

// Seed-like administrator grants do not include settings/users; role alone must not bypass permissions.
$db->run('DELETE FROM role_permissions WHERE role_id = 1 AND permission_id IN (SELECT id FROM permissions WHERE name IN (\'users.manage\',\'settings.manage\',\'finance.manage\',\'backups.manage\'))');
$login(1);
$restricted = navRequest($router,$app,'/admin');
$restrictedLinks = sideLinks(docNav($restricted->body()));
verifyNav(!isset($restrictedLinks['/admin/settings']) && !isset($restrictedLinks['/admin/users']) && !isset($restrictedLinks['/admin/finance']), 'admin role does not imply missing permission');
foreach (['/admin/settings','/admin/users','/admin/email-templates','/admin/finance','/admin/backups'] as $path) {
    verifyNav(statusOrException($router,$app,$path)===403, 'admin role alone denied ' . $path);
}
verifyNav(statusOrException($router,$app,'/admin/campaigns')===200, 'admin with content.manage retains campaigns');
$queueHtml = navRequest($router,$app,'/admin/email-queue')->body();
verifyNav(!str_contains($queueHtml, 'href="' . base_url('admin/settings#smtp') . '"') &&
          !str_contains($queueHtml, 'href="' . base_url('admin/email-templates') . '"'),
          'logs-only email queue has no inaccessible settings/template links');

// Missing permissions must also prevent token-bearing POST actions, not just hide UI.
verifyNav(statusOrException($router,$app,'/admin/settings/site','POST',['_token'=>$app->make(Csrf::class)->token()])===403, 'settings write guarded');
verifyNav(statusOrException($router,$app,'/admin/users/1/suspend','POST',['_token'=>$app->make(Csrf::class)->token()])===403, 'user write guarded');
$db->run("DELETE FROM role_permissions WHERE role_id = 1 AND permission_id = (SELECT id FROM permissions WHERE name = 'logs.view')");
$db->insert('role_permissions', ['role_id'=>1,'permission_id'=>$db->int("SELECT id FROM permissions WHERE name = 'settings.manage'")]);
$login(1);
$settingsHtml = navRequest($router,$app,'/admin/settings')->body();
$templateHtml = navRequest($router,$app,'/admin/email-templates')->body();
verifyNav(statusOrException($router,$app,'/admin/email-queue')===403 &&
          !str_contains($settingsHtml, 'href="' . base_url('admin/email-queue') . '"') &&
          !str_contains($templateHtml, 'href="' . base_url('admin/email-queue') . '"'),
          'settings-only staff cannot see or access logs-only email queue');

// Super-admin bypass remains valid; provider and donation endpoints are unchanged.
$login(2);
$db->insert('user_roles', ['user_id'=>2,'role_id'=>2]);
$login(2);
verifyNav(statusOrException($router,$app,'/admin/settings')===200, 'super admin settings loads');
verifyNav(statusOrException($router,$app,'/admin/users')===200, 'super admin users loads');
verifyNav(statusOrException($router,$app,'/admin/campaigns')===200, 'super admin campaigns loads');

echo "$checks V3 Part 2 admin navigation and authorization assertions passed.\n";
