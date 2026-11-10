<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use App\Core\Database;
use ZipArchive;

/** Private, bounded-memory CSV and OOXML exports from the existing report queries. */
final class ReportSpreadsheetService
{
    public function __construct(private ReportService $reports, private Database $db)
    {
    }

    /** @param array<string,mixed> $filters */
    public function csv(string $slug, array $filters): string
    {
        return $this->db->transaction(fn (): string => $this->buildCsv($slug,$filters));
    }

    private function buildCsv(string $slug, array $filters): string
    {
        $path = $this->temporaryPath();
        $handle = fopen($path, 'wb');
        if ($handle === false) throw new RuntimeException('Could not create report export.');
        try {
            $first = $this->reports->run($slug, $filters, 1, true);
            fwrite($handle, "\xEF\xBB\xBF");
            $write = static function (array $values, bool $header = false) use ($handle): void {
                fputcsv($handle, array_map(static function (mixed $v): string {
                    $s = (string) $v;
                    // Excel interprets untrusted donor names/notes as formulas even in CSV.
                    return preg_match('/^[\x00-\x20]*[=+@\-]/u', $s) ? "'" . $s : $s;
                }, $values));
            };
            $this->writeMeta($first, $write);
            $write([]);
            $columns = (array) $first['columns'];
            $write(array_column($columns, 'label'));
            $this->pages($slug, $filters, $first, static function (array $row) use ($write, $columns, $first): void {
                $cells = [];
                foreach ($columns as $col) $cells[] = ReportService::cell($col, $row, (string) $first['currency']);
                $write($cells);
            });
            $this->supplemental($first,$write,false);
            fclose($handle);
            return $path;
        } catch (\Throwable $e) {
            fclose($handle);
            @unlink($path);
            throw $e;
        }
    }

    /** @param array<string,mixed> $filters */
    public function xlsx(string $slug, array $filters): string
    {
        return $this->db->transaction(fn (): string => $this->buildXlsx($slug,$filters));
    }

