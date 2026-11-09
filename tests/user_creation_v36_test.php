<?php

declare(strict_types=1);

/**
 * V3.6: admin creates a user with a password and role(s); role privilege table; password reset still works.
 *   TEST_DB_NAME=almarah_test php tests/user_creation_v36_test.php
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
$login = function (?int $uid) use ($app, $session): void {
    $uid === null ? $session->forget('user_id') : $session->set('user_id', $uid);
    $auth = $app->make(\App\Services\AuthService::class);
    foreach (['resolved' => false, 'cachedUser' => null, 'cachedPermissions' => null] as $prop => $val) {
        $rp = new ReflectionProperty($auth, $prop); $rp->setAccessible(true); $rp->setValue($auth, $val);
    }
};
function call(string $method, string $path, array $data = []): array
{
    global $router, $app;
    if ($method === 'POST') { $data += ['_token' => $app->make(Csrf::class)->token()]; }
    try {
        $r = $router->dispatch(new Request($method, $path, $method === 'GET' ? $data : [], $method === 'POST' ? $data : [], [], []), $app);
        return [$r->status(), $r->body(), $r->headers()['Location'] ?? ''];
    } catch (HttpException $e) { return [$e->statusCode(), '', '']; }
}
$roleId = static fn (string $nme): int => (int) $database->scalar('SELECT id FROM roles WHERE name = :n', ['n' => $nme]);

// actors: a Super Admin and a non-super user who may create users
$database->insert('users', ['id' => 51, 'first_name' => 'Super', 'last_name' => 'Admin', 'email' => 'super@example.org', 'password_hash' => 'x', 'status' => 'active']);
$database->insert('user_roles', ['user_id' => 51, 'role_id' => $roleId('super_admin')]);
$database->run("INSERT IGNORE INTO permissions (name, group_name, label) VALUES ('users.create', 'users', 'Create users'), ('users.view', 'users', 'View users'), ('dashboard.view', 'site', 'Dashboard')");
$database->insert('roles', ['id' => 40, 'name' => 'user_helper', 'label' => 'User helper']);
foreach (['users.create', 'users.view', 'dashboard.view'] as $p) { $database->insert('role_permissions', ['role_id' => 40, 'permission_id' => (int) $database->scalar('SELECT id FROM permissions WHERE name=:n', ['n' => $p])]); }
$database->insert('users', ['id' => 52, 'first_name' => 'Help', 'last_name' => 'Er', 'email' => 'helper@example.org', 'password_hash' => 'x', 'status' => 'active']);
$database->insert('user_roles', ['user_id' => 52, 'role_id' => 40]);

$database->run("INSERT IGNORE INTO roles (name, label, description) VALUES ('finance_manager', 'Finance Manager', 'Verifies donations'), ('content_manager', 'Content Manager', 'Edits the website')");
foreach (['donations.record', 'donations.verify'] as $pn) {
    $database->run('INSERT IGNORE INTO role_permissions (role_id, permission_id) SELECT r.id, p.id FROM roles r JOIN permissions p ON p.name = :p WHERE r.name = \'finance_manager\'', ['p' => $pn]);
}

// --- 1. form shows password options and the role/privilege table ---------------------------------------------------
$login(51);
[$st, $html] = call('GET', '/admin/users/create');
ok($st === 200 && str_contains($html, 'name="password"') && str_contains($html, 'name="password_confirmation"') && str_contains($html, 'name="roles[]"'), 'form has password, confirmation and role selection');
ok(str_contains($html, 'Privileges this role has') && str_contains($html, 'Full access to everything') && str_contains($html, 'Finance Manager') && str_contains($html, 'Content Manager') && str_contains($html, 'No privileges assigned yet') && str_contains($html, 'Record offline donations'), 'role table lists what each role can do');
ok(!str_contains($html, 'disabled'), 'Super Admin can tick roles');

// --- 2. create with password + role -----------------------------------------------------------------------------
$good = ['first_name' => 'Fatima', 'last_name' => 'Noor', 'email' => 'Fatima@Example.org', 'phone' => '0300555', 'password_mode' => 'set', 'password' => 'Str0ngPassw0rd!', 'password_confirmation' => 'Str0ngPassw0rd!', 'roles' => ['finance_manager']];
[$st, , $loc] = call('POST', '/admin/users', $good);
$u = $database->selectOne("SELECT * FROM users WHERE email = 'fatima@example.org'");
ok($st === 302 && $u && str_contains($loc, '/admin/users/' . $u['id']), 'user created with a chosen password');
ok(password_verify('Str0ngPassw0rd!', (string) $u['password_hash']) && $u['status'] === 'active' && $u['phone'] === '0300555', 'password stored hashed and works');
ok($database->int('SELECT COUNT(*) FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=:u AND r.name=:n', ['u' => $u['id'], 'n' => 'finance_manager']) === 1, 'selected role assigned at creation');
ok($database->int("SELECT COUNT(*) FROM audit_logs WHERE action='user.created' AND entity_id = :i", ['i' => (string) $u['id']]) === 1 && $database->int("SELECT COUNT(*) FROM audit_logs WHERE action='user.roles_updated'") >= 1, 'creation and role assignment are audited');
$hash = (string) $u['password_hash'];
ok(!str_contains(json_encode($database->select("SELECT safe_metadata_json FROM audit_logs WHERE action='user.created'")), 'Str0ngPassw0rd'), 'password is never written to the audit log');

// --- 3. validation ---------------------------------------------------------------------------------------------
$bad = static fn (array $over) => call('POST', '/admin/users', array_merge($good, ['email' => 'v' . random_int(1000, 9999) . '@example.org'], $over));
$before = $database->int('SELECT COUNT(*) FROM users');
$bad(['password' => 'short1', 'password_confirmation' => 'short1']);
$bad(['password' => 'onlylettersnodigits', 'password_confirmation' => 'onlylettersnodigits']);
$bad(['password_confirmation' => 'Different1234']);
$bad(['roles' => []]);
$bad(['roles' => ['no_such_role']]);
call('POST', '/admin/users', array_merge($good, ['email' => 'fatima@example.org']));
ok($database->int('SELECT COUNT(*) FROM users') === $before, 'weak/mismatched password, missing or unknown role, duplicate email are all rejected');

// --- 4. email-link mode + password reset ------------------------------------------------------------------------
call('POST', '/admin/users', ['first_name' => 'Link', 'last_name' => 'User', 'email' => 'link@example.org', 'password_mode' => 'email', 'roles' => ['content_manager']]);
$l = $database->selectOne("SELECT * FROM users WHERE email='link@example.org'");
ok($l !== null && $database->int('SELECT COUNT(*) FROM password_reset_tokens WHERE user_id=:u', ['u' => $l['id']]) === 1, 'email-link mode creates the user and a password setup token');
$auth = $app->make(\App\Services\AuthService::class);
ok($auth->sendPasswordReset('fatima@example.org') === true && $database->int('SELECT COUNT(*) FROM password_reset_tokens WHERE user_id=:u', ['u' => $u['id']]) === 1, 'a user created with a password can later request a reset');
ok((string) $database->scalar('SELECT password_hash FROM users WHERE id=:i', ['i' => $u['id']]) === $hash, 'requesting a reset does not change the password until it is completed');
[$st] = call('GET', '/forgot-password');
$login(null);
[$st] = call('GET', '/forgot-password');
ok($st === 200, 'Forgot password page is available to the user');

// --- 5. non-super users cannot hand out roles ------------------------------------------------------------------
$login(52);
[$st, $html] = call('GET', '/admin/users/create');
ok($st === 200 && str_contains($html, 'Only a Super Admin can assign roles') && str_contains($html, 'disabled'), 'non-super sees the table but cannot tick roles');
call('POST', '/admin/users', array_merge($good, ['email' => 'sneaky@example.org', 'roles' => ['super_admin']]));
$s = $database->selectOne("SELECT id FROM users WHERE email='sneaky@example.org'");
ok($s !== null && $database->int('SELECT COUNT(*) FROM user_roles WHERE user_id=:u', ['u' => $s['id']]) === 0, 'a role posted by a non-super is ignored (no privilege escalation)');
$login(null);
ok(in_array(call('GET', '/admin/users/create')[0], [301, 302, 401, 403], true) && in_array(call('POST', '/admin/users', $good)[0], [301, 302, 401, 403, 419], true), 'guests cannot create users');

echo "$n user-creation checks passed.\n";
