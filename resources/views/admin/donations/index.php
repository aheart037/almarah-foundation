<?php
/**
 * Donation ledger.
 *
 * @var array $donations
 * @var int $total
 * @var array $sums
 * @var int $page
 * @var int $lastPage
 * @var array $filters
 * @var array $totals
 * @var array $counts
 */
?>
<div class="dash-head">
  <div>
    <h1>Donations</h1>
    <p class="sub">Every payment attempt, and what the bank said about it.</p>
  </div>
  <?php if ($can('reports.view')): ?>
    <div class="sr-actions">
      <a class="btn btn-light" href="<?= e(base_url('admin/ledger')) ?>">Ledger</a>
      <a class="btn btn-outline-brand" href="<?= e(base_url('admin/donations/export?' . http_build_query(array_filter($filters, static fn ($v) => $v !== '' && $v !== null && $v !== 0)))) ?>">Export filtered CSV</a>
    </div>
  <?php endif; ?>
</div>

<div class="stat-cards">
  <div class="stat-card brand">
    <div class="k">Confirmed</div>
    <div class="v"><?= e(money((int) ($sums['completed'] ?? 0))) ?></div>
    <div class="d">Filtered total</div>
  </div>
  <div class="stat-card">
    <div class="k">Awaiting the bank</div>
    <div class="v"><?= e(money((int) ($sums['pending'] ?? 0))) ?></div>
    <div class="d">Pending and processing</div>
  </div>
  <div class="stat-card">
    <div class="k">Failed / cancelled</div>
    <div class="v"><?= e(money((int) ($sums['failed'] ?? 0))) ?></div>
    <div class="d">Never counted</div>
  </div>
  <div class="stat-card">
    <div class="k">Refunded</div>
    <div class="v"><?= e(money((int) ($sums['refunded'] ?? 0))) ?></div>
    <div class="d"><?= e(number_format((int) ($counts['refunded'] ?? 0))) ?> donation(s)</div>
  </div>
</div>

