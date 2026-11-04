<?php
/**
 * Manual adjustment form for one donation.
 * @var array $donation @var array $transactions @var array $adjustments @var array $types
 */
$status = (string) $donation['status'];
$currency = (string) $donation['currency'];
?>
<div class="dash-head">
  <div>
    <h1>Adjust <?= e((string) $donation['public_reference']) ?></h1>
    <p class="sub">
      <span class="<?= e(status_badge_class($status)) ?>"><?= e($status) ?></span>
      · <?= e(money((int) $donation['amount_minor'], $currency)) ?>
      · <?= e((string) ($donation['donor_name'] ?? 'Anonymous')) ?>
      · <?= e((string) ($donation['purpose_name'] ?? 'General Fund')) ?>
      · <?= e((string) ($donation['gateway_name'] ?? ucwords(str_replace('_', ' ', (string) $donation['source'])))) ?>
    </p>
  </div>
  <a class="btn btn-light" href="<?= e(base_url('admin/donations/' . (int) $donation['id'])) ?>">Back to donation</a>
</div>

<div class="grid-2">
  <form method="post" action="<?= e(base_url('admin/finance/donations/' . (int) $donation['id'] . '/adjust')) ?>" class="panel" novalidate>
    <?= csrf_field() ?>
    <h2>New adjustment</h2>
    <?php if ($types === []): ?><p class="muted">No manual adjustments are permitted for this donation. Historical and verified payment records remain protected.</p><?php endif; ?>
    <p class="muted">Every adjustment is permanent, logged with your name, IP and reason, and visible in the audit log.</p>
    <?php if (error_for('form') !== ''): ?><div class="alert alert-error"><?= error_for('form') ?></div><?php endif; ?>

    <div class="field">
      <label for="adjustment_type">Adjustment<span class="req">*</span></label>
      <select class="select-field" id="adjustment_type" name="adjustment_type" data-toggle-period>
        <?php foreach ($types as $k => $label): ?>
          <option value="<?= e($k) ?>" <?= old('adjustment_type') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field" data-period="amount_correction" hidden>
      <label for="new_amount">New amount (<?= e($currency) ?>)</label>
      <input class="input" type="number" step="0.01" min="1" id="new_amount" name="new_amount" value="<?= old('new_amount', number_format(((int) $donation['amount_minor']) / 100, 2, '.', '')) ?>">
    </div>

    <?php if ($transactions !== []): ?>
    <div class="field">
      <label for="payment_transaction_id">Related gateway transaction</label>
      <select class="select-field" id="payment_transaction_id" name="payment_transaction_id">
        <option value="">— none —</option>
        <?php foreach ($transactions as $t): ?>
          <option value="<?= (int) $t['id'] ?>">#<?= (int) $t['id'] ?> <?= e((string) $t['gateway_code']) ?> · <?= e((string) $t['status']) ?> · <?= e((string) ($t['provider_transaction_id'] ?? $t['merchant_order_id'])) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>

    <div class="field">
      <label for="evidence_reference">Evidence reference</label>
      <input class="input" id="evidence_reference" name="evidence_reference" maxlength="190" value="<?= old('evidence_reference') ?>" placeholder="Bank statement line, gateway portal ID, email subject…">
    </div>

    <div class="field">
      <label for="reason">Reason<span class="req">*</span></label>
      <textarea class="input" id="reason" name="reason" rows="3" required minlength="10" maxlength="500" placeholder="Why is this change correct?"><?= old('reason') ?></textarea>
    </div>

    <div class="field" data-period="mark_completed" hidden>
      <label class="checkbox-row"><input type="checkbox" name="send_receipt" value="1" checked> Email the donor a receipt once completed</label>
    </div>

    <div class="sr-actions">
      <button class="btn btn-brand" type="submit" <?= $types === [] ? 'disabled' : '' ?>>Apply adjustment</button>
    </div>
  </form>

  <section class="panel">
    <h2>Previous adjustments</h2>
    <?php if ($adjustments === []): ?><p class="muted">None.</p><?php else: ?>
      <ul style="list-style:none;padding:0;margin:0;display:grid;gap:12px">
        <?php foreach ($adjustments as $a): ?>
          <li>
            <strong><?= e(str_replace('_', ' ', (string) $a['adjustment_type'])) ?></strong>
            <span class="muted">— <?= e((string) $a['old_status']) ?> → <?= e((string) $a['new_status']) ?><?= (int) $a['old_amount_minor'] !== (int) $a['new_amount_minor'] ? ', ' . e(money((int) $a['old_amount_minor'], $currency)) . ' → ' . e(money((int) $a['new_amount_minor'], $currency)) : '' ?></span>
            <div><?= e((string) $a['reason']) ?></div>
            <div class="muted" style="font-size:12px"><?= e((string) ($a['actor_name'] ?? '')) ?> · <?= e(dt((string) $a['created_at'], 'j M Y H:i')) ?><?= !empty($a['evidence_reference']) ? ' · ref ' . e((string) $a['evidence_reference']) : '' ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($transactions !== []): ?>
      <h3 style="margin-top:18px">Gateway transactions</h3>
      <table class="table">
        <thead><tr><th>#</th><th>Gateway</th><th>Status</th><th class="num">Amount</th></tr></thead>
        <tbody>
        <?php foreach ($transactions as $t): ?>
          <tr><td><?= (int) $t['id'] ?></td><td><?= e((string) $t['gateway_code']) ?></td><td><span class="<?= e(status_badge_class((string) $t['status'])) ?>"><?= e((string) $t['status']) ?></span></td><td class="num"><?= e(money((int) $t['amount_minor'], $currency)) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
</div>
