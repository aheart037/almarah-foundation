<?php
/**
 * Small, dependency-free helpers for the v3.1 admin dashboard.
 * Every function is prefixed dv31_ so nothing can clash with charts.php or helpers.php.
 */

if (!function_exists('dv31_int')) {
    /** Safe integer from any value (null, "", "12", 12.7). */
    function dv31_int($v): int
    {
        return is_numeric($v) ? (int) $v : 0;
    }
}

if (!function_exists('dv31_series')) {
    /** @param array<int,mixed> $rows @return array<int,int> */
    function dv31_series(array $rows, string $key): array
    {
        $out = [];
        foreach ($rows as $r) {
            $out[] = is_array($r) ? dv31_int($r[$key] ?? ($key === 'count' ? ($r['cnt'] ?? 0) : 0)) : 0;
        }

        return $out;
    }
}

if (!function_exists('dv31_delta')) {
    /** @return array{dir:string,text:string} */
    function dv31_delta(int $cur, int $prev): array
    {
        if ($prev <= 0 && $cur <= 0) {
            return ['dir' => 'flat', 'text' => 'No change'];
        }
        if ($prev <= 0) {
            return ['dir' => 'up', 'text' => 'New activity'];
        }
        $pct = ($cur - $prev) / $prev * 100;
        if (abs($pct) < 0.5) {
            return ['dir' => 'flat', 'text' => 'Flat vs previous 30 days'];
        }

        return ['dir' => $pct > 0 ? 'up' : 'down', 'text' => sprintf('%s%.0f%% vs previous 30 days', $pct > 0 ? '+' : '', $pct)];
    }
}

if (!function_exists('dv31_spark')) {
    /** Inline SVG sparkline (area + line). @param array<int,int> $values */
    function dv31_spark(array $values): string
    {
        $values = array_values($values);
        $n = count($values);
        if ($n < 2) {
            return '';
        }
        $max = max($values);
        $min = min($values);
        $span = max(1, $max - $min);
        $pts = [];
        foreach ($values as $i => $v) {
            $x = round($i / ($n - 1) * 100, 2);
            $y = $max === $min ? 16 : round(28 - (($v - $min) / $span) * 24, 2);
            $pts[] = [$x, $y];
        }
        $line = 'M' . implode(' L', array_map(static fn (array $p): string => $p[0] . ' ' . $p[1], $pts));
        $area = $line . ' L100 32 L0 32 Z';

        return '<svg class="dv-spark" viewBox="0 0 100 32" preserveAspectRatio="none" aria-hidden="true" focusable="false">'
            . '<path class="dv-spark-area" d="' . $area . '"/><path class="dv-spark-line" pathLength="1" d="' . $line . '"/></svg>';
    }
}

if (!function_exists('dv31_initials')) {
    function dv31_initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $out = '';
        foreach (array_slice(array_filter($parts), 0, 2) as $p) {
            $out .= mb_strtoupper(mb_substr($p, 0, 1));
        }

        return $out !== '' ? $out : '•';
    }
}

if (!function_exists('dv31_icon')) {
    function dv31_icon(string $name): string
    {
        $p = [
            'gift'     => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M5 12v8h14v-8"/><path d="M12 8c-2-4-6-4-6-1.5S10 8 12 8zm0 0c2-4 6-4 6-1.5S14 8 12 8z"/>',
            'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
            'shield'   => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="m9 12 2 2 4-4"/>',
            'hourglass'=> '<path d="M6 3h12M6 21h12M7 3c0 5 5 6 5 9s-5 4-5 9M17 3c0 5-5 6-5 9s5 4 5 9"/>',
            'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><path d="M16 5a3.5 3.5 0 0 1 0 6.5M18 14.3c2.2.7 3.5 2.6 3.5 5.7"/>',
            'alert'    => '<path d="M12 3 2 20h20L12 3z"/><path d="M12 10v4M12 17.5v.5"/>',
            'flag'     => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
            'hands'    => '<path d="M12 21s-7-4.6-9.3-9.1C1.2 8.9 2.8 5 6.4 5c2 0 3.4 1.1 4.2 2.4h2.8C14.2 6.1 15.6 5 17.6 5c3.6 0 5.2 3.9 3.7 6.9C19 16.4 12 21 12 21z"/>',
            'inbox'    => '<path d="M3 13l3-8h12l3 8v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-6z"/><path d="M3 13h5l1 3h6l1-3h5"/>',
            'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
            'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/>',
            'pulse'    => '<path d="M3 12h4l2-6 4 12 2-6h6"/>',
            'plus'     => '<path d="M12 5v14M5 12h14"/>',
            'refresh'  => '<path d="M20 11a8 8 0 0 0-14.5-4M4 4v4h4"/><path d="M4 13a8 8 0 0 0 14.5 4M20 20v-4h-4"/>',
            'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        ];

        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($p[$name] ?? '') . '</svg>';
    }
}
