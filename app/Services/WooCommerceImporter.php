<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Str;
use App\Repositories\DonationRepository;
use App\Repositories\DonorRepository;
use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * Imports the historical donation orders from the old WordPress/WooCommerce
 * site into the donations ledger.
 *
 * Two sources are supported and both feed the same normalised importer:
 *
 *   1. A CSV export (WooCommerce > Orders > Export, or the "Advanced Order
 *      Export" plugin). Column names are matched loosely, so the default
 *      exports work without renaming anything.
 *   2. The WordPress database itself (wp_posts / wp_postmeta /
 *      wp_woocommerce_order_items), when the old DB is still reachable.
 *
 * The import is idempotent: every order is keyed on donations.legacy_order_id,
 * so running it twice (or again after new orders arrive) never duplicates.
 */
final class WooCommerceImporter
{
    /** WooCommerce status → donations.status */
    private const STATUS_MAP = [
        'completed'  => 'completed',
        'processing' => 'completed',   // paid, awaiting fulfilment — for donations this is "paid"
        'on-hold'    => 'pending',     // bank transfer awaiting confirmation
        'pending'    => 'pending',
        'failed'     => 'failed',
        'cancelled'  => 'cancelled',
        'refunded'   => 'refunded',
        'checkout-draft' => 'abandoned',
        'draft'      => 'abandoned',
        'trash'      => 'cancelled',
    ];

    /** Payment method title → donations.source */
    private const SOURCE_MAP = [
        // card / online gateways first, so "Meezan Bank" is not read as a bank transfer
        'meezan'      => 'woocommerce',
        'etisalat'    => 'woocommerce',
        'ubl'         => 'woocommerce',
        'paypal'      => 'woocommerce',
        'stripe'      => 'woocommerce',
        'card'        => 'woocommerce',
        'bacs'        => 'bank_transfer',
        'direct bank' => 'bank_transfer',
        'bank'        => 'bank_transfer',
        'cod'         => 'cash',
        'cash'        => 'cash',
        'cheque'      => 'cheque',
        'check'       => 'cheque',
    ];

    /** CSV header aliases (lower-cased, punctuation stripped) → canonical key */
    private const COLUMNS = [
        'order_id'    => ['order id', 'order number', 'id', 'order', 'number'],
        'date'        => ['order date', 'date', 'date created', 'created', 'paid date', 'date paid'],
        'status'      => ['order status', 'status'],
        'total'       => ['order total', 'total', 'order total amount', 'total amount', 'amount'],
        'currency'    => ['currency', 'order currency'],
        'first_name'  => ['billing first name', 'first name', 'first name billing', 'customer first name'],
        'last_name'   => ['billing last name', 'last name', 'last name billing', 'customer last name'],
        'full_name'   => ['billing name', 'customer name', 'full name', 'name', 'customer'],
        'email'       => ['billing email', 'email', 'email billing', 'customer email', 'billing email address'],
        'phone'       => ['billing phone', 'phone', 'phone billing', 'customer phone'],
        'country'     => ['billing country', 'country', 'country billing'],
        'city'        => ['billing city', 'city', 'city billing'],
        'method'      => ['payment method title', 'payment method', 'payment gateway', 'payment'],
        'items'       => ['item name', 'product name', 'products', 'items', 'order items', 'line items', 'product'],
        'note'        => ['customer note', 'customer message', 'note', 'order note', 'order notes'],
        'txn'         => ['transaction id', 'transaction', 'payment transaction id'],
    ];

    /** @var array<string,int> product-name keyword (lower) → purpose id */
    private array $purposeKeywords = [];
    private ?int $defaultPurposeId = null;

    /** @var array<int,array<string,mixed>> */
    private array $purposes = [];

    public function __construct(
        private Database $db,
        private DonationRepository $donations,
        private DonorRepository $donors,
        private AuditService $audit
    ) {
        foreach ($this->db->select('SELECT id, name, slug FROM donation_purposes') as $p) {
            $this->purposes[(int) $p['id']] = $p;
            $this->purposeKeywords[mb_strtolower((string) $p['name'])] = (int) $p['id'];
            $this->purposeKeywords[str_replace('-', ' ', (string) $p['slug'])] = (int) $p['id'];
            if ((string) $p['slug'] === 'general-fund') {
                $this->defaultPurposeId = (int) $p['id'];
            }
        }
    }

    /** "Monthly Orphan Sponsorship=parent-the-orphan" style overrides. */
    public function addPurposeMapping(string $keyword, string $slug): void
    {
        foreach ($this->purposes as $id => $p) {
            if ((string) $p['slug'] === $slug) {
                $this->purposeKeywords[mb_strtolower(trim($keyword))] = $id;
                return;
            }
        }
        throw new InvalidArgumentException("Unknown purpose slug '{$slug}'.");
    }

    public function setDefaultPurpose(string $slug): void
    {
        foreach ($this->purposes as $id => $p) {
            if ((string) $p['slug'] === $slug) {
                $this->defaultPurposeId = $id;
                return;
            }
        }
        throw new InvalidArgumentException("Unknown purpose slug '{$slug}'.");
    }

    // ------------------------------------------------------------ sources

    /**
     * Read a WooCommerce CSV export into normalised order rows.
     * Multi-line-item exports (one row per item) are merged per order.
     *
     * @return array<int,array<string,mixed>>
     */
    public function readCsv(string $path): array
    {
        if (!is_readable($path)) {
            throw new RuntimeException("Cannot read {$path}.");
        }
        $fh = fopen($path, 'rb');
        if ($fh === false) {
            throw new RuntimeException("Cannot open {$path}.");
        }

        $first = fgets($fh);
        if ($first === false) {
            return [];
        }
        $first = preg_replace('/^\xEF\xBB\xBF/', '', $first) ?? $first;    // strip BOM
        $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
        $header = str_getcsv($first, $delimiter, '"', '\\');
        $map = $this->mapHeader($header);
        if (!isset($map['order_id'], $map['total'])) {
            throw new RuntimeException('CSV must contain at least an order id and an order total column. Found: ' . implode(', ', $header));
        }

        $orders = [];
        while (($row = fgetcsv($fh, 0, $delimiter, '"', '\\')) !== false) {
            if ($row === [null] || count(array_filter($row, static fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $get = static fn (string $key): string => isset($map[$key], $row[$map[$key]]) ? trim((string) $row[$map[$key]]) : '';
            $id = preg_replace('/\D+/', '', $get('order_id')) ?: $get('order_id');
            if ($id === '') {
                continue;
            }
            $name = $get('full_name');
            if ($name === '') {
                $name = trim($get('first_name') . ' ' . $get('last_name'));
            }
            if (!isset($orders[$id])) {
                $orders[$id] = [
                    'order_id' => $id,
                    'date'     => $get('date'),
                    'status'   => $get('status'),
                    'total'    => $get('total'),
                    'currency' => $get('currency'),
                    'name'     => $name,
                    'email'    => $get('email'),
                    'phone'    => $get('phone'),
                    'country'  => $get('country'),
                    'city'     => $get('city'),
                    'method'   => $get('method'),
                    'items'    => [],
                    'note'     => $get('note'),
                    'txn'      => $get('txn'),
                ];
            }
            if ($get('items') !== '') {
                $orders[$id]['items'][] = $get('items');
            }
        }
        fclose($fh);

        return array_values($orders);
    }

    /**
     * Read orders straight from a WordPress database.
     *
     * @return array<int,array<string,mixed>>
     */
    public function readWordPress(string $dsn, string $user, string $password, string $prefix = 'wp_', ?string $since = null): array
    {
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $p = preg_replace('/[^A-Za-z0-9_]/', '', $prefix) ?? 'wp_';

        $sql = "SELECT ID, post_date_gmt, post_status, post_excerpt FROM {$p}posts WHERE post_type = 'shop_order'";
        $params = [];
        if ($since !== null) {
            $sql .= ' AND post_date_gmt >= :since';
            $params['since'] = $since . ' 00:00:00';
        }
        $sql .= ' ORDER BY ID';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();
        if ($posts === []) {
            return [];
        }

        $ids = array_map(static fn ($r) => (int) $r['ID'], $posts);
        $in = implode(',', $ids);
        $meta = [];
        foreach ($pdo->query("SELECT post_id, meta_key, meta_value FROM {$p}postmeta WHERE post_id IN ({$in}) AND meta_key IN ('_order_total','_order_currency','_billing_first_name','_billing_last_name','_billing_email','_billing_phone','_billing_country','_billing_city','_payment_method','_payment_method_title','_transaction_id','_paid_date','_date_paid')") as $m) {
            $meta[(int) $m['post_id']][(string) $m['meta_key']] = (string) $m['meta_value'];
        }
        $items = [];
        foreach ($pdo->query("SELECT order_id, order_item_name FROM {$p}woocommerce_order_items WHERE order_item_type = 'line_item' AND order_id IN ({$in})") as $i) {
            $items[(int) $i['order_id']][] = (string) $i['order_item_name'];
        }

        $orders = [];
        foreach ($posts as $post) {
            $id = (int) $post['ID'];
            $m = $meta[$id] ?? [];
            $orders[] = [
                'order_id' => (string) $id,
                'date'     => (string) $post['post_date_gmt'],
                'status'   => preg_replace('/^wc-/', '', (string) $post['post_status']) ?? '',
                'total'    => $m['_order_total'] ?? '0',
                'currency' => $m['_order_currency'] ?? 'PKR',
                'name'     => trim(($m['_billing_first_name'] ?? '') . ' ' . ($m['_billing_last_name'] ?? '')),
                'email'    => $m['_billing_email'] ?? '',
                'phone'    => $m['_billing_phone'] ?? '',
                'country'  => $m['_billing_country'] ?? '',
                'city'     => $m['_billing_city'] ?? '',
                'method'   => $m['_payment_method_title'] ?? ($m['_payment_method'] ?? ''),
                'items'    => $items[$id] ?? [],
                'note'     => (string) $post['post_excerpt'],
                'txn'      => $m['_transaction_id'] ?? '',
                'paid_at'  => isset($m['_date_paid']) && ctype_digit($m['_date_paid']) ? '@' . $m['_date_paid'] : ($m['_paid_date'] ?? ''),
                'date_is_utc' => true,
            ];
        }
        return $orders;
    }

    // ------------------------------------------------------------- import

    /**
     * @param array<int,array<string,mixed>> $orders normalised rows
     * @return array{imported:int,skipped:int,updated:int,errors:array<int,string>,by_status:array<string,int>,total_minor:int}
     */
    public function import(array $orders, bool $dryRun = false, ?int $actorId = null, ?callable $progress = null): array
    {
        $stats = ['imported' => 0, 'skipped' => 0, 'updated' => 0, 'errors' => [], 'by_status' => [], 'total_minor' => 0];

        foreach ($orders as $o) {
            try {
                $n = $this->normalise($o);
            } catch (InvalidArgumentException $e) {
                $stats['errors'][] = "order {$o['order_id']}: " . $e->getMessage();
                continue;
            }

            $existing = $this->db->selectOne('SELECT id, status FROM donations WHERE legacy_order_id = :l', ['l' => $n['legacy_order_id']]);

            if ($existing !== null) {
                // Only move forward: pending → completed/failed etc. Never
                // touch a row that has been adjusted manually (it has an adjustment).
                if ($existing['status'] !== $n['status'] && in_array($existing['status'], ['pending', 'processing'], true)) {
                    $adjusted = $this->db->int('SELECT COUNT(*) FROM financial_adjustments WHERE donation_id = :d', ['d' => (int) $existing['id']]) > 0;
                    if (!$adjusted) {
                        if (!$dryRun) {
                            $this->donations->updateStatus((int) $existing['id'], $n['status'], $n['status'] === 'completed' ? $n['completed_at'] : null);
                        }
                        $stats['updated']++;
                        $progress && $progress('updated', $n);
                        continue;
                    }
                }
                $stats['skipped']++;
                continue;
            }

            if (!$dryRun) {
                $donorId = null;
                if ($n['email'] !== '') {
                    $donorId = $this->donors->findOrCreate($n['name'], $n['email'], $n['phone'] ?: null);
                } elseif ($n['name'] !== '') {
                    $donorId = $this->db->insert('donors', [
                        'name'  => $n['name'],
                        'email' => 'legacy+' . $n['legacy_order_id'] . '@almarah.local',
                        'phone' => $n['phone'] ?: null,
                    ]);
                }

                $donationId = $this->donations->create([
                    'public_reference' => 'WC-' . $n['legacy_order_id'],
                    'donor_id'         => $donorId,
                    'purpose_id'       => $n['purpose_id'],
                    'donation_type'    => $n['donation_type'],
                    'source'           => $n['source'],
                    'donor_country'    => $n['country'] ?: null,
                    'donor_city'       => $n['city'] ?: null,
                    'gateway_id'       => null,
                    'bank_reference'   => $n['txn'] ?: null,
                    'legacy_order_id'  => $n['legacy_order_id'],
                    'amount_minor'     => $n['amount_minor'],
                    'currency'         => $n['currency'],
                    'donor_message'    => $n['note'] ?: null,
                    'anonymous'        => 0,
                    'status'           => $n['status'],
                ]);
                $this->db->update('donations', [
                    'created_at'   => $n['created_at'],
                    'completed_at' => $n['status'] === 'completed' ? $n['completed_at'] : null,
                    'receipt_number' => $n['status'] === 'completed' ? 'WC-' . $n['legacy_order_id'] : null,
                ], 'id = :id', ['id' => $donationId]);
            }

            $stats['imported']++;
            $stats['by_status'][$n['status']] = ($stats['by_status'][$n['status']] ?? 0) + 1;
            if ($n['status'] === 'completed') {
                $stats['total_minor'] += $n['amount_minor'];
            }
            $progress && $progress('imported', $n);
        }

        if (!$dryRun && ($stats['imported'] > 0 || $stats['updated'] > 0)) {
            $this->audit->log('finance.import.woocommerce', 'donations', null, [
                'imported' => $stats['imported'], 'updated' => $stats['updated'], 'skipped' => $stats['skipped'],
                'errors' => count($stats['errors']),
            ], $actorId);
        }

        return $stats;
    }

    // ------------------------------------------------------------ helpers

    /** @return array<string,mixed> */
    private function normalise(array $o): array
    {
        $legacy = trim((string) ($o['order_id'] ?? ''));
        if ($legacy === '' || strlen($legacy) > 64) {
            throw new InvalidArgumentException('missing order id');
        }

        $amount = (string) ($o['total'] ?? '');
        $amount = preg_replace('/[^\d.,-]/', '', $amount) ?? '';
        if (substr_count($amount, ',') > 0 && substr_count($amount, '.') === 0 && preg_match('/,\d{2}$/', $amount)) {
            $amount = str_replace(',', '.', $amount);      // 1.234,56 style
        }
        $amount = str_replace(',', '', $amount);
        $minor = (int) round(((float) $amount) * 100);
        if ($minor <= 0) {
            throw new InvalidArgumentException('zero or invalid total "' . ($o['total'] ?? '') . '"');
        }

        $wcStatus = strtolower(trim((string) ($o['status'] ?? 'completed')));
        $wcStatus = preg_replace('/^wc-/', '', $wcStatus) ?? $wcStatus;
        $status = self::STATUS_MAP[$wcStatus] ?? null;
        if ($status === null) {
            throw new InvalidArgumentException("unknown status '{$wcStatus}'");
        }

        $dateText = (string) ($o['date'] ?? '');
        if (!empty($o['date_is_utc']) && !preg_match('/(Z|UTC|[+-]\d{2}:?\d{2})$/i', $dateText)) {
            $dateText .= ' UTC';
        }
        $ts = strtotime($dateText) ?: null;
        if ($ts === null) {
            throw new InvalidArgumentException('unreadable date "' . ($o['date'] ?? '') . '"');
        }
        $created = gmdate('Y-m-d H:i:s', $ts);
        $paidTs = !empty($o['paid_at']) ? (strtotime((string) $o['paid_at']) ?: $ts) : $ts;

        $currency = strtoupper(trim((string) ($o['currency'] ?? ''))) ?: 'PKR';
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            $currency = 'PKR';
        }

        $items = (array) ($o['items'] ?? []);
        $itemText = mb_strtolower(implode(' | ', $items));
        // The purpose whose keyword appears earliest in the line items wins
        // (i.e. the first product on the order), explicit --map keywords included.
        $purposeId = $this->defaultPurposeId;
        $bestPos = PHP_INT_MAX;
        foreach ($this->purposeKeywords as $kw => $pid) {
            $pos = $kw !== '' ? mb_strpos($itemText, (string) $kw) : false;
            if ($pos !== false && $pos < $bestPos) {
                $bestPos = $pos;
                $purposeId = $pid;
            }
        }

        $method = mb_strtolower((string) ($o['method'] ?? ''));
        $source = 'woocommerce';
        foreach (self::SOURCE_MAP as $kw => $src) {
            if ($method !== '' && str_contains($method, $kw)) {
                $source = $src;
                break;
            }
        }

        $email = mb_strtolower(trim((string) ($o['email'] ?? '')));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $email = '';
        }

        return [
            'legacy_order_id' => $legacy,
            'amount_minor'    => $minor,
            'currency'        => $currency,
            'status'          => $status,
            'created_at'      => $created,
            'completed_at'    => gmdate('Y-m-d H:i:s', $paidTs),
            'name'            => Str::limit(trim((string) ($o['name'] ?? '')), 120),
            'email'           => $email,
            'phone'           => Str::limit(trim((string) ($o['phone'] ?? '')), 40),
            'country'         => Str::limit(trim((string) ($o['country'] ?? '')), 60),
            'city'            => Str::limit(trim((string) ($o['city'] ?? '')), 80),
            'purpose_id'      => $purposeId,
            'donation_type'   => str_contains($itemText, 'monthly') || str_contains($itemText, 'subscription') ? 'monthly' : 'one_time',
            'source'          => $source,
            'txn'             => Str::limit(trim((string) ($o['txn'] ?? '')), 120),
            'note'            => Str::limit(trim(implode(' | ', $items) . (($o['note'] ?? '') !== '' ? ' — ' . $o['note'] : '')), 1000),
        ];
    }

    /** @param array<int,string|null> $header @return array<string,int> */
    private function mapHeader(array $header): array
    {
        $map = [];
        $clean = array_map(static fn ($h) => trim(preg_replace('/[^a-z0-9 ]+/', ' ', mb_strtolower((string) $h)) ?? ''), $header);
        $clean = array_map(static fn ($h) => preg_replace('/\s+/', ' ', $h) ?? $h, $clean);

        foreach (self::COLUMNS as $key => $aliases) {
            foreach ($aliases as $alias) {
                $idx = array_search($alias, $clean, true);
                if ($idx !== false) {
                    $map[$key] = (int) $idx;
                    break;
                }
            }
        }
        // Fallbacks: "first name (billing)" style headers from some plugins.
        if (!isset($map['first_name'])) {
            foreach ($clean as $i => $h) {
                if (str_contains($h, 'first name')) { $map['first_name'] = $i; break; }
            }
        }
        if (!isset($map['last_name'])) {
            foreach ($clean as $i => $h) {
                if (str_contains($h, 'last name')) { $map['last_name'] = $i; break; }
            }
        }
        if (!isset($map['email'])) {
            foreach ($clean as $i => $h) {
                if (str_contains($h, 'email')) { $map['email'] = $i; break; }
            }
        }
        return $map;
    }
}
