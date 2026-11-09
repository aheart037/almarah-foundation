<?php
/**
 * /donate — bank transfer section (v3.5).
 *
 * Same layout and look as the original donate page: "Donate via Bank Transfer"
 * heading, How to Donate, Zakat banner, Pakistan + International account cards,
 * Meezan Zakat account, Important points and the closing call to action.
 * The accounts come from the admin-managed bank_accounts table ($banks), so
 * adding, editing or hiding an account in the admin panel updates this page.
 *
 * Styles: the bnk-* / don-* rules already bundled in assets/css/site.css.
 *
 * @var list<array<string,mixed>> $banks
 */
$bnkPk = $bnkIntl = [];
$bnkZakat = null;
foreach ($banks as $__b) {
    $type = (string) ($__b['account_type'] ?? '');
    if (!empty($__b['is_international'])) {
        $bnkIntl[] = $__b;
    } elseif (stripos($type, 'zakat') !== false) {
        $bnkZakat = $bnkZakat ?? $__b;
    } else {
        $bnkPk[] = $__b;
    }
}
$bnkCopyIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
$bnkLogo = static function (array $b): string {
    $p = trim((string) ($b['logo_path'] ?? ''));
    $name = (string) ($b['bank_name'] ?? '');
    if ($p !== '') {
        $src = preg_match('#^https?://#i', $p) ? $p : asset($p);
        return '<div class="bnk-logo-box"><img src="' . e($src) . '" alt="' . e($name) . '" loading="lazy"></div>';
    }
    $initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name) ?: '?', 0, 2));
    return '<div class="bnk-logo-fallback" style="background:linear-gradient(135deg,#8A2050,#A92D63);">' . e($initials) . '</div>';
};
$bnkField = static function (string $label, string $value, string $cls = '') use ($bnkCopyIcon): string {
    if ($value === '') {
        return '';
    }
    return '<div class="bnk-detail-item"><div class="bnk-detail-label">' . e($label) . ' (tap to copy)</div>'
        . '<button type="button" class="bnk-copy-btn' . ($cls !== '' ? ' ' . $cls : '') . '" data-bnk-copy="' . e($value) . '">'
        . '<span>' . e($value) . '</span>' . $bnkCopyIcon . '</button></div>';
};
$bnkCard = static function (array $b, bool $intl) use ($bnkLogo, $bnkField): string {
    $cls = $intl ? 'intl-btn' : '';
    $isZelle = stripos((string) $b['bank_name'], 'zelle') !== false;
    $extra = trim((string) ($b['swift_code'] ?? ''));
    $extraLabel = preg_match('/^\d{2}-\d{2}-\d{2}$/', $extra) ? 'Sort Code' : 'SWIFT / BIC';
    $html  = '<div class="bnk-card' . ($intl ? ' intl' : '') . '"><div class="bnk-card-logo-area">' . $bnkLogo($b)
        . '<div class="bnk-card-name-wrap"><div class="bnk-card-name">' . e((string) $b['bank_name']) . '</div>'
        . '<div class="bnk-card-type">' . e((string) ($b['account_type'] ?? '')) . '</div></div></div>'
        . '<div class="bnk-card-details"><div class="bnk-detail-row">'
        . '<div class="bnk-detail-item"><div class="bnk-detail-label">Account Title</div><div class="bnk-detail-val">' . e((string) ($b['account_title'] ?? '')) . '</div></div>'
        . '<div class="bnk-card-divider"></div>'
        . $bnkField($isZelle ? 'Zelle Phone' : 'Account Number', trim((string) ($b['account_number'] ?? '')), $cls)
        . ($extra !== '' ? $bnkField($extraLabel, $extra, $cls) : '')
        . $bnkField('IBAN', trim((string) ($b['iban'] ?? '')), $cls);
    $branch = trim((string) ($b['branch'] ?? ''));
    if ($branch !== '') {
        $html .= '<div class="bnk-detail-item"><div class="bnk-detail-label">Branch</div><div class="bnk-detail-val">' . e($branch) . '</div></div>';
    }
    if ($isZelle) {
        $html .= '<div class="bnk-detail-item"><div class="bnk-detail-label">How to send</div><div class="bnk-detail-val" style="font-size:.80rem;color:var(--text-light);font-weight:500;">Open your Zelle app or your bank\'s Zelle feature and send to the phone number above.</div></div>';
    }
    return $html . '</div></div></div>';
};
if ($banks === []) {
    return;
}
?>
<div class="bnk-toast" id="bnkToast" role="status" aria-live="polite">Copied!</div>

<!-- Bank transfer heading -->
<div class="don-bank-divider" id="bank-transfer">
    <div class="don-bank-divider-inner">
        <div class="don-bank-divider-tag">Bank Transfer</div>
        <h2>Donate via <span>Bank Transfer</span></h2>
        <p>Transfer securely to any of our verified accounts. All accounts are registered under Almarah Foundation. Tap any number to copy it instantly.</p>
    </div>
</div>

