<?php

declare(strict_types=1);

/**
 * Website forms: contact, volunteer, newsletter (page + footer) are rendered, stored, and visible in the admin inbox.
 *   TEST_DB_NAME=almarah_test php tests/website_forms_part3_test.php   (disposable DB whose name contains "test")
 */
require __DIR__ . '/bootstrap.php';

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Exceptions\HttpException;
use Tests\Reports\Fixture;

$name = (string) getenv('TEST_DB_NAME');
if ($name === '' || !str_contains($name, 'test')) { throw new RuntimeException('Set disposable TEST_DB_NAME containing test.'); }
putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . $name);
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
$db = Fixture::connect();
Fixture::migrate($db);
Fixture::seed($db);
$app = require __DIR__ . '/../bootstrap/app.php';
$database = $app->make(Database::class);
$session = $app->make(Session::class);
$session->start();
$router = $app->make(Router::class);
$n = 0;
function ok(bool $c, string $label): void { global $n; if (!$c) throw new RuntimeException('FAIL ' . $label); $n++; }

$pdo = $database->pdo();
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['contact_messages', 'newsletter_subscribers', 'volunteers', 'form_submissions'] as $t) { $pdo->exec("TRUNCATE TABLE `$t`"); }
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
try { $app->make(\App\Core\RateLimiter::class); } catch (Throwable) {}