    private function buildXlsx(string $slug, array $filters): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Excel export requires the PHP zip extension.');
        }
        $path = $this->temporaryPath();
        $sheet = $this->temporaryPath();
        $h = fopen($sheet, 'wb');
        if ($h === false) throw new RuntimeException('Could not create Excel worksheet.');
        try {
            $first = $this->reports->run($slug, $filters, 1, true);
            $cur = strtoupper((string)$first['currency']);
            $currency = preg_replace('/[^A-Z]/', '', $cur) ?: 'PKR';
            $metaRows = 3 + count($this->reports->describeFilters($first)) + count((array)$first['summary']);
            $extraRows = 0;
            $maxColumns = count((array)$first['columns']);
            foreach ((array)($first['extra'] ?? []) as $extra) {
                $extraRows += 3 + count((array)($extra['rows'] ?? []));
                $maxColumns = max($maxColumns, count((array)$extra['columns']));
            }
            $lastRow = $metaRows + 2 + (int)$first['total'] + $extraRows;
            $lastColumn = self::columnLetter(max(2,$maxColumns));
            fwrite($h, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="A1:' . $lastColumn . $lastRow . '"/><sheetData>');
            $line = 0;
            $write = function (array $values, bool $header = false) use ($h, &$line): void {
                $line++;
                if ($line > 1048576) throw new RuntimeException('Excel worksheet row limit exceeded; narrow the filters or use CSV.');
                fwrite($h, '<row r="' . $line . '">');
                foreach ($values as $i => $value) {
                    $col = self::columnLetter($i + 1);
                    if (is_array($value) && isset($value['money_minor'])) {
                        $minor = (int)$value['money_minor'];
                        $amount = ($minor < 0 ? '-' : '') . intdiv(abs($minor),100) . '.' . str_pad((string)(abs($minor)%100),2,'0',STR_PAD_LEFT);
                        fwrite($h, '<c r="' . $col . $line . '" s="2"><v>' . $amount . '</v></c>');
                    } elseif (is_int($value)) {
                        fwrite($h, '<c r="' . $col . $line . '"><v>' . $value . '</v></c>');
                    } else {
                        fwrite($h, '<c r="' . $col . $line . '" t="inlineStr"' . ($header ? ' s="1"' : '') . '><is><t xml:space="preserve">' . self::xml((string)$value) . '</t></is></c>');
                    }
                }
                fwrite($h, '</row>');
            };
            $this->writeMeta($first, $write);
            $write([]);
            $columns = (array)$first['columns'];
            $write(array_column($columns, 'label'), true);
            $this->pages($slug, $filters, $first, static function (array $row) use ($write, $columns, $first): void {
                $cells=[];
                foreach ($columns as $col) {
                    $key=(string)$col['key'];
                    $v=$row[$key] ?? null;
                    if (($col['type'] ?? '') === 'money' && $v !== null && $v !== '') $cells[]=['money_minor'=>(int)$v];
                    elseif (($col['type'] ?? '') === 'int' && $v !== null && $v !== '') $cells[]=(int)$v;
                    else $cells[]=ReportService::cell($col,$row,(string)$first['currency']);
                }
                $write($cells);
            });
            $this->supplemental($first,$write,true);
            fwrite($h, '</sheetData></worksheet>');
            fclose($h);
            $zip = new ZipArchive();
            if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Could not create Excel workbook.');
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Report" sheetId="1" r:id="rId1"/></sheets></workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
            $format = '&quot;' . self::xml($currency . ' ') . '&quot;#,##0.00;[Red]-&quot;' . self::xml($currency . ' ') . '&quot;#,##0.00';
            $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><numFmts count="1"><numFmt numFmtId="164" formatCode="' . $format . '"/></numFmts><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/><xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>');
            if (!$zip->addFile($sheet,'xl/worksheets/sheet1.xml') || !$zip->close()) throw new RuntimeException('Could not finish Excel workbook.');
            @unlink($sheet);
            return $path;
        } catch (\Throwable $e) {
            if (is_resource($h)) fclose($h);
            @unlink($sheet);
            @unlink($path);
            throw $e;
        }
    }

    /** Follow the main ledger with labeled, deliberately limited summary tables. */
    private function supplemental(array $report, callable $write, bool $numeric): void
    {
        foreach ((array)($report['extra'] ?? []) as $extra) {
            $write([]);
            $write([(string)$extra['title']]);
            $columns=(array)$extra['columns'];
            $write(array_column($columns,'label'),true);
            foreach ((array)($extra['rows'] ?? []) as $row) {
                $cells=[];
                foreach ($columns as $col) {
                    $v=$row[$col['key']] ?? null;
                    if ($numeric && ($col['type'] ?? '')==='money' && $v!==null && $v!=='') $cells[]=['money_minor'=>(int)$v];
                    elseif ($numeric && ($col['type'] ?? '')==='int' && $v!==null && $v!=='') $cells[]=(int)$v;
                    else $cells[]=ReportService::cell($col,$row,(string)$report['currency']);
                }
                $write($cells);
            }
        }
    }

    private function temporaryPath(): string
    {
        $path=tempnam(sys_get_temp_dir(),'almarah-report-');
        if ($path === false) throw new RuntimeException('No private temporary storage available for export.');
        chmod($path,0600);
        return $path;
    }

    /** @param array<string,mixed> $report */
    private function writeMeta(array $report, callable $write): void
    {
        $write([$report['title']]);
        $write(['Generated UTC',(string)$report['generatedAt']]);
        $write(['Records',(int)$report['total']]);
        foreach ($this->reports->describeFilters($report) as [$name,$value]) $write([$name,$value]);
        foreach ((array)$report['summary'] as $card) {
            $v=$card['value'] ?? null;
            $value=match((string)($card['kind'] ?? 'text')) {
                'money' => money((int)$v,(string)($card['currency'] ?? $report['currency'])),
                'percent' => $v === null ? '—' : number_format((float)$v,1).'%',
                default => $v === null ? '—' : (string)$v,
            };
            $write([(string)$card['label'],$value]);
        }
    }

    /** @param array<string,mixed> $first */
    private function pages(string $slug,array $filters,array $first,callable $consume): void
    {
        for ($page=1;$page <= (int)$first['lastPage'];$page++) {
            $report=$page === 1 ? $first : $this->reports->run($slug,$filters,$page,true);
            foreach ((array)$report['rows'] as $row) $consume($row);
        }
    }

    private static function xml(string $value): string
    {
        $clean=preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u','',$value);
        return htmlspecialchars($clean ?? '',ENT_XML1 | ENT_QUOTES,'UTF-8');
    }

    private static function columnLetter(int $n): string
    {
        $s='';
        while ($n>0) { $n--; $s=chr(65+$n%26).$s; $n=intdiv($n,26); }
        return $s;
    }
}
