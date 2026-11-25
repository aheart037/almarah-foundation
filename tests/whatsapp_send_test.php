<?php

declare(strict_types=1);

/** WhatsApp sending: phone formatting and template variables. No database needed:  php tests/whatsapp_send_test.php */
require __DIR__ . '/bootstrap.php';

use App\Services\WetarseelClient;

$n = 0;
function chk(bool $ok, string $msg): void { global $n; if (!$ok) { throw new RuntimeException('FAIL: ' . $msg); } $n++; echo "ok $msg\n"; }

foreach ([['0300 1234567', '923001234567'], ['+92 300 1234567', '923001234567'], ['03001234567', '923001234567'],
          ['3001234567', '923001234567'], ['0092-300-1234567', '923001234567'], ['923001234567', '923001234567'],
          ['+1 (415) 555-2671', '14155552671'], ['+971501234567', '971501234567']] as [$in, $out]) {
    chk(WetarseelClient::phone($in) === $out, "phone $in -> $out");
}
foreach (['', 'abc', '12345', '+1234567890123456'] as $bad) {
    chk(WetarseelClient::phone($bad) === null, 'phone rejected: "' . $bad . '"');
}
chk(WetarseelClient::cleanParameters(["Ali\nKhan", '', "a    b\tc"]) === ['Ali Khan', '-', 'a b c'], 'template variables: no line breaks, tabs or empty values');
chk(!(new WetarseelClient())->isConfigured(), 'client is not configured without an API key');
echo "\n$n checks passed\n";
