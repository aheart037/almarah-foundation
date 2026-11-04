<?php

declare(strict_types=1);

namespace App\Services;

/**
 * CSV and Excel (.xlsx) exports of an administrative report (v3.1).
 *
 * Takes the same array that ReportService::run() produces (and that the PDF
 * exporter renders), so a CSV/XLSX always shows exactly what the screen and the
 * PDF show: same filters, same rows, same formatting. No third-party library
 * and no PHP zip extension is required.
 */
final class ReportExportService
{
    /** @return list<list<string>> flat table of rows shared by both formats */
    public function sheet(array $report, array $filters, string $defaultCurrency, string $org): array
    {
        $out = [];
        $out[] = [$org];
        $out[] = [(string) ($report['title'] ?? 'Report')];
        $out[] = ['Generated (UTC)', gmdate('j M Y H:i')];
        foreach ($filters as $pair) {
            if (is_array($pair) && count($pair) >= 2) {
                $out[] = [(string) $pair[0], (string) $pair[1]];
            }
        }
        if (!empty($report['basis'])) {
            $out[] = ['Basis', (string) $report['basis']];
        }
        $out[] = [];

        $summary = (array) ($report['summary'] ?? []);
        if ($summary !== []) {
            foreach ($summary as $item) {
                $out[] = [(string) ($item['label'] ?? ''), $this->summaryValue((array) $item, $defaultCurrency)];
            }
            $out[] = [];
        }

        $this->table($out, (string) ($report['rowsTitle'] ?? ''), (array) ($report['columns'] ?? []), (array) ($report['rows'] ?? []), $defaultCurrency, 'No records match the selected filters.');
        if (!empty($report['truncated'])) {
            $out[] = ['Only the first ' . number_format(ReportService::EXPORT_LIMIT) . ' of ' . number_format((int) ($report['total'] ?? 0)) . ' rows are included. Narrow the filters to export the rest.'];
        }

        foreach ((array) ($report['extra'] ?? []) as $extra) {
            $out[] = [];
            $this->table($out, (string) ($extra['title'] ?? ''), (array) ($extra['columns'] ?? []), (array) ($extra['rows'] ?? []), $defaultCurrency, 'Nothing to show for these filters.');
        }

        $notes = (array) ($report['notes'] ?? []);
        if ($notes !== []) {
            $out[] = [];
            $out[] = ['Notes and limitations'];
            foreach ($notes as $n) {
                $out[] = [(string) $n];
            }
        }

        return $out;
    }

    /** @param list<list<string>> $out */
    private function table(array &$out, string $title, array $columns, array $rows, string $cur, string $empty): void
    {
        if ($title !== '') {
            $out[] = [$title];
        }
        if ($rows === []) {
            $out[] = [$empty];
            return;
        }
        $out[] = array_map(static fn ($c): string => (string) ($c['label'] ?? ($c['key'] ?? '')), array_values($columns));
        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $col) {
                $line[] = ReportService::cell((array) $col, (array) $row, $cur);
            }
            $out[] = $line;
        }
    }

    private function summaryValue(array $item, string $cur): string
    {
        $v = $item['value'] ?? null;
        if ($v === null || $v === '') {
            return '—';
        }

        return match ((string) ($item['kind'] ?? 'text')) {
            'money'   => money((int) $v, (string) ($item['currency'] ?? $cur)),
            'int'     => number_format((float) $v),
            'percent' => number_format((float) $v, 1) . '%',
            'hours'   => (float) $v >= 48 ? number_format((float) $v / 24, 1) . ' days' : number_format((float) $v, 1) . ' h',
            default   => (string) $v,
        };
    }

    /**
     * Cells that start with = + - @ (or a control char) can run as formulas when
     * the file is opened in Excel. Donor names and notes are user-supplied, so
     * neutralise them with a leading apostrophe-style space-free prefix.
     */
    private static function safe(string $v): string
    {
        $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $v) ?? '';
        if ($v !== '' && strpos("=+-@\t\r", $v[0]) !== false && !preg_match('/^-?\d+(\.\d+)?$/', $v)) {
            return "'" . $v;
        }

        return $v;
    }

    public function csv(array $sheet): string
    {
        $fh = fopen('php://temp', 'r+');
        fwrite($fh, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads Urdu/Arabic names correctly
        foreach ($sheet as $row) {
            fputcsv($fh, array_map(static fn ($c): string => self::safe((string) $c), $row), ',', '"', '\\');
        }
        rewind($fh);
        $csv = (string) stream_get_contents($fh);
        fclose($fh);

        return $csv;
    }

    public function xlsx(array $sheet, string $sheetName = 'Report'): string
    {
        $rowsXml = '';
        $maxCols = 1;
        foreach ($sheet as $r => $row) {
            $cells = '';
            foreach (array_values($row) as $c => $val) {
                $ref = self::col($c) . ($r + 1);
                $style = $r === 1 ? ' s="1"' : '';
                $cells .= '<c r="' . $ref . '" t="inlineStr"' . $style . '><is><t xml:space="preserve">'
                    . htmlspecialchars(self::safe((string) $val), ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8') . '</t></is></c>';
            }
            $maxCols = max($maxCols, count($row));
            $rowsXml .= '<row r="' . ($r + 1) . '">' . $cells . '</row>';
        }
        $cols = '<cols><col min="1" max="1" width="30" customWidth="1"/>' . ($maxCols > 1 ? '<col min="2" max="' . $maxCols . '" width="22" customWidth="1"/>' : '') . '</cols>';
        $name = htmlspecialchars(mb_substr(preg_replace('/[\\\\\/?*\[\]:]/', ' ', $sheetName) ?: 'Report', 0, 31), ENT_XML1, 'UTF-8');

        return $this->zip([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . $name . '" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="14"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . $cols . '<sheetData>' . $rowsXml . '</sheetData></worksheet>',
        ]);
    }

    private static function col(int $i): string
    {
        $s = '';
        for ($n = $i; $n >= 0; $n = intdiv($n, 26) - 1) {
            $s = chr(65 + $n % 26) . $s;
        }

        return $s;
    }

    /** Minimal ZIP writer (stored, no compression) so no zip extension is needed. @param array<string,string> $files */
    private function zip(array $files): string
    {
        $data = '';
        $central = '';
        $count = 0;
        $dt = (int) ((1 << 21) | (1 << 16)); // 1980-01-01 00:00 (valid DOS date, keeps output deterministic)
        foreach ($files as $name => $content) {
            $crc = crc32($content);
            $size = strlen($content);
            $offset = strlen($data);
            $data .= pack('VvvvVVVVvv', 0x04034b50, 20, 0x0800, 0, $dt, $crc, $size, $size, strlen($name), 0) . $name . $content;
            $central .= pack('VvvvvVVVVvvvvvVV', 0x02014b50, 20, 20, 0x0800, 0, $dt, $crc, $size, $size, strlen($name), 0, 0, 0, 0, 0, $offset) . $name;
            $count++;
        }

        return $data . $central . pack('VvvvvVVv', 0x06054b50, 0, 0, $count, $count, strlen($central), strlen($data), 0);
    }
}
