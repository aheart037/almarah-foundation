<?php
/**
 * /donate — single donation page.
 * @var list<array> $purposes  @var string $selected  @var list<int> $presets  @var ?int $preAmount
 * @var list<array> $gateways  @var bool $gatewayReady  @var string $currency  @var int $minAmount  @var int $maxAmount
 * @var list<array> $banks
 */
$presets = $presets ?? [2500, 10000, 25000, 100000];
$oldAmount = old('amount', $preAmount !== null ? (string) $preAmount : '');
$presetChecked = $oldAmount !== '' && in_array((int) $oldAmount, $presets, true) ? (int) $oldAmount : ($oldAmount === '' ? ($presets[1] ?? $presets[0]) : null);
$customValue = $oldAmount !== '' && !in_array((int) $oldAmount, $presets, true) ? $oldAmount : '';
$selectedPurpose = null;
foreach ($purposes as $p) { if ($p['slug'] === $selected) { $selectedPurpose = $p; } }
$fmt = static fn (int $n): string => 'Rs ' . number_format($n);
?>
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Make a Difference</span>
        <h1>Donate Now</h1>
        <p>Every rupee reaches a child, a family or a student. Secure card payment through our bank partners, or transfer directly to our accounts.</p>
    </div>
</section>

<section class="section">
    <div class="container donate-layout">
        <div class="form-card">
            <?php if (!$gatewayReady || $gateways === []): ?>
            <div class="alert alert-warning" role="status">
                <div><strong>Online card payments are temporarily unavailable.</strong> You can still donate by bank transfer using the account details below.</div>
            </div>
            <?php endif; ?>

            <form id="donateForm" method="post" action="<?= e(base_url('fundraise-submit.php')) ?>" data-min="<?= $minAmount ?>" data-max="<?= $maxAmount ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="purpose">
                <input type="hidden" name="amount" value="<?= e($oldAmount) ?>">

                <h2 class="form-section-title"><span class="step-no">1</span> Where should your gift go?</h2>
                <div class="field">
                    <label for="purpose">Donation purpose</label>
                    <select id="purpose" name="target" required>
                        <?php foreach ($purposes as $p): ?>
                        <option value="<?= e((string) $p['slug']) ?>"<?= $p['slug'] === $selected ? ' selected' : '' ?> data-zakat="<?= (int) $p['is_zakat_eligible'] ?>" data-desc="<?= e((string) ($p['description'] ?? '')) ?>"><?= e((string) $p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($selectedPurpose && !empty($selectedPurpose['description'])): ?><div class="hint" id="purposeHint"><?= e((string) $selectedPurpose['description']) ?></div><?php else: ?><div class="hint" id="purposeHint"></div><?php endif; ?>
                </div>
                <div class="field">
                    <span class="label">Frequency</span>
                    <div class="choice-row">
                        <label class="choice"><input type="radio" name="donation_type" value="one_time"<?= old('donation_type', 'one_time') !== 'monthly' ? ' checked' : '' ?>> <span>One-time</span></label>
                        <label class="choice"><input type="radio" name="donation_type" value="monthly"<?= old('donation_type') === 'monthly' ? ' checked' : '' ?>> <span>Monthly pledge</span></label>
                    </div>
                    <div class="hint">Monthly pledges are recorded as your intention; we will send a friendly reminder each month — no card is stored.</div>
                </div>

                <h2 class="form-section-title"><span class="step-no">2</span> Choose an amount (<?= e($currency) ?>)</h2>
                <div class="amount-grid" role="radiogroup" aria-label="Preset amounts">
                    <?php foreach ($presets as $amt): ?>
                    <label class="amount-opt">
                        <input type="radio" name="amount_preset" value="<?= $amt ?>"<?= $presetChecked === $amt ? ' checked' : '' ?>>
                        <span><?= e($fmt($amt)) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="field amount-custom<?= error_for('amount') !== '' ? ' has-error' : '' ?>">
                    <label for="customAmount" class="sr-only">Or enter your own amount</label>
                    <span class="prefix">Rs</span>
                    <input id="customAmount" type="number" inputmode="numeric" min="<?= $minAmount ?>" max="<?= $maxAmount ?>" step="1" placeholder="Or enter your own amount" value="<?= e($customValue) ?>" autocomplete="off">
                    <div class="hint">Minimum <?= e($fmt($minAmount)) ?>, maximum <?= e($fmt($maxAmount)) ?> per transaction.</div>
                    <?php if (error_for('amount') !== ''): ?><div class="error"><?= e(error_for('amount')) ?></div><?php endif; ?>
                </div>

                <h2 class="form-section-title"><span class="step-no">3</span> Your details</h2>
                <div class="form-grid">
                    <?php foreach ([
                        ['name' => 'name', 'label' => 'Full name', 'required' => true, 'autocomplete' => 'name', 'max' => 120, 'placeholder' => 'E.g. Ali Raza'],
                        ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'max' => 190, 'hint' => 'Your receipt is sent here.', 'placeholder' => 'E.g. ali@example.com'],
                        ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'autocomplete' => 'tel', 'max' => 30, 'placeholder' => 'E.g. +92 300 400 5000'],
                        ['name' => 'city', 'label' => 'City', 'autocomplete' => 'address-level2', 'max' => 120, 'placeholder' => 'E.g. Lahore'],
                        ['name' => 'country', 'label' => 'Country', 'autocomplete' => 'country-name', 'max' => 80, 'value' => 'Pakistan'],
                        ['name' => 'message', 'label' => 'Message (optional)', 'type' => 'textarea', 'rows' => 2, 'max' => 500, 'wide' => true],
                    ] as $f) { require dirname(__DIR__, 2) . '/partials/forms/_field.php'; } ?>
                    <div class="field field-wide">
                        <label class="choice"><input type="checkbox" name="anonymous" value="1"<?= old('anonymous') !== '' ? ' checked' : '' ?>> <span>Keep my donation anonymous</span></label>
                    </div>
                </div>

                <h2 class="form-section-title"><span class="step-no">4</span> Payment method</h2>
                <div class="gateway-list">
                    <?php foreach ($gateways as $i => $gw): ?>
                    <label class="gateway-opt">
                        <input type="radio" name="gateway" value="<?= e((string) $gw['code']) ?>"<?= old('gateway', $i === 0 ? $gw['code'] : '') === $gw['code'] ? ' checked' : '' ?><?= $gatewayReady ? '' : ' disabled' ?>>
                        <span class="gw">
                            <?php if (!empty($gw['logo'])): ?><img src="<?= e((string) $gw['logo']) ?>" alt="" loading="lazy"><?php else: ?><span class="gw-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span><?php endif; ?>
                            <span><b><?= e((string) $gw['label']) ?></b><small><?= e((string) ($gw['description'] ?: 'Debit / credit card, verified by your bank')) ?></small></span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                    <label class="gateway-opt">
                        <input type="radio" name="gateway" value="bank_transfer" data-bank="1"<?= old('gateway') === 'bank_transfer' ? ' checked' : '' ?>>
                        <span class="gw">
                            <span class="gw-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10l9-6 9 6"/><path d="M5 10v8M9 10v8M15 10v8M19 10v8M3 18h18M2 22h20"/></svg></span>
                            <span><b>Bank transfer / deposit</b><small>Transfer to one of our accounts and email us the receipt</small></span>
                        </span>
                    </label>
                </div>
                <?php if (error_for('gateway') !== ''): ?><div class="error"><?= e(error_for('gateway')) ?></div><?php endif; ?>

                <div class="form-feedback" id="donateFeedback" role="alert"></div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-gold btn-lg" id="donateSubmit">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                        Continue to secure payment
                    </button>
                </div>
                <p class="form-note">Your card details are entered on the bank's own secure page and never touch our servers. Almarah Foundation is a registered charity; donations may be tax-deductible under section 2(36) of the Income Tax Ordinance.</p>
            </form>
        </div>

        <aside class="donate-aside">
            <div class="donate-summary">
                <h3>Your donation</h3>
                <div class="sum-amount" id="sumAmount">Rs 0</div>
                <dl>
                    <dt>Purpose</dt><dd id="sumPurpose"><?= e((string) ($selectedPurpose['name'] ?? '')) ?></dd>
                    <dt>Frequency</dt><dd id="sumType">One-time</dd>
                    <dt>Currency</dt><dd><?= e($currency) ?></dd>
                </dl>
            </div>

            <div class="bank-quick" id="bankTransfer">
                <h3>Bank transfer</h3>
                <p>Prefer to transfer directly? Use any account below and email your receipt to <a href="mailto:info@almarah.org">info@almarah.org</a>.</p>
                <?php foreach (array_slice($banks, 0, 3) as $b): ?>
                <div class="bank-mini">
                    <b><?= e((string) $b['bank_name']) ?></b> <small><?= e((string) $b['account_type']) ?></small>
                    <div>A/C <code><?= e((string) $b['account_number']) ?></code> <button type="button" class="copy-btn" data-copy="<?= e((string) $b['account_number']) ?>">Copy</button></div>
                    <?php if (!empty($b['iban'])): ?><div>IBAN <code><?= e((string) $b['iban']) ?></code> <button type="button" class="copy-btn" data-copy="<?= e((string) $b['iban']) ?>">Copy</button></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
                <a class="btn btn-outline" href="<?= e(base_url('banks')) ?>">All bank accounts (PK · UK · US)</a>
            </div>
        </aside>
    </div>
</section>
