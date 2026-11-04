<?php

declare(strict_types=1);

/** Destructive integration test: run ONLY against a disposable *_test database. */
require __DIR__ . '/bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\Csrf;
use Tests\Reports\Fixture;

$name = (string) (getenv('TEST_DB_NAME') ?: '');
if (!str_contains($name, 'test')) throw new RuntimeException('Set a disposable TEST_DB_NAME containing test.');
putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . $name);
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'part4test'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: ''));
putenv('APP_URL=' . (getenv('TEST_APP_URL') ?: 'http://127.0.0.1:8094'));
putenv('DONATION_RATES_DISABLE_REMOTE=1');
putenv('DONATION_RATES_CACHE_FILE=' . __DIR__ . '/../storage/cache/v3part4_rates_test.json');
putenv('DONATION_GEO_DISABLE_REMOTE=1');
$app = require __DIR__ . '/../bootstrap/app.php';
$db = $app->make(Database::class);
Fixture::migrate($db);
Fixture::seed($db);
$db->insert('donation_purposes', ['name'=>'General fund','slug'=>'general-fund','description'=>'Support our work','status'=>'active']);
foreach ([['PKR','PKR Account','pk-r-test'], ['USD','USD Account','us-d-test'], ['EUR','Hidden account','eu-r-test']] as [$code,$label,$account]) {
    $db->insert('bank_accounts', ['currency'=>$code, 'bank_name'=>$label,'account_title'=>'Test foundation','account_number'=>$account,'status'=>$code === 'EUR' ? 'hidden' : 'published']);
}
$session = $app->make(Session::class);
$session->start();
$router = $app->make(Router::class);
$csrf = $app->make(Csrf::class);
$checks = 0;
function ok4(bool $result, string $message): void { global $checks; if (!$result) throw new RuntimeException('FAIL: '.$message); $checks++; }
function page4(Router $router, $app, string $path, array $query = []): \App\Core\Response {
    return $router->dispatch(new Request('GET', $path, $query, [], [], []), $app);
}
$index = page4($router, $app, '/donate');
$html = $index->body();
ok4($index->status() === 200 && str_contains($html, 'data-simple-donation'), 'V1-style form renders');
ok4(str_contains($html, 'name="donation_attempt"') && str_contains($html, 'name="currency"'), 'one-use attempt and explicit currency');
ok4(str_contains($html, 'value="PKR" selected') && str_contains($html, 'value="USD"') && !str_contains($html, 'value="EUR"'), 'only published bank currencies appear');
ok4(str_contains($html, 'data-bank-currency="USD"') && !str_contains($html, 'eu-r-test'), 'foreign published bank is shown, hidden account never shown');
ok4(str_contains($html, 'Additional details (optional)') && str_contains($html, 'name="email"') && str_contains($html, 'name="name"'), 'essential details visible, extra information folded');
ok4(!str_contains($html, 'name="gateway" value="meezan"'), 'unconfigured online gateway not offered');
ok4(!str_contains($html, 'tax-deductible under section'), 'no unsupported tax-exemption claim on restored form');
$usd = page4($router, $app, '/donate', ['currency'=>'USD']);
ok4(str_contains($usd->body(), '<option value="USD" selected>') && str_contains($usd->body(), 'bank-mini" data-bank-currency="USD"'), 'USD selection keeps bank details in the right currency');
$bad = page4($router, $app, '/donate', ['currency'=>'EUR']);
ok4(str_contains($bad->body(), '<option value="PKR" selected>'), 'unsupported currency falls back to PKR');
$nonces = $session->get('donate.form_nonces');
ok4(count($nonces) === 3 && count(array_unique(array_keys($nonces))) === 3, 'separate tabs have distinct one-use tokens');
$transfer = page4($router, $app, '/donate/transfer', ['currency'=>'USD']);
ok4($transfer->status() === 200 && str_contains($transfer->body(), '<option value="USD" selected>') && !str_contains($transfer->body(), 'value="EUR"'), 'transfer report displays published currency');
$nonce = array_key_last($session->get('donate.transfer_nonces'));
$post = ['_token'=>$csrf->token(), 'transfer_attempt'=>$nonce,'donor_name'=>'Test Guest','donor_email'=>'guest4@example.org','donor_phone'=>'03001234567','amount'=>'55.25','currency'=>'USD','received_at'=>gmdate('Y-m-d'),'source'=>'bank_transfer'];
$before = $db->int('SELECT COUNT(*) FROM donations');
$made = $router->dispatch(new Request('POST','/donate/transfer', [], $post, [], []), $app);
ok4($made->status() === 302 && $db->int('SELECT COUNT(*) FROM donations') === $before + 1, 'guest transfer creates one pending donation');
$created = $db->selectOne("SELECT d.* FROM donations d JOIN donors donor ON donor.id=d.donor_id WHERE donor.email='guest4@example.org' ORDER BY d.id DESC LIMIT 1");
ok4($created['currency'] === 'USD' && (int) $created['amount_minor'] === 5525 && $created['status'] === 'pending' && $created['verification_status'] === 'submitted', 'USD minor units preserved and no false completion');
$again = $router->dispatch(new Request('POST','/donate/transfer', [], $post, [], []), $app);
ok4($again->status() === 302 && $db->int('SELECT COUNT(*) FROM donations') === $before + 1, 'repeat submission cannot create second financial record');
$invalid = $post; $invalid['currency'] = 'EUR';
$blocked = $router->dispatch(new Request('POST','/donate/transfer', [], $invalid, [], []), $app);
ok4($blocked->status() === 302 && $db->int('SELECT COUNT(*) FROM donations') === $before + 1, 'hidden currency rejected server-side');
$status = page4($router, $app, '/donation/processing', ['ref'=>$created['public_reference']]);
$fakeSuccess = page4($router, $app, '/donation/success', ['ref'=>$created['public_reference']]);
ok4($status->status() === 200 && $fakeSuccess->status() === 200 && str_contains($fakeSuccess->body(), 'data-pr="processing"') && !str_contains($fakeSuccess->body(), 'data-pr="completed"'), 'success URL cannot turn pending gift into success');
ok4(str_contains($status->body(), 'Transfer report received') && !str_contains($status->body(), 'Verified by the bank'), 'pending offline transfer is described as staff review, not bank confirmation');
$db->update('donations', ['verification_status'=>'legacy'], 'id=:id', ['id'=>1]);
$legacy = page4($router, $app, '/donation/DON-0001');
ok4(str_contains($legacy->body(), 'Verification not established') && !str_contains($legacy->body(), 'Print or save receipt'), 'historical completed legacy record not presented as a verified gift');
$db->update('donations', ['verification_status'=>'verified'], 'id=:id', ['id'=>1]);
$verified = page4($router, $app, '/donation/DON-0001');
ok4(str_contains($verified->body(), 'Print or save receipt') && str_contains($verified->body(), 'Payment Successful'), 'verified completed gift retains receipt flow');
$failed = page4($router, $app, '/donation/DON-0005');
ok4(str_contains($failed->body(), 'Payment Failed') && str_contains($failed->body(), 'href="http://127.0.0.1:8094/donate"'), 'failed gift gives a usable retry URL');
// Existing payment callback and database-backed status routes remain registered.
$routes = implode("\n", $router->routeList());
ok4(str_contains($routes,'/payments/meezan/return') && str_contains($routes,'/donation/{reference}'), 'gateway callback and verified payment-result routes preserved');
echo "$checks V3 Part 4 donation form integration checks passed.\n";