<section class="panel">
  <form class="toolbar" method="get" action="<?= e(base_url('admin/donations')) ?>">
    <div class="search-field">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      <input class="input" type="search" name="q" value="<?= e((string) $filters['q']) ?>" placeholder="Reference, donor, email or bank transaction">
    </div>
    <div class="select-field">
      <select class="select-field" name="status" data-autosubmit aria-label="Status">
        <option value="">Any status</option>
        <?php foreach (['completed', 'processing', 'pending', 'failed', 'cancelled', 'refunded', 'abandoned'] as $status): ?>
          <option value="<?= e($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="select-field">
      <select class="select-field" name="gateway" data-autosubmit aria-label="Gateway">
        <option value="">Any gateway</option>
        <option value="meezan" <?= $filters['gateway'] === 'meezan' ? 'selected' : '' ?>>Meezan Bank</option>
        <option value="etisalat" <?= $filters['gateway'] === 'etisalat' ? 'selected' : '' ?>>Etisalat / UBL EPG</option>
        <option value="offline" <?= $filters['gateway'] === 'offline' ? 'selected' : '' ?>>Offline / bank transfer</option>
      </select>
    </div>
    <div class="select-field">
      <select class="select-field" name="purpose_id" data-autosubmit aria-label="Purpose">
        <option value="">Any purpose</option>
        <?php foreach ($purposes ?? [] as $p): ?>
          <option value="<?= (int) $p['id'] ?>" <?= (int) ($filters['purpose_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>><?= e((string) $p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field" style="margin:0">
      <label class="sr-only" for="date_from">From</label>
      <input class="input" type="date" id="date_from" name="date_from" value="<?= e((string) $filters['date_from']) ?>">
    </div>
    <div class="field" style="margin:0">
      <label class="sr-only" for="date_to">To</label>
      <input class="input" type="date" id="date_to" name="date_to" value="<?= e((string) $filters['date_to']) ?>">
    </div>
    <?php foreach (['donor_name'=>'Donor name','phone'=>'Phone','email'=>'Email','amount_min'=>'Minimum amount','amount_max'=>'Maximum amount','receipt_number'=>'Receipt no.','transaction_id'=>'Transaction ID','recipient'=>'Recipient','referrer'=>'Referrer','staff_member'=>'Staff ID or email'] as $key=>$label): ?>
      <label><?= e($label) ?><input class="input" name="<?= e($key) ?>" value="<?= e((string) ($filters[$key] ?? '')) ?>"></label>
    <?php endforeach; ?>
    <label>Payment method <select name="payment_method" class="select-field"><option value="">Any</option>
    <?php foreach (['bank_transfer','online_transfer','cash','other','cheque','zelle','meezan','etisalat'] as $method): ?><option value="<?= e($method) ?>" <?= ($filters['payment_method'] ?? '') === $method ? 'selected' : '' ?>><?= e(ucwords(str_replace('_',' ',$method))) ?></option><?php endforeach; ?></select></label>
    <label>Verification <select name="verification_status" class="select-field"><option value="">Any</option>
    <?php foreach (['legacy','submitted','initial_review','verification_officer','finance_manager','rejected','verified'] as $stage): ?><option value="<?= e($stage) ?>" <?= ($filters['verification_status'] ?? '') === $stage ? 'selected' : '' ?>><?= e(ucwords(str_replace('_',' ',$stage))) ?></option><?php endforeach; ?></select></label>
    <button class="btn btn-brand" type="submit">Filter</button>
  </form>

  <p class="text-muted"><?= e(number_format($total)) ?> donation<?= $total === 1 ? '' : 's' ?> match.</p>

  <?php if ($donations === []): ?>
    <div class="empty-state">
      <h3>No donations match</h3>
      <p>Try clearing the filters, or widen the date range.</p>
      <a class="btn btn-brand" href="<?= e(base_url('admin/donations')) ?>">Show everything</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Receipt / Reference</th><th>Transaction date</th><th>Donor / phone / email</th><th>Method / transaction ID</th><th>Verification</th><th>Recipient</th><th>Referrer</th><th>Staff</th><th>Status</th><th class="num">Amount</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($donations as $donation): ?>
            <tr>
              <td class="mono"><a href="<?= e(base_url('admin/donations/' . (string) $donation['id'])) ?>"><?= e((string) $donation['public_reference']) ?></a><br><small><?= e((string) ($donation['receipt_number'] ?? 'Not issued')) ?></small></td>
              <td><?= e(dt((string) $donation['transaction_at'] ?: $donation['created_at'], 'j M Y H:i')) ?></td>
              <td class="wrap">
                <?= e(!empty($donation['anonymous']) ? 'Anonymous' : (string) ($donation['donor_name'] ?? '—')) ?>
                <?php if (empty($donation['anonymous']) && !empty($donation['donor_email'])): ?>
                  <div class="muted" style="font-size:12px"><?= e((string) $donation['donor_email']) ?></div>
                <?php endif; ?>
                <div><?= e((string) ($donation['donor_phone'] ?? '')) ?></div>
              </td>
              <td>
                <?= e($donation['gateway_code'] ? ucfirst((string) $donation['gateway_code']) : ucwords(str_replace('_', ' ', (string) ($donation['source'] ?? 'offline')))) ?>
                <div class="muted" style="font-size:12px"><?= e($donation['provider_transaction_id'] ?: ($donation['bank_reference'] ?? '')) ?></div>
              </td>
              <td><?= e((string) ($donation['verification_status'] ?? 'legacy')) ?></td>
              <td><?= e((string) ($donation['recipient_name'] ?: ($donation['acknowledgment_self'] ? ($donation['donor_name'] ?? '') : ''))) ?></td>
              <td><?= e((string) ($donation['referrer_name'] ?? '')) ?></td>
              <td><small>Created: <?= e((string) ($donation['created_by_email'] ?? 'Public')) ?><br>Verified: <?= e((string) ($donation['verified_by_email'] ?? '—')) ?></small></td>
              <td><span class="<?= e(status_badge_class((string) $donation['status'])) ?>"><?= e((string) $donation['status']) ?></span></td>
              <td class="num"><?= e(money((int) $donation['amount_minor'], (string) $donation['currency'])) ?></td>
              <td><a class="btn btn-light btn-sm" href="<?= e(base_url('admin/donations/' . (string) $donation['id'])) ?>">Open</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php
    $query = ['q' => $filters['q'], 'status' => $filters['status'], 'gateway' => $filters['gateway'], 'date_from' => $filters['date_from'], 'date_to' => $filters['date_to'], 'purpose_id' => $filters['purpose_id'] ?? '', 'source' => $filters['source'] ?? ''];
    $query = array_merge($query, array_intersect_key($filters, array_flip(['donor_name','phone','email','amount_min','amount_max','receipt_number','transaction_id','payment_method','verification_status','recipient','referrer','staff_member'])));
    require __DIR__ . '/../../partials/pagination.php';
    ?>
  <?php endif; ?>
</section>
