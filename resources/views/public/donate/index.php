<?php
/**
 * /donate — single donation page.
 * @var list<array> $purposes  @var string $selected  @var list<int> $presets  @var ?int $preAmount
 * @var list<array> $gateways  @var bool $gatewayReady  @var string $currency  @var int $minAmount  @var int $maxAmount
 * @var list<array> $banks
 */
$presets = $presets ?? [2500, 10000, 25000, 100000];
$oldAmount = old('amount', $selectedCurrency === $currency && $preAmount !== null ? (string) $preAmount : '');
$presetChecked = $oldAmount !== '' && in_array((int) $oldAmount, $presets, true) ? (int) $oldAmount : ($oldAmount === '' ? ($presets[1] ?? $presets[0]) : null);
$customValue = $oldAmount !== '' && !in_array((int) $oldAmount, $presets, true) ? $oldAmount : '';
$selectedPurpose = null;
foreach ($purposes as $p) { if ($p['slug'] === $selected) { $selectedPurpose = $p; } }
$fmt = static fn (int $n): string => number_format($n);
$hasSelectedBank = count(array_filter($banks, static fn ($b): bool => strtoupper((string) ($b['currency'] ?? '')) === $selectedCurrency)) > 0;
?>
<link rel="stylesheet" href="<?= e(asset('assets/css/donate-v31.css')) ?>">
<?php
// v3.1: SVG flags (flag-icons, MIT) replace emoji. Missing code => no flag, never an error.
$flagFile = ['PKR' => 'pk', 'USD' => 'us', 'GBP' => 'gb', 'EUR' => 'eu', 'AED' => 'ae', 'SAR' => 'sa', 'CAD' => 'ca', 'AUD' => 'au'];
$flagUrl = static fn (string $code): string => isset($flagFile[$code]) ? asset('assets/images/flags/' . $flagFile[$code] . '.svg') : '';
?>
<section class="dn-hero">
    <div class="container">
        <span class="dn-eyebrow">Make a Difference</span>
        <h1>Donate Now</h1>
        <p>Every gift helps a child, a family or a community. Choose where it goes, then give securely online in PKR or by bank transfer.</p>
    </div>
</section>