<!-- How to donate -->
<section class="bnk-intro">
    <div class="bnk-wrap">
        <div class="bnk-section-header">
            <div class="bnk-tag">Simple Process</div>
            <h2>How to <span class="accent">Donate</span></h2>
            <div class="bnk-divider"><div class="gem"></div></div>
            <p class="bnk-section-sub">Three easy ways to get your donation to us</p>
        </div>
        <div class="bnk-methods">
            <div class="bnk-method-card">
                <div class="bnk-method-icon">🏦</div>
                <h3>Branch Deposit</h3>
                <p>Walk into any branch of our partner banks and deposit directly into the Almarah Foundation account. Show the account number below to the bank teller.</p>
            </div>
            <div class="bnk-method-card">
                <div class="bnk-method-icon">📱</div>
                <h3>Online Transfer</h3>
                <p>Use your bank app or internet banking to send funds. For transfers between different banks, use the IBAN number through RAAST or IBFT.</p>
            </div>
            <div class="bnk-method-card">
                <div class="bnk-method-icon">🧾</div>
                <h3>Upload Your Receipt</h3>
                <p>Once you have transferred, upload your payment slip so our team can verify it and email you an acknowledgement and your certificate.</p>
                <p style="margin-top:14px;"><a class="don-btn-primary" href="<?= e(base_url('donate/transfer?mode=report')) ?>" style="padding:12px 24px;">Upload my receipt</a></p>
            </div>
        </div>
        </div>
    </div>
</section>

<?php if ($bnkZakat !== null): ?>
<!-- Zakat notice -->
<div class="bnk-zakat-banner">
    <div class="bnk-zakat-inner">
        <div class="bnk-zakat-text">
            <strong>Paying Zakat? Please use the <?= e((string) $bnkZakat['bank_name']) ?> account.</strong>
            <p>Your Zakat will be received and distributed in full accordance with Shariah. The dedicated <span><?= e((string) $bnkZakat['bank_name']) ?> Zakat account</span> is listed below.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Bank accounts -->
<section class="bnk-main">
    <div class="bnk-wrap">
        <div class="bnk-section-header">
            <div class="bnk-tag">Partner Banks</div>
            <h2>Our <span class="accent">Bank Accounts</span></h2>
            <div class="bnk-divider"><div class="gem"></div></div>
            <p class="bnk-section-sub">All accounts are registered under Almarah Foundation. Tap any account number to copy it.</p>
        </div>

        <?php if ($bnkPk !== []): ?>
        <div class="bnk-sub-label">
            <span class="bnk-sub-label-text">🇵🇰 &nbsp;Pakistan Accounts</span>
            <div class="bnk-sub-label-line"></div>
        </div>
        <div class="bnk-cards-grid">
            <?php foreach ($bnkPk as $b) { echo $bnkCard($b, false); } ?>
        </div>
        <?php endif; ?>

        <?php if ($bnkIntl !== []): ?>
        <div class="bnk-intl-block">
            <div class="bnk-sub-label">
                <span class="bnk-sub-label-text">🌍 &nbsp;International Donations</span>
                <div class="bnk-sub-label-line" style="background:linear-gradient(90deg,rgba(0,53,128,.25),transparent);"></div>
            </div>
            <div class="bnk-cards-grid-intl">
                <?php foreach ($bnkIntl as $b) { echo $bnkCard($b, true); } ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($bnkZakat !== null): ?>
<!-- Zakat account -->
<section class="bnk-zakat-section">
    <div class="bnk-wrap">
        <div class="bnk-section-header">
            <div class="bnk-tag">Islamic Banking</div>
            <h2><?= e((string) $bnkZakat['bank_name']) ?> <span class="accent">Zakat Account</span></h2>
            <div class="bnk-divider"><div class="gem"></div></div>
            <p class="bnk-section-sub">For Zakat payments only. Handled with full Shariah compliance.</p>
        </div>
        <div class="bnk-meezan-featured">
            <div class="bnk-meezan-inner">
                <div class="bnk-meezan-left">
                    <?php if (trim((string) ($bnkZakat['logo_path'] ?? '')) !== ''): $zl = (string) $bnkZakat['logo_path']; ?>
                    <div class="bnk-meezan-logo-wrap"><img src="<?= e(preg_match('#^https?://#i', $zl) ? $zl : asset($zl)) ?>" alt="<?= e((string) $bnkZakat['bank_name']) ?>"></div>
                    <?php endif; ?>
                    <div class="bnk-meezan-bank-name"><?= e((string) $bnkZakat['bank_name']) ?></div>
                    <div class="bnk-meezan-zakat-pill">Zakat Specific Account</div>
                    <p class="bnk-meezan-desc">This account is for Zakat only, so your Zakat reaches the right people the right way.</p>
                </div>
                <div class="bnk-meezan-right">
                    <div>
                        <div class="bnk-meezan-right-title">Zakat Account Details</div>
                        <p class="bnk-meezan-right-sub">Use these details when sending your Zakat. Tap any field to copy.</p>
                    </div>
                    <div class="bnk-meezan-details-grid">
                        <div class="bnk-meezan-detail">
                            <div class="bnk-meezan-detail-label">Account Title</div>
                            <div class="bnk-meezan-detail-val"><?= e((string) $bnkZakat['account_title']) ?></div>
                        </div>
                        <div class="bnk-meezan-detail">
                            <div class="bnk-meezan-detail-label">Bank Name</div>
                            <div class="bnk-meezan-detail-val"><?= e((string) $bnkZakat['bank_name']) ?></div>
                        </div>
                        <?php foreach ([['Account Number', (string) ($bnkZakat['account_number'] ?? '')], ['IBAN', (string) ($bnkZakat['iban'] ?? '')]] as [$zLabel, $zVal]): if (trim($zVal) === '') { continue; } ?>
                        <div class="bnk-meezan-detail" style="grid-column: 1 / -1;">
                            <div class="bnk-meezan-detail-label"><?= e($zLabel) ?> (tap to copy)</div>
                            <button type="button" class="bnk-meezan-copy-btn" data-bnk-copy="<?= e($zVal) ?>"><span><?= e($zVal) ?></span><?= $bnkCopyIcon ?></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="bnk-meezan-notice">
                        <p><strong>Shariah Compliant:</strong> All Zakat received through this account is managed under Islamic finance principles and distributed to those who are eligible.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Important points -->
