<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;

/**
 * Admin reports (v20).
 *
 * Every report is built from live database queries and returns one normalised
 * structure that the HTML page and the PDF exporter both render, so the two
 * can never disagree:
 *
 *   slug, title, description, basis, filters, filterDefs, currency,
 *   summary[], columns[], rows[], total, page, perPage, lastPage, sort, dir,
 *   extra[] (more tables), notes[], chart (overview only)
 *
 * Accounting rules used everywhere (so nothing is counted twice):
 *  - "Standing verified" money requires status=completed and verification_status=verified. A donation row is
 *    one gift, so it is counted once however many payment attempts it had.
 *  - Refunded, failed, cancelled, abandoned, pending and processing donations
 *    are never part of confirmed totals; they are shown on their own lines.
 *  - Amounts are only ever added within ONE currency. Every money report has a
 *    currency filter (default: the site currency); nothing is converted.
 *  - Donation reports are dated by donation date (donations.created_at).
 *    The Financial Summary is dated by cash date (completed_at and refund
 *    completed_at). Each report states its date basis on screen and in the PDF.
 *  - All times are UTC, the way they are stored.
 */
final class ReportService
{
    public const PER_PAGE = 25;
    public const EXPORT_LIMIT = 5000;

    /** Donations stuck unresolved for longer than this are "stale". */
    private const STALE_MINUTES = 30;

    /** A donation still waiting on staff verification. Failed, cancelled, abandoned and refunded payments are never "awaiting". */
    private const AWAITING_SQL = "d.verification_status NOT IN ('verified','rejected','legacy') AND d.status IN ('pending','processing','completed')";

    public function __construct(private Database $db)
    {
    }

    // ------------------------------------------------------------------ catalog

    /** @return array<string,array{title:string,short:string,icon:string}> */
    public static function catalog(): array
    {
        return [
            'overview'       => ['title' => 'Reports Overview Dashboard', 'short' => 'Key donation numbers, trends and payment health.', 'icon' => 'grid'],
            'donations'      => ['title' => 'Donation Summary Report', 'short' => 'Confirmed giving by day, month or year, with refunds.', 'icon' => 'heart'],
            'payments'       => ['title' => 'Payment Transactions Report', 'short' => 'Every gateway attempt and what the bank answered.', 'icon' => 'card'],
            'campaigns'      => ['title' => 'Campaign Performance Report', 'short' => 'Raised, donors and goals for each campaign.', 'icon' => 'flag'],
            'purposes'       => ['title' => 'Donation Purpose Report', 'short' => 'Where gifts went: raised, donors and zakat-eligible giving by purpose.', 'icon' => 'trend'],
            'referrers'      => ['title' => 'Referrer Report', 'short' => 'Who referred donors, and what those gifts total.', 'icon' => 'users'],
            'donors'         => ['title' => 'Donor Report', 'short' => 'Who gave, how much, and new versus returning donors.', 'icon' => 'user'],
            'verification'   => ['title' => 'Donation Verification Report', 'short' => 'Verification status, decisions, reviewers and turnaround time.', 'icon' => 'check'],
            'reconciliation' => ['title' => 'Bank Transfer & Reconciliation Report', 'short' => 'Donations and bank records that do not agree.', 'icon' => 'scale'],
            'refunds'        => ['title' => 'Refund & Failed Payment Report', 'short' => 'Refunds, failures, cancellations and the reasons.', 'icon' => 'undo'],
            'financial'      => ['title' => 'Financial Summary Report', 'short' => 'Gross received, refunds and net by period.', 'icon' => 'bank'],
            'receipts'       => ['title' => 'Donation Receipt Report', 'short' => 'Receipt emails sent, queued and failed.', 'icon' => 'mail'],
            'audit'          => ['title' => 'Admin Activity & Audit Log', 'short' => 'Who did what, and when, in the admin area.', 'icon' => 'shield'],
        ];
    }

    public static function exists(string $slug): bool
    {
        return isset(self::catalog()[$slug]);
    }

    // ------------------------------------------------------------------ entry point

    /**
     * @param array<string,mixed> $input raw request values
     * @return array<string,mixed>
     */
    public function run(string $slug, array $input, int $page = 1, bool $export = false): array
    {
        if (!self::exists($slug)) {
            throw new \InvalidArgumentException('Unknown report.');
        }

        $filters = $this->normalize($slug, $input);
        $perPage = $export ? self::EXPORT_LIMIT : self::PER_PAGE;
        $page = max(1, $page);

        $result = match ($slug) {
            'overview'       => $this->overview($filters, $page, $perPage),
            'donations'      => $this->donationSummary($filters, $page, $perPage),
            'payments'       => $this->payments($filters, $page, $perPage),
            'campaigns'      => $this->campaigns($filters, $page, $perPage),
            'purposes'       => $this->purposes($filters, $page, $perPage),
            'referrers'      => $this->referrers($filters, $page, $perPage),
            'donors'         => $this->donors($filters, $page, $perPage),
            'verification'   => $this->verification($filters, $page, $perPage),
            'reconciliation' => $this->reconciliation($filters, $page, $perPage),
            'refunds'        => $this->refunds($filters, $page, $perPage),
            'financial'      => $this->financial($filters, $page, $perPage),
            'receipts'       => $this->receipts($filters, $page, $perPage),
            'audit'          => $this->audit($filters, $page, $perPage),
        };

        $total = (int) ($result['total'] ?? count($result['rows'] ?? []));
        $result += [
            'slug'        => $slug,
            'title'       => self::catalog()[$slug]['title'],
            'filters'     => $filters,
            'filterDefs'  => $this->filterDefs($slug),
            'currency'    => $filters['currency'] ?? $this->defaultCurrency(),
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'lastPage'    => max(1, (int) ceil($total / max(1, $perPage))),
            'summary'     => [],
            'extra'       => [],
            'notes'       => [],
            'truncated'   => $export && $total > $perPage,
            'generatedAt' => gmdate('Y-m-d H:i:s'),
        ];

        return $result;
    }

    // ------------------------------------------------------------------ filters

    public function defaultCurrency(): string
    {
        return strtoupper((string) Config::get('app.currency', 'PKR')) ?: 'PKR';
    }

    /**
     * Cleans every filter. Unknown or invalid values fall back to "no filter",
     * so a crafted query string can never reach the SQL.
     *
     * @param array<string,mixed> $in
     * @return array<string,mixed>
     */
    public function normalize(string $slug, array $in): array
    {
        $str = static fn (string $k, int $max = 100): string => mb_substr(trim(is_scalar($in[$k] ?? null) ? (string) $in[$k] : ''), 0, $max);
        $date = static function (string $v): string {
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m) === 1 && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
                return $v;
            }

