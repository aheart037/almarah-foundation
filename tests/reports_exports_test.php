<?php

declare(strict_types=1);

/** Destructive integration suite for real authenticated export endpoints. */
require __DIR__ . '/bootstrap.php';

use Tests\Reports\Fixture;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Services\AuthService;
use App\Services\ReportService;

putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . (getenv('TEST_DB_NAME') ?: 'almarah_test'));
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
$db=Fixture::connect();
Fixture::migrate($db);
Fixture::seed($db);
foreach (['dashboard.view','reports.view'] as $name) {
    $id=$db->insert('permissions',['name'=>$name,'group_name'=>'reports','label'=>$name]);
    $db->insert('role_permissions',['role_id'=>1,'permission_id'=>$id]);
}
$app=require __DIR__.'/../bootstrap/app.php';
$session=$app->make(Session::class); $session->start();
$auth=$app->make(AuthService::class);
$router=$app->make(Router::class);
$auth->login($db->selectOne('SELECT * FROM users WHERE id=1'),'127.0.0.1');
$ok=0; $pdfResults=[];
function expCheck(bool $value,string $label): void { global $ok; if (!$value) throw new RuntimeException('FAIL: '.$label); $ok++; }
function exportGet(Router $router,$app,string $path,array $query=[]): \App\Core\Response {
    return $router->dispatch(new Request('GET',$path,$query,[],[],[]),$app);
}
function tempFileOf(\App\Core\Response $response): string {
    $ref=new ReflectionProperty(\App\Core\Response::class,'filePath');
    $ref->setAccessible(true);
    $path=$ref->getValue($response);
    if (!is_string($path) || !is_file($path)) throw new RuntimeException('Expected private export file.');
    return $path;
}
$filter=['date_from'=>'2026-10-01','date_to'=>'2026-10-31','currency'=>'PKR'];
$oneDay=$app->make(ReportService::class)->run('donations',['date_from'=>'2026-10-05','date_to'=>'2026-10-05']);
$verifiedCard=array_values(array_filter($oneDay['summary'],static fn(array $c): bool => $c['label']==='Confirmed amount'))[0]['value'] ?? null;
expCheck((int)$verifiedCard===500000,'inclusive single-day filter uses UTC even when configured app timezone is not UTC');
foreach (ReportService::catalog() as $slug=>$item) {
    $query=$filter;
    if ($slug==='reconciliation') $query['issue']='all';
    if ($slug==='audit') $query['who']='all';
    $url='/admin/reports/'.$slug;
    $resp=exportGet($router,$app,$url.'/pdf',$query);
    expCheck($resp->status()===200 && str_contains((string)($resp->headers()['Content-Type']??''),'application/pdf') && str_starts_with($resp->body(),'%PDF-'),$slug.' PDF endpoint delivers real PDF');
    $pdf=tempnam(sys_get_temp_dir(),'pdf-check-'); file_put_contents($pdf,$resp->body());
    $info=shell_exec('pdfinfo '.escapeshellarg($pdf).' 2>&1') ?: '';
    $text=shell_exec('pdftotext -layout '.escapeshellarg($pdf).' - 2>&1') ?: '';
    expCheck(str_contains($info,'Pages:') && str_contains($info,'Page size:'),$slug.' PDF opens in independent PDF parser');
    expCheck(str_contains($text,$item['title']) && str_contains($text,'ALMARAH FOUNDATION') && str_contains($text,'Page 1 of') && str_contains($text,'confidential'),$slug.' title, logo-side branding, footer, pagination');
    $currencyFilter=in_array('currency',array_column($app->make(ReportService::class)->filterDefs($slug),'name'),true);
    expCheck(str_contains($text,'1 Oct 2026 to 31 Oct 2026') && (!$currencyFilter || str_contains($text,'Currency')) && str_contains($text,'Records') && str_contains($text,'Generated'),$slug.' date range, filters, counts and generated time');
    expCheck(str_contains($resp->body(),'/Subtype /Image'),$slug.' real bundled foundation logo image embedded');
    $pdfResults[$slug]='PASS';
    unlink($pdf);
    $csv=exportGet($router,$app,$url.'/csv',$query);
    $csvPath=tempFileOf($csv);
    $csvBytes=file_get_contents($csvPath);
    expCheck(str_starts_with($csvBytes,"\xEF\xBB\xBF") && str_contains($csvBytes,$item['title']) && str_contains($csvBytes,'1 Oct 2026 to 31 Oct 2026') && str_contains($csvBytes,'Records'),$slug.' real CSV has active filters and summary');
    expCheck(str_contains((string)$csv->headers()['Content-Disposition'],'.csv"'),$slug.' CSV download header');
    unlink($csvPath);
    $xlsx=exportGet($router,$app,$url.'/xlsx',$query);
    $xlsxPath=tempFileOf($xlsx);
    $zip=new ZipArchive();
    expCheck($zip->open($xlsxPath)===true,$slug.' Excel export is real ZIP-based XLSX');
    $sheet=$zip->getFromName('xl/worksheets/sheet1.xml');
    $workbook=$zip->getFromName('xl/workbook.xml');
    $styles=$zip->getFromName('xl/styles.xml');
    expCheck(is_string($sheet) && simplexml_load_string($sheet)!==false && is_string($workbook) && simplexml_load_string($workbook)!==false && is_string($styles) && simplexml_load_string($styles)!==false,$slug.' Excel worksheet/workbook/styles parse as OOXML');
    expCheck(str_contains($sheet,htmlspecialchars($item['title'],ENT_XML1,'UTF-8')) && str_contains($sheet,'1 Oct 2026 to 31 Oct 2026') && str_contains($sheet,'Records'),$slug.' Excel has actual title and filters');
    $zip->close(); unlink($xlsxPath);
}
$financialCsv=exportGet($router,$app,'/admin/reports/financial/csv',$filter);
$financialCsvText=file_get_contents(tempFileOf($financialCsv));
expCheck(str_contains($financialCsvText,'Rs 13,300') && str_contains($financialCsvText,'Rs 10,300') && str_contains($financialCsvText,'Rs 3,000'),'CSV financial totals use the same verified/refund accounting as the report');
unlink(tempFileOf($financialCsv));
$financialXlsx=exportGet($router,$app,'/admin/reports/financial/xlsx',$filter);
$financialZip=new ZipArchive();$financialZip->open(tempFileOf($financialXlsx));
$financialSheet=$financialZip->getFromName('xl/worksheets/sheet1.xml');
expCheck(str_contains($financialSheet,'<v>13300.00</v>') && str_contains($financialSheet,'<v>3000.00</v>') && str_contains($financialSheet,'<v>10300.00</v>'),'Excel financial cells are numeric major currency units, not hardcoded strings');
$financialZip->close();unlink(tempFileOf($financialXlsx));
$overviewPdf=exportGet($router,$app,'/admin/reports/overview/pdf',$filter)->body();
$overviewTemp=tempnam(sys_get_temp_dir(),'overview-pdf-');file_put_contents($overviewTemp,$overviewPdf);
$overviewText=shell_exec('pdftotext -layout '.escapeshellarg($overviewTemp).' -') ?: '';
expCheck(str_contains($overviewText,'Verified donation trend') && str_contains($overviewText,'Recent transactions'),'overview PDF includes actual trends and recent activity tables');
unlink($overviewTemp);
$overviewCsv=exportGet($router,$app,'/admin/reports/overview/csv',$filter);
expCheck(str_contains(file_get_contents(tempFileOf($overviewCsv)),'Verified donation trend'),'overview CSV includes the trend breakdown, not only headline totals');
unlink(tempFileOf($overviewCsv));
// Financial figures must match report service, not merely be printable decorations.
$pdf=exportGet($router,$app,'/admin/reports/financial/pdf',$filter)->body();
$path=tempnam(sys_get_temp_dir(),'pdf-figures-'); file_put_contents($path,$pdf);
$text=shell_exec('pdftotext -layout '.escapeshellarg($path).' -') ?: '';
expCheck(str_contains($text,'13,300') && str_contains($text,'10,300') && str_contains($text,'3,000'), 'PDF financial gross, net and refund totals match verified donation and refund records');
unlink($path);
$filtered=exportGet($router,$app,'/admin/reports/payments/pdf',$filter+['status'=>'failed']);
$path=tempnam(sys_get_temp_dir(),'pdf-filter-');file_put_contents($path,$filtered->body());
$text=shell_exec('pdftotext -layout '.escapeshellarg($path).' -') ?: '';
expCheck(str_contains($text,'Failed') && str_contains($text,'DON-0003') && !str_contains($text,'DON-0001'), 'PDF status filter excludes other transactions');
unlink($path);
foreach (array_keys(ReportService::catalog()) as $slug) {
    $query=['date_from'=>'2030-01-01','date_to'=>'2030-01-31','q'=>'unmatched-export-search'];
    $pdf=exportGet($router,$app,'/admin/reports/'.$slug.'/pdf',$query)->body();
    expCheck(str_contains($pdf,'%PDF-') && str_contains($pdf,'%%EOF'),$slug.' empty PDF is valid');
    $temp=tempnam(sys_get_temp_dir(),'empty-pdf-');file_put_contents($temp,$pdf);
    $txt=shell_exec('pdftotext '.escapeshellarg($temp).' -') ?: '';
    expCheck(str_contains($txt,'No records match the selected filters'),$slug.' empty-state text rendered');
    unlink($temp);
}
// Formula injection must never turn an untrusted donor name into spreadsheet code.
$db->update('donors',['name'=>'=HYPERLINK("https://invalid.example","x")'],'id=:id',['id'=>1]);
$csv=exportGet($router,$app,'/admin/reports/donors/csv',$filter);
$bytes=file_get_contents(tempFileOf($csv));
expCheck(str_contains($bytes,"'=HYPERLINK"),'CSV neutralizes untrusted spreadsheet formula');
unlink(tempFileOf($csv));
$xlsx=exportGet($router,$app,'/admin/reports/donors/xlsx',$filter);
$z=new ZipArchive();$z->open(tempFileOf($xlsx));$xml=$z->getFromName('xl/worksheets/sheet1.xml');
expCheck(str_contains($xml,'t="inlineStr"') && str_contains($xml,'=HYPERLINK') && !str_contains($xml,'<f>'),'Excel keeps untrusted names as inert inline strings');
$z->close();unlink(tempFileOf($xlsx));
// Cross the former 5,000-row PDF cap: CSV/Excel must export every filtered row.
for($i=1;$i<=5020;$i++) $db->insert('audit_logs',['user_id'=>1,'action'=>'export.bulk','entity_type'=>'report','entity_id'=>(string)$i,'safe_metadata_json'=>'{}','created_at'=>'2026-10-30 10:00:00']);
$bulk=['date_from'=>'2026-10-30','date_to'=>'2026-10-30','area'=>'export','who'=>'all'];
$csv=exportGet($router,$app,'/admin/reports/audit/csv',$bulk);
$bytes=file_get_contents(tempFileOf($csv));
expCheck(str_contains($bytes,'report #1') && str_contains($bytes,'report #5020') && substr_count($bytes,',export.bulk,')===5020,'CSV streams more than 5,000 records without truncation');
unlink(tempFileOf($csv));
$xlsx=exportGet($router,$app,'/admin/reports/audit/xlsx',$bulk);
$z=new ZipArchive();$z->open(tempFileOf($xlsx));$sheet=$z->getFromName('xl/worksheets/sheet1.xml');
expCheck(substr_count($sheet,'report #')===5020 && str_contains($sheet,'report #5020'),'Excel includes rows beyond 5,000');
$z->close();unlink(tempFileOf($xlsx));
$bulkPdf=exportGet($router,$app,'/admin/reports/audit/pdf',$bulk)->body();
$pdfPath=tempnam(sys_get_temp_dir(),'bulk-pdf-');file_put_contents($pdfPath,$bulkPdf);
$bulkText=shell_exec('pdftotext -layout '.escapeshellarg($pdfPath).' -') ?: '';
$bulkInfo=shell_exec('pdfinfo '.escapeshellarg($pdfPath)) ?: '';
expCheck(str_contains($bulkText,'report #5020') && str_contains($bulkText,'report #1') && !str_contains($bulkText,'Only the first 5,000'), 'PDF includes both sides of 5,000-row boundary without truncating');
expCheck(preg_match('/Pages:\s+(\d+)/',$bulkInfo,$m)===1 && (int)$m[1]>10 && substr_count($bulkText,'Page ')>10,'long PDF opens, paginates, and retains page numbers');
unlink($pdfPath);
// Do not allow an unauthenticated or unauthorized user to generate any report PDF.
$auth->logout();
foreach (array_keys(ReportService::catalog()) as $slug) {
    try { $resp=exportGet($router,$app,'/admin/reports/'.$slug.'/pdf',$filter); $status=$resp->status(); }
    catch (\App\Exceptions\HttpException $e) { $status=$e->statusCode(); }
    expCheck(in_array($status,[302,401],true),$slug.' PDF rejects unauthenticated requester');
}
$session->start();$auth->login($db->selectOne('SELECT * FROM users WHERE id=2'),'127.0.0.1');
foreach (array_keys(ReportService::catalog()) as $slug) {
    try { exportGet($router,$app,'/admin/reports/'.$slug.'/pdf',$filter); $status=200; }
    catch (\App\Exceptions\HttpException $e) { $status=$e->statusCode(); }
    expCheck($status===403,$slug.' PDF rejects logged-in user without reports.view');
}
foreach (['pdf','csv','xlsx'] as $format) {
    try { exportGet($router,$app,'/admin/reports/financial/'.$format,$filter); $status=200; }
    catch (\App\Exceptions\HttpException $e) { $status=$e->statusCode(); }
    expCheck($status===403,'user without reports.view cannot export '.$format);
}
foreach ($pdfResults as $slug=>$result) echo "PDF {$slug}: {$result}\n";
echo "{$ok} export assertions passed.\n";