<section class="bnk-notes">
    <div class="bnk-notes-deco1"></div>
    <div class="bnk-notes-deco2"></div>
    <div class="bnk-wrap bnk-notes-inner">
        <div class="bnk-notes-header">
            <div class="bnk-notes-tag">Please Read</div>
            <h2>A Few <span>Important Points</span></h2>
            <div class="bnk-notes-divider"><div class="gem"></div></div>
        </div>
        <div class="bnk-notes-grid">
            <div class="bnk-note-card"><div class="bnk-note-body"><strong>Always Upload Your Receipt</strong><p>After completing your transfer, <a href="<?= e(base_url('donate/transfer?mode=report')) ?>" style="color:#FFD740;text-decoration:underline;">upload your payment slip</a>. This helps us confirm your donation and send you an acknowledgement.</p></div></div>
            <div class="bnk-note-card"><div class="bnk-note-body"><strong>Zakat Goes to the Zakat Account</strong><p>Please use the dedicated Zakat account for all Zakat payments. This keeps your Zakat fully Shariah compliant from the moment it leaves your account.</p></div></div>
            <div class="bnk-note-card"><div class="bnk-note-body"><strong>Use Only These Accounts</strong><p>All accounts on this page are officially registered to Almarah Foundation. Please do not transfer to any other account that claims to be ours.</p></div></div>
            <div class="bnk-note-card"><div class="bnk-note-body"><strong>Every Rupee Reaches the Program</strong><p>We keep our operations lean so your donation goes where it matters. Almarah Foundation publishes its financials annually for full transparency.</p></div></div>
            <div class="bnk-note-card"><div class="bnk-note-body"><strong>Donating from Abroad</strong><p>Use the international accounts above for transfers from outside Pakistan. For wire transfers or SWIFT details, write to us at info@almarah.org and we will assist you.</p></div></div>
            <div class="bnk-note-card"><div class="bnk-note-body"><strong>We Are Here to Help</strong><p>If you are unsure about anything or need help completing your donation, get in touch with us. Our team is happy to walk you through the process.</p></div></div>
        </div>
    </div>
</section>

<!-- Closing call to action -->
<section class="don-cta">
    <div class="don-cta-inner">
        <div class="don-cta-tag">Make a Difference</div>
        <h2>Your Donation <span>Changes Lives</span></h2>
        <p>Every contribution helps Almarah Foundation deliver dignity, education, and opportunity to the people who need it most across Pakistan.</p>
        <div class="don-cta-buttons">
            <a href="#online-form" class="don-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                Donate Online Now
            </a>
            <a href="<?= e(base_url('contact')) ?>" class="don-btn-secondary">Contact Us</a>
        </div>
    </div>
</section>

<script>
(function () {
    var toast = document.getElementById('bnkToast'), timer;
    function show(text) {
        if (!toast) { return; }
        toast.textContent = 'Copied: ' + text;
        toast.classList.add('show');
        clearTimeout(timer);
        timer = setTimeout(function () { toast.classList.remove('show'); }, 2500);
    }
    function legacyCopy(str) {
        var ta = document.createElement('textarea');
        ta.value = str; ta.style.cssText = 'position:fixed;opacity:0';
        document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
    }
    document.addEventListener('click', function (ev) {
        var btn = ev.target.closest ? ev.target.closest('[data-bnk-copy]') : null;
        if (!btn) { return; }
        var str = btn.getAttribute('data-bnk-copy');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(str).then(function () { show(str); }, function () { legacyCopy(str); show(str); });
        } else { legacyCopy(str); show(str); }
    });
})();
</script>
