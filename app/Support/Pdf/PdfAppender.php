<?php

declare(strict_types=1);

namespace App\Support\Pdf;

use RuntimeException;

/**
 * Appends every page of one PDF to the end of another, keeping the appended pages byte-for-byte
 * (vector text, images, fonts) instead of re-drawing them.
 *
 * Deliberately small and strict: it only reads classic cross-reference tables (what FPDF and
 * `qpdf --object-streams=disable` write) and a flat page tree. Anything else throws, so a bad input
 * can never silently produce a broken PDF. No third-party library is needed.
 */
final class PdfAppender
{
    /** @return string the merged PDF: every page of $base followed by every page of $extra */
    public static function append(string $base, string $extra): string
    {
        $b = self::parse($base);
        $e = self::parse($extra);

        // Base page tree root (FPDF: a single /Pages object that holds all pages).
        if (!preg_match('~/Pages\s+(\d+)\s+0\s+R~', $b['objects'][$b['root']] ?? '', $m)) {
            throw new RuntimeException('PDF merge: base document has no page tree.');
        }
        $basePages = (int) $m[1];

        // Extra pages, in order. Only a flat tree (Catalog -> Pages -> Page...) is supported.
        if (!preg_match('~/Pages\s+(\d+)\s+0\s+R~', $e['objects'][$e['root']] ?? '', $m)) {
            throw new RuntimeException('PDF merge: appended document has no page tree.');
        }
        $extraPages = (int) $m[1];
        if (!preg_match('~/Kids\s*\[([^\]]*)\]~', $e['objects'][$extraPages] ?? '', $m)
            || preg_match_all('~(\d+)\s+0\s+R~', $m[1], $kids) < 1) {
            throw new RuntimeException('PDF merge: appended document has no pages.');
        }
        $kidNumbers = array_map('intval', $kids[1]);
        foreach ($kidNumbers as $n) {
            if (!preg_match('~/Type\s*/Page\b~', $e['objects'][$n] ?? '') || str_contains(self::dictOf($e['objects'][$n]), '/Kids')) {
                throw new RuntimeException('PDF merge: nested page trees are not supported.');
            }
        }

        // Renumber the extra objects after the base ones (its catalog and page tree are dropped).
        $offset = $b['max'];
        $map = [];
        foreach (array_keys($e['objects']) as $n) {
            if ($n !== $e['root'] && $n !== $extraPages) {
                $map[$n] = ++$offset;
            }
        }
        $map[$extraPages] = $basePages; // /Parent of every appended page

        $out = $b['objects'];
        foreach ($map as $old => $new) {
            if ($old === $extraPages) {
                continue;
            }
            $out[$new] = self::renumber($e['objects'][$old], $map);
        }

        // Extend the base /Kids and /Count.
        $newKids = implode(' ', array_map(static fn (int $n): string => $map[$n] . ' 0 R', $kidNumbers));
        $pages = $out[$basePages];
        $pages = preg_replace_callback('~/Kids\s*\[([^\]]*)\]~', static fn (array $x): string => '/Kids [' . rtrim($x[1]) . ' ' . $newKids . ' ]', $pages, 1, $c1);
        $pages = preg_replace_callback('~/Count\s+(\d+)~', static fn (array $x): string => '/Count ' . ((int) $x[1] + count($kidNumbers)), (string) $pages, 1, $c2);
        if ($c1 !== 1 || $c2 !== 1) {
            throw new RuntimeException('PDF merge: unexpected base page tree.');
        }
        $out[$basePages] = $pages;

        return self::write($out, $b['root'], $b['info']);
    }

    // ------------------------------------------------------------------------------------------------

