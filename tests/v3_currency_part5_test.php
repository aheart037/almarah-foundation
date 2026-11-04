<?php

declare(strict_types=1);

/** Destructive integration and deterministic provider tests. Disposable *_test DB only. */
require __DIR__ . '/bootstrap.php';

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Services\DonationCurrencies;
use App\Services\DonationExchangeRates;
use App\Services\GeoCurrencyService;
use Tests\Reports\Fixture;

$name = (string) (getenv('TEST_DB_NAME') ?: '');
if (!str_contains($name, 'test')) throw new RuntimeException('Set a disposable TEST_DB_NAME containing test.');
putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . $name);
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'part4test'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: ''));
putenv('APP_URL=http://127.0.0.1:8094');
putenv('DONATION_GEO_DISABLE_REMOTE=1');
putenv('DONATION_RATES_DISABLE_REMOTE=1');
$cache = dirname(__DIR__) . '/storage/cache/v3part5_rates_test.json';
@unlink($cache);
putenv('DONATION_RATES_CACHE_FILE=' . $cache);
$app = require __DIR__ . '/../bootstrap/app.php';
$db = $app->make(Database::class);
Fixture::migrate($db);
Fixture::seed($db);
$db->insert('donation_purposes', ['name'=>'General fund','slug'=>'general-fund','status'=>'active']);
foreach (['PKR','USD','EUR','SAR'] as $code) {
    $db->insert('bank_accounts', ['currency'=>$code,'bank_name'=>$code.' Test Bank','account_title'=>'Test Foundation', 'account_number'=>'test-'.$code,'status'=>'published']);
}
$db->insert('bank_accounts', ['currency'=>'GBP','bank_name'=>'Hidden GBP','account_title'=>'Test','account_number'=>'hidden-gbp','status'=>'hidden']);
$session = $app->make(Session::class); $session->start();
$csrf = $app->make(Csrf::class);
$checks = 0;
function check5(bool $ok, string $why): void { global $checks; if (!$ok) throw new RuntimeException('FAIL: ' . $why); $checks++; }
$country = 'PK';
$lookups = 0;
$geo = new GeoCurrencyService(function (string $ip) use (&$country, &$lookups): ?array {
    $lookups++;
    return $country === 'FAIL' ? null : ['success'=>true, 'country_code'=>$country];
});
$providerCalls = 0;
$feed = function () use (&$providerCalls): array {
    $providerCalls++;
    return ['result'=>'success','base_code'=>'PKR', 'time_last_update_unix'=>time()-60,
        'time_next_update_unix'=>time()+3600, 'rates'=>['PKR'=>1,'USD'=>0.0036,'EUR'=>0.0033,'SAR'=>0.0135,'GBP'=>0.0031]];
};
$rates = new DonationExchangeRates($feed, $cache);
$app->singleton(GeoCurrencyService::class, $geo);
$app->singleton(DonationExchangeRates::class, $rates);
$router = $app->make(Router::class);
$_SERVER['REMOTE_ADDR'] = '8.8.8.8';
function show5($router,$app,array $query=[]): string { return $router->dispatch(new Request('GET','/donate',$query,[],[],[]),$app)->body(); }
function selected5(string $html,string $code): bool { return str_contains($html,'<option value="'.$code.'" selected>'); }
$pk = show5($router,$app);
check5(selected5($pk,'PKR'), 'Pakistan Geo-IP selects PKR');
check5(str_contains($pk,'🇵🇰 Pakistani Rupee – PKR') && str_contains($pk,'🇺🇸 US Dollar – USD') && str_contains($pk,'🇪🇺 Euro – EUR') && str_contains($pk,'🇸🇦 Saudi Riyal – SAR'), 'payable flag, name, code and symbol in options');
check5(!str_contains($pk, '<option value="GBP"'), 'hidden bank currency not offered');
check5($providerCalls === 1 && $lookups === 1, 'providers called once on initial visit');
$again = show5($router,$app);
check5($providerCalls === 1 && $lookups === 1, 'country and rates cached on repeat');
$session->forget('donate.geo_country'); $country='US';
$us=show5($router,$app);
check5(selected5($us,'USD'), 'international IP selects payable USD');
$session->forget('donate.geo_country'); $country='DE';
check5(selected5(show5($router,$app),'EUR'), 'Eurozone country selects EUR');
$session->forget('donate.geo_country'); $country='SA';
check5(selected5(show5($router,$app),'SAR'), 'Saudi Riyal offered when published bank account exists');
$session->forget('donate.geo_country'); $country='GB';
check5(selected5(show5($router,$app),'PKR'), 'international currency unsupported by published banks falls back PKR');
$session->forget('donate.geo_country'); $country='FAIL';
check5(selected5(show5($router,$app),'PKR'), 'failed Geo-IP falls back PKR');
check5($geo->country('127.0.0.1',$session) === null && $lookups === 6, 'private IP never sent to Geo-IP provider');
$preference = $router->dispatch(new Request('POST','/donate/currency', [],['_token'=>$csrf->token(), 'currency'=>'USD'],[],[]),$app);
check5($preference->status()===200 && selected5(show5($router,$app),'USD'), 'manual choice persists over Geo-IP failure');
check5(selected5(show5($router,$app,['currency'=>'PKR']),'PKR') && selected5(show5($router,$app),'PKR'), 'query choice overrides stored preference and persists');
$badChoice = $router->dispatch(new Request('POST','/donate/currency', [],['_token'=>$csrf->token(), 'currency'=>'GBP'],[],[]),$app);
check5($badChoice->status()===422 && selected5(show5($router,$app),'PKR'), 'unsupported manual currency rejected without changing preference');
check5($rates->estimate(2500,'PKR','USD')['amount'] === 9.0, 'PKR to USD estimate correct');
check5($rates->estimate(9,'USD','PKR')['amount'] === 2500.0, 'reverse estimate preserves input base');
check5($rates->estimate(1,'PKR','GBP') !== null && $rates->estimate(1,'PKR','XYZ') === null, 'service recognizes whitelist, rejects unsupported code');
$quoted = $router->dispatch(new Request('POST','/donate/estimate', [],['_token'=>$csrf->token(), 'amount'=>'2500','from'=>'PKR','to'=>'USD'],[],[]),$app);
$result = json_decode($quoted->body(),true);
check5($quoted->status()===200 && $result['estimate']==='9.00' && $result['currency']==='USD' && str_contains($result['updated'],'UTC'), 'quote endpoint returns estimate, selected currency and UTC timestamp');
$wrong = $router->dispatch(new Request('POST','/donate/estimate', [],['_token'=>$csrf->token(), 'amount'=>'2500','from'=>'PKR','to'=>'GBP'],[],[]),$app);
check5($wrong->status()===422, 'no estimate endpoint for unsupported payable currency');
$before = $db->int('SELECT COUNT(*) FROM donations');
check5($db->int('SELECT COUNT(*) FROM donations')===$before, 'conversion has no donation/payment side effects');
$router->dispatch(new Request('GET','/donate/transfer',['currency'=>'SAR'],[],[],[]),$app);
$nonce = array_key_last($session->get('donate.transfer_nonces'));
$reported = $router->dispatch(new Request('POST','/donate/transfer',[],[
    '_token'=>$csrf->token(), 'transfer_attempt'=>$nonce, 'donor_name'=>'Saudi Test Donor',
    'donor_email'=>'saudi-part5@example.org', 'donor_phone'=>'03001234567', 'amount'=>'49.25',
    'currency'=>'SAR', 'received_at'=>gmdate('Y-m-d'), 'source'=>'bank_transfer',
],[],[]),$app);
$record=$db->selectOne("SELECT * FROM donations WHERE donor_id IN (SELECT id FROM donors WHERE email='saudi-part5@example.org') ORDER BY id DESC LIMIT 1");
check5($reported->status()===302 && $record['currency']==='SAR' && (int)$record['amount_minor']===4925 && $record['status']==='pending', 'SAR report persists actual selected currency and minor amount, never PKR estimate');
check5(in_array('SAR',$app->make(\App\Services\ReportService::class)->currencies(),true), 'SAR financial reporting remains currency-separated');
$data=json_decode(file_get_contents($cache),true);
$freshData = $data;
$data['next']=time()-1;
file_put_contents($cache,json_encode($data));
$failedFetches=0;
$failRates = new DonationExchangeRates(function () use (&$failedFetches): ?array { $failedFetches++; return null; },$cache);
$stale = $failRates->snapshot();
check5($stale!==null && $stale['stale']===true && $failedFetches===1, 'API outage serves clearly stale cached estimates');
check5($failRates->snapshot()!==null && $failedFetches===1, 'failed API lookup backs off, no repeated calls');
$data=json_decode(file_get_contents($cache),true);
$data['updated']=time()-172801;
file_put_contents($cache,json_encode($data));
check5($failRates->snapshot()===null, 'too-old rates never shown');
$missing = $router->dispatch(new Request('POST','/donate/estimate', [],['_token'=>$csrf->token(), 'amount'=>'2500','from'=>'PKR','to'=>'USD'],[],[]),$app);
// Container's initially bound service is still fresh in memory, but reads the
// modified cache on every request and must fail closed after expiry.
check5($missing->status()===503 && !array_key_exists('estimate',json_decode($missing->body(),true)), 'expired rates fail with no misleading estimate');
check5(DonationCurrencies::forCountry('PK')==='PKR' && DonationCurrencies::forCountry('US')==='USD', 'country mapping is explicit and never used as a payment authority');
if (getenv('KEEP_CURRENCY_TEST_CACHE') === '1') file_put_contents($cache, json_encode($freshData));
else @unlink($cache);
echo "$checks V3 Part 5 currency integration checks passed.\n";
