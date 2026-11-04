<?php

declare(strict_types=1);

// v3.1 — CSV / XLSX report export (service level, no database needed).
// Run: php tests/reports_export_v31_test.php

require __DIR__ . '/../vendor/autoload.php';

use App\Services\ReportExportService;

$fail = 0;
$check = static function (bool $ok, string $label) use (&$fail): void {
    echo ($ok ? "  ok   " : "  FAIL ") . $label . PHP_EOL;
    if (!$ok) { $fail++; }
};

$svc = new ReportExportService();
$report = [
    'title' => 'Test Report', 'basis' => 'Test basis', 'total' => 2, 'truncated' => false,
    'summary' => [['label' => 'Total', 'value' => 3, 'kind' => 'int']],
    'columns' => [['key' => 'name', 'label' => 'Name', 'type' => 'text'], ['key' => 'n', 'label' => 'Count', 'type' => 'int']],
    'rows' => [['name' => '=HYPERLINK("http://x")', 'n' => 1], ['name' => 'Zainab — زینب', 'n' => 2]],
    'extra' => [], 'notes' => ['A note'],
];
$sheet = $svc->sheet($report, [['Date range', 'All time']], 'PKR', 'Almarah Foundation');

$csv = $svc->csv($sheet);
$check(str_starts_with($csv, "\xEF\xBB\xBF"), 'CSV starts with UTF-8 BOM');
$check(str_contains($csv, 'Zainab — زینب'), 'CSV keeps non-Latin text');
$check(!str_contains($csv, '"=HYPERLINK') && str_contains($csv, "'=HYPERLINK"), 'CSV neutralises formula cells');
$check(str_contains($csv, 'Test Report') && str_contains($csv, 'Date range'), 'CSV has title and filters');

$xlsx = $svc->xlsx($sheet, 'Test Report');
$check(str_starts_with($xlsx, 'PK'), 'XLSX is a zip');
$tmp = tempnam(sys_get_temp_dir(), 'x');
file_put_contents($tmp, $xlsx);
if (class_exists('ZipArchive')) {
    $z = new ZipArchive();
    $check($z->open($tmp) === true && $z->numFiles === 6, 'XLSX opens with 6 parts');
    $sheetXml = (string) $z->getFromName('xl/worksheets/sheet1.xml');
    $dom = new DOMDocument();
    $check($dom->loadXML($sheetXml) !== false, 'sheet XML is well-formed');
    $check(str_contains($sheetXml, 'زینب') && !str_contains($sheetXml, '>=HYPERLINK'), 'XLSX keeps text, neutralises formulas');
}
unlink($tmp);

echo $fail === 0 ? "ALL PASSED\n" : "$fail FAILED\n";
exit($fail === 0 ? 0 : 1);