            return '';
        };

        $f = ['date_from' => $date($str('date_from', 10)), 'date_to' => $date($str('date_to', 10))];

        // Overview opens on the last 30 days unless the person chose a range.
        if ($slug === 'overview' && !array_key_exists('date_from', $in) && !array_key_exists('date_to', $in)) {
            $f['date_from'] = gmdate('Y-m-d', strtotime('-29 days'));
            $f['date_to'] = gmdate('Y-m-d');
        }
        if ($f['date_from'] !== '' && $f['date_to'] !== '' && $f['date_from'] > $f['date_to']) {
            [$f['date_from'], $f['date_to']] = [$f['date_to'], $f['date_from']];
        }

        $f['q'] = $str('q');
        $currencies = $this->currencies();
        $cur = strtoupper($str('currency', 3));
        $f['currency'] = in_array($cur, $currencies, true) ? $cur : $this->defaultCurrency();

        $one = static fn (string $v, array $allowed): string => in_array($v, $allowed, true) ? $v : '';
        $id = static fn (string $v): string => ctype_digit($v) && $v !== '0' ? $v : '';

        $f['gateway'] = $one($str('gateway', 40), array_column($this->gateways(), 'code'));
        $f['campaign_id'] = $id($str('campaign_id', 10));
        $f['purpose_id'] = $id($str('purpose_id', 10));
        $f['status'] = $str('status', 40);
        $f['group'] = $one($str('group', 8), ['day', 'month', 'year']) ?: 'month';
        $f['environment'] = $one($str('environment', 10), ['sandbox', 'live']);
        $f['type'] = $str('type', 20);
        $f['issue'] = $str('issue', 40);
        $f['receipt_status'] = $one($str('receipt_status', 12), ['not_sent', 'queued', 'sent', 'failed']);
        $f['review_status'] = $one($str('review_status', 12), ['verified', 'pending', 'rejected', 'legacy']);
        $f['donor_type'] = $one($str('donor_type', 10), ['new', 'returning']);
        $f['min_gifts'] = $one($str('min_gifts', 2), ['2', '3', '5']);
        $f['entity'] = $str('entity', 60);
        $f['area'] = $str('area', 40);
        $f['who'] = $one($str('who', 8), ['admins', 'staff', 'all']) ?: 'staff';

        $f['sort'] = $str('sort', 30);
        $f['dir'] = strtolower($str('dir', 4)) === 'asc' ? 'asc' : 'desc';
        $f['dir_given'] = isset($in['dir']);

        return $f;
    }

    /** @return array<int,array{code:string,name:string}> */
    public function gateways(): array
    {
        try {
            return $this->db->select('SELECT code, name FROM gateways ORDER BY sort_order, name');
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array<int,string> */
    public function currencies(): array
    {
        $list = [$this->defaultCurrency()];
        try {
            foreach ($this->db->select('SELECT DISTINCT currency FROM donations') as $row) {
                $c = strtoupper((string) $row['currency']);
                if ($c !== '' && !in_array($c, $list, true)) {
                    $list[] = $c;
                }
            }
        } catch (\Throwable) {
            // keep the default
        }

        return $list;
    }

    /** @return array<int,array<string,mixed>> */
    private function campaignOptions(): array
    {
        return $this->db->select("SELECT id, title FROM campaigns WHERE deleted_at IS NULL ORDER BY title");
    }

    /**
     * Filter controls for a report's form. date_from and date_to are always
     * present; the rest are specific to the report.
     *
     * @return array<int,array<string,mixed>>
     */
    public function filterDefs(string $slug): array
    {
        $gateway = ['name' => 'gateway', 'label' => 'Gateway', 'type' => 'select', 'options' => array_column($this->gateways(), 'name', 'code')];
        $currency = ['name' => 'currency', 'label' => 'Currency', 'type' => 'select', 'options' => array_combine($this->currencies(), $this->currencies()), 'noAny' => true];
        $campaign = ['name' => 'campaign_id', 'label' => 'Campaign', 'type' => 'select', 'options' => array_column($this->campaignOptions(), 'title', 'id')];
        $q = static fn (string $ph): array => ['name' => 'q', 'label' => 'Search', 'type' => 'text', 'placeholder' => $ph];
        $dStatus = ['completed' => 'Completed', 'pending' => 'Pending', 'processing' => 'Processing', 'failed' => 'Failed', 'cancelled' => 'Cancelled', 'refunded' => 'Refunded', 'abandoned' => 'Abandoned'];

        return match ($slug) {
            'overview'    => [$q('Purpose name in the results table'), $currency],
            'donations'   => [$q('Donation reference, donor or email'), $currency, $gateway, $campaign, ['name' => 'group', 'label' => 'Group by', 'type' => 'select', 'options' => ['day' => 'Day', 'month' => 'Month', 'year' => 'Year'], 'noAny' => true]],
            'payments'    => [$q('Reference, order no. or bank transaction'), ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $dStatus], $gateway, ['name' => 'environment', 'label' => 'Environment', 'type' => 'select', 'options' => ['live' => 'Live', 'sandbox' => 'Sandbox']], $currency],
            'campaigns'   => [$q('Campaign title'), ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'active' => 'Active', 'paused' => 'Paused', 'closed' => 'Closed']], $currency],
            'purposes'    => [$q('Purpose name'), ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'hidden' => 'Hidden']], $currency],
            'referrers'   => [$q('Referrer name or phone'), $currency],
            'donors'      => [$q('Name, email or phone'), ['name' => 'donor_type', 'label' => 'Donor type', 'type' => 'select', 'options' => ['new' => 'New donors', 'returning' => 'Returning donors']], ['name' => 'min_gifts', 'label' => 'Gifts', 'type' => 'select', 'options' => ['2' => '2 or more', '3' => '3 or more', '5' => '5 or more']], $gateway, $currency],
            'verification' => [$q('Donation reference, donor or referrer'), ['name' => 'review_status', 'label' => 'Verification', 'type' => 'select', 'options' => ['pending' => 'Awaiting verification', 'verified' => 'Verified', 'rejected' => 'Rejected', 'legacy' => 'Legacy (before review)']], $currency],
            'reconciliation' => [$q('Reference, order no. or bank transaction'), ['name' => 'issue', 'label' => 'Show', 'type' => 'select', 'options' => ['offline_transfers' => 'Bank transfers (not reconciled)', 'all' => 'All gateway transactions (incl. matched)', 'status_mismatch' => 'Status mismatch', 'unbacked_donation' => 'Confirmed without payment', 'amount_mismatch' => 'Amount mismatch', 'no_bank_reference' => 'No bank reference', 'stale' => 'Stuck unresolved']], $gateway, $currency],
            'refunds'     => [$q('Reference, donor or email'), ['name' => 'type', 'label' => 'Outcome', 'type' => 'select', 'options' => ['refunded' => 'Refunded', 'partial_refund' => 'Partial refunds', 'failed' => 'Failed', 'cancelled' => 'Cancelled', 'abandoned' => 'Abandoned']], $gateway, $currency],
            'financial'   => [$q('Donation reference, donor or email'), $currency, $gateway, ['name' => 'group', 'label' => 'Group by', 'type' => 'select', 'options' => ['day' => 'Day', 'month' => 'Month', 'year' => 'Year'], 'noAny' => true]],
            'receipts'    => [$q('Reference, donor or email'), ['name' => 'receipt_status', 'label' => 'Receipt', 'type' => 'select', 'options' => ['sent' => 'Sent', 'queued' => 'Queued', 'failed' => 'Failed', 'not_sent' => 'Not sent']], $gateway, $currency],
            'audit'       => [$q('Action, entity, admin email or IP'), ['name' => 'who', 'label' => 'Who', 'type' => 'select', 'options' => ['staff' => 'All staff', 'admins' => 'Admins only', 'all' => 'Everyone'], 'noAny' => true], ['name' => 'area', 'label' => 'Area', 'type' => 'select', 'options' => $this->auditAreas()]],
            default       => [],
        };
    }

    /** @return array<string,string> */
    private function auditAreas(): array
    {
        try {
            $out = [];
            foreach ($this->db->select("SELECT DISTINCT SUBSTRING_INDEX(action, '.', 1) AS area FROM audit_logs ORDER BY area") as $r) {
                $out[(string) $r['area']] = ucfirst(str_replace('_', ' ', (string) $r['area']));
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    // ------------------------------------------------------------------ SQL helpers

    /** Adds a value to the parameter list and returns its unique placeholder. */
    private function p(array &$params, mixed $value): string
    {
        $name = 'p' . (count($params) + 1);
        $params[$name] = $value;

        return ':' . $name;
    }

    /**
     * SQL for "expr falls inside the chosen dates". The end date is inclusive.
     *
     * @param array<string,mixed> $params
     */
    private function range(string $expr, array $f, array &$params): string
    {
        $parts = [];
        if (($f['date_from'] ?? '') !== '') {
            $parts[] = $expr . ' >= ' . $this->p($params, $f['date_from'] . ' 00:00:00');
        }
        if (($f['date_to'] ?? '') !== '') {
            $parts[] = $expr . ' < ' . $this->p($params, (new \DateTimeImmutable($f['date_to'], new \DateTimeZone('UTC')))->modify('+1 day')->format('Y-m-d') . ' 00:00:00');
        }

        return $parts === [] ? '1 = 1' : '(' . implode(' AND ', $parts) . ')';
    }

    /**
     * Same as range(), but the dates are written into the SQL. Only for
     * conditions repeated inside one statement (native prepared statements do
     * not allow a named placeholder twice). Safe: normalize() only ever lets a
     * strict, calendar-checked YYYY-MM-DD string through.
     */
    private function rangeLiteral(string $expr, array $f): string
    {
        $parts = [];
        foreach ([['date_from', '>=', ''], ['date_to', '<', '+1 day']] as [$key, $op, $shift]) {
            $v = (string) ($f[$key] ?? '');
            if ($v === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) !== 1) {
                continue;
            }
            $v = $shift !== '' ? (new \DateTimeImmutable($v, new \DateTimeZone('UTC')))->modify($shift)->format('Y-m-d') : $v;
            $parts[] = $expr . ' ' . $op . " '" . $v . " 00:00:00'";
        }

        return $parts === [] ? '1 = 1' : '(' . implode(' AND ', $parts) . ')';
    }

    /**
     * ORDER BY from a whitelist, never from the raw request value.
     *
     * @param array<int,array<string,mixed>> $columns
     */
    private function order(array $columns, array $f, string $defaultKey, string $defaultDir, string $tie): array
    {
        $map = [];
        foreach ($columns as $c) {
            if (!empty($c['sort'])) {
                $map[(string) $c['key']] = (string) $c['sort'];
            }
        }
        $key = isset($map[$f['sort']]) ? (string) $f['sort'] : $defaultKey;
        $dir = ($f['sort'] !== '' && isset($map[$f['sort']])) || $f['dir_given'] ? $f['dir'] : $defaultDir;
        if (!isset($map[$key])) {
            return [' ORDER BY ' . $tie, $key, $dir];
        }

        return [' ORDER BY ' . $map[$key] . ' ' . strtoupper($dir) . ', ' . $tie, $key, $dir];
    }

    /** @param array<string,mixed> $f */
    private function like(string $q): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
    }

    private function limit(int $page, int $perPage): string
    {
        return ' LIMIT ' . (int) $perPage . ' OFFSET ' . max(0, ($page - 1) * (int) $perPage);
    }

    /** Fills currency onto each row so money cells always format correctly. */
    private function withCurrency(array $rows, string $cur): array
    {
        foreach ($rows as &$r) {
            $r['currency'] = $r['currency'] ?? $cur;
        }

        return $rows;
    }

    private function money(string $label, int $minor, string $cur, string $sub = ''): array
    {
        return ['label' => $label, 'value' => $minor, 'kind' => 'money', 'currency' => $cur, 'sub' => $sub];
    }

    private function count(string $label, int|float $n, string $sub = ''): array
    {
        return ['label' => $label, 'value' => $n, 'kind' => 'int', 'sub' => $sub];
    }

    private function pct(string $label, ?float $v, string $sub = ''): array
    {
        return ['label' => $label, 'value' => $v, 'kind' => 'percent', 'sub' => $sub];
    }

    private function text(string $label, string $v, string $sub = ''): array
    {
        return ['label' => $label, 'value' => $v, 'kind' => 'text', 'sub' => $sub];
    }

    /** A SUM over no rows is NULL; show such counts as 0. */
    private function zeroCounts(array $rows): array
    {
        foreach ($rows as &$r) {
            foreach (['donations', 'donors', 'referred'] as $k) {
                if (array_key_exists($k, $r) && $r[$k] === null) {
                    $r[$k] = 0;
                }
            }
        }

        return $rows;
    }

    /** Whole-number average, rounded (so cards and table rows always agree). */
    private function avg(int $total, int $count): int
    {
        return $count > 0 ? (int) round($total / $count) : 0;
    }

    private function ratio(int|float $part, int|float $whole): ?float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : null;
    }

    /** Human range text used under every report title and in the PDF. */
    public static function rangeText(array $f): string
    {
        $from = (string) ($f['date_from'] ?? '');
        $to = (string) ($f['date_to'] ?? '');
        $fmt = static fn (string $d): string => date('j M Y', (int) strtotime($d));
        if ($from === '' && $to === '') {
            return 'All time';
        }
        if ($from !== '' && $to !== '') {
            return $fmt($from) . ' to ' . $fmt($to);
        }

        return $from !== '' ? 'From ' . $fmt($from) : 'Up to ' . $fmt($to);
    }

    /**
     * Readable list of the filters in force, for the page and the PDF.
     *
     * @param array<string,mixed> $report a result from run()
     * @return array<int,array{0:string,1:string}>
     */
    public function describeFilters(array $report): array
    {
        $f = $report['filters'];
        $out = [['Date range', self::rangeText($f)]];
        foreach ($report['filterDefs'] as $def) {
            $name = (string) $def['name'];
            $val = (string) ($f[$name] ?? '');
            if ($val === '') {
                continue;
            }
            $label = $def['type'] === 'select' ? (string) ($def['options'][$val] ?? $val) : $val;
            $out[] = [(string) $def['label'], $label];
        }

        return $out;
    }

    /** Plain-text value of one cell: used by the PDF and by the web table. */
    public static function cell(array $col, array $row, string $defaultCurrency = 'PKR'): string
    {
        $v = $row[$col['key']] ?? null;
        $type = (string) ($col['type'] ?? 'text');
        if ($v === null || $v === '') {
            return $type === 'int' && $v === 0 ? '0' : '—';
        }

        return match ($type) {
            'int'      => number_format((float) $v),
            'money'    => money((int) $v, (string) ($row['currency'] ?? $defaultCurrency)),
            'percent'  => number_format((float) $v, 1) . '%',
            'date'     => gmdate('j M Y', (int) strtotime((string) $v . ' UTC')),
            'datetime' => gmdate('j M Y H:i', (int) strtotime((string) $v . ' UTC')),
            'hours'    => (float) $v >= 48 ? number_format((float) $v / 24, 1) . ' days' : number_format((float) $v, 1) . ' h',
            'badge', 'status' => ucfirst(str_replace('_', ' ', (string) $v)),
            default    => (string) $v,
        };
    }

    // ================================================================== 1. OVERVIEW

    /** @param array<string,mixed> $f */
    private function overview(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];

        // Donations made in the range (all statuses), grouped by outcome.
        $p = ['cur' => $cur];
        $where = 'd.currency = :cur AND ' . $this->range('d.created_at', $f, $p);
        $byStatus = [];
        foreach ($this->db->select("SELECT CASE WHEN d.verification_status='rejected' THEN 'rejected'
                                        WHEN d.status='completed' AND d.verification_status<>'verified' THEN CONCAT('unverified ',d.verification_status)
                                        ELSE d.status END AS outcome, COUNT(*) AS c, COALESCE(SUM(d.amount_minor),0) AS s, COUNT(DISTINCT d.donor_id) AS donors
                                    FROM donations d WHERE {$where} GROUP BY outcome", $p) as $r) {
            $byStatus[(string) $r['outcome']] = $r;
        }
        $n = static fn (string $s): int => (int) ($byStatus[$s]['c'] ?? 0);
        $amt = static fn (string $s): int => (int) ($byStatus[$s]['s'] ?? 0);

        $verified = $this->db->selectOne("SELECT COUNT(*) AS n, COALESCE(SUM(d.amount_minor),0) AS s
            FROM donations d WHERE {$where} AND d.status = 'completed' AND d.verification_status = 'verified'", $p) ?? [];
        $confirmedN = (int)($verified['n'] ?? 0);
        $confirmed = (int)($verified['s'] ?? 0);
        $donors = (int) $this->db->int("SELECT COUNT(DISTINCT d.donor_id) FROM donations d WHERE {$where} AND d.status = 'completed' AND d.verification_status = 'verified'", $p);
        $largest = (int) $this->db->int("SELECT COALESCE(MAX(d.amount_minor),0) FROM donations d WHERE {$where} AND d.status = 'completed' AND d.verification_status = 'verified'", $p);
        $failedN = $n('failed') + $n('cancelled') + $n('abandoned');
        $finished = $confirmedN + $n('refunded') + $failedN;

        // Refunds actually paid back in the range.
        $rp = ['cur' => $cur];
        $refund = $this->db->selectOne("SELECT COUNT(*) AS c, COALESCE(SUM(r.amount_minor),0) AS s FROM payment_refunds r JOIN donations d ON d.id = r.donation_id
                                        WHERE d.verification_status = 'verified' AND r.status = 'completed' AND r.currency = :cur AND " . $this->range('COALESCE(r.completed_at, r.created_at)', $f, $rp), $rp) ?? ['c' => 0, 's' => 0];

        // Confirmed money that is not tied to a donation purpose.
        $general = (int) $this->db->int("SELECT COALESCE(SUM(d.amount_minor),0) FROM donations d WHERE {$where} AND d.status = 'completed' AND d.verification_status = 'verified' AND d.purpose_id IS NULL", $p);

        // Trend: daily up to ~3 months, monthly beyond.
        $trend = $this->trend($f, $cur);

        // Per gateway.
        $gateways = $this->db->select(
            "SELECT COALESCE(NULLIF(d.payment_method,''),g.name,d.source,'Unknown') AS gateway, COUNT(*) AS attempts,
                    SUM(d.status IN ('completed','refunded') AND d.verification_status = 'verified') AS paid,
                    SUM(d.status IN ('failed','cancelled','abandoned')) AS failed,
                    COALESCE(SUM(CASE WHEN d.status = 'completed' AND d.verification_status = 'verified' THEN d.amount_minor ELSE 0 END),0) AS confirmed_minor
             FROM donations d LEFT JOIN gateways g ON g.id = d.gateway_id
             WHERE {$where} GROUP BY gateway ORDER BY confirmed_minor DESC",
            $p
        );
        foreach ($gateways as &$g) {
            $done = (int) $g['paid'] + (int) $g['failed'];
            $g['success_rate'] = $this->ratio((int) $g['paid'], $done);
        }
        unset($g);

        // Top campaigns by confirmed money.
        $topCampaigns = $this->db->select(
            "SELECT c.title, COUNT(d.id) AS donations, COALESCE(SUM(d.amount_minor),0) AS raised_minor
             FROM campaigns c JOIN donations d ON d.campaign_id = c.id AND d.status = 'completed' AND d.verification_status = 'verified' AND {$where}
             WHERE c.deleted_at IS NULL GROUP BY c.id, c.title ORDER BY raised_minor DESC, c.id ASC LIMIT 5",
            $p
        );

        // "Right now" snapshot, independent of the date filter.
        $pendingVerification = $this->db->int('SELECT COUNT(*) FROM donations d WHERE ' . self::AWAITING_SQL);
        $activePurposes = $this->db->int("SELECT COUNT(*) FROM donation_purposes WHERE status = 'active'");
        $activeCampaigns = $this->db->int("SELECT COUNT(*) FROM campaigns WHERE deleted_at IS NULL AND status = 'active'");
        $stuck = $this->db->int("SELECT COUNT(*) FROM donations WHERE status IN ('pending','processing') AND created_at < (UTC_TIMESTAMP() - INTERVAL " . self::STALE_MINUTES . ' MINUTE)');

        $other = array_values(array_diff($this->currencies(), [$cur]));
        $notes = ['All amounts are in ' . $cur . '. Verified means staff-approved and completed; historical legacy completed donations are excluded. Refunds and other outcomes are separate.'];
        if ($other !== []) {
            $notes[] = 'Other currencies exist in the system (' . implode(', ', $other) . '). Use the currency filter to view them; amounts are never converted or added together.';
        }

        $fp = ['cur' => $cur];
        $pWhere = 'd.currency = :cur AND ' . $this->range('d.created_at', $f, $fp);
        if ($f['q'] !== '') {
            $pWhere .= ' AND dp.name LIKE ' . $this->p($fp, $this->like($f['q']));
        }
        $frSql = "SELECT dp.id, dp.name AS title, COALESCE(c.title,'—') AS campaign, COUNT(d.id) AS donations,
            COALESCE(SUM(d.amount_minor),0) AS raised_minor FROM donation_purposes dp
            JOIN donations d ON d.purpose_id = dp.id AND d.status = 'completed' AND d.verification_status = 'verified'
            LEFT JOIN campaigns c ON c.id = dp.campaign_id WHERE {$pWhere}
            GROUP BY dp.id, dp.name, c.title";
        $frCount = $this->db->int("SELECT COUNT(*) FROM ({$frSql}) all_p", $fp);
        $frOrder = match ($f['sort']) {
            'title' => 'title', 'donations' => 'donations', 'raised_minor' => 'raised_minor', default => 'raised_minor',
        };
        $frDir = $f['sort'] === '' && !$f['dir_given'] ? 'DESC' : strtoupper($f['dir']);
        $listedPurposes = $this->db->select("SELECT * FROM ({$frSql}) all_p ORDER BY {$frOrder} {$frDir},id ASC" . $this->limit($page, $perPage), $fp);
        $topRows = [];
        foreach ($listedPurposes as $r) {
            $topRows[] = ['title' => $r['title'], 'campaign' => $r['campaign'], 'donations' => (int) $r['donations'], 'raised_minor' => (int) $r['raised_minor'],
                'share_pct' => $this->ratio((int) $r['raised_minor'], $confirmed), 'currency' => $cur];
        }

        return [
            'description' => 'Donation totals, trends and payment health for the chosen period.',
            'basis'       => 'Donations are dated by the day the donor gave (donation date). Refunds by the day they were paid back. Amounts in ' . $cur . '.',
            'summary'     => [
                $this->money('Confirmed donations', $confirmed, $cur, number_format($confirmedN) . ' staff-verified completed'),
                $this->count('Total verified donations', $confirmedN, money($confirmed,$cur)),
                $this->money('Average donation', $confirmedN > 0 ? $this->avg($confirmed, $confirmedN) : 0, $cur, 'Largest ' . money($largest, $cur)),
                $this->count('Unique donors', $donors, 'with a confirmed gift'),
                $this->money('Refunded', (int) $refund['s'], $cur, number_format((int) $refund['c']) . ' refund(s) paid'),
                $this->count('Refunded donations', $n('refunded')),
                $this->money('Awaiting the bank', (int)$this->db->scalar("SELECT COALESCE(SUM(d.amount_minor),0) FROM donations d WHERE {$where} AND d.status IN ('pending','processing') AND d.verification_status<>'rejected'",$p), $cur, number_format($n('pending') + $n('processing')) . ' pending / processing'),
                $this->count('Pending donations', $n('pending')+$n('processing')),
                $this->count('Rejected donations', $n('rejected')),
                $this->money('Rejected amount', (int)($this->db->scalar("SELECT COALESCE(SUM(d.amount_minor),0) FROM donations d WHERE {$where} AND d.verification_status='rejected'", $p) ?: 0), $cur),
                $this->count('Failed payments', (int)$this->db->int("SELECT COUNT(*) FROM payment_transactions pt JOIN donations d ON d.id=pt.donation_id WHERE d.currency=:cur AND pt.status='failed' AND " . $this->rangeLiteral('pt.created_at', $f), ['cur'=>$cur])),
                $this->pct('Payment success rate', $this->ratio($confirmedN + $n('refunded'), $finished), number_format($failedN) . ' failed, cancelled or abandoned'),
                $this->count('Active purposes', $activePurposes, 'right now'),
                $this->count('Active campaigns', $activeCampaigns, 'right now'),
                $this->count('Awaiting verification', $pendingVerification, 'donations in review, right now'),
                $this->count('Stuck payments', $stuck, 'unresolved over ' . self::STALE_MINUTES . ' minutes, right now'),
            ],
            'chart'       => ['series' => $trend['series'], 'unit' => $trend['unit']],
            'statusMix'   => array_map(static fn (array $r, string $k): array => ['status' => $k, 'c' => (int) $r['c']], $byStatus, array_keys($byStatus)),
            'gateways'    => $gateways,
            'topCampaigns' => $topCampaigns,
            'general'     => $general,
            'columns'     => [
                ['key' => 'title', 'label' => 'Purpose', 'sort' => 'title', 'w' => 4],
                ['key' => 'campaign', 'label' => 'Campaign', 'w' => 3],
                ['key' => 'donations', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'sort' => 'donations', 'w' => 1.4],
                ['key' => 'raised_minor', 'label' => 'Raised', 'type' => 'money', 'align' => 'right', 'sort' => 'raised_minor', 'w' => 2],
                ['key' => 'share_pct', 'label' => 'Share of verified', 'type' => 'percent', 'align' => 'right', 'w' => 1.8],
            ],
            'rows'        => $topRows,
            'total'       => $frCount,
            'sort'        => $frOrder,
            'dir'         => strtolower($frDir),
            'rowsTitle'   => 'Purposes with verified donations in this period',
            'extra'       => [
                ['title' => 'Recent transactions', 'columns' => [
                    ['key'=>'public_reference','label'=>'Donation','type'=>'code','w'=>2.3],
                    ['key'=>'donor','label'=>'Donor','w'=>2.5],
                    ['key'=>'amount_minor','label'=>'Amount','type'=>'money','w'=>2],
                    ['key'=>'status','label'=>'Status','type'=>'status','w'=>2],
                    ['key'=>'verification_status','label'=>'Verification','type'=>'status','w'=>2],
                    ['key'=>'created_at','label'=>'Date','type'=>'datetime','w'=>2],
                ], 'rows' => $this->withCurrency($this->db->select("SELECT d.public_reference, COALESCE(dn.name,'—') AS donor, d.amount_minor, d.status, d.verification_status, d.created_at FROM donations d LEFT JOIN donors dn ON dn.id=d.donor_id WHERE {$where} ORDER BY d.created_at DESC,d.id DESC LIMIT 10", $p), $cur)],
                ['title' => 'Recent verification activities', 'columns' => [
                    ['key'=>'public_reference','label'=>'Donation','type'=>'code','w'=>2.3],
                    ['key'=>'decision','label'=>'Decision','w'=>2],
                    ['key'=>'reviewer','label'=>'Reviewer','w'=>2.5],
                    ['key'=>'created_at','label'=>'Date','type'=>'datetime','w'=>2],
                ], 'rows' => $this->db->select("SELECT d.public_reference, ev.decision, COALESCE(u.email,'System') AS reviewer, ev.created_at FROM donation_review_events ev JOIN donations d ON d.id=ev.donation_id LEFT JOIN users u ON u.id=ev.actor_user_id WHERE d.currency=:cur AND " . $this->rangeLiteral('ev.created_at', $f) . " ORDER BY ev.created_at DESC,ev.id DESC LIMIT 10", ['cur'=>$cur])],
                [
                    'title'   => 'Payments by gateway',
                    'columns' => [
                        ['key' => 'gateway', 'label' => 'Payment method', 'w' => 3],
                        ['key' => 'attempts', 'label' => 'Attempts', 'type' => 'int', 'align' => 'right', 'w' => 1.4],
                        ['key' => 'paid', 'label' => 'Paid', 'type' => 'int', 'align' => 'right', 'w' => 1.2],
                        ['key' => 'failed', 'label' => 'Failed', 'type' => 'int', 'align' => 'right', 'w' => 1.2],
                        ['key' => 'success_rate', 'label' => 'Success rate', 'type' => 'percent', 'align' => 'right', 'w' => 1.6],
                        ['key' => 'confirmed_minor', 'label' => 'Confirmed', 'type' => 'money', 'align' => 'right', 'w' => 2],
                    ],
                    'rows'    => $this->withCurrency($gateways, $cur),
                ],
                [
                    'title'   => 'Top campaigns in this period',
                    'columns' => [
                        ['key' => 'title', 'label' => 'Campaign', 'w' => 5],
                        ['key' => 'donations', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'w' => 1.5],
                        ['key' => 'raised_minor', 'label' => 'Raised', 'type' => 'money', 'align' => 'right', 'w' => 2],
                    ],
                    'rows'    => $this->withCurrency($topCampaigns, $cur),
                ],
                [
                    'title'   => 'Donation outcomes',
                    'columns' => [
                        ['key' => 'status', 'label' => 'Outcome', 'type' => 'badge', 'w' => 3],
                        ['key' => 'c', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'w' => 1.5],
                        ['key' => 's', 'label' => 'Amount', 'type' => 'money', 'align' => 'right', 'w' => 2],
                    ],
                    'rows'    => $this->withCurrency(array_map(static fn (array $r, string $k): array => ['status' => $k, 'c' => (int) $r['c'], 's' => (int) $r['s']], $byStatus, array_keys($byStatus)), $cur),
                ],
                ['title' => 'Verified donation trend', 'columns' => [
                    ['key'=>'period','label'=>'Period','w'=>2.5],
                    ['key'=>'donations','label'=>'Verified donations','type'=>'int','w'=>2],
                    ['key'=>'confirmed_minor','label'=>'Verified amount','type'=>'money','w'=>2.5],
                ], 'rows' => $this->withCurrency(array_map(static fn (array $point): array => [
                    'period'=>$point['label'], 'donations'=>(int)$point['count'], 'confirmed_minor'=>(int)$point['minor']
                ], $trend['series']), $cur)],
            ],
            'notes'       => array_merge($notes, $general > 0 ? ['Donations not linked to a purpose in this period: ' . money($general, $cur) . '. They are included in the totals above.'] : []),
        ];
    }

    /**
     * Confirmed money over time. Daily for ranges up to 92 days, otherwise
     * monthly. Every day/month in the range is present (zero when quiet).
     *
     * @return array{series:array<int,array{label:string,minor:int,count:int}>,unit:string}
     */
    private function trend(array $f, string $cur): array
    {
        $today = gmdate('Y-m-d');
        $start = $f['date_from'] !== '' ? $f['date_from'] : (string) ($this->db->scalar("SELECT DATE(MIN(created_at)) FROM donations WHERE status = 'completed' AND verification_status='verified' AND currency = :c", ['c' => $cur]) ?: '');
        $end = $f['date_to'] !== '' ? $f['date_to'] : $today;
        if ($start === '' || $start > $end) {
            return ['series' => [], 'unit' => 'day'];
        }
        $utc = new \DateTimeZone('UTC');
        $first = new \DateTimeImmutable($start,$utc);
        $lastDate = new \DateTimeImmutable($end,$utc);
        $days = (int)$first->diff($lastDate)->days + 1;
        $unit = $days <= 92 ? 'day' : 'month';

        $p = ['cur' => $cur];
        $key = $unit === 'day' ? "DATE_FORMAT(d.created_at, '%Y-%m-%d')" : "DATE_FORMAT(d.created_at, '%Y-%m')";
        $rows = $this->db->select(
            "SELECT {$key} AS k, COUNT(*) AS c, COALESCE(SUM(d.amount_minor),0) AS s FROM donations d
             WHERE d.status = 'completed' AND d.verification_status = 'verified' AND d.currency = :cur AND " . $this->range('d.created_at', ['date_from' => $start, 'date_to' => $end], $p) . ' GROUP BY k',
            $p
        );
        $by = [];
        foreach ($rows as $r) {
            $by[(string) $r['k']] = $r;
        }

        $series = [];
        if ($unit === 'day') {
            for ($date=$first; $date <= $lastDate; $date=$date->modify('+1 day')) {
                $k=$date->format('Y-m-d');
                $series[] = ['label'=>$date->format('j M'), 'minor'=>(int)($by[$k]['s'] ?? 0), 'count'=>(int)($by[$k]['c'] ?? 0)];
            }
        } else {
            $month=new \DateTimeImmutable(substr($start,0,7) . '-01',$utc);
            $lastMonth=new \DateTimeImmutable(substr($end,0,7) . '-01',$utc);
            while ($month <= $lastMonth && count($series) < 120) {
                $k=$month->format('Y-m');
                $series[] = ['label'=>$month->format('M Y'), 'minor'=>(int)($by[$k]['s'] ?? 0), 'count'=>(int)($by[$k]['c'] ?? 0)];
                $month=$month->modify('+1 month');
            }
        }

        return ['series' => $series, 'unit' => $unit];
    }

    // ================================================================== 2. DONATION SUMMARY

    /** @param array<string,mixed> $f */
    private function donationSummary(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];
        $fmt = ['day' => '%Y-%m-%d', 'month' => '%Y-%m', 'year' => '%Y'][$f['group']];

        $p = ['cur' => $cur];
        $where = 'd.currency = :cur AND ' . $this->range('d.created_at', $f, $p);
        if ($f['gateway'] !== '') {
            $where .= ' AND d.gateway_id = (SELECT id FROM gateways WHERE code = ' . $this->p($p, $f['gateway']) . ')';
        }
        if ($f['campaign_id'] !== '') {
            $where .= ' AND d.campaign_id = ' . $this->p($p, (int) $f['campaign_id']);
        }

        if ($f['q'] !== '') {
            $like = $this->quote($this->like($f['q']));
            $where .= " AND (d.public_reference LIKE {$like} OR EXISTS (SELECT 1 FROM donors dn WHERE dn.id=d.donor_id AND (dn.name LIKE {$like} OR dn.email LIKE {$like})))";
        }

        $columns = [
            ['key' => 'period', 'label' => ucfirst($f['group']), 'sort' => 'period', 'w' => 2],
            ['key' => 'donations', 'label' => 'Confirmed', 'type' => 'int', 'align' => 'right', 'sort' => 'donations', 'w' => 1.5],
            ['key' => 'confirmed_minor', 'label' => 'Confirmed amount', 'type' => 'money', 'align' => 'right', 'sort' => 'confirmed_minor', 'w' => 2.4],
            ['key' => 'average_minor', 'label' => 'Average', 'type' => 'money', 'align' => 'right', 'sort' => 'average_minor', 'w' => 2],
            ['key' => 'refunded_n', 'label' => 'Refunded', 'type' => 'int', 'align' => 'right', 'sort' => 'refunded_n', 'w' => 1.4],
            ['key' => 'refunded_minor', 'label' => 'Refunded amount', 'type' => 'money', 'align' => 'right', 'sort' => 'refunded_minor', 'w' => 2.4],
            ['key' => 'gross_minor', 'label' => 'Gross received', 'type' => 'money', 'align' => 'right', 'sort' => 'gross_minor', 'w' => 2.4],
        ];
        [$order, $sortKey, $dir] = $this->order($columns, $f, 'period', 'desc', 'period DESC');

        $inner = "SELECT DATE_FORMAT(d.created_at, '{$fmt}') AS period,
                    SUM(d.status = 'completed' AND d.verification_status = 'verified') AS donations,
                    COALESCE(SUM(CASE WHEN d.status = 'completed' AND d.verification_status = 'verified' THEN d.amount_minor END),0) AS confirmed_minor,
                    COALESCE(ROUND(AVG(CASE WHEN d.status = 'completed' AND d.verification_status = 'verified' THEN d.amount_minor END)),0) AS average_minor,
                    SUM(d.status = 'refunded' AND d.verification_status = 'verified') AS refunded_n,
                    COALESCE(SUM(CASE WHEN d.status = 'refunded' AND d.verification_status = 'verified' THEN d.amount_minor END),0) AS refunded_minor,
                    COALESCE(SUM(CASE WHEN d.status IN ('completed','refunded') AND d.verification_status = 'verified' THEN d.amount_minor END),0) AS gross_minor
                  FROM donations d WHERE {$where} GROUP BY period HAVING SUM(d.status IN ('completed','refunded') AND d.verification_status = 'verified') > 0";

        $total = $this->db->int("SELECT COUNT(*) FROM ({$inner}) x", $p);
        $rows = $this->db->select("SELECT * FROM ({$inner}) x{$order}" . $this->limit($page, $perPage), $p);

        $tot = $this->db->selectOne(
            "SELECT SUM(d.status = 'completed' AND d.verification_status = 'verified') AS n, COALESCE(SUM(CASE WHEN d.status = 'completed' AND d.verification_status = 'verified' THEN d.amount_minor END),0) AS s,
                    COALESCE(MAX(CASE WHEN d.status = 'completed' AND d.verification_status = 'verified' THEN d.amount_minor END),0) AS mx,
                    COUNT(DISTINCT CASE WHEN d.status = 'completed' AND d.verification_status = 'verified' THEN d.donor_id END) AS donors,
                    SUM(d.status = 'refunded' AND d.verification_status = 'verified') AS rn, COALESCE(SUM(CASE WHEN d.status = 'refunded' AND d.verification_status = 'verified' THEN d.amount_minor END),0) AS rs,
                    SUM(d.status IN ('pending','processing') AND d.verification_status<>'rejected') AS pn, COALESCE(SUM(CASE WHEN d.status IN ('pending','processing') AND d.verification_status<>'rejected' THEN d.amount_minor END),0) AS ps,
                    SUM(d.status IN ('failed','cancelled','abandoned')) AS fn
             FROM donations d WHERE {$where}",
            $p
        ) ?? [];
        $confirmedN = (int) ($tot['n'] ?? 0);
        $confirmed = (int) ($tot['s'] ?? 0);

        $byGateway = $this->db->select(
            "SELECT COALESCE(g.name,'Unknown') AS gateway, COUNT(*) AS donations, COALESCE(SUM(d.amount_minor),0) AS confirmed_minor
             FROM donations d LEFT JOIN gateways g ON g.id = d.gateway_id
             WHERE {$where} AND d.status = 'completed' AND d.verification_status = 'verified' GROUP BY d.gateway_id, g.name ORDER BY confirmed_minor DESC",
            $p
        );
        foreach ($byGateway as &$g) {
            $g['share'] = $this->ratio((int) $g['confirmed_minor'], $confirmed);
        }
        unset($g);
        $byStatus = $this->db->select("SELECT CASE WHEN d.verification_status='rejected' THEN 'rejected' WHEN d.status='completed' AND d.verification_status<>'verified' THEN CONCAT('unverified ',d.verification_status) ELSE d.status END AS status, COUNT(*) AS c, COALESCE(SUM(d.amount_minor),0) AS s FROM donations d WHERE {$where} GROUP BY status ORDER BY c DESC", $p);
        $byCampaign = $this->db->select(
            "SELECT COALESCE(c.title,'No campaign / general') AS campaign, COUNT(*) AS donations, COALESCE(SUM(d.amount_minor),0) AS confirmed_minor
             FROM donations d LEFT JOIN campaigns c ON c.id = d.campaign_id
             WHERE {$where} AND d.status = 'completed' AND d.verification_status = 'verified' GROUP BY d.campaign_id, c.title ORDER BY confirmed_minor DESC LIMIT 10",
            $p
        );

        return [
            'description' => 'Confirmed giving grouped by ' . $f['group'] . ', with refunds shown separately.',
            'basis'       => 'Dated by donation date. Confirmed = staff-verified completed donations; gross also includes staff-verified refunded gifts, shown separately. Currency ' . $cur . '.',
            'summary'     => [
                $this->money('Confirmed amount', $confirmed, $cur, number_format($confirmedN) . ' donations'),
                $this->money('Average donation', $confirmedN > 0 ? $this->avg($confirmed, $confirmedN) : 0, $cur),
                $this->money('Largest donation', (int) ($tot['mx'] ?? 0), $cur),
                $this->count('Unique donors', (int) ($tot['donors'] ?? 0)),
                $this->money('Refunded', (int) ($tot['rs'] ?? 0), $cur, number_format((int) ($tot['rn'] ?? 0)) . ' donations'),
                $this->money('Gross received', $confirmed + (int) ($tot['rs'] ?? 0), $cur, 'confirmed + refunded'),
                $this->money('Awaiting the bank', (int) ($tot['ps'] ?? 0), $cur, number_format((int) ($tot['pn'] ?? 0)) . ' pending / processing'),
                $this->count('Failed / cancelled', (int) ($tot['fn'] ?? 0), 'never counted'),
                $this->money('Rejected', (int)$this->db->scalar("SELECT COALESCE(SUM(d.amount_minor),0) FROM donations d WHERE {$where} AND d.verification_status='rejected'", $p), $cur),
                $this->money('Cancelled', (int)$this->db->scalar("SELECT COALESCE(SUM(d.amount_minor),0) FROM donations d WHERE {$where} AND d.status='cancelled'", $p), $cur),
            ],
            'columns'     => $columns,
            'rows'        => $this->withCurrency($rows, $cur),
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'extra'       => [
                ['title' => 'Confirmed by gateway', 'columns' => [
                    ['key' => 'gateway', 'label' => 'Gateway', 'w' => 4], ['key' => 'donations', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'w' => 1.5],
                    ['key' => 'confirmed_minor', 'label' => 'Amount', 'type' => 'money', 'align' => 'right', 'w' => 2.2], ['key' => 'share', 'label' => 'Share', 'type' => 'percent', 'align' => 'right', 'w' => 1.3],
                ], 'rows' => $this->withCurrency($byGateway, $cur)],
                ['title' => 'All donations by outcome', 'columns' => [
                    ['key' => 'status', 'label' => 'Outcome', 'type' => 'badge', 'w' => 4], ['key' => 'c', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'w' => 1.5],
                    ['key' => 's', 'label' => 'Amount', 'type' => 'money', 'align' => 'right', 'w' => 2.2],
                ], 'rows' => $this->withCurrency($byStatus, $cur)],
                ['title' => 'Donors with verified gifts (top 10)', 'columns' => [
                    ['key'=>'donor','label'=>'Donor','w'=>3],['key'=>'email','label'=>'Email','w'=>3],
                    ['key'=>'donations','label'=>'Donations','type'=>'int','w'=>1.2],
                    ['key'=>'confirmed_minor','label'=>'Verified amount','type'=>'money','w'=>2],
                ], 'rows' => $this->withCurrency($this->db->select("SELECT COALESCE(dn.name,'—') AS donor, COALESCE(dn.email,'—') AS email,
                    COUNT(*) AS donations, COALESCE(SUM(d.amount_minor),0) AS confirmed_minor FROM donations d LEFT JOIN donors dn ON dn.id=d.donor_id
                    WHERE {$where} AND d.status='completed' AND d.verification_status='verified' GROUP BY d.donor_id,dn.name,dn.email ORDER BY confirmed_minor DESC LIMIT 10",$p),$cur)],
                ['title' => 'Top campaigns (confirmed)', 'columns' => [
                    ['key' => 'campaign', 'label' => 'Campaign', 'w' => 4], ['key' => 'donations', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'w' => 1.5],
                    ['key' => 'confirmed_minor', 'label' => 'Amount', 'type' => 'money', 'align' => 'right', 'w' => 2.2],
                ], 'rows' => $this->withCurrency($byCampaign, $cur)],
            ],
            'notes'       => ['Periods with no confirmed or refunded donations are not listed.'],
        ];
    }

    // ================================================================== 3. PAYMENT TRANSACTIONS

    /** @param array<string,mixed> $f */
    private function payments(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];
        $p = ['cur' => $cur];
        $where = 'pt.currency = :cur AND ' . $this->range('pt.created_at', $f, $p);
        if ($f['status'] !== '') {
            $where .= ' AND pt.status = ' . $this->p($p, $f['status']);
        }
        if ($f['gateway'] !== '') {
            $where .= ' AND g.code = ' . $this->p($p, $f['gateway']);
        }
        if ($f['environment'] !== '') {
            $where .= ' AND pt.environment = ' . $this->p($p, $f['environment']);
        }
        if ($f['q'] !== '') {
            $like = $this->like($f['q']);
            $where .= ' AND (d.public_reference LIKE ' . $this->p($p, $like) . ' OR pt.merchant_order_id LIKE ' . $this->p($p, $like)
                . ' OR pt.provider_transaction_id LIKE ' . $this->p($p, $like) . ' OR pt.provider_order_id LIKE ' . $this->p($p, $like) . ')';
        }
        $from = 'FROM payment_transactions pt JOIN donations d ON d.id = pt.donation_id LEFT JOIN gateways g ON g.id = pt.gateway_id LEFT JOIN donors dn ON dn.id = d.donor_id LEFT JOIN campaigns c ON c.id = d.campaign_id';

        $columns = [
            ['key' => 'created_at', 'label' => 'Created', 'type' => 'datetime', 'sort' => 'pt.created_at', 'w' => 2.2],
            ['key' => 'public_reference', 'label' => 'Donation', 'type' => 'code', 'sort' => 'd.public_reference', 'w' => 2.4, 'link' => 'donation'],
            ['key' => 'receipt_number', 'label' => 'Receipt no.', 'type' => 'code', 'w' => 2.2],
            ['key' => 'donor', 'label' => 'Donor', 'w' => 2.6],
            ['key' => 'campaign', 'label' => 'Campaign', 'w' => 2.6],
            ['key' => 'payment_method', 'label' => 'Payment method', 'w' => 2],
            ['key' => 'verification_status', 'label' => 'Verification', 'type' => 'status', 'w' => 2],
            ['key' => 'merchant_order_id', 'label' => 'Order no.', 'type' => 'code', 'sort' => 'pt.merchant_order_id', 'w' => 2.2],
            ['key' => 'provider_transaction_id', 'label' => 'Bank transaction', 'type' => 'code', 'sort' => 'pt.provider_transaction_id', 'w' => 2.4],
            ['key' => 'gateway', 'label' => 'Gateway', 'sort' => 'g.name', 'w' => 1.8],
            ['key' => 'environment', 'label' => 'Env', 'type' => 'badge', 'sort' => 'pt.environment', 'w' => 1.1],
            ['key' => 'status', 'label' => 'Status', 'type' => 'status', 'sort' => 'pt.status', 'w' => 1.7],
            ['key' => 'amount_minor', 'label' => 'Amount', 'type' => 'money', 'align' => 'right', 'sort' => 'pt.amount_minor', 'w' => 2],
            ['key' => 'response', 'label' => 'Bank response', 'w' => 3],
            ['key' => 'completed_at', 'label' => 'Completed', 'type' => 'datetime', 'sort' => 'pt.completed_at', 'w' => 2.2],
        ];
        [$order, $sortKey, $dir] = $this->order($columns, $f, 'created_at', 'desc', 'pt.id DESC');

        $total = $this->db->int("SELECT COUNT(*) {$from} WHERE {$where}", $p);
        $rows = $this->db->select(
            "SELECT pt.id, pt.created_at, pt.completed_at, pt.merchant_order_id, pt.provider_transaction_id, pt.environment, pt.status, pt.amount_minor,
                    d.id AS donation_id, d.public_reference, d.receipt_number, d.verification_status, COALESCE(dn.name,'—') AS donor,
                    COALESCE(c.title,'—') AS campaign, COALESCE(d.payment_method,g.name,'—') AS payment_method, g.name AS gateway,
                    TRIM(CONCAT(COALESCE(pt.provider_response_code,''), ' ', COALESCE(pt.provider_response_description,''))) AS response
             {$from} WHERE {$where}{$order}" . $this->limit($page, $perPage),
            $p
        );

        $agg = $this->db->select("SELECT pt.status, COUNT(*) AS c, COALESCE(SUM(pt.amount_minor),0) AS s {$from} WHERE {$where} GROUP BY pt.status", $p);
        $c = $s = [];
        foreach ($agg as $r) {
            $c[(string) $r['status']] = (int) $r['c'];
            $s[(string) $r['status']] = (int) $r['s'];
        }
        $n = static fn (string ...$k): int => array_sum(array_map(static fn (string $x): int => $c[$x] ?? 0, $k));
        $m = static fn (string ...$k): int => array_sum(array_map(static fn (string $x): int => $s[$x] ?? 0, $k));
        $attempts = array_sum($c);
        $dupes = $this->db->int("SELECT COUNT(*) FROM (SELECT donation_id FROM payment_transactions WHERE status IN ('completed','refunded') GROUP BY donation_id HAVING COUNT(*) > 1) x");

        $byGateway = $this->db->select(
            "SELECT COALESCE(g.name,'Unknown') AS gateway, pt.environment, COUNT(*) AS attempts, SUM(pt.status = 'completed') AS completed,
                    SUM(pt.status IN ('failed','cancelled','abandoned')) AS failed, COALESCE(SUM(CASE WHEN pt.status = 'completed' THEN pt.amount_minor END),0) AS completed_minor
             {$from} WHERE {$where} GROUP BY pt.gateway_id, g.name, pt.environment ORDER BY attempts DESC",
            $p
        );

        return [
            'description' => 'Every payment attempt sent to a gateway, with the bank\'s own answer.',
            'basis'       => 'Dated by when the attempt was created. One donation can have several attempts; completed attempt totals describe gateway outcomes, not staff-verified donations. Currency ' . $cur . '.',
            'summary'     => [
                $this->count('Payment attempts', $attempts),
                $this->money('Completed', $m('completed'), $cur, number_format($n('completed')) . ' transactions'),
                $this->count('Pending / processing', $n('pending', 'processing'), money($m('pending', 'processing'), $cur) . ' awaiting the bank'),
                $this->count('Failed', $n('failed'), money($m('failed'), $cur) . ' not collected'),
                $this->count('Cancelled / abandoned', $n('cancelled', 'abandoned')),
                $this->count('Refunded', $n('refunded'), money($m('refunded'), $cur)),
                $this->pct('Success rate', $this->ratio($n('completed', 'refunded'), $n('completed', 'refunded', 'failed', 'cancelled', 'abandoned')), 'of attempts that finished'),
                $this->count('Duplicate completed payments', $dupes, 'donations paid more than once (all time)'),
            ],
            'columns'     => $columns,
            'rows'        => $this->withCurrency($rows, $cur),
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'extra'       => [['title' => 'By gateway and environment', 'columns' => [
                ['key' => 'gateway', 'label' => 'Gateway', 'w' => 3], ['key' => 'environment', 'label' => 'Env', 'type' => 'badge', 'w' => 1.4],
                ['key' => 'attempts', 'label' => 'Attempts', 'type' => 'int', 'align' => 'right', 'w' => 1.5], ['key' => 'completed', 'label' => 'Completed', 'type' => 'int', 'align' => 'right', 'w' => 1.5],
                ['key' => 'failed', 'label' => 'Failed', 'type' => 'int', 'align' => 'right', 'w' => 1.3], ['key' => 'completed_minor', 'label' => 'Completed amount', 'type' => 'money', 'align' => 'right', 'w' => 2.4],
            ], 'rows' => $this->withCurrency($byGateway, $cur)]],
            'notes'       => ['Sandbox transactions are test payments. Filter by environment = Live to see real money only.'],
        ];
    }

    // ================================================================== 4. CAMPAIGN PERFORMANCE

    /** @param array<string,mixed> $f */
    private function campaigns(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];

        // Donation conditions live in the JOIN, so campaigns without gifts in
        // the period still appear (with zeros).
        $params = [];
        $join = 'd.campaign_id = c.id AND d.currency = ' . $this->p($params, $cur) . ' AND ' . $this->range('d.created_at', $f, $params);

        $where = 'c.deleted_at IS NULL';
        if ($f['status'] !== '') {
            $where .= ' AND c.status = ' . $this->p($params, $f['status']);
        }
        if ($f['q'] !== '') {
            $where .= ' AND c.title LIKE ' . $this->p($params, $this->like($f['q']));
        }

        $columns = [
            ['key' => 'title', 'label' => 'Campaign', 'sort' => 'title', 'w' => 4, 'link' => 'campaign'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'status', 'sort' => 'status', 'w' => 1.6],
            ['key' => 'runs', 'label' => 'Runs', 'w' => 3],
            ['key' => 'raised_minor', 'label' => 'Raised', 'type' => 'money', 'align' => 'right', 'sort' => 'raised_minor', 'w' => 2.2],
            ['key' => 'donations', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'sort' => 'donations', 'w' => 1.6],
            ['key' => 'donors', 'label' => 'Donors', 'type' => 'int', 'align' => 'right', 'sort' => 'donors', 'w' => 1.3],
            ['key' => 'average_minor', 'label' => 'Average', 'type' => 'money', 'align' => 'right', 'sort' => 'average_minor', 'w' => 2],
            ['key' => 'pending_minor', 'label' => 'Pending', 'type' => 'money', 'align' => 'right', 'sort' => 'pending_minor', 'w' => 2],
            ['key' => 'refunded_minor', 'label' => 'Refunded', 'type' => 'money', 'align' => 'right', 'sort' => 'refunded_minor', 'w' => 2],
        ];
        [$order, $sortKey, $dir] = $this->order($columns, $f, 'raised_minor', 'desc', 'id ASC');
        $inner = "SELECT c.id, c.title, c.status, c.start_at, c.end_at,
                    COALESCE(SUM(CASE WHEN d.status = 'completed' AND d.verification_status = 'verified' THEN d.amount_minor END),0) AS raised_minor,
                    SUM(d.status = 'completed' AND d.verification_status = 'verified') AS donations,
                    COUNT(DISTINCT CASE WHEN d.status = 'completed' AND d.verification_status = 'verified' THEN d.donor_id END) AS donors,
                    COALESCE(ROUND(AVG(CASE WHEN d.status = 'completed' AND d.verification_status = 'verified' THEN d.amount_minor END)),0) AS average_minor,
                    COALESCE(SUM(CASE WHEN d.status IN ('pending','processing') THEN d.amount_minor END),0) AS pending_minor,
                    COALESCE(SUM(CASE WHEN d.status = 'refunded' THEN d.amount_minor END),0) AS refunded_minor
                  FROM campaigns c LEFT JOIN donations d ON {$join} WHERE {$where} GROUP BY c.id, c.title, c.status, c.start_at, c.end_at";
        $outer = "SELECT x.* FROM ({$inner}) x";

        $total = $this->db->int("SELECT COUNT(*) FROM ({$inner}) y", $params);
        $rows = $this->db->select("SELECT * FROM ({$outer}) z{$order}" . $this->limit($page, $perPage), $params);
        foreach ($rows as &$r) {
            $r['runs'] = $this->runs((string) ($r['start_at'] ?? ''), (string) ($r['end_at'] ?? ''));
            $r['donations'] = (int) $r['donations'];
        }
        unset($r);

        $all = $this->db->selectOne("SELECT COALESCE(SUM(raised_minor),0) AS raised, COALESCE(SUM(donations),0) AS n, COALESCE(SUM(refunded_minor),0) AS refunded, COALESCE(SUM(pending_minor),0) AS pending, COUNT(*) AS campaigns FROM ({$inner}) y", $params) ?? [];
        $best = $this->db->selectOne("SELECT title, raised_minor FROM ({$inner}) y WHERE raised_minor > 0 ORDER BY raised_minor DESC, id ASC LIMIT 1", $params);
        $active = $this->db->int("SELECT COUNT(*) FROM ({$inner}) y WHERE status = 'active'", $params);

        // Money from donations not tied to any campaign, for context.
        $up = ['cur' => $cur];
        $unattached = (int) $this->db->int("SELECT COALESCE(SUM(d.amount_minor),0) FROM donations d WHERE d.status = 'completed' AND d.verification_status = 'verified' AND d.campaign_id IS NULL AND d.currency = :cur AND " . $this->range('d.created_at', $f, $up), $up);

        return [
            'description' => 'How each campaign is doing: money raised, donors, pending and refunded gifts.',
            'basis'       => 'Donations dated by donation date and linked by the campaign recorded on each donation. Currency ' . $cur . '.',
            'summary'     => [
                $this->count('Campaigns', (int) ($all['campaigns'] ?? 0), number_format($active) . ' active'),
                $this->money('Raised through campaigns', (int) ($all['raised'] ?? 0), $cur, number_format((int) ($all['n'] ?? 0)) . ' donations'),
                $this->money('Awaiting the bank', (int) ($all['pending'] ?? 0), $cur),
                $this->money('Refunded', (int) ($all['refunded'] ?? 0), $cur),
                $this->money('Raised outside campaigns', $unattached, $cur, 'donations with no campaign'),
                $this->text('Top campaign', $best !== null ? (string) $best['title'] : '—', $best !== null ? money((int) $best['raised_minor'], $cur) : 'no confirmed donations'),
            ],
            'columns'     => $columns,
            'rows'        => $this->withCurrency($rows, $cur),
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'notes'       => ['Only staff-verified completed donations count as raised. Pending, rejected and refunded gifts are shown on their own columns.'],
        ];
    }

    private function runs(string $start, string $end): string
    {
        $fmt = static fn (string $d): string => $d !== '' ? gmdate('j M Y', (int) strtotime($d . ' UTC')) : '';
        if ($start === '' && $end === '') {
            return 'Open-ended';
        }

        return trim(($start !== '' ? $fmt($start) : 'Any time') . ' – ' . ($end !== '' ? $fmt($end) : 'ongoing'));
    }

    // ================================================================== 5. DONATION PURPOSES

    /** @param array<string,mixed> $f */
    private function purposes(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];
        $aw = self::AWAITING_SQL;
        $ok = "d.status = 'completed' AND d.verification_status = 'verified'";

        // Donation conditions live in the JOIN, so purposes without gifts in
        // the period still appear (with zeros).
        $params = [];
        $join = 'd.purpose_id = dp.id AND d.currency = ' . $this->p($params, $cur) . ' AND ' . $this->range('d.created_at', $f, $params);

        $where = '1 = 1';
        if (in_array($f['status'], ['active', 'hidden'], true)) {
            $where .= ' AND dp.status = ' . $this->p($params, $f['status']);
        }
        if ($f['q'] !== '') {
            $where .= ' AND dp.name LIKE ' . $this->p($params, $this->like($f['q']));
        }

        $columns = [
            ['key' => 'name', 'label' => 'Purpose', 'sort' => 'name', 'w' => 4],
            ['key' => 'campaign', 'label' => 'Campaign', 'sort' => 'campaign', 'w' => 2.8],
            ['key' => 'zakat', 'label' => 'Zakat eligible', 'w' => 1.8],
            ['key' => 'status', 'label' => 'Status', 'type' => 'status', 'sort' => 'status', 'w' => 1.5],
            ['key' => 'raised_minor', 'label' => 'Raised', 'type' => 'money', 'align' => 'right', 'sort' => 'raised_minor', 'w' => 2.2],
            ['key' => 'share_pct', 'label' => 'Share of verified', 'type' => 'percent', 'align' => 'right', 'w' => 1.8],
            ['key' => 'donations', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'sort' => 'donations', 'w' => 1.6],
            ['key' => 'donors', 'label' => 'Donors', 'type' => 'int', 'align' => 'right', 'sort' => 'donors', 'w' => 1.3],
            ['key' => 'average_minor', 'label' => 'Average', 'type' => 'money', 'align' => 'right', 'sort' => 'average_minor', 'w' => 2],
            ['key' => 'pending_minor', 'label' => 'Awaiting verification', 'type' => 'money', 'align' => 'right', 'sort' => 'pending_minor', 'w' => 2.4],
            ['key' => 'refunded_minor', 'label' => 'Refunded', 'type' => 'money', 'align' => 'right', 'sort' => 'refunded_minor', 'w' => 2],
        ];
        [$order, $sortKey, $dir] = $this->order($columns, $f, 'raised_minor', 'desc', 'id ASC');

        $inner = "SELECT dp.id, dp.name, dp.status, dp.is_zakat_eligible, COALESCE(c.title,'—') AS campaign,
                    COALESCE(SUM(CASE WHEN {$ok} THEN d.amount_minor END),0) AS raised_minor,
                    COALESCE(SUM(CASE WHEN {$ok} THEN 1 END),0) AS donations,
                    COUNT(DISTINCT CASE WHEN {$ok} THEN d.donor_id END) AS donors,
                    COALESCE(ROUND(AVG(CASE WHEN {$ok} THEN d.amount_minor END)),0) AS average_minor,
                    COALESCE(SUM(CASE WHEN {$aw} THEN d.amount_minor END),0) AS pending_minor,
                    COALESCE(SUM(CASE WHEN d.status = 'refunded' AND d.verification_status = 'verified' THEN d.amount_minor END),0) AS refunded_minor
                  FROM donation_purposes dp
                  LEFT JOIN campaigns c ON c.id = dp.campaign_id
                  LEFT JOIN donations d ON {$join}
                  WHERE {$where} GROUP BY dp.id, dp.name, dp.status, dp.is_zakat_eligible, c.title";

        $total = $this->db->int("SELECT COUNT(*) FROM ({$inner}) y", $params);
        $rows = $this->db->select("SELECT * FROM ({$inner}) z{$order}" . $this->limit($page, $perPage), $params);

        // Everything verified in the period, for the "share" column.
        $gp = ['cur' => $cur];
        $grand = (int) $this->db->int("SELECT COALESCE(SUM(d.amount_minor),0) FROM donations d WHERE {$ok} AND d.currency = :cur AND " . $this->range('d.created_at', $f, $gp), $gp);
        foreach ($rows as &$r) {
            $r['donations'] = (int) $r['donations'];
            $r['donors'] = (int) $r['donors'];
            $r['zakat'] = (int) $r['is_zakat_eligible'] === 1 ? 'Yes' : 'No';
            $r['share_pct'] = $this->ratio((int) $r['raised_minor'], $grand);
        }
        unset($r);

        $all = $this->db->selectOne(
            "SELECT COALESCE(SUM(raised_minor),0) AS raised, COALESCE(SUM(donations),0) AS n, COALESCE(SUM(pending_minor),0) AS pending,
                    COALESCE(SUM(CASE WHEN is_zakat_eligible = 1 THEN raised_minor END),0) AS zakat
             FROM ({$inner}) y",
            $params
        ) ?? [];
        $best = $this->db->selectOne("SELECT name, raised_minor FROM ({$inner}) y WHERE raised_minor > 0 ORDER BY raised_minor DESC, id ASC LIMIT 1", $params);

        $np = ['cur' => $cur];
        $noPurpose = (int) $this->db->int("SELECT COALESCE(SUM(d.amount_minor),0) FROM donations d WHERE {$ok} AND d.purpose_id IS NULL AND d.currency = :cur AND " . $this->range('d.created_at', $f, $np), $np);

        return [
            'description' => 'Where gifts went: money raised, donors and pending gifts for each donation purpose.',
            'basis'       => 'Donations dated by donation date and grouped by the purpose chosen on each donation. Only staff-verified completed gifts count as raised. Currency ' . $cur . '.',
            'summary'     => [
                $this->count('Purposes listed', $total),
                $this->money('Raised through purposes', (int) ($all['raised'] ?? 0), $cur, number_format((int) ($all['n'] ?? 0)) . ' verified donations'),
                $this->money('In zakat-eligible purposes', (int) ($all['zakat'] ?? 0), $cur, 'purposes marked zakat eligible'),
                $this->money('Not tied to a purpose', $noPurpose, $cur, 'general giving'),
                $this->money('Awaiting verification', (int) ($all['pending'] ?? 0), $cur),
                $this->text('Top purpose', $best !== null ? (string) $best['name'] : '—', $best !== null ? money((int) $best['raised_minor'], $cur) : 'no verified donations'),
            ],
            'columns'     => $columns,
            'rows'        => $this->withCurrency($rows, $cur),
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'notes'       => ['"Share of verified" compares each purpose with all verified giving in the period, including gifts that have no purpose. "Zakat eligible" shows whether the purpose is set up to receive zakat; it does not mean every gift to it was zakat.'],
        ];
    }

    // ================================================================== 6. REFERRERS

    /** @param array<string,mixed> $f */
    private function referrers(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];
        $aw = self::AWAITING_SQL;
        $ok = "d.status = 'completed' AND d.verification_status = 'verified'";

        $p = [];
        $where = "d.referral_enabled = 1 AND TRIM(COALESCE(d.referrer_name,'')) <> '' AND d.currency = " . $this->p($p, $cur) . ' AND ' . $this->range('d.created_at', $f, $p);
        if ($f['q'] !== '') {
            $like = $this->like($f['q']);
            $where .= ' AND (d.referrer_name LIKE ' . $this->p($p, $like) . ' OR d.referrer_phone LIKE ' . $this->p($p, $like) . ')';
        }

        $columns = [
            ['key' => 'referrer', 'label' => 'Referrer', 'sort' => 'referrer', 'w' => 4],
            ['key' => 'phone', 'label' => 'Phone', 'w' => 2.4],
            ['key' => 'referred', 'label' => 'Gifts referred', 'type' => 'int', 'align' => 'right', 'sort' => 'referred', 'w' => 1.8],
            ['key' => 'donations', 'label' => 'Verified gifts', 'type' => 'int', 'align' => 'right', 'sort' => 'donations', 'w' => 1.8],
            ['key' => 'donors', 'label' => 'Donors', 'type' => 'int', 'align' => 'right', 'sort' => 'donors', 'w' => 1.3],
            ['key' => 'raised_minor', 'label' => 'Raised', 'type' => 'money', 'align' => 'right', 'sort' => 'raised_minor', 'w' => 2.2],
            ['key' => 'average_minor', 'label' => 'Average', 'type' => 'money', 'align' => 'right', 'sort' => 'average_minor', 'w' => 2],
            ['key' => 'pending_minor', 'label' => 'Awaiting verification', 'type' => 'money', 'align' => 'right', 'sort' => 'pending_minor', 'w' => 2.4],
            ['key' => 'last_at', 'label' => 'Latest referral', 'type' => 'date', 'sort' => 'last_at', 'w' => 2],
        ];
        [$order, $sortKey, $dir] = $this->order($columns, $f, 'raised_minor', 'desc', 'referrer ASC');

        $inner = "SELECT TRIM(d.referrer_name) AS referrer, MAX(NULLIF(TRIM(d.referrer_phone),'')) AS phone, COUNT(*) AS referred,
                    COALESCE(SUM(CASE WHEN {$ok} THEN 1 END),0) AS donations,
                    COUNT(DISTINCT CASE WHEN {$ok} THEN d.donor_id END) AS donors,
                    COALESCE(SUM(CASE WHEN {$ok} THEN d.amount_minor END),0) AS raised_minor,
                    COALESCE(ROUND(AVG(CASE WHEN {$ok} THEN d.amount_minor END)),0) AS average_minor,
                    COALESCE(SUM(CASE WHEN {$aw} THEN d.amount_minor END),0) AS pending_minor,
                    DATE(MAX(d.created_at)) AS last_at
                  FROM donations d WHERE {$where} GROUP BY TRIM(d.referrer_name)";

        $total = $this->db->int("SELECT COUNT(*) FROM ({$inner}) y", $p);
        $rows = $this->db->select("SELECT * FROM ({$inner}) z{$order}" . $this->limit($page, $perPage), $p);

        $gp = ['cur' => $cur];
        $grand = (int) $this->db->int("SELECT COALESCE(SUM(d.amount_minor),0) FROM donations d WHERE {$ok} AND d.currency = :cur AND " . $this->range('d.created_at', $f, $gp), $gp);
        $all = $this->db->selectOne(
            "SELECT COALESCE(SUM(raised_minor),0) AS raised, COALESCE(SUM(donations),0) AS n, COALESCE(SUM(pending_minor),0) AS pending FROM ({$inner}) y",
            $p
        ) ?? [];
        $best = $this->db->selectOne("SELECT referrer, raised_minor FROM ({$inner}) y WHERE raised_minor > 0 ORDER BY raised_minor DESC, referrer ASC LIMIT 1", $p);

        return [
            'description' => 'Who referred donors, and what those gifts total.',
            'basis'       => 'Donations dated by donation date. Only gifts where the donor chose to name a referrer are included. Only staff-verified completed gifts count as raised. Currency ' . $cur . '.',
            'summary'     => [
                $this->count('Referrers', $total),
                $this->money('Raised through referrals', (int) ($all['raised'] ?? 0), $cur, number_format((int) ($all['n'] ?? 0)) . ' verified donations'),
                $this->pct('Share of all verified giving', $this->ratio((int) ($all['raised'] ?? 0), $grand), 'referred gifts against everything verified in the period'),
                $this->money('Awaiting verification', (int) ($all['pending'] ?? 0), $cur),
                $this->text('Top referrer', $best !== null ? (string) $best['referrer'] : '—', $best !== null ? money((int) $best['raised_minor'], $cur) : 'no verified referred gifts'),
            ],
            'columns'     => $columns,
            'rows'        => $this->withCurrency($rows, $cur),
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'notes'       => ['Referrer names are grouped as typed on the donation form, so two spellings of the same person appear as two rows.'],
        ];
    }

    // ================================================================== 7. DONORS

    /** @param array<string,mixed> $f */
    private function donors(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];
        $p = ['cur' => $cur];
        $where = "d.status = 'completed' AND d.verification_status = 'verified' AND d.currency = :cur AND " . $this->range('d.created_at', $f, $p);
        if ($f['gateway'] !== '') {
            $where .= ' AND d.gateway_id = (SELECT id FROM gateways WHERE code = ' . $this->p($p, $f['gateway']) . ')';
        }
        $having = [];
        if ($f['q'] !== '') {
            $like = $this->like($f['q']);
            $where .= ' AND (dn.name LIKE ' . $this->p($p, $like) . ' OR dn.email LIKE ' . $this->p($p, $like) . ' OR dn.phone LIKE ' . $this->p($p, $like) . ')';
        }
        if ($f['min_gifts'] !== '') {
            $having[] = 'COUNT(*) >= ' . (int) $f['min_gifts'];
        }
        $kindWhere = match ($f['donor_type']) {
            'new'       => "kind = 'New'",
            'returning' => "kind = 'Returning'",
            default     => '1 = 1',
        };
        $curLit = $this->quote($cur);

        $columns = [
            ['key' => 'name', 'label' => 'Donor', 'sort' => 'name', 'w' => 3.2],
            ['key' => 'email', 'label' => 'Email', 'sort' => 'email', 'w' => 4],
            ['key' => 'phone', 'label' => 'Phone', 'w' => 2.3],
            ['key' => 'recipients', 'label' => 'Acknowledgment recipients', 'w' => 3],
            ['key' => 'account', 'label' => 'Account', 'w' => 1.6],
            ['key' => 'kind', 'label' => 'Type', 'w' => 1.6],
            ['key' => 'gifts', 'label' => 'Gifts', 'type' => 'int', 'align' => 'right', 'sort' => 'gifts', 'w' => 1.1],
            ['key' => 'total_minor', 'label' => 'Total given', 'type' => 'money', 'align' => 'right', 'sort' => 'total_minor', 'w' => 2.2],
            ['key' => 'average_minor', 'label' => 'Average', 'type' => 'money', 'align' => 'right', 'sort' => 'average_minor', 'w' => 2],
            ['key' => 'largest_minor', 'label' => 'Largest', 'type' => 'money', 'align' => 'right', 'sort' => 'largest_minor', 'w' => 2],
            ['key' => 'anonymous', 'label' => 'Anon. gifts', 'type' => 'int', 'align' => 'right', 'sort' => 'anonymous', 'w' => 1.4],
            ['key' => 'first_in_range', 'label' => 'First gift', 'type' => 'date', 'sort' => 'first_in_range', 'w' => 1.9],
            ['key' => 'last_gift', 'label' => 'Last gift', 'type' => 'date', 'sort' => 'last_gift', 'w' => 1.9],
        ];
        [$order, $sortKey, $dir] = $this->order($columns, $f, 'total_minor', 'desc', 'id ASC');

        $inner = "SELECT dn.id, dn.name, dn.email, COALESCE(dn.phone,'') AS phone, IF(dn.user_id IS NULL, 'Guest', 'Registered') AS account,
                    GROUP_CONCAT(DISTINCT COALESCE(NULLIF(d.recipient_name,''), dn.name) ORDER BY d.recipient_name SEPARATOR ', ') AS recipients,
                    COUNT(*) AS gifts, SUM(d.amount_minor) AS total_minor, ROUND(AVG(d.amount_minor)) AS average_minor, MAX(d.amount_minor) AS largest_minor,
                    SUM(d.anonymous) AS anonymous, MIN(d.created_at) AS first_in_range, MAX(d.created_at) AS last_gift,
                    MIN(first.first_ever) AS first_ever
                  FROM donations d JOIN donors dn ON dn.id = d.donor_id
                  LEFT JOIN (SELECT donor_id, MIN(created_at) AS first_ever FROM donations WHERE status='completed' AND verification_status='verified' AND currency={$curLit} GROUP BY donor_id) first ON first.donor_id=dn.id
                  WHERE {$where} GROUP BY dn.id, dn.name, dn.email, dn.phone, dn.user_id"
              . ($having === [] ? '' : ' HAVING ' . implode(' AND ', $having));
        $outer = "SELECT x.*, IF(x.first_ever >= x.first_in_range, 'New', 'Returning') AS kind FROM ({$inner}) x";
        $filtered = "SELECT * FROM ({$outer}) w WHERE {$kindWhere}";

        $total = $this->db->int("SELECT COUNT(*) FROM ({$filtered}) y", $p);
        $rows = $this->db->select("SELECT * FROM ({$filtered}) z{$order}" . $this->limit($page, $perPage), $p);
        $a = $this->db->selectOne(
            "SELECT COUNT(*) AS donors, COALESCE(SUM(total_minor),0) AS total, COALESCE(SUM(gifts),0) AS gifts, COALESCE(MAX(total_minor),0) AS top,
                    SUM(kind = 'New') AS new_donors, SUM(kind = 'Returning') AS returning_donors, COALESCE(SUM(anonymous),0) AS anon
             FROM ({$filtered}) y",
            $p
        ) ?? [];

        return [
            'description' => 'Everyone with a confirmed donation in the period, and whether they are new or returning.',
            'basis'       => 'Donors with at least one completed donation dated in the range. "New" = their first ever completed donation falls in the range. Currency ' . $cur . '.',
            'summary'     => [
                $this->count('Donors', (int) ($a['donors'] ?? 0)),
                $this->count('New donors', (int) ($a['new_donors'] ?? 0)),
                $this->count('Returning donors', (int) ($a['returning_donors'] ?? 0)),
                $this->money('Total given', (int) ($a['total'] ?? 0), $cur, number_format((int) ($a['gifts'] ?? 0)) . ' donations'),
                $this->money('Average per donor', (int) ($a['donors'] ?? 0) > 0 ? $this->avg((int) $a['total'], (int) $a['donors']) : 0, $cur),
                $this->money('Top donor', (int) ($a['top'] ?? 0), $cur),
                $this->count('Anonymous gifts', (int) ($a['anon'] ?? 0), 'hidden from the public, visible to admins'),
            ],
            'columns'     => $columns,
            'rows'        => $this->withCurrency($rows, $cur),
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'notes'       => ['Donor details are visible to administrators only. A donor is identified by the email used at checkout; the same person using two emails appears as two donors.'],
        ];
    }

    private function quote(string $v): string
    {
        return $this->db->pdo()->quote($v);
    }

    // ================================================================== 8. DONATION VERIFICATION

    /** @param array<string,mixed> $f */
    private function verification(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];
        $waiting = self::AWAITING_SQL;
        $decided = "d.verification_status IN ('verified','rejected')";

        $p = [];
        $where = 'd.currency = ' . $this->p($p, $cur) . ' AND ' . $this->range('d.created_at', $f, $p);
        $where .= match ($f['review_status']) {
            'verified' => " AND d.verification_status = 'verified'",
            'rejected' => " AND d.verification_status = 'rejected'",
            'legacy'   => " AND d.verification_status = 'legacy'",
            'pending'  => " AND {$waiting}",
            default    => " AND d.verification_status <> 'legacy'", // legacy gifts were never reviewed
        };
        if ($f['q'] !== '') {
            $like = $this->like($f['q']);
            $where .= ' AND (d.public_reference LIKE ' . $this->p($p, $like) . ' OR dn.name LIKE ' . $this->p($p, $like)
                . ' OR dn.email LIKE ' . $this->p($p, $like) . ' OR d.referrer_name LIKE ' . $this->p($p, $like) . ')';
        }

        $from = "FROM donations d
                 LEFT JOIN donors dn ON dn.id = d.donor_id
                 LEFT JOIN donation_review_events de ON de.id = (SELECT MAX(e.id) FROM donation_review_events e WHERE e.donation_id = d.id AND e.decision IN ('approve','reject'))
                 LEFT JOIN users du ON du.id = COALESCE(de.actor_user_id, d.verified_by)";

        $columns = [
            ['key' => 'public_reference', 'label' => 'Donation', 'type' => 'code', 'sort' => 'd.public_reference', 'w' => 2.4, 'link' => 'donation'],
            ['key' => 'donor', 'label' => 'Donor', 'sort' => 'donor', 'w' => 2.8],
            ['key' => 'amount_minor', 'label' => 'Amount', 'type' => 'money', 'align' => 'right', 'sort' => 'd.amount_minor', 'w' => 2],
            ['key' => 'method', 'label' => 'Payment method', 'w' => 2],
            ['key' => 'payment_status', 'label' => 'Payment', 'type' => 'status', 'sort' => 'd.status', 'w' => 1.7],
            ['key' => 'verification_status', 'label' => 'Verification', 'type' => 'status', 'sort' => 'd.verification_status', 'w' => 2],
            ['key' => 'submitted_at', 'label' => 'Submitted', 'type' => 'datetime', 'sort' => 'submitted_at', 'w' => 2.2],
            ['key' => 'reviewer', 'label' => 'Reviewed by', 'sort' => 'reviewer', 'w' => 3],
            ['key' => 'decided_at', 'label' => 'Decided', 'type' => 'datetime', 'sort' => 'decided_at', 'w' => 2.2],
            ['key' => 'review_hours', 'label' => 'Turnaround', 'type' => 'hours', 'align' => 'right', 'sort' => 'review_hours', 'w' => 1.7],
            ['key' => 'waiting_hours', 'label' => 'Waiting', 'type' => 'hours', 'align' => 'right', 'sort' => 'waiting_hours', 'w' => 1.6],
            ['key' => 'note', 'label' => 'Reason / note', 'w' => 3.4],
        ];
        [$order, $sortKey, $dir] = $this->order($columns, $f, 'submitted_at', 'desc', 'd.id DESC');

        $select = "SELECT d.id, d.public_reference, d.currency, d.amount_minor, COALESCE(dn.name,'—') AS donor,
                    COALESCE(NULLIF(d.payment_method,''), d.source, '—') AS method, d.status AS payment_status, d.verification_status,
                    d.created_at AS submitted_at, du.email AS reviewer,
                    CASE WHEN {$decided} THEN de.created_at END AS decided_at,
                    CASE WHEN {$decided} AND de.created_at >= d.created_at THEN ROUND(TIMESTAMPDIFF(MINUTE, d.created_at, de.created_at) / 60, 1) END AS review_hours,
                    CASE WHEN {$waiting} THEN ROUND(TIMESTAMPDIFF(MINUTE, d.created_at, UTC_TIMESTAMP()) / 60, 1) END AS waiting_hours,
                    CASE WHEN d.verification_status = 'rejected' THEN NULLIF(de.comment,'') END AS note";

        $total = $this->db->int("SELECT COUNT(*) {$from} WHERE {$where}", $p);
        $rows = $this->db->select("{$select} {$from} WHERE {$where}{$order}" . $this->limit($page, $perPage), $p);

        $a = $this->db->selectOne(
            "SELECT COUNT(*) AS n, COALESCE(SUM(d.verification_status = 'verified'),0) AS verified, COALESCE(SUM(d.verification_status = 'rejected'),0) AS rejected,
                    COALESCE(SUM({$waiting}),0) AS waiting,
                    ROUND(AVG(CASE WHEN {$decided} AND de.created_at >= d.created_at THEN TIMESTAMPDIFF(MINUTE, d.created_at, de.created_at) / 60 END), 1) AS avg_hours,
                    ROUND(MAX(CASE WHEN {$waiting} THEN TIMESTAMPDIFF(MINUTE, d.created_at, UTC_TIMESTAMP()) / 60 END), 1) AS oldest
             {$from} WHERE {$where}",
            $p
        ) ?? [];
        $verified = (int) ($a['verified'] ?? 0);
        $rejected = (int) ($a['rejected'] ?? 0);

        $byReviewer = $this->db->select(
            "SELECT COALESCE(du.email, 'Not recorded') AS reviewer, COUNT(*) AS decisions,
                    COALESCE(SUM(d.verification_status = 'verified'),0) AS verified, COALESCE(SUM(d.verification_status = 'rejected'),0) AS rejected,
                    COALESCE(SUM(CASE WHEN d.verification_status = 'verified' THEN d.amount_minor END),0) AS verified_minor
             {$from} WHERE {$where} AND {$decided} GROUP BY du.email ORDER BY decisions DESC",
            $p
        );

        return [
            'description' => 'How donations were verified: where they stand, who decided and how long it took.',
            'basis'       => 'Dated by donation date. Legacy donations (from before review existed) are left out unless chosen. Reviewer and decision time come from the review log. Currency ' . $cur . '.',
            'summary'     => [
                $this->count('Donations in view', (int) ($a['n'] ?? 0)),
                $this->count('Awaiting verification', (int) ($a['waiting'] ?? 0), isset($a['oldest']) && $a['oldest'] !== null ? 'oldest waiting ' . number_format((float) $a['oldest'] / 24, 1) . ' days' : ''),
                $this->count('Verified', $verified),
                $this->count('Rejected', $rejected),
                $this->pct('Verification rate', $this->ratio($verified, $verified + $rejected), 'verified of verified + rejected'),
                ['label' => 'Average review time', 'value' => $a['avg_hours'] ?? null, 'kind' => 'hours', 'sub' => 'from submission to decision'],
            ],
            'columns'     => $columns,
            'rows'        => $rows,
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'extra'       => [['title' => 'Decisions by reviewer', 'columns' => [
                ['key' => 'reviewer', 'label' => 'Reviewer', 'w' => 5], ['key' => 'decisions', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'w' => 1.6],
                ['key' => 'verified', 'label' => 'Verified', 'type' => 'int', 'align' => 'right', 'w' => 1.5], ['key' => 'rejected', 'label' => 'Rejected', 'type' => 'int', 'align' => 'right', 'w' => 1.5],
                ['key' => 'verified_minor', 'label' => 'Verified amount', 'type' => 'money', 'align' => 'right', 'w' => 2.4],
            ], 'rows' => $this->withCurrency($byReviewer, $cur)]],
            'notes'       => ['Donations verified before review logging began show "—" for reviewer and times. Only the latest decision per donation is shown.'],
        ];
    }

    // ================================================================== 9. RECONCILIATION

    private const ISSUES = [
        'status_mismatch'   => 'Status mismatch',
        'unbacked_donation' => 'Confirmed without payment',
        'amount_mismatch'   => 'Amount mismatch',
        'no_bank_reference' => 'No bank reference',
        'stale'             => 'Stuck unresolved',
    ];

    /** @param array<string,mixed> $f */
    private function reconciliation(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];
        if ($f['issue'] === 'offline_transfers') return $this->bankTransfers($f,$page,$perPage);
        $stale = self::STALE_MINUTES;
        $p = ['cur' => $cur];
        $where = 'pt.currency = :cur AND ' . $this->range('pt.created_at', $f, $p);
        if ($f['gateway'] !== '') {
            $where .= ' AND g.code = ' . $this->p($p, $f['gateway']);
        }
        if ($f['q'] !== '') {
            $like = $this->like($f['q']);
            $where .= ' AND (d.public_reference LIKE ' . $this->p($p, $like) . ' OR pt.merchant_order_id LIKE ' . $this->p($p, $like)
                . ' OR pt.provider_transaction_id LIKE ' . $this->p($p, $like) . ')';
        }

        // The first matching rule decides the issue; NULL means the records agree.
        $issueSql = "CASE
            WHEN pt.status IN ('completed','refunded') AND d.status NOT IN ('completed','refunded') THEN 'status_mismatch'
            WHEN d.status = 'completed' AND d.verification_status = 'verified' AND pt.status NOT IN ('completed','refunded')
                 AND NOT EXISTS (SELECT 1 FROM payment_transactions p2 WHERE p2.donation_id = d.id AND p2.status IN ('completed','refunded')) THEN 'unbacked_donation'
            WHEN pt.status IN ('completed','refunded') AND pt.amount_minor <> d.amount_minor THEN 'amount_mismatch'
            WHEN pt.status IN ('completed','refunded') AND (pt.provider_transaction_id IS NULL OR pt.provider_transaction_id = '') THEN 'no_bank_reference'
            WHEN pt.status IN ('pending','processing') AND pt.created_at < (UTC_TIMESTAMP() - INTERVAL {$stale} MINUTE) THEN 'stale'
            ELSE NULL END";

        $inner = "SELECT pt.id, pt.created_at, pt.merchant_order_id, pt.provider_transaction_id, pt.status AS txn_status, pt.amount_minor AS txn_amount,
                    pt.last_reconciled_at, d.id AS donation_id, d.public_reference, d.status AS donation_status, d.amount_minor AS donation_amount,
                    g.name AS gateway, ROUND(TIMESTAMPDIFF(MINUTE, pt.created_at, UTC_TIMESTAMP()) / 60, 1) AS age_hours, {$issueSql} AS issue
                  FROM payment_transactions pt JOIN donations d ON d.id = pt.donation_id LEFT JOIN gateways g ON g.id = pt.gateway_id
                  WHERE {$where}";

        $show = $f['issue'];
        $filter = match (true) {
            $show === 'all'                  => '1 = 1',
            isset(self::ISSUES[$show])       => 'issue = ' . $this->quote($show),
            default                          => 'issue IS NOT NULL',
        };

        $columns = [
            ['key' => 'issue_label', 'label' => 'Issue', 'w' => 2.8],
            ['key' => 'created_at', 'label' => 'Created', 'type' => 'datetime', 'sort' => 'created_at', 'w' => 2.2],
            ['key' => 'public_reference', 'label' => 'Donation', 'type' => 'code', 'sort' => 'public_reference', 'w' => 2.4, 'link' => 'donation'],
            ['key' => 'gateway', 'label' => 'Gateway', 'sort' => 'gateway', 'w' => 1.8],
            ['key' => 'provider_transaction_id', 'label' => 'Bank transaction', 'type' => 'code', 'w' => 2.4],
            ['key' => 'txn_status', 'label' => 'Payment', 'type' => 'status', 'sort' => 'txn_status', 'w' => 1.6],
            ['key' => 'donation_status', 'label' => 'Donation', 'type' => 'status', 'sort' => 'donation_status', 'w' => 1.6],
            ['key' => 'txn_amount', 'label' => 'Payment amount', 'type' => 'money', 'align' => 'right', 'sort' => 'txn_amount', 'w' => 2.1],
            ['key' => 'donation_amount', 'label' => 'Donation amount', 'type' => 'money', 'align' => 'right', 'sort' => 'donation_amount', 'w' => 2.1],
            ['key' => 'age_hours', 'label' => 'Age', 'type' => 'hours', 'align' => 'right', 'sort' => 'age_hours', 'w' => 1.4],
            ['key' => 'last_reconciled_at', 'label' => 'Last reconciled', 'type' => 'datetime', 'sort' => 'last_reconciled_at', 'w' => 2.2],
        ];
        [$order, $sortKey, $dir] = $this->order($columns, $f, 'created_at', 'desc', 'id DESC');

        $total = $this->db->int("SELECT COUNT(*) FROM ({$inner}) i WHERE {$filter}", $p);
        $rows = $this->db->select("SELECT * FROM ({$inner}) i WHERE {$filter}{$order}" . $this->limit($page, $perPage), $p);
        foreach ($rows as &$r) {
            $r['issue_label'] = isset(self::ISSUES[(string) ($r['issue'] ?? '')]) ? self::ISSUES[(string) $r['issue']] : 'Matched';
        }
        unset($r);

        $counts = [];
        foreach ($this->db->select("SELECT issue, COUNT(*) AS c FROM ({$inner}) i GROUP BY issue", $p) as $r) {
            $counts[(string) ($r['issue'] ?? '')] = (int) $r['c'];
        }
        $reviewed = array_sum($counts);
        $exceptions = $reviewed - ($counts[''] ?? 0);

        // Donations that never produced a payment attempt (checkout abandoned
        // before the gateway) and confirmed donations without a verified payment.
        $dp = ['cur' => $cur];
        $dwhere = 'd.currency = :cur AND ' . $this->range('d.created_at', $f, $dp);
        $noAttempt = $this->db->selectOne("SELECT COUNT(*) AS c FROM donations d WHERE {$dwhere} AND d.status IN ('pending','processing')
                                           AND d.created_at < (UTC_TIMESTAMP() - INTERVAL {$stale} MINUTE) AND NOT EXISTS (SELECT 1 FROM payment_transactions x WHERE x.donation_id = d.id)", $dp) ?? [];
        $unbacked = $this->db->selectOne("SELECT COUNT(*) AS c, COALESCE(SUM(d.amount_minor),0) AS s FROM donations d WHERE {$dwhere} AND d.status = 'completed' AND d.verification_status = 'verified' AND d.gateway_id IS NOT NULL
                                          AND NOT EXISTS (SELECT 1 FROM payment_transactions x WHERE x.donation_id = d.id AND x.status IN ('completed','refunded'))", $dp) ?? [];

        // Ledger check per gateway: confirmed donations versus completed payments.
        $ledger = $this->db->select(
            "SELECT COALESCE(g.name,'Unknown') AS gateway, COUNT(*) AS donations, COALESCE(SUM(d.amount_minor),0) AS donation_minor,
                    SUM(EXISTS (SELECT 1 FROM payment_transactions x WHERE x.donation_id = d.id AND x.status IN ('completed','refunded'))) AS backed,
                    COALESCE(SUM(CASE WHEN EXISTS (SELECT 1 FROM payment_transactions x WHERE x.donation_id = d.id AND x.status IN ('completed','refunded')) THEN d.amount_minor END),0) AS backed_minor
             FROM donations d LEFT JOIN gateways g ON g.id = d.gateway_id WHERE {$dwhere} AND d.status = 'completed' AND d.verification_status = 'verified' AND d.gateway_id IS NOT NULL GROUP BY d.gateway_id, g.name",
            $dp
        );
        foreach ($ledger as &$l) {
            $l['difference'] = (int) $l['donation_minor'] - (int) $l['backed_minor'];
        }
        unset($l);

        return [
            'description' => 'Checks that every confirmed donation has a matching completed bank payment, and flags the rest.',
            'basis'       => 'Payment attempts dated by creation time. Currency ' . $cur . '. "Stuck" means pending or processing for more than ' . $stale . ' minutes.',
            'summary'     => [
                $this->count('Payments reviewed', $reviewed),
                $this->count('Exceptions found', $exceptions, 'records that do not agree'),
                $this->count('Status mismatches', $counts['status_mismatch'] ?? 0, 'bank says paid, donation does not'),
                $this->count('Confirmed without payment', (int) ($unbacked['c'] ?? 0), money((int) ($unbacked['s'] ?? 0), $cur) . ' confirmed, no verified payment'),
                $this->count('Amount mismatches', $counts['amount_mismatch'] ?? 0),
                $this->count('No bank reference', $counts['no_bank_reference'] ?? 0, 'completed but no bank transaction id'),
                $this->count('Stuck unresolved', $counts['stale'] ?? 0),
                $this->count('Donations never sent to a bank', (int) ($noAttempt['c'] ?? 0), 'pending, no payment attempt'),
            ],
            'columns'     => $columns,
            'rows'        => $this->withCurrency($rows, $cur),
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'extra'       => [['title' => 'Confirmed donations versus completed payments', 'columns' => [
                ['key' => 'gateway', 'label' => 'Gateway', 'w' => 3], ['key' => 'donations', 'label' => 'Confirmed donations', 'type' => 'int', 'align' => 'right', 'w' => 2],
                ['key' => 'donation_minor', 'label' => 'Donation total', 'type' => 'money', 'align' => 'right', 'w' => 2.2], ['key' => 'backed', 'label' => 'With verified payment', 'type' => 'int', 'align' => 'right', 'w' => 2.2],
                ['key' => 'backed_minor', 'label' => 'Verified total', 'type' => 'money', 'align' => 'right', 'w' => 2.2], ['key' => 'difference', 'label' => 'Difference', 'type' => 'money', 'align' => 'right', 'w' => 2],
            ], 'rows' => $this->withCurrency($ledger, $cur)]],
            'notes'       => [
                'Offline transfer submissions are stored as donations without gateway attempts. Choose Bank transfers in the Show filter to inspect their references and staff verification. A donor-supplied reference or a verified donation is not evidence of reconciliation with a bank statement; no reconciliation status is invented.',
                'The default view lists exceptions only. Choose "All transactions" in the Show filter to include records that match.',
            ],
        ];
    }

    /** Offline transfer applications: no bank statement or settlement ledger exists. */
    private function bankTransfers(array $f, int $page, int $perPage): array
    {
        $p=['cur'=>$f['currency']];
        $where="d.currency=:cur AND d.source IN ('bank_transfer','online_transfer') AND " . $this->range('COALESCE(d.transaction_at,d.created_at)',$f,$p);
        if ($f['gateway'] !== '') {
            // Offline transfers have no gateway; a gateway filter yields no rows.
            $where .= ' AND 1=0';
        }
        if ($f['q'] !== '') {
            $like=$this->quote($this->like($f['q']));
            $where .= " AND (d.bank_reference LIKE {$like} OR d.public_reference LIKE {$like} OR d.receipt_number LIKE {$like} OR dn.name LIKE {$like} OR dn.email LIKE {$like})";
        }
        $from='FROM donations d LEFT JOIN donors dn ON dn.id=d.donor_id LEFT JOIN users u ON u.id=d.verified_by';
        $columns=[
            ['key'=>'bank_reference','label'=>'Bank reference','sort'=>'d.bank_reference','type'=>'code','w'=>2.5],
            ['key'=>'donor','label'=>'Donor','sort'=>'dn.name','w'=>2.3],
            ['key'=>'receipt_number','label'=>'Receipt number','type'=>'code','w'=>2.2],
            ['key'=>'transaction_at','label'=>'Transaction date','type'=>'datetime','sort'=>'d.transaction_at','w'=>2.3],
            ['key'=>'amount_minor','label'=>'Amount','type'=>'money','sort'=>'d.amount_minor','w'=>2.2],
            ['key'=>'verification_status','label'=>'Verification','type'=>'status','sort'=>'d.verification_status','w'=>2],
            ['key'=>'verified_by','label'=>'Verified by','w'=>2.3],
            ['key'=>'reconciliation_status','label'=>'Reconciliation','w'=>2.5],
        ];
        [$order,$sort,$dir]=$this->order($columns,$f,'transaction_at','desc','d.id DESC');
        $total=$this->db->int("SELECT COUNT(*) {$from} WHERE {$where}",$p);
        $rows=$this->db->select("SELECT d.id,d.bank_reference,d.receipt_number,COALESCE(d.transaction_at,d.created_at) AS transaction_at,d.amount_minor,d.verification_status,
            COALESCE(dn.name,'—') AS donor, u.email AS verified_by, 'Not recorded' AS reconciliation_status
            {$from} WHERE {$where}{$order}" . $this->limit($page,$perPage),$p);
        $stats=$this->db->selectOne("SELECT COUNT(*) AS n,COALESCE(SUM(d.amount_minor),0) AS submitted_minor,
            COALESCE(SUM(CASE WHEN d.verification_status='verified' AND d.status IN ('completed','refunded') THEN d.amount_minor END),0) AS verified_minor
            {$from} WHERE {$where}",$p) ?? [];
        return ['description'=>'Offline bank transfers and their real donor-submitted references; bank statement reconciliation is not captured.',
            'basis'=>'Dated by entered transaction date, or submission date when no transaction date exists. No bank statement settlement or reconciliation flag exists.',
            'summary'=>[$this->count('Transfer submissions',(int)($stats['n']??0)),
                $this->money('Submitted amount',(int)($stats['submitted_minor']??0),$f['currency']),
                $this->money('Verified amount',(int)($stats['verified_minor']??0),$f['currency'])],
            'columns'=>$columns,'rows'=>$this->withCurrency($rows,$f['currency']),'total'=>$total,'sort'=>$sort,'dir'=>$dir,
            'notes'=>['Not recorded means no bank-statement reconciliation data exists in this application; it does not imply that a transfer is matched or unmatched. Verification is an independent staff review.']];
    }

    // ================================================================== 10. REFUNDS & FAILED

    /** @param array<string,mixed> $f */
    private function refunds(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];
        if ($f['type'] === 'partial_refund') return $this->partialRefunds($f,$page,$perPage);
        $p = ['cur' => $cur];
        $eventExpr = "CASE WHEN d.status = 'refunded' THEN COALESCE(rf.completed_at, d.updated_at) ELSE d.created_at END";
        $types = ['refunded', 'failed', 'cancelled', 'abandoned'];
        $where = 'd.currency = :cur AND ' . $this->range($eventExpr, $f, $p);
        if (in_array($f['type'], $types, true)) {
            $where .= ' AND d.status = ' . $this->p($p, $f['type']);
        } else {
            $where .= " AND d.status IN ('refunded','failed','cancelled','abandoned')";
        }
        if ($f['gateway'] !== '') {
            $where .= ' AND g.code = ' . $this->p($p, $f['gateway']);
        }
        if ($f['q'] !== '') {
            $like = $this->like($f['q']);
            $where .= ' AND (d.public_reference LIKE ' . $this->p($p, $like) . ' OR dn.name LIKE ' . $this->p($p, $like) . ' OR dn.email LIKE ' . $this->p($p, $like) . ')';
        }
        $from = "FROM donations d
                 LEFT JOIN gateways g ON g.id = d.gateway_id LEFT JOIN donation_purposes dpu ON dpu.id = d.purpose_id LEFT JOIN donors dn ON dn.id = d.donor_id
                 LEFT JOIN payment_transactions lt ON lt.id = (SELECT MAX(t.id) FROM payment_transactions t WHERE t.donation_id = d.id)
                 LEFT JOIN payment_refunds rf ON rf.id = (SELECT MAX(r.id) FROM payment_refunds r WHERE r.donation_id = d.id)
                 LEFT JOIN payment_refunds rc ON rc.id = (SELECT MAX(r.id) FROM payment_refunds r WHERE r.donation_id = d.id AND r.status='completed')
                 LEFT JOIN users ru ON ru.id = rf.requested_by";

        $columns = [
            ['key' => 'event_at', 'label' => 'Date', 'type' => 'datetime', 'sort' => 'event_at', 'w' => 2.2],
            ['key' => 'transaction_id', 'label' => 'Transaction ID', 'type' => 'code', 'w' => 2.2],
            ['key' => 'receipt_number', 'label' => 'Receipt number', 'type' => 'code', 'w' => 2.2],
            ['key' => 'public_reference', 'label' => 'Donation', 'type' => 'code', 'sort' => 'public_reference', 'w' => 2.4, 'link' => 'donation'],
            ['key' => 'donor', 'label' => 'Donor', 'sort' => 'donor', 'w' => 2.6],
            ['key' => 'purpose', 'label' => 'Purpose', 'w' => 3],
            ['key' => 'gateway', 'label' => 'Gateway', 'sort' => 'gateway', 'w' => 1.7],
            ['key' => 'status', 'label' => 'Outcome', 'type' => 'status', 'sort' => 'status', 'w' => 1.6],
            ['key' => 'amount_minor', 'label' => 'Original amount', 'type' => 'money', 'align' => 'right', 'sort' => 'amount_minor', 'w' => 2],
            ['key' => 'refund_amount_minor', 'label' => 'Refund amount', 'type' => 'money', 'w' => 2.2],
            ['key' => 'refund_date', 'label' => 'Refund date', 'type' => 'datetime', 'w' => 2.2],
            ['key' => 'reason', 'label' => 'Bank response / reason', 'w' => 3.4],
            ['key' => 'refund_status', 'label' => 'Refund', 'type' => 'status', 'w' => 1.5],
            ['key' => 'refunded_by', 'label' => 'Refunded by', 'w' => 2.6],
        ];
        [$order, $sortKey, $dir] = $this->order($columns, $f, 'event_at', 'desc', 'id DESC');

        $select = "SELECT d.id, d.public_reference, d.receipt_number, lt.provider_transaction_id AS transaction_id,
                    rc.amount_minor AS refund_amount_minor, rc.completed_at AS refund_date, d.status, d.amount_minor, d.anonymous, {$eventExpr} AS event_at,
                    CASE WHEN d.anonymous = 1 THEN 'Anonymous' ELSE COALESCE(dn.name, '—') END AS donor,
                    COALESCE(dpu.name, 'General fund') AS purpose, COALESCE(g.name, 'Unknown') AS gateway,
                    TRIM(CONCAT(COALESCE(lt.provider_response_code,''), ' ', COALESCE(lt.provider_response_description, rf.provider_response_description, ''))) AS reason,
                    rf.status AS refund_status, ru.email AS refunded_by";
        $wrap = "SELECT * FROM ({$select} {$from} WHERE {$where}) w";

        $total = $this->db->int("SELECT COUNT(*) {$from} WHERE {$where}", $p);
        $rows = $this->db->select("{$wrap}{$order}" . $this->limit($page, $perPage), $p);

        $a = [];
        foreach ($this->db->select("SELECT d.status, COUNT(*) AS c, COALESCE(SUM(d.amount_minor),0) AS s {$from} WHERE {$where} GROUP BY d.status", $p) as $r) {
            $a[(string) $r['status']] = $r;
        }
        $n = static fn (string $k): int => (int) ($a[$k]['c'] ?? 0);
        $m = static fn (string $k): int => (int) ($a[$k]['s'] ?? 0);

        // Outcome rate for the same dates, over every donation that finished.
        $rp = ['cur' => $cur];
        $rate = $this->db->selectOne("SELECT SUM(d.status IN ('completed','refunded') AND d.verification_status = 'verified') AS ok, SUM(d.status IN ('failed','cancelled','abandoned')) AS bad
                                      FROM donations d WHERE d.currency = :cur AND " . $this->range('d.created_at', $f, $rp), $rp) ?? [];

        // A partial refund leaves the donation status completed. It must not be
        // lost merely because this legacy outcome list groups by donation status.
        $partial = $this->partialRefunds($f,1,10);
        $reasons = $this->db->select(
            "SELECT COALESCE(NULLIF(TRIM(CONCAT(COALESCE(lt.provider_response_code,''), ' ', COALESCE(lt.provider_response_description,''))), ''), 'No reason recorded') AS reason,
                    COUNT(*) AS c, COALESCE(SUM(d.amount_minor),0) AS s
             {$from} WHERE {$where} AND d.status IN ('failed','cancelled','abandoned') GROUP BY reason ORDER BY c DESC LIMIT 10",
            $p
        );
        $rfp = ['cur' => $cur];
        $refundTries = $this->db->select(
            "SELECT r.created_at, d.public_reference, r.amount_minor, r.status, TRIM(CONCAT(COALESCE(r.provider_response_code,''), ' ', COALESCE(r.provider_response_description,''))) AS response
             FROM payment_refunds r JOIN donations d ON d.id = r.donation_id
             WHERE r.status IN ('failed','rejected') AND r.currency = :cur AND " . $this->range('r.created_at', $f, $rfp) . ' ORDER BY r.id DESC LIMIT 50',
            $rfp
        );

        return [
            'description' => 'Refunded and unsuccessful donations, with the bank\'s reason where it gave one.',
            'basis'       => 'Refunds are dated by the day the refund completed; failed, cancelled and abandoned donations by donation date. Currency ' . $cur . '. Anonymous donors are shown as "Anonymous".',
            'summary'     => [
                $this->count('Refunded donations', $n('refunded')),
                $this->count('Partial refunds', (int)$partial['total']),
                $this->money('Refunded amount', (int)($partial['allRefundedMinor'] ?? 0), $cur),
                $this->count('Failed', $n('failed'), money($m('failed'), $cur) . ' not collected'),
                $this->count('Cancelled', $n('cancelled'), money($m('cancelled'), $cur)),
                $this->count('Abandoned', $n('abandoned'), money($m('abandoned'), $cur)),
                $this->pct('Unsuccessful rate', $this->ratio((int) ($rate['bad'] ?? 0), (int) ($rate['ok'] ?? 0) + (int) ($rate['bad'] ?? 0)), 'failed + cancelled + abandoned, of finished donations'),
            ],
            'columns'     => $columns,
            'rows'        => $this->withCurrency($rows, $cur),
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'extra'       => [
                ['title' => 'Most common reasons for unsuccessful payments', 'columns' => [
                    ['key' => 'reason', 'label' => 'Bank response', 'w' => 6], ['key' => 'c', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'w' => 1.6],
                    ['key' => 's', 'label' => 'Amount', 'type' => 'money', 'align' => 'right', 'w' => 2.2],
                ], 'rows' => $this->withCurrency($reasons, $cur)],
                ['title' => 'Refund requests that did not complete', 'columns' => [
                    ['key' => 'created_at', 'label' => 'Requested', 'type' => 'datetime', 'w' => 2.2], ['key' => 'public_reference', 'label' => 'Donation', 'type' => 'code', 'w' => 2.4],
                    ['key' => 'amount_minor', 'label' => 'Requested refund amount', 'type' => 'money', 'align' => 'right', 'w' => 2], ['key' => 'status', 'label' => 'Status', 'type' => 'status', 'w' => 1.5],
                    ['key' => 'response', 'label' => 'Bank response', 'w' => 4],
                ], 'rows' => $this->withCurrency($refundTries, $cur)],
                ['title' => 'Completed partial refunds (latest 10; choose Partial refunds for search and pagination)', 'columns' => $partial['columns'], 'rows' => $partial['rows']],
            ],
            'notes'       => ['The system records one bank answer per payment attempt; the reason shown is the latest attempt for that donation.'],
        ];
    }

    /** Refund transactions on donations that remain completed (partial refunds). */
    private function partialRefunds(array $f,int $page,int $perPage): array
    {
        $p=['cur'=>$f['currency']];
        $where="r.status='completed' AND r.currency=:cur AND d.status='completed' AND d.verification_status='verified' AND " . $this->range('COALESCE(r.completed_at,r.created_at)',$f,$p);
        if ($f['gateway'] !== '') $where .= ' AND g.code=' . $this->p($p,$f['gateway']);
        if ($f['q'] !== '') {
            $like=$this->quote($this->like($f['q']));
            $where .= " AND (d.public_reference LIKE {$like} OR dn.name LIKE {$like} OR dn.email LIKE {$like})";
        }
        $from='FROM payment_refunds r JOIN donations d ON d.id=r.donation_id LEFT JOIN donors dn ON dn.id=d.donor_id LEFT JOIN gateways g ON g.id=r.gateway_id LEFT JOIN payment_transactions pt ON pt.id=r.payment_transaction_id';
        $columns=[
            ['key'=>'transaction_id','label'=>'Transaction ID','type'=>'code','w'=>2.2],
            ['key'=>'receipt_number','label'=>'Receipt number','type'=>'code','w'=>2],
            ['key'=>'donor','label'=>'Donor','w'=>2.2],
            ['key'=>'original_minor','label'=>'Original amount','type'=>'money','w'=>2],
            ['key'=>'refund_amount_minor','label'=>'Refund amount','type'=>'money','sort'=>'r.amount_minor','w'=>2],
            ['key'=>'refund_date','label'=>'Refund date','type'=>'datetime','sort'=>'r.completed_at','w'=>2.1],
            ['key'=>'status','label'=>'Donation status','type'=>'status','w'=>1.8],
            ['key'=>'refund_status','label'=>'Refund status','type'=>'status','w'=>1.8],
        ];
        [$order,$sort,$dir]=$this->order($columns,$f,'refund_date','desc','r.id DESC');
        $total=$this->db->int("SELECT COUNT(*) {$from} WHERE {$where}",$p);
        $rows=$this->db->select("SELECT r.id,pt.provider_transaction_id AS transaction_id,d.receipt_number,COALESCE(dn.name,'—') AS donor,
            d.amount_minor AS original_minor,r.amount_minor AS refund_amount_minor,r.completed_at AS refund_date,d.status,r.status AS refund_status
            {$from} WHERE {$where}{$order}" . $this->limit($page,$perPage),$p);
        $allP=['cur'=>$f['currency']];
        $allWhere="r.status='completed' AND r.currency=:cur AND d.verification_status='verified' AND " . $this->range('COALESCE(r.completed_at,r.created_at)',$f,$allP);
        if ($f['gateway'] !== '') $allWhere .= ' AND g.code=' . $this->p($allP,$f['gateway']);
        if ($f['q'] !== '') {
            $like=$this->quote($this->like($f['q']));
            $allWhere .= " AND (d.public_reference LIKE {$like} OR dn.name LIKE {$like} OR dn.email LIKE {$like})";
        }
        $allRefunded=(int)$this->db->scalar("SELECT COALESCE(SUM(r.amount_minor),0) {$from} WHERE {$allWhere}",$allP);
        return ['description'=>'Completed partial refunds on donations still marked completed.',
            'basis'=>'One row per actual completed refund transaction, dated by refund completion. Only staff-verified donations contribute to financial refund totals.',
            'summary'=>[$this->count('Partial refunds',$total),$this->money('Partial refund amount',(int)$this->db->scalar("SELECT COALESCE(SUM(r.amount_minor),0) {$from} WHERE {$where}",$p),$f['currency']),$this->money('All completed refunds',$allRefunded,$f['currency'])],
            'columns'=>$columns,'rows'=>$this->withCurrency($rows,$f['currency']),'total'=>$total,'sort'=>$sort,'dir'=>$dir,'allRefundedMinor'=>$allRefunded,
            'notes'=>['A partial refund does not change a donation from completed to refunded. Each completed refund transaction is counted once; pending or failed refund requests do not reduce net donations.']];
    }

    // ================================================================== 11. FINANCIAL SUMMARY

    /** @param array<string,mixed> $f */
    private function financial(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];
        $fmt = ['day' => '%Y-%m-%d', 'month' => '%Y-%m', 'year' => '%Y'][$f['group']];
        $cash = 'COALESCE(d.completed_at, d.created_at)';
        $search = $f['q'] === '' ? '' : (' AND (d.public_reference LIKE ' . $this->quote($this->like($f['q'])) . ' OR EXISTS (SELECT 1 FROM donors dn WHERE dn.id=d.donor_id AND (dn.name LIKE ' . $this->quote($this->like($f['q'])) . ' OR dn.email LIKE ' . $this->quote($this->like($f['q'])) . ')))');

        $params = ['cur' => $cur];
        $gWhere = "d.currency = :cur AND d.status IN ('completed','refunded') AND d.verification_status = 'verified' AND " . $this->range($cash, $f, $params) . $search;
        $gJoin = '';
        if ($f['gateway'] !== '') {
            $gWhere .= ' AND d.gateway_id = (SELECT id FROM gateways WHERE code = ' . $this->p($params, $f['gateway']) . ')';
        }
        $gross = $this->db->select(
            "SELECT DATE_FORMAT({$cash}, '{$fmt}') AS period, COUNT(*) AS donations, COALESCE(SUM(d.amount_minor),0) AS gross_minor FROM donations d {$gJoin} WHERE {$gWhere} GROUP BY period",
            $params
        );

        $rp = ['cur' => $cur];
        $rWhere = "r.status = 'completed' AND r.currency = :cur AND " . $this->range('COALESCE(r.completed_at, r.created_at)', $f, $rp) . $search;
        if ($f['gateway'] !== '') {
            $rWhere .= ' AND r.gateway_id = (SELECT id FROM gateways WHERE code = ' . $this->p($rp, $f['gateway']) . ')';
        }
        $refunds = $this->db->select(
            "SELECT DATE_FORMAT(COALESCE(r.completed_at, r.created_at), '{$fmt}') AS period, COUNT(*) AS refunds, COALESCE(SUM(r.amount_minor),0) AS refunded_minor FROM payment_refunds r JOIN donations d ON d.id = r.donation_id WHERE d.verification_status = 'verified' AND {$rWhere} GROUP BY period",
            $rp
        );

        $by = [];
        foreach ($gross as $r) {
            $by[(string) $r['period']] = ['period' => (string) $r['period'], 'donations' => (int) $r['donations'], 'gross_minor' => (int) $r['gross_minor'], 'refunds' => 0, 'refunded_minor' => 0];
        }
        foreach ($refunds as $r) {
            $k = (string) $r['period'];
            $by[$k] ??= ['period' => $k, 'donations' => 0, 'gross_minor' => 0, 'refunds' => 0, 'refunded_minor' => 0];
            $by[$k]['refunds'] = (int) $r['refunds'];
            $by[$k]['refunded_minor'] = (int) $r['refunded_minor'];
        }
        $all = [];
        foreach ($by as $row) {
            $row['net_minor'] = $row['gross_minor'] - $row['refunded_minor'];
            $row['average_minor'] = $row['donations'] > 0 ? $this->avg($row['gross_minor'], $row['donations']) : 0;
            $all[] = $row;
        }

        $columns = [
            ['key' => 'period', 'label' => ucfirst($f['group']), 'sort' => 'period', 'w' => 2],
            ['key' => 'donations', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'sort' => 'donations', 'w' => 1.5],
            ['key' => 'gross_minor', 'label' => 'Gross received', 'type' => 'money', 'align' => 'right', 'sort' => 'gross_minor', 'w' => 2.4],
            ['key' => 'refunds', 'label' => 'Refunds', 'type' => 'int', 'align' => 'right', 'sort' => 'refunds', 'w' => 1.3],
            ['key' => 'refunded_minor', 'label' => 'Refunded', 'type' => 'money', 'align' => 'right', 'sort' => 'refunded_minor', 'w' => 2.4],
            ['key' => 'net_minor', 'label' => 'Net', 'type' => 'money', 'align' => 'right', 'sort' => 'net_minor', 'w' => 2.4],
            ['key' => 'average_minor', 'label' => 'Average gift', 'type' => 'money', 'align' => 'right', 'sort' => 'average_minor', 'w' => 2.2],
        ];
        [, $sortKey, $dir] = $this->order($columns, $f, 'period', 'desc', 'id');
        usort($all, static function (array $a, array $b) use ($sortKey, $dir): int {
            $c = $a[$sortKey] <=> $b[$sortKey];

            return $dir === 'asc' ? $c : -$c;
        });
        $total = count($all);
        $rows = array_slice($all, ($page - 1) * $perPage, $perPage);

        $gross = (int) array_sum(array_column($all, 'gross_minor'));
        $refunded = (int) array_sum(array_column($all, 'refunded_minor'));
        $count = (int) array_sum(array_column($all, 'donations'));

        // Money still in the pipeline or never collected (not part of net).
        $pp = ['cur' => $cur];
        $pend = $this->db->selectOne("SELECT COUNT(*) AS c, COALESCE(SUM(d.amount_minor),0) AS s FROM donations d WHERE d.currency = :cur AND d.status IN ('pending','processing') AND d.verification_status<>'rejected' AND " . $this->range('d.created_at', $f, $pp) . $search, $pp) ?? [];
        $fp = ['cur' => $cur];
        $lost = $this->db->selectOne("SELECT COUNT(*) AS c, COALESCE(SUM(d.amount_minor),0) AS s FROM donations d WHERE d.currency = :cur AND d.status IN ('failed','cancelled','abandoned') AND " . $this->range('d.created_at', $f, $fp) . $search, $fp) ?? [];

        // Per gateway and per campaign (same cash rules).
        $bp = ['cur' => $cur];
        $bWhere = "d.currency = :cur AND d.status IN ('completed','refunded') AND d.verification_status = 'verified' AND " . $this->range($cash, $f, $bp) . $search;
        $gw = $this->db->select(
            "SELECT COALESCE(NULLIF(d.payment_method,''),g.name,d.source,'Unknown') AS gateway, COUNT(*) AS donations, COALESCE(SUM(d.amount_minor),0) AS gross_minor
             FROM donations d LEFT JOIN gateways g ON g.id = d.gateway_id WHERE {$bWhere} GROUP BY gateway",
            $bp
        );
        $rgp = ['cur' => $cur];
        $rgw = [];
        foreach ($this->db->select("SELECT COALESCE(NULLIF(d.payment_method,''),g.name,d.source,'Unknown') AS gateway, COALESCE(SUM(r.amount_minor),0) AS s
            FROM payment_refunds r JOIN donations d ON d.id=r.donation_id LEFT JOIN gateways g ON g.id=d.gateway_id
            WHERE d.verification_status='verified' AND r.status='completed' AND r.currency=:cur AND " . $this->range('COALESCE(r.completed_at,r.created_at)',$f,$rgp) . $search . ' GROUP BY gateway', $rgp) as $r) {
            $rgw[(string)$r['gateway']] = (int)$r['s'];
        }
        foreach ($rgw as $method => $amount) {
            if (!in_array($method,array_column($gw,'gateway'),true)) {
                $gw[]=['gateway'=>$method,'donations'=>0,'gross_minor'=>0];
            }
        }
        foreach ($gw as &$g) {
            $g['refunded_minor'] = $rgw[(string) $g['gateway']] ?? 0;
            $g['net_minor'] = (int) $g['gross_minor'] - $g['refunded_minor'];
            $g['share'] = $this->ratio($g['net_minor'], $gross - $refunded);
        }
        unset($g);
        usort($gw, static fn (array $a, array $b): int => $b['net_minor'] <=> $a['net_minor']);

        $cp = ['cur' => $cur];
        $camp = $this->db->select(
            "SELECT d.campaign_id, COALESCE(c.title,'No campaign / general') AS campaign, COUNT(*) AS donations,
                    COALESCE(SUM(d.amount_minor),0) AS gross_minor
             FROM donations d LEFT JOIN campaigns c ON c.id = d.campaign_id
             WHERE d.currency = :cur AND d.verification_status = 'verified' AND d.status IN ('completed','refunded') AND " . $this->range($cash, $f, $cp) . $search . ' GROUP BY d.campaign_id, c.title',
            $cp
        );
        $crp = ['cur' => $cur];
        $campRefunds = [];
        foreach ($this->db->select("SELECT d.campaign_id, COALESCE(SUM(r.amount_minor),0) AS refunded_minor
             FROM payment_refunds r JOIN donations d ON d.id = r.donation_id
             WHERE d.verification_status = 'verified' AND r.status = 'completed' AND r.currency = :cur AND " . $this->range('COALESCE(r.completed_at, r.created_at)', $f, $crp) . $search . ' GROUP BY d.campaign_id', $crp) as $rr) {
            $key = $rr['campaign_id'] === null ? 'general' : (string) $rr['campaign_id'];
            $campRefunds[$key] = (int) $rr['refunded_minor'];
        }
        foreach ($camp as &$c) {
            $key = $c['campaign_id'] === null ? 'general' : (string) $c['campaign_id'];
            $c['refunded_minor'] = $campRefunds[$key] ?? 0;
            $c['net_minor'] = (int) $c['gross_minor'] - $c['refunded_minor'];
            unset($campRefunds[$key]);
        }
        unset($c);
        foreach ($campRefunds as $key => $amount) {
            $camp[] = ['campaign_id' => $key === 'general' ? null : (int) $key,
                'campaign' => $key === 'general' ? 'No campaign / general' : (string) ($this->db->scalar('SELECT title FROM campaigns WHERE id=:id', ['id'=>$key]) ?: 'Deleted campaign'),
                'donations' => 0, 'gross_minor' => 0, 'refunded_minor' => $amount, 'net_minor' => -$amount];
        }
        usort($camp, static fn (array $a, array $b): int => (int)$b['net_minor'] <=> (int)$a['net_minor']);
        $camp = array_slice($camp, 0, 10);

        return [
            'description' => 'Money received, refunds paid back and the net result for each period.',
            'basis'       => 'Cash basis: gross is dated by the day the donation was confirmed, refunds by the day they were paid back, so a refund reduces the period it happened in. Currency ' . $cur . '. No conversion between currencies.',
            'summary'     => [
                $this->money('Gross received', $gross, $cur, number_format($count) . ' donations'),
                $this->money('Refunded', $refunded, $cur, $this->ratio($refunded, $gross) !== null ? number_format((float) $this->ratio($refunded, $gross), 1) . '% of gross' : ''),
                $this->money('Net received', $gross - $refunded, $cur, 'gross minus refunds'),
                $this->money('Average gift', $count > 0 ? $this->avg($gross, $count) : 0, $cur),
                $this->money('Awaiting the bank', (int) ($pend['s'] ?? 0), $cur, number_format((int) ($pend['c'] ?? 0)) . ' not yet counted'),
                $this->money('Not collected', (int) ($lost['s'] ?? 0), $cur, number_format((int) ($lost['c'] ?? 0)) . ' failed, cancelled or abandoned'),
                $this->money('Rejected', (int)$this->db->scalar("SELECT COALESCE(SUM(d.amount_minor),0) FROM donations d WHERE d.verification_status='rejected' AND d.currency=:cur AND " . $this->rangeLiteral('d.created_at',$f) . $search,['cur'=>$cur]),$cur),
            ],
            'columns'     => $columns,
            'rows'        => $this->withCurrency($rows, $cur),
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'extra'       => [
                ['title' => 'By gateway', 'columns' => [
                    ['key' => 'gateway', 'label' => 'Payment method', 'w' => 3], ['key' => 'donations', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'w' => 1.5],
                    ['key' => 'gross_minor', 'label' => 'Gross', 'type' => 'money', 'align' => 'right', 'w' => 2.2], ['key' => 'refunded_minor', 'label' => 'Refunded', 'type' => 'money', 'align' => 'right', 'w' => 2.2],
                    ['key' => 'net_minor', 'label' => 'Net', 'type' => 'money', 'align' => 'right', 'w' => 2.2], ['key' => 'share', 'label' => 'Share of net', 'type' => 'percent', 'align' => 'right', 'w' => 1.6],
                ], 'rows' => $this->withCurrency($gw, $cur)],
                ['title' => 'By campaign (top 10 by net)', 'columns' => [
                    ['key' => 'campaign', 'label' => 'Campaign', 'w' => 4], ['key' => 'donations', 'label' => 'Donations', 'type' => 'int', 'align' => 'right', 'w' => 1.5],
                    ['key' => 'gross_minor', 'label' => 'Gross', 'type' => 'money', 'align' => 'right', 'w' => 2.2], ['key' => 'refunded_minor', 'label' => 'Refunded', 'type' => 'money', 'align' => 'right', 'w' => 2.2],
                    ['key' => 'net_minor', 'label' => 'Net', 'type' => 'money', 'align' => 'right', 'w' => 2.2],
                ], 'rows' => $this->withCurrency($camp, $cur)],
            ],
            'notes'       => [
                'Gateway fees, bank settlement dates and payout amounts are not recorded in this system, so "Net" means donations received minus refunds paid. It is not the amount that reached the bank account.',
                'A refund paid later than its donation reduces that later period. Campaign and gateway net totals use the same cash-date basis as the overall net.',
            ],
        ];
    }

    // ================================================================== 12. RECEIPTS

    /** @param array<string,mixed> $f */
    private function receipts(array $f, int $page, int $perPage): array
    {
        $cur = $f['currency'];
        $p = ['cur' => $cur];
        $where = "d.currency = :cur AND d.status IN ('completed','refunded') AND d.verification_status = 'verified' AND " . $this->range('COALESCE(d.completed_at, d.created_at)', $f, $p);
        if ($f['receipt_status'] !== '') {
            $where .= ' AND d.receipt_email_status = ' . $this->p($p, $f['receipt_status']);
        }
        if ($f['gateway'] !== '') {
            $where .= ' AND g.code = ' . $this->p($p, $f['gateway']);
        }
        if ($f['q'] !== '') {
            $like = $this->like($f['q']);
            $where .= ' AND (d.public_reference LIKE ' . $this->p($p, $like) . ' OR d.receipt_number LIKE ' . $this->p($p, $like) . ' OR d.recipient_name LIKE ' . $this->p($p, $like) . ' OR dn.name LIKE ' . $this->p($p, $like) . ' OR dn.email LIKE ' . $this->p($p, $like) . ')';
        }
        $from = "FROM donations d LEFT JOIN donors dn ON dn.id = d.donor_id LEFT JOIN gateways g ON g.id = d.gateway_id LEFT JOIN donation_purposes dpu ON dpu.id = d.purpose_id
                 LEFT JOIN email_jobs ej ON ej.dedupe_key = CONCAT('donation.receipt:', d.id)";

        $columns = [
            ['key' => 'confirmed_at', 'label' => 'Confirmed', 'type' => 'datetime', 'sort' => 'confirmed_at', 'w' => 2.2],
            ['key' => 'receipt_number', 'label' => 'Receipt no.', 'type' => 'code', 'sort' => 'd.public_reference', 'w' => 2.4, 'link' => 'donation'],
            ['key' => 'donor', 'label' => 'Donor', 'sort' => 'donor', 'w' => 2.6],
            ['key' => 'recipient', 'label' => 'Recipient', 'w' => 2.5],
            ['key' => 'verification_status', 'label' => 'Verification', 'type' => 'status', 'w' => 1.8],
            ['key' => 'certificate_status', 'label' => 'Certificate', 'type' => 'status', 'w' => 1.8],
            ['key' => 'email', 'label' => 'Sent to', 'sort' => 'email', 'w' => 3.6],
            ['key' => 'purpose', 'label' => 'Purpose', 'w' => 3],
            ['key' => 'gateway', 'label' => 'Gateway', 'sort' => 'gateway', 'w' => 1.6],
            ['key' => 'amount_minor', 'label' => 'Amount', 'type' => 'money', 'align' => 'right', 'sort' => 'd.amount_minor', 'w' => 2],
            ['key' => 'donation_status', 'label' => 'Donation', 'type' => 'status', 'w' => 1.6],
            ['key' => 'receipt_status', 'label' => 'Receipt email', 'type' => 'status', 'sort' => 'd.receipt_email_status', 'w' => 1.8],
            ['key' => 'sent_at', 'label' => 'Email sent', 'type' => 'datetime', 'sort' => 'sent_at', 'w' => 2.2],
            ['key' => 'last_error', 'label' => 'Problem', 'w' => 3],
        ];
        [$order, $sortKey, $dir] = $this->order($columns, $f, 'confirmed_at', 'desc', 'd.id DESC');

        $total = $this->db->int("SELECT COUNT(*) {$from} WHERE {$where}", $p);
        $rows = $this->db->select(
            "SELECT d.id, d.public_reference, d.receipt_number, d.certificate_status, d.verification_status,
                    COALESCE(NULLIF(d.recipient_name,''),dn.name,'—') AS recipient, d.amount_minor, d.status AS donation_status, d.receipt_email_status AS receipt_status,
                    COALESCE(d.completed_at, d.created_at) AS confirmed_at, COALESCE(dn.name,'—') AS donor, COALESCE(dn.email,'—') AS email,
                    COALESCE(dpu.name,'General fund') AS purpose, COALESCE(g.name,'Unknown') AS gateway, ej.sent_at AS sent_at, ej.last_error AS last_error
             {$from} WHERE {$where}{$order}" . $this->limit($page, $perPage),
            $p
        );

        $counts = [];
        foreach ($this->db->select("SELECT d.receipt_email_status AS s, COUNT(*) AS c {$from} WHERE {$where} GROUP BY d.receipt_email_status", $p) as $r) {
            $counts[(string) $r['s']] = (int) $r['c'];
        }
        $all = array_sum($counts);

        return [
            'description' => 'Whether every confirmed donation was sent its receipt email.',
            'basis'       => 'Confirmed and refunded donations dated by confirmation. Receipt status is the delivery state recorded by the email queue. Currency ' . $cur . '.',
            'summary'     => [
                $this->count('Receipts due', $all, 'confirmed donations'),
                $this->count('Sent', $counts['sent'] ?? 0),
                $this->count('Queued', $counts['queued'] ?? 0, 'waiting to be sent'),
                $this->count('Failed', $counts['failed'] ?? 0, 'need attention'),
                $this->count('Not sent', $counts['not_sent'] ?? 0),
                $this->pct('Delivery rate', $this->ratio($counts['sent'] ?? 0, $all)),
            ],
            'columns'     => $columns,
            'rows'        => $this->withCurrency($rows, $cur),
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'notes'       => [
                'Historical completed legacy donations are excluded: no staff-verified receipt can be inferred. The report shows actual issued receipt numbers and recorded email/certificate state.',
                'Failed receipts can be retried from Emails → Email queue.',
            ],
        ];
    }

    // ================================================================== 13. AUDIT LOG

    /** @param array<string,mixed> $f */
    private function audit(array $f, int $page, int $perPage): array
    {
        $p = [];
        $where = $this->range('al.created_at', $f, $p);
        if ($f['who'] === 'staff') {
            $where .= " AND EXISTS (SELECT 1 FROM user_roles ur JOIN roles ro ON ro.id=ur.role_id WHERE ur.user_id=al.user_id AND ro.name IN ('admin','super_admin','finance_manager','content_manager','volunteer_manager','initial_reviewer','initial_review','verification_officer'))";
        } elseif ($f['who'] === 'admins') {
            $where .= " AND EXISTS (SELECT 1 FROM user_roles ur JOIN roles ro ON ro.id = ur.role_id WHERE ur.user_id = al.user_id AND ro.name IN ('admin','super_admin'))";
        }
        if ($f['area'] !== '') {
            $where .= ' AND al.action LIKE ' . $this->p($p, $this->like($f['area'] . '.') );
        }
        if ($f['q'] !== '') {
            $like = $this->like($f['q']);
            $where .= ' AND (al.action LIKE ' . $this->p($p, $like) . ' OR al.entity_type LIKE ' . $this->p($p, $like) . ' OR al.entity_id LIKE ' . $this->p($p, $like)
                . ' OR u.email LIKE ' . $this->p($p, $like) . ' OR al.ip_address LIKE ' . $this->p($p, $like) . ')';
        }
        $from = 'FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id';

        $columns = [
            ['key' => 'created_at', 'label' => 'When', 'type' => 'datetime', 'sort' => 'al.created_at', 'w' => 2.3],
            ['key' => 'role', 'label' => 'Current role', 'w' => 2.5],
            ['key' => 'previous_value', 'label' => 'Previous value', 'w' => 2.5],
            ['key' => 'new_value', 'label' => 'New value', 'w' => 2.5],
            ['key' => 'actor', 'label' => 'Who', 'sort' => 'actor', 'w' => 3.4],
            ['key' => 'action', 'label' => 'Action', 'sort' => 'al.action', 'w' => 3.2],
            ['key' => 'entity', 'label' => 'Record', 'sort' => 'al.entity_type', 'w' => 2.2],
            ['key' => 'details', 'label' => 'Details', 'w' => 4.6],
            ['key' => 'ip_address', 'label' => 'IP address', 'sort' => 'al.ip_address', 'w' => 2.2],
        ];
        [$order, $sortKey, $dir] = $this->order($columns, $f, 'created_at', 'desc', 'al.id DESC');

        $total = $this->db->int("SELECT COUNT(*) {$from} WHERE {$where}", $p);
        $rows = $this->db->select(
            "SELECT al.id, al.created_at, al.action, al.entity_type, al.entity_id, al.safe_metadata_json, al.ip_address,
                    COALESCE(u.email, 'System / visitor') AS actor,
                    (SELECT GROUP_CONCAT(DISTINCT ro.name ORDER BY ro.name SEPARATOR ', ') FROM user_roles ur JOIN roles ro ON ro.id=ur.role_id WHERE ur.user_id=al.user_id) AS role
             {$from} WHERE {$where}{$order}" . $this->limit($page, $perPage),
            $p
        );
        foreach ($rows as &$r) {
            $r['entity'] = trim((string) ($r['entity_type'] ?? '') . ((string) ($r['entity_id'] ?? '') !== '' ? ' #' . $r['entity_id'] : ''));
            $r['details'] = $this->metaText((string) ($r['safe_metadata_json'] ?? ''));
            $meta = json_decode((string)($r['safe_metadata_json'] ?? ''),true);
            $r['previous_value'] = is_array($meta) ? $this->metaText(json_encode($meta['previous'] ?? $meta['before'] ?? $meta['old'] ?? [], JSON_UNESCAPED_UNICODE)) : '';
            $r['new_value'] = is_array($meta) ? $this->metaText(json_encode($meta['new'] ?? $meta['after'] ?? $meta['to'] ?? [], JSON_UNESCAPED_UNICODE)) : '';
        }
        unset($r);

        $a = $this->db->selectOne("SELECT COUNT(*) AS n, COUNT(DISTINCT al.user_id) AS actors, MIN(al.created_at) AS first_at, MAX(al.created_at) AS last_at {$from} WHERE {$where}", $p) ?? [];
        $byAction = $this->db->select("SELECT al.action, COUNT(*) AS c {$from} WHERE {$where} GROUP BY al.action ORDER BY c DESC, al.action ASC LIMIT 12", $p);
        $byActor = $this->db->select("SELECT COALESCE(u.email, 'System / visitor') AS actor, COUNT(*) AS c, MAX(al.created_at) AS last_at {$from} WHERE {$where} GROUP BY u.email ORDER BY c DESC LIMIT 12", $p);

        return [
            'description' => 'A read-only record of actions taken in the system: who did what, to which record, and when.',
            'basis'       => 'Dated by when the action was recorded. "All staff" includes financial, review and management staff; "Admins only" limits to administrators. Entries cannot be edited or deleted from the interface.',
            'summary'     => [
                $this->count('Events', (int) ($a['n'] ?? 0)),
                $this->count('People involved', (int) ($a['actors'] ?? 0)),
                $this->text('Most common action', $byAction[0]['action'] ?? '—', isset($byAction[0]) ? number_format((int) $byAction[0]['c']) . ' times' : ''),
                $this->text('Most active', $byActor[0]['actor'] ?? '—', isset($byActor[0]) ? number_format((int) $byActor[0]['c']) . ' events' : ''),
                $this->text('Earliest in view', ($a['first_at'] ?? null) ? gmdate('j M Y H:i', (int) strtotime((string) $a['first_at'] . ' UTC')) : '—'),
                $this->text('Latest in view', ($a['last_at'] ?? null) ? gmdate('j M Y H:i', (int) strtotime((string) $a['last_at'] . ' UTC')) : '—'),
            ],
            'columns'     => $columns,
            'rows'        => $rows,
            'total'       => $total,
            'sort'        => $sortKey,
            'dir'         => $dir,
            'extra'       => [
                ['title' => 'Most common actions', 'columns' => [['key' => 'action', 'label' => 'Action', 'w' => 6], ['key' => 'c', 'label' => 'Times', 'type' => 'int', 'align' => 'right', 'w' => 1.5]], 'rows' => $byAction],
                ['title' => 'Activity by person', 'columns' => [['key' => 'actor', 'label' => 'Who', 'w' => 5], ['key' => 'c', 'label' => 'Events', 'type' => 'int', 'align' => 'right', 'w' => 1.5], ['key' => 'last_at', 'label' => 'Last activity', 'type' => 'datetime', 'w' => 2.4]], 'rows' => $byActor],
            ],
            'notes'       => ['Details come from the audit log\'s "safe metadata": the system strips passwords, tokens and card data before writing it. Prior/new values appear only when actually stored in safe metadata. Roles reflect current membership (historical role assignments were not recorded). The log records actions, not page views.'],
        ];
    }

    /** Compact "key: value, key: value" text from stored safe metadata. */
    private function metaText(string $json): string
    {
        if ($json === '' || $json === '{}' || $json === '[]') {
            return '';
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return mb_substr($json, 0, 160);
        }
        $parts = [];
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $v = json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            if ($v === null || $v === '') {
                continue;
            }
            $parts[] = (is_int($k) ? '' : str_replace('_', ' ', (string) $k) . ': ') . (is_bool($v) ? ($v ? 'yes' : 'no') : (string) $v);
        }

        return mb_substr(implode(', ', $parts), 0, 180);
    }
}