    /**
     * @return array{objects:array<int,string>,root:int,info:?int,max:int} objects hold the text between "obj" and "endobj"
     */
    private static function parse(string $pdf): array
    {
        if (!str_starts_with($pdf, '%PDF-')) {
            throw new RuntimeException('PDF merge: not a PDF.');
        }
        if (!preg_match('~startxref\s+(\d+)\s+%%EOF\s*$~', $pdf, $m)) {
            throw new RuntimeException('PDF merge: missing startxref.');
        }
        $xrefPos = (int) $m[1];
        if (substr($pdf, $xrefPos, 4) !== 'xref') {
            throw new RuntimeException('PDF merge: only classic cross-reference tables are supported.');
        }
        $tablePart = substr($pdf, $xrefPos);
        $trailerPos = strpos($tablePart, 'trailer');
        if ($trailerPos === false) {
            throw new RuntimeException('PDF merge: missing trailer.');
        }
        $table = substr($tablePart, 4, $trailerPos - 4);
        $trailer = substr($tablePart, $trailerPos);
        if (str_contains($trailer, '/Encrypt') || str_contains($trailer, '/Prev')) {
            throw new RuntimeException('PDF merge: encrypted or incremental PDFs are not supported.');
        }

        // Xref subsections: "<first> <count>" then 20-byte entries "oooooooooo ggggg n".
        $offsets = [];
        if (!preg_match_all('~(?:^|\s)(\d+)\s+(\d+)\s*\n((?:\d{10} \d{5} [nf]\s*\n?)+)~', $table . "\n", $subs, PREG_SET_ORDER)) {
            throw new RuntimeException('PDF merge: unreadable cross-reference table.');
        }
        foreach ($subs as $sub) {
            $num = (int) $sub[1];
            foreach (preg_split('~\n~', trim($sub[3])) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                [$off, $gen, $flag] = preg_split('~\s+~', $line);
                if ($flag === 'n') {
                    if ((int) $gen !== 0) {
                        throw new RuntimeException('PDF merge: non-zero object generations are not supported.');
                    }
                    $offsets[$num] = (int) $off;
                }
                $num++;
            }
        }
        asort($offsets);

        $objects = [];
        $nums = array_keys($offsets);
        $pos = array_values($offsets);
        foreach ($nums as $i => $n) {
            $end = $pos[$i + 1] ?? $xrefPos;
            $raw = substr($pdf, $pos[$i], $end - $pos[$i]);
            if (!preg_match('~^' . $n . ' 0 obj~', $raw)) {
                throw new RuntimeException('PDF merge: object ' . $n . ' is not at its recorded offset.');
            }
            $close = strrpos($raw, 'endobj');
            if ($close === false) {
                throw new RuntimeException('PDF merge: object ' . $n . ' is not terminated.');
            }
            $objects[$n] = substr($raw, strlen($n . ' 0 obj'), $close - strlen($n . ' 0 obj'));
        }
        if (!preg_match('~/Root\s+(\d+)\s+0\s+R~', $trailer, $r) || !isset($objects[(int) $r[1]])) {
            throw new RuntimeException('PDF merge: missing document catalog.');
        }
        $info = preg_match('~/Info\s+(\d+)\s+0\s+R~', $trailer, $i) && isset($objects[(int) $i[1]]) ? (int) $i[1] : null;

        return ['objects' => $objects, 'root' => (int) $r[1], 'info' => $info, 'max' => max($nums)];
    }

    /** The dictionary text of an object, i.e. everything before its stream data (which is binary). */
    private static function dictOf(string $body): string
    {
        return preg_split('~(?<=>>)\s*stream\r?\n~', $body, 2)[0];
    }

    /** Rewrite "N 0 R" references in the dictionary part only; stream bytes are never touched. */
    private static function renumber(string $body, array $map): string
    {
        $parts = preg_split('~(?<=>>)(\s*stream\r?\n)~', $body, 2, PREG_SPLIT_DELIM_CAPTURE);
        $dict = preg_replace_callback(
            '~\b(\d+)\s+0\s+R\b~',
            static function (array $x) use ($map): string {
                $n = (int) $x[1];
                if (!isset($map[$n])) {
                    throw new RuntimeException('PDF merge: reference to missing object ' . $n . '.');
                }
                return $map[$n] . ' 0 R';
            },
            $parts[0]
        );
        return $dict . ($parts[1] ?? '') . ($parts[2] ?? '');
    }

    /** @param array<int,string> $objects */
    private static function write(array $objects, int $root, ?int $info): string
    {
        ksort($objects);
        $max = (int) array_key_last($objects);
        $out = "%PDF-1.7\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        foreach ($objects as $n => $body) {
            $offsets[$n] = strlen($out);
            $out .= $n . ' 0 obj' . $body . "endobj\n";
        }
        $xref = strlen($out);
        $out .= "xref\n0 " . ($max + 1) . "\n0000000000 65535 f \n";
        for ($n = 1; $n <= $max; $n++) {
            $out .= isset($offsets[$n]) ? sprintf("%010d 00000 n \n", $offsets[$n]) : "0000000000 65535 f \n";
        }
        $out .= "trailer\n<< /Size " . ($max + 1) . ' /Root ' . $root . ' 0 R' . ($info !== null ? ' /Info ' . $info . ' 0 R' : '') . " >>\nstartxref\n" . $xref . "\n%%EOF\n";
        return $out;
    }
}