function get(string $path): array
{
    global $router, $app;
    try {
        $r = $router->dispatch(new Request('GET', $path, [], [], [], []), $app);
        return [$r->status(), $r->body()];
    } catch (HttpException $e) { return [$e->statusCode(), '']; }
}
function post(string $path, array $data): array
{
    global $router, $app;
    $data += ['_token' => $app->make(Csrf::class)->token(), '_t' => time() - 20];
    try {
        $r = $router->dispatch(new Request('POST', $path, [], $data, [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest', 'HTTP_ACCEPT' => 'application/json']), $app);
        return [$r->status(), $r->body()];
    } catch (HttpException $e) { return [$e->statusCode(), '']; }
}

// --- 1. the pages show real forms (no leftover WordPress shortcodes) ----------------------------------------
foreach (['/contact' => 'forms/contact', '/become-a-volunteer' => 'forms/volunteer', '/newsletter' => 'forms/newsletter'] as $path => $action) {
    [$st, $html] = get($path);
    ok($st === 200 && str_contains($html, '<form') && str_contains($html, $action) && !str_contains($html, '[forminator_form'), "$path renders a working form");
}
[, $home] = get('/');
ok(preg_match('#<form class="footer-subscribe"[^>]*action="[^"]*forms/newsletter"#', $home) === 1, 'footer newsletter form posts to forms/newsletter');
ok(str_contains(get('/contact')[1], 'name="message"') && str_contains(get('/become-a-volunteer')[1], 'name="cnic"') && str_contains(get('/newsletter')[1], 'name="email"'), 'form fields are present');

// --- 2. submissions are stored ----------------------------------------------------------------------------------
[$st] = post('/forms/contact', ['name' => 'Sara Khan', 'email' => 'sara@example.org', 'phone' => '0300111', 'subject' => 'Sponsoring a child', 'message' => 'Hello, I would like to know how to sponsor an orphan.']);
ok($st < 400 && $database->int("SELECT COUNT(*) FROM contact_messages WHERE email='sara@example.org'") === 1, 'contact message stored');
post('/forms/contact', ['name' => 'Sara Khan', 'email' => 'sara@example.org', 'phone' => '0300111', 'subject' => 'Sponsoring a child', 'message' => 'Hello, I would like to know how to sponsor an orphan.']);
ok($database->int("SELECT COUNT(*) FROM contact_messages WHERE email='sara@example.org'") === 1, 'identical resubmission is not stored twice');
post('/forms/contact', ['name' => 'X', 'email' => 'bad', 'message' => 'short']);
ok($database->int('SELECT COUNT(*) FROM contact_messages') === 1, 'invalid contact form is rejected');
post('/forms/contact', ['name' => 'Spam Bot', 'email' => 'bot@example.org', 'message' => 'Buy cheap stuff now please', 'website' => 'http://spam']);
ok($database->int("SELECT COUNT(*) FROM contact_messages WHERE email='bot@example.org'") === 0, 'honeypot spam is not stored');

post('/forms/newsletter', ['name' => 'Omar', 'email' => 'Omar@Example.org', 'source' => 'footer']);
post('/forms/newsletter', ['email' => 'omar@example.org', 'source' => 'newsletter-page']);
ok($database->int("SELECT COUNT(*) FROM newsletter_subscribers WHERE email='omar@example.org'") === 1, 'newsletter signup stored once (no duplicate subscriber)');

$vol = ['full_name' => 'Ali Raza', 'guardian_name' => 'Nazeer Ahmad', 'cnic' => '35202-1234567-1', 'date_of_birth' => '2003-04-05', 'gender' => 'male',
    'street' => '3 Canal Park', 'city' => 'Lahore', 'state' => 'Punjab', 'country' => 'Pakistan', 'email' => 'ali@example.org', 'phone' => '03001234567', 'whatsapp' => '03001234567',
    'occupation_type' => 'student', 'institution' => 'LUMS', 'program' => 'BSc', 'semester' => '4', 'motivation' => 'I want to help children in need.', 'experience' => 'School charity drives.',
    'skills' => 'Teaching, design', 'availability' => ['Monday', 'Saturday']];
post('/forms/volunteer', $vol);
ok($database->int("SELECT COUNT(*) FROM volunteers WHERE email='ali@example.org'") === 1, 'volunteer application stored');
post('/forms/volunteer', ['email' => 'half@example.org', 'full_name' => 'Half Form']);
ok($database->int("SELECT COUNT(*) FROM volunteers WHERE email='half@example.org'") === 0, 'incomplete volunteer form is rejected');
[$st] = post('/forms/contact', ['name' => 'No Token', 'email' => 'nt@example.org', 'message' => 'This has no csrf token at all.', '_token' => 'wrong']);
ok($st === 403 || $st === 419, 'missing/invalid CSRF token is refused');

// --- 3. everything is visible in the admin inbox ---------------------------------------------------------------
$login = function (?int $uid) use ($app, $session): void {
    $uid === null ? $session->forget('user_id') : $session->set('user_id', $uid);
    $auth = $app->make(\App\Services\AuthService::class);
    foreach (['resolved' => false, 'cachedUser' => null, 'cachedPermissions' => null] as $prop => $val) {
        $rp = new ReflectionProperty($auth, $prop); $rp->setAccessible(true); $rp->setValue($auth, $val);
    }
};
$database->insert('roles', ['id' => 13, 'name' => 'inbox_staff', 'label' => 'Inbox staff']);
$database->insert('roles', ['id' => 14, 'name' => 'inbox_forms_only', 'label' => 'Forms only']);
foreach ([[31, 'inbox@example.org'], [32, 'formsonly@example.org']] as [$id, $mail]) {
    $database->insert('users', ['id' => $id, 'first_name' => 'U' . $id, 'last_name' => 'T', 'email' => $mail, 'password_hash' => 'x', 'status' => 'active']);
}
foreach (['dashboard.view' => 'Dashboard', 'forms.view' => 'View form submissions', 'volunteers.manage' => 'Manage volunteers'] as $pn => $pl) {
    $database->run("INSERT IGNORE INTO permissions (name, group_name, label) VALUES (:n, 'site', :l)", ['n' => $pn, 'l' => $pl]);
}
$perm = static fn (string $p): int => (int) $database->scalar('SELECT id FROM permissions WHERE name = :n', ['n' => $p]);
foreach (['dashboard.view', 'forms.view', 'volunteers.manage'] as $p) { $database->insert('role_permissions', ['role_id' => 13, 'permission_id' => $perm($p)]); }
foreach (['dashboard.view', 'forms.view'] as $p) { $database->insert('role_permissions', ['role_id' => 14, 'permission_id' => $perm($p)]); }
$database->insert('user_roles', ['user_id' => 31, 'role_id' => 13]);
$database->insert('user_roles', ['user_id' => 32, 'role_id' => 14]);
$login(31);
[$st, $h] = get('/admin/inbox/contact');
ok($st === 200 && str_contains($h, 'Sara Khan') && str_contains($h, 'Sponsoring a child'), 'admin inbox lists the contact message');
[, $h] = get('/admin/inbox/newsletter');
ok(str_contains($h, 'omar@example.org'), 'admin inbox lists the newsletter subscriber');
[, $h] = get('/admin/inbox/volunteers');
ok(str_contains($h, 'ali@example.org') || str_contains($h, 'Ali'), 'admin inbox lists the volunteer application');
$cid = (int) $database->scalar("SELECT id FROM contact_messages WHERE email='sara@example.org'");
[$st, $h] = get('/admin/inbox/contact/' . $cid);
ok($st === 200 && str_contains($h, 'how to sponsor an orphan'), 'admin can open a message');
[, $h] = get('/admin');
ok($st === 200, 'admin dashboard still opens');
$login(32);
ok(get('/admin/inbox/contact')[0] === 200 && get('/admin/inbox/volunteers')[0] === 403, 'forms-only staff cannot read volunteer applications');
$login(null);
ok(in_array(get('/admin/inbox/contact')[0], [301, 302, 401, 403], true), 'guests cannot open the inbox');

echo "$n website form checks passed.\n";