<section class="dn-section">
    <div class="container donate-layout dn-layout">
        <div class="form-card dn-card">
            <?php if ((!$gatewayReady || $gateways === []) && $selectedCurrency === $currency): ?>
            <div class="alert alert-warning" role="status">
                <div><strong>Online card payments are temporarily unavailable.</strong> If a bank account is listed below, you can still donate by transfer.</div>
            </div>
            <?php endif; ?>

            <form id="donateForm" method="post" action="<?= e(base_url('fundraise-submit.php')) ?>" data-transfer-url="<?= e(base_url('donate/transfer')) ?>" data-min="<?= $minAmount ?>" data-max="<?= $maxAmount ?>" data-site-currency="<?= e($currency) ?>" data-simple-donation="1" data-currency-url="<?= e(base_url('donate/currency')) ?>" data-estimate-url="<?= e(base_url('donate/estimate')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="donation_attempt" value="<?= e($donationNonce) ?>">
                <input type="hidden" name="type" value="purpose">
                <input type="hidden" name="amount" value="<?= e($oldAmount !== '' ? $oldAmount : (string) ($rateEstimates[$selectedCurrency][(string) ($presetChecked ?? '')] ?? '')) ?>">

                <h2 class="dn-step"><span class="dn-step-no">1</span> Where should your gift go?</h2>
                <div class="field">
                    <label for="purpose">Donation purpose</label>
                    <select id="purpose" name="target" required>
                        <?php foreach ($purposes as $p): ?>
                        <option value="<?= e((string) $p['slug']) ?>"<?= $p['slug'] === $selected ? ' selected' : '' ?> data-zakat="<?= (int) $p['is_zakat_eligible'] ?>" data-desc="<?= e((string) ($p['description'] ?? '')) ?>"><?= e((string) $p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($selectedPurpose && !empty($selectedPurpose['description'])): ?><div class="hint" id="purposeHint"><?= e((string) $selectedPurpose['description']) ?></div><?php else: ?><div class="hint" id="purposeHint"></div><?php endif; ?>
                </div>
                <details class="donate-extra"<?= old('donation_type') === 'monthly' ? ' open' : '' ?>>
                    <summary>Monthly pledge instead? (optional)</summary>
                <div class="field">
                    <span class="label">Frequency</span>
                    <div class="choice-row">
                        <label class="choice"><input type="radio" name="donation_type" value="one_time"<?= old('donation_type', 'one_time') !== 'monthly' ? ' checked' : '' ?>> <span>One-time</span></label>
                        <label class="choice"><input type="radio" name="donation_type" value="monthly"<?= old('donation_type') === 'monthly' ? ' checked' : '' ?>> <span>Monthly pledge</span></label>
                    </div>
                    <div class="hint">Monthly pledges are recorded as your intention; we will send a friendly reminder each month — no card is stored.</div>
                </div>
                </details>

                <div class="dn-step-row">
                    <h2 class="dn-step"><span class="dn-step-no">2</span> Choose an amount</h2>
                    <div class="field currency-field dn-currency" id="currencyField">
                        <label for="donationCurrency" class="sr-only">Donation currency</label>
                        <select id="donationCurrency" name="currency" aria-describedby="currencyHelp rateStatus">
                            <?php foreach ($currencies as $code): $opt = $currencyOptions[$code] ?? ['name'=>$code,'flag'=>'','symbol'=>$code]; ?>
                            <option value="<?= e($code) ?>" data-flag="<?= e($flagUrl($code)) ?>"<?= $selectedCurrency === $code ? ' selected' : '' ?>><?= e($opt['name'] . ' – ' . $code . ' (' . $opt['symbol'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="currency-combobox dn-combo" id="currencyCombobox" hidden>
                            <?php $curFlag = $flagUrl($selectedCurrency); ?>
                            <img id="currencyFlag" class="dn-flag dn-flag-current" src="<?= e($curFlag) ?>" alt="" width="22" height="16"<?= $curFlag === '' ? ' hidden' : '' ?>>
                            <input id="currencySearch" type="text" role="combobox" autocomplete="off" aria-autocomplete="list" aria-expanded="false" aria-controls="currencyOptions" aria-describedby="currencyHelp" aria-label="Search donation currencies" placeholder="Search currency, country or code">
                            <svg class="dn-caret" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                            <div id="currencyOptions" class="currency-list" role="listbox" aria-label="Available donation currencies" hidden>
                                <?php foreach ($currencies as $code): $opt = $currencyOptions[$code] ?? ['name'=>$code,'flag'=>'','symbol'=>$code]; $fu = $flagUrl($code); ?>
                                <button type="button" role="option" class="currency-option" id="currencyOption-<?= e($code) ?>" data-code="<?= e($code) ?>" aria-selected="<?= $selectedCurrency === $code ? 'true' : 'false' ?>" tabindex="-1">
                                    <?php if ($fu !== ''): ?><img class="dn-flag" src="<?= e($fu) ?>" alt="" width="22" height="16" loading="lazy"><?php else: ?><span class="dn-flag dn-flag-none" aria-hidden="true"></span><?php endif; ?>
                                    <span><?= e($opt['name']) ?></span> <b><?= e($code) ?></b> <span><?= e($opt['symbol']) ?></span>
                                </button>
                                <?php endforeach; ?>
                                <p id="currencyEmpty" hidden>No matching payable currency.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="dn-fineprint" id="currencyHelp"><?= $currencySource === 'location' ? 'Suggested from your approximate location. ' : '' ?>You can change currency at any time. Online payment is in <?= e($currency) ?>; other currencies use a published bank account.</p>
                <p class="dn-fineprint" id="rateStatus" role="status" aria-live="polite"><?php if ($rateUpdated): ?>Converted amounts are estimates<?= $rateStale ? ' (cached rate; refresh pending)' : '' ?>. Rates updated <?= e(gmdate('Y-m-d H:i', (int) $rateUpdated)) ?> UTC. No payment conversion is performed.<?php else: ?>Exchange-rate estimates are unavailable. Enter an amount in your chosen currency; no conversion will be performed.<?php endif; ?></p>
                <script type="application/json" id="donationEstimates"><?= json_encode($rateEstimates, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_THROW_ON_ERROR) ?></script>
                <div class="amount-grid" role="radiogroup" aria-label="Preset donation amounts"<?= $selectedCurrency === $currency || isset($rateEstimates[$selectedCurrency]) ? '' : ' hidden' ?>>
                    <?php foreach ($presets as $amt): ?>
                    <label class="amount-opt">
                        <input type="radio" name="amount_preset" data-base-amount="<?= $amt ?>" value="<?= e((string) ($rateEstimates[$selectedCurrency][(string) $amt] ?? $amt)) ?>"<?= $presetChecked === $amt ? ' checked' : '' ?>>
                        <span><span class="amount-code"><?= e($selectedCurrency) ?></span> <span class="amount-display"><?= e(isset($rateEstimates[$selectedCurrency][(string) $amt]) ? number_format((float) $rateEstimates[$selectedCurrency][(string) $amt], 2) : $fmt($amt)) ?></span></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="field amount-custom<?= error_for('amount') !== '' ? ' has-error' : '' ?>">
                    <label for="customAmount">Or enter your own amount (<?= e($selectedCurrency) ?>)</label>
                    <span class="prefix" id="amountPrefix"><?= e($selectedCurrency) ?></span>
                    <input id="customAmount" type="number" inputmode="decimal" min="<?= $minAmount ?>" max="<?= $maxAmount ?>" step="0.01" placeholder="Enter amount" value="<?= e($customValue) ?>" autocomplete="off">
                    <div class="hint">Online <?= e($currency) ?> payments: minimum <?= e($fmt($minAmount)) ?>, maximum <?= e($fmt($maxAmount)) ?> per transaction.</div>
                    <?php if (error_for('amount') !== ''): ?><div class="error"><?= e(error_for('amount')) ?></div><?php endif; ?>
                </div>
                <p class="dn-fineprint">Location suggestion uses <a href="https://ipwhois.io/privacy" target="_blank" rel="noopener noreferrer">IPWho</a> (your IP address, once). Your own choice always wins. <a href="https://www.exchangerate-api.com" target="_blank" rel="noopener noreferrer">Rates by Exchange Rate API</a>.</p>

                <h2 class="dn-step"><span class="dn-step-no">3</span> Your details</h2>
                <div class="form-grid">
                    <?php foreach ([
                        ['name' => 'name', 'label' => 'Full name', 'required' => true, 'autocomplete' => 'name', 'max' => 120, 'placeholder' => 'E.g. Ali Raza'],
                        ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'max' => 190, 'hint' => 'Your receipt is sent here.', 'placeholder' => 'E.g. ali@example.com'],
                        ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'autocomplete' => 'tel', 'max' => 30, 'placeholder' => 'E.g. +92 300 400 5000'],
                    ] as $f) { require dirname(__DIR__, 2) . '/partials/forms/_field.php'; } ?>
                </div>
                <details class="donate-extra"<?= old('anonymous') !== '' || old('city') !== '' || old('message') !== '' || old('recipient_name') !== '' || old('acknowledgment_self') === '0' || old('referral_enabled') === '1' ? ' open' : '' ?>>
                    <summary>Additional details (optional)</summary>
                    <div class="form-grid">
                        <?php foreach ([
                            ['name' => 'city', 'label' => 'City', 'autocomplete' => 'address-level2', 'max' => 120],
                            ['name' => 'country', 'label' => 'Country', 'autocomplete' => 'country-name', 'max' => 80, 'value' => 'Pakistan'],
                            ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'rows' => 2, 'max' => 500, 'wide' => true],
                        ] as $f) { require dirname(__DIR__, 2) . '/partials/forms/_field.php'; } ?>
                        <div class="field field-wide"><label class="choice"><input type="checkbox" name="anonymous" value="1"<?= old('anonymous') !== '' ? ' checked' : '' ?>> <span>Keep my donation anonymous</span></label></div>
                    </div>
                    <?php $__extra = dirname(__DIR__, 2) . '/partials/donation-extra-fields.php'; if (is_file($__extra)) { require $__extra; } ?>
                </details>

                <h2 class="dn-step"><span class="dn-step-no">4</span> Payment method</h2>
                <div class="gateway-list">
                    <?php foreach ($gateways as $i => $gw): ?>
                    <label class="gateway-opt" data-online-method<?= $selectedCurrency === $currency ? '' : ' hidden' ?>>
                        <input type="radio" name="gateway" value="<?= e((string) $gw['code']) ?>"<?= ($selectedCurrency === $currency && old('gateway', $i === 0 ? $gw['code'] : '') === $gw['code']) ? ' checked' : '' ?><?= $gatewayReady && $selectedCurrency === $currency ? '' : ' disabled' ?>>
                        <span class="gw">
                            <?php if (!empty($gw['logo'])): ?><img src="<?= e((string) $gw['logo']) ?>" alt="" loading="lazy"><?php else: ?><span class="gw-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span><?php endif; ?>
                            <span><b><?= e((string) $gw['label']) ?></b><small><?= e((string) ($gw['description'] ?: 'Debit / credit card, verified by your bank')) ?></small></span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                    <label class="gateway-opt" id="bankMethod"<?= $hasSelectedBank ? '' : ' hidden' ?>>
                        <input type="radio" name="gateway" value="bank_transfer" data-bank="1"<?= ($selectedCurrency !== $currency || $gateways === [] || old('gateway') === 'bank_transfer') && $hasSelectedBank ? ' checked' : '' ?>>
                        <span class="gw">
                            <span class="gw-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10l9-6 9 6"/><path d="M5 10v8M9 10v8M15 10v8M19 10v8M3 18h18M2 22h20"/></svg></span>
                            <span><b>Bank transfer / deposit</b><small>Pay into our bank account, then upload your slip so we can verify and send your certificate</small></span>
                        </span>
                    </label>
                </div>
                <?php if (error_for('gateway') !== ''): ?><div class="error"><?= e(error_for('gateway')) ?></div><?php endif; ?>

                <div class="form-feedback" id="donateFeedback" role="alert" tabindex="-1"></div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-gold btn-lg" id="donateSubmit">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                        Continue to secure payment
                    </button>
                </div>
                <p class="form-note">Your card details are entered on the bank's own secure page and never touch our servers. A transfer is not considered received until confirmed by our team. No card details are stored on this site.</p>
            </form>
        </div>

        <aside class="donate-aside dn-aside">
            <div class="donate-summary dn-summary">
                <h3>Your donation</h3>
                <div class="sum-amount" id="sumAmount"><?= e($selectedCurrency) ?> 0</div><div class="hint" id="sumEstimateNote"><?= $selectedCurrency === $currency ? 'Online payments are charged in PKR.' : 'Estimated amount only. Your bank transfer and report must use the selected currency.' ?></div>
                <dl>
                    <dt>Purpose</dt><dd id="sumPurpose"><?= e((string) ($selectedPurpose['name'] ?? '')) ?></dd>
                    <dt>Frequency</dt><dd id="sumType">One-time</dd>
                    <dt>Currency</dt><dd id="sumCurrency"><?= e($selectedCurrency) ?></dd>
                </dl>
            </div>

            <div class="bank-quick" id="bankTransfer">
                <h3>Bank transfer</h3>
                <p>Prefer to transfer directly? Use a matching account below, then upload your slip so our team can verify it and email your tax exemption certificate.</p>
                <?php foreach ($banks as $b): ?>
                <?php $bankCurrency = strtoupper((string) ($b['currency'] ?? '')); if (!in_array($bankCurrency, $currencies, true)) continue; ?>
                <div class="bank-mini" data-bank-currency="<?= e($bankCurrency) ?>"<?= $selectedCurrency === $bankCurrency ? '' : ' hidden' ?>>
                    <strong><?= e($bankCurrency) ?></strong>
                    <b><?= e((string) $b['bank_name']) ?></b> <small><?= e((string) $b['account_type']) ?></small>
                    <div>A/C <code><?= e((string) $b['account_number']) ?></code> <button type="button" class="copy-btn" data-copy="<?= e((string) $b['account_number']) ?>">Copy</button></div>
                    <?php if (!empty($b['iban'])): ?><div>IBAN <code><?= e((string) $b['iban']) ?></code> <button type="button" class="copy-btn" data-copy="<?= e((string) $b['iban']) ?>">Copy</button></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
                <p id="noBankMessage"<?= $hasSelectedBank ? ' hidden' : '' ?>>No published bank account is available for this currency.</p>
                <a class="btn btn-outline" href="<?= e(base_url('banks')) ?>">All bank account information</a>
                <a class="btn btn-gold" id="reportTransfer" href="<?= e(base_url('donate/transfer?mode=report&currency=' . rawurlencode($selectedCurrency))) ?>">I already paid &mdash; upload my slip</a>
                <a class="btn btn-outline" href="<?= e(base_url('donate/transfer?mode=new&currency=' . rawurlencode($selectedCurrency))) ?>">I want to make a new transfer</a>
            </div>
        </aside>
    </div>
</section>

<script src="<?= e(asset('assets/js/donate.js')) ?>" defer></script>
<script>
/* v3.1: keep the small flag beside the currency box in step with the selection. */
(function () {
    var sel = document.getElementById('donationCurrency'), img = document.getElementById('currencyFlag');
    if (!sel || !img) { return; }
    sel.addEventListener('change', function () {
        var f = sel.selectedOptions[0] ? sel.selectedOptions[0].getAttribute('data-flag') : '';
        if (f) { img.src = f; img.hidden = false; } else { img.hidden = true; }
    });
})();
</script>
